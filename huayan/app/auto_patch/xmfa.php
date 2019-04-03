<?php
$alter_status = true;
$table_msg = 'xmfa表更新';
$xmfa_columns = $App->get_columns('xmfa');
if( !in_array('check_jcx', $xmfa_columns) ){
    $error_msg = '增加check_jcx和round_func字段';
    $sql = "ALTER TABLE `xmfa` ADD `check_jcx` CHAR(1) NOT NULL DEFAULT '1' COMMENT '是否判断检出限' AFTER `jcx`, ADD `round_func` VARCHAR(10) NOT NULL DEFAULT '_round' COMMENT '修约函数' AFTER `check_jcx`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
if( !in_array('blws', $xmfa_columns) ){
    $error_msg = '增加blws字段';
    $sql = "ALTER TABLE `xmfa` ADD `blws` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '保留位数' AFTER `w5`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
if( !in_array('round_inits', $xmfa_columns) ){
    $error_msg = '增加round_inits字段';
    $sql = "ALTER TABLE `xmfa` ADD `round_inits` char(1) NOT NULL DEFAULT '0' COMMENT '是否修约整数位' AFTER `blws`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);
?>