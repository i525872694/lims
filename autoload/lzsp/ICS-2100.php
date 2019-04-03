<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟,溴酸盐）
*作者：zhengsen
*时间：2015-03-24
*/
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");

$xmArr= array("F-"=>'181',"F"=>'181',"CL-"=>'182',"CL"=>'182',"SO4-"=>'190',"SO4"=>'190',
	"NO3-"=>'186',"NO3"=>'186',"NO3-N"=>'186',"PO4"=>'563',"PO4-"=>'563',"NO2-N"=>'187',
	"NO2"=>"187","NO2-"=>"187");
$quzhi_zt = $bhZt ='';
$zhi = $jcxm_arr = $bar_code_arr = array();
for($i=0;$i<count($arr);$i++){
	$row = explode(' ', $arr[$i]);
	foreach ($row as $line) {
		//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
		$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($line))));
		//匹配项目名称后开始获取样品编号
		if(!empty($xmArr[$line])){
			$xm		= $xmArr[$line];
			if (!in_array($line, $jcxm_arr)) {
				$jcxm_arr[] = $line;
			}
			$bhZt	= "start";
		}
		//匹配到AMOUNT时停止获取样品编号
		if(stristr($line,"含")){
			// $bhZt  = "stop";
		}
		//开始获取编号
		if($bhZt=="start"&&match_bar($line)){
			$bar = match_bar($line);
			if (!in_array($bar, $bar_code_arr)) {
				$bar_code_arr[] = $bar;
			}
			$quzhi_zt = "start";
			$cishu = 0;
		}
		if($quzhi_zt=="start"&&stristr($line,".")){//开始获取数据 获取第6个带“.”的值包括“n.a.”
			$cishu++;
			if($cishu==4){
				if($line=="N.A."){//如果结果为'n.a.'，则默认为0.0000
					$line="0.0000";
				}
				$zhi[$xm][$bar]=$line;
				$quzhi_zt = "stop";
			}
		}
	}
}
/*if ($u['admin']) {
	print_rr($jcxm_arr);
	print_rr($bar_code_arr);
	print_rr($zhi);exit;
}*/
return $zhi;
?>
