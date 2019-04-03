<?php
/**
 * @author:zengqingxin;
 * @date:2018-07-23 16:26:58;
 * @型号:连续流动分析仪 SKALAR 5000;
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
$cishu=0;
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    $tmp_arr = explode(' ',$line);
    foreach($tmp_arr as $k=>$v){
        //获得单位
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
        //获得项目
        if(!empty($xmArr[$v])){
            $xm_vid=$xmArr[$v];
            $get_bar='on';
        }
        //获得样品编号
        if($get_bar=='on'&&match_bar($v)){
            $bar=match_bar($v);
            $quzhi_zt='on';
        }
        //取值
        if($quzhi_zt=='on'&&stristr($v,'.')){
            $cishu++;
            if($cishu==2){
                //判断是否取到单位,没有则给默认值
                if(empty($unit)){
                    $unit='MG/L';
                }
                $zhi[$xm_vid][$bar]=$v;
                //单位转换
                if($v!='0'){
                    if(empty($unit_arr[$unit]['blws'])){
                        // $zhi[$xm_vid][$bar]=del0($v*$unit_arr[$unit]['hs']);
                        $zhi[$xm_vid][$bar]=$v*$unit_arr[$unit]['hs'];

                    }else{
                        // $zhi[$xm_vid][$bar]=del0(number_format(($v*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']));
                        $zhi[$xm_vid][$bar]=number_format(($v*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
                    }
                }
                $quzhi_zt='off';
                $cishu=0;
            }
        }
    }
}
if(count($zhi)){
	yqdaoru($zhi,'vd27');
}
?>