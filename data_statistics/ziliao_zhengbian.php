<?php
/*
*功能：月统计报告查看页面
*作者：zhengsen
*时间：2015-05-15
*系统：兰州
*/
include '../temp/config.php';
$fzx_id	= $u['fzx_id'];
//获取年份
$year	= $_GET['year'];
if(empty($year)){
	$year	= date('Y');
}
//获得月份
$month	= $_GET['month'];
if(empty($month)){
	$month	= date('m');
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'资料整编','href'=>$current_url);
$_SESSION['daohang']['tjbg_month']	= $trade_global['daohang'];


disp("data_statistics/ziliao_zhengbian");
?>