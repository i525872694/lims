<?php
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//删除文件
if($_GET['ajax']=='del_file'){
    $fid=$_GET['file_id'];
    $info=$DB->fetch_one_assoc("select `files` from `hn_users` where `uid`='$_GET[id]'");
	$files=json_decode($info['files'],true);
    if(is_array($files)){
        //删除文件并修改数据库
        $path=__DIR__.'/upload_file';
        $file=$path.'/'.$files[$fid]['newname'];
        @unlink($file);
        unset($files[$fid]);
        $files=array_values($files);
        $file_json=json_encode($files,JSON_UNESCAPED_UNICODE);
        $DB->query("update `hn_users` set `files`='$file_json' where `uid`='$_GET[id]'");
    }
    echo 'ok';
    exit;
}
if(empty($_POST)){
	$uid=$_GET['uid'];
	$sql="SELECT * FROM `hn_users` AS `h` RIGHT JOIN `users` AS `u` ON `h`.`uid`=`u`.`id` where uid='$uid'";
	$val=$DB->fetch_one_array($sql);
	if($val['sex'] == '男'){
		$nan = 'selected';
		$nv = '';
	}else{
		$nan = '';
		$nv = 'selected';
	}
	if($dxyqcz_select=='是'){
		$select="selected='selected'";
	}else{
		$select2="selected='selected'";
	}
	$dxyqcz_select="<option value='是' $select>是</option><option value='否' $select2>否</option>";
	$file=json_decode($val['files'],true);
	$path=$rooturl.'/app_modal/upload_file';
	if(!empty($file)&&is_array($file)){
		foreach($file as $k=>$v){
			$url=$path.'/'.$v['newname'];
			$files.="<a href='$url' target='_blank' download='$v[oldname]'>$v[oldname]</a>";
			$files.="<a class='red icon-remove bigger-140' onclick='del_file($val[uid],$k)' title='删除文件'></a><br/>";
		}
	}
	$lims->disp('user_manager/usermanager_modify');
}else{
	$uid=$_POST['uid'];
	//获取表单传值
	$hn_users_arr=array('idcard','whcd','zc','dxyqcz','jsnx','glgwzsbh','jygwzsbh','pjgwzsbh','zgxz','cygwzsbh','zhiwu','gprq','lgrq','zy','cygpbh','gw','bz');
	while( list( $key, $value ) = each( $hn_users_arr ) ){
		$hn_users_sql .= "`$value`='$_POST[$value]',";
	}
	$hn_users_sql = rtrim( $hn_users_sql, ',' );
	//上传文件
	$path=__DIR__.'/upload_file';
    //文件夹不存在则创建
    if(!file_exists($path)) {  
        mkdir($path,0777,true);  
    }
    $info=$DB->fetch_one_assoc("select `files` from `hn_users` where `uid`='$uid'");
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
	//判断是增加还是修改	
	$sql="SELECT * FROM `hn_users`  WHERE uid='$uid'";
	$R=$DB->fetch_one_array($sql);
	$jid=$R['jid'];
	if($jid!=''){
		//更新
		 $R=$DB->query("update  `hn_users` set $hn_users_sql ,files='$files'  WHERE uid='$uid'");
		}
		else{
		//插入
			$R=$DB->query("insert into  `hn_users`  set $hn_users_sql ,files='$files' ");
	}
	$s_name = $_POST['s_name']; //原来的姓名
	$xingming=$_POST['xingming']; //新的姓名
	$s_sex = $_POST['s_sex'];//原来的性别
	$sex=$_POST['sex'];//新的性别
	//如果姓名或性别或职务状态 有变化 则更新users表
	if($s_name != $xingming || $s_sex != $sex){
		$users = "update users set `userid`='$xingming',`sex`='$sex' $zz_sql where id='$uid'";
		$DB->query($users);
	}
	$url=$rooturl.'/app_modal/user_list.php';
	echo '<div style="text-align:center;margin-top:200px;">修改成功,1秒后返回</div>',"<script>setTimeout('location.href=\"$url\"',1000);</script>";//$rooturl/user_manage/hn_usermanager.php
	//gotourl("$rooturl/user/hn_usermanager.php");
}
?>
