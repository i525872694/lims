<?php
include '../temp/config.php';

$id = get_int($_GET['id']);
if(empty($id)){
    echo 'error';
}else{
    $time = date('Y-m-d',time());
    $return = array();
    $return['qz'] = $u['userid'];
    $return['qz_date'] = $time;
    $DB->query("update `cy` set `sc_qz`='$u[userid]',`sc_qz_date`='$time'");
    echo json_encode($return);
}