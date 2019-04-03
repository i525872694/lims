<?php
/**
  * 功能：保存容器信息
  * 作者：zhengsen
  * 时间：2014-4-11
**/
include '../../temp/config.php';
if(empty($u['userid'])){
	nologin();
}

$fzx_id=$u['fzx_id'];
//发送数据到duijie表中
$curl_arr = array();
$curl_arr['USER_NAME'] = $_POST['user_name'];
$curl_arr['FZX_ID'] = $fzx_id;
$curl_arr['LIMS_ID'] = $_POST['id'];
$curl_arr['BOTTLE_NAME'] = $_POST['rq_name'];
$curl_arr['BOTTLE_VOLUME'] = $_POST['rq_size'].$_POST['fenlei'];
$curl_data = array();
$curl_data['action'] = 'table';
$curl_data['table'] = 'BOTTLE_DUIJIE';
$curl_data['action2'] = 'add';
//如果获得id就执行更新操作
if($_POST['id'])
{
	$curl_data['action2'] = 'update';
	if(!empty($_POST['vid']))
	{
		$vid=implode(",",$_POST['vid']);
	}
	else
	{
		$vid='';
	}
	if($_POST['fenlei']==''){
		$_POST['fenlei']='塑料瓶';
	}
	$sql="UPDATE `rq_value` SET `rq_name`='".$_POST['rq_name']."',`bcj`='".$_POST['bcj']."',`rq_size`='".$_POST['rq_size']."',`vid`='".$vid."',`mr_shu`='".$_POST['mr_shu']."',`fenlei`='".$_POST['fenlei']."' WHERE id='".$_POST['id']."'" ;
	$query=$DB->query($sql);
}
//如果没有获得id就执行插入操作
else
{
	if(!empty($_POST['vid']))
	{
		$vid=implode(",",$_POST['vid']);
	}
	else
	{
		$vid='';
	}
	if($_POST['fenlei']==''){
		$_POST['fenlei']='塑料瓶';
	}
	$sql="insert into `rq_value` (`fzx_id`,`rq_name`,`bcj`,`rq_size`,`vid`,`mr_shu`,`fenlei`) values('".$fzx_id."','".$_POST['rq_name']."','".$_POST['bcj']."','".$_POST['rq_size']."','".$vid."','".$_POST['mr_shu']."','".$_POST['fenlei']."')";
	$query=$DB->query($sql);
	$id = $DB->insert_id();
	$curl_arr['LIMS_ID'] = $id;
}
$curl_data['data'] = $curl_arr;
$data = curl_request($duijie_url.'xd_cyrw/cy_duijie_url.php',$curl_data);
echo "<script>location.href='rq_list.php'</script>";
?>
