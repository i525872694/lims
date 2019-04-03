<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-11
 * Time: 下午5:01
 */
//保存标签的层级关系

include __DIR__ . '/../../temp/config.php';
include __DIR__ . '/func.php';

//数据处理
$all_node = all_site_type_data();
$delete_node = $all_node;
$insert_data = [];//准备写入的
$tree_data = $_POST['nodes'];

foreach ($tree_data as $node) {
    jiexi_node_array($node);
}

if( count($delete_node)){
    $delete_ids = array_map(function($v){
         return $v['id'];
    },$delete_node);
    $sql = " delete from site_type where id in ('".implode("','",$delete_ids)."') limit ".count($delete_ids);
    $DB->query($sql);
}


if (count($insert_data)) {
    $sql = "insert into site_type " . batch_write_sql($insert_data, ['id', 'name', 'pid', 'checked']);
    $DB->query($sql);
}
echo 'ok';


//解析ztree插件传递的tree数据,修改已经有的，存储准备写入的
function jiexi_node_array($node)
{
    global $DB, $all_node, $insert_data,$delete_node;

    $node['checked'] = ($node['checked'] == 'false' ? 0 : 1);

    $id = $node['id'];
    $name = $node['name'];
    $checked = $node['checked'];

    if (count($all_node[$id])) {
        $old_data = $all_node[$id];
        unset($delete_node[$id]);
        $old_data['checked']= intval($old_data['checked']);

        $update_field = [];


        if ($name != $old_data['name']) {
            $update_field[] = " `name`='$name' ";
        }


        if ( $checked != $old_data['checked'] ) {

            $update_field[] = " `checked`='$checked' ";
        }

        if (count($update_field)) {
            $sql = "update site_type set " . implode(',', $update_field) . " where id='$id' limit 1";

            $DB->query($sql);
        }

    } else {
        $insert_data[$node['id']] = [
            'id' => $id,
            'name' => $name,
            'pid' => intval($node['pId']),
            'checked' => $checked,
        ];
    }

    if (count($node['children'])) {
        foreach ($node['children'] as $new_node) {
            jiexi_node_array($new_node);
        }
    }
}
