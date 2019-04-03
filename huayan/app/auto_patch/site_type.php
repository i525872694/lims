<?php
$alter_status = true;
$table_msg = 'site_group表更新';
$site_group_columns = $App->get_columns('site_group');
if( !in_array('milieu_values', $site_group_columns) ){
    $error_msg = '增加环境字段';
    $sql = "ALTER TABLE `site_group` ADD `milieu_values` TEXT NOT NULL AFTER `qckb_values`";
    $query = $DB->query($sql);
    $DB->query("ALTER TABLE `site_group` ADD `xcpx_milieu_values` TEXT NOT NULL AFTER `milieu_values`");
    $DB->query("ALTER TABLE `site_group` ADD `qckb_milieu_values` TEXT NOT NULL AFTER `xcpx_milieu_values`");
    error_msg($query, $error_msg) || ($alter_status = false);
}
return error_msg($alter_status, $table_msg);
?>