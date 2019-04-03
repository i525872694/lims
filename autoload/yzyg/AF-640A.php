<?php
/*
 *功能：原子荧光仪器导入页面(砷、硒、汞)
 *型号：AF-610、AF-350
 *作者：tangyongsheng
 *时间：2016-12-26
 */
$xmArr = array("AS"=>"166","SE"=>"141","HG"=>"138","NONE"=>"NONE");
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'UG/L'=>array('hs'=>'0.001','blws'=>'7'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$quzhi_zt = $unit = '';
$zhi =$jcxm_arr=$bar_code_arr=array();
$cishu=0;
$get_xmZt=1;//获得项目的状态
$xm=array();//图谱的化验项目
for($i=0;$i<count($arr);$i++){
    //循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
    $line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    //echo $line."<br>";
    //获取项目
    if(stristr($line,'元素')){
        $xm_line=explode(':',$line);
        foreach($xm_line as $k=>$v){
            $v=trim($v);
            //获取项目
            if(!empty($xmArr[$v])&&$get_xmZt){
                $xm_vid=$xmArr[$v];
                if(!in_array($v,$jcxm_arr)){
                    $jcxm_arr[]=$v;
                }
            }
        }
    }
    if(empty($xm_vid)&&$xmArr[$line]){
        $xm_vid=$xmArr[$line];
    }
    //获取样品编号
    if(match_bar($line)){
        $bar      = match_bar($line);
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
        $quzhi_zt = "start";
        continue;
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
    //获取第一个带小数点的数据
    if($quzhi_zt=="start" && (stristr($line,".")||preg_match('/^\d+$/',trim($line)))){
        $cishu++;
        if($cishu==2){
            if(empty($unit)){
                $unit='MG/L';
            }
            if ($unit=="MG/L") {
                if ($xm_vid==166) {
                    $zhi[165][$bar] = $line;
                }
                $zhi[$xm_vid][$bar] = $line;
            }else {
                if($unit_arr[$unit]!=''){
                    if(empty($unit_arr[$unit]['blws'])){
                        $line=$line*$unit_arr[$unit]['hs'];
                    }else{
                        $line=del0(number_format(($line*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']));
                    }
                }
                if ($xm_vid==166) {
                    $zhi[165][$bar] = $line;
                }
                $zhi[$xm_vid][$bar] = $line;
            }
            $quzhi_zt  = "stop";
            $cishu=0;
        }
        continue;
    }
}
if(is_array($zhi) && count($zhi)){
    yqdaoru($zhi,'vd27');
}
?>
