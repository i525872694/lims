<?php

//要检查的表格 和字段名
$check_table = array(
    'duijie'=>array(
        'unit'=>array('VARCHAR(100)','单位'),
        'lims_read'=>array("ENUM('0','1')","评价系统",'0'),
        'quality'=>array("VARCHAR(50)","水质类别",''),
        'qualified'=>array("VARCHAR(50)","是否合格",''),
        'beishu'=>array("VARCHAR(50)","超标倍数",'')
        )
);

$fields = array();

foreach($check_table as $k=>$v){
    $query = $DB->query("select COLUMN_NAME,column_comment from INFORMATION_SCHEMA.Columns where table_name='$k' and table_schema='$dbname'");
    while( $row = $DB->fetch_assoc($query) ){
        $fields[$k][] = $row['COLUMN_NAME'];
    }
    $last = count($fields[$k])-1;
    $field_after  = $fields[$k][$last];
    foreach($v as $field =>$field_v){
        if(!in_array($field,$fields[$k])){
            $sql = "ALTER TABLE `$k` ADD `$field` $field_v[0] NOT NULL COMMENT '$field_v[1]' DEFAULT '$field_v[2]' AFTER `$field_after`;";
            $DB->query($sql);
        }
    }
}