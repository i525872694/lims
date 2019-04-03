<?php
$alter_status = true;
$table_msg = 'leixing表更新';
$leixing_columns = $App->get_columns('leixing');
$error_msg = 'leixing增加sort字段';
if(!in_array('sort', $leixing_columns)){
    $sql = "ALTER TABLE `leixing` ADD `sort` VARCHAR(10) NOT NULL";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);