<?php
include '../temp/config.php';
include '../huayan/ahlims.php';
require_once $rootdir."/system_settings/site_type_set/func.php";
$fzx_id = FZX_ID;
$trade_global['daohang'][]  = array('icon'  => '','html'  => '监测计划下达','href'  => "xd_cyrw/xd_cyrw.php?year={$_GET['year']}&month={$_GET['month']}&day={$_GET['day']}");
//引入
$trade_global['js'] = array(
    'lims/d3/d3.js',
    'lims/d3/d3.layout.js',
    'lims/d3/tree.js',
    'lims/jquery.fly.min.js'
);
// 监测计划站点
$sql_where_arr = $cy_sql_where_arr  = ['1'];
// 采样日期
if( !isset($_GET['year']) || !intval($_GET['year']) ){
    $year = date('Y');
}else{
    $year = intval($_GET['year']);
}
if( !isset($_GET['month']) || !intval($_GET['month']) ){
    $month = date('m');
}else{
    $month = intval($_GET['month']);
}
if( empty($_GET['day'])){
    $day = date('d');
}else{
    $day = $_GET['day'];
}
$day_select= get_select_day($year,$month,$day,'全部');//获取“天”的下拉菜单

$sql_where_arr[]    = "YEAR(`jhdate`)   = '{$year}'";
$cy_sql_where_arr[] = "YEAR(`cy_date`)  = '{$year}'";
$sql_where_arr[]    = "MONTH(`jhdate`)  = '{$month}'";
$cy_sql_where_arr[] = "MONTH(`cy_date`) = '{$month}'";
if($day!='全部'){
    $sql_where_arr[]    = "DAYOFMONTH(`jhdate`) = '{$day}'";
    $cy_sql_where_arr[] = "DAYOFMONTH(`cy_date`)= '{$day}'";
}
$jh_sites = [];
$sql_where_str      = implode(' AND ', $sql_where_arr);
$cy_sql_where_str   = implode(' AND ', $cy_sql_where_arr);
#########获取所有站点信息
$site_arr   = [];
$site_sql   = "SELECT * FROM `sites` WHERE `fzx_id`='{$fzx_id}' AND `act`='1'";
$site_query = $DB->query($site_sql);
while ($site_row = $DB->fetch_assoc($site_query)) {
    $site_row['assay_values']   = @explode(',', $site_row['assay_values']);
    $site_arr[$site_row['id']]  = $site_row;
}
#########获取已经下达的站点
$old_cy_site_arr    = [];
$old_cy_site_sql    = "SELECT * FROM `cy_rec` WHERE  {$cy_sql_where_str} AND `fzx_id`='{$fzx_id}'";
$old_cy_site_query  = $DB->query($old_cy_site_sql);
while ($old_cy_site_row = $DB->fetch_assoc($old_cy_site_query)) {
    $sid    = $old_cy_site_row['sid'];
    $date_str   = date('Y年m月d日',strtotime($old_cy_site_row['cy_date']));
    $old_cy_site_arr[$date_str][$sid]  = $sid;
}
##################获取对应日期下的监测计划
$sql = "SELECT * FROM `cy_jh` WHERE {$sql_where_str}";
$query = $DB->query($sql);
while($row = $DB->fetch_assoc($query)){
    if(empty($row['sid'])){
        continue;
    }
    $date_str   = date('Y年m月d日',strtotime($row['jhdate']));
    $disabled   = 'can_check';
    if(in_array($row['sid'], $old_cy_site_arr[$date_str])){
        $disabled   = 'canot_check';//已经下达过任务的站点
    }
    $row['xm_num'] = count($site_arr['assay_values']); 
    $jh_sites[$date_str][$disabled][] = $row;
}
#############################获取站点路线#######################
$group_num      = 0;
$cy_group_list  = $cy_group_site_list   = [];
$cy_group_sql   = "SELECT * FROM `site_group` AS sg 
                    INNER JOIN `sites` AS s ON sg.site_id=s.id 
                    WHERE sg.`fzx_id`='{$fzx_id}' AND sg.site_type='1'
                    ORDER BY sg.`sort`,sg.`site_sort`";//只显示常规任务的批次
$cy_group_query = $DB->query($cy_group_sql);
while ($cy_group_row = $DB->fetch_assoc($cy_group_query)) {
    if(!in_array($cy_group_row['group_name'], $cy_group_list)){
        $group_num++;
        $group_mark   = $group_num;
        $cy_group_list[$group_num]      = $cy_group_row['group_name'];
    }else{
        $group_mark   = array_search($cy_group_row['group_name'], $cy_group_list);
    }
    $cy_group_row['xm_num'] = count(explode(',',$cy_group_row['assay_values'])); 
    $cy_group_site_list[$group_mark][]  = $cy_group_row;
}
#############################获取所有的站点######################
$type_site_html = '';
######查出全部站点类型
$site_type_list = $st_sid_list  = [];
$site_type_sql  = $DB->query("SELECT `st`.*,`str`.sid,`str`.`stid` 
                            FROM `site_type` AS `st` 
                            LEFT JOIN `site_type_record` AS `str` ON `st`.`id`=`str`.`stid` 
                            WHERE `st`.fzx_id='{$fzx_id}'");
while ($site_type_row = $DB->fetch_assoc($site_type_sql)) {
    if($site_type_row['pid'] =='0'){//不显示树状图根基点
        //continue;
    }
    $site_type_id   = $site_type_row['id'];//站点类型id
    $sid            = $site_type_row['sid'];//站点id
    $site_type_list[$site_type_id] = $site_type_row;
    if(!empty($sid)){
        $st_sid_list[$sid][$site_type_id]  = $site_type_id;
    }
}

######统计出一级任务类型id、站点类型数组 arr[子级id]=>[所有父级id集合]
$parent_id_list = $stype = [];
foreach ($site_type_list as $value_type_list) {
    $type_id    = $value_type_list['id'];
    //取出站点类型数组 arr[子级id]=>[所有父级id集合]
    if($value_type_list['pid'] == '0'){
        $stype[$type_id] = [];
    }else{
        $stype[$type_id] = get_st_pid($type_id, array(), $site_type_list);
    }
    $stype[$type_id] = implode(',',$stype[$type_id]);
    //统计出一级任务类型id
    $parent_id  = get_parent_id($site_type_list,$type_id);
    $parent_id_list[$type_id] = $parent_id;
}
######查出全部站点并根据站点类型分组
$st_site_list   = [];
//$site_sql   = $DB->query("SELECT * FROM `sites` WHERE `act`='1' AND `fzx_id`='{$fzx_id}'");
//while ($site_row = $DB->fetch_assoc($site_sql)) {
foreach ($site_arr as $site_row) {
    $sid    = $site_row['id'];
    //今日新添加站点,优先显示
    if(!array_key_exists($sid, $st_sid_list)){
        $st_site_list['未分配站点类型的站点'][$sid]    = $site_row;
        $site_type_list['未分配站点类型的站点']['name']= '未分配站点类型的站点';
    }else{
        foreach ($st_sid_list[$sid] as $value_st_id) {
            $parent_id  = $parent_id_list[$value_st_id];//只统计一级站点类型
            $st_site_list[$parent_id][$sid]   = $site_row;
        }
    }
}
#####组装成html显示到页面
krsort($st_site_list);
$type_site_html = [];
foreach ($st_site_list as $key_st_id => $value_site_list) {
    $site_num   = count($value_site_list);
    //站点类型显示
    $type_site_html[$key_st_id] .= "<fieldset class='site_box'><legend><BLINK> {$site_type_list[$key_st_id]['name']} (<span class='site_num'>{$site_num}</span>):</BLINK> </legend>";
    foreach ($value_site_list as $key_sid => $value_site) {
        $st_id_str  = [];
        foreach ($st_sid_list[$key_sid] as $key => $value) {
            if(stristr($stype[$value], ",$key_st_id,")){
                $st_id_str[]  = $stype[$value];
            }
        }
        $st_id_str  = ",".implode(',', $st_id_str).",";//该站点在该一级任务类型下所有所属类型
        //站点显示
        $site_xm_num    = count($site_arr[$key_sid]['assay_values']);
        $type_site_html[$key_st_id] .= "<div class='col-xs-1 site-div' title='{$value_site_list[$key_sid]['site_name']}' stid='{$st_id_str}' search='yes'>
                                            <div class='label_div'>
                                                <input type='checkbox' name='site_id' value='{$key_sid}' />
                                                <span class='site_name'>{$value_site_list[$key_sid]['site_name']}</span>
                                            </div>
                                            <span class='modi_button' title='修改站点项目' sid='{$key_sid}'>[{$site_xm_num}]</span>
                                        </div>";
        /*$type_site_html[$key_st_id] .= "<label class=\"btn btn-white\" title=\"{$value_site_list[$key_sid]['site_name']}\" title=\"未下达任务的站点\" >
                                <input type=\"checkbox\" name=\"sid[]\" value=\"{$key_sid}\" />
                                <span class=\"site\">{$value_site_list[$key_sid]['site_name']}</span>
                                <a href=\"javascript:;\">[{$site_xm_num}]</a>
                            </label>";*/
    }
    $type_site_html[$key_st_id] .= "</fieldset>";
}
$type_site_html = implode('', $type_site_html);
############显示任务树
//数据处理
$all_node = all_site_type_data();
if(count($all_node)){
    $tree = treeArray($all_node,0);
    $zNodes= json_encode($tree[0]);
}else{
    $zNodes= json_encode(['name'=>'站点标签设置','id'=>1]);
}


$lims = new DefaultApp();
$lims->disp('xd_cyrw/xd_cyrw');//变量必须用{}括起来，//可以在里面写循环，详情见html文件
//传入年月、传出对应年月的天数下拉菜单
function get_select_day($year,$month,$selected_day='',$all=''){
    if( !isset($year) || !intval($year) ){
        $year = date('Y');
    }
    if( !isset($month) || !intval($month) ){
        $month = date('m');
    }
    $now_date   = "$year-$month-01";
    $day_num    = date('d', strtotime("$now_date +1 month -1 day"));//最大天数
    $day_select = "<select name='day'>";
    if($all == '全部'){
        $day_select .= "<option value='全部'>全部</option>";
    }
    for ($i=1; $i < $day_num; $i++) {
        if(strlen($i) == '1'){
            $day    = '0'.$i;
        }else{
            $day    = $i;
        }
        $selected   = ($day == $selected_day)?'selected':'';
        $day_select .= "<option value='{$day}' {$selected}>{$day}</option>";
    }
    $day_select .= "</select>";
    return $day_select;
}
//获取父级id
function get_parent_id($parent_id_list,$child_id){
    $parent_id  = $parent_id_list[$child_id]['pid'];//pid
    $grand_pid  = $parent_id_list[$parent_id]['pid'];//父级的pid
    if(empty($grand_pid)){
        return $child_id;
    }else{
        $parent_id  = get_parent_id($parent_id_list,$parent_id);
        return $parent_id;
    }
}
//取出站点类型数组 arr[子级id]=>[所有父级id]
function get_st_pid($id, $pids, $site_type){
    $pid = $site_type[$id]['pid'];
    if($pid=='0'){
        $pids[] = $id;
    }else{
        $pids[] = $id;
        $pids = get_st_pid($pid, $pids, $site_type);
    }
    return $pids;
}