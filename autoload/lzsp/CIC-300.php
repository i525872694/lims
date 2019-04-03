<?php
/**
 * @author:zengqingxin
 * @date:2018-07-17 18:23:11
 */

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","\\","(");

$xmArr= array("F-"=>'181',"F"=>'181',"氟氟氟"=>'181',"CL-"=>'182',"CL"=>'182',"氯氟氟"=>'182',"SO4-"=>'190',"SO4"=>'190',"硫亚亚"=>'190',"NO3-"=>'186',"NO3"=>'186',"NO3-N"=>'186',"亚亚亚亚"=>'186',"PO4"=>'563',"亚亚亚"=>'563',"PO4-"=>'563',"NO2-N"=>'187');
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt = $bhZt ='';
$zhi	 = array();
$bar_code_arr=[];
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    $tmp_arr = explode(' ',$line);
    foreach($tmp_arr as $k=>$v){
        if(!$unit){
            if(stristr($line,'MG/L')||stristr($v,'UG/ML')||stristr($v,'µG/ML')){
                $unit='MG/L';
                $get_bar='on';
            }
            if(stristr($v,'μG/L')||stristr($v,'µG/L')||stristr($v,'UG/L')){
                $unit='µG/L';
                $get_bar='on';
            }
            if(stristr($v,'NG/UL')){
                $unit='MG/L';
                $get_bar='on';
            }
        }
        //获取样品编号
        if(match_bar($v)){
            $bar      = match_bar($v);
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
        }
        if(stristr($v,'序号')){
            $get_xm = 'on';
        }
        if($get_xm=='on'&&!empty($xmArr[$v])){
            $xm_vid=$xmArr[$v];
            $quzhi_zt='on';
        }
        if($quzhi_zt=='on'&&(stristr($v,'.')||$v=='0')){
            if(empty($unit)){
                $unit='MG/L';
            }
            $zhi[$xm_vid][$bar]=$v;
            if($v!='0'){
                if(empty($unit_arr[$unit]['blws'])){
                    $zhi[$xm_vid][$bar]=$v*$unit_arr[$unit]['hs'];
                }else{
                    $zhi[$xm_vid][$bar]=number_format(($v*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                }
            }
            $quzhi_zt='off';
        }

    }

}

if(count($zhi)){
    yqdaoru($zhi,'vd27');
}
?>


