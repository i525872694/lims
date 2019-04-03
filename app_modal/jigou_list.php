<?php
//用户信息列表
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//接收双击表格修改ajax传的数据 更改数据库
if($_GET['edit']==1){

    exit;
}
//表格数据
if($_GET['ajax']){
    $hubinfos = [];
    if($u['is_zz']=='1'){
        $where="";
    }else{
        $where="where `id`=$u[fzx_id]";
    }
    $sql = "SELECT * from `hub_info` $where";
    $query = $DB->query($sql);
    $i='1';
    while($row=$DB->fetch_assoc($query)){
        $row['xuhao']=$i;
        if($row['id']==$u['fzx_id']){
            $row['xiugai']="修改";
        }
        $hubinfos[] = $row;
        $i++;
    }
    die(json_encode($hubinfos));
}
$table_key = 'jigou_list';
$ag_grid_data = jiexi_ag_grid_table($table_key);
$t = json_decode($ag_grid_data['columnDefs'],true) ;
//第一列阻止更改
$t[0]['editable']='false';
$t[1]['cellClass']='jumpLink';
//修改点击跳转
$t[9]['editable']='false';
$t[9]['cellClass']='jumpLink';
if($u[is_zz]!=1){
  $t[9]['cellClass']='green icon-edit bigger-130';
}
$ag_grid_data['columnDefs']=json_encode($t);
$grid= json_decode($ag_grid_data['gridOptions'],true) ;
//添加边框线
$grid['rowStyle']=array('border-bottom'=>'1px solid black');
//自动分页 根据窗口大小
$grid['pagination']='true';
$grid['paginationAutoPageSize']='true';
$ag_grid_data['gridOptions']=json_encode($grid);
$uid=$u[fzx_id];
$ui_ag_grid = $lims->temp('ag_grid/ui_ag_grid');
// $mainhtml = $this->temp("jiexi_demo", get_defined_vars());
$lims->disp('app_modal/jigou_list');
