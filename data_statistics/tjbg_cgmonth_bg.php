<?php
/*
*功能：查看和下载常规月报统计表的信息
*作者：高龙
*时间：2016/5/6
 */
if($_GET['action']=='json'){
	$checkLogin = false;
}
include '../temp/config.php';
include './report_data_func.php';
if($u['userid']==''&&$_GET['action']!='json'){
	nologin();
}
$trade_global['daohang'][]			= array('icon'=>'','html'=>'数据统计结果','href'=>"./data_statistics/tjbg_cgmonth_bg.php?set_id={$_GET['set_id']}&action={$_GET['action']}&how_show={$_GET['how_show']}");
$_SESSION['daohang']['tjbg_cgmonth_bg']	= $trade_global['daohang'];
//error_reporting(E_ERROR | E_WARNING | E_PARSE);ini_set('display_errors', '1');
$fzx_id=$u['fzx_id'];//获取分中心的id
$action	= !empty($_GET['action'])?$_GET['action']:$_POST['action'];
//根据set_id来查询该报告所需要的配置信息
$result_set_arr	= $mb_set_arr	= [];
if($_GET['set_id']){
	$cg_rs=$DB->fetch_one_assoc("SELECT * FROM `baogao_list` WHERE id='".$_GET['set_id']."'");
	if(!empty($cg_rs['result_set'])){
		$result_set_arr	= json_decode($cg_rs['result_set'],true);
	}
	if(!empty($cg_rs['gx_set'])){
		$mb_set_arr		= json_decode($cg_rs['gx_set'],true);
	}
}
if(empty($mb_set_arr)){
	echo "<script>alert('警告：您在数据库里没有进行该表的个性设置，请联系管理员！！'); window.close();history.go(-1)</script>";
	exit();
}
//将传过来的年份和月份转化成具体的时间段
switch ($cg_rs['count_type']) {
	case '年报'://年报
		$time_start	=$result_set_arr['choose_date']['begin_date'];
		$time_end	=$result_set_arr['choose_date']['end_date'];
		break;
	case '月报'://月报
		$day1	= $result_set_arr['choose_date']['day1'];
		$day2	= $result_set_arr['choose_date']['day2'];
		switch ($day1) {
			case '月初':
				$day1	= '01';
				break;
			case '月末':
				$day1	= date('d',strtotime("{$cg_rs['year']}-{$cg_rs['month']}-01 +1 month -1 day"));
				break;
			default:
				# code...
				break;
		}
		switch ($day2) {
			case '月初':
				$day2	= '01';
				break;
			case '月末':
				$day2	= date('d',strtotime("{$cg_rs['year']}-{$cg_rs['month']}-01 +1 month -1 day"));
				break;
			default:
				# code...
				break;
		}
		if($result_set_arr['choose_date']['month_type'] == '本月'){
			$time_start = $cg_rs['year'].'-'.$cg_rs['month'].'-'.$day1;
			$time_end   = $cg_rs['year'].'-'.$cg_rs['month'].'-'.$day2;
		}else{
			if($cg_rs['month'] == '01'){
				$time_start = ($cg_rs['year']-1).'-'.'12'.'-'.$day1;
				$time_end   = $cg_rs['year'].'-'.$cg_rs['month'].'-'.$day2;
			}else{
				$smonth = $cg_rs['month'] - 1;
				if($smonth < 10){
					$smonth = '0'.$smonth;
				}
				$time_start = $cg_rs['year'].'-'.$smonth.'-'.$day1;
				$time_end   = $cg_rs['year'].'-'.$cg_rs['month'].'-'.$day2;
			}
		}
		break;
	case '周报'://周报
		$shi = array('01','03','05','07','08','10','12');
		$year  = substr($_GET['begin_date'],0,4);
		$month = substr($_GET['begin_date'],5,2);
		$day   = substr($_GET['begin_date'],8,2);
		$zhou_arr['周一'] = $_GET['begin_date'];
		$zhou2_arr = array('周二','周三','周四','周五','周六','周日');
		for($i=0;$i<6;$i++){//***
			$day = $day+1;
			if($day < 10){
				$day = '0'.$day;
			}
			if(in_array($month,$shi) || $month == '02'){
				if($month == '02'){
					if($day > 29){
						$month = '0'.($month+1);
						$day   = '01';
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
					
				}
				if(in_array($month, $shi)){
					if($day > 31){
						$month = $month + 1;
						$day   = '01';
						if($month < 10){
							$month = '0'.$month;
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
						if($month >=12){
							$year = $year + 1;
							$month = '01';
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
						if($month>=10 && $month<=11){
							$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
						}
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
					
				}
			}else{
				if($day > 30){
					$month = $month+1;
					$day   = '01';
					if($month < 10){
						$month = '0'.$month;
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}else{
						$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
					}
				}else{
					$zhou_arr[$zhou2_arr[$i]] = $year.'-'.$month.'-'.$day;
				}
			}
		}//***
		$time_start = $zhou_arr[$result_set_arr['choose_date']['week1']];
		$time_end   = $zhou_arr[$result_set_arr['choose_date']['week2']];
		break;
	case '日报'://日报
		if(!empty($result_set_arr['choose_date'])){
			$day = substr($_GET['date'],8);
			if(($day - $result_set_arr['choose_date']['before_days']) >= 1){
				$day_time = substr($_GET['date'],0,8);
				$time_start = $day_time.($day - $result_set_arr['choose_date']['before_days']);
				$time_end   = $day_time.($day - $result_set_arr['choose_date']['before_days']);
			}else{
				$time_start = $_GET['date'];
				$time_end   = $_GET['date'];
			}
		}else{
			$time_start = $_GET['date'];
			$time_end   = $_GET['date'];
		}
		break;
	default:
		# code...
		break;
}
$time_start_china	= date('Y年m月d',strtotime($time_start));
$time_end_china		= date('Y年m月d',strtotime($time_end));
//获取批次名称及批次名称下的站点id并将数组付给$_POST['sites'] //###根据站点管理优化站点选择界面后，这里直接就是sid，就不需要转换了
if(!empty($result_set_arr['alone_sites'])){
	$pmzid_arr	= [];
	$sid 		= implode(',', $result_set_arr['alone_sites']);
	$result 	= $DB->query("SELECT site_id,group_name FROM site_group WHERE id in({$sid})");
	while ($pmzid = $DB->fetch_assoc($result)) {
		$pmzid_arr[] = $pmzid['site_id']; 
	}
	$result_set_arr['sites_id'] = $pmzid_arr;
}
if(!empty($result_set_arr['all_sites'])){
	if(empty($result_set_arr['sites_id'])){
		$result_set_arr['sites_id'] = $result_set_arr['all_sites'];
	}else{
		$result_set_arr['sites_id'] = array_merge($result_set_arr['sites_id'],$result_set_arr['all_sites']);
	}
}
//现场规定时间段内采样信息，cy_rec表信息
$report_rec_list	= report_get_rec($time_start,$time_end,$result_set_arr['sites_id']);
//获取批次内 平行样和非平行样的对应关系
$px_arr = $cids = array();
foreach($report_rec_list as $k=>$v){
	if(in_array($k,array('water_type_list'))||!in_array($v['zk_flag'],array('5','-6'))){
		continue;
	}
	$cids[$v['cyd_id']][$v['sid']][$v['zk_flag']] = $v['id'];
}
foreach($cids as $cydid=>$v){
	foreach($v as $flag => $vv){
		$px_arr[$vv['5']] = $vv['-6'];
	}
}
$water_type_list	= $report_rec_list['water_type_list'];
unset($report_rec_list['water_type_list']);
$water_type_fater	= get_parent_type($water_type_list);//获取父级水样类型
$water_type_list	= array_unique(array_merge(array_keys($water_type_fater),array_values($water_type_fater)));//water_type的集合（包含父类和子类）
$jcbz_list	= get_jcbz($water_type_list);
//获取检测项目名称信息
$xm_name_list		= get_xm_name();
$cid_list			= array_keys($report_rec_list);//cid集合
$report_order_list	= report_get_order($cid_list,$result_set_arr['alone_vid']);
//获取检测结果数据
foreach($report_rec_list as $k=>$v){
	if($v['zk_flag']=='-6'){
		unset($report_rec_list[$k]);
	}
}
if(empty($report_rec_list) || empty($report_order_list)){
	echo "<script>alert('警告：您所选择的站点，并没有检测数据'); window.close();history.go(-1)</script>";
	exit();
}
//生成json数据返回
//现场项目数组
$xc_bs = array(
	'qi_wen'=>'AIRT',//气温
	'qi_ya'=>'ATM',//气压
	'liu_l'=>'Q',//流量
	'feng_su'=>'WNDV',//风速
	'feng_xiang'=>'WNDDIR',//风向
	'tian_qi'=>'WTH',//天气
	'liu_s'=>'FLWV',//流速
	'water_height'=>'Z',//水位
);
foreach($report_rec_list as $k=>$v){
	$_arr = $report_order_list[$k];
	foreach($_arr as $vid=>$vv){
		$xcpx_value = $report_order_list[$px_arr[$k]][$vid];
        if(!empty($xcpx_value['vd0'])){
			$_arr[$vid] = $xcpx_value;
		}
	}
	$_arr['SPT'] = $v['cy_date'].' '.$v['cy_time'];
	$_arr['PRPNM'] = $v['site_line'];
	$_arr['LYNM'] = $v['site_vertical'];
	$_arr['STNM'] = $v['site_name'];
	foreach($xc_bs as $xc_k=>$xc_v){
		if(array_key_exists($xc_k,$v) && !empty($v[$xc_k])){
			$_arr[$xc_v] = $v[$xc_k];
		}
	}
	$return_result_json[$v['cyd_id']][$v['site_code']] = $_arr;
}
$report_pingjia_list= [];
//超标判定
foreach($report_order_list as $cid=>$vid_report){
	$cid_water_type	= $report_rec_list[$cid]['water_type'];
	if(!empty($jcbz_list[$cid_water_type])){
		$panding_jcbz_list	= $jcbz_list[$cid_water_type];
		//以下if测试用
		if(!in_array($cid_water_type,array('1','3'))){
			$intent_quality	= $jcbz_list[$cid_water_type]['jcbz_content']['jcbz_mark'];
		}else{
			$intent_quality	= 'Ⅲ类';
		}
	}else{
		$fater_type	= $water_type_fater[$cid_water_type];
		$panding_jcbz_list	= $jcbz_list[$fater_type];//用父级水样类型的标准来判定
		//以下if测试用
		if(!in_array($fater_type,array('1','3'))){
			$intent_quality	= $jcbz_list[$fater_type]['jcbz_content']['jcbz_mark'];
		}else{
			$intent_quality	= 'Ⅲ类';
		}
	}
	foreach ($vid_report as $vid => $report_arr) {
		$report_pingjia_list[$cid][$vid]	= xm_water_quality($vid,$report_arr['vd0'],$panding_jcbz_list,$intent_quality);
		if($vid=='114'){
			//$report_order_list[$cid][$vid]['vd0'] = _round($report_order_list[$cid][$vid]['vd0'],1);
		}
		$report_order_list[$cid][$vid]['pingjia']	= $report_pingjia_list[$cid][$vid];
	}
}
//获取项目排序的设置
if($result_set_arr['xm_px_id']){
	$xm_px_rs=$DB->fetch_one_assoc("SELECT * FROM n_set WHERE id='".$result_set_arr['xm_px_id']."'");
	$xm_px_arr	= explode(",",$xm_px_rs['module_value1']);
	$xm_px_arr	= array_intersect($xm_px_arr,$result_set_arr['alone_vid']);//只显示选中的项目
	$wu_px_xm	= array_diff($result_set_arr['alone_vid'],$xm_px_arr);
	$xm_px_arr	= array_merge($xm_px_arr,$wu_px_xm);
}else{
	$xm_px_arr	= $result_set_arr['alone_vid'];
}
/*//获取计量单位
if(!empty($max_water_type)){
	$diff_str	= implode(',',$result_set_arr['alone_vid']);
	$sql_unit		= $DB->query("SELECT xmid,unit FROM `xmfa` WHERE `act`='1' AND `mr`='1' AND `lxid`='{$max_water_type}' AND `xmid` in($diff_str) group BY xmid");
	while ($rs_unit = $DB->fetch_assoc($sql_unit)) {
		$unit_arr[$rs_unit['xmid']]	= $rs_unit['unit'];	
	}
}*/
//报告有另外的php单独显示
$title_name = $result_set_arr['cgb_title'];//报告名称
if(!empty($mb_set_arr['result_php_name'])){
	if(!stristr($mb_set_arr['result_php_name'],".php")){
		$mb_set_arr['result_php_name']	.= ".php";
	}
	include($mb_set_arr['result_php_name']);
}else{
	include('result_moren_mb.php');
}
if($action=='json'){
	echo json_encode($return_result_json);
}else if($action=='see'){
	echo "<script src='{$rooturl}/js/jquery-2.1.0.js'></script>
	<script src='{$rooturl}/js/sweetalert2.js'></script>
	<link rel='stylesheet' href='{$rooturl}/css/bootstrap.min.css'>
	<link rel='stylesheet' href='{$rooturl}/css/sweetalert2.css'>
		<style>
			td.vd0_button:hover{
				color:blue;
				cursor: pointer;
				transform: scale(1.2);
				opacity: 1;
				border-color:black !important;
			}
			@media print {
				.Noprint{ display:none;}
		   }
		</style>
		<script>
			$(function(){
				$(\"td.vd0_button[tid]\").click(function(){
					var tid		= $(this).attr('tid');
					var cyd_id	= $(this).attr('cyd_id');
					if(tid){
						window.open('{$rooturl}/huayan/assay_form.php?tid='+tid);
					}else if(cyd_id){
						window.open('{$rooturl}/cy/cy_record.php?cyd_id='+cyd_id);
					}else{
						alert('无化验单');
					}
				});
			})
			function duijie(){
				$.post('duijie.php',{set_id:{$_GET[set_id]}},function(data){
					data = JSON.parse(data);
					var content = '新上传'+data.add_i+'条,更新'+data.up_i+'条';
					swal('上传成功',content,'success');
				})
			}
			function set_cols(){
				var _cols = $('#cols_num').val();
				if(_cols<13||_cols==''||_cols===undefined){
					_cols = 13;
				}
				window.location.href='tjbg_cgmonth_bg.php?cols_num='+_cols+'&action=see&set_id={$_GET[set_id]}';
			}
		</script>
		";
		if(empty($mb_set_arr['duijie_date'])){
			$str = '审核并上传至评价系统';
		}else{
			$str = date('Y-m-d H:i:s',$mb_set_arr['duijie_date']).' 已上传<br/>';
			$str .= "重新上传";
		}
		$return_result_html .= "<div style='text-align:center;background:#cccccc;position:fixed;bottom:30px;width:100%;opacity:0.8'><button class='btn btn-primary btn-sm' style='opacity:1.0' onclick='duijie()'>$str</button></div>";
		$return_result_html.='<a href="#top" style="position:fixed;bottom:0;right:0"> <img width="25" alert="返回顶部" src="../img/back-top.png"></a>';
	echo $return_result_html;
}else if($action=='xia'){
	header("Content-Type:   application/msexcel");        
	header("Content-Disposition:   attachment;   filename=".$title_name.".xls");        
	header("Pragma:   no-cache");        
	header("Expires:   0");
	echo "
		<html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\"
		xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">
		<title></title>
		<head></head>
		<body>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name></x:Name><x:WorksheetOptions><x:Selected/><x:FreezePanes/><x:FrozenNoSplit/>
	<x:SplitHorizontal>2</x:SplitHorizontal><x:TopRowBottomPane>2</x:TopRowBottomPane><x:ActivePane>2</x:ActivePane></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
		".$return_result_html."</body>
		</html>
	";
}else if($action=='chart'){
	$_POST	= $result_set_arr;
	$_POST['time_start']	= $result_set_arr['choose_date']['begin_date'];
	$_POST['time_end']		= $result_set_arr['choose_date']['end_date'];
	$_POST['how_show']		= $_GET['how_show'];
	include("../data_chart/custom_chart2.php");
    exit;
}else{
	echo "参数传递错误，请截图并联系安恒管理员！";
}

?>