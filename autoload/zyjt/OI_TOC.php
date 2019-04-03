<?php
/*
*功能：总有机碳载入
*作者：zhengsen
*时间：2015-06-23
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>''),'µG/L'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt=''; 
$cishu=0;
$zhi=$bar_code_arr=$jcxm_arr=array();
//取出数组键数
$jcxm_arr=array("总有机碳"=>"111");
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//获取样品编号
    if(match_bar($line)){
        $bar = match_bar($line);
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $cishu=0;
        $quzhi_zt = "start";
        continue;
    }
    //获取第三个带小数点的数据
    if($quzhi_zt=="start" && stristr($line, ".")){
        $cishu++;
        if($cishu==2){
            $zhi['111'][$bar] =number_format($line,3);
            $quzhi_zt  = "stop";
        }
    }
}
return $zhi;