<?php
include "../temp/config.php";
//领用申请和采购申请
if($_GET['type']=='sjqm_sq'||$_GET['type']=='sjqm_cg'){
	$sq_name=$_GET['name'];
	$sq_user=$u['userid'];
	$sq_num=$_GET['num'];
	$company=$_GET['buy_danwei'];
	$sq_date=time();
	$id=$_GET['id'];
	$sq_status=$_GET['type']=='sjqm_sq'?0:3;
	$sql = "INSERT INTO `sjqm_sq` (`sq_id` , `sq_name` , `sq_num` , `lq_user` , `sq_date` , `sq_status`,`company`) VALUES ('$id' , '$sq_name' , '$sq_num' , '$sq_user' , '$sq_date' , '$sq_status','$company')";
	if($DB->query($sql)){
		echo 'success';
	}else{
		echo 'wrong';
	}
	exit;
}
//申请记录删除
if($_GET['type']=='del_sq'){
	$id=$_GET['ids'];
	$sql="update `sjqm_sq` set `sq_jindu`='3' where `id` in ($id)";
	if($DB->query($sql)){
		echo 'success';
	}else{
		echo 'wrong';
	}
	exit;
}
//入库，出库
if($_GET['type']=='sq'){
	$id=$_GET['ids'];
	$rk_arr=explode(',',$id);
	foreach($rk_arr as $k=>$v){
		$info=$DB->fetch_one_assoc("select `sq_id`,`sq_num`,`lq_user` from `sjqm_sq` where `id`='$v'");
		$time=time();
		if($_GET['sq_type']=='rk'){
			$user=$u['userid'];
			$rksql1=$DB->query("UPDATE  `sjqm` SET `kucun` =  `kucun`+ '$info[sq_num]' WHERE id = '$info[sq_id]'");
			$type='r';
		}else{
			$user=$info['lq_user'];
			$rksql1=$DB->query("UPDATE  `sjqm` SET `kucun` =  `kucun`- '$info[sq_num]' WHERE id = '$info[sq_id]'");
			$type='c';
		}
		$sql2 = "SELECT `kucun` FROM `sjqm` WHERE `id` = '$info[sq_id]'";
		$rs = $DB->query($sql2);
		$r = $DB->fetch_assoc($rs);
		$rksql2=$DB->query("INSERT INTO `sjqm_ls` (`sj_id`, `type`, `time`, `shuliang`,  `jiecun`, `user`) VALUES('$info[sq_id]', '$type', '$time', '$info[sq_num]', '$r[kucun]', '$user')");
		$DB->query("update `sjqm_sq` set `sq_jindu`='2' where `id`='$v'");
	}
	echo 'success';
	exit;
}
if($_POST['handle'] == 'saomiao'){
	$sm_bh = $_POST['sm_bh'];
	$sql = "SELECT * FROM `sjqm` WHERE `sm_bh` = '{$sm_bh}'";
	$res=$DB->query($sql);
	$data=$DB->fetch_assoc($res);
	$htmls = <<<ETF
					<td>领用物品名称：</td>
					<td colspan=2><input  type="text" value="{$data['name']}"/></td>
					<input type="hidden" name="id" class='wp_id' value="{$data['id']}"/>
				 	<td><span style="margin-left:2px;color:red;">
					 	库存:{$data['kucun']}个</span>&nbsp;&nbsp;
					 	<a onclick="del(this)" style="cursor:pointer;">
					 		<img src="$rooturl/img/Close_16px.png" title='删除此申请'/>
					 	</a>&nbsp;&nbsp;
				 	</td>
ETF;
echo empty($data)?'wrong':$htmls;

// echo $htmls;
	die;
}else{
	$sql="SELECT * FROM sjqm WHERE id=$_POST[id]";
	$res=$DB->query($sql);
	$data=$DB->fetch_assoc($res);
	$htmls = <<<ETF
				<div style="margin-top:2px; ">领用物品名称：<input  type="text" value="{$data['name']}"/>领取数量：<input type="text"  class='wp_shuliang'   name="shuliang[]"/><input type="hidden" name="id[]" class='wp_id' value="{$data['id']}"/><span style="margin-left:2px;color:red;">库存:{$data['kucun']}个</span>&nbsp;&nbsp;<a onclick="del(this)" style="cursor:pointer;"><img src="$rooturl/img/Close_16px.png" title='删除此申请'/></a>&nbsp;&nbsp;<a onclick='show_buy(this,{$data['id']},"{$data['name']}");' style='cursor:pointer;' title='采购申请' class="glyphicon glyphicon-plus green"></a></div>
ETF;
	echo empty($data)?'wrong':$htmls;
die;
}