<?php
//用户信息列表
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//接收双击表格修改ajax传的数据 更改数据库
if($_GET['edit']==1){
    $field=$_GET['sql_field'];
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
    exit;
}
//仪器删除
if($_GET['del']){
    $id=$_GET['id'];
    $sql="DELETE FROM `yiqi` WHERE id='$id'";
    $DB->query($sql);
    if($_GET['url']){
        echo "<script>location.href='$rooturl/main.php'</script>";
    }
    echo "<script>location.href='yiqi_list.php'</script>";
    exit;
}
//表格数据
if($_GET['ajax']){
    $yiqis = [];
    if($u['is_zz']=='1'){
        $where=$_GET['fzx']!=0?"where `yiqi`.`fzx_id`='$_GET[fzx]'":"";
    }else{
        $where="where `yiqi`.`fzx_id`='$u[fzx_id]'";
    }
    $sql = "SELECT `yiqi`.`id`,`yiqi`.`fzx_id`,`yq_mingcheng`,`yq_xinghao`,`yiqi`.`yq_chucangbh`,`yq_jiage`,`yq_neibubh`,`yq_zzcangjia`,`yiqi`.`yq_scriqi`,`yq_gouzhirq`,`yq_jiandingriqi`,`yq_baofei`,`hub_info`.`hub_name` from `yiqi` left join `hub_info` on `yiqi`.`fzx_id`=`hub_info`.`id` $where";
    $query = $DB->query($sql);
    $i='1';
    while($row=$DB->fetch_assoc($query)){
      if($row['fzx_id']==$u['fzx_id']&&$u['is_zz']=='1'){
          $row['xiugai']="修改";
          $row['del']="删除";
      }
      $row['red']=0;
      if(!empty($row['yq_jiandingriqi'])){
          if(strtotime("+1 month")>strtotime($row['yq_jiandingriqi'])){
              $row['red']=1;
          }
      }
        $row['xuhao']=$i;
        $yiqis[] = $row;
        $i++;
    }
    die(json_encode($yiqis));
}
if($u['is_zz']=='1'){
    $where='';
}else{
    $where="where `fzx_id`='$u[fzx_id]'";
}
$sql="select count(id) from yiqi $where";
$row=$DB->fetch_one_assoc($sql);
$num=$row['count(id)'];
$table_key = 'yiqi';
$ag_grid_data = jiexi_ag_grid_table($table_key);
$t = json_decode($ag_grid_data['columnDefs'],true) ;
//第一列阻止更改
$t[0]['editable']='false';
//分中心下拉框
$sql="select `id`,`hub_name` from `hub_info`";
$query = $DB->query($sql);
while($row=$DB->fetch_assoc($query)){
    $fzx_arr[] = $row;
	$fzx_arr2[]=$row['hub_name'];
}
foreach($fzx_arr as $k=>$v){
    if($v[id]==$_GET[fzx]){
        $checkbox="selected='selected'";
    }else{
        $checkbox='';
    }
    $fzx_select.="<option value='$v[id]' $checkbox>$v[hub_name]</option>";
}
if($u['is_zz']=='1'){
    $select="选择分中心:<select name='' id='fzx' onchange='fzx_list()'>
            <option value='0'>全部</option>
            '$fzx_select'
        </select>";
}
$fzx_arr=array('values'=>$fzx_arr2);
$t[11]['cellEditor']='select';
$t[11]['cellEditorParams']=$fzx_arr;
//修改点击跳转
$t[12]['editable']='false';
$t[12]['cellClass']='jumpLink';
if($u['is_zz']!='1'){
  $t[12]['cellClass']='green icon-edit bigger-130';
}
//删除
$t[13]['editable']='false';
$t[13]['cellClass']='jumpLink';
if($u['is_zz']!='1'){
  $t[13]['cellClass']='red icon-remove bigger-140';
}
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
$lims->disp('app_modal/yiqi_list');
