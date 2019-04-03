<?php
/**
 * 功能：添加站点保存
 * 作者：zhangdengsheng
 * 日期：2014-07-14
 * 描述：接收从group_add_sites.html页面传过来的值添加新站点（可添加多条）
*/
require_once "../temp/config.php";
require_once __ROOTDIR__ . "/inc/site_func.php";
$fzx_id		= FZX_ID;//中心
$jieGuo		= 'no';//ajax添加站点用
$ajax_group	= array();
$site_type	= $_POST['site_type'];//任务类型
$group_name	= trim($_POST['group_name']);//批次
if($_POST['xid']){//这里将方法id变成 项目id和方法id
	$vids= join(',',$_POST['xid']);
}else{
	if($_POST['vid']){//如果不是 xid 表示 是原来的  站点管理
		$vids = join(',',$_POST['vid']);
	}
}
for($i=0;$i<count($_POST['site_name']);$i++){
    $site_name = trim($_POST['site_name'][$i]);
    if( $site_name ){
        $site_info = array();
		$site_info['fzx_id']		= $fzx_id;
        $site_info['site_type']		= $site_type;//任务类型
        $site_info['site_name']		= $_POST['site_name'][$i];//站名
        $site_info['site_code']		= $_POST['site_code'][$i];//站码
        $site_info['water_type']	= $_POST['water_type'][$i];//水样类型
        $site_info['site_address']    = $_POST['site_address'][$i];//站点地址
        $site_info['site_line']    = $_POST['site_line'][$i];//垂线编号
        $site_info['site_vertical']    = $_POST['site_vertical'][$i];//层面编号
		/*$site_info['fp_id']			= $_POST['fenz'][$i];//分中心
		$site_info['river_name']	= $_POST['river_name'][$i];//区域
		$site_info['xz_area']		= $_POST['xz_area'][$i];//行政区
		$site_info['site_address']	= $_POST['site_address'][$i];//街道*/
		
		//$site_info['create_date']	= 'now()';//创建时间
		sort($_POST['xid']);
		$site_info['assay_values']	= implode(',',$_POST['xid']);//默认检测项目
		$sid = save_new_site( $site_info );//引用save_new_site方法向site表插数据
		//发送数据到duijie表中
		if(!empty($sid)){
			$curl_arr = array();
			$curl_arr['STCD'] = $_POST['site_code'][$i];
			$curl_arr['STNM'] = $_POST['site_name'][$i];
			$curl_arr['PRPNM'] = $_POST['site_line'][$i];
			$curl_arr['LYNM'] = $_POST['site_vertical'][$i];
			$curl_arr['STLC'] = $_POST['site_address'][$i];
			$curl_arr['FZX_ID'] = $fzx_id;
			$curl_arr['LIMS_ID'] = $sid;
			$curl_data = array();
			$curl_data['data'] = $curl_arr;
			$curl_data['action'] = 'table';
			$curl_data['table'] = 'SITE_DUIJIE';
			$curl_data['action2'] = 'add';
			$data = curl_request($duijie_url.'xd_cyrw/cy_duijie_url.php',$curl_data);
		}
		//print_rr($tjcs_n);die;
		if($_POST['action']=='site_add_ajax'){
			$sql = "INSERT INTO site_group SET 
				site_id			= {$sid},
				fzx_id			= '$fzx_id',
				site_type		= '$site_type',
				group_name		= '$group_name',
				cuser			= '{$u['userid']}',
				ctime			= now(),
				assay_values	= '{$site_info['assay_values']}'";//向site_group表插数据
				$DB->query( $sql );
				//判断是否插入成功，并加入数组ajax返回用
				$new_group_id	= $DB->insert_id();
				if((int)$new_group_id>0){
					$jieGuo	= 'yes';
					//$ajax_group[$new_group_id]	= $site_info['site_name'];
					$ajax_group[$sid]    = $site_info['site_name'];
				}
		} 
    }
}
if($_POST['action']=='site_add_ajax'){
	echo json_encode(array('jieGuo'=>$jieGuo,'ajax_group'=>$ajax_group));
}else{
	gotourl( "$rooturl/site/site_manage_list.php?action=site_add" );
}
?>
