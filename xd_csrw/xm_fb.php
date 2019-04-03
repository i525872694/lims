<?php
include("../temp/config.php");
require_once("../inc/cy_func.php");
//登陆及权限判断
if($u['xd_cy_rw']!='1'){
        //跳转到登陆页
        echo "没有权限";
        exit;
}
$fzx_id 	= $u['fzx_id'];
$site_type      = get_str($_GET['site_type']);//temp/global.inc.php 中定义的站点类别
$fp_sites	= '';
if(!in_array($site_type,array_keys($global['site_type']))){
        $site_type      = '0';
}
$assay_values = '';
if($_GET['cyd_id']){
	$assay_values = get_all_assay_value( $_GET['cyd_id'] );
	$cyd = get_cyd( $_GET['cyd_id'] );
}else{
	echo "<script>alert('发生错误，请重试！');</script>";
	die();
}
$avstr = implode(',',$assay_values);
//显示站点
if(!empty($assay_values)){
	$where	= " and xm.id in ($avstr) ";
}else{
	$where	= 'and xm.id <0';//没有项目不查询，直接条件小于0 ，就什么都不查询了
}
$value_checked	= $value_checkbox = $value_options = '';
$checked_num	= $checkbox_num	  = $checked_value_num 	 = 0;
$buchongfu = array();
$fenlei_arr	= array();
$sql_xcjc_value	= $DB->query("SELECT xm.id,xm.value_C,xm.fenlei,xm.is_xcjc FROM `xmfa` AS fa INNER JOIN `assay_value` AS xm ON fa.xmid=xm.id WHERE fa.fzx_id='$fzx_id' AND fa.act='1' AND fa.mr='1' $where GROUP BY fa.xmid");
while($rs_xcjc_value = $DB->fetch_assoc($sql_xcjc_value)){
	//已经选中的项目
	if(empty($rs_xcjc_value['fenlei'])){
		$rs_xcjc_value['fenlei']	= '未分类';
	}
	if(!in_array($rs_xcjc_value['fenlei'],$fenlei_arr)){//根据项目分类显示项目
		$fenlei_arr[]	 = $rs_xcjc_value['fenlei'];
		$fenlei_num	 = count($fenlei_arr);
	}
	$value_options  .= "<option value='{$rs_xcjc_value['id']}'>{$rs_xcjc_value['value_C']}</option>";
	//根据条件默认选中项目
	if($cyd['xmfb']){
		$xmfb = json_decode($cyd['xmfb'],true);
		foreach($xmfb as $av=>$ff){
			$fbfzx= $ff;
			if($av == $rs_xcjc_value['id']){
				$buchongfu[] = $av;
				if($checkbox_num<$fenlei_num){
					$value_checkbox .= "<div class='checkbox_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
					$checkbox_num    = $fenlei_num;
				}
				$value_checkbox	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}' checked />{$rs_xcjc_value['value_C']}{$rs_xcjc_value['id']}</label>";
			}
		}
		if(!in_array($rs_xcjc_value['id'],$buchongfu)){
			if($checkbox_num<$fenlei_num){
				$value_checkbox .= "<div class='checkbox_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
				$checkbox_num    = $fenlei_num;
			}
			$value_checkbox	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}' />{$rs_xcjc_value['value_C']}{$rs_xcjc_value['id']}</label>";
		}
		
		
	}else{
		if($checkbox_num<$fenlei_num){
			$value_checkbox .= "<div class='checkbox_fenlei' classs='no' style='clear:both;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;margin-bottom:1px;'>{$rs_xcjc_value['fenlei']}</div>";
			$checkbox_num    = $fenlei_num;
		}
		$value_checkbox	.= "<label class='show' style='float:left;margin-bottom:1px;margin-left:1px;height:43px;width:130px;border:1px #D7D7D7 solid;'><input type='checkbox' name='vid[]' value='{$rs_xcjc_value['id']}' />{$rs_xcjc_value['value_C']}</label>";
	}
	
}
//显示分中心名称列表，必须放在这里，否则变量$fbfzx没有值
$zxsql = $DB->query("select * from hub_info where id <> '$fzx_id'");
$fzxop = '';
while($rezx = $DB->fetch_assoc($zxsql)){
	if($rezx){
		if($fbfzx == $rezx['id']){
			$fzx_name = $rezx['hub_name'];
			$fzxop .="<option value='{$rezx['id']}' selected>{$rezx['hub_name']}</option>"; 
		}else{
			$fzxop .="<option value='{$rezx['id']}'>{$rezx['hub_name']}</option>"; 
		}
	}
}
$avti='';
if(!empty($buchongfu)){
	foreach($buchongfu as $avv){
		$avti .= $_SESSION['assayvalueC'][$avv].',';
	}
	$ti  = "<div><font color='red' size='4pt'><B>本批任务中项目".$avti."已经被分配给了".$fzx_name."</B></font></div>";
	$ti1 = "本批任务中项目".$avti."已经被分配给了".$fzx_name;
}else{
	$ti  = '';
	$ti1 = '';
}
// 不能直接写在备注里
// if($ti1){
// 	$DB->query("update cy set csrw_tzd_note ='$ti1' where id='{$_GET['cyd_id']}'");//更新采样表
// }
#######显示界面
if($checked_value_num==0){//如果一个选中的项目都没有，就直接显示成全屏选项目的格式
	$lines	= "<div id='checkbox'>
			<p style='background-color:#FFCC99;top:90px;width:100%;'>
				请选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选/反选' />
			</p>
			$value_checkbox
			<div class='fixed' id='checkbox_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
		</div>";
}else if($value_checkbox==''){
	$lines  = "<div id='checked' style='width:100%;float:left;border:1px #56932C solid;'>
                        <p style='position:fixed;top:90px;width:100%;background-color:#90CA1F;'>
                                目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选/反选' />
                        </p>
                        <div class='fixed' id='checked_fixed' style='width:100%;background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
                        $value_checked
                </div>";
}else{//已选项目和未选项目分屏显示
	$lines	= "<div id='checked' style='width:50%;float:left;border:1px #56932C solid;'>
			<p style='position:fixed;top:90px;width:50%;background-color:#90CA1F;'>
				目前已经选择的项目：<span id='checked_num'>$checked_value_num</span> 个
				<input type='button' class='all_checked' value='全选/反选' />
			</p>
			<div class='fixed' id='checked_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checked
		</div>
		<div id='checkbox' style='width:50%;float:left;border:1px #FFCC99 solid;'>
			<p style='position:fixed;top:90px;width:50%;background-color:#FFCC99;'>
				还可以选择以下项目<span style='color:red;'>&nbsp;&nbsp;&nbsp;(已选择：<span id='num_tishi'>0</span> 项)</span>
				<input type='button' class='all_check' value='全选/反选' />
			</p>
			<div class='fixed' id='checkbox_fixed' style='background-color:#99CCFF;text-align:center;font-weight:bold;height:30px;line-height:30px;display:none;'></div>
			$value_checkbox
		</div>";
}
echo temp("xm_fb.html");
?>
