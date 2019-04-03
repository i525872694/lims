<?php
include "../../temp/config.php";
//导航
//$trade_global['daohang']	= array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),array('icon'=>'','html'=>'检测能力统计表','href'=>$current_url));
$trade_global['daohang'][]	= array('icon'=>'','html'=>'检测能力统计列表','href'=>$current_url);
$_SESSION['daohang']['jcnl_tj_list']	= $trade_global['daohang'];
$fzx_id=FZX_ID;
$sql = "SELECT * FROM `hub_info` WHERE 1 ";
$query = $DB->query($sql);
$i = 0;
$lines = '';
while ($row = $DB->fetch_assoc($query)) {
	$i++;
	$xm_total = $DB->num_rows($DB->query("SELECT distinct xmid FROM `xmfa` WHERE `act`='1'and `fzx_id`='{$row['id']}' "));
	$fa_total = $DB->num_rows($DB->query("SELECT distinct fangfa FROM `xmfa` WHERE `act`='1'and `fzx_id`='{$row['id']}' "));
	$row['hub_name'] = str_replace(array('监测中心','分中心'),array('监测中心<strong>','</strong>分中心'), $row['hub_name']);
	$lines .= temp('jcnl_tj/jcnl_tj_list_line');
}
disp('jcnl_tj/jcnl_tj_list');
