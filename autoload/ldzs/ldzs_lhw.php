<?php
/*
 *功能：流动注射仪器导入页面
 *作者：zhengsen
 *时间：2015-03-24
 */
$xmArr=array("TCN"=>"179","氰化物"=>'179',"PHENOL"=>"105","挥发酚"=>'105',"MBAS"=>"107","阴离子合成洗涤剂"=>'107',"CR"=>"135","总磷"=>"120","总氮"=>"121","NH3-N"=>"198","KMN04"=>"104","S"=>"185","硫化物"=>'185',"LAS"=>'107');
$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>");
$zhi    = $xm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt='';
$j=0;
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //开启获取项目的状态
    if(stristr($line,'METH')){
        $get_xm=1;
    }
    //开启获取项目的状态
    if(stristr($line,'UNIT')){
        $get_xm='';
    }
    //获取项目
    if(!empty($xmArr[$line])&&$get_xm){
        if(!in_array($xmArr[$line],$xm_arr)){
            $xm_arr[]=$line;
            //$xm_arr[]=$xmArr[$line];
        }
        if(!in_array($line,$jcxm_arr)){
            $jcxm_arr[]=$line;
        }
    }
    //匹配样品编号
    if(match_bar($line)){
        $get_xm='';//停止获取项目名称
        $bar = match_bar($line);
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $quzhi_zt = "start";
        $cishu=0;
        continue;
    }
    //取出数值
    if($quzhi_zt=='start'){
        //获取信号值
        if(preg_match("/^\d{4,5}$/",$line)){
            $j++;
            if($j%2==0){
                $zhi['vd4'][$bar][$xm_arr[1]]=$line;
            }else{
                $zhi['vd4'][$bar][$xm_arr[0]]=$line;
            }
        }
        //获取结果值
        if(stristr($line,".")||$line=='0'){
            $cishu++;
            $zhi['vd27'][$bar][$xm_arr[$cishu-1]]=$line;
        }
        if($cishu==count($xm_arr)){
            $cishu=0;
            $quzhi_zt='stop';
        }
    } 
}
return $zhi;
?>
