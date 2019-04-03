<?php
header("Content-Type:text/html;charset=utf-8"); 
$objPHPExcel = PHPExcel_IOFactory::load($lujing);
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

// print_rr($sheetData[1]);

$xmArr   = array("CU"=>'159',"ZN"=>'161',"CD"=>'133',"LI"=>"163","BE"=>"145","AL"=>"152","MN"=>"157","CO"=>"167","NI"=>"148","MO"=>"146","AG"=>"150","BA"=>"143","PB"=>"137","SR"=>"164","CR"=>"135","AS"=>"166","SE"=>"141","TL"=>"151",'V'=>"169","FE"=>"154","SB"=>"142","NA"=>"162","K"=>"172","CA"=>"173","HG"=>"138");
$newsxmArr=array("CU"=>'Cu',"ZN"=>'Zn',"CD"=>'Cd',"LI"=>"Li","BE"=>"Be","AL"=>"Al","MN"=>"Mn","CO"=>"Co","NI"=>"Ni","MO"=>"Mo","AG"=>"Ag","BA"=>"Ba","PB"=>"Pb","SR"=>"Sr","CR"=>"Cr","AS"=>"As","SE"=>"Se","TL"=>"Tl","FE"=>"Fe","SB"=>"Sb","NA"=>"Na","CA"=>"Ca","HG"=>"Hg");

$xm_arr=array();
foreach ($sheetData[1] as $key => $value) {
    if(empty($value)){
        continue;
    }
    $value  = trim(strtoupper($value));
    $temp_line=explode(' ', $value);
    foreach ($temp_line as $k => $v) {
        //获取项目
        if(!empty($xmArr[$v]) && !in_array($xmArr[$v],$xm_arr)){
            $xm_arr[$key]=$xmArr[$v];
            // $xm_arr[$key]=$v;
        }
    }
}
$zhi = array();
unset($sheetData[1]);
unset($sheetData[2]);
foreach ($sheetData as $key => $value) {
    if(match_bar($value['G'])){
        $bar = match_bar($value['G']);
        if(!in_array($bar,$bar_code_arr)){
            $bar_code_arr[]=$bar;
        }
    }else{
        continue;
    }
    foreach ($xm_arr as $c => $vid) {
        if(stristr($value[$c], '<')){
            $value[$c] = 0;
        }
        $zhi[$bar][$vid] = number_format($value[$c],6);
    }
}
// print_rr($xm_arr);
// print_rr($zhi);
return $zhi;
// if(count($zhi)){
//     //把编号和项目更新到pdf表的pdf_detail字段
//     update_pdf_detail($pdf_rs['id'],$bar_code_arr,$jcxm_arr);
//     yqdaoru($lie_data, 'vd27');
// }
?>