<?php
$alter_status = true;
$table_msg = 'assay_order表更新';
$assay_pay_columns = $App->get_columns('assay_pay');
$error_msg = 'assay_pay增加CC字段';
if(!in_array('CC', $assay_pay_columns)){
    $sql = "ALTER TABLE `assay_pay` ADD `CC` VARCHAR(10) NOT NULL AFTER `CB`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = 'assay_pay增加CR字段';
if(!in_array('CR', $assay_pay_columns)){
    $sql = "ALTER TABLE `assay_pay` ADD `CR` VARCHAR(10) NOT NULL AFTER `CC`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = 'assay_pay增加CT字段';
if(!in_array('CT', $assay_pay_columns)){
    $sql = "ALTER TABLE `assay_pay` ADD `CT` VARCHAR(10) NOT NULL AFTER `CR`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = '增加btdata字段';
if( !in_array('btdata', $assay_pay_columns) ){
    $sql = "ALTER TABLE `assay_pay` ADD `btdata` TEXT NOT NULL COMMENT '表头参数' AFTER `CT`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = '增加pzid字段';
if( !in_array('pzid', $assay_pay_columns) ){
    $sql = "ALTER TABLE `assay_pay` ADD `pzid` TEXT NOT NULL COMMENT '标基准溶液配置id' AFTER `bdid`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);
