<?php
/**
 * 功能： 报告列表弹出层显示
 * 作者：zhengsen
 * 日期：2014-10-15
 * 描述：
*/
include '../temp/config.php';
include INC_DIR . "cy_func.php";
$divline="";
$fzx_id=$u['fzx_id'];

//ajax改变报告的模板
if($_POST['action']=='change_bg_mb'){
	if($_POST['rec_id']&&$_POST['te_id']){
		$query=$DB->query("UPDATE report SET te_id='".$_POST['te_id']."' WHERE cy_rec_id='".$_POST['rec_id']."'");
		if($query){
			echo 1;
		}else{
			echo 0;
		}
	}
	exit();
}
//ajax改变报告的打印状态
if($_POST['action']=='change_print_status'){
	if($_POST['rec_id']&&isset($_POST['p_status'])){
		//查询当前cy表的进度
		$cy_status_arr=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_POST['cyd_id']."'");
		if($cy_status_arr['status']==7){
			 $DB->query("UPDATE cy SET status='8' WHERE id='".$_POST['cyd_id']."'"); 
		}
		if($cy_status_arr['status']=='8'||$DB->affected_rows()){
			$bg_dy_date='';
			if($_POST['p_status']==1){
				$bg_dy_date=date('Y-m-d');
			}
			$query=$DB->query("UPDATE report SET print_status='".$_POST['p_status']."',bg_dy_date='".$bg_dy_date."' WHERE cy_rec_id='".$_POST['rec_id']."'");
			if($query){
				echo 1;
			}else{
				echo 0;
			}
		}else{
			echo -1;
		}
	}
	exit();
}
//ajax修改 报告是否显示 评定信息
if($_POST['action']=='change_show_pingjia'){
	if(!empty($_POST['id']) && !empty($_POST['value'])){
		$old_report	= $DB->fetch_one_assoc("SELECT id,json FROM `report` WHERE `id`='{$_POST['id']}'");
		$report_json	= array();
		if(!empty($old_report['json'])){
			$report_json	= json_decode($old_report['json'],true);
		}
		$report_json['show_pingjia']	= $_POST['value'];
		$report_json_str	= JSON($report_json);
		$DB->query("UPDATE `report` SET `json`='{$report_json_str}' WHERE `id`='{$old_report['id']}' ");
		echo "1";
	}else{
		echo "0";
	}
	exit;
}

//向report初始化报告的信息
if(!empty($_GET['cyd_id'])){
	$query=$DB->query("SELECT * FROM report WHERE cyd_id='".$_GET['cyd_id']."'");
	$nums=mysql_num_rows($query);
	if(!$nums){
		$R=$DB->query("SELECT cr.*,c.cy_date,c.ys_date FROM cy c JOIN cy_rec cr ON c.id=cr.cyd_id where cyd_id='".$_GET['cyd_id']."' and zk_flag>=0 and sid>=0 ORDER BY cr.bar_code");
		while($row = $DB->fetch_assoc($R)){
			//print_rr($row);
			$temp_rs=array();
			$temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$row['water_type']."'");
			if(!empty($temp_rs)){
				$te_id= $temp_rs['id'];
			}else{
				$max_water_type=get_water_type_max($row['water_type'],$fzx_id);
				$temp_rs=$DB->fetch_one_assoc("SELECT id FROM report_template WHERE  state > 0 AND water_type='".$max_water_type."'");
				$te_id= $temp_rs['id'];
			}			 
			$DB->query(" INSERT INTO report(cyd_id,water_type,cy_rec_id,state,bg_date,te_id,tab_user)values('".$_GET['cyd_id']."','".$row['water_type']."','".$row['id']."','9',curdate(),'".$te_id."','".$u['userid']."')");  
		 }
	}
	//查询当前cy表的进度
	$cy_status_arr=$DB->fetch_one_assoc("SELECT * FROM cy WHERE id='".$_GET['cyd_id']."'");
	if($cy_status_arr['status']==7){
		 $DB->query("UPDATE cy SET status='8' WHERE id='".$_GET['cyd_id']."'"); 
	}
}
//打印状态
$print_status_arr=array(0=>"未打印",1=>"已打印");

//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询所有模板
$bgmb_list = array();
$cyd_id=$_GET['cyd_id'];	
$C = $DB->query("SELECT  c.*,s.water_type FROM cy_rec c LEFT JOIN `sites` s ON c.sid = s.id WHERE c.cyd_id='".$cyd_id."' AND sid>'0' AND zk_flag>'-1' order by c.bar_code");
while($c=$DB->fetch_assoc($C)){
	if(empty($c['water_type'])){
		$water_type_bh=substr($c['bar_code'],1,1);
		$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
		$water_type_max=$c['water_type']=get_water_type_max($water_type,$fzx_id);
	}else{
		$water_type_max=get_water_type_max($c['water_type'],$fzx_id);
	}

// 循环所有模板  设置站点的模板	 
	$url    =   'cid='.$c['id'].'&cyd_id='.$cyd_id.'&sid='.$c['sid'];
	$re_rs=$DB->fetch_one_assoc("SELECT  id,te_id,print_status,json FROM report WHERE cyd_id='".$cyd_id."'  AND cy_rec_id='".$c['id']."' ");
	$report_json	= array();
	if(!empty($re_rs['json'])){
		$report_json	= json_decode($re_rs['json'],true);
	}
	//判断该报告是否被退回过
	$back_warn='';
	if($re_rs['print_status']=='-1'){
		$back_warn="<span style=\"color:red\" id=".$c['id'].">(被退回)</span>";
	}
	$sql ="SELECT  * FROM report_template WHERE state = '1' ";

	$rows = $DB->query($sql);
	$bgmb_list  = '';  
	while($row=$DB->fetch_assoc($rows)){
		if( $row['id'] ==  $re_rs['te_id'] ||(empty($re_rs) && $row['water_type'] == $water_type_max) ){
			$bgmb_list  .= ' <option value ='.$row['id'].'  selected="selected">'.$row['te_name'].'</option>';  
		 }else{
			$bgmb_list  .= ' <option value ='.$row['id'].'>'.$row['te_name'].'</option>';  
		 }
	}
	//计算完成度
	$sql_order="SELECT ao.vd0,ap.over  FROM assay_order ao LEFT JOIN assay_pay ap ON ao.tid=ap.id  where ao.cid='".$c['id']."' AND ao.cyd_id='".$cyd_id."' AND ao.hy_flag>=0  AND ao.sid>0 ";
	$query_order=mysql_query($sql_order);
	$y_nums=$z_nums=0;
	while($rs_order=$DB->fetch_assoc($query_order)){
		$z_nums++;
		if(!empty($rs_order['vd0'])||$rs_order['vd0']=='0'){
			if(empty($show_shuju_arr)){
				$y_nums++;
			}else{
				if(in_array($rs_order['over'],$show_shuju_arr)){
					$y_nums++;
				}
			}
		}
	}
	
	 if($c[water_type] !=''){
	 $bzlx=$DB->fetch_one_assoc("SELECT  lname FROM leixing WHERE id='".$c['water_type']."'");
	 $szlx= $bzlx['lname'];
	}
	//显示打印状态
	$print_sta_option='';
	foreach($print_status_arr as $key=>$value){
		if($key==$re_rs['print_status']){
			$print_sta_option.="<option value=".$key." selected=\"selected\">".$value."</option>";
		}else{
			$print_sta_option.="<option value=".$key.">".$value."</option>";
		}
	}
	//是否显示 评定加过的选择菜单
	if($report_json['show_pingjia'] == 'no'){
		$pingding_select	= "<select name='show_pingding' onchange=\"change_show_pingjia(this);\" shuju_id='{$re_rs['id']}'><option value='yes'>显示</option><option value='no' selected>不显示</option></select>";
	}else{
		$pingding_select	= "<select name='show_pingding' onchange=\"change_show_pingjia(this);\" shuju_id='{$re_rs['id']}'><option value='yes' selected>显示</option><option value='no'>不显示</option></select>";
	}
	$divline.=  '<tr align=center class="tr">
				
					<td nowrap="nowrap"><label>'.$c['id'].'<input type="checkbox" name="cids[]" value='.$c['id'].'></label></td>
					<td nowrap="nowrap">
						'.$c['bar_code'].' 
					</td>
					<td nowrap="nowrap">'.$szlx.'</td>
				 
					<td style="text-align:left;">'.$c['site_name'].'</td>
					<td nowrap="nowrap">'.$y_nums.'/'.$z_nums.'</td>
					<td nowrap="nowrap"> 
					<select  name="bgmb" onchange=change_bg_mb('.$c['id'].',this)>  
					 '.$bgmb_list.'
					</select>  
				   </td>
				   <td>'.$pingding_select.'</td>
				   <td nowrap="nowrap">
					<select name="print_status" onchange=change_print_status('.$c['id'].',this)>
					'.$print_sta_option.'
					</select>
					<br/>'.$back_warn.'
				   </td>
					<td id="state'.$c['id'].'" align="center" nowrap="nowrap">
						<a href="'.$rooturl.'/baogao/bg_chakan.php?'.$url.'" target="_blank" class="btn btn-xs btn-primary">查看报告</a>&nbsp;
						<a href="'.$rooturl.'/baogao/bg_chakan.php?lx=2&'.$url.'" target="_blank" class="btn btn-xs btn-primary">下载WORD</a> 
						<button type="button" class="btn btn-xs btn-primary" onclick="bg_xm_set('.$cyd_id.','.$c[id].')" >项目设定</button> 
					</td>
				</tr>';
}
	disp("bg/bg_site_list");
?>