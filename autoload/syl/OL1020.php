<?php
/*
*功能：红外测油仪
*作者：汤永胜
*时间：2017-01-04
*检测项目：石油类
*数组格式:$zhi = array([编号1]=>值1,[编号2]=>值2)
*/
header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
//$lujing="./files/20170104115159_10.21.90.99.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$quzhi_zt=$quzhi_zt2='';
$zhi =$bar_code_arr=$jcxm_arr=array();
$xmArr=array("总油"=>'627',"石油"=>'108',"石油类"=>'108',"动植物油"=>'110');
$cishu=0;
//取出数组键数
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
        //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
       $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
       $line = str_replace('－','-',$line); //将 '－'转换成'-'；
       //echo $line."<br/>";
       //获取样品编号
        if(match_bar($line)){
           $bar      = match_bar($line);
           if(!in_array($bar,$bar_code_arr)){
               $bar_code_arr[]=$bar;
           }
           $quzhi_zt = "start";
        }
        //匹配检测项目
        if(!empty($xmArr[$line])){
            $xm_vid=$xmArr[$line];
            if(!in_array($line,$jcxm_arr)){
                $jcxm_arr[]=$line;
            }
            $quzhi_zt2 = "start";
        }
        //获取最终结果值
        if($quzhi_zt=='start'&&$quzhi_zt2=='start'&&(stristr($line,'.')||$line=="0")){
             $zhi[$bar][$xm_vid]=$line;
             $quzhi_zt='stop';
             $quzhi_zt2='stop';
        }
}
// print_rr($zhi);
if(count($zhi)){
    //把编号和项目更新到pdf表的pdf_detail字段
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
    yqdaoru($zhi,'vd27');
}
?>
