<?php
$alter_status = true;
$table_msg = 'jzry_bd表更新';
$jzry_bd_columns = $App->get_columns('jzry_bd');
if( !in_array('kbyl', $jzry_bd_columns) ){
    $error_msg = '增加kbyl字段';
    $sql = "ALTER TABLE `jzry_bd`  ADD `kbyl` VARCHAR(500) NOT NULL COMMENT '滴定空白用量'  AFTER `yl`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
if( !in_array('sjyl', $jzry_bd_columns) ){
    $error_msg = '增加sjyl字段';
    $sql = "ALTER TABLE `jzry_bd` ADD `sjyl` VARCHAR(500) NOT NULL COMMENT '实际用量' AFTER `kb_yl`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);
?>