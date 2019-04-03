<?php
/*
*功能：离子色谱仪器载入页面（硝酸盐氮,硫酸盐,氯离子,氟）
*作者：zhengsen
*时间：2014-10-21
*/
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");

$xmArr= array("F-"=>'181',"F"=>'181',"氟氟氟"=>'181',"CL-"=>'182',"CL"=>'182',"氯氟氟"=>'182',"SO4-"=>'190',"SO4"=>'190',"硫亚亚"=>'190',"NO3-"=>'186',"NO3"=>'186',"NO3-N"=>'186',"亚亚亚亚"=>'186',"PO4"=>'563',"亚亚亚"=>'563',"PO4-"=>'563',"NO2-N"=>'187',"NO2"=>'187','硫硝硝'=>'190',"亚亚磷亚亚"=>"187",
	"硫磷亚"=>"190","磷磷亚"=>"563","亚磷亚"=>"186"
);
$quzhi_zt = $bhZt ='';
$zhi = $bar_code_arr = $jcxm_arr = array();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	$line = str_replace("（", " ",$line);
	$array = explode(" ", $line);
	foreach ($array as $key => $value) {
		if (empty($value)) {
			continue;
		}
		// echo $value."<br>";
		//匹配项目名称后开始获取样品编号
		if(!empty($xmArr[$value])){
			$xm		= $xmArr[$value];
			if (!in_array($value, $jcxm_arr)) {
				$jcxm_arr[] = $value;
			}
			$bhZt	= "start";
		}
		//匹配到AMOUNT时停止获取样品编号
		if(stristr($value,"AMOUNT")){
			$bhZt  = "stop";
		}
		//开始获取编号
		if($bhZt=="start"&&match_bar($value)){
			$bar = match_bar($value);
			if(isset($zhi[$xm][$bar])){//如果碰到相同的编号默认第二个为平行样
				$bar = $bar."P";
			}
			if (!in_array($bar, $bar_code_arr)) {
				$bar_code_arr[] = $bar;
			}
			$quzhi_zt = "start";
		}
		if($quzhi_zt=="start"&&stristr($value,".")){//开始获取数据 获取第6个带“.”的值包括“n.a.”
			$cishu++;
			if($cishu==6){
				if($value=="N.A."){//如果结果为'n.a.'，则默认为0.0000
					$value="0.0000";
				}
				$zhi[$xm][$bar]=$value;
				$quzhi_zt = "stop";
				$cishu = 0;
			}
		}
	}
}
// print_rr($jcxm_arr);
// print_rr($bar_code_arr);
// print_rr($zhi);exit;
if(count($zhi)){
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
    yqdaoru($zhi,'vd27');
}

