<?php
/**
 * 功能：采样记录表信息的填写、查看、打印
 * 作者：zhengsen
 * 时间：2014-04-15
*/
include '../temp/config.php';
require_once INC_DIR . "cy_func.php";
if($u[userid] == '') nologin();
$fzx_id = $u['fzx_id'];
$_SESSION['back_url'] = $current_url;
//导航
$trade_global['daohang'][]  = array('icon'=>'','html'=>'采样记录表 '."<span style='font-size:20px;color:red;font-weight:bold'>"."(采样单编号:".$_GET['cyd_bh'].")"."</span>",'href'=>'./cy/cy_record.php?cyd_id='.$_GET[cyd_id]);
$_SESSION['daohang']['cy_record']   = $trade_global['daohang'];
$trade_global['js']     = array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']    = array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');

###########取采样表（cy）数据
$cyd_id = get_int($_GET['cyd_id'] );
$cyd    = get_cyd( $cyd_id );
//处理json数据
$cy_json= [];
if($cyd['json']!=''){
	$cy_json   = json_decode($cyd['json'],true);
}
//处理异常日期数据
if($cyd['cy_user_qz_date'] == '0000-00-00'){
	$cyd['cy_user_qz_date'] = '';//采样人签字时间
}
if($cyd['sy_user_qz_date'] == '0000-00-00'){
	$cyd['sy_user_qz_date'] = '';//送样人签字时间
}
if($cyd['sh_user_qz_date'] == '0000-00-00'){
	$cyd['sh_user_qz_date'] = '';//样品接收人签字时间
}
$cy_users_list      = array_filter([$cyd['cy_user'],$cyd['cy_user2']]);         //被分配采样人
$cy_users_qz_list   = array_filter([$cyd['cy_user_qz'],$cyd['cy_user_qz2']]);   //已签字采样人集合
##############同步采样系统的最新数据
$load_cy	= !empty($_GET['load_cy'])?$_GET['load_cy']:'yes';//是否加载对接方采样表的信息
if(!empty($cyd['cy_user_qz'])){//签字后就不同步数据了,没传参数也不更新，稍后增加。
	$load_cy	= 'no';
}
############取出cy_rec表数据（站点及现场环境信息）
$cy_rec_list= $bar_arr=[];
$rec_sql    = "SELECT * FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}' AND `river_name`!='标准样品'";
$rec_query  = $DB->query($rec_sql);
while ($rec_row = $DB->fetch_assoc($rec_query)) {
	$bar_arr[$rec_row['bar_code']]  = $rec_row['json'];
	if(!empty($rec_row['json'])){
		$rec_row['json']    = json_decode($rec_row['json'],true);
	}else{
		$rec_row['json']    = [];
	}
	$cy_rec_list[$rec_row['id']]    = $rec_row;
}
$bar_code_str   = "'".implode("','", array_keys($bar_arr))."'";//样品编号字符串
###########取出现场检测项目信息
$sql_xc_jcxm    = "SELECT ao.id AS ao_id,ao.cid,ao.vid,ao.vd0,ao.tid,ap.assay_element,ap.userid,ap.td5,ap.yq_bh
					FROM `assay_pay` as ap 
					RIGHT JOIN `assay_order` as ao on ap.id=ao.tid 
					WHERE ap.is_xcjc='1' and ap.cyd_id='{$cyd_id}'";
$query_xc_jcxm  = $DB->query($sql_xc_jcxm);
$xc_jcxm_arr    = array();
while($rs_xc_jcxm = $DB->fetch_assoc($query_xc_jcxm)){
	$xc_jcxm_arr[$rs_xc_jcxm['vid']]    = $rs_xc_jcxm['assay_element'];
	$cy_rec_list[$rs_xc_jcxm['cid']]['xc_result'][$rs_xc_jcxm['vid']]   = $rs_xc_jcxm;
}
##########取出该批次应显示的 表头字段（根据水样类型到$global中去设置）
$water_type_arr = explode(',',$cyd['water_type']);//这批样的水样类型
foreach($water_type_arr as $key=>$value){
	$water_type_max_arr[]=get_water_type_max($value,$fzx_id);
}
$water_types    = array_unique($water_type_max_arr);
$cy_record_bt_arr = array();
foreach($water_types as $key=>$value){
	if(empty($global['cy_record_bt'][$value])){
		$value='moren';
	}
	$cy_record_bt_arr=array_merge($cy_record_bt_arr,$global['cy_record_bt'][$value]);
}
$xc_xm_td       = !empty($global['xcjc_ymxs'])?$global['xcjc_ymxs']:['检测结果'];//现场检测项目需显示的信息（一般为结果、仪器、人员）
$xc_xm_td_num   = count($xc_xm_td);
$bt_end_list    = ["shuiti_zhuangtai","shuiti_yanse"];//现场检测项目后面要显示的表头数据。一般只显示水体颜色和水体状态
#########取出每个现场元素的下拉选项
$xc_options = [];
$xc_options_sql     = "SELECT * FROM `n_set` WHERE `module_name`='jilubiao_value'";
$xc_options_query   = $DB->query($xc_options_sql);
while ($xc_options_row = $DB->fetch_assoc($xc_options_query)) {
	$tmp_option_arr = explode(',', $xc_options_row['module_value1']);
	foreach ($tmp_option_arr as $value) {
		$xc_options[$xc_options_row['module_value2']]   .= "<option value='{$value}'>{$value}</option>";
	}
}
//检测人员下拉选i
$xc_user_list	= [];
$xc_user_list   = ["{$cyd['cy_user']}","{$cyd['cy_user2']}"];
$xc_user_list   = array_filter($xc_user_list);
foreach ($xc_user_list as $value_user) {
	  $xc_options['jcry'] = "<option value='{$value_user}'>{$value_user}</option>";
}
//现场检测项目相关信息
if(!empty($xc_jcxm_arr)){
	$xc_yiqi_list   = [];//$xc_user_list = [];
	//检测人员下拉选项
	/*$xc_user_list   = ["{$cyd['cy_user']}","{$cyd['cy_user2']}"];
	$xc_user_list   = array_filter($xc_user_list);
	foreach ($xc_user_list as $value_user) {
		$xc_options['jcry'] = "<option value='{$value_user}'>{$value_user}</option>";
	}*/
	//检测仪器下拉选项
	$vid_str    = implode(',',array_keys($xc_jcxm_arr));
	$yiqi_sql   = $DB->query("SELECT xf.xmid,yq.yq_mingcheng,yq.yq_xinghao,yq.yq_sbbianhao from xmfa as xf INNER JOIN yiqi as yq on xf.yiqi=yq.id where xf.fzx_id={$fzx_id} AND xmid in($vid_str)");
	while ($yiqi_row = $DB->fetch_assoc($yiqi_sql)) {
		$xc_yiqi_list[$yiqi_row['xmid']][]  = $yiqi_row['yq_xinghao'];
		$xc_options['jcyq'][$yiqi_row['xmid']]  = "<option value='{$yiqi_row['yq_xinghao']}'>{$yiqi_row['yq_xinghao']}</option>";
	}
}
############组装表头部分html代码
$cy_record_bt_str   = '';//表头文字代码
$bt_rowspan_num     = ($xc_xm_td_num<=1)?'2':'3';//正常字段需要合并的行数
$xcjc_td_num        = count($xc_jcxm_arr)*$xc_xm_td_num;
$xcjc_td_num        = ($xcjc_td_num)?$xcjc_td_num:'1';
$cols_num           = count($cy_record_bt_arr)+$xcjc_td_num+7;
$huanjing_arr = array('tian_qi'=>'WTH','feng_xiang'=>'WNDDIR','feng_su'=>'WNDV','liu_l'=>'Q','qi_ya'=>'ATM','qi_wen'=>'AIRT','water_height'=>'Z');//现场环境统计的参数
foreach ((array)$cy_record_bt_arr as $key_name => $value_field) {
	$cy_record_bt_str   .= "<td rowspan='{$bt_rowspan_num}' ><div style='width:50px'>{$key_name}</div></td>";
}
//组装在管理中的环境参数
// foreach($xc_huanjing as $k=>$v){
// 	$cy_record_bt_str   .= "<td rowspan='{$bt_rowspan_num}' ><div style='width:50px'>{$v}</div></td>";
// }
//现场检测项目部分html
$xc_jcxm_total  = '1';
$xc_jcxm= $xcxm_xs= '';
if(!empty($xc_jcxm_arr)){
	$xc_jcxm_total = count($xc_jcxm_arr)*$xc_xm_td_num;//现场检测项目信息总共所需列数
	foreach ($xc_jcxm_arr as $key_vid => $value_xm_name) {
		$xc_jcxm    .= "<td style='max-width:80px' colspan='{$xc_xm_td_num}'>{$value_xm_name}</td>";
		//只有一列的时候，不需要显示标题。肯定是检测结果
		if($xc_xm_td_num >1){
			foreach($xc_xm_td as $value_name){
				$xcxm_xs.= "<td>{$value_name}</td>";
			}
		}
	}
}else{
	$xc_jcxm='<td style="max-width:80px" rowspan="'.($bt_rowspan_num-1).'">无</td>';
}
#############组装每个采样点数据的html(每行的html)
//print_rr($cy_rec_list);
$cy_record_lines    = '';
$xuhao  = 0;
foreach ($cy_rec_list as $key_cid => $rec_list) {
	$xuhao++;
	$cy_record_line = '';
	//现场环境信息
	foreach ((array)$cy_record_bt_arr as $key_name => $value_field) {
		//如果该字段在现场环境统计中并且环境参数没有勾选，跳过
		if(!empty($huanjing_arr[$value_field])){
			if(!in_array($huanjing_arr[$value_field],explode(',',$rec_list['milieu_values']))){
				$cy_record_line.='<td>-</td>';
				continue;
			}
		}
		$inputid    = $value_field.$key_cid;
		if(in_array($value_field,array('cy_way','cy_tool','cy_wrxx','tian_qi'))){//需要可输入下拉菜单的元素
			$cy_record_line .= "<td>
								<div style=\"position:relative;width:70px;\">       
									<span style=\"margin-left:100px;width:18px;overflow:hidden;position: absolute;left:-45px;top:-15px\">     
										<select style=\"width:118px;margin-left:-100px;display:black\" inputid='{$inputid}' class='input_select' name='d[$key_cid][$value_field]' >
											<option value=''>请选择</option>
											{$xc_options[$value_field]}
										</select>
									</span>
									<input name='d[$key_cid][$value_field]' id='$inputid' style=\"width:57px;;height:29px;position:absolute;left:1px;top:-14px\" value='{$rec_list[$value_field]}' placeholder='可输入' onclick=\"show_type(this);\"/>    
								</div>
							</td>";
		}else{
			$cy_record_line .= "<td><input type='text' class='input_select' size='1' name='d[$key_cid][$value_field]' value='{$rec_list[$value_field]}' /></td>";
		}
	}
	//现场检测项目数据
	if(!empty($xc_jcxm_arr)){
		foreach ($xc_jcxm_arr as $key_vid => $value_xm_name) {
			foreach($xc_xm_td as $key=>$value_name){
				$ao_id      = $rec_list['xc_result'][$key_vid]['ao_id'];//order表的id
				if(empty($ao_id)){//站点不检测该现场检测项目
					$cy_record_line .= "<td>-</td>";
					continue;
				}
				$input_select   = 'no';
				switch ($value_name) {
					case '仪器型号':
						$input_select   = 'yes';//启用可搜索下拉菜单
						$field      = 'jcyq';
						$input_option   = $xc_options[$field][$key_vid];
						$result_vd0 = $rec_list['xc_result'][$key_vid]['td5'];
						if($result_vd0=='' && count($xc_yiqi_list[$key_vid])==1){
							$result_vd0 = $xc_yiqi_list[$key_vid][0];
						}
						break;
					case '检测结果':
						$field      = 'jcjg';
						$result_vd0 = $rec_list['xc_result'][$key_vid]['vd0'];
						break;
					case '检测人员':
						$input_select   = 'yes';//启用可搜索下拉菜单
						$field          = 'jcry';
						$input_option   = $xc_options[$field];
						$result_vd0     = $rec_list['xc_result'][$key_vid]['userid'];
						if($result_vd0=='' && count($xc_yiqi_list[$key_vid])==1){
							$result_vd0 = $xc_user_list[0];
						}
						break;
					default:
						$field      = $key;
						$result_vd0 = '未定义字段';
						break;
				}

				$inputid    = $key_vid.$field.$key_cid;
				if($input_select == 'yes'){
					$cy_record_line .= "<td>
										<div style=\"position:relative;width:70px;\">       
											<span style=\"margin-left:100px;width:18px;overflow:hidden;position: absolute;left:-45px;top:-15px\">     
												<select style=\"width:118px;margin-left:-100px;display:black\" field='{$field}{$key_vid}' inputid='{$inputid}' class='input_select'  name='xcjc[$ao_id][$field]' >
													<option value='' >请选择</option>
													{$input_option}
												</select>
											</span>
											<input name='xcjc[$ao_id][$field]' id='$inputid'  field='{$field}{$key_vid}' style=\"width:57px;;height:29px;position:absolute;left:1px;top:-14px\" value='{$result_vd0}' placeholder=\"可输入\" onclick=\"show_type(this);\"/>    
										</div>
									</td>";
				}else{
					$cy_record_line .= "<td><input type='text' name='xcjc[$ao_id][$field]' style='min-width:50px;' value='{$result_vd0}' /></td>";
				}
			}
		}
	}else{
		$cy_record_line .= "<td>-</td>";
	}
	//无水站点的处理
	if($rec_list['status']=='-1'){
		$act = "<select name=\"d[{$rec_list[id]}][status]\" id=\"ctt{$xuhao}\" style=\"display:none;width:100%;\" class=\"wushui\" onFocus=\"show_wus({$xuhao})\" onchange=\"hide_wus({$xuhao})\" >
				<option value=\"1\">有水样</option>
				<option value=\"-1\"selected = \"selected\">无水样</option>
			</select>";
	}else{
		$act = "<select name=\"d[{$rec_list[id]}][status]\" id=\"ctt{$xuhao}\" style=\"display:none;width:100%;\" onFocus=\"show_wus({$xuhao})\"  onchange=\"hide_wus($xuhao)\"  >
			<option value=\"1\" selected = \"selected\">有水样</option>
			<option value=\"-1\">无水样</option>
		</select>";
	}
	//采样日期的默认
	if(empty($rec_list['cy_date'])||$rec_list['cy_date']=='0000-00-00'){
		$rec_list['cy_date']=date('Y-m-d');
	}
	//采样时间
	$rec_list['cy_time'] = substr( $rec_list['cy_time'], 0, 5 );
	if($rec_list['cy_time']=='00:00'){
		$rec_list['cy_time']='';
	}
	//现场检测项目后后面的表头信息
	foreach ( $bt_end_list as $value_field) {
		$inputid    = $value_field.$key_cid;
		$$value_field   = "<div style=\"position:relative;width:70px;\">       
									<span style=\"margin-left:100px;width:18px;overflow:hidden;position: absolute;left:-45px;top:-15px\">     
										<select style=\"width:118px;margin-left:-100px;display:black\" inputid='{$inputid}' class='input_select' name='json[$key_cid][$value_field]' >
											<option value=''>请选择</option>
											{$xc_options[$value_field]}
										</select>
									</span>
									<input name='json[$key_cid][$value_field]' id='$inputid' style=\"width:57px;;height:29px;position:absolute;left:1px;top:-14px\" value='{$rec_list['json']['shuiti'][$value_field]}' placeholder='可输入' onclick=\"show_type(this);\"/>    
								</div>";
	}
	$img_button = '';
	if(!empty($rec_list['xc_img'])){
		$img_button = "<button  type='button' class='btn btn-xs btn-primary' onclick='look_pic($rec_list[id])'>查看图片</button>";
	}
	$cy_record_lines    .= temp('cy/cy_record_line.html');//行模板
}
##########采样单被退回的提示信息
$tuiHuiTiShi    = '';
if(!empty($cy_json['退回']) && $cyd['sh_user_qz']==''){
		$jsonTuiHui     = end($cy_json['退回']);//取出最新一次退回的结果
		if(!empty($jsonTuiHui['xiuGaiLiYou'])){
				$xiuGaiLiYou    = "<tr><td>修改理由：</td><td>{$jsonTuiHui['xiuGaiLiYou']}</td></tr>";
		}else{
				$xiuGaiLiYou    = '';
		}
		$tuiHuiTiShi    = "<style>.tuiHui td{border:#000 1px solid;}</style>
					<table class='tuiHui' cellpadding=\"10\"  style=\"margin:auto;width:100%;color:red;border-collapse: collapse;text-align:left;\">
							<caption style='color:red;'>此采样单被退回</caption>
							<tr>
									<td width=70 nowrap>退 回 人：</td>
									<td>{$jsonTuiHui['tuiHuiUser']}</td>
							</tr>
							<tr>
									<td nowrap>退回时间：</td>
									<td>{$jsonTuiHui['tuiHuiTime']}</td>
							</tr>
							<tr>
									<td nowrap>退回原因：</td>
									<td>{$jsonTuiHui['tuiHuiReason']}</td>
							</tr>
							$xiuGaiLiYou
					</table>";
}
###########修改记录（记录下采样人的签字后的整个采样单界面）
if(in_array($u['userid'],$cy_users_qz_list)){
		$html   = temp("cy/cy_record.html");
		if(is_array($cy_json['退回'])){
			$jsonTuiHui     = end($cy_json['退回']);//取出最新一次退回的结果
		}
		if(!empty($jsonTuiHui['xiuGaiLiYou'])){
				$xiuGaiLiYou    = addslashes_deep($jsonTuiHui['xiuGaiLiYou']);
		}else{
				$xiuGaiLiYou    = '';
		}
		cy_shuyuan($cyd['id'],$u['userid'],$html,$xiuGaiLiYou);
}
############采样人签字部分的html组装。为了避免签字部分的改变 被记录到 “修改记录”中，这里将签字部分改成变量的方式
$cyd_qz = get_userid_img("cy",array("cy_user_qz","cy_user_qz2","sy_user_qz","sh_user_qz"),$_GET['cyd_id']);//将签名转换成电子签名
//判断登录人身份以及是否有签字权限
$cy_qz_qx   = $sh_qz_qx = 'no';
if($u['admin']){
	if(empty($cy_users_qz_list)){
		$cy_qz_qx   = 'yes';
	}else if(empty($cyd['sh_user_qz'])){
		$sh_qz_qx   = 'yes';
	}
}else{
	if(in_array($u['userid'],$cy_users_list) && !in_array($u['userid'], $cy_users_qz_list)){
		$cy_qz_qx   = 'yes';
	}
	if(!in_array($u['userid'],$cy_users_list) && !empty($cy_users_qz_list) && $u['ypjs']){
		$sh_qz_qx   = 'yes';
	}
}
//显示签字人或签字按钮、 及保存按钮
$cy_user_text       = [];
foreach ($cyd_qz as $key_field => $value_qz) {
	switch ($key_field) {
		case 'cy_user_qz':case 'cy_user_qz2'://采样人签字
			$tmp_arr    = [$cy_user_text['cy_user_qz']];
			if(!empty($value_qz)){
				$tmp_arr[]          = $value_qz;
			}else if($cy_qz_qx == 'yes'){
				$cy_qz_qx   = 'no';//签字按钮只显示一个即可
				if(!empty($cy_json['退回'])){
					$tmp_arr[]  = "<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz' onclick=\"return $(this).qbox({title:'修改理由(签字后修改理由将不能修改)',src:'{$rooturl}/huayan/hyd_huitui.php?action=cyd_modify&id={$cyd['id']}&button_name='+this.name,w:600,h:230});\">";
				}else{
					$tmp_arr[]  = "<input class=\"btn btn-xs btn-primary\" type='submit' value='签字' id='cy_user_qz' name='cy_user_qz'>";
				}
				//保存按钮
				$cy_user_text['save_button']    = '<input type="submit" value="保存" class="Noprint btn btn-xs btn-primary no_print" onfocus="yanzheng()">';
			}
			$cy_user_text['cy_user_qz'] = implode('、',array_filter($tmp_arr));
			break;
		case 'sy_user_qz'://送样人签字
			if(!empty($cyd['sh_user_qz'])){
				$cy_user_text['sy_user_qz'] = $value_qz;//审核人签字后，只显示名字，不显示下拉菜单
			}else{
				array_unshift($xc_user_list, $value_qz);
				$xc_user_list   = array_filter(array_unique($xc_user_list));
				$sy_user_option = "";
				foreach ($xc_user_list as $value_cy_user) {
					$sy_user_option .= "<option value='{$value_cy_user}'>{$value_cy_user}</option>";
				}
				$cy_user_text['sy_user_qz'] ="<select name=\"sy_user_qz\">{$sy_user_option}</select>";
			}
			break;
		case 'sh_user_qz'://审核人签字
			if(!empty($value_qz)){
				$cy_user_text['sh_user_qz'] = $value_qz;
			}else if($sh_qz_qx == 'yes'){
				$cy_user_text['sh_user_qz'] = "<input class=\"btn btn-xs btn-primary\" type='submit' value='签字'  name='ypjs_user_qz'>";
			}
			break;
		default:
			# code...
			break;
	}
}
######查看修改记录按钮
$hy_shuyuan     = $DB->query("select id from hy_shuyuan where cyd_id = '{$cyd['id']}'");
$shuyuan_rows   = $DB->num_rows($hy_shuyuan);
$show_xiuGaiJiLu= '';
if($shuyuan_rows>0){
		$show_xiuGaiJiLu = "<!--<p class=\"center no_print\">--><a class='btn btn-xs btn-primary no_print' href='$rooturl/cy/shuyuan_cy.php?cyd_id={$cyd['id']}' >查看修改记录</a><!--</p>-->";
}
$cy_user_text['sc_user_qz'] = $cyd['sc_qz'];
$cy_user_text['sc_user_qz_date'] = $cyd['sc_qz_date'];
//有权限的时候出现签字按钮
if($u['ziliao_zhengbian']=='1'&&empty($cyd['sc_qz'])){
	$cy_user_text['sc_user_qz'] = "<button class='btn btn-xs btn-primary sc_qz' type='button' onclick='return sc_qz(`$cyd_id`)'>签字</button>";
}
//签字信息的html
$line_qz    = "
	<input type=\"hidden\" name=\"current_user\" value=\"{$u['userid']}\" />
	<table align=\"center\" style=\"width:80%\"> 

	<tr> 
	<td align=\"center\" width=\"30%\">采样人：{$cy_user_text['cy_user_qz']}</td><td align=\"center\" width=\"30%\">送样人：{$cy_user_text['sy_user_qz']}</td><td align=\"center\">样品审核人：{$cy_user_text['sh_user_qz']}</td> <td align=\"center\">审查：{$cy_user_text['sc_user_qz']}</td> 
	</tr> 
	<tr> 
	<td align=\"center\">日期：{$cyd['cy_user_qz_date']}</td><td align=\"center\">日期：{$cyd['sy_user_qz_date']}</td><td align=\"center\">日期：{$cyd['sh_user_qz_date']}</td><td align=\"center\" class='sc_qz_date'>日期：{$cy_user_text['sc_user_qz_date']}</td>  
	</tr> 
	</table> 
	<p class=\"center\">{$cy_user_text['save_button']} {$show_xiuGaiJiLu}</p>";

########退回按钮
$huiTuiButton   = '';
//采样员签字后，admin和审核人可以看到退回按钮
if((!in_array($u['userid'], $cy_users_list) && $u['ypjs']) || $u['admin'] ){
		$huiTuiButton   = "<a href=\"#\" title='回退采样单到>采样员未签字状态' onclick=\"return $(this).qbox({title:'采样单退回(将采样单退回到采样员“未签字”状态)',src:'$rooturl/huayan/hyd_huitui.php?action=cyd&id={$cyd['id']}',w:600,h:230});\"style=\"position:fixed;right:80px;bottom:15px;\" class=\"button blue\"> 退回&nbsp;采样单 </a>";
}
/*//如果是打印页面显示设置
if($_GET['print']){
	if(!empty($_GET['page_size'])){
		$page_size=$_GET['page_size'];
	}else{
		$page_size='9';//默认打印8行
	}
	$input_note="此处设置打印行数，默认9行";
	echo temp("cy/cy_tzd_print_head");
}*/
//点击打印时的显示
if($_GET['print']){
	$print  = "<link href=\"$rooturl/css/lims/print.css\" rel=\"stylesheet\"/>";
}else{
   // $dayin  = "<br /><a class='btn btn-xs btn-primary' href=\"?cyd_id=$_GET[cyd_id]&print=1&ajax=1\" target='_blank'><i class='icon-print bigger-130'></i>打印</a>";
	$syd    = "<a class='btn btn-xs btn-primary' href=\"xccy_jlb.php?cyd_id=$_GET[cyd_id]\" target='_blank'><i class='icon-print bigger-130'></i>打印送验单</a>";
	$tongbu    = "<a class='btn btn-xs btn-primary' href='#' onclick=\"get_duijie_result({$cyd_id},true);\" ><i class='icon-download bigger-130'></i>同步并覆盖最新采样数据</a>";
}
if(empty($_GET['print'])){
	disp("cy/cy_record.html");
}
?>
