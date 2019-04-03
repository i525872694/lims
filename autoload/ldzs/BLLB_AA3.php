<?php
/*
 *功能：布朗卢比AA3流动注射仪器导入页面
 *作者：zhengsen
 *时间：2016-01-26
 */
//if($u['admin']){echo 'aaa';exit;}
$arr=array();
//include("../../temp/config.php");
//$lujing="../files/20160122104330_10.2.10.68.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr   =array('COD MN'=>'104','COD CR'=>'118','CODMN'=>'104','CODCR'=>'118');

$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>");
$zhi    = $hyxm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt=$cishu='';

//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//当匹配到特殊标识时开始获取项目
	if(stristr($line,'METH')&&empty($hyxm_arr)){
		$get_xm=1;
	}
	//当匹配到特殊标示时停止获取项目
	if(stristr($line,'SAMPLE')){
		$get_xm='';
	}
	//匹配项目名称
	if($get_xm&&$xmArr[$line]){
		if(!in_array($xmArr[$line],$hyxm_arr)){
				$jcxm_arr[]=$line;
				$hyxm_arr[]=$xmArr[$line];
		}
	}
	//获取项目结束后开始获取样品编号
	if(!empty($hyxm_arr)&&!$get_xm){
		$temp_line=explode(" ",$line);
		foreach($temp_line as $key=>$value){
			//匹配样品编号
			if(match_bar($value)){
				$bar = match_bar($value);
				if(!in_array($bar,$bar_code_arr)){
					$bar_code_arr[]=$bar;
				}
				$quzhi_zt = "start";
				$cishu=0;
				continue;
			}
			//取出数值
			if($quzhi_zt=='start'){
				//匹配到小数点后记录次数并赋值
				if(stristr($value,".")){
					$cishu++;
					$zhi['vd27'][$bar][$hyxm_arr[$cishu-1]]=$value;
					continue;
				}
				//获取小数点后面的响应值
				if($cishu&&empty($zhi['vd3'][$bar][$hyxm_arr[$cishu-1]])&&$value!=''){
					$zhi['vd3'][$bar][$hyxm_arr[$cishu-1]]=$value;
				}
				//最后一个项目赋值后初始化$cishu并关闭获取数据的状态
				if($cishu==count($hyxm_arr)&&!empty($zhi['vd3'][$bar][$hyxm_arr[$cishu-1]])){
					$cishu=0;
					$quzhi_zt='stop';
				}
			}
		}
	}
}
//print_rr($zhi);exit();
//if($u['userid']=='admin')print_rr($zhi);
if(count($zhi)){
	foreach($zhi as $zrlie=>$data){
		yqdaoru($data,$zrlie);
	}
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
}
?>
