
<?php
include "../temp/config.php";
if($_GET['id']){
    $upd = $DB->query("UPDATE `assay_method` SET method_number='".$_GET['method_number']."' WHERE id= '".$_GET['id']."'");
    $log_content = "id:".$_GET['id'].'content:'.$_GET['method_number'].'<br/>';
    file_put_contents("./method_upload.txt", $log_content, FILE_APPEND);
    if($upd){
        echo  1;
    }else{
        echo  0;
    }
}
if($_GET['ajax']==1){
$method = $DB->query("SELECT * FROM `assay_method`");
$num = $DB->num_rows($method);
$method_list = $DB->fetch_assoc($method);
$i=0;
$is=0;
$array=array();
$date = date("Y-m-d");
while($method_list = $DB->fetch_assoc($method)){
    $is++;
    $array[$i]['xh']=$is;
    $array[$i]['id']=$method_list['id'];
    $array[$i]['method_number']=$method_list['method_number'];
    $array[$i]['method_name']=$method_list['method_name'];
    $i++;
}  
    echo  json_encode($array);
}
