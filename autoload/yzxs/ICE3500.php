<?php
/*
*功能：原子吸收仪器载入页面（铁、锰）
*作者：zhengsen
*时间：2014-12-30
*/
$arr     = array();
//include("../../temp/config.php");
//$lujing="../files/slw_yzxs.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$cishu   = 0;
$quzhi_zt ='';
$xmArr=array("FE"=>"154","MN"=>"157","CD"=>"133","CU"=>"159","ZN"=>"161","PB"=>"137");
$zhi	 = array();
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
//print_rr($arr);exit();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    $tmp_arr = explode(' ',$line);
    foreach($tmp_arr as $k=>$v){
        // if(!$unit){
            if(stristr($line,'MG/L')||stristr($v,'UG/ML')||stristr($v,'µG/ML')){
                $unit='MG/L';
            }
            if(stristr($v,'μG/L')||stristr($v,'µG/L')||stristr($v,'UG/L')){
                $unit='µG/L';
            }
            if(stristr($v,'NG/UL')){
                $unit='MG/L';
            }
        // }
        if(!empty($xmArr[$v])){
            $xm_vid=$xmArr[$v];
        }
        if(match_bar($v)){
            $bar = match_bar($v);
            if(isset($zhi[$bar])){
                $bar = $bar."P";
            }
            $quzhi_zt = "start";
        }
        if($quzhi_zt=="start"&&(stristr($v,".")||preg_match('/\d+/',$v))){
            $cishu++;
            if($cishu==4){
                $v=$v*1;
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
                $quzhi_zt = "stop";
                $cishu = 0;
            }
        }
    }	
}
// print_rr($zhi);exit();
if(count($zhi)){
	yqdaoru($zhi,'vd27');
}
?>

