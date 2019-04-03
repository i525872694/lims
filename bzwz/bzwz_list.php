<?php
/**
 * 功能：标准溶液，标准样品列表页面
 * 作者: Mr Zhou
 * 日期: 2014-10-17
 * 描述: 标准溶液，标准样品管理
*/
include ('../temp/config.php');
$fzx_id = FZX_ID;
$wz_status=array('全部','有效','已失效');
if(''==$_GET['_wz_type'])	$_GET['_wz_type']='有效';
if(''==$_GET['wz_type'])	$_GET['wz_type']='标准溶液';
if($_GET['type']=='jdwz'){
	$_GET['wz_type']='剧毒物质';
}
$wz_type_subdivide = empty($_GET['wz_type_subdivide']) ? '全部' : $_GET['wz_type_subdivide'];
//总中心赋予分中心筛选的权利
if($u['ls_zz']=='1'){
	$fzx_select = '查看分中心：<select  id="fzx_id" name="fzx_id" style="min-width:150px" class="chosen-select">';
	$sql = "SELECT `id` , `hub_name` FROM `hub_info`";
	$re = $DB->query($sql);
	while($data = $DB->fetch_assoc($re)){
		if($data['id'] == $_GET['fzx_id']){
			$fzx_select .= "<option  value='{$data['id']}' selected>{$data['hub_name']}</option>";
		}
		$fzx_select .= "<option value='{$data['id']}'>{$data['hub_name']}</option>";
	}
	$fzx_select .= "</select>";
	if(!empty($_GET['fzx_id'])){
		$fzx_sql = " `fzx_id` = '{$_GET['fzx_id']}'";
	}else{
		$fzx_sql = "`fzx_id`='$fzx_id'";
	}
}else{
	$fzx_select = '<input type="hidden" id="fzx_id" name="fzx_id" value="'.$u['fzx_id'].'">';
	$fzx_sql = "`fzx_id`='$fzx_id'";
} 
//详细分类
if($wz_type_subdivide=='全部'){
	$wz_type_subdivide_sql = '';
	//显示全部类别的时候需要显示每个物品的细分类别
	$wz_type_subdivide_title = "<th style='width:10%;'>类别</th>";
}else{
	$wz_type_subdivide_sql = " AND `wz_type_subdivide` = '$wz_type_subdivide'";
	$wz_type_subdivide_title = '';
}
if($_GET['wz_type']=='标准溶液'){
	$tab_active=0;
	$tabs = '#tabs-1';
	$bzwz_content = 'tabs_1_bzry';
}else if($_GET['wz_type']=='标准样品'){
	$tab_active=1;
	$tabs = '#tabs-2';
	$bzwz_content = 'tabs_2_bzyp';
}else if($_GET['wz_type']=='剧毒物质'){
	$tab_active=2;
	$tabs = '#tabs-3';
	$bzwz_content = 'tabs_3_jdwz';	
}else{
	$tabs="#tabs-4";
	$bzwz_content='tabs_4_djb';
}
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$current_url.$tabs));

$_wz_types=select_check($_GET['_wz_type'],$wz_status);
$wz_type_="AND `wz_type`='{$_GET['wz_type']}'";
//物质名称
if('' == $_GET['wz_name'] || '全部' == $_GET['wz_name']){
	// $wz_name = '全部';
	$_wz_name='';
}else{
	$wz_name = $_GET['wz_name'];
	$_wz_name="AND `wz_name`='{$_GET['wz_name']}'";
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
// 得到 名称 详细分类 的下拉菜单
$sql="SELECT distinct `wz_name` , `wz_type_subdivide` FROM  `bzwz` WHERE $fzx_sql $wz_type_ $__wz_type $_guobiao ORDER BY CONVERT( `wz_name` USING gbk ),`time_limit` ASC";
$query=$DB->query($sql);
$wz_name_arr=$wz_type_subdivide_arr=array();
$wz_name_arr[]='全部';
$wz_type_subdivide_arr[]='全部';
while($row=$DB->fetch_assoc($query)){
	$wz_name_arr[]=$row['wz_name'];
	if(!empty($row['wz_type_subdivide'])){
		$wz_type_subdivide_arr[] = trim($row['wz_type_subdivide']);
	}
}
$ryline=select_check($_GET['wz_name'],$wz_name_arr);
$wz_type_subdivide_arr = array_unique($wz_type_subdivide_arr);
$wz_type_subdivide_line=select_check($_GET[wz_type_subdivide],$wz_type_subdivide_arr);
$tixing_day=$DB->fetch_one_assoc("select `module_value2` from `n_set` where `fzx_id`=$u[fzx_id] and `module_name`='bzwz_tixing'");
$tixing_day=$tixing_day['module_value2']?$tixing_day['module_value2']:30;
$warn_time=strtotime("+$tixing_day day");
$sql = "SELECT * FROM `bzwz` WHERE $fzx_sql $wz_type_ $__wz_type $_wz_name $_guobiao $wz_type_subdivide_sql AND `wz_status` = '0'  ORDER BY  CONVERT( `wz_name` USING gbk ),`time_limit` ASC";
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
			$lines.=temp('bzwz/bzwz_list_line');
		}
	}
	else{
		$xuhao++;
		$lines.=temp('bzwz/bzwz_list_line');
	}
}
$yearOption = '<option value="'.date('Y').'" selected>'.date('Y').'</option>';
$$bzwz_content = temp('bzwz/bzwz_list_content');
disp('bzwz/bzwz_list');


//下拉列表默认选中
function select_check($check_str,$check_arr){
	foreach($check_arr as $k=>$v){
		$check=$v==$check_str?"selected":'';
		$options.="<option value='$v' $check>$v</option>";
	}
	return $options;
}
?>