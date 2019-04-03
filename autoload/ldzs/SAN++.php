<?php
/**
 * @author:zengqingxin;
 * @date:2018-07-19 15:30:06
 */
 $arr=array();

$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr=array("TCN"=>"179","PHENOL"=>"105","MBAS"=>"107","CR"=>"135","TP"=>"120","TN"=>"121","NH3-N"=>"198","KMN04"=>"104","S"=>"185","NH3"=>"198","COD"=>"104","CR6+"=>'135',"LAS"=>'107',"LAS-"=>'107',"H2S"=>"185");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = $bar_code_arr=$jcxm_arr= $jcxm_id_arr=array();
$quzhi_zt=$get_xm=$get_bar=$get_xs='';
$k=0;

for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	if(stristr($line,"SAMPLE")){
		$get_xm=1;
	}
	if($get_xm){
		if(stristr($line,"-")){
			$temp_line=explode("-",$line);
		}else{
			$temp_line=explode(" ",$line);
		}

		foreach($temp_line as $key=>$value){
			if($xmArr[$value]){
				if(!in_array($value,$jcxm_arr)){
					$jcxm_arr[]=$value;
					$jcxm_id_arr[]= $xmArr[$value];
				}
                $xm_vid=$xmArr[$value];
		        $get_bar='on';
                
			}
			continue;
		}
	}
	if(stristr($line,'CORRECTEDHEIGHT') || stristr($line,'CORRECTED') ){ //CORRECTEDHEIGHT  ,NEEDLENUMBER
		$get_xm='';//停止获取项目
	}
	if(!$unit){
        if(stristr($line,'MG/L')||stristr($line,'UG/ML')||stristr($line,'µG/ML')){
            $unit='MG/L';
        }
        if(stristr($line,'μG/L')||stristr($line,'µG/L')||stristr($line,'UG/L')){
            $unit='µG/L';
        }
        if(stristr($line,'NG/UL')){
            $unit='MG/L';
        }
    }
	if($get_bar=='on'){
		// print_rr($line);
		if(match_bar(str_replace(' ','',$line))){//总站编号匹配
			$bar = match_bar(str_replace(' ','',$line));
			// print_rr($bar);
			if(!in_array($bar,$bar_code_arr)){
				$bar_code_arr[]=$bar;
			}
			$quzhi_zt = "start";
			$vid	= $jcxm_id_arr[0];
			continue;
		}
	}
	
    //取值
    if($quzhi_zt=='start'&&stristr($line,'.')){
        if(empty($unit)){
			$unit='MG/L';
		}
        $zhi[$xm_vid][$bar]=$line;
        if($line!='0'){
            if(empty($unit_arr[$unit]['blws'])){
                $zhi[$xm_vid][$bar]=$line*$unit_arr[$unit]['hs'];
            }else{
                $zhi[$xm_vid][$bar]=number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
            }
        }
        $quzhi_zt = "stop";
    }
}
// print_rr($zhi);
if(count($zhi)){

	//把编号和项目更新到pdf表的pdf_detail字段
	update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);

	yqdaoru($zhi,'vd27');
}
 
?>
