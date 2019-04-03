<?php
/*
*功能：i间断性化学分析仪
*作者：Mr zhou
*时间：2018-02-26
*检测项目：
*使用地区：
*/
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");
//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr   = array(
	"氨氮"=>'198',"NH4-N"=>'198'
);
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = $xm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt=$cishu='';
$get_xm = '1';
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//echo $line."<br/>";
	//匹配检测项目
	if(!empty($xmArr[$line])){
	    $xm_vid=$xmArr[$line];
	    if(!in_array($line,$jcxm_arr)){
	        $jcxm_arr[]=$line;
	    }
	    $quzhi_zt2 = "start";
	}
	if(empty($jcxm_arr)){
		continue;
	}
	//获取样品编号
	if(match_bar($line)){
	   $bar = match_bar($line);
	   if(!in_array($bar,$bar_code_arr)){
	       $bar_code_arr[]=$bar;
	   }
	   $quzhi_zt = "start";
	}
	//获取最终结果值
	if($quzhi_zt=='start'&&$quzhi_zt2=='start'&&(stristr($line,'.')||$line=="0")){
	     $zhi[$bar][$xm_vid]=$line;
	     $quzhi_zt='stop';
	}
}
return $zhi;
