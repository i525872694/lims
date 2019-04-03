<?php
include "../temp/config.php";
foreach($_SESSION['assayvalueC'] as $key=>$value){
	$xm_select.="<option value='{$value}'>$value</option>";
}
$sql = "SELECT `id` , `userid` FROM `users` WHERE `fzx_id` = '{$u['fzx_id']}' AND `group` != '0' AND `group` != '测试组'";
// echo $sql;
$re = $DB->query($sql);
while($data = $DB->fetch_assoc($re)){
	$user_select .="<option value='{$data['id']}'>{$data['userid']}</option>";
}

if($_GET['handle']=='add'){
	$value_arr = array_flip($_SESSION['assayvalueC']);
	// print_rr($_POST);
	// die;
	foreach($_POST['xmfa'] as $key=>$value){
		if(!empty($_POST['xmfa'][$key]) && !empty($_POST['limit_date'][$key])){
			$sql_xm = "INSERT INTO `xmfa` (`fzx_id` , `xmid` , `userid` , `sgz_date`) VALUES ('{$u['fzx_id']}' , '{$value_arr[$_POST['xmfa'][$key]]}' , '{$_POST['userid']['0']}' , '{$_POST['limit_date'][$key]}')";
			// echo $sql_xm;die;
			$DB->query($sql_xm);
			$fid = $DB->insert_id();
			$sql_zheng = "INSERT INTO `users_zheng` (`userid` , `fid` , `limit_date`) VALUES ('{$_POST['name']}' , '{$fid}' , '{$_POST['limit_date'][$key]}')";
			$DB->query($sql_zheng);
			// echo $sql_zheng.'<hr>';
			echo "<script>alert('添加成功!');location.href='hw_userwoke.php'</script>";
		}
		
	}
	
die;
}
disp("user_manager/hw_userwork_add_xm");