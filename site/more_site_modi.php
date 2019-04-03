<?php
//站点批量修改
include("../temp/config.php");
$field_list = $_POST['field'];
if(empty($field_list['modi_site'])){
    echo "请选择站点！";
    exit;
    //必须有站点
}
//获取站点类型
$old_type_list = [];
$old_type_sql  = $DB->query("SELECT * FROM `site_type_record` WHERE `sid`='{$field_list['modi_site']}'");
while ($old_type_row = $DB->fetch_assoc($old_type_sql)) {
    $old_type_list[$old_type_row['sid']][]   = $old_type_row['stid'];
}
$modi_site_list = explode(',', $field_list['modi_site']);
foreach ($modi_site_list as $sid) {
    $set_sql    = [];
    if(!empty($field_list['water_type'])){//水样类型
       $set_sql[]    = " `water_type`='{$field_list['water_type']}'";
    }
    if(!empty($field_list['site_xm'])){//监测项目
        $set_sql[]    = " `assay_values`='{$field_list['site_xm']}'";
    }
    if(count($set_sql) >'0'){
        $set_sql    = implode(',', $set_sql);
        $DB->query("UPDATE `sites` set {$set_sql} WHERE `id`='{$sid}'");
    }
    //站点类型修改
    if(!empty($field_list['site_type'])){
       ###########更新站点类型信息
        // $old_type_list = [];
        // $old_type_sql  = $DB->query("SELECT * FROM `site_type_record` WHERE `sid`='{$sid}'");
        // while ($old_type_row = $DB->fetch_assoc($old_type_sql)) {
        //     $old_type_list[]   = $old_type_row['stid'];
        // }
        $tmp_old_type   = $old_type_list[$sid]?$old_type_list[$sid]:[];
        $new_type_list  = explode(',',$field_list['site_type']);
        $delete_type_id = array_diff($tmp_old_type,$new_type_list);//要删除的site_type
        $insert_type_id = array_diff($new_type_list,$tmp_old_type);//要插入的site_type
        //删除取消选择的任务类型
        if(count($delete_type_id) >='1'){
            $DB->query("DELETE FROM `site_type_record` WHERE sid='{$sid}' AND `stid` IN (".implode(',',$delete_type_id).")");
        }
        //插入新选择的任务类型
        if(count($insert_type_id) >='1'){
            foreach ($insert_type_id as $value) {
                $DB->query("INSERT INTO `site_type_record` SET sid='{$sid}',`stid`='{$value}',`create_time`='".date('Y-m-d H:i:s')."'");
            }
        } 
    }
    echo "保存成功";
}
?>