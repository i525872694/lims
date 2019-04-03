<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-11
 * Time: 下午5:40
 */
function all_site_type_data($checked_list=[])
{
    global $DB;
    $sql ="select * from site_type ORDER by id asc ";
    $rows = $DB->query($sql);
    $all_node = [];
    while ($row = $DB->fetch_assoc($rows))
    {
        if(@in_array($row['id'],$checked_list)){
            $row['check']=true;//选中状态
        }else{
            $row['check']=false;
        }
        //$row['checked']= $row['check'];
        $all_node[$row['id']]=$row;
    }
    return $all_node;
}

// 将数据按照所属关系封装   类似 arr2tree
function treeArray($data,$fid)
{
    $result = array();
    //定义索引数组，用于记录节点在目标数组的位置，类似指针
    $p = array();

    foreach($data as $val)
    {
        if($val['pid'] == $fid)
        {
            $i = count($result);
            $result[$i] = isset($p[$val['id']])? array_merge($val,$p[$val['id']]) : $val;
            $p[$val['id']] = & $result[$i];
        } else {
            $i = count($p[$val['pid']]['children']);
            $p[$val['pid']]['children'][$i] = $val;
            $p[$val['id']] = & $p[$val['pid']]['children'][$i];
        }
    }

    return $result;
}
