<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *@author：zengqingxin
 *@date：2018年07月11日
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
foreach($arr as $k=>$v){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($v))));
	if(stristr($v,'元素')){
		$line = str_replace('：',':',$line);
		$line = str_replace(' ','',$line);		
		$xm_tmp = explode(':',$line);
		for($i=0;$i<count($xm_tmp);$i++){
			if($get_xmZt&&!empty($xmArr[$xm_tmp[$i]])){
				$xm_vid = $xmArr[$xm_tmp[$i]];
				$get_xmZt='';
				$get_barZt='on';
			}
		}	
	}
	if($get_barZt=='on'&&match_bar($line)){
		$bar = match_bar($line);
		$quzhi_zt='on';
		$cishu = 0;
		continue;
	}
	if($quzhi_zt=='on'&&stristr($line,'.')){
		$cishu++;
		if($cishu==2){
			$zhi[$xm_vid][$bar]=$line;
			$quzhi_zt='off';
			$cishu=0;
		}
	}
}
return $zhi;