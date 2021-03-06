<?php
/**
 * 功能：个性化配置页面
 * 作者：hanfeng
 * 日期：2015-03-06
 * 描述：
*/
include("../../temp/config.php");
//分中心id
$fzx_id		= FZX_ID;
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'系统个性化配置','href'=>'./system_settings/view_settings/view_settings.php')
);
$bar_code_display	= $show_shuju_display	= "display:none;";
if($u['admin']){
	$bar_code_display	= $show_shuju_display	= "display:block;";
}else if($u['is_zz']=='1'){
	$bar_code_display	= "display:block;";
	$bar_make_display	= "display:none;";
}
#############样品编号格式配置
//样品编号结构组成配置
$bar_code_make_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_make' ORDER BY id DESC LIMIT 1");
if(!empty($bar_code_make_old['module_value1'])){
	$bar_code_make_arr	= explode("+",$bar_code_make_old['module_value1']);
	$bar_code_serial_num= end($bar_code_make_arr);//最后一个值 是流水号个数
	$fzx_mark_checked	= $site_type_checked	= $water_type_checked = $fenge_checked = $fzx_fenge_checked	='';
	foreach ($bar_code_make_arr as $key => $value) {
		if($value == 'fzx_mark'){
			$fzx_mark_checked	= 'checked';
		}
		if($value == 'site_type'){
			$site_type_checked	= 'checked';
		}
		if($value == 'water_type'){
			$water_type_checked	= 'checked';
		}
		if($value == '-'){
			$fenge_checked	= 'checked';
		}
		if($value == 'fzx-'){
			$fzx_fenge_checked	= 'checked';
		}
	}
}
//样品编号对应的额流水号的位数
$bar_code_serial_count	= '';
for ($i=4; $i > 2; $i--){
	if($i==$bar_code_serial_num){
		$bar_code_serial_count	.= "<option value='{$i}' selected>{$i}位</option>";
	}else{
		$bar_code_serial_count	.= "<option value='{$i}'>{$i}位</option>";
	}
	
}
//取出任务类型默认配置
$moren_site_type_mark	= array();
$sql_moren_site_type_mark	= $DB->query("SELECT * FROM `n_set` WHERE `module_name`='bar_code_mark_site'");
while ($rs_moren_site_type_mark=$DB->fetch_assoc($sql_moren_site_type_mark)) {
		$moren_site_type_mark[$rs_moren_site_type_mark['module_value2']]	= $rs_moren_site_type_mark['module_value1'];
}

//$str_option	= "<option value='A'>A</option><option value='B'>B</option><option value='C'>C</option><option value='D'>D</option><option value='E'>E</option><option value='F'>F</option><option value='G'>G</option><option value='H'>H</option><option value='I'>I</option><option value='J'>J</option><option value='K'>K</option><option value='L'>L</option><option value='M'>M</option><option value='N'>N</option><option value='O'>O</option><option value='P'>P</option><option value='Q'>Q</option><option value='R'>R</option><option value='S'>S</option><option value='T'>T</option><option value='U'>U</option><option value='V'>V</option><option value='W'>W</option><option value='X'>X</option><option value='Y'>Y</option><option value='Z'>Z</option>";
$str_input	= "";
//找出分中心的标识
if($u['admin']){
	$sql_where	= "";
}else if($u['is_zz']=='1'){
	$sql_where	= " AND `parent_id`='{$fzx_id}' || `id`='{$fzx_id}' ";
}else{
	$sql_where	= "AND `id`='{$fzx_id}' ";
}
$bar_code_fzx_type	= '';
$fzx_mark	= $DB->query("SELECT * FROM `hub_info` WHERE 1 {$sql_where}");
$num_rows	= $DB->num_rows($fzx_mark);
while ($fzx_mark_rs	= $DB->fetch_assoc($fzx_mark)) {
	if(!empty($fzx_mark_rs['sort_name'])){
		$fzx_mark_rs['hub_name']	= $fzx_mark_rs['sort_name'];
	}
	if($fzx_mark_rs['id']==$fzx_id && $num_rows=='1'){
		$fzx_name	= '本分中心标识';
	}else{
		$fzx_name	= $fzx_mark_rs['hub_name'];
	}
/*	if(!empty($fzx_mark_rs['bar_code'])){
		$bar_code_fzx_type	.= "<label title='{$fzx_mark_rs['hub_name']}' style='min-width:160px;white-space:nowrap;'>{$fzx_name}<select name='fzx_mark[{$fzx_mark_rs['id']}]'><option value='{$fzx_mark_rs['bar_code']}'>{$fzx_mark_rs['bar_code']}</optioin>{$str_option}</select></label>";
		
	}else{
		$bar_code_fzx_type	.= "<label title='{$fzx_mark_rs['hub_name']}' style='min-width:160px;white-space:nowrap;'>{$fzx_name}<select name='fzx_mark[{$fzx_mark_rs['id']}]'>{$str_option}</select></label>";
	}*/
	$bar_code_fzx_type	.= "<label title='{$fzx_mark_rs['hub_name']}' style='min-width:180px;white-space:nowrap;margin-left:10px;'>{$fzx_name}<input type='text' name='fzx_mark[{$fzx_mark_rs['id']}]' value='{$fzx_mark_rs['bar_code']}' placeholder='分中心标识' style='width:90px' /></label>";
}

//取出本单位所有的站点类别/任务类型/任务性质
$bar_code_site_type	= "";
foreach($global['site_type'] as $key=>$value){
	if(!empty($moren_site_type_mark[$key])){
		$bar_code_site_type	.= "<label>{$value}<select name='site_type_mark[$key]'><option value='{$moren_site_type_mark[$key]}' selected>{$moren_site_type_mark[$key]}</option>{$str_option}</select></label>";
	}else{
		$bar_code_site_type	.= "<label>{$value}<select name='site_type_mark[$key]'>{$str_option}</select></label>";
	}
}
//取出所有的水样类型并存到数组中
$bar_code_water_type	= '';
$water_type_sql	= $DB->query("SELECT * FROM `leixing` WHERE `act`='1' and `parent_id`='0'");
while ($rs_water_type	= $DB->fetch_assoc($water_type_sql)) {
	$bar_code_water_type .= "<label>{$rs_water_type['lname']}<select parent='{$rs_water_type['id']}' old_value='' name='water_type_mark[{$rs_water_type['id']}]'><option value='{$rs_water_type['bar_code_mark']}'>{$rs_water_type['bar_code_mark']}</option>{$str_option}</select></label>";
	$water_type_sql2 = $DB->query("SELECT * FROM `leixing` WHERE `act`='1' and `parent_id`='{$rs_water_type['id']}'");
	while ($rs_water_type2 = $DB->fetch_assoc($water_type_sql2)) {
		$bar_code_water_type	.= "<label>{$rs_water_type2['lname']}<select parent_id='{$rs_water_type['id']}' old_value='' name='water_type_mark[{$rs_water_type2['id']}]'><option value='{$rs_water_type2['bar_code_mark']}'>{$rs_water_type2['bar_code_mark']}</option>{$str_option}</select></label>";
	}
	$bar_code_water_type	.= "<br >";
}
#############样品编号按年编号或按月编号的配置
//从数据库获取相应配置
$bar_code_create_str= $bar_code_create_year	= $bar_code_create_mouth	= '';
$bar_code_create	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='bar_code_create' ORDER BY id DESC limit 1");
if(!empty($bar_code_create['module_value1']) && $bar_code_create['module_value1'] == 'year'){
	$bar_code_create_year	= "checked";
}else{
	$bar_code_create_mouth	= "checked";
}
############化验单数据什么时候能显示到数据报告上的配置
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
	$begin_checked	= $finish_checked = $jh_checked = $fh_checked	= '';
	foreach ($show_shuju_arr as $key => $value) {
		if($value == '已开始'){
			$begin_checked	= 'checked';
		}
		if($value == '已完成'){
			$finish_checked	= 'checked';
		}
		if($value == '已校核'){
			$jh_checked	= 'checked';
		}
		if($value == '已复核'){
			$fh_checked	= 'checked';
		}
	}
}
###########下达采样任务页面设置：同时生成室内空白设置
$yes_checked= '';
$no_checked	= 'checked';
$create_snkb_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='xdcy'");
if(!empty($create_snkb_old['module_value1'])){
	$create_snkb_arr	= json_decode($create_snkb_old['module_value1'],true);
	if($create_snkb_arr['create_snkb'] == 'yes'){
		$yes_checked= 'checked';
		$no_checked	= '';
	}
}
disp("view_settings.html");
?>