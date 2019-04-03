<?php
$alter_status = true;
$table_msg = 'assay_order表更新';
$order_columns = $App->get_columns('assay_order');
$error_msg = '增加order_id字段';
if( !in_array('order_id', $order_columns) ){
    $error_msg = '增加order_id字段';
    $sql = "ALTER TABLE `assay_order` ADD `order_id` INT NOT NULL COMMENT '排序id' AFTER `id`";
    $query = $DB->query($sql);
    // 进行默认排序
    $sql = "SELECT `module_value1` AS len1, `module_value2` AS len2 FROM `n_set` WHERE `module_name`='bar_code_length' AND `fzx_id`='{$fzx_id}'";
    $code_len = $DB->fetch_one_assoc($sql);
    // 如果没有设置数据则默认样品编号长度为13位，后四位为流水号
    if(empty($code_len)){
        $code_len = [
            'len1' => 13, 'len2' => 4
        ];
    }
    // 化验单样品默认排序规则
    $order_by = "RIGHT(LEFT(`bar_code`, {$code_len['len1']}), {$code_len['len2']}) ASC";
    // 取消执行时间限制
    set_time_limit(0);
    $sql = "SELECT `id` FROM `assay_pay` WHERE 1";
    $query = $DB->query($sql);
    while ($row = $DB->fetch_assoc($query)) {
        $DB->query("SET @r:=-1");
        $DB->query("UPDATE `assay_order` AS `ao` SET `order_id`=(@r:=@r+1) WHERE `tid`='{$row['id']}' ORDER BY {$order_by} ASC");
    }
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = '增加js_gongshi字段';
if( !in_array('js_gongshi', $order_columns) ){
    $sql = "ALTER TABLE `assay_order` ADD `js_gongshi` TEXT NOT NULL COMMENT '计算公式' AFTER `xiang_dui_pian_cha`";
    $query = $DB->query($sql);
    error_msg($query, $error_msg) || ($alter_status = false);
}
$error_msg = '增加zk_data字段';
if( !in_array('zk_data', $order_columns) ){
    $sql = "ALTER TABLE `assay_order` ADD `zk_data` TEXT NOT NULL COMMENT '质控数据' AFTER `vd32`";
    $query = $DB->query($sql);
    $sql = "INSERT INTO `menu` (`id`, `type`, `parent_id`, `name`, `url`, `sort`, `title`, `qx`, `icon`) VALUES (NULL, '0', '33', '质控计算设置', './huayan/ahlims.php?app=zhikong target=main', '4', '质控计算设置', '', '');";
    $DB->query($sql);
    $sql = "SELECT * FROM `assay_order` WHERE RIGHT(`bar_code`, 1)='J'";
    $query=$DB->query($sql);
    while ($row=$DB->fetch_assoc($query)) {
        $zk_data = array(
                'id' => $row['id'],
                'action' => '40',
                'x_y' => 1,
                'x_j' => 1,
                'v_y' => $row['vd28'],
                'c_c' => $row['vd29'],
                'c_c_unit' => $row['vd31'],
                'v_c' => $row['vd30'],
                'v_c_unit' => $row['vd32'],
                'v_o' => 0,
                'v_o_unit' => 'mL',
                'x_v' => 1
            );
        $zk_data = JSON($zk_data);
        $sql = "UPDATE `assay_order` SET `zk_data`='{$zk_data}' WHERE id='{$row['id']}'";
        $query_1 = $DB->query($sql);
        error_msg($query_1, $error_msg) || ($alter_status = false);
    }
}
return error_msg($alter_status, $table_msg);
?>