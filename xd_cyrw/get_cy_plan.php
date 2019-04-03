<?php
//获取采样计划及相关站点
include '../temp/config.php';
$fzx_id = $u['fzx_id'];
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
    $month = $_GET['month'];
}
if( empty($_GET['day'])){
    $day = '全部';//date('d');
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
$site_sql   = "SELECT * FROM `sites` WHERE (`fzx_id`='{$fzx_id}' OR `fp_id`='{$fzx_id}') AND `act`='1' ORDER BY id";
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
    if(empty($row['sid']) OR !array_key_exists($row['sid'], $site_arr)){
        continue;
    }
    $date_str   = date('Y年m月d日',strtotime($row['jhdate']));
    $disabled   = 'can_check';
    if(in_array($row['sid'], $old_cy_site_arr[$date_str])){
        $disabled   = 'canot_check';//已经下达过任务的站点
    }
    $row['xm_num'] = count($site_arr['assay_values']); 
    $row['site_value']  = $site_arr['assay_values'];
    $jh_sites[$date_str][$disabled][] = $row;
}
ksort($jh_sites);//按照日期排序
//生成html文件
$html   = '';
foreach ($jh_sites as $date => $sites) {
    $html   .= "<div class='row item' data-item='0'><div class='well well-sm date-div' title='点击全选/反选''>{$date}监测计划</div>";
    foreach ($sites['can_check'] as $key => $site) {
        $html   .= "<label title='{$site['sname']}' class='site_label'><input type='checkbox' class='check_sites' site_id='{$site['sid']}' value='' site_value_num='{$site['xm_num']}' xcpx_value_num='{$site['xm_num']}' qckb_value_num='{$site['xm_num']}' site_value='{$site['site_value']}' xcpx_value='' qckb_value='' />{$site['sname']}</label>";
    }
    foreach ($sites['canot_check'] as $key => $site) {
        $html   .= "<label class='btn btn-white' title='{$site['sname']}' title='已经下达的站点' disabled>
                        <input type='checkbox' name='sid[]' value='{$site['sid']}' disabled />
                        <span class='site'>{$site['sname']}</span>
                    </label>";
    }
    $html   .= "</div>";
}
###年份下拉菜单
$year_data[] = $year;
$year_max       = date('Y')+1;
for( $i = $year_max; $i >= $begin_year; $i-- ){
    if( $i != $year ){
        $year_data[] = $i;
    }
}
$year_list = disp_options( $year_data );
###月份下拉菜单
$month_max  = ($year==date('Y'))?date('n'):'12';
$month_data = [$month];
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $month ){
        $month_data[] = $month_text;
    }
}
$month_list = disp_options( array_unique($month_data) );
###天,下拉菜单
$day_select= get_select_day($year,$month,$day,'全部');//获取“天”的下拉菜单
if(empty($html)){
    $html   = "<div class='row item' data-item='0' style='text-align: center;'>本天没有监测任务</div>";
}
$html_filter    = "<div class='' style='padding:5px;background-color:#D9EDF7;'>
                <table style='max-width:900px'>
                    <tr>
                        <td>
                            采样日期：
                            年<select name='year'>{$year_list}</select>
                            月<select name='month'>{$month_list}</select>
                            日{$day_select}
                        </td>
                    </tr>
                </table>
            </div>";
echo $html   = $html_filter.'<div class="widget-body cy_jh_div"> <div class="widget-main">'.$html.'</div></div>';
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
    for ($i=1; $i <= $day_num; $i++) {
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
?>