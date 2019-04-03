<?php
/**
 * 功能：标准溶液，标准样品一览表
 * 作者: xiewenhao
 * 描述: 标准溶液，标准样品一览表
*/
include ('../temp/config.php');
$fzx_id = FZX_ID;
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$current_url.$tabs));
//标物一览
//项目名称
$sql_vid = $DB->query("SELECT id,value_C FROM `assay_value`");
while($re1 = $DB->fetch_assoc($sql_vid)){
	$vid_arr[$re1['id']] = $re1['value_C'];
}
//表头实验室信息
$hub_sql = $DB->query("SELECT * FROM `hub_info` where 1");
while($re2 = $DB->fetch_assoc($hub_sql)){
	$hub_names[$re2['id']] = $re2['hub_name'];
	$all_address[$re2['id']] = $re2['Address'];
}
if($_GET['type']){
	$type = $_GET['type'];
}else{
	$type = '标准样品';
}
$sel = '';
if($_GET['fzx']){
	$fzx = $_GET['fzx'];
}else{
	$fzx = '1';
}
if($fzx_id==1){
	if($_GET['pri']!=1&&$_GET['pri']!=2){
		$sel .= "<br><center><select name='fzx' id='dayin' onchange='cfzx(this)' flag='$type'><option value=''>请选择...</option>";
		foreach($hub_names as $k3=>$v3){
			if($k3 == $fzx){
				$sel .="<option value='$k3' selected>$v3</option>";
			}else{
				$sel .="<option value='$k3'>$v3</option>";
			}
		}
		$sel .="</select>";
		$sel .= "&nbsp;&nbsp;<button type='button' class='btn btn-primary btn-sm' onclick='ptn(1)'>打印</button>&nbsp;&nbsp;<button type='button' class='btn btn-primary btn-sm' onclick='ptn(2)'>下载</button></center>";
	}
	$hub_name = $hub_names[$fzx];
	$address = $all_address[$fzx];
	$fzx_id = $fzx;
}else{
	if($_GET['pri']!=1&&$_GET['pri']!=2){
		$sel .= "<center><button type='button' class='btn btn-primary btn-sm' onclick='ptn(1)'>打印</button>&nbsp;&nbsp;<button type='button' class='btn btn-primary btn-sm' onclick='ptn(2)'  flag='$type' id='dayin'>下载</button></center>";
	}
	$hub_name = $hub_names[$fzx_id];
	$address = $all_address[$fzx_id];
}
$bwsql = "select b.wz_name,b.guobiao,b.time_limit,b.wz_type,d.* from bzwz_detail as d left join bzwz as b on b.id = d.wz_id where fzx_id = '$fzx_id' and b.time_limit>now() order by fzx_id, d.vid,wz_name";
if($type == '标准样品'){
	$type = '标准物质';
}
$bwrec = $DB->query($bwsql);
$lines = $fenye = "";
$xuhao = $xuhao1 = '1';
$biaowei = "</table>";
$fenge = '16';//从1开始的，所以第一页比其他页少一行
$fenge1 = $fenge-1;//第二页起因为取余的原因，会比第一页多一行
while($row = $DB->fetch_assoc($bwrec)){
	$fenye = "";
	if($xuhao%$fenge1==0){
		$xuhao1 = '1';//每次需要分页就重置，用来判断最后一页需要多少空白行
		$fenye = $biaowei."<div style='page-break-after: always;'></div>".temp("bzwz/bwyl_header.html");
	}
	$lines .=$fenye;
	if($row['vid']){
		$vidname = $vid_arr[$row['vid']];
		// if(!$row['c_bound']){
		// 	if($row['consistence']&&$row['eligible_bound']){
		// 		$danwei = preg_replace("/\d/","",$row['consistence']);
		// 		$danwei = preg_replace("/\./","",$danwei);
		// 		if(stristr($row['eligible_bound'],'%')){
		// 			$xiao = ((double)$row['consistence']-(double)$row['consistence']*(double)$row['eligible_bound']*0.01);
		// 			$da = ((double)$row['consistence']+(double)$row['consistence']*(double)$row['eligible_bound']*0.01);
		// 			$fanwei = $xiao.'~'.$da.$danwei;
		// 		}else{
		// 			$xiao = ((double)$row['consistence']-(double)$row['eligible_bound']);
		// 			$da = ((double)$row['consistence']+(double)$row['eligible_bound']);
		// 			$fanwei = $xiao.'~'.$da.$danwei;
		// 		}
		// 	}else{
		// 		$fanwei = '';
		// 	}
		// }else{
		// 	$fanwei = $row['c_bound'];
		// }
		if($row['consistence']&&$row['eligible_bound']){
			$danwei = preg_replace("/\d/","",$row['consistence']);
			$danwei = preg_replace("/\./","",$danwei);
			if(stristr($row['eligible_bound'],'%')){
				$xiao = ((double)$row['consistence']-(double)$row['consistence']*(double)$row['eligible_bound']*0.01);
				$da = ((double)$row['consistence']+(double)$row['consistence']*(double)$row['eligible_bound']*0.01);
				$fanwei = $xiao.'~'.$da.$danwei;
			}else{
				$xiao = ((double)$row['consistence']-(double)$row['eligible_bound']);
				$da = ((double)$row['consistence']+(double)$row['eligible_bound']);
				$fanwei = $xiao.'~'.$da.$danwei;
			}
		}else{
			$fanwei = '';
		}
	}
	$lines .= "<tr align='center'>";
	$lines .= "<td>$xuhao</td><td>$vidname</td><td>{$row['guobiao']}</td><td>{$row['wz_name']}</td><td>$fanwei</td><td>{$row['eligible_bound']}</td><td>&nbsp;</td><td>{$row['time_limit']}</td><td>{$row['bw_note']}</td>";
	$lines .= "</tr>";
	$xuhao1++;
	$xuhao++;
}
//空白填充
if($xuhao%$fenge!=0){
	if($xuhao<$fenge){
		$fenge = $fenge1;
	}
	$kong = $fenge-$xuhao1;
	for($j=1;$j<=$kong;$j++){
		$lines .= "<tr>";
		$lines .= "<td></td><td></td><td></td><td></td><td></td><td></td><td>&nbsp;</td><td></td><td></td>";
		$lines .= "</tr>";
	}
}
if($_GET['pri']==2){
	header("Content-Type:application/msexcel");        
	header("Content-Disposition:attachment;filename={$hub_names[$fzx_id]}.xls");        
	header("Pragma:no-cache");        
	header("Expires:0");  
}
disp('bzwz/bwyl.html');