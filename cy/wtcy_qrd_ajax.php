<?php
/* 
* 功能：委托检测确认单ajax页面
* @Author: gongyanxiao
* @Date:   2017-07-04 21:05:43
*/
include '../temp/config.php';
$fzx_id=$u['fzx_id'];
$cyd_id = $_GET['cyd_id'];
$sql = $DB->query("select * from cy where id='{$cyd_id}'");
$arr = $DB->fetch_assoc($sql);
$url_array = json_decode($arr['json'],true);
$url = $url_array['wtcy_qrd']['url'];
if ((($_FILES[$_GET['pi']]["type"] == "image/gif")|| ($_FILES[$_GET['pi']]["type"] == "image/jpeg")|| ($_FILES[$_GET['pi']]["type"] == "image/jpg")|| ($_FILES[$_GET['pi']]["type"] == "image/png")|| ($_FILES[$_GET['pi']]["type"] == "image/bmp")|| ($_FILES[$_GET['pi']]["type"] == "image/pjpeg"))){
    $extend = explode(".",$_FILES[$_GET['pi']]["name"]);
    $key = count($extend)-1;
    $ext = ".".$extend[$key];
    $newfile = time().$ext;
    $dirname=date('Ymd',time());
    $pics="./uplode/".$newfile;
    if(move_uploaded_file($_FILES[$_GET['pi']]["tmp_name"],"./uplode/".$newfile)){
       unlink($url);
       $json['wtcy_qrd']['url']=$pics;
       $json['wtcy_qrd']['file_name']=$_FILES[$_GET['pi']]["name"];
       $jsons = json_encode($json,JSON_UNESCAPED_UNICODE);
       $DB->query("update cy set json='{$jsons}' where fzx_id='{$fzx_id}' and id='{$cyd_id}'");
    } 
     echo $_FILES[$_GET['pi']]["name"].'+'.$pics;
}else{
    echo "更新失败";
}
?>