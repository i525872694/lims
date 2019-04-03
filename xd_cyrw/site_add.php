<?php
/**
 * 功能：下达采样任务页面上点击 "添加新批次按钮"加载的页面
 * 描述
*/
include("../temp/config.php");
include("$rootdir/inc/Pinyin.php");

require_once $rootdir."/system_settings/site_type_set/func.php";
$fzx_id = $u['fzx_id'];
//导航栏
$trade_global['daohang'][] = array('icon'=>'','html'=>'站点管理','href'=>"site/site_manage_list.php");
$_SESSION['daohang']['site_manage_list'] = $trade_global['daohang'];
//引入
$trade_global['js'] = array(
    'lims/d3/d3.js',
    'lims/d3/d3.layout.js',
    'lims/d3/tree.js',
    'lims/jquery.fly.min.js'
);
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
$site_sql   = $DB->query("SELECT * FROM `sites` WHERE `act`='1' AND `fzx_id`='{$fzx_id}'");
while ($site_row = $DB->fetch_assoc($site_sql)) {
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
        //得到名称的中文首字母 如：天通苑 为：tty
        $fqname_py=Pinyin::getShortPinyin($value_site_list[$key_sid]['site_name']);
        $type_site_html[$key_st_id] .= "<div class='col-xs-1 site-div ' data-val='{$value_site_list[$key_sid]['site_name']} $fqname_py' stid='{$st_id_str}' search='yes'>
                                            <div class='label_div'>
                                                <input type='checkbox' name='site_id' value='{$key_sid}' />
                                                <span class='site_name'>{$value_site_list[$key_sid]['site_name']}</span>
                                            </div>
                                        </div>";
    }
    $type_site_html[$key_st_id] .= "</fieldset>";
}
$type_site_html = implode('', $type_site_html);
echo temp('xd_cyrw/site_add.html');
############显示任务树
//数据处理
$all_node = all_site_type_data();
if(count($all_node)){
    $tree = treeArray($all_node,0);
    $zNodes= json_encode($tree[0]);
}else{
    $zNodes= json_encode(['name'=>'站点标签设置','id'=>1]);
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
?>

