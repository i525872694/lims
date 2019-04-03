<?php
/**
  * 功能：采样记录表信息的保存
  * 作者：zhengsen
  * 时间：2014-04-21
**/
include '../temp/config.php';
$cyd_id = $_POST['cyd_id'];
$cyd_bh = $_POST['cyd_bh'];
//获取是否清空签字日期的配置信息
global $global;
if( isset($global['hyd']['tuihui']['clear_sign_date']) ){
  $clear_sign_date = $global['hyd']['tuihui']['clear_sign_date'];
}else{
  $clear_sign_date = true;
}
$sql = "SELECT `sy_user_qz_date` FROM `cy` WHERE id='".$cyd_id."'";
$query = $DB->query($sql);
$sy_user_qz_date = $DB->fetch_assoc($query);
$sy_user_qz_date = reset($sy_user_qz_date);
if($clear_sign_date){
  $sy_user_qz_date = date('Y-m-d H:i:s');
}else{
  if(!isset($sy_user_qz_date) || $sy_user_qz_date[0]==0){
    $sy_user_qz_date = date('Y-m-d H:i:s');
  }
}

//更新cy表里的数据
if($cyd_id ){
  update_record( 'cy', $_POST['cyd'], "id = $cyd_id" );
}
//把现场的数据更新到assay_order表里
$pay_sql=$DB->query("select * from `assay_order` where cyd_id='{$cyd_id}'");
while($pay_arr=$DB->fetch_assoc($pay_sql)){
  $payid_arr[$pay_arr['id']]=$pay_arr['tid'];
}
if(!empty($_POST['xcjc'])){
  $jc_user_list = [];
  foreach($_POST['xcjc'] as $k=>$v){
    $DB->query("update `assay_order` set `vd0`='{$v['jcjg']}' where id='{$k}'");
    $DB->query("update `assay_pay` set `userid`='{$v['jcry']}',`td5`='{$v['jcyq']}' where id='{$payid_arr[$k]}'");
  }
}
//将水体颜色和水体状态存储到cy_rec表的json字段里
foreach($_POST['json'] as $k=>$v){
    $sql=$DB->query("select * from cy_rec where id='{$k}'");
        while($num=$DB->fetch_assoc($sql)){
      $shuiti_json_arr= json_decode($num['json'],true);
      $shuiti_json_arr['shuiti']  = $v;
      $shuiti_json  = JSON($shuiti_json_arr);
      $DB->query("update `cy_rec` set `json`='{$shuiti_json}' where id='{$k}'");
        }
}
$i = 0;

foreach( $_POST['d'] as $k=>$v) {
//对时间进行修正
  $v = my_trim($v);
  $cy_rec_data=$v;
  $cy_rec_data['cy_note']=$v['cy_note'];
  $str=array('.','。',';','：');
  $cy_rec_data['cy_time']=str_replace($str,':',$v['cy_time']);

  if($cy_rec_data['status'] == '-1'){
    $i++;
  }
  update_record( 'cy_rec', $cy_rec_data, " id = $k" );
    
}

if($_POST['sy_user_qz']){
  $cy_user_qz_qr= $DB->query("select * from cy where id='{$cyd_id}'");
  $now=$DB->fetch_array($cy_user_qz_qr);
  $DB->query("update `cy` set `sy_user_qz`='{$_POST['sy_user_qz']}',`sy_user_qz_date`='".$sy_user_qz_date."' where id='{$cyd_id}'");
}
//if($cy_user_qz)
// if($i){
//     if( $i == count( $_POST['d'] )){
//         $DB->query("UPDATE cy SET status ='-1' WHERE id = '".$cyd_id."'"); //没有有效样品采回，无需生成化验。
//  }
// }
//如果是采样人签字
if(!empty($_POST['cy_user_qz'])){
  gotourl('cy_record_qz.php?action=cy_user_qz&&cyd_bh='.$cyd_bh.'&&cyd_id='.$cyd_id);  
}
//如果是样品接收人签字
elseif(!empty($_POST['ypjs_user_qz'])){
  gotourl('cy_record_qz.php?action=ypjs_user_qz&&cyd_bh='.$cyd_bh.'&&cyd_id='.$cyd_id);
}else{
  gotourl( $_SESSION['back_url'] );
}

?>