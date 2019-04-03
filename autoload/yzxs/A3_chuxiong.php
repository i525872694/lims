<?php
/*
*功能：原子吸收仪器载入页面（铜、锌、铁、锰、铅、镉）
*作者：zhengsen
*时间：2015-04-02
*/
//include("../../temp/config.php");
$arr     = array();
//$lujing="../files/tl_yzxs2.pdf";
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$cishu   = 0;
$quzhi_zt ='';
$xmArr=array("FE"=>"154","MN"=>"157","CU"=>"159","ZN"=>"161","CD"=>"133","PB"=>"137","K"=>"172","NA"=>"162","NI"=>"148");
$get_xmzt='1';
$get_bar=$unit='';
$zhi = $bar_code_arr = $jcxm_arr = array();
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'3'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
//print_rr($arr);exit();
//if($u['admin']==1){print_rr($arr);}
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	//if($u['admin']==1){echo $line."<br>";}
	if($get_xmzt){
		if($xmArr[$line]){
			$xmid=$xmArr[$line];
			if (!in_array($line, $jcxm_arr)) {
				$jcxm_arr[] = $line;
			}
			$get_xmzt='';
			$get_bar='1';
		}
	}
	if(match_bar(str_replace(" ",'',$line))&&$get_bar){
		$bar = match_bar(str_replace(" ",'',$line));
		if(!in_array($bar, $bar_code_arr)){
			$bar_code_arr[] = $bar;
		}
		$quzhi_zt = "start";
		$cishu = 0;
	}
	if(!$unit){
		if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'µG/ML')){
			$unit='MG/L';
		}
		if(stristr($value,'μG/L')||stristr($value,'µG/L')||stristr($value,'UG/L')){
			$unit='µG/L';
		}
		if(stristr($value,'NG/UL')){                       	$unit='MG/L';
		}
			

	}
	if($quzhi_zt=="start"&&((preg_match("/[-]?[0-9]+[.][0-9]{3}$/", $line))||strstr($line,'未未未')||stristr($line,'未检出')||$line===0)){
        $cishu++;
		if($cishu==3){
			if(empty($unit)){
				$unit='MG/L';
			}
			if(stristr($line,"-")||stristr($line,'未检出')||stristr($line, '未未未')){
				$line='0.000';
            }
			$zhi[$bar][$xmid]=$line;
			if($line!='0'&&$line!='0.000'){
				if(empty($unit_arr[$unit]['blws'])){
					$zhi[$bar][$xmid]=del0($line*$unit_arr[$unit]['hs']);
				}else{
					$zhi[$bar][$xmid]=number_format((del0($line*$unit_arr[$unit]['hs'])),$unit_arr[$unit]['blws']);
				}
			}
			$cishu=0;
            $quzhi_zt='stop';
        }
	}
}
//print_rr($zhi);exit();
/*if($u['admin']==1){
	print_rr($jcxm_arr);
	print_rr($bar_code_arr);
	print_rr($zhi);exit;
}*/
if(count($zhi)){
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
	yqdaoru($zhi,'vd27');
}
?>
