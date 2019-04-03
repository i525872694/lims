<?php
include "../temp/config.php";
$daohang = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'检验方法标准号配置','href'=>'system_settings/assay_method/assay_method_list.php')
);
$trade_global['daohang'] = $daohang;

disp('method_list');