<?php
/*
 *功能：修改成果月报表信息
 *作者：zhengsen
 *时间：2014-10-22
 */
include("../temp/config.php");
include INC_DIR . "cy_func.php";
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];

//ajax改变报告的模板
if($_POST['action']=='change_bg_mb'){
	if($_POST['rec_id']&&$_POST['te_id']){
		$DB->query("UPDATE report SET te_id='".$_POST['te_id']."' WHERE cy_rec_id='".$_POST['rec_id']."'");
		if(mysql_affected_rows()){
			echo 1;
		}else{
			echo 0;
		}
	}
	exit();
}

//切换报告类型时获取报告编号
if($_POST['bg_lx']&&$_POST['cy_date']&&$_POST['cyd_id']&&$_POST['year']&&!isset($_POST['bh'])){
	$report_rs=$DB->fetch_one_assoc("select * from report where bg_lx ='".$_POST['bg_lx']."' AND cyd_id='".$_POST['cyd_id']."'  order by id desc");
	if(!empty($report_rs['bg_bh'])){
		$bg_bh=(int)$report_rs['bg_bh'];
	}else{
		$report_rs2=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx = '".$_POST['bg_lx']."' AND r.cyd_id!='".$_POST['cyd_id']."' AND r.year='".$_POST['year']."' AND c.fzx_id='".$fzx_id."' order by r.bg_bh desc");
		if(!empty($report_rs2['bg_bh'])){
			$bg_bh=(int)$report_rs2['bg_bh']+1;
		}else{
			$bg_bh=1;
		}
	}
	echo $bg_bh;
	exit();
}
//手动输入编号时进行验证是否重复
if($_POST['bg_lx']&&$_POST['cy_date']&&$_POST['cyd_id']&&$_POST['year']&&$_POST['bh']){
	$_POST['bh']=(int)$_POST['bh'];
	if($_POST['action']!='bef_bh'){
		$report_rs=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx='".$_POST['bg_lx']."' AND bg_bh ='".$_POST['bh']."' AND r.cyd_id!='".$_POST['cyd_id']."' AND r.year='".$_POST['year']."'  AND c.fzx_id='".$fzx_id."' order by r.bg_bh desc");
		if(!empty($report_rs)){
			echo 1;
		}else{
			echo 0;
		}
	}else{
		$report_rs=$DB->fetch_one_assoc("select * from report r JOIN cy c ON r.cyd_id=c.id where bg_lx='".$_POST['bg_lx']."' AND bg_bh !='".$_POST['bh']."'  AND bg_bh<'".$_POST['bh']."' AND bg_bh!='' AND r.year='".$_POST['year']."' AND c.fzx_id='".$fzx_id."' order  by r.bg_bh desc");
		if(!empty($report_rs)){
			$cy_rs=$DB->fetch_one_assoc("select group_name from cy where id='".$report_rs['cyd_id']."'");
			echo "上次报告编号：".$report_rs['bg_lx'].$report_rs['bg_bh']."<br/>上次批次名称：".$cy_rs['group_name'];
		}else{
			echo "当前报告编号为本月初始编号";
		}
	}
	exit();
}

$trade_global['js']		= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']	= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');

//向report初始化报告的信息
if(!empty($_GET['cyd_id'])){
	$query=$DB->query("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
	$nums=mysql_num_rows($query);
	if(!$nums){
		$R=$DB->query("SELECT cr.*,c.cy_date,c.ys_date FROM cy c JOIN cy_rec cr ON c.id=cr.cyd_id where cyd_id='".$_GET['cyd_id']."' and zk_flag>=0 and sid>=0 ORDER BY cr.bar_code");
		while($row = $DB->fetch_assoc($R)){
			//print_rr($row);
			$max_water_type=get_water_type_max($row['water_type'],$fzx_id);
			 $temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$max_water_type."'");
			 $te_id= $temp_rs['id'];
			 
			 $DB->query(" INSERT INTO report(cyd_id,water_type,cy_rec_id,state,bg_date,te_id,tab_user)values('".$_GET['cyd_id']."','".$row['water_type']."','".$row['id']."','9',curdate(),'".$te_id."','".$u['userid']."')");  
		 }	
	}
	//查询当前cy表的进度
	$cy_status_arr=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_GET['cyd_id']."'");
	if($cy_status_arr['status']==7){
		 $DB->query("UPDATE cy SET status='8' WHERE id='".$_GET['cyd_id']."'"); 
	}
}
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'检测报告列表','href'=>'./baogao/bg_liebiao.php'),
	array('icon'=>'','html'=>'修改报告信息','href'=>'./baogao/modi_bg_message_list.php?cyd_id='.$_GET['cyd_id'].'&cy_date='.$_GET['cy_date'])
);

if($_GET['cy_date']){
	$year=date('Y',strtotime($_GET['cy_date']));
}
//查询出报告编号
$report_rs=$DB->fetch_one_assoc("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
if($report_rs['sj_date']==''||$report_rs['sj_date']=='0000-00-00'){
	$report_rs['sj_date']='';
}
if($report_rs['bg_date']==''||$report_rs['bg_date']=='0000-00-00'){
	$report_rs['bg_date']='';
}
//查询当前报告编号是否为空，如果为空在查询最大的编号然后加1
if(!empty($report_rs['bg_bh'])&&!empty($report_rs['bg_lx'])){
		$bg_lx=$report_rs['bg_lx'];
		$bg_bh=(int)$report_rs['bg_bh'];

}else{
	$bg_lx='T';
	$report_bh=$DB->fetch_one_assoc("SELECT r.bg_bh FROM  report r JOIN cy c ON r.cyd_id=c.id WHERE bg_lx = '".$bg_lx."' AND r.year='".$year."' AND c.fzx_id='".$fzx_id."' ORDER BY bg_bh DESC");
	if(!empty($report_bh)){
		$bg_bh=(int)$report_bh['bg_bh']+1;
	}else{
		$bg_bh=1;
	}
}
$bg_lx_arr=array('T','W','S','E');
$options='';
foreach($bg_lx_arr as $key=>$value){
	if($bg_lx==$value){
		$options.="<option value=".$value." selected=\"selected\">".$value."</option>";
	}else{
		$options.="<option value=".$value.">".$value."</option>";
	}
}
for( $i = date('Y'); $i >= $begin_year; $i-- ){
        $year_data[] = $i;
}
$year_list = disp_options( $year_data,0,$year );
//检验类别
$jy_lb_arr=array("监督","supervision","抽样","sample","委托","entrust");
$jy_lb_option='';
foreach($jy_lb_arr as $key=>$value){
	if($report_rs['jy_lb']==$value){
		$jy_lb_option.="<option value=".$value." selected=\"selected\">".$value."</option>";
	}else{
		$jy_lb_option.="<option value=".$value.">".$value."</option>";
	}
}
//日期类型
$date_lx_arr=array("采样日期","Take sample date","送样日期","Send sample date");
$date_lx_options='';
foreach($date_lx_arr as $key=>$value){
	if($value==$report_rs['date_lx']){
		$date_lx_options.="<option value='".$value."' selected=\"selected\">".$value."</option>";
	}else{
		$date_lx_options.="<option value='".$value."'>".$value."</option>";
	}
}
//水样类型
$lx_sql="SELECT * FROM leixing WHERE (fzx_id=0 or fzx_id='".$fzx_id."')";
$lx_query=$DB->query($lx_sql);

while($lx_rs=$DB->fetch_assoc($lx_query)){
	$lx_rs_arr[$lx_rs['id']]=$lx_rs['lname'];
}
//批准签发人职务
$qf_user_position_arr=array('&nbsp;&nbsp;&nbsp;&nbsp;负责人','技术负责人','质量负责人');
//查询当前批次的所有站点
$sql_report2="SELECT r.*,cr.site_name FROM report r LEFT JOIN cy_rec cr ON r.cy_rec_id=cr.id AND r.cyd_id=cr.cyd_id WHERE r.cyd_id='".$_GET['cyd_id']."'";
$query_report2=$DB->query($sql_report2);
while($report_rs2=$DB->fetch_assoc($query_report2)){
	$cr_id=$report_rs2['cy_rec_id'];
	$wt_options='';
	foreach($lx_rs_arr as $key=>$value){
		if($report_rs2['water_type']==$key){
			$wt_options.="<option value=".$key." selected=\"selected\">".$value."</option>";
		}else{
			$wt_options.="<option value=".$key.">".$value."</option>";
		}
	}
	//批准签发人职务列表
	$qf_options='';
	foreach($qf_user_position_arr as $key=>$value){
		if($report_rs2['qf_user_position']==$value){
			$qf_options.="<option value=".$value." selected=\"selected\">".$value."</option>";
		}else{
			$qf_options.="<option value=".$value.">".$value."</option>";
		}
	}
	$modi_bg_message_line.=temp("bg/modi_bg_message_line");
}

disp("bg/modi_bg_message_list");

?>
