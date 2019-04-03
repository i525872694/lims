<?php
/**
 * 功能：生成化验单相关函数
 * 作者：zhengsen
 * 时间：2016-04-10
**/
require_once INC_DIR . "cy_func.php";
/**
 * 功能：生成assay_order表数据
 * 作者：zhengsen
 * 日期：
 * 参数1：[Array] [arow] [cy_rec表数据数组]
 * 参数2：[Array] [xc_exam_value_arr] [现场项目数组]
 * 参数3：[Array] [xcpx_arr] [现场平行项目数组]
 * 参数4：[boole] [snkb] [是否生成室内空白]
 * 参数5：[Array] [jcxm_vids_arr] [所有检测项目信息]
 * 返回值：
 * 描述：
*/
function create_assay_order( $arow, $xc_exam_value_arr,$xcpx_arr,$snkb,$jcxm_vids_arr,$xmfb_data) {
	global $DB,$global,$fzx_id,$rootdir;
	// 化验任务计数器
	$_count = 0;
	// 根据$sql_data组合生成SQL语句
	$sql_data = array();
	// 化验项目
	$arow['assay_values']	= trim($arow['assay_values'],',');
	$assay_list = explode(',',$arow['assay_values']);
	// 室内平行项目
	$snpx_arr = empty($arow['snpx_item']) ? array() : explode(',',$arow['snpx_item']);
	// 加标回收项目
	$jbhs_arr = empty($arow['jbhs_item']) ? array() : explode(',',$arow['jbhs_item']);
	// 下达的质控信息
	$_xcpx = empty($xcpx_arr) ? false : true;// 是否是现场平行
	$_jbhs = empty($jbhs_arr) ? false : true;// 是否做加标回收
	$_snpx = empty($snpx_arr) ? false : true;// 是否做室内平行
	// SQL语句
	$sql = "INSERT INTO `assay_order` (`cyd_id`,`cid`,`sid`,`water_type`,`river_name`,`site_name`,`create_date`,`bar_code`,`bar_code_position`,`vid`,`hy_flag`) VALUES ";
	foreach ($assay_list as $vid){
		// 如果是现场项目不需要生成化验单
		if( !empty($xc_exam_value_arr) && !$arow['by_id'] && in_array( $vid, $xc_exam_value_arr) ){
			continue;
		}
		// 化验数目加1	
		$_count++;
		// 记录这批任务所有的检测项目信息（现场项目不记录）
		if( !empty($arow['water_type']) ){
			// 根据分包信息来判定该检测任务所属的实验室ID
			$fp_id = ( isset($xmfb_data[$vid]) && intval($xmfb_data[$vid]) ) ? $xmfb_data[$vid] : $fzx_id;
			$jcxm_vids_arr[$fp_id]['xmfb_info'][$vid] = $vid;
			$jcxm_vids_arr[$fp_id]['sylx_vids'][$arow['water_type']][$vid] = $vid;
		}
		// 样品化验质控标识
		$hy_flag = $arow['zk_flag'];
		// 如果原样里面的检测项目并未做后面指定的质控需要恢复之前的质控标识
		// 现场平行
		if( $_xcpx && intval($hy_flag) && !in_array($vid,$xcpx_arr)){
			$hy_flag -= 5;
		}
		// 加标回收
		if( $_jbhs && intval($hy_flag) && !in_array($vid,$jbhs_arr)){
			$hy_flag -= 40;
		}
		// 室内平行
		if( $_snpx && intval($hy_flag) && !in_array($vid,$snpx_arr)){
			$hy_flag -= 20;
		}
		//全程序空白的情况，把对应置空
		if($arow['water_type']=='0'){
			$arow['water_type'] = '';
		}
		// 插入数据的SQL语句
		// 顺序不能变,需要与上面$sql总字段顺序一致
		$sql2 = array(
			'cyd_id' => "'{$arow['cyd_id']}'",
			'id' => "'{$arow['id']}'",
			'sid' => "'{$arow['sid']}'",
			'water_type' => "'{$arow['water_type']}'",
			'river_name' => "'{$arow['river_name']}'",
			'site_name' => "'{$arow['site_name']}'",
			'curdate' => "CURDATE()",
			'bar_code' => "'{$arow['bar_code']}'",
			'bar_code_position' => "'{$arow['bar_code_position']}'",
			'vid' => "'{$vid}'",
			'hy_flag' => "'{$hy_flag}'"
		);
		$sql_data[] = '('.implode(',', $sql2).')';
		// 该样品是全程序空白并且规定同时做两个室内空白
		if( in_array($hy_flag, $global['qckb_flag']) && intval($snkb) ){
			$_count+=2;
			include_once $rootdir.'/huayan/ahlims.php';
			include_once $rootdir.'/huayan/lib/make_bar_code.class.php';
			$makeBarCode = new MakeBarCodeAPP();
			$kongbai = $makeBarCode->get_new_kongbai(array(
				'tid' => 0,
				'vid' => $vid,
				'create_date' => date('Y-m')
			));
			$sql_data[]	= '('.implode(',', array_merge($sql2, array(
				'sid'=>"'{$kongbai['sid']}'",
				'site_name'=>"'{$kongbai['bar_code']}'",
				'bar_code'=>"'{$kongbai['bar_code']}'",
				'hy_flag'=>"'-2'"
			))).')';
			$kongbai = $makeBarCode->get_new_kongbai(array(
				'tid' => 0,
				'vid' => $vid,
				'create_date' => date('Y-m')
			), 1);
			$sql_data[]	= '('.implode(',', array_merge($sql2, array(
				'sid'=>"'{$kongbai['sid']}'",
				'site_name'=>"'{$kongbai['bar_code']}'",
				'bar_code'=>"'{$kongbai['bar_code']}'",
				'hy_flag'=>"'-2'"
			))).')';
		}
		// 做室内平行
		if( in_array($hy_flag, $global['snpx_flag']) ){
			$_count++;
			$_r = $arow['bar_code'].'P';
			$sql_data[]	= '('.implode(',', array_merge($sql2, array(
				'bar_code'=>"'{$_r}'",
				'hy_flag'=>"'-20'"
			))).')';
		}
		// 做加标回收
		if( in_array($hy_flag, $global['jbhs_flag']) ){
			$_count++;
			$_r = $arow['bar_code'].'J';
			$sql_data[]	= '('.implode(',', array_merge($sql2, array(
				'bar_code'=>"'{$_r}'",
				'hy_flag'=>"'-40'"
			))).')';
		}
		// 现场平行B样
		if( '-6' == $hy_flag ){
			if( !empty($arow['snpx_item']) ){
				//现场平行（平行样）+室内平行
				$snpx_arr = explode(',',$arow['snpx_item']);
				if( in_array($vid, $snpx_arr) ){
					$_count++;
					$_r = $arow['bar_code'].'P';
					$sql_data[]	= '('.implode(',', array_merge($sql2, array(
						'bar_code'=>"'{$_r}'",
						'hy_flag'=>"'-26'"
					))).')';
				}
			}
			if( !empty($arow['jbhs_item']) ){
				//现场平行（平行样）+加标
				$jbhs_arr = explode(',',$arow['jbhs_item']);
				if(in_array($vid, $jbhs_arr)){
					$_count++;
					$_r = $arow['bar_code'].'J';
					$sql_data[]	= '('.implode(',', array_merge($sql2, array(
						'bar_code'=>"'{$_r}'",
						'hy_flag'=>"'-46'"
					))).')';
				}
			}
		}
	}
	if($sql_data) {
		$DB->query( $sql . implode(',', $sql_data) );
	}
	return array(
		'_count' => $_count,
		'jcxm_vids_arr' => $jcxm_vids_arr
	);
}
/**
 * 功能：生成assay_pay表数据
 * 作者：zhengsen
 * 日期：
 * 参数1：[Number] [cyd_id] [采样单ID]
 * 参数2：[Array] [jcxm_vids_arr] [所有检测项目]
 * 返回值：
 * 描述：此函数向assay_pay表里插入数据并更新assay_order表的tid
*/
function create_assay_pay( $cyd_id,$jcxm_vids_arr){
	global $DB,$water_type_max,$jcbz_data,$value_C_data,$fzx_id,$error_msg;
	set_time_limit(1000);
	// value表全部检测项目
	$value_C_data = array();
	$sql = "SELECT `id`, `value_C` FROM `assay_value` WHERE 1";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$value_C_data[$row['id']] = $row['value_C'];
	}
	// 取出子类对应的父类
	$sql = "SELECT DISTINCT `lx`.`id`, `lx`.`parent_id` FROM `leixing` AS `lx` LEFT JOIN `cy_rec` AS `rec` ON `lx`.`id`=`rec`.`water_type` WHERE `rec`.`cyd_id`='{$cyd_id}' ORDER BY `rec`.`water_type`";
	$query = $DB->query($sql);
	while( $row = $DB->fetch_assoc($query) ){
		if( '0' == $row['parent_id'] ){
			$water_type_max[$row['id']] = $row['id'];
		}else{
			$water_type_max[$row['id']] = get_water_type_max($row['id'],$fzx_id);
		}
	}
	$new_assay_pays = 0;
	// 所有需要生成化验的检测项目
	foreach ($jcxm_vids_arr as $fp_id => $fp_vid_arr) {
		if( empty($fp_vid_arr['xmfb_info']) ){
			continue;
		}
		// 查询出该分中心所有化验员
		$user_arr = array();
		$user_query = $DB->query("SELECT `id`,`userid` FROM `users` WHERE `group`!='0' AND `fzx_id`='{$fp_id}' ORDER BY `system_admin` ASC");
		while($row = $DB->fetch_assoc($user_query)){
			// 没有设置检测方法时默认分配给管理员（管理员有修改化验员和删除化验单的权限）
			$mr_user = $row;
			$user_arr[$row['id']] = $row['userid'];
		}
		$tid_arr = array();
		// 本批次样品涉及到的所有水样类型
		$sylx_vids = $fp_vid_arr['sylx_vids'];
		// 本批次样品涉及到的所有检测项目
		$xmfb_info = implode(',', $fp_vid_arr['xmfb_info']);
		$order_sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$cyd_id}' AND `vid`IN({$xmfb_info}) AND `sid`>'0' AND `water_type`>'0' GROUP BY `water_type`,`vid` ORDER BY `water_type` ASC";
		$order_query = $DB->query($order_sql);
		while ($order_row = $DB->fetch_assoc($order_query)) {
			$xmid = intval($order_row['vid']);
			$lxid = intval($order_row['water_type']);
			$xmfa_par_row = array();
			$xmfa_sql = "SELECT `x`.`id`, `x`.`fangfa`, `x`.`lxid`, `x`.`xmid`, `x`.`userid`, `x`.`userid2`, `am`.`value_C` 
				FROM `xmfa` AS `x` 
				LEFT JOIN `assay_method` AS `am` ON `x`.`fangfa`=`am`.`id` 
				WHERE `fzx_id`='{$fp_id}' AND `act`='1' AND `lxid` IN('{$lxid}','{$water_type_max[$lxid]}') AND `xmid`='{$xmid}' 
				ORDER BY  `x`.`lxid` DESC , `x`.`mr` DESC";
			$row = $DB->fetch_one_assoc($xmfa_sql);
			if( empty($row) ){
				$row['fangfa'] = '0';
				$row['userid'] = $mr_user['id'];
				$error_msg[0] = '由于未设置检测方法';
				if( empty($error_msg[$xmid]) ){
					$error_msg[$xmid] = $value_C_data[$xmid].'分配给了管理员：'.$user_arr[$row['userid']];
				}
			}
			if( empty($tid_arr[$xmid][$row['fangfa']][$row['userid']]) ){
				$assay_pay_info = array();
				$assay_pay_info['fp_id']	= $fp_id;
				$assay_pay_info['fzx_id']	= $fzx_id;
				$assay_pay_info['cyd_id']	= $cyd_id;
				$assay_pay_info['vid']		= $xmid;
				$assay_pay_info['fid']		= $row['id'];
				$assay_pay_info['create_date']= date('Y-m-d H:i:s');
				$assay_pay_info['uid']	= $row['userid'];
				$assay_pay_info['uid2']	= $row['userid2'];
				$assay_pay_info['userid']	= $user_arr[$row['userid']];
				$assay_pay_info['userid2']	= $user_arr[$row['userid2']];
				$assay_pay_info['assay_element'] = empty($row['value_C']) ? $value_C_data[$xmid] : $row['value_C'];
				$tid = new_record('assay_pay',$assay_pay_info);
				$new_assay_pays++;
				$tid_arr[$xmid][$row['fangfa']][$row['userid']] = $tid;
			}else{
				$tid = $tid_arr[$xmid][$row['fangfa']][$row['userid']];
			}
			// 全程序空白、室内空白、标准样品，默认都更新它
			$DB->query("UPDATE `assay_order` SET `assay_over` ='S', `tid`='{$tid}' WHERE `cyd_id`='{$cyd_id}' AND `vid`='{$xmid}' AND `water_type`='0'");
			// 更新assay_order表检测数据信息
			$DB->query("UPDATE `assay_order` SET `assay_over` ='S', `tid`='{$tid}' WHERE `cyd_id`='{$cyd_id}' AND `vid`='{$xmid}' AND water_type='{$lxid}' AND assay_over='0'");
			// 设置排序字段
			$DB->query("SET @r:=-1");
			$DB->query("UPDATE `assay_order` SET `order_id`=(@r:=@r+1) WHERE `tid`='{$tid}' ORDER BY `id` ASC");
		}
	}
	//更新cy表的hyd_count字段
	$DB->query("UPDATE `cy` SET `hyd_count` = '{$new_assay_pays}' WHERE `id`='{$cyd_id}' AND fzx_id='{$fzx_id}'");
	return $new_assay_pays;
}
?>
