<?php
/**
 * 功能：委托检测确认单 列表页
 * 作者：gongyanxiao
 * 时间：2014-04-15
**/
include '../temp/config.php';
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'委托检测采样确认单','href'=>'./cy/cyrw_list.php');
$_SESSION['daohang']['cyrw_list'] = $trade_global['daohang'];
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];//分中心id
$sql = "SELECT *  FROM cy  where fzx_id='".$fzx_id."' and site_type='3' and json like '%wtcy_qrd%'";
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
$res = $DB->query( $sql );
$result = array();
while( $row = $DB->fetch_assoc($res) ){
    $file_url = json_decode($row['json'],true);
    $urls  =  $file_url['wtcy_qrd']['url'];
    $file_name  =  $file_url['wtcy_qrd']['file_name'];
	if($row['json']!=''){
        $cy_json   = json_decode($row['json'],true);
    }else{
        $cy_json   = array();
    }
    $row["site_total"] = count( elementsToArray( $row["sites"] ) );
    $row['status_text'] = $global['status'][$row['status']];
    $result[] = $row;
}
foreach($result as $key=>$data)
{
	$i = $key+1;
	$file =json_decode($data['json'],true);
    $file_name  = $file['wtcy_qrd']['file_name'];
	if(!empty($data['cy_user'])&&!empty($data['cy_user2'])){
		$cy_users=$data['cy_user'].' 、'.$data['cy_user2'];
	}else{
		$cy_users=$data['cy_user'].$data['cy_user2'];	
	}
	$data['group_name'] = '委托任务（真实名称已隐藏）';
	$cyd_bh="<a href=\"site_code.php?cyd_id={$data[id]}\">{$data[cyd_bh]}</a>";
	//让admin可以方便的看到cyd_id，方便维护
	$cyd_id	= '';
	if($u['admin'] == '1' && $show_zt != '演示'){
		$cyd_id	= "<font color='#D88376'>(id:{$data[id]})</font>";
	}
	$lines.=temp("cy/wtcy_list_line.html");
}
//所有年
$year_data[] = $_GET["year"];
for( $i = date('Y'); $i >= 2005; $i-- ){
    if( $i != $_GET['year'] ){
        $year_data[] = $i;
    }
}
$month_data[] = $_GET["month"];
$year_list = disp_options( $year_data );
//所有月
$rs_month = $DB->fetch_one_assoc("SELECT month(cy_date) as m FROM `cy` WHERE `fzx_id`='$fzx_id' AND year(cy_date)='{$_GET['year']}' AND month(cy_date)>'".date('m')."' GROUP BY month(cy_date) ORDER BY month(cy_date) DESC LIMIT 1");
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
disp("cy/wtcy_list.html");
