<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *作者：zhengsen
 *时间：2015-03-30
 */
$xmArr = array("AS"=>"166","SE"=>"141","HG"=>"138","NONE"=>"NONE");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit=array('MG/L'=>array('hs'=>'1','blws'=>'5'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt='';
$zhi=$bar_code_arr=$jcxm_arr     = array();
$cishu=0;
$get_xmZt=1;//获得项目的状态
$xm=array();//图谱的化验项目
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	$line = str_replace(array("："),array(":"),$line);
	//取出化验项目
	if($get_xmZt){
		$temp_xmArr = explode(':',$line);
		foreach($temp_xmArr as $key=>$value){
			if(isset($xmArr[$value])&&$value!=''&&!in_array($value,$xm)){
				$xm[]=$value;
			}
			if(!in_array($value,$jcxm_arr)){
				$jcxm_arr[]=$value;
		    }
		}
	}
	//取出编号
	if(match_bar($line)){
		$bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar;
		}
		$quzhi_zt = "start";
		$get_xmZt='0';
	}
	//取出项目相应数值
	if($quzhi_zt=='start'){
		if(stristr($line,".")){
			$last_sj=$line;
		}
		if(isset($unit[$line]) && !empty($unit[$line])){
			$zhi[$bar][$xmArr[$xm[$cishu]]]=$last_sj;
			$cishu++;
		}
		if($cishu==count($xm)){
			$quzhi_zt='stop';
			$cishu=0;
		}
	}
}
return $zhi;