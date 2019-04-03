<?php
//是否有室温度要求列表
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//########导航
//$trade_global['daohang'][]	= array('icon'=>'','html'=>'分析仪器使用记录表','href'=>$current_url);
$_SESSION['daohang']['assay_form']	= $trade_global['daohang'];
//接收双击表格修改ajax传的数据 更改数据库
if($_GET['edit']==1){

}
//仪器删除
if($_GET['del']){
    $id=$_GET['id'];
    $sql="DELETE FROM `yiqi` WHERE id='$id'";
    $DB->query($sql);
    echo "<script>location.href='yiqi_list.php'</script>";
    exit;
}
//表格数据
if($_GET['ajax']){
    $shiwen_yiqis = [];
    if($u['is_zz']=='1'){
        $where=$_GET['fzx']!=0?"and `yiqi`.`fzx_id`='$_GET[fzx]'":"";
    }else{
        $where="and `yiqi`.`fzx_id`='$u[fzx_id]'";
    }
    $sql = "SELECT `yiqi`.`id`,`yq_mingcheng`,`yq_xinghao`,`yq_neibubh`,`wendu_max`,`wendu_min`,`shidu_max`,`shidu_min`,`hub_info`.`hub_name` from `yiqi` left join `hub_info` on `yiqi`.`fzx_id`=`hub_info`.`id` where `is_shiwen`='1' $where";
    $query = $DB->query($sql);
    $i='1';
    $ii="查看及添加";
    while($row=$DB->fetch_assoc($query)){
        $row['xuhao']=$i;
        $row['chakan']=$ii;
        $shiwen_yiqis[] = $row;
        $i++;
    }
    die(json_encode($shiwen_yiqis));
}
if($u['is_zz']=='1'){
    $where='';
}else{
    $where="and `fzx_id`='$u[fzx_id]'";
}
$sql="select count(id) from yiqi where `is_shiwen`='1'";
$row=$DB->fetch_one_assoc($sql);
$num=$row['count(id)'];
$table_key = 'shiwen';
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
//查看详情点击跳转
$t[9]['editable']='false';
$t[9]['cellClass']='jumpLink';
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



//tab 文件管理
$type=$_GET['type']?$_GET['type']:'shiwen';
$tab_active=$_GET['tab']?$_GET['tab']:0;
if($type!='shiwen'){
    //如果表里没有当前分中心数据，自动创建
    $info=$DB->fetch_one_assoc("select * from `jiance_files` where `fzx_id`=$u[fzx_id]");
    if(!$info['id']){
        $query=$DB->query("insert into `jiance_files`(`fzx_id`)values('$u[fzx_id]')");
    }
    $hide='is_hide';
    $title=$_GET['title'];
    $mysql_table='jiance_files';
    $id=$info['id'];
    //循环数据 查看
    $info=$DB->fetch_one_assoc("select `id`,`{$type}` from `jiance_files` where `fzx_id`='$u[fzx_id]'");
    $file_arr=json_decode($info[$type],true);
    $i=1;
    foreach($file_arr as $k=>$v){
        $files=$caozuo='';
        $caozuo="<a class='glyphicon glyphicon-cloud-upload' onclick=\"add_file('$k')\" title='添加文件'></a> |";
        $caozuo.="<a class='green icon-edit bigger-130' onclick=\"xiugai('$k',this)\" title='修改'></a> |";
        $caozuo.="<a class='red icon-remove bigger-130' onClick=\"del('$mysql_table','$id','$k','$type')\" title='删除'></a> ";
        
        $path=$rooturl.'/app_modal/upload_file';
        foreach($v['file'] as $f_k=>$f_v){
            $url=$path.'/'.$f_v['newname'];
            $files.="<center><a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a>";
            $files.="<a class='red icon-remove bigger-140' onclick=\"del_file('$mysql_table','$id','$k','$f_k','$type')\" title='删除文件'></a></center>";
        }
    
        $files_lines.=<<<EOF
        <tr>
            <td>$i</td>
            <td class='bdname'>$v[bdname]</td>
            <td class='file'>$files</td>
            <td class='beizhu'>$v[beizhu]</td>
            <td>$caozuo</td>
        </tr>
EOF;
        $i++;
    }
    
    $mysql_ziduan=$type;
    $content_str='files_content'.$tab_active;
    $$content_str=$lims->temp('app_modal/jigou_file');
}


$lims->disp('app_modal/shiwen');
