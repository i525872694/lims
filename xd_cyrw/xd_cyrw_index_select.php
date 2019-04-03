<?php
/**
 * 功能：下达采样任务页面
 * 作者：韩枫
 * 日期：2014-04-21
 * 描述
*/
include("../temp/config.php");
//导航
$daohang    = array('icon'=>'','html'=>'开发测试','href'=>$_SESSION['url_stack'][0]);
$trade_global['daohang']['xd_cyrw_index_select']= $daohang;
$trade_global['js'] = array('jquery.date_input.js','lims/selectToGroup.js');
$trade_global['css'] = array('date_input.css','lims/selectToGroup_2.0.css');
//登陆及权限判断
if($u['xd_cy_rw']!='1'){
    //跳转到登陆页
    echo "没有权限";
    exit;
}
$fzx_id         = $u['fzx_id'];
$cy_date        = '';//date('Y-m-d');
$fp_id      = $_GET['fzx_id'];
$tjcs       = $_GET['tjcs'];
$xdcy_title = '下达采样任务';
$disabled   = 'disabled=disabled';
$group_name = '';
$site_type  = !empty($_GET['site_type'])?$_GET['site_type']:'1';
#########各中心设置的现场检测项目
$xcjc_value     = $DB->fetch_one_assoc("SELECT  module_value1 FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='xcjc_value' order by id desc limit 1");
$xcjc_value_arr = array_filter(@explode(',',$xcjc_value['module_value1']));
#########取出相同"任务类型"的上一批次任务所做的现场检测项目
$old_xcjc_value = array();
$cy_last    = $DB->fetch_one_assoc("SELECT id FROM `cy` WHERE site_type='".$site_type."' AND fzx_id='".$fzx_id."' ORDER BY id DESC LIMIT 1 ");
$sql_old_xcjc_value     = $DB->query("select vid from `assay_pay`  where fzx_id='".$fzx_id."' AND is_xcjc='1' AND cyd_id='".$cy_last['id']."' ");
while($rs_old_xcjc_value= $DB->fetch_assoc($sql_old_xcjc_value)){
    $old_xcjc_value[]       = $rs_old_xcjc_value['vid'];
}
#########取出本单位检测的所有的检测项目并对应显示 现场检测项目、全程空白项目、现场平行项目
$xcjc_value_checkbox1   = $xcjc_value_checkbox = '';
$sql_xcjc_value = $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa LEFT JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' GROUP BY fa.xmid");
$qckb_value_num = $xcpx_value_num   = $xcjc_value_num   = 0;//可检测项目数量改成不检测项目数量
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
    //默认现场检测项目
    if($rs_xcjc_value['is_xcjc']=='1' && (empty($xcjc_value_arr) || in_array($rs_xcjc_value['id'],$xcjc_value_arr))){
        if(in_array($rs_xcjc_value['id'],$old_xcjc_value)){
            $xcjc_value_num++;
            $xcjc_value_checkbox1   .= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}' checked>{$rs_xcjc_value['value_C']}</label>";
        }else{
            $xcjc_value_checkbox    .= "<label><input type='checkbox' name='xcjc_value[]' value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</label>";
        }
    }
}
$xcjc_value_checkbox    = $xcjc_value_checkbox1.$xcjc_value_checkbox;//把默认选中的现场检测项目放到一起
########取出所有采样员
$sql_cy_user    = $DB->query("SELECT * FROM `users` WHERE fzx_id='$fzx_id' and `group`!='0' and `group`!='测试组' and `cy`='1' ORDER BY convert(`userid` using gb2312) asc");//order by userid DESC
//如果获得了采样单的id
$sid_arr=array();
$xcpx_sid_arr=array();
if($_GET['cyd_id']){
    $cy_sql="SELECT cy_user,cy_user2,cy_date,sites,snkb FROM `cy` WHERE id='".$_GET['cyd_id']."'";
    $cy_rs=$DB->fetch_one_assoc($cy_sql);
    $site_str='';
    $cy_user=$cy_rs['cy_user'];
    $cy_user2=$cy_rs['cy_user2'];
    $cy_date=$cy_rs['cy_date'];
    if(!empty($cy_rs['snkb'])){
        $snkb_checked="checked=checked";
    }
    $rec_rs=$DB->fetch_one_assoc("SELECT id FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND sid=0");
    if(!empty($rec_rs)){
        $qckb_checked="checked=checked";
    }

    $rec_sql="SELECT * FROM `cy_rec` WHERE cyd_id='".$_GET['cyd_id']."' AND sid>0";
    $rec_query=$DB->query($rec_sql);
    while($rec_rs=$DB->fetch_assoc($rec_query)){
        if(!in_array($rec_rs['sid'],$sid_arr)){
            $sid_arr[]=$rec_rs['sid'];
        }
        if($rec_rs['sid']>0&&$rec_rs['zk_flag']<0){
            $xcpx_sid_arr[]=$rec_rs['sid'];
        }
    }
    if(!empty($sid_arr)){
        $group_name_checked="checked=checked";
        $disabled='';
    }
}
$option_user    = '';
while($rs_cy_user=$DB->fetch_assoc($sql_cy_user)){
    $selected       ='';
    $selected2      ='';
    if($cy_user==$rs_cy_user['userid']&&!empty($cy_user)){
        $selected       = "selected=selected";
    }
    if($cy_user2==$rs_cy_user['userid']&&!empty($cy_user2)){
        $selected2      = "selected=selected";
    }
    $option_user   .= "<option {$selected} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
    $option_user2  .= "<option {$selected2} value='{$rs_cy_user['userid']}'>{$rs_cy_user['userid']}</option>";
}
#######取出所有的水样类型并存到数组中
$water_type_arr = array();
$water_type_sql = $DB->query("SELECT * FROM `leixing` WHERE 1");
while ($rs_water_type   = $DB->fetch_assoc($water_type_sql)) {
    $water_type_arr[$rs_water_type['id']]   = $rs_water_type['lname'];
}
//获取全部站点

$query = $DB->query("select * from `sites` where `fzx_id`='{$fzx_id}' and `act`='1'");
$type_site_html = '';
while($row = $DB->fetch_assoc($query)){
    //站点的水样类型
    $water_type_str = $water_type_arr[$row['water_type']];
    $site_value_arr = @explode(',',$row['assay_values']);
    if(!empty($row['assay_values'])){//解决没选项目时，项目数量的判断失误问题
        $site_values_num= count($site_value_arr);
        $site_value     = implode(',', $site_value_arr);
    }else{
        $site_values_num= 0;
    }
    $milieu_values = '';

    $type_site_html .= <<<EOF
    <label class="site_label" data='{$row[site_name]}' title='{{$water_type_str}}{$row['river_name']}.{$row['site_name']}$line_vertical'><input type='checkbox' class='check_sites' site_id='{$row[id]}' value=''  site_value_num='$site_values_num' xcpx_value_num='$site_values_num' qckb_value_num='{$site_values_num}'  site_value='{$site_value}' milieu_values='{$milieu_values}' xcpx_value='' qckb_value='' />{$row['site_name']}<font color="#9B9898"></font></label>
EOF;
}

if($_GET['action']=='ajax_site_old'){
    //$get_data = array('lines'=>$lines,'fp_fzx_options'=>$fp_fzx_options,'site_area_options'=>$site_area_options,'tjcs_options'=>$tjcs_options,'site_options'=>$site_options);
    //echo JSON($get_data);//
    echo $lines;
}else{
    disp("xd_cyrw_index_select.html");
}
?>
