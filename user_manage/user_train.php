<?php
include("../temp/config.php");

//删除文件
if($_GET['ajax']=='del_file'){
    $fid=$_GET['file_id'];
    $info=$DB->fetch_one_assoc("select `id`,`file` from `user_train` where `id`='$_GET[id]'");
    $files=json_decode($info['file'],true);
    if(is_array($files)){
        //删除文件并修改数据库
        $path=__DIR__.'/../app_modal/upload_file';
        $file=$path.'/'.$files[$fid]['newname'];
        @unlink($file);
        unset($files[$fid]);
        $files=array_values($files);
        $file_json=json_encode($files,JSON_UNESCAPED_UNICODE);
        $DB->query("update `user_train` set `file`='$file_json' where `id`='$_GET[id]'");
    }
    echo 'ok';
    exit;
}
//删除
if($_GET['ajax']=='del'){
    //根据接收的参数判断是单删还是多删
    if($_GET['id']){
        $id=$_GET['id'];
        $info=$DB->fetch_one_assoc("select `id`,`file` from `user_train` where `id`='$id'");
        $files=json_decode($info['file'],true);
        if(is_array($files)){
            $path=__DIR__.'/../app_modal/upload_file';
            foreach($files as $file_k=>$file_v){
                echo $file=$path.'/'.$file_v['newname'];
                @unlink($file);
            }
        }
        $sql="delete from `user_train` where `id` =$id";
    }
    if($_GET['ids']){
        $ids=implode(',',$_GET['ids']);
        //循环删除文件
        foreach($_GET['ids'] as $k=>$v){
            $info=$DB->fetch_one_assoc("select `id`,`file` from `user_train` where `id`='$v'");
            $files=json_decode($info['file'],true);
            if(is_array($files)){
                $path=__DIR__.'/../app_modal/upload_file';
                foreach($files as $file_k=>$file_v){
                    echo $file=$path.'/'.$file_v['newname'];
                    @unlink($file);
                }
            }
        }
        $sql="delete from `user_train` where `id` in($ids)";
    }
    $DB->query($sql);
    echo 'ok';
    exit;
}
//修改
if($_GET['ajax']=='edit'){
    $field=$_GET['field'];
    $value=$_GET['value'];
    if($field=='time'){
        $value[$field]=date('Y-m-d H:i:s',strtotime($value[$field]));
    }
    $status=$DB->query("update `user_train` set `$field`='$value[$field]' where `id`='$value[id]'");
    if($status){
        echo 'ok';
    }else{
        echo 'error';
    }
    exit;
}
//添加
if($_POST){
    //处理接收的数据
    $id=$_POST['id'];
    $name=trim($_POST['name']);
    $time=$_POST['time'];
    $didian=trim($_POST['didian']);
    $content=trim($_POST['content']);
    //报告编号
    $bh_time=date('Ym');
    $bh=generate_bh($bh_time);
    $path=__DIR__.'/../app_modal/upload_file';
    //文件夹不存在则创建
    if(!file_exists($path)) {  
        mkdir($path,0755,true);  
    }
    if($id){
        $info=$DB->fetch_one_assoc("select * from `user_train` where `id`='$id'");
        $file_arr=json_decode($info['file'],true);
        $arr_i=count($file_arr)>0?count($file_arr):0;
    }else{
        $arr_i=0;
    }
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
    /*for($ii=0;$ii<50;$ii++){
        $bh=$bh+1;
        $sql="insert into `user_train`(`bh`,`name`,`time`,`didian`,`content`,`file`) values('$bh','$name','$time','$didian','$content','$files')";
        $DB->query($sql);
    }*/
    if($id){
        $sql="update `user_train` set `file`='$files' where `id`='$id'";
    }else{
        $sql="insert into `user_train`(`bh`,`name`,`time`,`didian`,`content`,`file`) values('$bh','$name','$time','$didian','$content','$files')";
    }
    $DB->query($sql);
    echo "ok";
    exit;
}

//第一次使用创建表 
$sql="CREATE TABLE IF NOT EXISTS `user_train` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `bh` varchar(50) NOT NULL COMMENT '编号',
    `name` varchar(100) NOT NULL COMMENT '培训名称',
    `time` varchar(50) NOT NULL COMMENT '培训时间',
    `didian` varchar(50) NOT NULL COMMENT '培训地点',
    `content` varchar(100) NOT NULL COMMENT '培训目的及参加人员',
    `file` text COMMENT '文件地址',
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='人员培训表' AUTO_INCREMENT=1 ;";
$DB->query($sql);


$sql="select * from `user_train` order by `id` asc";
$query=$DB->query($sql);
$i=1;
while($row=$DB->fetch_assoc($query)){
    $row['xuhao']=$i;
    $row['files']='';
    if($row['file']){
        $file=stripslashes(html_entity_decode($row['file']));
        $file=json_decode($file,true);
        foreach($file as $f_k=>$f_v){
            $url=$rooturl.'/app_modal/upload_file/'.$f_v['newname'];
            $row['files'].="<span><a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a><a class='red icon-remove bigger-140' onclick='del_file($row[id],$f_k)' title='删除文件'></a>".'<br/></span>';
        }
    }
    //$row['bh'].="&nbsp;&nbsp;<a class='red icon-remove bigger-140' onclick='train_del($row[id])'></a>";
    $row['files'].="&nbsp;&nbsp;<a class='glyphicon glyphicon-cloud-upload' onclick='add_file($row[id])'></a>";
    $row['operation']="<a class='red icon-remove bigger-140' onclick='train_del($row[id])'></a>";
    $data[]=$row;
    $i++;
}
$data=json_encode($data,JSON_UNESCAPED_UNICODE);
if($_GET['ajax']=='data'){
    header('Content-type: application/json');
    echo $data;
    exit;
}

//查询编号
function generate_bh($bh_time){
    global $DB;
    $sql="select `bh` from `user_train` where `bh` like '%$bh_time%'";
    $query=$DB->query($sql);
    while($row=$DB->fetch_assoc($query)){
        $row['bh'];
        $arr[]=$row['bh'];
    }
    if($arr){
        $bh=max($arr)+1;
        return $bh;
    }else{
        return $bh_time.'001';
    }
}

$time=date('Y-m-d H:i');
disp('user_manager/user_train');
?>