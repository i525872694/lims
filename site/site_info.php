<?php
/**
 * 功能：修改站点
 * 作者：zhangdengsheng
 * 日期：2014-08-08
 * 描述：修改站点的信息及关联项目
*/
include '../temp/config.php';
$fzx_id			= FZX_ID;//中心
require_once "$rootdir/inc/site_func.php";
require_once $rootdir."/system_settings/site_type_set/func.php";
###################导航
$trade_global['daohang'][] =  array('icon'=>'','html'=>'查看修改站点','href'=>'site/site_info.php?site_id='.$_GET['site_id']);
$_SESSION['daohang']['site_info'] = $trade_global['daohang'];
//引入
$trade_global['js'] = array(
    'lims/d3/d3.js',
    'lims/d3/d3.layout.js',
    'lims/d3/tree.js'
);
###############################验证站点
$sid = 0;
if ( isset( $_GET['site_id'] ) && (int)$_GET['site_id'] != 0 )
    $sid = (int)$_GET['site_id'];
if ( !$sid )
    error_show( '非法站点编号' );
###################得到站点信息
$av_flag = false; //是否显示该站点化验项目*/
$sql = "SELECT * FROM sites WHERE id = '$sid' ";
$site_info = $DB->fetch_one_assoc( $sql );
$siteInfoJSON = json_encode($site_info);
##########取出所有的任务类型
$site_type_list = [];
$old_type_sql  = $DB->query("SELECT * FROM `site_type_record` WHERE `sid`='{$sid}'");
while ($old_type_row = $DB->fetch_assoc($old_type_sql)) {
    $site_type_list[]   = $old_type_row['stid'];
}
$site_type_id   = implode(',',$site_type_list);
############显示任务树
//数据处理
$all_node = all_site_type_data($site_type_list);
if(count($all_node)){
    $tree = treeArray($all_node,0);
    $zNodes= json_encode($tree[0]);
}else{
    $zNodes= json_encode(['name'=>'站点标签设置','id'=>1]);
}
###################添加水源限制
if($_GET['syxz']){
	$onsy = $DB->fetch_one_assoc("select * from n_set where module_name='syxz' and module_value1='".$_GET['syxz']."'");
	if(!$onsy['id']){
		$DB->query("insert into n_set set fzx_id='1',module_name='syxz',module_value1='".$_GET['syxz']."'");
	}
}
###################查看地图传过来的，更新经纬度
if($_GET['jingdu'] && $_GET['weidu'] ){
	$jingdu= $_GET['jingdu'];
	$weidu = $_GET['weidu'];
	$DB->query("UPDATE `sites` SET `jingdu` = '$jingdu',`weidu` = '$weidu' WHERE `sites`.`id` ='$sid';");
}
###################获取水源限制下拉列表
$syxz = '';
$sysql = $DB->query("select * from n_set where module_name='syxz'");
while($sy = $DB->fetch_assoc($sysql)){
	if($sy){
		$syxzs = ','.$site_info['syxz'].',';
		$pd = '';
		$pd = strstr($syxzs,','.$sy['module_value1'].',');
		if($pd){
			$syxz .="<label><input type='checkbox' name='syxz[]' value='{$sy['module_value1']}' checked>{$sy['module_value1']}&nbsp;&nbsp;</label>"; 
		}else{
			$syxz .="<label><input type='checkbox' name='syxz[]' value='{$sy['module_value1']}'>{$sy['module_value1']}&nbsp;&nbsp;</label>";
		}
	}
}
if($u['admin']){
    //水源限制添加按钮
	$cc = "<img src=\"$rooturl/img/tianjia.jpg\" name=\"tianjia\" width=\"51px\" height=\"24px\" class=\"bianse\" id='bianshe' onclick='tjsy()'/>";
}

if ( !$site_info ){
    error_show('该站点不存在');
}
##################转换显示经纬度
$site_info['weidu']=todfm($site_info['weidu']);
$site_info['jingdu']=todfm($site_info['jingdu']);
$leix=get_syleixing($site_info['water_type'],$bs='123');//获取水样类型
#####################监测项目选择部分
$yes_jcxm   = $no_jcxm  = [];
$temp_site_values = @explode( ',',$site_info['assay_values']);//站点当前监测的项目
$jcxm_sql = $DB->query("SELECT xmfa.xmid,av.value_C FROM `xmfa` inner join `assay_value` as av on xmfa.xmid=av.id where xmfa.fzx_id='$fzx_id' GROUP BY xmfa.xmid ");
while ($jcxm_row = $DB->fetch_assoc($jcxm_sql)) {
    if(@in_array($jcxm_row['xmid'], $temp_site_values)){//目前站点监测的项目
        $yes_jcxm[$jcxm_row['xmid']]= "<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\" title='vid:{$jcxm_row['xmid']}'><label><input type=\"checkbox\" checked name=\"vid[]\" value=\"{$jcxm_row['xmid']}\" />{$jcxm_row['value_C']}</label></li>";
    }else{//目前站点不监测的项目
        $no_jcxm[$jcxm_row['xmid']] = "<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\" title='vid:{$jcxm_row['xmid']}'><label><input type=\"checkbox\" name=\"vid[]\" value=\"{$jcxm_row['xmid']}\" />{$jcxm_row['value_C']}</label></li>";
    }
}
$yes_jcxm_html  = implode('',$yes_jcxm);
$yes_jcxm_num   = count($yes_jcxm);
$no_jcxm_html   = implode('',$no_jcxm);
$no_jcxm_num    = count($no_jcxm);
####################获取模板
$S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id' " );
while( $row = $DB->fetch_assoc( $S ) ) {
    $mbxm.="<option value='$row[module_value1]'>$row[module_value2]</option> ";
}

disp("site/site_info");
?>
