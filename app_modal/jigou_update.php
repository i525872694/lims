<?php
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//删除文件
if($_GET['ajax']=='del_file'){
    $fid=$_GET['file_id'];
    $info=$DB->fetch_one_assoc("select `id`,`files` from `hub_info` where `id`='$_GET[id]'");
    $files=json_decode($info['files'],true);
    if(is_array($files)){
        //删除文件并修改数据库
        $path=__DIR__.'/upload_file';
        $file=$path.'/'.$files[$fid]['newname'];
        @unlink($file);
        unset($files[$fid]);
        $files=array_values($files);
        $file_json=json_encode($files,JSON_UNESCAPED_UNICODE);
        $DB->query("update `hub_info` set `files`='$file_json' where `id`='$_GET[id]'");
    }
    echo 'ok';
    exit;
}
if($_POST){
    $id=$_POST[id];
    if($id!=$u[fzx_id]){
        echo "<script>location.href='jigou_list.php'</script>";
        exit;
    }
    $path=__DIR__.'/upload_file';
    //文件夹不存在则创建
    if(!file_exists($path)) {  
        mkdir($path,0777,true);  
    }
    $info=$DB->fetch_one_assoc("select `files` from `hub_info` where `id`='$id'");
    $file_arr=json_decode($info['files'],true);
    $arr_i=count($file_arr)>0?count($file_arr):0;
    $i=0;
    //循环处理文件
    foreach($_FILES['file']['name'] as $file_key=>$file_v){
        $sFileName = $_FILES['file']['name'][$i];
        $uploaded_file=$_FILES['file']['tmp_name'][$i];
        //保存文件
        $date=date('Y-m-d_H-i-s-');  
        $new_file_name=$date.rand(1,1000).substr($sFileName,strrpos($sFileName,"."));
        $move_to_file=$path."/".$new_file_name;
        if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
            $file_arr[$arr_i]['newname']=$new_file_name;
            $file_arr[$arr_i]['oldname']=$sFileName;
        }
        $i++;
    }
    $files=json_encode($file_arr,JSON_UNESCAPED_UNICODE);
    //存入数据库
    $sql="update `hub_info` set `hub_name`='$_POST[hub_name]',`Address`='$_POST[Address]',`youbian`='$_POST[youbian]',`Phone`='$_POST[Phone]',`fax`='$_POST[fax]',`email`='$_POST[email]',`jingdu`='$_POST[jingdu]',`weidu`='$_POST[weidu]',`files`='{$files}' where `id`='$_POST[id]'";
    $DB->query($sql);
    //跳转页面
    echo "<script>location.href='jigou_update.php?id=$_POST[id]'</script>";
    exit;
}else{
    $id=$_GET['id'];
    $sql="select * from `hub_info` where `id`='$id'";
    $info=$DB->fetch_one_assoc($sql);
    $file=json_decode($info['files'],true);
    $path=$rooturl.'/app_modal/upload_file';
    if(!empty($file)&&is_array($file)){
        foreach($file as $k=>$v){
            $url=$path.'/'.$v['newname'];
            $files.="<a href='$url' target='_blank' download='$v[oldname]'>$v[oldname]</a>";
            $files.="<a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a><a class='red icon-remove bigger-140' onclick='del_file($info[id],$k)' title='删除文件'></a><br/>";
        }
    }
    $lims->disp('app_modal/jigou_update');
}
?>