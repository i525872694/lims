<?php
//用户信息列表
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//接收双击表格修改ajax传的数据 更改数据库
if($_GET['edit']==1){
    $field=$_GET['sql_field'];
    $uid=$_GET['sql_uid'];
    $users_arr=array('userid','sex','hub_name');
    $hn_users_arr=array('csrq','whcd','zc','zy','gwsj','jsnx','gw','bz');
    $sql_content=$_GET['sql_content'];
    if(in_array($field,$users_arr)){
        if($field=='hub_name'){
            echo $sql="select `id` from `hub_info` where `hub_name`='$sql_content'";
            $row=$DB->fetch_one_assoc($sql);
            echo $sql="UPDATE `users` SET `fzx_id`='$row[id]' where `id`='$uid'";
            $DB->query($sql);
            exit;
        }
        echo $sql="update `users` set `$field`='$sql_content' where `id`='$uid'";
        $DB->query($sql);
    }
    if(in_array($field,$hn_users_arr)){
        echo $sql="update `hn_users` set `$field`='$sql_content' where `uid`='$uid'";
        $DB->query($sql);
    }
    exit;
}
//用户信息删除
if($_GET['del']){
    $uid=$_GET['uid'];
    $sql="DELETE FROM `users` WHERE id='$uid'";
    $sql2="DELETE FROM `hn_users` WHERE uid='$uid'";
    $DB->query($sql);
    $DB->query($sql2);
    echo "<script>location.href='user_list.php'</script>";
    exit;
}
//表格数据
if($_GET['ajax']){
    $users = [];
    if($u['is_zz']=='1'){
        $where=$_GET['fzx']!=0?"and `u`.fzx_id='$_GET[fzx]'":"";
    }else{
        $where="and `u`.`fzx_id`='$u[fzx_id]'";
    }
    $sql = "SELECT `h`.*, `u`.`userid`,`u`.`fzx_id`,`u`.`sex`,`info`.`hub_name` FROM `hn_users` AS `h` RIGHT JOIN `users` AS `u` ON `h`.`uid`=`u`.`id` LEFT JOIN `hub_info` as `info` on `u`.`fzx_id`=`info`.`id` where `h`.`uid`>0.$where order by `u`.`id` asc";
    $query = $DB->query($sql);
    $i='1';
    while($row=$DB->fetch_assoc($query)){
      if($u['is_zz']=='1'&&$row['fzx_id']==$u['fzx_id']){
        $row['xiugai']="修改";
        $row['del']="删除";
      }
        $row['xuhao']=$i;
        $users[] = $row;
        $i++;
    }
    die(json_encode($users));
}
$table_key = 'hn_users';
$ag_grid_data = jiexi_ag_grid_table($table_key);
$t = json_decode($ag_grid_data['columnDefs'],true) ;
//第一列阻止更改
$t[0]['editable']='false';
//性别下拉框
$sex_arr=array('values'=>array('男','女'));
$t[3]['cellEditor']='select';
$t[3]['cellEditorParams']=$sex_arr;
//学历下拉框
$xueli_arr=array('values'=>array('高中','中专','大专','大学','硕士','博士','其他'));
$t[6]['cellEditor']='select';
$t[6]['cellEditorParams']=$xueli_arr;
//职称下拉框
$zhicheng_arr=array('values'=>array('研究员级高级工程师','高级工程师','工程师','助理工程师','高级技师','技师','高级工','中级工','初级工'));
$t[5]['cellEditor']='select';
$t[5]['cellEditorParams']=$zhicheng_arr;
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
$t[17]['cellEditor']='select';
$t[17]['cellEditorParams']=$fzx_arr;
//修改点击跳转
$t[19]['editable']='false';
$t[19]['cellClass']='jumpLink';
if($u['is_zz']!='1'){
  $t[19]['cellClass']='green icon-edit bigger-130';
}
//删除
$t[20]['editable']='false';
$t[20]['cellClass']='jumpLink';
if($u['is_zz']!='1'){
  $t[20]['cellClass']='red icon-remove bigger-140';
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
$lims->disp('app_modal/user_list');
