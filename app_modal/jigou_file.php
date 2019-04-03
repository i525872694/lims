<?php
//检测机构文件页面
include '../huayan/ahlims.php';

$title='机构文件';
$id=$_GET['id'];
//删除数据
if($_GET['action']=='del'){
    $ziduan=$_GET['ziduan'];
    $info=$DB->fetch_one_assoc("select `$ziduan` from `$_GET[table]` where `id`='$_GET[t_id]'");
    $files_arr=json_decode($info[$ziduan],true);
    $files=$files_arr[$_GET['k']]['file'];
    //如果存在文件的话，循环删除文件
    if(is_array($files)){
        $path=__DIR__.'/upload_file';
        foreach($files as $file_k=>$file_v){
            $file=$path.'/'.$file_v['newname'];
            @unlink($file);
        }
    }
    unset($files_arr[$_GET['k']]);
    $file_json=json_encode($files_arr,JSON_UNESCAPED_UNICODE);
    $DB->query("update `$_GET[table]` set `$ziduan`='$file_json' where `id`='$_GET[t_id]'");
    echo 'ok';
    exit;
}
//删除文件
if($_GET['action']=='del_file'){
    $ziduan=$_GET['ziduan'];
    $info=$DB->fetch_one_assoc("select `$ziduan` from `$_GET[table]` where `id`='$_GET[id]'");
    $files_arr=json_decode($info[$ziduan],true);
    $files=$files_arr[$_GET['file_id']]['file'];
    if(is_array($files)){
        $path=__DIR__.'/upload_file';
        foreach($files as $file_k=>$file_v){
            if($file_k==$_GET['k_id']){
                $file=$path.'/'.$file_v['newname'];
                @unlink($file);
                unset($files_arr[$_GET['file_id']]['file'][$file_k]);
            }
        }
        $file_json=json_encode($files_arr,JSON_UNESCAPED_UNICODE);
        $DB->query("update `$_GET[table]` set `$ziduan`='$file_json' where `id`='$_GET[id]'");
    }
    echo 'ok';
    exit;
}
//上传and修改
if($_POST['action']=='add'||$_POST['action']=='update'){
    $id=$_POST['id'];
    $ziduan=$_POST['mysql_ziduan'];
    $mysql_table=$_POST['mysql_table'];
    $info=$DB->fetch_one_assoc("select `{$ziduan}` from `{$mysql_table}` where `id`=$id");
    $file_arr=json_decode($info[$ziduan],true);
    $arr_i=count($file_arr)>0?count($file_arr):0;
    if($_POST['action']=='update'){
        $tmp_arr=$file_arr[$_POST['file_k']]['file'];
    }
    $i=count($tmp_arr)>0?count($tmp_arr):0;
    $k_i=0;
    $path=__DIR__.'/upload_file';
    //循环处理文件
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
    $tmp_json=array();
    $tmp_json['bdname']=$_POST['bdname'];
    $tmp_json['beizhu']=$_POST['beizhu'];
    $tmp_json['file']=$tmp_arr;
    if($_POST['action']=='update'){
        $tmp_k=$_POST['file_k'];
        $file_arr[$tmp_k]=$tmp_json;
    }else{
        $file_arr[$arr_i]=$tmp_json;
    }
    $file_json=json_encode($file_arr,JSON_UNESCAPED_UNICODE);
    $sql="update `{$mysql_table}` set `{$ziduan}`='$file_json' where `id`=$id";
    $DB->query($sql);
    echo "<script>location.href='jigou_file.php?id=$id'</script>";
    exit;
}
//添加文件
if($_POST['action']=='add_file'){
    $id=$_POST['id'];
    $ziduan=$_POST['mysql_ziduan'];
    $mysql_table=$_POST['mysql_table'];
    $info=$DB->fetch_one_assoc("select `{$ziduan}` from `{$mysql_table}` where `id`=$id");
    $file_arr=json_decode($info[$ziduan],true);
    $tmp_arr=$file_arr[$file_k]['file'];
    $arr_i=count($tmp_arr)>0?count($tmp_arr):0;
    //循环处理文件
    $path=__DIR__.'/upload_file';
    $i=0;
    foreach($_FILES['file']['name'] as $file_key=>$file_v){
        $sFileName = $_FILES['file']['name'][$i];
        $uploaded_file=$_FILES['file']['tmp_name'][$i];
        //保存文件
        $date=date('Y-m-d_H-i-s-');  
        $new_file_name=$date.rand(1,1000).substr($sFileName,strrpos($sFileName,"."));
        $move_to_file=$path."/".$new_file_name;
        if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
            $tmp_arr[$arr_i]['newname']=$new_file_name;
            $tmp_arr[$arr_i]['oldname']=$sFileName;
        }
        $arr_i++;
        $i++;
    }
    $file_arr[$file_k]['file']=$tmp_arr;
    $file_json=json_encode($file_arr,JSON_UNESCAPED_UNICODE);
    $sql="update `$mysql_table` set `$ziduan`='$file_json' where `id`=$id";
    $DB->query($sql);
    echo 'ok';
    exit;
}

$mysql_table='hub_info';
$mysql_ziduan='files';
$info=$DB->fetch_one_assoc("select `$mysql_ziduan` from `$mysql_table` where `id`='$id'");
$file_arr=json_decode($info['files'],true);
$i=1;
foreach($file_arr as $k=>$v){
    $files=$caozuo='';
    $caozuo="<a class='glyphicon glyphicon-cloud-upload' onclick=\"add_file('$k')\" title='添加文件'></a> |";
    $caozuo.="<a class='green icon-edit bigger-130' onclick=\"xiugai('$k',this)\" title='修改'></a> |";
    $caozuo.="<a class='red icon-remove bigger-130' onClick=\"del('hub_info','$id','$k','$mysql_ziduan')\" title='删除'></a> ";

    
    $path=$rooturl.'/app_modal/upload_file';
    foreach($v['file'] as $f_k=>$f_v){
        $url=$path.'/'.$f_v['newname'];
        $files.="<center><a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a>";
        $files.="<a class='red icon-remove bigger-140' onclick=\"del_file('hub_info','$id','$k','$f_k','$mysql_ziduan')\" title='删除文件'></a></center>";
    }

    $files_lines.=<<<EOF
    <tr>
        <td>$i</td>
        <td class='bdname'>$v[bdname]</td>
        <td class='file'>$files</td>
        <td class='beizhu'>$v[beizhu]</td>
        <td>$caozuo</td>
    </tr>
EOF;
    $i++;
}
disp('app_modal/jigou_file');
?>