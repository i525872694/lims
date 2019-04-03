<?php
//分析仪器使用记录表
include '../huayan/ahlims.php';
$lims = new DefaultApp();
$trade_global['daohang'][]	= array('icon'=>'','html'=>'温湿度总览表','href'=>$rooturl.'/app_modal/shiwen.php');

//接收双击表格修改ajax传的数据 更改数据库
if($_GET['edit']==1){
    /*$field=$_GET['sql_field'];
    $id=$_GET['sql_id'];
    $sql_content=$_GET['sql_content'];
    if($field=='hub_name'){
            echo $sql="select `id` from `hub_info` where `hub_name`='$sql_content'";
            $row=$DB->fetch_one_assoc($sql);
            echo $sql="UPDATE `yiqi` SET `fzx_id`='$row[id]' where `id`='$id'";
            $DB->query($sql);
            exit;
        }
    echo $sql="update `yiqi` set `$field`='$sql_content' where `id`='$id'";
    $DB->query($sql);
    exit;*/
}
//仪器删除
if($_GET['del']){
    $id=$_GET['id'];
    $uid=$_GET['uid'];
    $year=$_GET['year'];
    $sql="DELETE FROM `shiwen_yiqi` WHERE id='$id'";
    $DB->query($sql);
    echo "<script>location.href='shiwen_yiqi.php?id=$uid&year=$year'</script>";
    exit;
}
//表格数据
if($_GET['ajax']){
    $shiwen_yiqis = [];
    $id=$_GET['id'];
    $year=$_GET['year'];
    $sql = "select * from `shiwen_yiqi`  where `yiqi_id`='$id' and `year`='$year'";
    $query = $DB->query($sql);
    $i='1';
    while($row=$DB->fetch_assoc($query)){
        $row['xuhao']=$i;
        $shiwen_yiqis[] = $row;
        $i++;
    }
    die(json_encode($shiwen_yiqis));
}
$id=$_GET['id'];
$year=$_GET['year'];
$sql="select `yq_mingcheng`,`yq_neibubh` from `yiqi` where `id`=$id";
$info=$DB->fetch_one_assoc($sql);
$yiqi_name=$info['yq_mingcheng'];
$yiqi_bianhao=$info['yq_neibubh'];
$table_key = 'sw_yiqi';
$ag_grid_data = jiexi_ag_grid_table($table_key);
$t = json_decode($ag_grid_data['columnDefs'],true) ;
//第一列阻止更改
$t[0]['editable']='false';
//修改点击跳转
$t[11]['editable']='false';
$t[11]['cellClass']='jumpLink';
$t[11]['cellClass']='green icon-edit bigger-130';
//删除
$t[12]['editable']='false';
$t[12]['cellClass']='jumpLink';
$t[12]['cellClass']='red icon-remove bigger-140';
$ag_grid_data['columnDefs']=json_encode($t);
$grid= json_decode($ag_grid_data['gridOptions'],true) ;

//添加边框线
$grid['rowStyle']=array('border-bottom'=>'1px solid black');
//自动分页 根据窗口大小
$grid['pagination']='true';
$grid['paginationAutoPageSize']='true';
$ag_grid_data['gridOptions']=json_encode($grid);
$ui_ag_grid = $lims->temp('ag_grid/ui_ag_grid');
// $mainhtml = $this->temp("jiexi_demo", get_defined_vars());
$lims->disp('app_modal/shiwen_yiqi');
