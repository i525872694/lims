<?php
include "../../temp/config.php";
//导航
//$trade_global['daohang']	= array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),array('icon'=>'','html'=>'检测能力统计表','href'=>$current_url));
$trade_global['daohang'][]      = array('icon'=>'','html'=>'检测能力统计详细表','href'=>$current_url);
$_SESSION['daohang']['jcnl_tj_list']    = $trade_global['daohang'];
$trade_global['css']		= array('lims/main.css');
$fzx_id=intval($_GET['fzx_id']) ? intval($_GET['fzx_id']) : '1';
$hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE 1 ");
$sql	= "SELECT `type`,`lname`,`lxid`,`value_C`,`xmid`,`method_number`,concat(`method_number`,`method_name`) AS `full_name` FROM `xmfa` f LEFT JOIN `assay_value` AS v ON f.`xmid`=v.`id` LEFT JOIN `assay_method` m ON f.`fangfa`=m.`id` LEFT JOIN `leixing` l ON f.`lxid`=l.`id`  WHERE f.`fzx_id`='$fzx_id' ORDER BY `lxid`,`xmid`";
$query	= $DB->query($sql);
$shuzi_arr = array('（一）','（二）','（三）','（四）','（五）','（六）','（七）','（八）','（九）');
$fangfa = array();
while ($row = $DB->fetch_assoc($query)) {
	if($row['type']==''){
		$row['type']	= '水';
	}
	$fangfa[$row['type']][$row['lname']]['count']++;
	$fangfa[$row['type']][$row['lname']]['data'][$row['xmid']][] = $row;
}
$pjbz_arr = array(
	'地表水'=>'地表水环境质量标准 GB3838-2002 农田灌溉水质标准 GB5084-2005 渔业用水标准 GB11607-1989 地表水资源质量标准 SL63-1994 生活饮用水水源水质标准 CJ3020-1993',
	'地下水'=>'地下水 质量标准 GB/T14848-1993',
	'生活饮用水'=>'城市供水水质标准 CJ/T206-2005 生活饮用水卫生标准 GB5749-2006',
	'污废水及再生水'=>'污水综合排放标准 GB8978-1996 污水排入城镇下水道水质标准 CJ343-2010 城市污水再生利用 城市杂用水水质 GB/T18920-2002 <br/>城市污水再生利用 景观环境用水水质 GB/T18921-2002 <br/>城市污水再生利用 补充水源水质 GB/T18923-2002 <br/>城市污水再生利用 工业用水水质 GB/T19923-2002 <br/>城市污水再生利用 地下水回灌水质 GB/T19772-2005 <br/>城市污水再生利用 农田灌溉用水水质 GB/T20922-2007 <br/>再生水水质标准 SL368-2006',
	'土壤与底质'=>'土壤环境质量标准 GB15618-1995'
);
$lines = '';
$last_vid = 0;
$fangfa2 = array(
	'一、水' => $fangfa['水'],
	'二、土壤' => $fangfa['土壤']
);
foreach ($fangfa2 as $type_name => $type) {
	$j=0;
	$lines .= '<tr><td colspan="6" align="left">'.$type_name.'</td></tr>';
	foreach ($type as $lname => $leixing) {
		$i = 0;
		$leixingCount = $leixing['count']+1;
		$lines .= '<tr><td rowspan="'.$leixingCount.'">'.$shuzi_arr[$j++].'</td><td rowspan="'.$leixingCount.'">'.$lname.'</td><td colspan="4" align="left">'.$pjbz_arr[$lname].'</td></tr>';
		foreach ($leixing['data'] as $vid => $rows) {
			$i++;
			foreach ($rows as $key => $value) {
				$vid_head=$vid_foot='';
				if($last_vid != $vid){
					$last_vid = $vid;
					$vidCount = count($rows);
					$vid_head = '<td rowspan="'.$vidCount.'">'.$i.'</td><td rowspan="'.$vidCount.'">'.$value['value_C'].'</td>';
					$vid_foot = '<td rowspan="'.$vidCount.'">&nbsp;</td>';
				}
				$lines .= '<tr>'.$vid_head.'<td>'.$value['full_name'].'&nbsp;'.$value['method_number'].'</td>'.$vid_foot.'</tr>';
			}
		}
	}
}
disp('jcnl_tj/jcnl_tj');
