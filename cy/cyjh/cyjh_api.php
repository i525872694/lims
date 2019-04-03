<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-24
 * Time: 下午7:09
 */
include __DIR__ .'/../../temp/config.php';


$sql = "select * from cy_jh";
$rows = $DB->query($sql);
$result = [];
while ($row = $DB->fetch_assoc($rows))
{
    $timestamp = strtotime($row['jhdate']);
    $date = date("m-d",$timestamp);
    $row['year'] = date("Y",$timestamp);
    $row['m'] = date("m",$timestamp);
    $result[$row['sname'] ]['sname']=$row['sname'];
    $result[$row['sname'] ]['year']=$row['year'];
    $result[$row['sname'] ]['id']=$row['id'];

    $result[$row['sname'] ]['map'][$date]=$row['id'];

    $result[$row['sname'] ][$row['m']]=$date;

}
$result = array_values($result);
//$mouth = range(1,12);
//foreach ($result as $sname=>$info)
//{
//    foreach ($mouth as $m)
//    {
//        $m = str_pad($m,2,'0',STR_PAD_LEFT);
//        if(!array_key_exists($m,$info)){
//
//        }
//    }
//}

header("Content-type: application/json");
echo json_encode($result);