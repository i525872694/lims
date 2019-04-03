<?php
// ini_set("display_errors", '1');
// error_reporting(E_ALL & ~E_NOTICE);
include_once './ahlims.php';
/**
 * 功能：化验单计算和化验单质控函数
 * 作者：Mr Zhou
 * 日期：2014-10-22
 * 描述：得到某张化验单全部数据
*/
function get_hyd_data($tid) {
	global $DB,$u,$fzx_id,$global,$rootdir;
	$fzx_sql_where = "ap.`id`='{$tid}'";
	//总站[进度管理||报告查看权限]可以查看其他分中心的化验单
	if( !$u['is_zz'] && !($u['jindu_manage'] || $u['baogao']) ){
		$fzx_sql_where .= " AND ( ap.`fzx_id` = '{$fzx_id}' OR ap.`fp_id` = '{$fzx_id}')";
	}
	$pay = $DB->fetch_one_assoc("SELECT * FROM `assay_pay` AS `ap` WHERE 1 AND {$fzx_sql_where} ");
	if( empty($pay) ){
		return array();
	}else if('未开始' == $pay['over']){
		$pay = up_new_hyd_bt( $pay );
	}
	// 特殊项目的单子在化验员未签字时做如下特殊处理
	if( empty($pay['sign_01']) && empty($pay['sign_012']) ){
		if(file_exists($rootdir.'/huayan/get_hyd_data.php')){
			include_once $rootdir.'/huayan/get_hyd_data.php';
			$getHydData = "getHydData_{$pay['vid']}";
			//'110,125,128,174,181,494,567,592,598,601,605,651,653,655,657'
			function_exists($getHydData) && $getHydData($pay);
		}
	}
	//scid有数据说明已关联标准曲线，bdid有值说明已关联标液标定
	if( !intval($pay['scid']) && !intval($pay['bdid'])&& !intval($pay['pzid']) ) {
		$sql = "SELECT	ap.*,bt.`zongheng`, bt.`lines`, btm.`table_name`,
						cy.`cyd_bh`, cy.`water_type`, cy.`site_type`, cy.`cy_date`, cy.`ys_date`, cy.`group_name`
			FROM `assay_pay` AS ap
			LEFT JOIN `cy` ON ap.`cyd_id` = cy.`id` 
			LEFT JOIN `bt` ON ap.`fid` = `bt`.`fid`
			LEFT JOIN `bt_muban` btm ON ap.`table_id` = btm.`id`
			WHERE 1 AND {$fzx_sql_where}" ;
		$hyd_data = $DB->fetch_one_assoc( $sql );
	}else{
		//关联标准曲线信息 standard_curve 表
		if( intval($pay['scid']) ){
			$sql = "SELECT
				sc.`CR` AS CR,
				sc.`CT` AS CT,
				sc.`unit`AS sc_unit,
				sc.`td1` AS bsm_gg,
				sc.`td2` AS bo_chang,
				sc.`td3` AS dr_v,
				sc.`td4` AS tl_ff,
				sc.`td5` AS sc_bh,
				sc.`td31`AS sc_ldrq,
				sc.`td7` AS scby_name,
				sc.`td8` AS scby_nd,
				sc.`td9` AS scby_date,
				sc.`qx_sx` AS qx_sx,
				sc.`qx_xx` AS qx_xx,
				ap.*, btm.`table_name`,
				bt.`zongheng`, bt.`lines`,
				IF(sc.`td18` IS NULL OR sc.`td18`='',
					IF(sc.`td15` IS NULL OR sc.`td15`='',
						IF(sc.`td12` IS NULL OR sc.`td12`='',sc.`td8`,
							sc.`td12`),sc.`td15`),sc.`td18`) AS sc_syy_nd,
				cy.`cyd_bh`, cy.`cy_date`, cy.`ys_date`, cy.`site_type`, cy.`water_type`, cy.`group_name`
				FROM `assay_pay` AS ap
				LEFT JOIN `cy` ON ap.`cyd_id` = cy.`id`
				LEFT JOIN `bt` ON ap.`fid` = `bt`.`fid`
				LEFT JOIN `standard_curve` AS sc ON sc.`id` = ap.`scid`
				LEFT JOIN `bt_muban` btm ON ap.`table_id` = btm.`id`
				WHERE 1 AND {$fzx_sql_where}";
			$hyd_data = $DB->fetch_one_assoc( $sql );
			$sc_upper_limit = $DB->fetch_one_assoc( "SELECT `vd1` FROM `standard_curve_record` WHERE `sc_id` = '{$hyd_data['sc_id']}' ORDER BY `vd1`+0 DESC LIMIT 1" );
			$hyd_data['sc_upper_limit'] = $sc_upper_limit['vd1'];
			$hyd_data['CAA'] = $hyd_data['CA'] >= 0 ? '+'.$hyd_data['CA'] : $hyd_data['CA'];
			$hyd_data['quxian'] = QuxianApp::get_sc_gongshi($hyd_data);
			// 化验单截距检验若为不合格则显示不合格
			if($hyd_data['CT']!='' && $hyd_data['CT']!='不合格'){
				$hyd_data['CT'] = '合格';
			}
		}else if(intval($pay['bdid'])){//关联标液标定信息
			$sql = "SELECT
				bd.`mol_m`,
				bd.`bzry_id`,
				bd.`zsj_name`,
				bd.`bzry_pzrq`,
				bd.`bzry_bdrq`,
				bd.`jzry_nddw`,
				ap.*, ap.`CA` bzry_name, ap.`CB` bzry_nongdu, bt.`zongheng`, bt.`lines`,
				cy.`cyd_bh`, cy.`cy_date`,cy.`ys_date`, cy.`site_type`, cy.`water_type`, cy.`group_name`, btm.`table_name`
				FROM `assay_pay` AS ap 
				LEFT JOIN `cy` ON ap.`cyd_id` = cy.`id`
				LEFT JOIN `bt` ON ap.`fid` = `bt`.`fid`
				LEFT JOIN `jzry_bd` AS bd ON bd.`id` = ap.`bdid`
				LEFT JOIN `bt_muban` btm ON ap.`table_id` = btm.`id`
				WHERE 1 AND {$fzx_sql_where}";
			$hyd_data = $DB->fetch_one_assoc( $sql );
		}else if(intval($pay['pzid'])) {//关联标液标定信息
			$sql = "SELECT
				bd.`jzry_pzrq`,
				ap.*, ap.`CA` sjmc, ap.`CB` sj_nd, bt.`zongheng`, bt.`lines`,
				cy.`cyd_bh`, cy.`cy_date`,cy.`ys_date`, cy.`site_type`, cy.`water_type`, cy.`group_name`, btm.`table_name`
				FROM `assay_pay` AS ap 
				LEFT JOIN `cy` ON ap.`cyd_id` = cy.`id`
				LEFT JOIN `bt` ON ap.`fid` = `bt`.`fid`
				LEFT JOIN `jzry_bd` AS bd ON bd.`id` = ap.`bdid`
				LEFT JOIN `bt_muban` btm ON ap.`table_id` = btm.`id`
				WHERE 1 AND {$fzx_sql_where}";
			$hyd_data = $DB->fetch_one_assoc( $sql );
			$hyd_data['sj_nd']=floatval($hyd_data['sj_nd']);
		}
	}
	// 检测方法
	$fangfa = $DB->fetch_one_assoc("SELECT m.`id` mid,m.`pid` mpid,x.`w1`,x.`w2`,x.`w3`,x.`w4`,x.`w5`,`blws` FROM `xmfa` x LEFT JOIN `assay_method` m ON x.`fangfa`=m.`id` WHERE x.`id`='{$hyd_data['fid']}'");
	// json数据
	$hyd_data['json'] = empty($hyd_data['json']) ? '' : json_decode($hyd_data['json'],true);
	$hyd_data['btdata'] = empty($hyd_data['btdata']) ? [] :  json_decode($hyd_data['btdata'], true);
	$hyd_data['otherData'] = empty($hyd_data['otherData']) ? [] :  json_decode($hyd_data['otherData'], true);
	// 收样时间
	if(!is_array($hyd_data['json']) || empty($hyd_data['json']['rwjs']['js_date'])){
		$hyd_data['js_date'] = $hyd_data['td31'];
	}else{
		$hyd_data['js_date'] = date('Y-m-d',strtotime($hyd_data['json']['rwjs']['js_date']));
	}
	if(is_array($fangfa)){
		$hyd_data = array_merge($hyd_data,$fangfa);
	}
	// 判断是否是分包的任务
	if( $hyd_data['fzx_id'] == $hyd_data['fp_id']){
		$hyd_data['xmfb_fzx'] = '';
		$hyd_data['xmfb_msg'] = '';
	}else{
		$hub_fp = $DB->fetch_one_assoc("SELECT `sort_name` FROM `hub_info` WHERE `id`='{$hyd_data['fp_id']}'");
		$hub_fzx = $DB->fetch_one_assoc("SELECT `sort_name` FROM `hub_info` WHERE `id`='{$hyd_data['fzx_id']}'");
		$hyd_data['xmfb_fzx'] = $hub_info['sort_name'];
		// 判断是否是分包的任务
		if( $hyd_data['fzx_id'] == $hyd_data['fp_id']){
			$hyd_data['xmfb_msg'] = '';
		}else{
			if( FZX_ID == $hyd_data['fzx_id'] ){
				$hyd_data['xmfb_msg'] = '<strong class="red">&nbsp;&nbsp;&nbsp;*</strong>已分包给<strong>'.$hub_fp['sort_name'].'</strong>';
			}else if( FZX_ID == $hyd_data['fp_id'] ){
				$hyd_data['xmfb_msg'] = '<strong class="red">&nbsp;&nbsp;&nbsp;*</strong><strong class="red">'.$hub_fzx['sort_name'].'</strong></strong>分包任务';
			}else{
				$hyd_data['xmfb_msg'] = '<strong class="red">&nbsp;&nbsp;&nbsp;*</strong>该任务是<strong class="red">【'.$hub_fzx['sort_name'].'】</strong>分包给<strong class="red">【'.$hub_fp['sort_name'].'】</strong>的化验任务';
			}
		}
	}
	// 获取水样类型 根据本化验单样品显示
	require_once INC_DIR . 'cy_func.php';
	$lxid = array();
	$water_type = array();
	$sql = "SELECT `water_type` FROM `assay_order` WHERE `tid`='{$hyd_data['id']}' AND `water_type`>0 GROUP BY `water_type`";
	$query = $DB->query($sql);
	while ($row=$DB->fetch_assoc($query)) {
		// 显示具体水样类型，不需要转换成一级水样类型
		// $water_type_name = get_water_type_max($row['water_type'], $fzx_id);
		$water_type_name = $row['water_type'];
		if(!in_array($water_type_name, $lxid)){
			$lxid[] = $water_type_name;
		}
	}
	// 本化验单包含的样品类型id
	$hyd_data['lxid'] = implode(',', $lxid);//水样类型id
	$hyd_data['water_type'] = get_water_types($hyd_data['water_type']);
	$query = $DB->query("SELECT `lname` FROM `leixing` where `id`IN({$hyd_data['lxid']})");
	while ($row=$DB->fetch_assoc($query)) {
		$water_type[]=$row['lname'];
	}
	$hyd_data['water_type_name']=implode(',', $water_type);
	return $hyd_data;
}
/**
 * 功能：
 * 作者：Mr Zhou
 * 日期：2014-11-27 
 * 参数： 
 * 返回值：
 * 功能描述：如果是多合一的副项目，跳转至主化验单
*/
function get_hyd_id($cyd_id,$vid){
	global $DB,$dhy_arr;
	if(!isset($dhy_arr['xm'][$_POST['vid']])){
		$dhy_arr['xm'][$vid] = array($vid);
	}
	$vid = implode(',',$dhy_arr['xm'][$vid]);
	$sql = "SELECT ao.id,ao.vid,ao.tid,ao.sid,ao.hy_flag FROM `assay_pay` ap LEFT JOIN `assay_order` ao ON ap.id=ao.tid WHERE ao.`cyd_id`='$cyd_id' AND ao.`vid` IN($vid) ORDER BY bar_code";
	$query = $DB->query($sql);
	$tid = array();
	while ($row=$DB->fetch_assoc($query)) {
		$flag = $row['sid'].'_'.$row['hy_flag'];
		$tid[$row['vid']]['vid'] = $row['vid'];
		$tid[$row['vid']]['tid'] = $row['tid'];
		$tid[$row['vid']]['oid'][$flag] = $row['id'];
	}
	return $tid;
}
/** 
 * 功能：循环得到化验单每行数据
 * 作者：铁龙
 * 日期：2014-06-16 
 * 参数： $returnFlage 0旧版化验单查看 1旧版化验单打印 2新版化验单查看 3新版化验单打印 
 * 返回值：
 * 功能描述：得到化验单的每一行数据
*/
function get_assay_hyd_line($tid, $biao, $returnFlage=0){
	global $DB,$global,$u,$arow,$site_zk_flag,$dhy_arr,$rootdir;
	$c = 0;
	$i=1;
	$line = array();
	$hgxz_arr = array();
	$vid = $arow['vid'];
	//化验单模板文件地址
	$plan_file_path = $global['hyd']['plan_file_path'];
	// 化验单样品默认排序规则
	$order_by = "(CASE WHEN  hy_flag ='-2' THEN 0 ELSE 1 END), RIGHT(LEFT(ao.`bar_code`, 13), 4)";
	// 系统自定义配置的排序规则
	if(isset($global['hyd']['bar_code_order']) && !empty($global['hyd']['bar_code_order'])){
		$order_by = $global['hyd']['bar_code_order'];
	}
	$sql = "SELECT `ao`.*, '{$arow['td31']}' AS `date`, `cr`.`cy_date` FROM `assay_order` AS `ao` LEFT JOIN `cy_rec` AS `cr` ON `ao`.`cid`=`cr`.`id` WHERE `ao`.`tid`='{$tid}' ORDER BY {$order_by} ASC ,`ao`.`id` ASC";
	$query = $DB->query( $sql );
	while( $row = $DB->fetch_assoc($query) ) {
		//修改质控结果信息
		$row = get_zhikong( $row );
		//站码显示
		$site_name = $row['bar_code'];
		$popover = $hyd_zk_class = $is_chaobiao = '';
		$is_hy_user	 = ($u['userid']==$arow['userid'] || $u['userid']==$arow['userid2']) ? 1 : 0;//是否是本项目的化验员
		// $row['cy_date'] = date('m-d',strtotime($row['cy_date']));
		if( 'over'!=$row['assay_over'] && ( $is_hy_user && ''==$arow['sign_01'] || $u['admin'])){
			$hyd_zk_class = 'hydzk';
			// 质控数据
			$zk_data =  empty($row['zk_data']) ? '' : json_encode($row['zk_data']);
			$data = [
				"data-zk-data='{$zk_data}'",
				"data-vd28 = '{$row['vd28']}'",
				"data-vd29 = '{$row['vd29']}'",
				"data-vd30 = '{$row['vd30']}'",
				"data-vd31 = '{$row['vd31']}'",
				"data-vd32 = '{$row['vd32']}'",
				"data-vd33 = '{$row['vd33']}'",
				"data-orid = '{$row['id']}'",
				"data-flag = '{$row['hy_flag']}'",
				"data-ping = '{$row['ping_jia']}'",
				"data-code = '{$row['bar_code']}'",
				"data-over = '{$row['assay_over']}'",
				"data-reli = '{$row['reliable']}'",
				"data-dhy = '".intval($dhy_arr[$vid])."'"
			];
			$data = implode(' ', $data);
		}
		if( $global['hyd']['code_jiema']['is_jiema']&&$arow[$global['hyd']['code_jiema']['sign']] ){
			( !$u['admin'] ) && ( $data = '' );
			$site_name = $row['site_name'].$site_zk_flag[$row['hy_flag']]; //站点名称+质控信息
		}
		// 化验单查看
		if(in_array($returnFlage, [0, 2])){
			$popover	= 'data-trigger="hover focus" data-placement="top" data-rel="popover" data-animation="true" ';
			if(in_array($row['hy_flag'],array('-40','-60','-66'))){
				$massage = get_jiabiao_data($row['zk_data']);
				//历史信息浮窗
				$popover .= 'data-content="'.$massage.'" data-original-title="<i class=\'icon-beaker green\'>&nbsp;'.$row['bar_code'].'加标信息"';
			}else if($row['hy_flag'] == '-2'){
				$massage = "信号值：{$row['vd28']}";
				//历史信息浮窗
				$popover .= 'data-content="'.$massage.'" data-original-title="<i class=\'icon-beaker green\'>&nbsp;室内空白信息"';
			}else if($row['hy_flag'] == '-4'||$row['hy_flag'] == '-8'){
				$massage = "批号：{$row['vd28']} <br />标准值：{$row['vd29']} {$row['vd31']}<br />允许误差范围：{$row['vd30']} {$row['vd32']}";
				//历史信息浮窗
				$popover .= 'data-content="'.$massage.'" data-original-title="<i class=\'icon-beaker green\'>&nbsp;'.$row['bar_code'].'信息"';
			}else {
				// 数据是否超标提示
				if(!function_exists('is_chaobiao')){
					include_once $rootdir.'/baogao/bg_func.php';
				}
				if(!isset($hgxz_arr[$row['water_type']])){
					$hgxz_arr[$row['water_type']] = get_hgxz($row['water_type'],$row['vid']);
				}
				$chaobiao = is_chaobiao($row['vid'],$row['water_type'],$hgxz_arr[$row['water_type']]['hg_xz'],$row['vd0'],$arow['unit']);
				$chaobiao['jc_xz'] = $hgxz_arr[$row['water_type']]['hg_xz']; // 合格限值
				$chaobiao['pd_bz'] = $hgxz_arr[$row['water_type']]['pd_bz']; // 判定标准
				$is_chaobiao = json_encode($chaobiao);
				// 相关数据浮窗
				if(''!=$arow['sign_01'] && ($u['jh']||$u['fh']||$u['sh']||$u['admin'])){
					$popover_msg = get_popover_msg($arow, $row, $chaobiao);
					$popover .= 'data-content="'.$popover_msg['content'].'"  data-original-title="'.$popover_msg['title'].'"';
					$popover .= 'data-show-bar-code="1" data-content="'.$popover_mag['content'].'"  data-original-title="'.$popover_mag['title'].'"';
				}else{
					$popover = '';
				}
			}
			$row['site_name2'] = $row['site_name'];
			$row['bar_code'] = $row['site_name'] = '<span class="site_name tooltip-success '.$hyd_zk_class.'" '.$data.' '.$popover.' >'.$site_name.' </span>';
			//为平行和加标加上标识
			$row['xiang_dui_pian_cha'] = $_xdpc		= '<span class="hydzkpc '.$row['pj_class'].'">'.$row['_xdpc'].'</span>';
			$row['ping_jun'] = $_ping_jun	= '<span class="hydzkpj '.$row['pj_class'].'" data-chaobiao=\''.$is_chaobiao.'\'>'.$row['ping_jun'].'</span>';
		}else{
			// 化验单打印
			unset($row['json']);
			unset($row['zk_data']);
			unset($row['js_gongshi']);
			$row['site_name2'] = $row['site_name'];
			$row['bar_code'] = $row['site_name'] = $site_name;
			$row['ping_jun'] = $_ping_jun = $row['ping_jun'];
			$row['xiang_dui_pian_cha'] = $_xdpc = $row['_xdpc'];
		}

		eval('$one_line = "'.gettemplate($plan_file_path.'line_'.$biao.'.html').'";');
		$c++;$i++;
		$aline[] = in_array($returnFlage, [2, 3]) ? $row : $one_line; 
	}
	return (count($aline) && !$returnFlage) ? implode('', $aline) : $aline;
}
// 获取加标信息
function get_jiabiao_data($zk_data){
	if(empty($zk_data)){
		return '';
	}
	$zk_data = json_decode($zk_data, true);
	return "原水样体积：{$zk_data['v_y']} mL<br />标液浓度：{$zk_data['c_c']} {$zk_data['c_c_unit']}<br />标液体积：{$zk_data['v_c']} {$zk_data['v_c_unit']}";
}
// 获取合格限值
function get_hgxz($water_type,$vid){
	global $DB,$fzx_id,$rootdir;
	if(!function_exists('get_water_type_max')){
		include_once "{$rootdir}/inc/cy_func.php";
	}
	$water_type_max=get_water_type_max($water_type,$fzx_id);
	$jcbz_sql="SELECT 
		`aj`.`dw`,`aj`.`xz`, `aj`.`eglish_xz`, `aj`.`panduanyiju`,
		`n`.`module_value2` AS `water_type`, `n`.`module_value1` AS `pd_bz`
		FROM `n_set` AS `n` LEFT JOIN `assay_jcbz` AS `aj` ON `n`.`id`=`aj`.`jcbz_bh_id` 
		WHERE `module_name`='jcbz_bh' AND `module_value3`='1' AND `module_value2`='{$water_type_max}' AND `aj`.`vid`='{$vid}'";
	$jcbz_rs=$DB->fetch_one_assoc($jcbz_sql);
	if(!empty($jcbz_rs['panduanyiju'])){
		$jcbz_rs['hg_xz']=$jcbz_rs['panduanyiju'];
	}else{
		$jcbz_rs['hg_xz']=$jcbz_rs['xz'];
	}
	/*if($is_eglish&&!empty($jcbz_rs['eglish_xz'])){
		$jcbz_rs['hg_xz']=$jcbz_rs['eglish_xz'];
	}else{
		$jcbz_rs['hg_xz']=$jcbz_rs['xz'];
	}*/
	return $jcbz_rs;
}
/** 
 * 功能：质控内容处理
 * 作者：
 * 日期：
 * 参数： 
 * 返回值：
 * 功能描述：化验单每一条数据里面质控内容的处理
*/
function get_zhikong($row) {
	global $arow,$global;
	// 根据质控配置处理平均值和相对偏侧列的显示
	$_avg = $row['ping_jun'];
	$_xdpc = $row['xiang_dui_pian_cha'];
	if(in_array($row['hy_flag'], array('5','20','21','25','60','65'))){
		// 室内平行和现场平行的平行原样的平均值
		$ZK_pingxing = new ZK_pingxingApp();
		$set = $ZK_pingxing->get_pingxing_config($row['water_type'], $row['vid']);
		// 原样不显示偏差
		if('1' == $set['xdpc_show']){
			$_xdpc = $_avg = '';
		}
	}else if(in_array( $row['hy_flag'], array('-40', '-60', '-66') )){
		$ZK_jiabiao = new Zk_jiabiaoApp();
		$set = $ZK_jiabiao->get_jiabiao_config($row['water_type'], $row['vid']);
		// 加标样是否显示理论浓度
		if('0' == $set['show_li']){
			$_avg = '';
		}
	}else if(in_array( $row['hy_flag'], array('-4', '-8') )){
		$ZK_biaoyang = new Zk_biaoyangApp();
		$set = $ZK_biaoyang->get_biaoyang_config($row['water_type'], $row['vid']);
		// 加标样是否显示理论浓度
		if($set['show_avg'] == 'no'){
			$_avg = '';
		}
		if('0' == $set['show_zkjg']){
			$_xdpc = $_avg = '';
		}
	}else if(in_array( $row['hy_flag'], array('3', '23', '43', '63') )){
		// 化验单未签字时不显示盲样评价信息
		if('' == $arow['sign_01']){
			$_xdpc = $_avg = '';
		}else{
			// 如果是标准样品，化验员签字后再样品编号后加一个星号
			$row['bar_code'] .= '*';
		}
	}
	// 空白,自控样无采样日期
	if(in_array($row['hy_flag'],array('-2','-4'))){
		$row['cy_date'] = '';
	}
	$row['pj_class'] = ($row['ping_jia']=='不合格') ? 'red' : '';
	// 填充空白单元格
	$_xdpc === '' && $_xdpc = $global['hyd']['tianchong_empty_cell'];
	$_avg === '' && $_avg = $global['hyd']['tianchong_empty_cell'];
	$row['_xdpc'] = $_xdpc ;
	$row['ping_jun'] = $_avg;
	return $row;
}
/** 
 * 功能：历史数据获取
 * 作者：Mr Zhou
 * 日期：2015-11-09
 * 参数：$row
 * 参数：$gs	获取的数据的个数
 * 返回值：
 * 功能描述：获取某个站点的历史数据
*/
function get_history_rec($row,$gs=5) {
	global $DB,$fzx_id;
	$rows = array();
	$query = $DB->query("SELECT `vd0`,`td31` AS `date` FROM `assay_order` o LEFT JOIN `assay_pay` p ON o.`tid`=p.`id` WHERE o.`sid`='{$row['sid']}' AND p.`fp_id`='{$fzx_id}' AND o.`vid`='{$row['vid']}' AND o.`hy_flag`>-1 ORDER BY p.`td31` DESC,o.`id` DESC LIMIT {$gs}");
	while($row=$DB->fetch_assoc($query)){
		$rows[] = $row;
	}
	return $rows;
}
/** 
 * 功能：历史同期数据
 * 作者：Mr Zhou
 * 日期：2015-11-09
 * 参数：$row
 * 参数：$gs	获取的数据的个数
 * 返回值：
 * 功能描述：
*/
function get_historical($row,$gs=5){
	global $DB,$fzx_id;
	$rows = array();
	for ($i=0; $i < $gs; $i++) { 
		if( '' != $row['vd0'] && strstr('<',$row['vd0']) ){
			$vd0	= $row['vd0'];
		}else{
			$vd0	= $row['vd0'] + ($row['vd0']/1)*rand(0,5);
		}
		$date	= date('Y-m-d',strtotime('-'.$i.' year',strtotime($row['date'])));
		$rows[$i]	= array('date' => $date, 'vd0' => $vd0);
	}
	return $rows;
}
/** 
 * 功能：获取浮窗内容
 * 作者：Mr Zhou
 * 日期：2015-11-09
 * 参数：$row
 * 返回值：
 * 功能描述：
*/
function get_popover_msg($arow, $row, $chaobiao){
	$popover_msg['title'] = '样品编号：'.$row['bar_code'].'<br />站点名称：'.$row['site_name'].'<br />项目名称：'.$arow['assay_element'];
	//历史数据
	$history_rows = get_history_rec($row,5);
	//历史同期
	// $historical_rows = get_historical($row,5);
	$max_count = 5;//max(count($historical_rows),count($history_rows));
	$popover_content = '<tr><th>最近五次</th></tr>';
	// $popover_content = '<tr><th>最近五次</th><th>历史同期</th></tr>';
	for ($i=0; $i < $max_count; $i++) { 
		$popover_content .= '<tr><td nowrap>';
		if(isset($history_rows[$i]) && !empty($history_rows[$i])){
			$popover_content .= $history_rows[$i]['date'].' ：'.$history_rows[$i]['vd0'];
		}
		// $popover_content .= '</td><td nowrap>';
		// if(isset($historical_rows[$i]) && !empty($historical_rows[$i])){
		// 	$popover_content .= $historical_rows[$i]['date'].' ：'.$historical_rows[$i]['vd0'];
		// }
		$popover_content .= '</td></tr>';
	}
	$popover_msg['content'] = '<table>'.$popover_content.'</table>';
	// $popover_msg['data'] = [];
	// if(empty($row['_vd0'])){
	// 	$row['_vd0'] = '0.213';
	// }
	// $popover_msg['data']['max'] = round_yxws(abs($row['_vd0'])*1.8,3);
	// $popover_msg['data']['min'] = round_yxws(abs($row['_vd0'])*0.7,3);
	// $popover_msg['data']['last_month'] = round_yxws(abs($row['_vd0'])*1.2,3);
	// $popover_msg['data']['last_year'] = round_yxws(abs($row['_vd0'])*1.1,3);
	// $popover_msg['data']['qx_max'] = round_yxws(abs($row['_vd0'])*0.5,3);
	// $popover_msg['data']['qx_min'] = round_yxws(abs($row['_vd0'])*1.5,3);
	// $popover_msg['data']['bz_xz'] = $chaobiao['jc_xz'];
	// eval('$popover_msg[\'content\'] = "'.gettemplate('hyd/assay_popover_msg').'";');
	return $popover_msg;
}
/** 
 * 功能：得到化验单
 * 作者：Mr zhou
 * 日期：2015-06-13 
 * 参数： 
 * 返回值：
 * 功能描述：
*/
function get_assay_form($arow){
	global $DB,$u,$bt_muban,$global,$zong_biao,$heng_biao,$dwname,$tid,$rooturl,$rootdir;
	$yq_bh = $global['hyd']['yq_bh'];
	empty($yq_bh) && ($yq_bh='yq_chucangbh');
	// $arow['file_code'] = 'BJSHJ-CX-11-2014-JL-22';
	$is_hy_user = ($u['id']==$arow['uid']|| $u['id']==$arow['uid2']) ? true : false;//是否是本项目的化验员
	//根据相应权限及请求更新检测方法
	if((($is_hy_user||$u['system_admin'])&&empty($arow['sign_02'])) || $u['admin']){ $arow = change_assay_method($arow); }

	$payJson	= is_array($arow['json']) ? $arow['json'] : json_decode($arow['json'],true);
	$year		= date( 'Y', strtotime($arow['time_01']) );
	$month		= date( 'm', strtotime($arow['time_01']) );
	$zongheng	= (in_array($arow['zongheng'],array('zong','heng'))?$arow['zongheng']:'heng').'_biao';//表格纵横板式
	$zongheng	= $$zongheng;//表格纵横板式的宽度
	empty($arow['table_name'])&&$arow['table_name']=$bt_muban[$arow['table_id']];
	$table_name	= $arow['table_name'];
	''!=$_GET['table_name'] && $table_name=$_GET['table_name'];
	$arow['zhanming'] = $zhanming = ($global['hyd']['code_jiema']['is_jiema']&&$arow[$global['hyd']['code_jiema']['sign']]) ? '站 名' : '样品编号';
	//被退回的化验单的 回退原因的显示 默认是关闭状态
	$huiTuiShow	= '';
	if(!in_array($arow['over'],array('已审核','已复核'))&&$payJson['退回']!=''){
			$huiTuiLiYou= end($payJson['退回']);
			$huiTuiShow = '<div class="panel hyd_tuihui">
				<div class="panel-heading">
					<h4 class="panel-title" style="text-align:left;">
						<a href="#collapseTwo_'.$arow['id'].'" data-parent="#accordion_'.$arow['id'].'" data-toggle="collapse" class="accordion-toggle collapsed">
							<i data-icon-show="icon-angle-right" data-icon-hide="icon-angle-down" class="bigger-110 icon-angle-right"></i>
							&nbsp;化验单退回信息
						</a>
					</h4>
				</div>
				<div id="collapseTwo_'.$arow['id'].'" class="panel-collapse collapse" style="color:red;text-align:left;">
					<div class="panel-body">
						<dl class="dl-horizontal">
							<dt>退 回 人：</dt><dd>'.$huiTuiLiYou['tuiHuiUser'].'</dd>
							<dt>退回时间：</dt><dd>'.$huiTuiLiYou['tuiHuiTime'].'</dd>
							<dt>退回原因：</dt><dd>'.$huiTuiLiYou['tuiHuiReason'].'</dd>
							<dt>修改理由：</dt><dd>'.$huiTuiLiYou['xiuGaiLiYou'].'</dd>
						</dl>
					</div>
				</div>
			</div>';
	}
	//当前化验单的配置与最新配置做比较，检查是否有人修改了该方法的配置，如果修改了就给化验单提醒，让化验员判断是否需要更新至最新配置
	$f_diff = '';
	if(!in_array($arow['over'],array('已审核','已复核'))){
		//检测方法，仪器，检出限，单位，表格
		$dl_horizontal = '';
		$sql = "SELECT xf.`unit`,am.`method_name` AS td1,am.`method_number` AS td2,xf.`jcx` AS td3,yq.`yq_mingcheng` AS td4,yq.`yq_xinghao` AS td5,yq.`{$yq_bh}` AS yq_bh,xf.`hyd_bg_id` table_id FROM `xmfa` xf LEFT JOIN `assay_method` am ON xf.`fangfa`=am.`id` LEFT JOIN `yiqi` yq ON xf.`yiqi`=yq.`id` WHERE xf.`id`='{$arow['fid']}'";
		$fang = $DB->fetch_one_assoc($sql);
		if($arow['td2'] != $fang['td2']){
			$dl_horizontal .= '<tr><td>检测方法编号</td><td>'.$arow['td2'].'</td><td class="red">'.$fang['td2'].'</td></tr>';
			$dl_horizontal .= '<tr><td>检测方法名称</td><td>'.$arow['td1'].'</td><td class="red">'.$fang['td1'].'</td></tr>';
		}
		if($arow['td3'] != $fang['td3'] || $arow['unit'] != $fang['unit']){
			$jcx_class = ($arow['td3'] != $fang['td3']) ? ' class="red"' : '';
			$unit_class = ($arow['unit'] != $fang['unit']) ? ' class="red"' : '';
			$dl_horizontal .= '<tr><td>检出限</td><td>'.$arow['td3'].'（'.$arow['unit'].'）</td><td><span '.$jcx_class.'>'.$fang['td3'].'</span><span '.$unit_class.'>（'.$fang['unit'].'）</span></td></tr>';
		}
		if($arow['td4'] != $fang['td4']){
			$dl_horizontal .= '<tr><td>仪器名称</td><td>'.$arow['td4'].'</td><td class="red">'.$fang['td4'].'</td></tr>';
		}
		if($arow['td5'] != $fang['td5']){
			$dl_horizontal .= '<tr><td>仪器型号</td><td>'.$arow['td5'].'</td><td class="red">'.$fang['td5'].'</td></tr>';
		}
		if($arow['yq_bh'] != $fang['yq_bh']){
			$dl_horizontal .= '<tr><td>仪器编号</td><td>'.$arow['yq_bh'].'</td><td class="red">'.$fang['yq_bh'].'</td></tr>';
		}
		if($arow['table_id'] != $fang['table_id']){
			$muban[$arow['table_id']] = $DB->fetch_one_assoc("SELECT `table_cname` FROM `bt_muban` WHERE `id`='{$arow['table_id']}'");
			$muban[$fang['table_id']] = $DB->fetch_one_assoc("SELECT `table_cname` FROM `bt_muban` WHERE `id`='{$fang['table_id']}'");
			$dl_horizontal .= '<tr><td>化验单表格</td><td>'.$muban[$arow['table_id']]['table_cname'].'</td><td class="red">'.$muban[$fang['table_id']]['table_cname'].'</td></tr>';
		}
		if(''==$dl_horizontal){
			$f_diff = '';
		}else{
			$f_diff = '<div class="panel hyd_f_diff">
				<div class="panel-heading">
					<h4 class="panel-title" style="text-align:left;">
						<a href="#collapseThree_'.$arow['id'].'" data-parent="#accordion_'.$arow['id'].'" data-toggle="collapse" class="accordion-toggle">
							<i data-icon-show="icon-angle-right" data-icon-hide="icon-angle-down" class="bigger-110 icon-angle-down"></i>
							&nbsp;您的化验单的配置已经被更改过，请确定是否需要更新至最新配置
						</a>
					</h4>
				</div>
				<div id="collapseThree_'.$arow['id'].'" class="panel-collapse in">
					<div class="panel-body">
						<table class="dl-horizontal single" style="width:100%">
							<tr><td>项目</td><td>化验单目前配置</td><td>最新配置</td></tr>
							'.$dl_horizontal.'
						</table>
						<div style="">
							<button class="btn btn-primary btn-sm fa_change_up_'.$arow['id'].'">更新配置<strong></strong></button>
						</div>
					</div>
				</div>
			</div>';
		}
	}
	if('0'!=$_GET['hyd_handle']){
		$hyd_handle = get_hyd_handle($arow,$is_hy_user);
	}
	$accordion = $f_diff.$huiTuiShow.$hyd_handle['html'];
	//保存按钮
	$arow['canModi'] = false;
	$assay_modi_submit = '';
	$a = ( FZX_ID == $arow['fzx_id'] || FZX_ID == $arow['fp_id']) ? true : false;
	$b = ( $is_hy_user && empty($arow['sign_01']) && empty($arow['sign_012']) ) ? true : false;
	if( $a && ( $b || $u['admin'] ) ){
		$arow['canModi'] = true;
		$assay_modi_submit = '<input class="btn btn-primary btn-sm hyd_sub_'.$arow['id'].'" type="button" name="action" value="保存" />';
	}
	//化验员签字日期显示
	if($arow['sign_date_01']!=''&&$arow['sign_date_012']!=''&&$arow['sign_date_012']<$arow['sign_date_01']){
		$arow['sign_date_01']=$arow['sign_date_012'];
	}
	//获取化验单签字表单
	$assay_sign_form = get_assay_form_sign($arow,'assay_pay');
	$arow['zhikong'] = $global['zk']['zhikong'];
	//加密令牌
	$_SESSION['token_key']['hyd'][$arow['id']] = md5(uniqid(rand()));
	$arow_json = json_encode($arow);

	// 声明化验单对象
	$AssayApp = new AssayApp();
	//化验单模板文件地址
	$plan_file_path = $global['hyd']['plan_file_path'];
	$filePath = "{$rootdir}/template/{$plan_file_path}/{$table_name}.txt";
	// 新模板
	if(is_file($filePath)){
		$arowData = $arow_json;
		$linesData = get_assay_hyd_line($arow['id'], $table_name, 2);
		$initialAttributedText = file_get_contents($filePath);
		$arowData = json_encode($arow);
		$linesData = json_encode($linesData);
		$hyd = $AssayApp->temp('hyd/assay_form_hyd_new', get_defined_vars());
	}else{
		$plan_file = "{$plan_file_path}/plan_{$table_name}.html";
		if(!is_file("{$rootdir}/template/{$plan_file}")){
			$plan = '<strong class="red">此化验单表格模板配置不正确,请在检测方法配置中重新配置!</strong>';
		}else{
			//环境条件的表格头部
			$hjtj_bt = temp($plan_file_path.'hjtj_bt');
			$aline = get_assay_hyd_line($arow['id'], $table_name);
			eval('$plan = "'.gettemplate($plan_file).'";');
		}
		$hyd = $AssayApp->temp('hyd/assay_form_hyd', get_defined_vars());
	}
	return $hyd;
}
function get_hyd_handle($arow,$is_hy_user){
	global $DB,$u,$fzx_id,$global;
	$tid = $arow['id'];
	$handle = array(
		'html' => '',
		'data' => array('DY' => '',/*打印按钮*/'FA' => '', /*方法切换*/'ZR' => '', /*载入按钮*/'FJ' => '', /*附件按钮*/'HT' => '', /*退回按钮*/'BH' => '', /*显示隐藏编号*/'MB' => '' /*模板切换*/)
	);
	//打印
	$title = '化验单号:'.$tid.',采样单号:'.$arow['cyd_id'].',化验项目编号:'.$arow['vid'].',表格号:'.$arow['table_name'];
	$handle['data']['DY'] = '<button title="'.$title.'" class="btn btn-primary btn-sm hyd_print_'.$tid.'"><i class="icon-print bigger-120"></i><strong>打印'. $tid .'</strong></button>';
	//方法切换
	// AND `xmfa`.`lxid` IN({$arow['lxid']})
	$sql = "SELECT xmfa.*,`leixing`.`lname`,`method_number`, `method_name` FROM `assay_method` LEFT JOIN `xmfa` ON xmfa.`fangfa` = assay_method.`id` LEFT JOIN `leixing` ON `xmfa`.`lxid` = `leixing`.id WHERE `xmfa`.`fzx_id`='{$arow['fp_id']}' AND `xmfa`.`xmid`='{$arow['vid']}' AND `xmfa`.`act`='1' ORDER BY `leixing`.`id`, `method_number`";
	$query = $DB->query($sql);
	$is_has_jcff = false;//是否正确配置了检测方法
	while($row = $DB->fetch_assoc( $query ) ){
		if($row['id'] == $arow['fid']){
			$selected = 'selected';
			$is_has_jcff = true;
			// $hyfa = '<option value="'.$row['id'].'"> &nbsp;更新本方法['.$row['method_number'].']至最新配置</option>'.$hyfa;
		}else{
			$selected = '';
		}
		$beizhu = empty($row['beizhu']) ? '' : '['.$row['beizhu'].']';
		$hyfa .= '<option '.$selected.' value="'.$row['id'].'">['.$row['lname'].']'.$beizhu.'['.$row['method_number'].']'.$row['method_name'].'</option>';
	}
	if(!$is_has_jcff){
		$hyfa = '<option>本化验单检测方法配置不正确，请重新选择</option>'.$hyfa;
	}
	$handle['data']['FA'] = '<select class="fa_change_'.$arow['id'].'" data="'.$arow['id'].'" style="width:280px">'.$hyfa.'</select>';
	//附件查看
	//由于个别化验单比如辽宁的汞，暂时无法做仪器载入，但是他们需要把仪器出的小条数据打印进系统，方便校核，所以暂时所有化验单都显示附件的按钮了
	$handle['data']['FJ'] = '<button class="btn btn-primary btn-sm pdf_files_'.$tid.'">查看附件</button>';
	//仪器载入配置存在fid关联配置和仪器id关联配置两种，需要根据数据库字段来具体区分
	$column_name = $DB->fetch_one_assoc("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='{$DB->dbname}' AND `TABLE_NAME`='yq_autoload_set' AND `COLUMN_NAME` IN ('fid','yq_id')");
	if('fid'==$column_name['COLUMN_NAME']){
		$load_rs = $DB->fetch_one_assoc("SELECT s.`fid` FROM `yq_autoload_set` s LEFT JOIN `xmfa` x ON s.`fid`=x.`fangfa` AND s.`fzx_id`=x.`fzx_id` WHERE x.`fzx_id`='$fzx_id' AND x.`id`='{$arow['fid']}'");
	}else if('yq_id'==$column_name['COLUMN_NAME']){
		 $load_rs = $DB->fetch_one_assoc("SELECT s.`yq_id` FROM `yq_autoload_set` s LEFT JOIN `xmfa` x ON s.`yq_id`=x.`yiqi` AND s.`fzx_id`=x.`fzx_id` WHERE x.`fzx_id`='$fzx_id' AND x.`id`='{$arow['fid']}'");
	}else{
		$load_rs = array();
	}
	if(!empty($load_rs[$column_name['COLUMN_NAME']])){
		//化验员在两个化验员都没有签字的时候可以看到载入按钮 和 仪器曲线添加按钮
		if($u['admin'] || ($is_hy_user&&empty($arow['sign_01'])&&empty($arow['sign_012']))){
			$handle['data']['ZR'] = '<button class="btn btn-primary btn-sm reloade_'.$tid.'" data="'.$arow['fid'].'" title="自动载入数据">载入</button>';
		}
		if('lzzls' == $global['hyd']['danwei']){
			$handle['data']['QX'] = '<button class="btn btn-primary btn-sm yiqi_sc_'.$tid.'" data="'.$arow['fid'].'" title="添加仪器曲线">曲线</button>';
		}
	}
	//回退按钮
	//校核人、复核人、审核人 在化验员 签字后 可以看到"回退按钮"
	if(FZX_ID==$arow['fp_id']&&(!empty($arow['sign_01']) || !empty($arow['sign_012']))){
			$jh_arr = explode("','",$u['user_other']['v1']);
			$fh_arr = explode("','",$u['user_other']['v2']);
			$sh_arr = explode("','",$u['user_other']['v3']);
			$a=$b=$c=false;
			//未复核并且具有校核该项目的权限
			(empty($arow['sign_03'])&&in_array($arow['vid'],$jh_arr))&&$b=true;
			//未审核并且具有复核该项目的权限
			(empty($arow['sign_04'])&&in_array($arow['vid'],$fh_arr))&&$b=true;
			//未**核并且具有审核该项目的权限
			(empty($arow['sign_05'])&&in_array($arow['vid'],$sh_arr))&&$c=true;
			if($a || $b || $c || $u['admin']){
				$handle['data']['HT'] = '<button class="btn btn-primary btn-sm tui_Hui_'.$tid.'" title="化验单退回（将化验单退回到->化验员“未签字”状态）" >退回化验单</button>';
			}

		// 显示隐藏样品编号
		$handle['data']['BH'] = '<button data-show="0" class="btn btn-primary btn-sm toggle_bar_'.$tid.'" data="'.$load_rs['fid'].'" title="显示隐藏编号">显示编号</button>';
	}
	//模板快递切换，方便开发使用
	if(intval($_SESSION['u']['debug'])){
	// if( $u['admin'] ){
		$muban = '';
		$sql = "SELECT `id`, `act`, `table_name`, `table_cname`, `zongheng` FROM `bt_muban` WHERE 1 ORDER BY `table_name`";
		$query = $DB->query($sql);
		while ($row = $DB->fetch_assoc($query)) {
			if($row['id'] == $arow['table_id']){
				$selected = 'selected';
				$is_has_jcff = true;
			}else{
				$selected = '';
			}
			$muban .= '<option '.$selected.' value="'.$row['id'].'">'.$row['table_cname'].'【'.$row['table_name'].'】</option>';
		}
		$handle['data']['MB'] = '<br /><select class="mb_change_'.$arow['id'].'" style="width:300px">'.$muban.'</select><br />';
	}
	//化验单操作 默认是打开状态
	$handle['html'] = '<div class="hyd_handle" style="margin: 10px">
		<!-- 打印链接 -->
		'.$handle['data']['DY'].'
		<!-- 化验方法切换 -->
		'.$handle['data']['FA'].'
		<!-- 数据载入 -->
		'.$handle['data']['ZR'].'
		<!-- 仪器曲线 -->
		'.$handle['data']['QX'].'
		<!-- 查看附件 -->
		'.$handle['data']['FJ'].'
		<!-- 退回按钮 -->
		'.$handle['data']['HT'].'
		<!-- 显示编号 -->
		'.$handle['data']['BH'].'
		<!-- 模板切换 -->
		'.$handle['data']['MB'].'
	</div>';
	return $handle;
}
/** 
 * 功能：得到化验单签字表单
 * 作者：Mr zhou
 * 日期：2015-06-13 
 * 参数： 
 * 返回值：
 * 功能描述：
*/
function get_assay_form_sign($arow,$table_name='assay_pay'){
	global $DB,$global,$u,$tid;
	!intval($tid) && $tid = $arow['id'];
	$admin		= $u['admin'];
	$columns	= array('sign_01','sign_012','sign_02','sign_03','sign_04');
	$userid_img = get_userid_img($table_name,$columns,$arow['id']);
	$fx_user	= $userid_img['sign_01'];
	$jh_user	= $userid_img['sign_02'];
	$fh_user	= $userid_img['sign_03'];
	$sh_user	= $userid_img['sign_04'];
	$fx_user2	= empty($arow['sign_012']) ? '' : '、'.$userid_img['sign_012'];
	if( (isset($arow['fp_id']) && FZX_ID == $arow['fp_id']) || (!isset($arow['fp_id']) && FZX_ID == $arow['fzx_id']) ){
		//未校核时开始签字任务
		if( empty($arow['sign_02']) ){
			//第一化验员
			if( empty($arow['sign_01'])  && ( $admin || $u['id'] == $arow['uid'] ) ){
				//第一个人没有签字，并且登录人是规定的第一个签字人
				$fx_user = '<input class="btn btn-primary btn-xs" type="button" name="fx_qz" value="签字" />';
			}else if( $arow['sign_01'] != $u['userid'] && $u['id'] == $arow['uid'] ){
				//如果第一签字人不是第一化验员，第一化验员可以签字，但是不再签字并保存
				$fx_user = '<input class="btn btn-primary btn-xs" type="button" name="fx2_qz" value="签字" />';
				$fx_user2= '、'.$userid_img['sign_01'];
			}else{
				$fx_user = $userid_img['sign_01'];
			}
			//第二化验员
			if( !empty($arow['uid2']) && $u['id'] == $arow['uid2'] ){
				if( !empty($arow['sign_012'])  ){
					$fx_user2 = ' 、'.$userid_img['sign_012'];
				}else if( empty($arow['sign_01']) ){
					//如果第二化验员签字时第一化验员还没签字，将会把第二化验员认为是第一签字人
					$fx_user2 = '<input class="btn btn-xs btn-primary" type="button" name="fx_qz" value="签字" />';
				}else if( $arow['sign_01'] != $u['userid'] ){
					//如果第二化验员不是第一签字人
					$fx_user2 = ' 、<input class="btn btn-xs btn-primary" type="button" name="fx2_qz" value="签字" />';
				}
			}
		}
		//有一个化验员签字后就可以开始审核
		$jh_user = $fh_user = $sh_user = '';
		if( !empty($arow['sign_01']) ){
			// 校核项目
			$jh_arr = explode("','",$u['user_other']['v1']);
			// 复核项目
			$fh_arr = explode("','",$u['user_other']['v2']);
			// 审核项目
			$sh_arr = explode("','",$u['user_other']['v3']);
			//校核部分的显示
			if( empty($arow['sign_02']) ){
				// && !in_array($u['userid'],array($arow['sign_01'], $arow['sign_012']))
				if( $admin || ( $u['jh']  && in_array($arow['vid'],$jh_arr) ) ){
					$jh_user='<input class="btn btn-xs btn-primary" type="button" name="jh_qz" value="签字" />';
				}
			}else{
				//复核部分的显示
				$jh_user = $userid_img['sign_02'];
				if( empty($arow['sign_03']) ){
					// && $u['userid'] != $arow['sign_02']
					if( $admin || ( $u['fh']  && in_array($arow['vid'],$fh_arr) ) ){
						$fh_user = '<input class="btn btn-xs btn-primary" type="button" name="fh_qz" value="签字" />';
					}
				}else{
					//审核部分的显示
					$fh_user=$userid_img['sign_03'];
					if( empty($arow['sign_04']) ){
						// && $u['userid'] != $arow['sign_03']
						if( $admin || ( $u['sh']  && in_array($arow['vid'],$sh_arr) ) ){
							$sh_user = '<input class="btn btn-xs btn-primary" type="button" name="sh_qz" value="签字" />';
						}
					}else{
						$sh_user=$userid_img['sign_04'];
					}
				}
			}
		}
	}
	//FORM表单HTML
	$assay_sign_form = '<form name="shehe_'.$tid.'" class="assay_sign_form" action="#"><table class="center">';
	//比较两个签字日期，使用较晚的签字日期
	$time_01 = intval(strtotime($arow['sign_date_01']));
	$time_02 = intval(strtotime($arow['sign_date_012']));
	// 分析签字日期
	$fx_sign_date = '';
	// 946702800 是日期2000-01-01
	// 默认使用第一签字人日期，签字日期显示年月日
	if($time_01 > 946702800){
		$fx_sign_date = date('Y-m-d', $time_01);
	}
	// 第二签字人签字后，取较晚的签字日期
	if(!empty($arow['sign_012']) && $time_02 > 946702800 && $time_01 < $time_02){
		$fx_sign_date = date('Y-m-d', $time_02);
	}
	//签字名称
	$sign_button = '<td>检测:'.$fx_user.$fx_user2.'</td>';
	//签字日期
	$sign_date	= '<td>'.$fx_sign_date.'</td>';
	//获取审核级数
	//审核级数设置：'sh_set'=> array('02'=>array('jh','校核'),[...])
	foreach ($global['hyd']['sh_set'] as $key => $sign) {
		//定义$user_button
		eval('$user_button = $'.$sign[0].'_user;');
		//签字名称
		$sign_button.= "<td>{$sign[1]}：{$user_button}</td>";
		//签字日期
		$date = empty($arow["sign_date_{$key}"]) ? '' : date('Y-m-d',strtotime($arow["sign_date_{$key}"]));
		//整合日期
		$sign_date	.= "<td class=\"{$sign[0]}_qz_{$tid}\">{$date}</td>";
	}
	$hide_sign_date = (true != $global['hyd']['hide_sign_date']) ? '' : 'style="display:none;"';
	$assay_sign_form .= '<tr>'.$sign_button.'</tr><tr '.$hide_sign_date.'>'.$sign_date.'</tr></table></form>';
	return $assay_sign_form;
}
/**
 * 功能：关联显示表头信息
 * 作者：Mr Zhou
 * 日期：
 * 参数1：[类型] [参数名] [参数解释]
 * 参数2：[类型] [参数名] [参数解释]
 * 返回值：
 * 描述：由于新生成化验单是没有默认插入表头信息以及关联的检测方法修改时不会更新已生成的化验单，所以在显示未签字的化验单是需要关联更新一下表头信息
 * 描述：未开始状态的化验单一并更新bt表的最新信息
*/
function up_new_hyd_bt( $hyd ) {
	global $DB,$u,$global;
	$a = $b = $c = $d = false;
	('未开始'!=$hyd['over']) && $a = true;
	($u['userid']==$hyd['userid']) && $b = true;
	($u['userid']==$hyd['userid2']) && $c = true;
	if( $a || !( $b || $c || $u['admin'] ) ){
		return $hyd;
	}
	$yq_bh = $global['hyd']['yq_bh'];
	empty($yq_bh) && ($yq_bh='yq_chucangbh');
	//更新bt表的信息
	$sql = "SELECT `b`.*,`x`.`unit`,`x`.`hyd_bg_id` AS `table_id`,`m`.`method_name` AS `td1`,`m`.`method_number` AS `td2`,`x`.`jcx` AS `td3`,`y`.`yq_mingcheng` AS `td4`,`y`.`yq_xinghao` AS `td5`,`y`.`{$yq_bh}` AS `yq_bh`,CURDATE() `td31` FROM `xmfa` AS `x` LEFT JOIN `bt` AS `b` ON `x`.`id`=`b`.`fid` LEFT JOIN `assay_method` AS `m` ON `x`.`fangfa`=`m`.`id` LEFT JOIN `yiqi` AS `y` ON `x`.`yiqi`=`y`.`id` WHERE `x`.`id`='{$hyd['fid']}'";
	$row = $DB->fetch_one_assoc($sql);
	//温湿度
	$row['32'] = $global['hyd']['wendu'];//默认温度
	$row['33'] = $global['hyd']['shidu'];//默认湿度
	$row['scid'] = $row['bdid'] = 0;
	//更新曲线信息
	$sc=$DB->fetch_one_assoc("SELECT `id`,`CA`,`CB` FROM `standard_curve` WHERE `vid`='{$hyd['vid']}' AND `fzx_id`='{$hyd['fp_id']}' ORDER BY `id` DESC");
	if( intval($sc['id']) ){
		$row['scid'] = $sc['id'];
		$row['CA'] = $sc['CA'];
		$row['CB'] = $sc['CB'];
		$row['CT'] = $sc['CT'];
		$row['CR'] = $sc['CR'];
	}else{
		$bd=$DB->fetch_one_assoc("SELECT `id` FROM `jzry_bd` WHERE `vid`='{$hyd['vid']}' AND `fzx_id`='{$hyd['fp_id']}' ORDER BY `id` DESC");
		if( intval($bd['id']) ){
			$row['bdid']= $bd['id'];
			$row['CA']= $bd['bzry_name'];
			$row['CB']= $bd['bzry_nongdu'];
		}
	}
	$sql_where = array();
	for ($i=1; $i <= 33; $i++) {
		$key = "td{$i}";
		$sql_where[$i] = "`$key`='{$row[$key]}'";
	}
	// 如果没有进行任务接收，则在第一次打开化验时更新状态
	$hyd['json'] =  json_decode($hyd['json'],true);
	if(!isset($hyd['json']['rwjs'])){
		$hyd['json']['rwjs'] = array(
			'zt' => '已接受',
			'js_date'=>date('Y-m-d H:i:s')
		);
		$sql_where['json'] = "`json` = '".JSON($hyd['json'])."'";
	}
	$sql_where['CA']		= "`CA`='{$row['CA']}'";
	$sql_where['CB']		= "`CB`='{$row['CB']}'";
	$sql_where['CT']		= "`CA`='{$row['CT']}'";
	$sql_where['CR']		= "`CB`='{$row['CR']}'";
	$sql_where['btdata']	= "`btdata`='{$row['btdata']}'";
	$sql_where['scid']		= "`scid`='{$row['scid']}'";
	$sql_where['bdid']		= "`bdid`='{$row['bdid']}'";
	$sql_where['unit']		= "`unit`='{$row['unit']}'";
	$sql_where['yq_bh']		= "`yq_bh`='{$row['yq_bh']}'";
	$sql_where['time_01']	= "`time_01`='{$row['td31']}'";
	$sql_where['start_time']= "`start_time`='".date('Y-m-d H:i:s')."'";
	// $sql_where['td32']		= "`td32`='{$row['32']}'";
	// $sql_where['td33']		= "`td33`='{$row['33']}'";
	$sql_where['table_id']	= "`table_id`='{$row['table_id']}'";
	if($u['admin'] || in_array($u['id'], array($hyd['uid'],$hyd['uid2']))){
		$sql_where['over'] = "`over`='已开始'";
	}
	$sql_str = implode(' , ', $sql_where );
	$query = $DB->query("UPDATE `assay_pay` SET {$sql_str} WHERE `id`='{$hyd['id']}' LIMIT 1");
	if( !$query ){
		return array();
	}else{
		return $DB->fetch_one_assoc("SELECT * FROM `assay_pay` AS `ap` WHERE `id`='{$hyd['id']}' LIMIT 1");
	}
}
/**
 * 功能：更新检测方法配置项
 * 作者：Mr Zhou
 * 日期：
 * 返回值：
 * 描述：
*/
function change_assay_method($arow){
	global $DB,$u,$global;
	$fzx_id = $arow['fp_id'];
	$yq_bh = $global['hyd']['yq_bh'];
	empty($yq_bh) && ($yq_bh='yq_chucangbh');
	//查询该项目在本中心配置的的所有检测方法
	$sql = "SELECT xmfa.*,`leixing`.`lname`,`method_number`, `method_name`,`yq_mingcheng` ,`yq_xinghao`,`{$yq_bh}` AS yq_bh,btm.`table_name` FROM `assay_method` LEFT JOIN `xmfa` ON xmfa.`fangfa` = assay_method.`id` LEFT JOIN `yiqi` yq ON `xmfa`.`yiqi`=yq.`id` LEFT JOIN `leixing` ON `xmfa`.`lxid` = `leixing`.id LEFT JOIN `bt_muban` btm ON `xmfa`.`hyd_bg_id`=btm.`id` WHERE `xmfa`.`fzx_id`='{$arow['fp_id']}' AND `xmid`='{$arow['vid']}' AND `xmfa`.`act`='1' ";

	if(intval($_GET['upfid'])){
		//在化验单页面修改本化验单的检测方法
		$row = $DB->fetch_one_assoc($sql.' AND `xmfa`.id = '.intval($_GET['upfid']));
		if($row['id']){
			//切换方法了,调用表头数据并且更新pay表中数据
			$DB->query("UPDATE `assay_pay` SET `unit`='{$row['unit']}', `td1`='{$row['method_name']}', `td2`='{$row['method_number']}',`td3`='{$row['jcx']}',`td4`='{$row['yq_mingcheng']}',`td5`='{$row['yq_xinghao']}',`yq_bh`='{$row['yq_bh']}',`fid`='{$row['id']}',`table_id`='{$row['hyd_bg_id']}' WHERE `id` = '{$arow['id']}'");
			$arow = get_hyd_data($arow['id']);
			$arow['table_name'] = $row['table_name'];
		}
	}
	//admin账户可以随意切换表格来进行模板测试
	if($u['admin'] && intval($_GET['up_table_id'])){
		$table_id = intval($_GET['up_table_id']);
		$muban = $DB->fetch_one_assoc("SELECT `table_name`,`zongheng` FROM `bt_muban` WHERE `id`='{$table_id}' ");
		$DB->query("UPDATE `assay_pay` SET `table_id`='{$table_id}' WHERE `id` = '{$arow['id']}' LIMIT 1");
		$arow['table_id']	= $table_id;
		$arow['zongheng']	= $muban['zongheng'];
		$arow['table_name']	= $muban['table_name'];
	}
	return $arow;
}
/**
 * 功能：更新化验结果(兼容多合一)
 * 作者：
 * 日期：
 * 参数1：[类型] [参数名] [参数解释]
 * 参数2：[类型] [参数名] [参数解释]
 * 返回值：
 * 描述：更新化验结果,增加多合一的计算
*/
function calc_a_task($id, &$vdx, $tid, $vid, $xmfa_data) {
	global $DB,$dhy_arr,$vid_hyd,$u;
	if(!$_POST['isJexcel']){
		$sql = "SELECT `vid`,`vd0`,`hy_flag`,`_vd0`,`js_gongshi` FROM `assay_order` WHERE `id` ='{$id}' LIMIT 1";
		$v0 = $DB->fetch_one_assoc($sql);
		$vdx['vd0'] = trim($vdx['vd0']);
		if(''!=$vdx['_vd0']&&$vdx['vd0'][0]=='<'||$vdx['vd0']===$v0['vd0']){
			$vdx['_vd0']=$v0['_vd0'];
		}else{
			$vdx['_vd0']=$vdx['vd0'];
		}
		//多合一
		if(intval($dhy_arr[$vid])){
			foreach ($dhy_arr['vd'][$dhy_arr[$vid]] as $key => $value) {
				$vdx[$value['_vd0']] = $vdx[$value['vd0']];
				$tid = (''!=$vid_hyd[$vid]['tid']) ? $vid_hyd[$vid]['tid']:$tid;
				$add = intval($dhy_arr['ad'][$dhy_arr[$vid]][$key]['vd0']);
				$vdx[$value['vd0']] = round_value( $vdx[$value['_vd0']],$tid,$add, 'auto', $xmfa_data);
			}
		}
		// 指定修约的列
		if( isset($_POST['round_columns']) && !empty($_POST['round_columns']) ){
			foreach ($_POST['round_columns'] as $key => $value) {
				$vdx[$value] = round_value( $vdx[$value], $tid, $add, true, $xmfa_data);
			}
		}
		$vdx['vd0'] = round_value( $vdx['_vd0'], $tid, $add, 'auto', $xmfa_data);
		// js_gongshi 字段更新
		$v0['js_gongshi'] = empty($v0['js_gongshi']) ? array() : json_decode($v0['js_gongshi'], true);
		// 赋新值
		$v0['js_gongshi']['jsgs_msg'] = $vdx['js_gongshi'];
		$v0['js_gongshi'] = JSON($v0['js_gongshi'], true);
		// 删除$vdx['js_gongshi'],避免下面的循环重新赋值更新js_gongshi字段
		unset($vdx['js_gongshi']);
	}
	/**********修约结束**********/
	$vd_data=array();
	$vd_data[] = "`js_gongshi`='{$v0['js_gongshi']}'";
	//经过公式计算过的数值 给数组 $vd_data 更新该化验任务的 vd0-31
	while($one_vd=each($vdx)){
		$vd_data[]="`{$one_vd['key']}`='{$one_vd['value']}'";
	}
	$vd_data_str = implode( ',', $vd_data );
	$task_status = ( $vdx['vd0'] !== '' ) ? ",assay_over='1' " : ", assay_over='S' ";
	//把经过计算 的值 的数组 加入 sql 语句。
	$sql = "UPDATE `assay_order` SET {$vd_data_str} {$task_status} WHERE `id` = '$id' AND `tid` = '$tid'";
	$DB->query($sql);
}
/**
 * 功能：化验单数值修约
 * 作者：
 * 日期：
 * 参数1：[string] [$bar_code] [样品编号]
 * 返回值：[string] $row['site_name'] [站点名称]
 * 描述：化验单数值修约
*/
function round_value($_vd0, $tid, $add=0, $check_jcx='auto', $w=[]) {
	global $DB,$global,$u;
	if(!is_numeric($_vd0)){
		return $_vd0;
	}
	$vd0 = $_vd0;
	$add = intval($add);
	if(empty($w)){
		$w = $DB->fetch_one_assoc("SELECT xf.*,ap.`vid` FROM `xmfa` xf LEFT JOIN `assay_pay` ap ON ap.`fid`=xf.`id` WHERE ap.`id`='{$tid}'");
	}
	// 根据检测方法配置判断是否判断检出限和修约函数
	// $check_jcx = $check_jcx;
	if('auto' === $check_jcx || '' === $check_jcx){
		// 1是判断检出限，2是不判断检出限
		$check_jcx = true;
		if(isset($w['check_jcx']) && '2' == $w['check_jcx']){
			$check_jcx = false;
		}
	}
	// 修约函数，仅对固定小数位数的修约方式有效
	$_round = '_round';
	if(isset($w['round_func']) && in_array($w['round_func'], ['round', '_round'])){
		$_round = $w['round_func'];
	}
	if(strlen($_vd0) > 0){
		if( !empty($w['blws']) ){
			$blws = json_decode($w['blws'],true);
			foreach ($blws as $key => $row) {
				if( $_vd0 >= $row[0] ){
					$vd0 = round_yxws($_vd0, $row[1]+$add, $row[2]+$add);
					break;
				}
			}
		}else {
			if($_vd0 < 1){
				$vd0 = !is_numeric($w['w1']) ? $_vd0 : $_round($_vd0, $w['w1'] + $add);
			}elseif($_vd0 < 10 && $_vd0 >= 1){
				$vd0 = !is_numeric($w['w2']) ? $_vd0 : $_round($_vd0, $w['w2'] + $add);
			}elseif($_vd0 < 100 && $_vd0 >= 10){
				$vd0 = !is_numeric($w['w3']) ? $_vd0 : $_round($_vd0, $w['w3'] + $add);
			}elseif($_vd0 < 1000 && $_vd0 >= 100){
				$vd0 = !is_numeric($w['w4']) ? $_vd0 : $_round($_vd0, $w['w4'] + $add);
			}else{
				$vd0 = !is_numeric($w['w5']) ? $_vd0 : $_round($_vd0, $w['w5'] + $add);
			}
		}
	}
	//使用哪个值进行检出限判断，_vd0表示先判定检出限再修约，vd0表示先修约再判定
	$check_jcx_with_value = (isset($global['hyd']['check_jcx_with_value']) && !empty($global['hyd']['check_jcx_with_value'])) ? $global['hyd']['check_jcx_with_value'] : '_vd0';
	if( true === $check_jcx ){
		if( '-' == $w['jcx'] && empty($$check_jcx_with_value)){
			if('hfzls' == $global['hyd']['danwei']){
				$vd0 = '未检出';
			}
		}else if( floatval($w['jcx']) > 0 && $$check_jcx_with_value < floatval($w['jcx']) ){
			$vd0 = "<{$w['jcx']}";
		}
	}
	return $vd0;
}
/**
 * 功能：有样品编号获取站点名称
 * 作者：
 * 日期：
 * 参数1：[string] [$bar_code] [样品编号]
 * 返回值：[string] $row['site_name'] [站点名称]
 * 描述：更新化验结果
*/
function get_site_name_from_bar_code( $bar_code ) {
	global $DB;
	$row = $DB->fetch_one_assoc("SELECT s.`site_name` FROM `cy_rec` c LEFT JOIN `sites` s ON s.`id` = c.`sid` WHERE `bar_code` = '$bar_code' LIMIT 1");
	return $row['site_name'];
}
/**
 * 功能：输出错误提示
 * 作者：Mr Zhou
 * 日期：2015-11-01
 * 参数1：[string] [$error_content] [错误提示]
 * 描述：输出错误提示
*/
function error_msg($error_content){
	die(json_encode(array('error'=>'1','content'=>$error_content)));
}
// 获取水样类型函数
if(!function_exists('get_water_types')){
	function get_water_types( $site_type = 0 ) {
		global $DB;
		$sql = "SELECT DISTINCT water_type FROM sites WHERE water_type != '' ";
		if( !is_null($site_type) )
			$sql .= " AND site_type = '$site_type' ";
		$sql .= " ORDER BY water_type ";
		$res = $DB->query( $sql );
		$result = array();
		while( $row = $DB->fetch_assoc( $res ) )
			$result[] = $row['water_type'];
		return $result;
	}
}