<?php
include ('../temp/config.php');
$fzx_id = FZX_ID;
$wz_status=array('全部','有效','已失效');
if(''==$_GET['_wz_type'])	$_GET['_wz_type']='有效';
if(''==$_GET['wz_type'])	$_GET['wz_type']='标准溶液';
$wz_type_subdivide = empty($_GET['wz_type_subdivide']) ? '全部' : $_GET['wz_type_subdivide'];
//详细分类
if($wz_type_subdivide=='全部'){
	$wz_type_subdivide_sql = '';
	//显示全部类别的时候需要显示每个物品的细分类别
	$wz_type_subdivide_title = "<th style='width:10%;'>类别</th>";
}else{
	$wz_type_subdivide_sql = " AND `wz_type_subdivide` = '$wz_type_subdivide'";
	$wz_type_subdivide_title = '';
}
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$current_url.$tabs));

foreach ($wz_status as $i => $value) {
	$selected = ($value==$_GET['_wz_type']) ? 'selected':'';
	$_wz_types.='<option '.$selected.' value="'.$value.'">'.$value.'</option>';
}
$wz_type_="AND `wz_type`='{$_GET['wz_type']}'";
//物质名称
if('' == $_GET['wz_name'] || '全部' == $_GET['wz_name']){
	// $wz_name = '全部';
	$_wz_name='';
}else{
	$wz_name = $_GET['wz_name'];
	$_wz_name="AND `wz_name`='{$_GET['wz_name']}'";
}
if(!empty($_GET['wz_name']) && $_GET['wz_name']!='全部'){
	$wz_name_selected = "<option selected>$_GET[wz_name]</option>";
}else{
	$wz_name_selected = "";
}
//显示详细分类默认选中项
if(!empty($_GET['wz_type_subdivide'])){
	$wz_type_subdivide_selected = "<option value='$_GET[wz_type_subdivide]' selected>$_GET[wz_type_subdivide]</option>";
}else{
	$wz_type_subdivide_selected = '';
}
//国标号
if('' == $_GET['guobiao'] || '全部' == $_GET['guobiao']){
	$guobiao = '全部';
	$_guobiao='';
}else{
	$guobiao = $_GET['guobiao'];
	$_guobiao="AND `guobiao`='{$_GET['guobiao']}'";
}
//物质状态 是否有效
switch($_GET['_wz_type']){
	case "有效":
		$__wz_type="AND `time_limit`>curdate()";
		break;
	case "已失效":
		$__wz_type="AND `time_limit`<=curdate()";
		break;
	case "全部":
		$__wz_type='';
		break;
}
$wz_type = $_GET['wz_type'];
//下载
header("Content-type: application/octet-stream;charset=gbk");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename={$_GET['wz_type']}.xls");

$warn_time=strtotime("+4 week");
$sql = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' $wz_type_ $__wz_type $_wz_name $_guobiao $wz_type_subdivide_sql ORDER BY  CONVERT( `wz_name` USING gbk ),`time_limit` ASC";
$query=$DB->query($sql);
while($r=$DB->fetch_assoc($query)){
	$del = '<a href="bzwz.php?action=删除&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'" onclick="if(confirm(\'确定要删除吗？\'))return true;else return false;">删除</a>';
	$edi = '<a href="bzwz.php?action=修改&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'">修改</a>';
	$ruk = '<a href="bzwz.php?action=入库&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'&wz_name='.$r['wz_name'].'">入库</a>';
	$chu = '<a href="bzwz.php?action=出库&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'&wz_name='.$r['wz_name'].'">出库</a>';
	$kan = '<a href="bzwz.php?action=查看&wz_id='.$r['id'].'&wz_type='.$r['wz_type'].'">查看详情</a>';
	$operation=(!$u['bzwz_manage']) ? $kan : $ruk.' | '.$chu.' | '.$edi.' | '.$del;
	//显示全部类别的时候需要显示每个物品的细分类别
	if($wz_type_subdivide=='全部'){
		$wz_type_subdivide_lines = "<td style='min-wide:70px;'>{$r['wz_type_subdivide']}</td>";
	}else{
		$wz_type_subdivide_lines = '';
	}
	$time=strtotime($r['time_limit']);
	if($time<=$warn_time){
		$time_limit="<td style='color:red'>{$r['time_limit']}</td>";
	}else{
		$time_limit="<td>{$r['time_limit']}</td>";
	}
	if($r['amount']<=$r['limit_num']){
		$num="<td style='color:red'>{$r['amount']} {$r['unit']}</td>";
	}else{
		$num="<td>{$r['amount']} {$r['unit']}</td>";
	}
	if($_GET['_wz_type']=='有效')
	{	
		if( $r['amount']>=0){
			$xuhao++;
			$lines.=temp('bzwz/bzwz_list_line_download');
		}
	}
	else{
		$xuhao++;
		$lines.=temp('bzwz/bzwz_list_line_download');
	}
}
$yearOption = '<option value="'.date('Y').'" selected>'.date('Y').'</option>';
if($_GET['wz_type']=='标准溶液'){
	$$bzwz_content = temp('bzwz/bzwz_list_content_download');
}else{
	$$bzwz_content = temp('bzwz/bzwz_list_content_download');
}
disp('bzwz/bzwz_list_download');
?>