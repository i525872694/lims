<?php
include "../../temp/config.php";
if($_POST['action']=='yq_type'){
    $sql_yq_type = "SELECT id FROM `yq_type` WHERE yq_type_name='{$_POST['name']}'";
    $type_info = $DB->fetch_one_assoc($sql_yq_type);
    if(!empty($type_info)){
        $id = $type_info['id'];
        $fac_arr_query= $DB->query("SELECT * FROM `yq_autoload_storeroom` WHERE yq_type_id='{$id}'");
        $factory_id_arr=[];
        while($fac_res = $DB->fetch_assoc($fac_arr_query)){
            if(!in_array($fac_res['yq_factory_id'],$factory_id_arr)){
                $factory_id_arr[]=$fac_res['yq_factory_id'];
                $fac_id=$fac_res['yq_factory_id'];
                $factory_name = $DB->fetch_one_assoc("SELECT factory_name FROM `yq_factory` WHERE id='{$fac_id}' ");
                $factory_op .= "<option value='{$fac_id}'>{$factory_name['factory_name']}</option>";
            }
            
        }
        $arr_type = [
            'info'=>'1',
            'values'=>$id,
            'options'=>$factory_op
        ];
        $type_json = json_encode($arr_type);
        echo $type_json;
    }else{
        $sql_add = "INSERT INTO `yq_type` (yq_type_name,is_load) VALUES('".$_POST['name']."','1')";
        $add_num = $DB->query($sql_add);
        $id = $DB->insert_id();
        $arr_type = [
            'info'=>'2',
            'values'=>$id
        ];
        $type_json = json_encode($arr_type);
        echo $type_json;
    }
}
if($_POST['action']=='yq_fac'){
    $sql_yq_fac = "SELECT * FROM `yq_autoload_storeroom` AS s LEFT JOIN `yq_factory` AS f on s.yq_factory_id=f.id WHERE s.yq_type_id='{$_POST['yq_type_id']}' AND f.factory_name='{$_POST['name']}'";
    $type_info = $DB->fetch_one_assoc($sql_yq_fac);
    if(!empty($type_info)){
        $yq_factory_id = $type_info['yq_factory_id'];
        $mode_arr_query = $DB->query("SELECT * FROM `yq_autoload_storeroom` WHERE yq_type_id='{$_POST['yq_type_id']}' AND yq_factory_id='{$yq_factory_id}'");
        while($mode_res = $DB->fetch_assoc($mode_arr_query)){
            $id = $mode_res['yq_factory_id'];
            $yq_mode_name = $mode_res['yq_mode_name'];
            $mode_options .= "<option value='{$yq_mode_name}'>$yq_mode_name</option>"; 
        }
        $arr_mode = [
            'info'=>'1',
            'values'=>$id,
            'options'=>$mode_options
        ];
        $json_mode = json_encode($arr_mode);
        echo $json_mode;
    }else{
        $sql_add = "INSERT INTO `yq_factory` (factory_name) VALUES('".$_POST['name']."')";
        $add_num = $DB->query($sql_add);       
        $id = $DB->insert_id();
        $arr_mode = [
            'info'=>'2',
            'values'=>$id
        ];
        $json_mode = json_encode($arr_mode);
        echo $json_mode;
    }
}
if($_POST['action']=='yq_mode'){
    $sql_yq_mode = "SELECT * FROM `yq_autoload_storeroom` AS s WHERE s.yq_type_id='{$_POST['yq_type_id']}' AND s.yq_factory_id='{$_POST['yq_factory_id']}' AND s.yq_mode_name='{$_POST['name']}'";
    $mode_info = $DB->fetch_one_assoc($sql_yq_mode);
    if(!empty($mode_info)){
        $yq_mode_name = $mode_info['yq_mode_name'];
        $load_way_options="<option value='1'>pdf</option>";
        $arr_load_way = [
            'info'=>1,
            'values'=>$yq_mode_name,
            'options'=>$load_way_options
        ];
        $json_load_way=json_encode($arr_load_way);
        echo $json_load_way;
    }else{
        $values = $_POST['name'];
        $arr_load_way = [
            'info'=>2,
            'values'=>$values
        ];
        $json_load_way=json_encode($arr_load_way);
        echo $json_load_way;
    }
}
if($_POST['action']=='new_storeroom'){
    $sql_new_storeroom="INSERT INTO `yq_autoload_storeroom` (yq_factory_id,yq_type_id,yq_mode_name,load_way,load_file,printer) VALUES('{$_POST['new_factory_id']}','{$_POST['new_type_id']}','{$_POST['new_mode_name']}','{$_POST['new_loadway_name']}','{$_POST['new_loadfile_name']}','{$_POST['new_prints_name']}')";
    $new_storeroom = $DB->query($sql_new_storeroom);
    $storeroom_id=$DB->insert_id();
    echo $storeroom_id;
}
?>