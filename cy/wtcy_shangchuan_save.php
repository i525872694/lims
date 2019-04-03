<?php
/**
 * 功能：委托检测采样确认单上传保存
 * 作者：gongyanxiao
 * 时间：2017-07-03
**/
include '../temp/config.php';
include "../inc/cy_func.php";
$fzx_id=$u['fzx_id'];
//文件上传
foreach($_FILES as $key=>$value){
    if(!empty($value['name'])){
		$upfile=$_FILES[$key]; 
		//获取数组里面的值 
		$name        = $upfile["name"];//上传文件的文件名 
		
		$type        = $upfile["type"];//上传文件的类型 

		$size        = $upfile["size"];//上传文件的大小 
		$tmp_name    = $upfile["tmp_name"];//上传文件的临时存放路径 
		//print_rr($_FILES);die();
		$allowedExts = array("gif", "jpeg", "jpg", "png","JPEG","GIF","JPG","PNG");
		$temp        = explode(".", $_FILES[$key]["name"]);
		$extension   = end($temp);        // 获取文件后缀名
		$newname     = date('ymdhis').".".end($temp);
		$path        = "./uplode".'/'.$newname;
		$error       = $upfile["error"];//上传后系统返回的值 
		if(!in_array($extension, $allowedExts)){
		        echo "<script>alert('请选择正确格式的文件');location.href='wtcy_shangchuan_list.php'</script>";
		        exit;
		}
		if($size>100000000000){
            echo "文件过大，上传失败";exit;
		}
		move_uploaded_file($tmp_name,$path);
		$json['wtcy_qrd']['url']=$path;
		$json['wtcy_qrd']['file_name']=$name;
        $jsons = json_encode($json,JSON_UNESCAPED_UNICODE);
		$DB->query("update cy set json='{$jsons}' where fzx_id='{$fzx_id}' and id='{$key}'");
    }
}
if($error==0){ 
	echo "<script>alert('文件上传成功');location.href='wtcy_list.php'</script>";
}else{
   echo "<script>alert('文件上传失败');location.href='wtcy_shangchuan_list.php'</script>";
}gotourl("wtcy_list.php");	
?>
