<?php
require_once "../temp/config.php";
require_once __ROOTDIR__ . "/inc/site_func.php";
require_once  "../inc/cy_func.php";
//if(count($_POST['json'])>0)$json = JSON($_POST['json']);
$fzx_id	= FZX_ID;//中心
$sid = (int)$_POST['site_id'];
if( !$sid ){
    die( '非法操作' );
}
$site_info = $_POST['modi'];//准备修改的字段及内容

$site_info['jingdu'] = trans_jwd($site_info['jingdu']);
$site_info['weidu'] = trans_jwd($site_info['weidu']);
//数据发送到duijie数据库中
$curl_arr = array();
$curl_arr['STCD'] = $site_info['site_code'];
$curl_arr['STNM'] = $site_info['site_name'];
$curl_arr['PRPNM'] = $site_info['site_line'];
$curl_arr['LYNM'] = $site_info['site_vertical'];
$curl_arr['STLC'] = $site_info['site_address'];
$curl_arr['LGTD'] = $site_info['jingdu'];
$curl_arr['LTTD'] = $site_info['weidu'];
$curl_arr['BSNM'] = $site_info['area'];
$curl_arr['HNNM'] = $site_info['water_system'];
$curl_arr['RVNM'] = $site_info['river_name'];
$curl_arr['FZX_ID'] = $fzx_id;
$curl_arr['LIMS_ID'] = $sid;
$curl_data = array();
$curl_data['data'] = $curl_arr;
$curl_data['action'] = 'table';
$curl_data['table'] = 'SITE_DUIJIE';
$curl_data['action2'] = 'update';
$data = curl_request($duijie_url.'xd_cyrw/cy_duijie_url.php',$curl_data);
$site_info['syxz']  = implode(',',$_POST['syxz']);//水源限制
sort($_POST['vid']);
$site_info['assay_values']  = implode(',',$_POST['vid']);//监测项目
######################更新站点表头信息
if ( update_site_info( $sid, $site_info ) ){
   $info = "成功更新站点信息\n" ;
}
###########更新站点类型信息
$old_type_list = [];
$old_type_sql  = $DB->query("SELECT * FROM `site_type_record` WHERE `sid`='{$sid}'");
while ($old_type_row = $DB->fetch_assoc($old_type_sql)) {
    $old_type_list[]   = $old_type_row['stid'];
}
$new_type_list  = explode(',',$_POST['site_type_id']);
$delete_type_id = array_diff($old_type_list,$new_type_list);//要删除的site_type
$insert_type_id = array_diff($new_type_list,$old_type_list);//要插入的site_type
//删除取消选择的任务类型
if(count($delete_type_id) >='1'){
    $DB->query("DELETE FROM `site_type_record` WHERE sid='{$sid}' AND `stid` IN (".implode(',',$delete_type_id).")");
}
//插入新选择的任务类型
if(count($insert_type_id) >='1'){
    foreach ($insert_type_id as $value) {
        $DB->query("INSERT INTO `site_type_record` SET sid='{$sid}',`stid`='{$value}',`create_time`='".date('Y-m-d H:i:s')."'");
    }
}
######################更新rec和order表的站点名称，只有未生成报告的才进行修改。
/*$cydsql = $DB->query("SELECT cr.cyd_id FROM `cy_rec` AS cr LEFT JOIN `report` AS re ON cr.id=re.cy_rec_id where cr.sid='{$sid}' AND (re.print_status!='1' OR re.id is null)");
while($cyd = $DB->fetch_assoc($cydsql)){*/
    //站点名称里的 左上、做下、平行等特殊字符也会被覆盖掉
	/*$recup = $DB->query("update cy_rec set site_name='".$site_name."',river_name='".$river_name."' where cyd_id='".$cyd['cyd_id']."' and sid='".$sid."'");
	$ordup = $DB->query("update assay_order set site_name='".$site_name."',river_name='".$river_name."' where cyd_id='".$cyd['cyd_id']."' and sid='".$sid."'");*/
//}
goback();


?>
