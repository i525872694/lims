<?php
include("../temp/config.php");
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
if($_POST['cyd_id']){
	$year=$_POST['year'];
	$bh_bh= (int)$_POST['bg_bh'];
	//更新报告信息
	if(!empty($_POST['sites'])){

		foreach($_POST['sites'] as $key=>$value){
			$DB->query("UPDATE report SET water_type='".$value['water_type']."',year='".$year."',bg_lx='".$_POST['bg_lx']."',bg_bh='".$bg_bh."',lx_user='".$_POST['lx_user']."',tel='".$_POST['tel']."',sj_date='".$_POST['sj_date']."',bg_date='".$_POST['bg_date']."',date_lx='".$_POST['date_lx']."',jy_lb='".$_POST['jy_lb']."',yp_sl='".$value['yp_sl']."',jc_yj='".$value['jc_yj']."',pj_yj='".$value['pj_yj']."',yp_zt='".$value['yp_zt']."',qf_user_position='".$value['qf_user_position']."' WHERE cyd_id='".$_POST['cyd_id']."' AND cy_rec_id='".$key."'");
		}
	}
	gotourl("modi_bg_message_list.php?cyd_id=".$_POST['cyd_id']."&cy_date=".$_POST['cy_date']);

}else{
    die('没有有效验收记录，无法修改检测报告信息！');

}

?>