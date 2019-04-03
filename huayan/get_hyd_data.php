 <?php
/**
 * 功能：
 * 作者: Mr
 * 日期: 2015-11-01
 * 描述:这里是需要特殊处理数据的化验单函数
 */
//'110,125,128,174,181,494,567,592,598,601,605,651,653,655,657'
/**
 * 功能：动植物油的化验单
 * 日期：2015-07-17
 * 参数：
 * 返回值：
 * 功能描述：动植物油浓度等于总油浓度加上石油浓度
 */
function getHydData_110($pay) {
	global $DB, $global;
	if ('lnsw' != $global['hyd']['danwei']) {
		return false;
	}
	//110 动植物油，603 总油，108石油类。
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(110,627,108) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['110'] as $key => $value) {
		$vd1 = is_numeric($orders[627][$value['bar_code']]['vd0']) ? $orders[627][$value['bar_code']]['vd0'] : 0;
		$vd2 = is_numeric($orders[108][$value['bar_code']]['vd0']) ? $orders[108][$value['bar_code']]['vd0'] : 0;
		$_vd0 = $vd1 - $vd2;
		$vd0 = round_value($_vd0, $pay['fid']);
		$sql = "UPDATE `assay_order` SET `vd1`='{$orders[627][$value['bar_code']]['vd0']}',`vd2`='{$orders[108][$value['bar_code']]['vd0']}',`vd0`='{$vd0}',`_vd0`='{$_vd0}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}

function getHydData_119($pay) {
	global $DB, $global;
	if ('yunnan' != $global['hyd']['danwei']) {
		return false;
	}

	// (徐靖2018年10月19日)114五日生化需氧量项目的vd4数据改为从119耗氧量项目的vd27中提取,暂不使用154项目的数据
	// $sql = "SELECT * FROM `assay_order` WHERE `cyd_id` in (select cyd_id from assay_order where tid ={$pay['id']} group by cyd_id) AND `vid` IN(119,154) ";
	// $query = $DB->query($sql);
	// while ($row=$DB->fetch_assoc($query)) {
	// 	$orders[$row['vid']][$row['bar_code']]=$row;
	// }
	// $sql = '';
	// $table_sql="SELECT * FROM `bt_muban` WHERE `id` =".$pay['table_id'];
	// $table_value=$DB->fetch_assoc($DB->query($table_sql));
	// if($table_value['table_name']=="bod5_yq"){
	// 	foreach ($orders['119'] as $key => $value) {
	// 		//$vd12 = round_value($value['vd12'],$orders['154'][$key]['fid']);
	// 		if($value['vd4']==""){
	// 			$vd4=_round($orders[154][$value['bar_code']]['vd0'],1);
	//             $sql = "UPDATE `assay_order` SET `vd4`='{$vd4}' WHERE `id` = '{$value['id']}'";
	//             $DB->query($sql);
	// 		}
	// 	 }
	// }

	$sql2 = "SELECT * FROM `assay_order` WHERE `cyd_id` in (select cyd_id from assay_order where tid ={$pay['id']} group by cyd_id) AND `vid` IN(119,114) ";
	$query2 = $DB->query($sql2);
	while ($row2 = $DB->fetch_assoc($query2)) {
		$orders2[$row2['vid']][$row2['bar_code']] = $row2;
	}
	$table_sql2 = "SELECT * FROM `bt_muban` WHERE `id` =" . $pay['table_id'];
	$table_value2 = $DB->fetch_assoc($DB->query($table_sql2));

	if ($table_value2['table_name'] == "bod5_jzxs" || $table_value2['table_name'] == "bod5_yq") {
		foreach ($orders2['119'] as $key2 => $value2) {
			if ($value2['vd4'] == "") {
				$vd4 = $orders2[114][$value2['bar_code']]['vd27'];
				$sql2 = "UPDATE `assay_order` SET `vd4`='{$vd4}' WHERE `id` = '{$value2['id']}'";
				$DB->query($sql2);
			}
		}
	}
}

/**
 * 功能：丽江溶解氧仪器法换算值根据气压进行计算(徐靖)
 * 日期：2018-10-11
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_114($pay) {
	global $DB, $global;
	if ('yunnan' != $global['hyd']['danwei']) {
		return false;
	}
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id` in (select cyd_id from assay_order where tid ={$pay['id']} group by cyd_id) AND `vid` = 114 ";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql1 = "SELECT * FROM `cy_rec` WHERE `cyd_id` in (select cyd_id from assay_order where tid ={$pay['id']} group by cyd_id)";
	$query1 = $DB->query($sql1);
	while ($row1 = $DB->fetch_assoc($query1)) {
		$orders1[$row1['vid']][$row1['bar_code']] = $row1;
	}

	$table_sql = "SELECT * FROM `bt_muban` WHERE `id` =" . $pay['table_id'];
	$table_value = $DB->fetch_assoc($DB->query($table_sql));
	if ($table_value['table_name'] == "yi_qi_rong_jie_yang10_lijiang") {
		foreach ($orders['114'] as $key => $value) {
			if ($value['vd25'] == "") {
				$vd25 = reset($orders1)[$value['bar_code']]['qi_ya'];
				$sql = "UPDATE `assay_order` SET `vd25`='{$vd25}' WHERE `id` = '{$value['id']}'";
				$DB->query($sql);
			}
		}
	}
}

/**
 * 功能：总碱度的化验单
 * 日期：2015-07-17
 * 参数：
 * 返回值：
 * 功能描述：给其他项目加上质控数据
 */
function getHydData_125($pay) {
	global $DB, $global;
	if ('bjyth' != $global['hyd']['danwei']) {
		return false;
	}
	//125总碱度 188碳酸盐 189重碳酸盐 575氢氧化物
	$sql = "SELECT ao.*,p.fid FROM `assay_order` ao left join assay_pay p on ao.tid=p.id WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(125,188,189) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['125'] as $key => $value) {
		$vd12 = round_value($value['vd12'], $orders['189'][$key]['fid']);
		$vd17 = round_value($value['vd17'], $orders['188'][$key]['fid']);
		$sql = "UPDATE `assay_order` SET
			`vd10`='{$orders[189][$value['bar_code']]['ping_jun']}',`vd11`='{$orders[189][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd12`='{$vd12}',
			`vd15`='{$orders[188][$value['bar_code']]['ping_jun']}',`vd16`='{$orders[188][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd17`='{$vd17}',
			`vd20`='{$orders[125][$value['bar_code']]['ping_jun']}',`vd21`='{$orders[125][$value['bar_code']]['xiang_dui_pian_cha']}'
			WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：侵蚀性二氧化碳的化验单处理
 * 作者：Mr Zhou
 * 日期：2014-12-01
 * 参数：
 * 返回值：
 * 功能描述：1、与总碱度有关联，只有总碱度=重碳酸盐碱度(P=0)时才滴定侵蚀性二氧化碳；
 *		   2、侵蚀性二氧化碳计算时要减去总碱度的用量
 */
function getHydData_128($pay) {
	global $DB, $dhy_arr, $global;
	if ('lnsw' != $global['hyd']['danwei']) {
		return false;
	}
	//125 总碱度 128 侵蚀性二氧化碳
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(125,128) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['128'] as $key => $value) {
		$P = $orders[125][$value['bar_code']]['vd4'];
		$M = $orders[125][$value['bar_code']]['vd7'];
		if (floatval($P) > 0) {
			$M = '-';
		}
		$sql = "UPDATE `assay_order` SET `vd12`='{$M}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：离子色谱的化验单
 * 日期：2015-09-02
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_181($pay) {
	global $DB, $global;
	if ('bjyth' != $global['hyd']['danwei']) {
		return false;
	}
	//181氟化物氯化物182硫化物185硝酸盐氮186
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN(181,182,185,186) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['181'] as $key => $value) {
		$sql = "UPDATE `assay_order` SET
			`vd19`='{$orders[181][$value['bar_code']]['ping_jun']}',`vd20`='{$orders[181][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd21`='{$orders[182][$value['bar_code']]['ping_jun']}',`vd22`='{$orders[182][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd23`='{$orders[186][$value['bar_code']]['ping_jun']}',`vd24`='{$orders[186][$value['bar_code']]['xiang_dui_pian_cha']}',
			`vd25`='{$orders[185][$value['bar_code']]['ping_jun']}',`vd26`='{$orders[185][$value['bar_code']]['xiang_dui_pian_cha']}'
			WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：镁的化验单
 * 作者：Mr Zhou
 * 日期：2014-11-30
 * 参数：
 * 返回值：
 * 功能描述：镁是有减差法来测的，即总硬度的值减去钙的值
 */
function getHydData_173($pay) {
	//print_rr( $pay  );
	global $DB, $global;
	if (!in_array($global['hyd']['danwei'], array('lnsw', 'yunnan', 'hljsw'))) {
		return false;
	}
	//vid 103总硬度  173钙 174镁
	//vd2总硬度标液用量V0(mL)
	//vd3总硬度空白用量V1(mL)
	//vd4总硬度取样体积V2(mL)
	//vd5钙标液用量V3(mL)
	//vd6钙取样体积V4(mL)
	//td26总硬度化验单id
	//td27钙化验单id
	$sql = "select  cyd_id from assay_order where tid = {$pay['id']} group by cyd_id";

	$query = $DB->query($sql);
	$num = $DB->num_rows($query);
	if ($num > 1) {
		while ($row = $DB->fetch_assoc($query)) {
			$cyd[] = $row['cyd_id'];
		}
	} else {
		$cyd = $pay['cyd_id'];
	}

	if (is_array($cyd)) {
		$cyd = implode(',', $cyd);
		$sql = "SELECT * FROM `assay_order` WHERE `cyd_id` in ({$cyd}) AND `vid` IN(103,173,174) ORDER BY `id`";
	} else {
		$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$cyd}' AND `vid` IN(103,173,174) ORDER BY `id`";
	}

	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {

		// print_rr($row);
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['173'] as $key => $value) {
		// print_rr($value);
		if (!$orders[173][$value['bar_code']]['vd20']) {
			$sql = "UPDATE `assay_order` SET `vd20`='{$orders[103][$value['bar_code']]['vd16']}' WHERE `id` = '{$value['id']}'";
			$DB->query($sql);
		}
	}
}
/**
 * 功能：三卤甲烷
 * 作者：
 * 日期：2015-07-09
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_494($pay) {
	global $DB, $global;
	if ('qdzls' != $global['hyd']['danwei']) {
		return false;
	}
	//三卤甲烷	  vid 494
	//三氯甲烷	  vid 496   限值  60
	//三溴甲烷	  vid 497   限值  100
	//一氯二溴甲烷  vid 498   限值  100
	//二氯一溴甲烷  vid 499   限值  60
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id` ='{$pay['cyd_id']}' AND `vid` IN ('494','496','497','498','499')";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	//将三卤甲烷分量的vid和限值放在一个数组里面
	$sljw_arr = array('496' => '60', '499' => '60', '498' => '100', '497' => '100');
	foreach ($orders[494] as $key => $value) {
		$bar = $value['bar_code'];
		$zongLiang = 0.00;
		foreach ($sljw_arr as $vid => $xz) {
			if ('<' == $orders[$vid][$bar]['vd0'][0]) {
				//如果检测结果小于检出限使用检出限的一半除以限值得到该项目的分量值
				$fenLiang = floatval(str_replace('<', '', $orders[$vid][$bar]['vd0'])) / 2 / $xz * 1000;
			} else {
				$fenLiang = floatval($orders[$vid][$bar]['_vd0']) / $xz * 1000;
			}
			$zongLiang += $fenLiang;
		}
		$vd0 = round_value($zongLiang, $pay['fid']);
		$sql = "UPDATE `assay_order` SET `_vd0`='$zongLiang',`vd0` = '$vd0',
				`vd3`='{$orders['496'][$bar]['vd0']}', `vd4` ='{$orders['496'][$bar]['_vd0']}',`vd5` ='{$orders['496'][$bar]['tid']}',
				`vd6`='{$orders['497'][$bar]['vd0']}', `vd7` ='{$orders['497'][$bar]['_vd0']}',`vd8` ='{$orders['497'][$bar]['tid']}',
				`vd9`='{$orders['498'][$bar]['vd0']}', `vd10`='{$orders['498'][$bar]['_vd0']}',`vd11`='{$orders['498'][$bar]['tid']}',
				`vd12`='{$orders['499'][$bar]['vd0']}',`vd13`='{$orders['499'][$bar]['_vd0']}',`vd14`='{$orders['499'][$bar]['tid']}'
				WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：矿化度的化验单
 * 作者：Mr Zhou
 * 日期：2015-05-07
 * 参数：
 * 返回值：
 * 功能描述：矿化度的检测结果计算中要减去二分之一的重碳酸盐的含量
 */
function getHydData_567($pay) {
	global $DB, $global;
	if (!in_array($global['hyd']['danwei'], array('lnsw', 'hljsw'))) {
		return false;
	}
	//vid 567矿化度  188重碳酸盐
	//vd9 存储重碳酸盐含量
	$sql = "select distinct cyd_id from assay_order where tid = {$pay['id']} group by cyd_id";
	$query = $DB->query($sql);
	$num = $DB->num_rows($query);
	if ($num > 1) {
		while ($row = $DB->fetch_assoc($query)) {
			$cyd[] = $row['cyd_id'];
		}
	} else {
		$cyd = $pay['cyd_id'];
	}
	if (is_array($cyd)) {
		$cyd = implode(',', $cyd);
		$sql = "SELECT * FROM `assay_order` WHERE `cyd_id` in ({$cyd}) AND `vid` in(567,188) ORDER BY `id`";
	} else {
		$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$cyd}' AND `vid` in(567,188) ORDER BY `id`";
	}
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders['567'] as $key => $value) {
		$sql = "UPDATE `assay_order` SET `vd9`='{$orders[188][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'"; //将对应项目的结果值赋给本项目的对应字段
		$DB->query($sql);
	}
}
/**
 * 功能：硬度的化验单
 * 作者：Mr Zhou
 * 日期：2015-04-20
 * 参数：
 * 返回值：
 * 功能描述：硬度是有减差法来测的，即总硬度的值碳酸盐硬度的值
 */
function getHydData_592($pay) {
	getHydData_598($pay);
}
function getHydData_598($pay) {
	global $DB, $global;
	if ('qdzls' != $global['hyd']['danwei']) {
		return false;
	}
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN({$pay['vid']},103,595) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		if ('' != $row['ping_jun']) {
			$row['vd0'] = $row['ping_jun'];
			//如果是平行样，将原样的vd0改为平均值
			$orders[$row['vid']][str_replace('P', '', $row['bar_code'])]['vd0'] = $row['ping_jun'];
		}
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders[$pay['vid']] as $key => $value) {
		$sql = "UPDATE `assay_order` SET `vd3`='{$orders[103][$value['bar_code']]['vd0']}',`vd4`='{$orders[595][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：三价铁的化验单
 * 作者：Mr Zhou
 * 日期：2015-05-14
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_601($pay) {
	global $DB, $global;
	if ('lnsw' != $global['hyd']['danwei']) {
		return false;
	}
	//vid 601 三价铁 599 二价铁 153 总铁 154 铁
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid`in(601,599,153,154) ORDER BY `id`"; //查询出本项目和对应的项目
	$query = $DB->query($sql);
	$tid = array();
	while ($row = $DB->fetch_assoc($query)) {
		$tid[$row['vid']] = $row['tid'];
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$td7 = $tid[153] ? $tid[153] : $tid[154];
	$td8 = $tid[599];
	$sql = "UPDATE `assay_pay` SET `td7`='$td7',`td8`='$td8' WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid`=601"; //将对应项目的结果值赋给本项目的对应字段
	$DB->query($sql);
	foreach ($orders['601'] as $key => $value) {
		$Fe = $tid[153] ? $orders[153][$value['bar_code']]['_vd0'] : $orders[154][$value['bar_code']]['_vd0'];
		$vd0 = $Fe - $orders[599][$value['bar_code']]['_vd0'];
		$sql = "UPDATE `assay_order` SET `vd3`='$Fe',`vd4`='{$orders[599][$value['bar_code']]['_vd0']}' WHERE `id` = '{$value['id']}'"; //将对应项目的结果值赋给本项目的对应字段
		$DB->query($sql);
	}
}
/**
 * 功能：盐基度的化验单
 * 作者：Mr Zhou
 * 日期：2015-04-20
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_605($pay) {
	global $DB, $global;
	if ('qdzls' != $global['hyd']['danwei']) {
		return false;
	}
	$sql = "SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN({$pay['vid']},603) ORDER BY `id`";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		if ('' != $row['ping_jun']) {
			$row['vd0'] = $row['ping_jun'];
			//如果是平行样，将原样的vd0改为平均值
			$orders[$row['vid']][str_replace('P', '', $row['bar_code'])]['vd0'] = $row['ping_jun'];
		}
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	$sql = '';
	foreach ($orders[$pay['vid']] as $key => $value) {
		//将聚氯化铝项目Al₂O₃[vid:603]的检测结果赋值给盐基度的vd6，作为ω₁参与计算
		$sql = "UPDATE `assay_order` SET `vd6`='{$orders[603][$value['bar_code']]['vd0']}' WHERE `id` = '{$value['id']}'";
		$DB->query($sql);
	}
}
/**
 * 功能：地下水阴阳离子
 * 日期：2015-09-23
 * 参数：
 * 返回值：
 * 功能描述：
 */
function getHydData_651($pay) {
	getHydData_657($pay);
}
function getHydData_653($pay) {
	getHydData_657($pay);
}
function getHydData_655($pay) {
	getHydData_657($pay);
}
function getHydData_657($pay) {
	global $DB, $global;
	if ('lnsw' != $global['hyd']['danwei']) {
		return false;
	}
	//NH₄⁺含量=氨氮含量*1.286
	//NO₂⁻含量=亚硝酸盐氮*3.285
	//NO₃⁻含量=硝酸盐氮*4.43
	//P₂O₅含量=磷酸盐*4.43
	//651 氨根离子NH₄⁺(198氨氮)，653 亚硝酸根离子NO₂⁻(187亚硝酸盐氮)，655 硝酸根离子NO₃⁻(186硝酸盐氮)，657五氧化二磷P₂O₅(563磷酸盐)
	$dxs_lz_dy = array(
		651 => array('vid' => 198, 'k' => 1.286, 'v' => 'NH₄⁺', 'v2' => '氨氮'),
		653 => array('vid' => 187, 'k' => 3.285, 'v' => 'NO₂⁻', 'v2' => '亚硝酸盐氮'),
		655 => array('vid' => 186, 'k' => 4.43, 'v' => 'NO₃⁻', 'v2' => '硝酸盐氮'),
		657 => array('vid' => 563, 'k' => 0.739454, 'v' => 'P₂O₅', 'v2' => '磷酸盐'),
	);
	$vid = $pay['vid'];
	$DB->query("UPDATE `assay_pay` SET `td9`='{$dxs_lz_dy[$vid]['v']}',`td10`='{$dxs_lz_dy[$vid]['v2']}',`td11`='{$dxs_lz_dy[$vid]['k']}' WHERE `id`='{$pay['id']}'");
	$query = $DB->query("SELECT * FROM `assay_order` WHERE `cyd_id`='{$pay['cyd_id']}' AND `vid` IN('$vid','{$dxs_lz_dy[$vid]['vid']}') ORDER BY `id`");
	while ($row = $DB->fetch_assoc($query)) {
		$orders[$row['vid']][$row['bar_code']] = $row;
	}
	if (empty($orders[$dxs_lz_dy[$vid]['vid']])) {
		return false;
	}
	$sql = '';
	foreach ($orders[$vid] as $key => $value) {
		$vd01 = $orders[$dxs_lz_dy[$vid]['vid']][$value['bar_code']]['vd0'];
		$_vd01 = $orders[$dxs_lz_dy[$vid]['vid']][$value['bar_code']]['_vd0'];
		$_vd02 = $_vd01 * $dxs_lz_dy[$vid]['k'];
		$vd02 = round_value($_vd02, $pay['fid']);
		$sql = "UPDATE `assay_order` SET `vd3`='{$_vd01}',`vd4`='{$vd01}',`vd0`='{$vd02}',`_vd0`='{$_vd02}' WHERE `id`='{$value['id']}' AND `tid`='{$pay['id']}'";
		$DB->query($sql);
	}
}
