<?php
include '../temp/config.php';
include(INC_DIR."cy_func.php");
$fzx_id	= $u['fzx_id'];
if($_POST['act'] == 'xcxm'){
	$oldxc = $_POST['oldxc'];
	$xmid = $_POST['xmid'];
	$cydid = $_POST['cydid'];
	$oa = explode(',',$oldxc);
	$insert_pay = array();
	$ss = '';//标志
	$cy_info = $DB->fetch_one_assoc("select * from cy where id='$cydid'");
	//取出水样类型的大类和小类.
	$water_type_fenlei	= array();
	$sql_leixing		= $DB->query("SELECT * FROM `leixing` WHERE (fzx_id='$fzx_id' OR `parent_id`='0') AND act='1'");
	while($rs_leixing = $DB->fetch_assoc($sql_leixing)){
		if($rs_leixing['parent_id']=='0'){
			$water_type_fenlei[$rs_leixing['id']]	= $rs_leixing['id'];
		}else{
			$water_type_fenlei[$rs_leixing['id']]   = $rs_leixing['parent_id'];
		}
	}
	//查询出本中心的人
	$user_arr=array();
	$user_sql="SELECT * FROM users WHERE fzx_id='".$fzx_id."'";
	$user_query=$DB->query($user_sql);
	while($user_rs=$DB->fetch_assoc($user_query)){
		$user_arr[$user_rs['id']]=$user_rs['userid'];
	}

	if(in_array($xmid,$oa)){
		$ss='1';
		$kks = array_search($xmid,$oa);
		unset($oa[$kks]);
		if($cy_info['status']<'6'){
			$delsql1 = $DB->query("delete from assay_pay where cyd_id='$cydid' and vid = '$xmid'");
			$delsql2 = $DB->query("delete from assay_order where cyd_id='$cydid' and vid = '$xmid'");
		}else{
			$recsql = $DB->query("select * from cy_rec where cyd_id='$cydid'");
			while($cy_rec1 = $DB->fetch_assoc($recsql)){
					$sql_pay_xmfa   = $DB->query("SELECT *  FROM `xmfa`  WHERE  fzx_id='$fzx_id' AND lxid='".$cy_rec1['water_type']."' AND xmid='$xmid' AND act='1' AND mr='1' order by xmid");
					$num_pay_xmfa	= $DB->num_rows($sql_pay_xmfa);
					//如果小类中没有方法，就取大类中的方法
					if($num_pay_xmfa<1){
						$sql_pay_xmfa   = $DB->query("SELECT *  FROM `xmfa`  WHERE  fzx_id='$fzx_id' AND lxid='".$water_type_fenlei[$cy_rec1['water_type']]."' AND xmid='$xmid' AND act='1' AND mr='1' order by xmid");
					}

					while($rs_pay_xmfa = $DB->fetch_assoc($sql_pay_xmfa)){
						$ss = $DB->query("UPDATE `assay_pay` SET userid='".$user_arr[$rs_pay_xmfa['userid']]."',userid2='".$user_arr[$rs_pay_xmfa['userid2']]."',is_xcjc='0' WHERE cyd_id='$cydid' AND vid='$xmid' and fid='".$rs_pay_xmfa['id']."'");
					}
			}
		}
		
		if($oa){
			$new_xc = implode(',',$oa);
		}else{
			$new_xc = '';
		}
		$upcy = $DB->query("update cy set xc_exam_value ='$new_xc' where id='$cydid'");
	}else{
		$delsql1 = $DB->query("delete from assay_pay where cyd_id='$cydid' and vid = '$xmid'");
		$delsql2 = $DB->query("delete from assay_order where cyd_id='$cydid' and vid = '$xmid'");
        #########现场检测项目的处理:往assay_order表里插入数据
			//采样批次修改时先去掉无关的order表记录
		$recsql = $DB->query("select * from cy_rec where cyd_id='$cydid'");
		while($cy_rec_info = $DB->fetch_assoc($recsql)){
			//将所有的现场检测项目插入到assay_order表
            $assay_values_arr =explode(',',$cy_rec_info['assay_values']); 
			if(in_array($xmid,$assay_values_arr)){
				$insert_order	= array();
				$xc_water_type[$cy_rec_info['water_type']] = $xmid;
				$insert_order['cyd_id']	= $cy_rec_info['cyd_id'];//采样单id
	            $insert_order['cid']	= $cy_rec_info['id'];//cy_rec表id
	            $insert_order['sid']	= $cy_rec_info['sid'];//站点表id
	            $insert_order['water_type']	= $cy_rec_info['water_type'];//水样类型
	            $insert_order['site_name']	= $cy_rec_info['site_name'];//站点名称
	            $insert_order['river_name']	= $cy_rec_info['river_name'];//河流名称
	            $insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
	            $insert_order['assay_over']	= '0';//化验单某样品完成状态
	            $insert_order['create_date']	= $cy_rec_info['create_date'];//化验单创建时间
	            $insert_order['vid']    = $xmid;//化验项目id
				$insert_order['hy_flag']	= $cy_rec_info['zk_flag'];//质控标识
                new_record('assay_order' ,$insert_order);//将信息插入到assay_order表
			}
		}
		#########现场检测项目的处理:往assay_pay表里插入数据
			//采样批次修改时先去掉无关的pay表记录
		
		$values_fangfa  = array();
		foreach($xc_water_type as $key_water_type=>$value_vids){
				//取出方法表配置的一些信息
				$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='$key_water_type' AND xmfa.xmid='$value_vids' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
				$num_pay_xmfa	= $DB->num_rows($sql_pay_xmfa);
				//如果小类中没有方法，就取大类中的方法
				if($num_pay_xmfa<1){
					$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='{$water_type_fenlei[$key_water_type]}' AND xmfa.xmid='$value_vids' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
				}
				$insert_pay	=  array();
				while($rs_pay_xmfa = $DB->fetch_assoc($sql_pay_xmfa)){
					//如果标准中没有此项目的名称，就到session中去找
					$insert_pay['cyd_id']		= $cydid;
					$insert_pay['fzx_id']		= $fzx_id;
					$insert_pay['create_date']	= $cy_info['create_date'];
					$insert_pay['is_xcjc']		= '1';
			   	    $insert_pay['vid']    = $xmid;//化验项目id
					$insert_pay['assay_element']	= $_SESSION['assayvalueC'][$rs_pay_xmfa['xmid']];
					$insert_pay['userid']   = $cy_info['cy_user'];
					$insert_pay['userid2']  = $cy_info['cy_user2'];
					$insert_pay['unit']     = $rs_pay_xmfa['unit'];
					$insert_pay['td2']      = $rs_pay_xmfa['method_number'];//$rs_pay_xmfa['fangfa'];
					$insert_pay['td3']      = $rs_pay_xmfa['jcx'];
					$insert_pay['td4']      = $rs_pay_xmfa['yq_mingcheng'];
					$insert_pay['td5']      = $rs_pay_xmfa['yq_sbbianhao'];
					$insert_pay['table_id'] = $rs_pay_xmfa['hyd_bg_id'];
					$insert_pay['fid']		= $rs_pay_xmfa['id'];//项目法表的id
					if(!@array_key_exists($rs_pay_xmfa['fangfa'],$values_fangfa[$rs_pay_xmfa['xmid']])){
						$tid    = new_record('assay_pay' ,$insert_pay);//将信息插入到assay_pay表
						$values_fangfa[$rs_pay_xmfa['xmid']][$rs_pay_xmfa['fangfa']]    = $tid;
					}
					$tid	= $values_fangfa[$rs_pay_xmfa['xmid']][$rs_pay_xmfa['fangfa']];
					//全程序空白没有水样类型，默认都更新它
					if($tid){
						$ss = $DB->query("UPDATE `assay_order` SET tid='$tid' WHERE cyd_id='{$insert_pay['cyd_id']}' AND vid='{$insert_pay['vid']}' AND (water_type='$key_water_type' OR hy_flag='1')");
					}
					
				}
		}
		if($oldxc){
			$new_xc = $oldxc.','.$xmid;
		}else{
			$new_xc = $xmid;
		}
		$upcy = $DB->query("update cy set xc_exam_value ='$new_xc' where id='$cydid'");
	}
if($ss){
	echo $new_xc ;
}else{
	echo 'wrong';
}
			
}

?> 