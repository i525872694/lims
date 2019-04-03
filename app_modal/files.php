<?php
//检测机构文件页面
include '../huayan/ahlims.php';
$fzx_id = $u['fzx_id'];
$id=$_GET['id'];
//删除数据
if($_GET['action']=='del'){
    if(empty($id)){
        exit;
    }
    $table = $_GET['table'];
    $info = $DB->fetch_one_assoc("select * from `$table` where `id`='$id'");
    $files = json_decode($info['data'],true);
	if(count($files)>=1){
        $path=__DIR__.'/upload_file';
        foreach($files as $file_k=>$file_v){
            $file=$path.'/'.$file_v['newname'];
            @unlink($file);
        }
    }
    $DB->query("delete from `$table` where `id`='$id'");
    echo 'ok';
    exit;
}
//上传
if($_POST['action']=='add'){
    $id=$_POST['id'];
    $ziduan=$_POST['type'];
    $mysql_table=$_POST['mysql_table'];
    $i=0;
    $k_i=0;
    $path=__DIR__.'/upload_file';
    //循环处理文件
    $tmp_arr = array();
    foreach($_FILES['file']['name'] as $file_key=>$file_v){
        $sFileName = $_FILES['file']['name'][$k_i];
        $uploaded_file=$_FILES['file']['tmp_name'][$k_i];
        //保存文件
        $date=date('Y-m-d_H-i-s-');  
        $new_file_name=$date.rand(1,1000).substr($sFileName,strrpos($sFileName,"."));
        $move_to_file=$path."/".$new_file_name;
        if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
            $tmp_arr[$i]['newname']=$new_file_name;
            $tmp_arr[$i]['oldname']=$sFileName;
        }
        $i++;$k_i++;
    }
    $datas=array();
    $datas['fzx_id']=$fzx_id;
    $datas['type']=$_POST['type'];
    $datas['name']=$_POST['bdname'];
    $datas['name2']=$_POST['beizhu'];
    $datas['data']=json_encode($tmp_arr,JSON_UNESCAPED_UNICODE);
    $datas['join_id'] = $_POST['id'];
    new_record('files',$datas);

    echo "<script>location.href='jigou_file.php?id=$id'</script>";
    exit;
}
?>