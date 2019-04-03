<?php
/*
*功能：原子吸收载入页面 型号：Aanalyst-800
*作者：汤永胜
*时间：2016-11-28
*检测项目：铜锌铅镉锰钾钠镁铝等
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
/*$lujing="./files/20161128.pdf";*/
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$xmArr=array("FE"=>"154","CU"=>"159","ZN"=>"161","CD"=>"133","PB"=>"137",'K'=>'172',"NA"=>'162',"MN"=>"157",'CA'=>'173','MG'=>'174');
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt='';
$zhi =$jcxm_arr=$bar_code_arr=array();
$cishu=0;
$get_xm=1; //开启获取项目的标识
//取出数组键数
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //    echo $line.'<br/>';
        //取出编号匹配有8个的 数字, 加0~1个的 "J"或"P"
        $temp_arr=explode(" ",$line);
        foreach($temp_arr as $key=>$value){
            // echo $value.'<br/>';
            //获得单位
            if($get_unit=='on'){
                if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'µG/ML')){
                    $unit='MG/L';
                    $get_unit='off';
                }
                if(stristr($value,'μG/L')||stristr($value,'µG/L')||stristr($value,'UG/L')||stristr($value,'微克/升')){
                    $unit='µG/L';
                    $get_unit='off';
                }
                if(stristr($value,'NG/UL')){
                    $unit='MG/L';
                    $get_unit='off';
                }
            }
            //获取样品编号
            if(match_bar($value)){
                $bar      = match_bar($value);
                $get_xm = 'on';
                continue;
            }
            //获取项目
            $value=trim($value);
            if(!empty($xmArr[$value])&&$get_xm=='on'){
                $xm_vid=$xmArr[$value];
                $get_xm='off';
                $quzhi_zt='on';
                $get_unit = 'on';
                continue;
            }
            if($quzhi_zt=='on'&&stristr($value,'#')){
                $quzhi_zt2='on';
            }
            if($quzhi_zt2=='off'&&stristr($value,'均值')){
                $quzhi_zt3='on';
            }
            if($quzhi_zt=="on"&&$quzhi_zt2=="on"&&preg_match("/^[+-]?\d+\.\d{2,4}$/",$value)){
                if(empty($unit)){
                    $unit='MG/L';
                }
                $zhi[$xm_vid][$bar]=$value;
                if($value!='0'){
                    if(empty($unit_arr[$unit]['blws'])){
                        $zhi[$xm_vid][$bar]=$value*$unit_arr[$unit]['hs'];
                    }else{
                        $zhi[$xm_vid][$bar]=number_format(($value*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                    }
                }
                $quzhi_zt='off';
                $quzhi_zt2='off';
            }
            if($quzhi_zt=='off'&&$quzhi_zt3=='on'&&preg_match("/^[+-]?\d+\.\d{2,4}$/",$value)){
                if(empty($unit)){
                    $unit='MG/L';
                }
                $zhi[$xm_vid][$bar]=$value;
                if($value!='0'){
                    if(empty($unit_arr[$unit]['blws'])){
                        $zhi[$xm_vid][$bar]=$value*$unit_arr[$unit]['hs'];
                    }else{
                        $zhi[$xm_vid][$bar]=number_format(($value*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                    }
                }
                $quzhi_zt='end';
                $quzhi_zt3='off';
            }
        }    
}
if(count($zhi)){
    //把编号和项目更新到pdf表的pdf_detail字段
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
    yqdaoru($zhi,'vd27');
}
?>
