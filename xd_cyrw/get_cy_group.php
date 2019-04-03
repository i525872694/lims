<?php
//获取采样计划及相关站点
include '../temp/config.php';
$fzx_id = $u['fzx_id'];
#######取出所有的水样类型并存到数组中
$water_type_arr = array();
$water_type_sql = $DB->query("SELECT * FROM `leixing` WHERE 1");
while ($rs_water_type   = $DB->fetch_assoc($water_type_sql)) {
    $water_type_arr[$rs_water_type['id']]   = $rs_water_type['lname'];
}
#############取出站点及统计属性等信息
//获取站点有没有多个垂线和层面
$sql_site_line_vertical= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' ORDER BY site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)) {
    $site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
}
$sites_arr  = $group_arr    = $group_html   = array();
$sql_sites  = $DB->query("SELECT si.*,gr.id as gr_id,gr.group_name,gr.assay_values,gr.xcpx_values,gr.qckb_values,gr.sort as gr_sort,gr.milieu_values,gr.xcpx_milieu_values,gr.qckb_milieu_values FROM `sites` AS si LEFT JOIN `site_group` AS gr ON si.id=gr.site_id  WHERE gr.fzx_id='$fzx_id' AND gr.act='1' AND (si.fzx_id='$fzx_id' OR si.fp_id='{$fzx_id}') ORDER BY gr.group_name,gr.sort,si.site_name");
while($rs_sites = $DB->fetch_assoc($sql_sites)){
    $milieu_values = $rs_sites['milieu_values'];
    $xcpx_milieu_values = $rs_sites['xcpx_milieu_values'];
    $qckb_milieu_values = $rs_sites['qckb_milieu_values'];
    //判断相同站码但水样类型不同的站点
    $line_vertical  = '';
    if(count($site_line_vertical[$rs_sites['site_code']])>1){
        $line_vertical  .= "(".$water_type_arr[$rs_sites['water_type']].")";
    }
    //判断出该站点的垂线和层面
    if(count($site_line_vertical[$rs_sites['site_code']][$rs_sites['water_type']])>1){
        $str_site_line   = $global['site_line'][$rs_sites['site_line']];
        $str_site_vertical      = $global['site_vertical'][$rs_sites['site_vertical']];
        $line_vertical  .= "(".$str_site_line.$str_site_vertical.")";
    }
    //站点的水样类型
    $water_type_str = $water_type_arr[$rs_sites['water_type']];
    $site_value_arr = @explode(',',$rs_sites['assay_values']);
    if(!empty($rs_sites['assay_values'])){//解决没选项目时，项目数量的判断失误问题
        $site_values_num= count($site_value_arr)+count(array_filter(explode(',',$milieu_values)));
        $site_value     = implode(',', $site_value_arr);
        $site_disabled  = '';
    }else{
        $site_values_num= 0;
        $site_disabled  = "disabled=disabled";//如果站点中没有检测项目，这个站点将不允许选择
    }
    //现场平行项目数量
    $xcpx_value_arr = @explode(',',$rs_sites['xcpx_values']);
    if(!empty($rs_sites['xcpx_values'])){
        $tmp_xcpx_value_arr = array_intersect($xcpx_value_arr, $site_value_arr);
        $xcpx_values_num    = count($tmp_xcpx_value_arr)+count(array_filter(explode(',',$xcpx_milieu_values)));
        $xcpx_values        = implode(',', $tmp_xcpx_value_arr);
    }else{
        $xcpx_values_num    = $site_values_num;
        $xcpx_values        = '';
    }
    //全程空白项目数量
    $qckb_value_arr = @explode(',',$rs_sites['qckb_values']);
    if(!empty($rs_sites['qckb_values'])){
        $tmp_qckb_value_arr = array_intersect($qckb_value_arr, $site_value_arr);
        $qckb_values_num    = count($tmp_qckb_value_arr)+count(array_filter(explode(',',$qckb_milieu_values)));
        $qckb_values        = implode(',', $tmp_qckb_value_arr);
    }else{
        $qckb_values_num    = $site_values_num;
        $qckb_values        = '';
    }
    if(!in_array($rs_sites['group_name'], $group_arr)){
        $group_arr[]    = $rs_sites['group_name'];
    }
    $group_key      = array_search($rs_sites['group_name'], $group_arr);
    $group_html[$group_key]   = "<tr class='site_group_tr' group_mark='{$group_key}' style=\"font-weight:bold;height:25px;cursor:pointer;\"><td style=\"background-color:#99CCFF;text-align:left;\"><label style='font-weight:bold;width:100%;'><input type='checkbox' id='group_{$group_key}' />{$rs_sites['group_name']}</label></td></tr><tr  site_group_mark='{$group_key}'><td>";
    $sites_arr[$group_key]    .= "<label title='{{$water_type_str}}{$rs_sites['river_name']}.{$rs_sites['site_name']}$line_vertical' tjcs='{$tjcs_id}' class='site_label'><input type='checkbox' class='check_sites' site_id='{$rs_sites['id']}' value='{$rs_sites['gr_id']}' site_value_num='$site_values_num' xcpx_value_num='$xcpx_values_num' milieu_values='{$milieu_values}' xcpx_milieu_values='{$xcpx_milieu_values}' qckb_milieu_values='{$qckb_milieu_values}' qckb_value_num='{$qckb_values_num}' site_value='{$site_value}' xcpx_value='{$xcpx_values}' qckb_value='{$qckb_values}' milieu_values_num='{$milieu_values_num}' />{$rs_sites['site_name']}<font color='#9B9898'>$line_vertical</font></label>";
}
##############显示每种统计类型的站点
$lines  = '';
foreach($group_html as $key=>$value){
    $lines  .= $value.$sites_arr[$key]."</td></tr>";
}
echo "<div class=\"site_content\">
                <div style=\"width:100%;margin:0 auto;margin-bottom:10px;padding-left:0px;padding-right:0px;\" class=\"widget-header header-color-blue4 center\">
                    <p class=\"center xdrw_search\">
                        任务类型:
                        <select name=\"tjcs\" id=\"tjcs\" class=\"chosen\" style=\"width:210px;\">
                            <option value=\"全部\">全部</option>
                            $tjcs_options
                        </select>
                        <input type=\"text\" name=\"\" placeholder=\"搜索站点\" class='search-input'>
                    </p>
                </div>
                <table id=\"table_site_old\">
                    {$lines}
                </table>
            </div>";
//echo $lines;
?>