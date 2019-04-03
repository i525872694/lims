<?php
include '../temp/config.php';
include '../huayan/ahlims.php';
// error_reporting(E_ALL);
// ini_set('display_errors',1);
if($_GET['ajax']){
    $users = [];
    $sql = "SELECT * FROM `users` LIMIT 20";
    $query = $DB->query($sql);
    while($row=$DB->fetch_assoc($query)){
        $users[] = $row;
    }
    die(json_encode(array('total'=>20,'rows'=>$users)));
}
$lims = new DefaultApp();
$lims->disp('app_modal/user_manage');