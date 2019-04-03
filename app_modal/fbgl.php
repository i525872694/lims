<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];
$trade_global['daohang'][] = array('icon'=>'','html'=>'质量监督','href'=>$current_url);

//tab 文件管理
if(empty($_GET['type'])){
	exit;
}
$type=$_GET['type'];
$tab_active=$_GET['tab']?$_GET['tab']:0;
$hide='is_hide';
$title=$_GET['title'];
$mysql_table='files';
$id = $_GET['id'];
//循环数据 查看
$info=$DB->query("select * from `files` where `fzx_id`='$u[fzx_id]' and `type`='$type'");
$i=1;
while($row = $DB->fetch_assoc($info)){
	$tid = $row['id'];
	$files=$caozuo='';
	$caozuo.="<a class='red icon-remove bigger-130' onClick=\"del('$mysql_table','$tid','$type')\" title='删除'></a> ";
	
	$path=$rooturl.'/app_modal/upload_file';
	$files = json_decode($row['data'],true);
	$files_str = '';
	if(count($files)>=1){
		foreach($files as $f_k=>$f_v){
			$url=$path.'/'.$f_v['newname'];
			$files_str.="<center><a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a></center>";
		}
	}

	$files_lines.=<<<EOF
	<tr>
		<td>$i</td>
		<td class='bdname'>$row[name]</td>
		<td class='file'>$files_str</td>
		<td class='beizhu'>$row[name2]</td>
		<td>$caozuo</td>
	</tr>
EOF;
	$i++;
}
$mysql_ziduan=$type;
$content_str='files_content'.$tab_active;
$$content_str=temp('user_manager/file');
disp('app_modal/fbgl');

?>
