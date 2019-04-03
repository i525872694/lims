<?php 
/*
*功能：获取仪器打印的数据载入到化验单中1
*作者：郑森
*时间：2016-04-17
*/
/*error_reporting(E_ALL);
ini_set('display_errors', '1'); */
//判断如果是多化验载入就不加载config.php
include_once "../temp/config.php";
include_once INC_DIR.'/cy_func.php';
include_once INC_DIR.'../autoload/autoload_func.php';
include_once INC_DIR.'/classes/PHPExcel.php';
$fzx_id=FZX_ID;//分中心id
$tid=$_GET['tid'];//化验单id
$fid=$_GET['fid'];//方法的id
$zr_vid=$vid=$_GET['vid'];//项目的id
//查看仪器载入的配置信息
$load_set_sql="SELECT r.* FROM `yq_autoload_storeroom` as r LEFT JOIN `yq_autoload_set` s ON s.storeroom_id=r.id JOIN xmfa x ON s.yq_id=x.yiqi AND s.fzx_id=x.fzx_id WHERE x.id='".$fid."' AND x.fzx_id='".$fzx_id."'";
$load_set_rs=$DB->fetch_one_assoc($load_set_sql);

if(!empty($load_set_rs)){
	$load_way=$global['load_way'][$load_set_rs['load_way']];
	$print_name=$load_set_rs['printer'];
}
//pdf载入方式
if($load_way=='pdf' && $print_name){ 
	$pid_arr=array();
	$hydpdf_query=$DB->query("SELECT `pid` FROM `hydpdf` WHERE `tid`='".$_GET['tid']."' GROUP BY pid");
	while($hydpdf_rs=$DB->fetch_assoc($hydpdf_query)){
		$pid_arr[]=$hydpdf_rs['pid'];
	}
	if(!empty($pid_arr)){
		$pid_str=implode(',',$pid_arr);
		$pid_sql="OR id in (".$pid_str.")";
		$pid_sql2="id in (".$pid_str.")";
	}
	//die($pid_sql);
	if($u['admin']){
		if($pid_sql2){
			$sql="SELECT * FROM `pdf` WHERE ".$pid_sql2;
		}else{
			$sql="SELECT * FROM `pdf` WHERE 0";
			$sql=("SELECT * FROM `pdf` WHERE (`cdate`>='".date("Y-m-d",strtotime("-30 day"))." 00:00:00' ".$pid_sql.") AND `print_name`='".$print_name."' ORDER BY id ASC");
		}
		$pdf_query = $DB->query($sql);
	}else{
		//判断打印的电脑IP和解析的文件的IP是否相同，如果相同执行载入文件
		//$ip=$_SERVER['REMOTE_ADDR']; //获取本机电脑的IP
		//$startip=substr($ip,0,strripos($ip,'.')).'.1'; //IP 第一个
		//$endip=substr($ip,0,strripos($ip,'.')).'.255'; //IP 最后一个
		//AND `ip` between INET_ATON('".$startip."') and INET_ATON('".$endip."')
		$pdf_query = $DB->query("SELECT * FROM `pdf` WHERE (`cdate`>='".date("Y-m-d",strtotime("-30 day"))." 00:00:00' ".$pid_sql.")   AND `print_name`='".$print_name."' ORDER BY id ASC");
	}
	if(!empty($fzx_id)){
		$res_fzx=$DB->fetch_one_assoc("SELECT bar_code FROM hub_info WHERE id='{$fzx_id}'")['bar_code'];
		// print_rr($res_fzx);
	}
	while($pdf_rs=$DB->fetch_assoc($pdf_query)){
		$yqdaoru_rs=$DB->fetch_one_assoc("SELECT * FROM `yqdaoru` WHERE pid ='".$pdf_rs['id']."'");
		if(!$yqdaoru_rs){
			$lujing = $global['pdf_file_way'].$pdf_rs['file'];//文件的具体路径
			$arr     = array();
			$pname   = basename($lujing);
			if(!is_dir("/tmp/pdf")){
			    mkdir("/tmp/pdf",0777);
			}
			//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
			if(!file_exists("/tmp/pdf/".$pname."s.html")){
			    exec("pdftohtml -i $lujing /tmp/pdf/$pname");
			}
			$lujing2 = "/tmp/pdf/".$pname."s.html";
			$arr     = @file($lujing2);//把文件 读取成数组
			// if($u['admin']){
			// 	print_rr($arr);
			// }
			$zhi = include($load_set_rs['load_file']);//调用载入文件
			// if($u['admin']){
			// 	print_rr($zhi);
			// }
			if(is_array($zhi) && count($zhi)){
				update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
				yqdaoru($zhi, 'vd27');
			}
		}		
	}
}
$count= zrsj($tid,$fzx_id);
//判断如果不是多化验载入就提示载入数量并跳转到化验单
if(empty($_POST['s'])){
	if($count>0){
		$msg="自动载入数据{$count}个!";
	}else{
		$msg='没有数据载入';
	}
	echo json_encode(array('error'=>'0','content'=>$msg));
}
?>
