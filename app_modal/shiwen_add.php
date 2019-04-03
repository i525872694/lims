<?php
include '../huayan/ahlims.php';
$lims = new DefaultApp();
if($_POST){
    $month=$_POST['month']>10?$_POST['month']:'0'.$_POST['month'];
    $day=$_POST['day']>10?$_POST['day']:'0'.$_POST['day'];
    $riqi=$month.'-'.$day;
    $sql="INSERT INTO `shiwen_yiqi`(`id`, `yiqi_id`, `year`, `riqi`, `shiwen`, `wendu`, `xm_name`, `jc_start`, `jc_end`, `status`, `shiyong_name`, `jc_name`, `bz`) VALUES ('','$_POST[id]','$_POST[year]','$riqi','$_POST[shiwen]','$_POST[wendu]','$_POST[xm_name]','$_POST[jc_start]','$_POST[jc_end]','$_POST[status]','$_POST[shiyong_name]','$_POST[jc_name]','$_POST[bz]')";
    $DB->query($sql);
    echo "<script>location.href='shiwen_yiqi.php?id=$id&year=$year'</script>";
    exit;
}else{
    $id=$_GET['id'];
    $year=$_GET['year'];
    $sql="select `yq_mingcheng`,`yq_neibubh` from `yiqi` where `id`='$id'";
    $info=$DB->fetch_one_assoc($sql);
    $lims->disp('app_modal/shiwen_add');
}
?>
