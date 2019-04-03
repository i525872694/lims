<?php
$checkLogin = false;
include '../temp/config.php';

if($_GET['token']!='123456'){
    exit;
}
//电导率
$query = $DB->query("SELECT * FROM `assay_order` WHERE `vid` = '117' and `vd0`!= ' ' and `vd0` is not null and `vd0`!='0' and `vd0`!='/' ORDER BY `assay_order`.`vd0` DESC");
while($row = $DB->fetch_assoc($query)){
    $vd0 = $row['vd0'];
    $id = $row['id'];
    if($vd0<1000){
        $vd0 = _round($vd0,0);
    }else{
        $vd0 = _round($vd0,3);
    }
    $sql = "update `assay_order` set `vd0`='$vd0' where `id`='$id'";
    $DB->query($sql);
}
//透明度
$query = $DB->query("SELECT * FROM `assay_order` WHERE `vid` = '98' and `vd0`!= ' ' and `vd0` is not null and `vd0`!='0' and `vd0`!='/' ORDER BY `assay_order`.`vd0` DESC");
while($row = $DB->fetch_assoc($query)){
    $vd0 = $row['vd0'];
    $id = $row['id'];
    $vd0 = _round($vd0,2);
    $sql = "update `assay_order` set `vd0`='$vd0'  where `id`='$id'";
    $DB->query($sql);
}