<?php
include './temp/config.php';
include './huayan/ahlims.php';
unset($_SESSION['daohang']);//清除导航栏
$fzx_id = FZX_ID;
$modi_pwd = $_GET['modi_pwd'] ? 1 : 0;//判断是否需要修改密码
$lims = new DefaultApp();
echo $lims->temp('index1.html');