<?php
include ('../temp/config.php');
$fzx_id = FZX_ID;

if($_GET['act']=='xiuxuhao'){
	if($_GET['bdid']&&$_GET['xuhao']){
		$sql =$DB->query("update bzwz_detail set xuhao='".$_GET['xuhao']."' where id='".$_GET['bdid']."'");
		if($sql){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}
}
//提醒天数设置
if($_GET['status']=='tixing_day'){
	$day=$_GET['day'];
	//判断该分中心在数据库里数据是否存在，不存在创建，存在即修改
	$is_yes=$DB->fetch_one_assoc("select count(*) as `num` from `n_set` where `fzx_id`=$u[fzx_id] and `module_name`='bzwz_tixing'");
	$is_yes=$is_yes['num'];
	if($is_yes=='0'){
		$info=$DB->query("insert into `n_set`(`fzx_id`,`module_name`,`module_value1`,`module_value2`)values('$u[fzx_id]','bzwz_tixing','标准物质提醒天数','$day')");
	}else{
		$info=$DB->query("update `n_set` set `module_value2` = $day where `fzx_id`=$u[fzx_id] and `module_name`='bzwz_tixing'");
	}
	if($info){
		echo 1;
	}else{
		echo 0;
	}
	
}