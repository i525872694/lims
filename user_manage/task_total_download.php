<?php
/*
* 所有基于新模板的主程序(显示界面的), 除了文件名不同, 内容完全相同.
*/
include '../temp/config.php';
header("Content-type: application/octet-stream;charset=utf-8");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=人员工作量统计表.xls");
$sl = $_GET['site_type'];
$yl = $_GET['year'];
$lx = $_GET['rw_type'];
$fzx_id=$_SESSION['u']['fzx_id'];
if($u['is_zz'] =='1'){
	$user_fzx_sql = '1';
	$fzx_id = $_GET['fzx_id_'];
}else{
	$user_fzx_sql = "fzx_id='$fzx_id' ";
}
//#########导航
$daohang = array(
		array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
		array('icon'=>'','html'=>'工作量统计','href'=>'user_manage/task_total.php'),
);
$trade_global['daohang']	= $daohang;
//默认搜索 今年、全部任务性质、整个实验室的任务
if( !isset($_GET['site_type']) ){
	$_GET['site_type']	= '全部';
}
if( !$_GET['rw_type'] ){
	$_GET['rw_type']	= '实验室任务';
}
if(!$_GET["year"]){
	$_GET["year"]		= date("Y");
}
//根据筛选 整理sql条件
$person_cy_task = $person_hy_task = '';
if($_GET['rw_type'] != '实验室任务'){
	if($_GET['rw_type']=='个人任务'){
		$uname	= $u['userid'];
		//第二采样人时不行
		$person_cy_task	= " AND (cy.cy_user = '{$uname}' OR cy.cy_user2 = '{$uname}')";
		$person_hy_task	= " AND (ap.userid='{$uname}' OR ap.sign_012='{$uname}')";
	}else{
		$uname	= $_GET['rw_type'];
		$person_cy_task	= " AND (cy.cy_user = '{$uname}' OR cy.cy_user2 = '{$uname}') ";
		$person_hy_task	= " AND (ap.userid='{$uname}' OR ap.sign_012='{$uname}')";
	}
}
$site_type_str	= "";
if($_GET['site_type'] != "全部"){
	$site_type_str = " AND cy.site_type = '{$_GET['site_type']}' ";
}
//开始获取每月数据
$result	= array();
$maxmonth = ($_GET['year'] == date('Y')) ? (int)date('n')  : 12;
for( $i=1; $i <= $maxmonth; $i++ ) {
	$month		= ($i<10) ? "0$i" : $i;
	$next_month	= $i+1;
	$next_month	= ($next_month < 10) ? "0$next_month" : $next_month;
	$next_year	= $_GET['year'];
	if( $month==12 ){
		$next_year	= $_GET['year'] + 1;
		$next_month	= '01';
	}

	//每月一号是一年的第几周
	$first_week	= date('W',strtotime($_GET['year'].'-'.$month.'-01'));
	//每月最后一天是一年的第几周
	if($next_month =='01'){
		$last_week  = date('W',strtotime($_GET['year'].'-'.$month.'-31'));
	}else{
		$last_week  = date('W',strtotime($_GET['year'].'-'.$next_month.'-01') - 1);
	}
	if($next_month =='01' &&  $last_week=='1'){
		$last_week	='53';
	}
	$weeks	= array();
	if( in_array( $first_week, array(52,53) ) ) {
		$weeks[] = (int)$first_week;
		for($k=1;$k<=$last_week;$k++){
			$weeks[]	= $k;
		}
	}else {
		for( $k = (int)$first_week; $k <= $last_week; $k++ ){
			$weeks[]	= $k;
		}
	}

	$year_month	= "{$_GET['year']}-{$month}";
	//该月采样站点总数
	$sql	= "SELECT COUNT(cy_rec.id) AS cy_total FROM cy_rec
			LEFT JOIN cy ON cy.id = cy_rec.cyd_id
			WHERE cy.fzx_id='$fzx_id' and year(cy.cy_date) = '$_GET[year]' AND month(cy.cy_date) = '$month' AND cy_rec.sid >= 0 ";
	$sql	.= $person_cy_task . $site_type_str;
	$row	= $DB->fetch_one_assoc($sql);
	$result[$month]["cy_total"]	= $row["cy_total"];
	//该月每周采样站点数
	$sql	= "SELECT COUNT(cy_rec.id) AS cy_week_total FROM cy_rec
			LEFT JOIN cy ON cy.id = cy_rec.cyd_id
			WHERE cy.fzx_id='$fzx_id' and cy.cy_date >= '$_GET[year]-$month-01' AND cy.cy_date < '$next_year-$next_month-01'
			AND WEEK(cy.cy_date, 3) = __week__  AND sid >=0";
	$sql	.= $person_cy_task . $site_type_str;
	for( $j = 0; $j <= 5; $j++ ) {
		if ( !isset($weeks[$j]) ) {
			$result[$month]["cy_week_total"][$j]	= '-';
			$weeks[$j]	= '-';
			continue;
		}
		$new_sql = str_replace( '__week__', $weeks[$j], $sql );
		$row = $DB->fetch_one_assoc( $new_sql );
		$result[$month]["cy_week_total"][$j] = $row["cy_week_total"];$cy[$j][]=$row["cy_week_total"];
	}
	$result[$month]["weeks"]	= $weeks;
	//该月化验任务总数
	$sql	= "SELECT COUNT(ao.id) AS hy_total FROM assay_order ao LEFT JOIN assay_pay ap
			ON ap.id = ao.tid LEFT JOIN cy ON cy.id = ap.cyd_id
			WHERE cy.fzx_id='$fzx_id' and year(cy.cy_date) = '$_GET[year]' AND month(cy.cy_date) = '$month' AND hy_flag >= 0 AND ao.sid >= 0 " ;
	$sql	.= $person_hy_task . $site_type_str;
	$row	= $DB->fetch_one_assoc( $sql );
	$result[$month]["hy_total"] = $row["hy_total"];
	//该月每周化验任务数(本周有超过3天就算在此周里)
	$sql	= "SELECT COUNT(ao.id) AS hy_week_total FROM assay_order ao LEFT JOIN assay_pay ap
			ON ap.id = ao.tid LEFT JOIN cy ON cy.id = ap.cyd_id
			WHERE cy.fzx_id='$fzx_id' and cy.cy_date >= '$_GET[year]-$month-01' AND cy.cy_date < '$next_year-$next_month-01'
			AND WEEK(cy.cy_date, 3) = __week__ AND hy_flag >= 0 AND ao.sid >= 0";
	$sql	.= $person_hy_task . $site_type_str;
	for( $j = 0; $j <= 5; $j++ ) {
		if( $weeks[$j] == '-' ) {
			$result[$month]["hy_week_total"][$j] = '-';
			continue;
		}
		$new_sql= str_replace( '__week__', $weeks[$j], $sql );
		$row	= $DB->fetch_one_assoc( $new_sql );
		$result[$month]["hy_week_total"][$j] = $row["hy_week_total"];$hy[$j][]=$row["hy_week_total"];
	}
}
//求出各月总计任务数，及 每年每周任务数
for($i=0;$i<6;$i++){
	$sc[$i]	= @array_sum($cy[$i]);
	$sh[$i]	= @array_sum($hy[$i]);
}
$sumc	= @array_sum($sc);// 求出 采样年度的和
$sumh	= @array_sum($sh);// 求出 化验 年度的和

$_GET['site_type']	= str_replace(' ','',$_GET['site_type']);//不知为何，变量前面有空格，这里给去掉
if($_GET['site_type'] != '全部'){
	$title_site_type= $global['site_type'][$_GET['site_type']];//标题上的 任务性质提示
}
//任务性质列表
$site_type_list = disp_options( $global['site_type'] ,1,$_GET['site_type']);
$site_type_list	= "<option value='全部'>全部</option>".$site_type_list;
//年份列表
$year_data[]	= $_GET["year"];
$sql	= "SELECT DISTINCT year(cy_date) AS Y FROM cy where fzx_id='$fzx_id' ORDER BY year(cy_date) DESC";
$res	= $DB->query($sql);
while($row = $DB->fetch_assoc($res)) {
    if($row['Y'] != $_GET['year']) {
        $year_data[]	= $row['Y'];
    }
}
$year_list	= disp_options( $year_data );
//任务类型列表
$usarr	= array($u['fzx_id'] => array( $_GET['rw_type'], "实验室任务", "个人任务" ));
$R		= $DB->query("select id, userid , fzx_id from `users` where $user_fzx_sql and `group` !='0' AND `group`!='测试组' order by `userid` desc");   /*找出用户资料*/
while ( $r = $DB->fetch_assoc( $R ) ) {
	$usarr[$r['fzx_id']][]	= $r['userid'];
}
$sql = "SELECT `id`,`hub_name` FROM `hub_info`";
$re = $DB->query($sql);
while($data = $DB->fetch_assoc($re)){
	$fzx_name_arr[$data['id']] = $data['hub_name'];
}
// print_rr($fzx_name_arr);
// $rw_type_data	= array_unique( $usarr);
foreach($usarr as $key =>$value){
		$rw_type_list .= "<optgroup label='{$fzx_name_arr[$key]}'>";
	foreach($value as $k=>$v){
		$rw_type_list	.= "<option value={$v} data='{$key}'>{$v}</option>";
	}
	$rw_type_list .= "</optgroup>";
}
// print_rr($result);
//把每行数据显示出来
while( list( $month, $data ) = each( $result ) ){
	$line	.= temp('user_manager/task_total_line');
}

disp('user_manager/task_total_v_download');


?>
