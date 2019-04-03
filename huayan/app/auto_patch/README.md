数据库内容更新补丁
使用说明:
1.新建一个php文件,文件命名规则:Y-M-d-两位流水号-功能简述.php
2.$App->get_columns('assay_order');方法获取某个表的字段
3.使用php执行huayan文件夹下的ahrun.php文件统一更新
4.返回值格式:
    return [
        'error' => true|false,
        'error_msg' => 文字内容描述
    ];
5.示例:给assay_order表增加order_id字段
<?php
$order_columns = $App->get_columns('assay_order');
if( !in_array('order_id', $order_columns) ){
    $error_msg = '增加order_id字段';
    $sql = "ALTER TABLE `assay_order` ADD `order_id` INT NOT NULL COMMENT '排序id' AFTER `id`";
    $query = $DB->query($sql);
    return [
        'error' => $query,
        'error_msg' => $error_msg
    ];
}else{
    return [
        'error' => true,
        'error_msg' => 'assay_order表中已有order_id字段,无需增加.'
    ];
}
?>