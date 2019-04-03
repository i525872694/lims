<?php
/*
*功能：icp仪器载入页面
*作者：tangyongsheng
*时间：2016-12-16
*检测项目：硒、镉、银、铜、锌、铅、锰
*使用地区：省中心
*/
// header("Content-Type:text/html;charset=utf-8");
// include '../autoload_func.php';
// $lujing = '../20180314165745_PDF_11.pdf';//文件的具体路径
$arr     = array();
$pname   = basename($lujing);
if(!is_dir("/tmp/pdf")){
    mkdir("/tmp/pdf",0777);
}
//把pdf转换成html格式(产生3个文件,数据在xxxs.html中>>>XXX.pdf转换的)
if(!file_exists("/tmp/pdf/".$pname."s.html")){
    exec("pdftohtml -i $lujing /tmp/pdf/$pname");
}
$lujing2 = "/tmp/pdf/".$pname."s.html";
$arr     = @file($lujing2);//把文件 读取成数组

// echo '<pre>';
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>");
//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");
$xmArr   = array("CU"=>'159',"ZN"=>'161',"CD"=>'133',"LI"=>"163","BE"=>"145","AL"=>"152","MN"=>"157","CO"=>"167","NI"=>"148","MO"=>"146","AG"=>"150","BA"=>"143","PB"=>"137","SR"=>"164","CR"=>"135","AS"=>"166","SE"=>"141","TL"=>"151",'V'=>"169","FE"=>"154","SB"=>"142","NA"=>"162","K"=>"172","CA"=>"173","HG"=>"138");
$newsxmArr=array("CU"=>'Cu',"ZN"=>'Zn',"CD"=>'Cd',"LI"=>"Li","BE"=>"Be","AL"=>"Al","MN"=>"Mn","CO"=>"Co","NI"=>"Ni","MO"=>"Mo","AG"=>"Ag","BA"=>"Ba","PB"=>"Pb","SR"=>"Sr","CR"=>"Cr","AS"=>"As","SE"=>"Se","TL"=>"Tl","FE"=>"Fe","SB"=>"Sb","NA"=>"Na","CA"=>"Ca","HG"=>"Hg");
$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'4'),'µG/L'=>array('hs'=>'0.001','blws'=>'7'),'NG/ML'=>array('hs'=>'0.001','blws'=>'7'));
$zhi    = $xm_arr=$bar_code_arr=$jcxm_arr=array();
$quzhi_zt=$cishu='';
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
    $line  = str_replace('&LT;', '<', $line);

	$temp_line=explode(" ",$line);
	foreach ($temp_line as $key => $value) {

        if(stristr($value,'样品名称')){
            $get_xm='';
        }else if(stristr($value,'样品')){
            $get_xm=1;
            $xm_arr=array();
            $quzhi_zt='stop';
        }
        //获取项目
        if(!empty($xmArr[$value])&&$get_xm){
            if(!in_array($xmArr[$value],$xm_arr)){
                $xm_arr[]=$xmArr[$value];
                // $xm_arr[]=$value;
            }
            if(!in_array($value,$jcxm_arr)){
                $jcxm_arr[]=$value;
            }
        }
        //匹配样品编号
        if(match_bar($value)){
            $get_xm='';//停止获取项目名称
            $bar = match_bar($value);
            if(!in_array($bar,$bar_code_arr)){
                $bar_code_arr[]=$bar;
            }
            $quzhi_zt = "start";
            $cishu=0;
            continue;
        }
        //取出数值
        if($quzhi_zt=='start'){
            if(stristr($value,".") && $cishu < count($xm_arr)*2){
                $cishu++;
                if(($cishu-1)%2==0){
                    $zhi[$bar][$xm_arr[($cishu-1)/2]] = stristr($value, '<') ? 0 : sc_to_num($value);
                }
            }
        }
	}
}
return $zhi;