<?php
//功能 试剂器皿管理  列表筛选页面
include "../temp/config.php";
//导航
$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'库房管理','href'=>"$rooturl/sjqm/sjqm_list.php")
);
//$trade_global['js'] = array('bootbox.min.js');
if($_GET['handle']=='download'){
	header("Content-Type:   application/msexcel"); 
	header("Content-Disposition: attachment; filename={$_GET['type']}.xls");
	header("Pragma:   no-cache");        
    header("Expires:   0"); 
}
$trade_global['daohang']= $daohang;
if(empty($_GET['fzx_id'])){
	$fzx_id=$u['fzx_id'];
}else{
	$fzx_id = $_GET['fzx_id'];
}
if($u['is_zz'] == '1'){
	$fzx_select = '　查看分中心：<select  id="fzx_id" name="fzx_id" style="max-width:290px" class="chosen-select">';
	$sql = "SELECT * FROM `hub_info`";
	$re = $DB->query($sql);
	while($data = $DB->fetch_assoc($re)){
		$option_select='';
		if($_GET['fzx_id'] == $data['id']){
			$option_select='selected';
		}
		$fzx_select .= "<option value='{$data['id']}' $option_select>{$data['hub_name']}</option>";
	}
	$fzx_select .= "</select>";
}
//库存
if(!empty($_GET['kucun']) && $_GET['kucun'] != '全部'){
	$kucun_sql = " AND `kucun` = '{$_GET['kucun']}'";
}else{
	$kucun_sql = '';
}
//存放位置
if(!empty($_GET['nice_name']) && $_GET['nice_name'] != '全部'){
	$nice_name_sql = " AND `nice_name` = '{$_GET['nice_name']}'";
}else{
	$nice_name_sql = '';
}
//类型
$fl2 = array('试剂','药品','器皿','杂物');
//类型对应的条件
$fl = array('试剂'=>0,'药品'=>3,'器皿'=>1,'杂物'=>2,'全部'=>'0,1,2,3');
//分类
$fenlei = $_GET['type'] = ($_GET['type'])?$_GET['type']:'全部';
//列表页按钮“新增XX”全部的时候只显示新增
$types = ($_GET['type'] == '全部') ? '' : $_GET['type'];
//条件
if($fenlei=='全部'){
	$type_sql = "";
}else{
	$type_sql = " AND type = '$fenlei'";
}
if($_GET['jibie']=='全部' || empty($_GET['jibie'])){
	$jibie_sql = '';
}else{
	if($_GET['jibie']=='无级别'){
		$_GET['jibie'] = '';
		$val = '无级别';
	}
	$jibie_sql = " AND `jibie` = '$_GET[jibie]'";
}
//库房物品的下拉菜单
$flstrn	= '';
$sqlname = "SELECT distinct `name` , `jibie` FROM `sjqm` WHERE `fzx_id`='$fzx_id'  $type_sql order by `name` desc,`jibie`,`guige` ";
$rsn = $DB->query($sqlname);
while($rn = $DB->fetch_assoc($rsn)){
		//得到级别下拉菜单
		$jibie_arr[] =$rn['jibie']; 
        $x= $rn['name'];
        if($x == $_GET['name']){
                $flstrn .= "<option value=$x selected = 1>$x</option>";
        }else{
                $flstrn .= "<option value=$x>$x</option>";
        }
}
if(!empty($jibie_arr)){
	//级别下拉菜单
	foreach(array_unique($jibie_arr) as $key=>$value){
		if(empty($value)){
			$value = '无级别';
		}
		if($value == $_GET['jibie']){
			$jibie_select .= "<option value = '$value' selected>$value</option>";
		}else{
			$jibie_select .= "<option value = '$value'>$value</option>";
		}
	}
}
//获取标签页label
if($u['is_zz'] == '1' && !empty($_GET['fzx_id'])){
	$fid = $_GET['fzx_id'];
}else{
	$fid = $u['fzx_id'];
}
$sql_label = "SELECT * FROM `sjqm` WHERE `fzx_id` = '{$fid}' GROUP BY `type` ORDER BY id DESC";
$re = $DB->query($sql_label);
$i = 0;
while ($data = $DB->fetch_assoc($re)) {
	// print_rr($data);
	$label .= <<<ETF
	<li>
      <a href="#tabs-$i" class='types' title="$data[type]" onclick="location='sjqm_list.php?name=&type=$data[type]&fzx_id=$fid#tabs-$i'" style="min-width:100px;">$data[type]</a>
    </li>
ETF;
$i++;
}
$label .=<<<EFO
	<li>
      <a href="#tabs-999" class='types' onclick="location='sjqm_list.php?name=&type=全部&fzx_id=$fid#tabs-999'" style="min-width:100px;">全部</a>
    </li>
EFO;
	if($fenlei=='全部'){
    	$type_label = "<th>类别</th>";
    }else{
    	$type_label = '';
    }
//=========================================================================================================================================================================================//
$ns = !empty($_GET['name']) ? " and `name`='$_GET[name]'":'';
//暂时不对试剂进行排序
$order = 'order by name,`youxiaoqi` ';
$sql = "SELECT * FROM `sjqm` WHERE `fzx_id`='$fzx_id' $nice_name_sql $jibie_sql $kucun_sql   $type_sql  $ns $order";
$rs = $DB->query($sql);
$i=1;
while($r = $DB->fetch_assoc($rs)){
	$youxiaoqi_bianse	= $kucun_bianse	= '';
	//库存提醒
	if(!empty($r['KCtixing']) && $r['kucun'] <= $r['KCtixing']){
		$kucun_bianse = " class='bianse' ";
	}
	//有效期提醒
	if(empty($r['GQtixing'])){
		$r['GQtixing']	= 0;
	}
	if(!empty($r['youxiaoqi']) ){
		$tixing_date	= date('Y-m-d',strtotime("+ {$r['GQtixing']} days"));
		if($tixing_date >= $r['youxiaoqi']){
			$youxiaoqi_bianse	= " class='bianse' ";
		}
	}
	if($u['kufang_manage'] != '1'){
		$operation="<a onclick='sq(this,1)'>领用申请</a>|<a onclick='sq(this,2)'>采购申请</a>";
	}else{
		$operation="<a onclick='sq(this,1)'>领用申请</a>|<a onclick='sq(this,2)'>采购申请</a>|<a href=\"javascript:if(confirm('你真的要删除名字为$r[name]的".$fl2[$r['type']]."么?\\n一经删除,无法恢复!')) location='sjqm.php?action=删除&id=$r[id]&wz_type=$r[type]'\">删除</a>|<a href=sjqm.php?action=修改&id=$r[id]&wz_type=$r[type]>修改</a>|<a href=sjqm.php?action=入库&id=$r[id]>入库</a>|<!--<a href=sjqm.php?action=出库&id=$r[id]>出库</a> |--> <a onclick='print_bq(this,$r[id],$r[kucun]);'>打印标签</a>";
	}
    $r['danjia'] = $r['danjia']? $r['danjia'] : '';
    if($fenlei=='全部'){
    	$type_label_line = "<td>$r[type]</td>";
    }else{
    	$type_label_line = '';
    }
    if($_GET['handle']=='download'){
    	eval("\$lines.=\"".gettemplate('sjqm_list_line_download').'";');
    }else{
		eval("\$lines.=\"".gettemplate('sjqm_list_line').'";');
    }
	$i++;
}
//得到库存下拉列表
$sql = "SELECT `kucun` FROM `sjqm` WHERE `fzx_id` = '{$fzx_id}' GROUP BY `kucun`";
$re = $DB->query($sql);
while($data = $DB->fetch_assoc($re)){
	if($data['kucun'] == $_GET['kucun']){
		$kucun_select.= "<option value='{$data['kucun']}' selected>{$data['kucun']}</option>";
	}else{
		$kucun_select.= "<option value='{$data['kucun']}'>{$data['kucun']}</option>";
	}	
}
//得到存放位置下拉列表
$sql = "SELECT `nice_name` FROM `sjqm` WHERE `fzx_id` = '{$fzx_id}' GROUP BY `nice_name`";
$re = $DB->query($sql);
while($data = $DB->fetch_assoc($re)){
	if(!empty($data['nice_name'])){
		if($data['nice_name'] == $_GET['nice_name']){
			$nice_name_select.= "<option value='{$data['nice_name']}' selected>{$data['nice_name']}</option>";
		}else{
			$nice_name_select.= "<option value='{$data['nice_name']}'>{$data['nice_name']}</option>";
		}	
	}
	
}
echo "<input type='hidden' value='{$u['kufang_manage']}' id='qx' />";
if($_GET['handle']=='download'){
	disp("sjqm_list_download");
}else{
	disp("sjqm_list");
}

?>
