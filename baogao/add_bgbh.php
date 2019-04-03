<?php
/**
 * 修改模板信息功能（弹框显示页面）
 * zhengsen
 * 4-21
 * 处理来自模板列表请求，完成对指定模板信息修改
*/
include "../temp/config.php";
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];
//所有年
if($_GET['bg_lx']){
	$bg_lx=$_GET['bg_lx'];
}else{
	$bg_lx='T';
}
if($_GET['cy_date']){
	$year=date('Y',strtotime($_GET['cy_date']));
}
$report_rs=$DB->fetch_one_assoc("SELECT r.bg_bh FROM report r JOIN cy c ON r.cyd_id=c.id WHERE bg_lx = '".$bg_lx."' AND r.year='".$year."' AND c.fzx_id='".$fzx_id."' ORDER BY bg_bh DESC");
if(!empty($report_rs)){
	$bg_bh_arr=explode('T',$report_rs['bg_bh']);
	$bg_bh=$bg_bh_arr[1]+1;
}else{
	$bg_bh=1;
}
if($_GET['bg_lx']){
	echo $bg_bh;
	exit();
}
for( $i = date('Y'); $i >= $begin_year; $i-- ){
        $year_data[] = $i;
}
$year_list = disp_options( $year_data );
	   echo temp("bg/add_bgbh");
?>