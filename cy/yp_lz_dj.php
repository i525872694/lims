<?php
/*
**用途：浙江样品验收登记表
*/
include '../temp/config.php';
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'采样验收列表','href'=>'./cy/cy_ys_list.php'),
    array('icon'=>'','html'=>'样品验收登记表','href'=>$current_url)
);

$fzx_id=$u['fzx_id'];
$cyd_id=$_GET['cyd_id'];
$load_spread_file = temp('public/load_spread_file');
if(!$_GET['ajax']){
    disp('yangpinliuzhuan/yp_lz_dj');
}
$file_path='../template/yangpinliuzhuan/yp_lz_dj.json';


########组装成json返回
$re_back=array(
    'headerData'=>[],
    'linesData'=>[],
    'sp_style'=>json_decode(file_get_contents($file_path),true)
);
echo json_encode(array('error'=>'0','data'=>$re_back));
?>