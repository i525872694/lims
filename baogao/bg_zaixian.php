<?php
/**
 * 功能： 在线编辑处理页面
 * 作者： 罗磊
 * 日期： 2014-4-4
 * 描述：处理报告模版列表页面的操作请求 
*/



include '../temp/config.php';
	if(!empty($_POST['mbid'])){
		$mbid  =  get_int($_POST['mbid']);
	}else{
		$mbid  =  get_int($_GET['mbid']);
	}
	if(!empty($_POST['lx'])){
		$lx    =  get_int($_POST['lx']);
	}else{
		$lx    =  get_int($_GET['lx']);
	}
	$fname =  $filename = get_int($_GET['filename']);



/* $lx操作类型 1为编辑部分信息   
*   2  编辑全部模版信息
*   3  修改状态  
*   4  修改模板信息
*   5  新建模版
*/
 if($lx== 1){
    $jcbg  = bgtemp("$filename");
	echo temp('bg/bg_zx_bj');
	exit;
 
 }elseif($lx == 2){
	$sql="SELECT * FROM `report_template` WHERE `id` =$mbid";
	$rows = $DB->query($sql);
	$row = $DB->fetch_assoc($rows);
	//print_rr($row);
	//echo $row['fc'];    
	$title = $row['te_name'];     		   //
	$jcbg.='<!--|-->'.bgtemp($row['fc']);              //报告封面
	$jcbg.='<!--|-->'.bgtemp($row['bt']);              //表头信息
	$jcbg.='<!--|-->'.bgtemp($row['audit']);           //报告签名
	$jcbg.='<!--|-->'.bgtemp($row['exp']);             //报告说明
 
	disp('bg/bg_zx_bj');
    exit;
}elseif($lx == 3){

	$sql = "SELECT state FROM `report_template` WHERE `id` = $mbid"; 
	$rows = $DB->query($sql);
	$row = $DB->fetch_assoc($rows);
	//print_rr($row);
	if($row[state] == 1){
	  $state = 0;
	}else if($row[state] == 0){
	   $state = 1;
	}
	$sql = "UPDATE `report_template` SET state = $state WHERE `id` = $mbid";
	$count = $DB->query($sql);
	if($count > 0 ){	  
		echo "<script>alert('更新成功！');location.href='bg_mb_list.php'</script>";
	}else{
		echo "<script>alert('更新失败！请联系系统管理员');location.href='bg_mb_list.php'</script>";
	}
	 
   }elseif($lx == 4){
		$sql = "UPDATE `report_template` SET `te_name` = '".$_POST['mbname']."',`state` = '".$_POST['state']."',`jiego` = '".$_POST['sm_mb_order']."',`hang1` = '".$_POST['hang1']."' ,`hang2` = '".$_POST['hang2']."' WHERE `report_template`.`id` ='".$mbid."'";
		$count = $DB->query($sql);

		if($count > 0 ){	  
			echo "<script>alert('更新成功！');location.href='bg_mb_list.php'</script>";
		}else{
			echo "<script>alert('更新失败！请联系系统管理员');location.href='bg_mb_list.php'</script>";
		}
  }elseif($lx == 5){
		$sql="SELECT * FROM `report_template` WHERE `id` =$mbid";
		$rows = $DB->query($sql);
		$row = $DB->fetch_assoc($rows);
	    //print_rr($row);
		//echo $row['fc'];    

		$jcbg.="<!--|-->".bgtemp($row['bt']); 
		$jcbg.="<!--|-->".bgtemp($row['shuju']);         //数据页

		$title = $row['te_name']; 

		echo temp('bg/bg_zx_bj');
		exit;

  }else{
		echo "操作失败  请重新选择模版";
  }

?>
