<?php
// 2018-06-11 万通930Excel导入
header("Content-Type:text/html;charset=utf-8"); 
$objPHPExcel = PHPExcel_IOFactory::load($lujing);
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

// print_rr($sheetData[1]);


$xmArr= array("F"=>'181',"CL"=>'182',"SO4"=>'190',"NO3"=>'186',"PO4"=>'563',"NO2"=>"187","NO3-N"=>"186");

$xm_arr=array();
foreach ($sheetData[1] as $key => $value) {
    if(empty($value)){
        continue;
    }
    $value  = trim(strtoupper($value));
    //获取项目
    if(!stristr($value, '浓度')){
        continue;
    }
    $v = explode('.', $value)[1];
    if(!empty($xmArr[$v]) && !in_array($xmArr[$v],$xm_arr)){
        $xm_arr[$key]=$xmArr[$v];
        // $xm_arr[$key]=$v;
    }
}
$zhi = array();
unset($sheetData[1]);
// unset($sheetData[2]);
foreach ($sheetData as $key => $value) {
    if(match_bar(trim($value['B']))){
        $bar = match_bar(trim($value['B']));
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
        $zhi[$bar][$vid] = floatval($value[$c]);
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