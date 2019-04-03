<?php

if($_POST['action'] == 'fwjs'){
	$nongdu = $_POST['nongdu'];
	$buqueding = $_POST['buqueding'];
	$buqueding=preg_replace('/([\x80-\xff]*)/i','',$buqueding);//过滤中文数据
	$zhengze = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\'|\`|\-|\=|\\\|\|/";//过滤特殊符号
	$buqueding=preg_replace($zhengze,"",$buqueding);
	$fanwei = '';
	if($nongdu&&$buqueding){
		$danwei = preg_replace("/\d/","",$nongdu);
		$danwei = preg_replace("/\./","",$danwei);
		if(stristr($buqueding,'%')){
			$xiao = ((double)$nongdu-(double)$nongdu*(double)$buqueding*0.01);
			$da = ((double)$nongdu+(double)$nongdu*(double)$buqueding*0.01);
			$fanwei = $xiao.'~'.$da.$danwei;
		}else{
			$xiao = ((double)$nongdu-(double)$buqueding);
			$da = ((double)$nongdu+(double)$buqueding);
			$fanwei = $xiao.'~'.$da.$danwei;
		}
	}
	if($fanwei){
		echo $fanwei;
	}else{
		echo 'wrong';
	}
}