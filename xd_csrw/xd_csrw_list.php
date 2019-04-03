<?php
/**
 * 功能：分配测试任务列表
 * 作者：zhengsen
 * 时间：2014-06-15
**/
include '../temp/config.php';
require_once '../inc/cy_func.php';
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'下达测试任务列表','href'=>'./xd_csrw/xd_csrw_list.php'),
);
if(!$u['userid']){
	nologin();
}
$fzx_id=$u['fzx_id'];
$sql = "SELECT * FROM cy WHERE status >= '5' ";

if( !isset($_GET['site_type']) ){
    $_GET['site_type'] = "全部" ;
}

if( $_GET['site_type'] != "全部" ){
    $sql .= " AND site_type = '".$_GET['site_type']."' ";
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

$sql .= " AND cy_date LIKE '{$_GET[cy_date]}%'  AND fzx_id='".$fzx_id."' ORDER BY `cy_date`,`id` DESC ";
$res = $DB->query( $sql );
$result = array();
$i= 0;
while( $row = $DB->fetch_assoc($res) ) {
    //无水不生成测试任务
    $recsql = $DB->query("select status from cy_rec where cyd_id = '$row[id]'");
    $sumrec = 0;$sta = 0;
    while($recsta = $DB->fetch_assoc($recsql)){
        ++$sumrec;
        if($recsta[status]==-1){
            ++$sta;
        }
    } 
    if($sta != $sumrec){
        if( $row['status'] == '5' ){  //生成化验单前
            if( $u['xd_csrw']){
                $operation="<a href='fp_csrw.php?cyd_id={$row[id]}'>分配测试任务</a>";
            }
        }else{  //已生成化验单
                $operation="<a href='fp_csrw.php?cyd_id={$row[id]}'>修改测试任务</a>|<a href='$rooturl/huayan/ahlims.php?app=pay_list&cyd_id=$row[id]&year=$_GET[year]&month=$_GET[month]'>查看化验单</a>";
        }
        // if($row['status']=='6' || $u['admin']=='1'){
        if($u['admin']=='1'){
            $return='<<'; 
        }else{
            $return=''; 
        }
        
    }else{
        $operation="<div><font color='red'>该任务所有站点无水</font></div>";
    }
    $i++;
    //让admin可以方便的看到cyd_id，方便维护
    $cyd_id = '';
    if($u['admin'] == '1' && $show_zt != '演示'){
            $cyd_id = "<font color='#D88376'>(id:{$row[id]})</font>";
    }
    $csrw_list_lines.=temp("xd_csrw_list_line.html");   

}
//获得任务类型
$site_type_list="<option value='全部'>全部</option>";
foreach($global['site_type'] as $key=>$value){
	if($_GET['site_type']=="$key"){
		$site_type_list.="<option value=".$key." selected='selected'>".$value."</option>";
	}else{
		$site_type_list.="<option value=".$key.">".$value."</option>";
	}
}

$year_data[] = $_GET["year"];
$begin_year = empty($begin_year) ? 2005:$begin_year;
if($_GET["month"] == '12'){
    $jieshu = date('Y')+1;
}else{
    $jieshu = date('Y');
}
for( $i = $jieshu; $i >= $begin_year; $i-- )
    if( $i != $_GET['year'] ) 
        $year_data[] = $i;

$month_data[] = $_GET["month"];

$year_list = disp_options( $year_data );
//所有月
$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );
disp("xd_csrw_list.html");
?>
