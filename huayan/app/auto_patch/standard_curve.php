<?php
$alter_status = true;
$table_msg = 'standard_curve表更新';
$sc_columns = $App->get_columns('standard_curve');
if( !in_array('qx_sx', $sc_columns) ){
    $error_msg = '增加qx_sx和qx_xx字段';
    $sql = "ALTER TABLE `standard_curve` ADD `qx_sx` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '曲线上限' AFTER `jzbd_id`, ADD `qx_xx` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '曲线下限' AFTER `qx_sx`;";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);
?>