<?php
/*
 *功能：流动注射仪器导入页面
 *作者：zhengsen
 *时间：2015-03-24
 */
header("Content-Type:text/html;charset=utf-8");

$xmArr=array(
    "COD"=>'104','高锰酸盐指数'=>'104',
    "S"=>'185',"硫化物"=>'185',
    "LAS"=>'107',"阴离子合成洗涤剂"=>'107',
    "NH4-N"=>'198',"氨氨"=>'198',
    "VLPH"=>'105',"挥挥酚"=>"105",
    "CN"=>'179',"氰化物"=>'179',
    "TN"=>'121',
    "TP"=>'120'
);
$newsxmArr=array("COD"=>'高锰酸盐指数',"S"=>'硫化物',"LAS"=>'阴离子合成洗涤剂',"NH4-N"=>'氨氮',"VLPH"=>"挥发酚","VLHP"=>"挥发酚","CN"=>'氰化物',"挥挥挥"=>"挥发酚","挥挥挥挥"=>"氰化物");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$html    = array("<BR>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;","<BR/>");
$zhi    = $xm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt=$cishu='';
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //echo $line."<br>";
    $temp_line=explode(" ",$line);
    foreach($temp_line as $key=>$value){
        if(stristr($value,'METHOD')){
            $get_xm=1;
        }
        if(stristr($value,'UNIT')){
            $get_xm='';
            $get_bar='on';
        }
        if($get_xm==1&&(stristr($value,':')||stristr($value,'：'))){
            $value=str_replace([':','：'],'',$value);
        }
        //获取项目
        if(!empty($xmArr[$value])&&$get_xm){
            if(!in_array($xmArr[$value],$xm_arr)){
                $xm_arr[]=$xmArr[$value];
            }
            if(!in_array($value,$jcxm_arr)){
                $jcxm_arr[]=$value;
            }
        }
        //获取编号开始
        if(stristr($value,'Results')){
            $get_bar='on';
        }
        // 获取编号结束
        if(stristr($value,'Corrections')){
            $get_bar='off';
        }
        //匹配样品编号
        if(match_bar($value)&&$get_bar=='on'){
            $get_xm='';//停止获取项目名称
            $bar = match_bar($value);
           // echo $bar."<br />";
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
            $quzhi_zt = "start";
            $cishu=0;
            continue;
        }
        //取出数值
        if($quzhi_zt=='start'){
            if(stristr($value,".")){
                $cishu++;
                $zhi[$bar][$xm_arr[$cishu-1]]=$value;
                
            }
            if($cishu==count($xm_arr)){
                $cishu=0;
                $quzhi_zt='stop';
            }
        }
    }
}
// print_rr($zhi);
return $zhi;
?>
