<?php
/**
 * @author:zengqingxin;
 * @date:2018-07-11 18:08:13
 */

header("Content-Type:text/html;charset=utf-8"); 
$arr     = array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf"))mkdir("/tmp/pdf",0777);
if(!file_exists("/tmp/pdf/".$pname."s.html"))exec("pdftohtml -i $lujing /tmp/pdf/$pname");//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$cishu   = 0;
$quzhi_zt =$get_bar=$vid='';
$get_xm=1;
$xmArr=array("CU"=>"159","ZN"=>"161","MN"=>"157","K"=>"172","NA"=>"162",'CD'=>'133','PB'=>'137','FE'=>'154');
$bar_code_arr=[];
$zhi	 = array();
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
foreach($arr as $k=>$v){
    $v  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($v))));
    if(!empty($v)){
        $line_arr[]=$v;
    }
}
for($i=0;$i<count($line_arr);$i++){
    $tmp_arr = explode(' ',$line_arr[$i]);
    foreach($tmp_arr as $key=>$value){
        $value=str_replace(' ','',$value);
        if(!empty($xmArr[$value])){
            $xm_vid=$xmArr[$value];
            $unit_zt = 'on';
        }
        if(stristr($value,'CD')){
            $xm_vid='133';
        }
        if(stristr($value,'PB')){
            $xm_vid='137';
        }
        // if(!$unit){
            if(stristr($value,'MG/L')||stristr($value,'UG/ML')||stristr($value,'µG/ML')){
                $unit='MG/L';
                $get_bar='on';
            }
            if(stristr($value,'μG/L')||stristr($value,'µG/L')||stristr($value,'UG/L')){
                $unit='µG/L';
                $get_bar='on';
            }
            if(stristr($value,'NG/UL')){
                $unit='MG/L';
                $get_bar='on';
            }
        // }
        if(match_bar($value)||short_match_bar($value)){

            if(match_bar($value)){
               $bar      = match_bar($value); 
            }else if(short_match_bar($value)){
                $bar=short_match_bar($value);
            }
            if(preg_match('/^[A-Z]{2}\d{5}(加碱)?(PJ|P|J|\+)?(加碱)?$/',$bar)){
                $year = date('Y');
                $bar = $res_fzx.substr_replace(substr_replace($bar,$year,2,0),$bar_code,0,0);
            }
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
            $quzhi_zt = 'start';
        }
        
        if($quzhi_zt=='start'&&stristr($value,'.')){
            $zhi[$xm_vid][$bar]=$value;
            if($line!='0'){
                if(empty($unit_arr[$unit]['blws'])){
                    $zhi[$xm_vid][$bar]=$value*$unit_arr[$unit]['hs'];
                }else{
                    $zhi[$xm_vid][$bar]=number_format(($value*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                }
            }
            $quzhi_zt = "stop";
        }
    }
}
// print_rr($unit_arr[$unit]['hs']);
//  print_r($zhi);exit;
if(count($zhi)){
    //把编号和项目更新到pdf表的pdf_detail字段
    update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
    yqdaoru($zhi,'vd27');
}
?>
