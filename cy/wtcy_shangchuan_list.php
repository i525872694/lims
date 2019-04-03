<?php
/**
 * 功能：委托检测采样确认单上传页
 * 作者：gongyanxiao
 * 时间：2017-07-03
**/
include '../temp/config.php';
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'委托检测采样确认单','href'=>'./cy/wtcy_list.php');
$_SESSION['daohang']['cyrw_list'] = $trade_global['daohang'];
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];//分中心id
$sql = "SELECT *  FROM cy  where fzx_id='".$fzx_id."' and site_type='3' and json not like '%wtcy_qrd%'";
if( !isset($_GET['site_type']) )
    $_GET['site_type'] = "全部" ;
if( $_GET["site_type"] != "全部" ) {
    $sql .= " AND site_type ={$_GET['site_type']}";
}
if( !$_GET['cy_date'] ){
    $_GET['cy_date'] = date( "Y-m" );
}
if( !$_GET['year'] ){
    $_GET["year"] = date( "Y" );
}
if( !$_GET['month'] ){
    $_GET["month"] = date( "m" );
}
$sql .= "  AND cy_date LIKE '{$_GET['cy_date']}%' ORDER BY cy.cy_date ASC";
//echo $sql;
$res = $DB->query( $sql );
$result = array();
while( $row = $DB->fetch_assoc($res) )
{
	if($row['json']!=''){
        $cy_json   = json_decode($row['json'],true);
    }else{
        $cy_json   = array();
    }
    $row["site_total"] = count( elementsToArray( $row["sites"] ) );
    $row['status_text'] = $global['status'][$row['status']];
    if(!empty($cy_json['退回']) && empty($row['sh_user_qz'])){//这里是被退回的采样单
    	if(!empty($row['cy_user_qz']) || !empty($row['cy_user_qz2'])){
    		$row['status_text']     = '<font color=red>退回任务已签字</font>';
    	}else{
        	$row['status_text']     = '<font color=red>采样记录被退回</font>';
    	}
    }
    $result[] = $row;
}
foreach($result as $key=>$data)
{
	$i = $key+1;
	if(!empty($data['cy_user'])&&!empty($data['cy_user2'])){
		$cy_users=$data['cy_user'].' 、'.$data['cy_user2'];
	}else{
		$cy_users=$data['cy_user'].$data['cy_user2'];	
	}
	//委托任务不显示批名
	if($data['site_type']=='3'){
		$data['group_name'] = '委托任务（真实名称已隐藏）';
	}
	$cyd_bh="<a href=\"site_code.php?cyd_id={$data[id]}\">{$data[cyd_bh]}</a>";
	//让admin可以方便的看到cyd_id，方便维护
	$cyd_id	= '';
	if($u['admin'] == '1' && $show_zt != '演示'){
		$cyd_id	= "<font color='#D88376'>(id:{$data[id]})</font>";
	}
	$lines.=temp("cy/wtcy_shangchuan_list_line.html");
}
//所有年
$year_data[] = $_GET["year"];
for( $i = date('Y'); $i >= 2005; $i-- )
    if( $i != $_GET['year'] ) 
        $year_data[] = $i;

$month_data[] = $_GET["month"];

$year_list = disp_options( $year_data );
//所有月
if($rs_month['m']){
	$month_max	= $rs_month['m'];
}else{
	$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
}
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );
disp("cy/wtcy_shangchuan_list.html");

?>
