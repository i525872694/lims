<?php
include '../huayan/ahlims.php';
$lims = new DefaultApp();
if($_POST){
    $sql="UPDATE `shiwen_yiqi` SET `shiwen`='$_POST[shiwen]',`wendu`='$_POST[wendu]',`xm_name`='$_POST[xm_name]',`jc_start`='$_POST[jc_start]',`jc_end`='$_POST[jc_end]',`status`='$_POST[status]',`shiyong_name`='$_POST[shiyong_name]',`jc_name`='$_POST[jc_name]',`bz`='$_POST[bz]' WHERE `id`=$_POST[id]";
    $DB->query($sql);
    $uid=$_POST['uid'];
    $year=$_POST['year'];
    echo "<script>location.href='shiwen_yiqi.php?id=$uid&year=$year'</script>";
}else{
    $id=$_GET['id'];
    $uid=$_GET['uid'];
    $year=$_GET['year'];
    $sql="select * from `shiwen_yiqi` where `id`='$id'";
    $info=$DB->fetch_one_assoc($sql);
    $lims->disp('app_modal/shiwen_yiqi_update');
}
?>
