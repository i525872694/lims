<?php
/**
 * 功能：下达采样任务后台处理页面
 * 作者：韩枫
 * 日期：2014-05-26
 * 描述
*/
include("../temp/config.php");
include(INC_DIR."cy_func.php");
$fzx_id	= $u['fzx_id'];
$success_info="批任务下达成功";
if(!$_POST['cy_flag']){
	$_POST['xcjc_value'] = '';
}
if(!empty($_POST['cyd_id'])){
	$success_info="批任务修改成功";
	$cy_rs	= $cy_rec_old	= $cy_rec_qckb	= $cy_rec_old_zk	= $cy_rec_sites	= array();
	$cy_rs	= $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE id='".$_POST['cyd_id']."'");
	$_POST['site_type']=$cy_rs['site_type'];
	$sql_cy_rec	= $DB->query("SELECT * FROM `cy_rec` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_cy_rec	= $DB->fetch_assoc($sql_cy_rec)) {
		if($rs_cy_rec['sid']>0){
			$cy_rec_sites[]	= $rs_cy_rec['sid'];
		}
		if($rs_cy_rec['zk_flag']>=0){
			if($rs_cy_rec['zk_flag']==1){
				$cy_rec_qckb[$rs_cy_rec['kb_sid']]= $rs_cy_rec;//批次修改之前正全程序空白的信息
			}else{
				$cy_rec_old[$rs_cy_rec['sid']]	= $rs_cy_rec;//批次修改之前正常站点
			}
			
		}else{
			$cy_rec_old_zk[$rs_cy_rec['sid']]	= $rs_cy_rec;//批次修改之前现场平行站点的信息
		}
	}
	$pay_old	= $order_old	= array();
	$sql_assay_pay	 = $DB->query("SELECT * FROM `assay_pay` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_assay_pay = $DB->fetch_assoc($sql_assay_pay)) {
		$pay_old[$rs_assay_pay['vid']]	= $rs_assay_pay;//不同方法时有可能会重复？？？
	}
	$sql_assay_order	= $DB->query("SELECT * FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}'");
	while ($rs_assay_order = $DB->fetch_assoc($sql_assay_order)) {
		$order_old[$rs_assay_order['cid']][$rs_assay_order['vid']]	= $rs_assay_order;
	}
	//如果是修改采样任务要把之前的任务删除掉重新生成新的一批
	//$DB->query("DELETE FROM `cy` WHERE id='".$_POST['cyd_id']."'");
	//$DB->query("DELETE FROM `cy_rec` WHERE cyd_id='".$_POST['cyd_id']."'");
	$DB->query("DELETE FROM `assay_pay` WHERE cyd_id='".$_POST['cyd_id']."' AND `is_xcjc`='1'");
	//$DB->query("DELETE FROM `assay_order` WHERE cyd_id='".$_POST['cyd_id']."'");
}
$cyd_id_list    = [];
#########取出所有水样类型
$water_type_all	= array();
$sql_water_type	= $DB->query("SELECT * FROM `leixing` WHERE 1");
while($rs_water_type=$DB->fetch_assoc($sql_water_type)){
	$water_type_all[$rs_water_type['id']]	= $rs_water_type['parent_id'];
	$water_type_name_arr[$rs_water_type['id']]	= $rs_water_type['lname'];
}
#########取出现场平行项目
$xcpx_value     = $DB->fetch_one_assoc("SELECT module_value1 FROM `n_set` WHERE fzx_id='$fzx_id' AND module_name='xcpx_value' AND module_value2='{$_POST['site_type']}' ORDER BY id DESC LIMIT 1");
$xcpx_value_arr = @explode(',',$xcpx_value['module_value1']);
#########取出全程序空白项目
$qckb_value     = $DB->fetch_one_assoc("select module_value1 from `n_set` where fzx_id='$fzx_id' and module_name='qckb_value' and module_value2='{$_POST['site_type']}' order by id DESC limit 1");
$qckb_value_arr = @explode(',',$qckb_value['module_value1']);
//获取站点有没有多个垂线和层面
$sql_site_line_vertical		= array();
$sql_site_line_vertical     = $DB->query("SELECT * FROM `sites` WHERE fzx_id='$fzx_id' OR fp_id='$fzx_id' AND (`site_code`!='' OR `site_code` is NOT NULL) ORDER BY tjcs,site_name");
while ($rs_site_line_vertical= $DB->fetch_assoc($sql_site_line_vertical)){
	//奇怪，sql里面的为空限制不管用。这里只能再加一道关
	if($rs_site_line_vertical['site_code'] !=''){
			$site_line_vertical[$rs_site_line_vertical['site_code']][$rs_site_line_vertical['water_type']][]    = 1;
			//$site_line_vertical[$rs_site_line_vertical['site_code']][]    = $rs_site_line_vertical['site_code'];
	}
}
#####循环传过来的数组，将每个批次的数据插入cy表
$xy_i	= 0;//下达采样任务成功的批次数
foreach($_POST as $key=>$value){
	//区分出每个批次的信息
	$cy_info	 = $xcpx_sites	= $pay_vids = array();
	$all_assay_values= $water_type_arr = $xc_exam_arr = $xc_water_type = array();
	if(is_array($value)&&@array_key_exists('sites',$value)){
		//修改采样任务时，先将去掉的站点的cy_rec表的记录删除掉
		if(!empty($_POST['cyd_id'])){
			$old_sites	= array_diff($cy_rec_sites, $value['sites']);
			$old_sites_str	= implode(',', $old_sites);
			if($old_sites_str!=''){
				$DB->query("DELETE FROM `cy_rec` WHERE cyd_id='{$_POST['cyd_id']}' and (sid in ($old_sites_str) OR `kb_sid` in ('{$old_sites_str}'))");
				$DB->query("DELETE FROM `assay_order`	WHERE cyd_id='{$_POST['cyd_id']}' and sid in ($old_sites_str)");
			}
		}
		########生成记录到cy表
		if(stristr($value['cy_user'],',')){
			$tmp_user	= explode(',', $value['cy_user']);
			$value['cy_user']	= $tmp_user[0];
			$value['cy_user2']	= $tmp_user[1];
		}
		$cy_info['site_type']	= $_POST['site_type'];//任务性质
		$cy_info['cy_flag']		= $_POST['cy_flag'];//是否是委托检测
		$cy_info['fzx_id']		= $fzx_id;//分中心id
		
		$cy_info['group_name']	= ($_POST['group_name']!='') ? $_POST['group_name'] : $key;//批名
		$cy_info['cy_user']		= ($_POST['cy_flag']=='1') ? $value['cy_user'] : "委托方";//采样人1
		$cy_info['cy_user2']	= ($_POST['cy_flag']=='1') ? $value['cy_user2']:"";//采样人2
		$cy_info['status']		= ($_POST['cy_flag']=='1') ? "0" : "5";//批次样品状态,如果是委托方送样，那状态直接到“样品接受”
		$cy_info['cy_date']		= $_POST['cy_riqi'];//采样日期
		$cy_info['create_date']	= date( 'Y-m-d' );//创建日期
		$cy_info['create_user'] = $u['userid'];//创建人
		$cy_info['sites']		= implode(",",$value['sites']);//站点集合
		$cy_info['xc_exam_value']= @implode(",",$_POST['xcjc_value']);//现场检测项目集合
		$cy_info['xc_huanjing'] = @implode(",",$_POST['xc_huanjing_value']);//环境参数集合
		$cy_info['snkb']		= ($value['snkb']=='yes') ? 1 : 0;//是否同时检测室内空白
		//计算样品数量
		$cy_info['yp_count']	= count($value['sites']);
		if(!empty($value['xcpx'])){
			$cy_info['yp_count']	= $cy_info['yp_count']+count($value['xcpx']);
		}
		if(isset($value['qckb'])){
			$cy_info['yp_count']	= $cy_info['yp_count']+count($value['qckb']);
		}
		//现场检测项目数组
		if(!empty($_POST['xcjc_value'])){
			$xc_exam_arr	= $_POST['xcjc_value'];
		}
		if(empty($_POST['cyd_id'])){
			$cy_info["cyd_bh"]	= new_cyd_bh($cy_info['site_type'],$cy_info['cy_date']);//生成采样单编号,函数在cy_func.php中
			$cyd_id = new_record( 'cy', $cy_info );//将数据插入cy表，函数在function.php中
			if(!$first_cyd_id){
				$first_cyd_id=$cyd_id;
			}
			if( !$cyd_id ) die( "生成采样单失败!" );
		}else{
		########修改采样单的时候只更新不生成
			//去除掉不需要更新的字段
			unset($cy_info["cyd_bh"]);
			unset($cy_info["status"]);
			unset($cy_info["create_date"]);
			unset($cy_info["create_user"]);
			$sum_cyd	= update_rec('cy', $cy_info,$_POST['cyd_id']);
			$cyd_id		= $_POST['cyd_id'];
			if(!$first_cyd_id){
				$first_cyd_id=$cyd_id;
			}
		}
		#########生成记录到cy_rec表
		$xcpx_sites	= $value['xcpx'];//检测现场平行的站点
		$qckb_sites	= $value['qckb'];//检测qckb的站点
		$where_sites	= '';
		if($key!='jdrw'){
             $where_sites   .= "AND sg.group_name = '{$cy_info['group_name']}'";
        }
		//可以用gr_id
		if(!empty($value['gr_ids'])){
			$where_sites	.= "AND sg.id in (".implode(",",$value['gr_ids']).")";
		}
		$sql_siteArr	= $DB->query("
			SELECT s.id AS sid, s.river_name,s.site_code,s.fix_bar_code,s.site_line,s.site_vertical,s.site_name,s.water_type,sg.id, sg.assay_values, curdate() AS create_date,sg.xcpx_values,sg.qckb_values,sg.milieu_values,sg.xcpx_milieu_values,sg.qckb_milieu_values,s.jingdu, s.weidu
			FROM `site_group` AS sg RIGHT JOIN sites AS s ON s.id = sg.site_id
			WHERE s.fzx_id='$fzx_id' $where_sites AND s.`id` IN({$cy_info['sites']}) GROUP BY s.`id` ORDER BY sg.site_sort, field(s.id,{$cy_info['sites']})");
			//s.sort,s.id
        if($DB->num_rows($sql_siteArr) <= 0){
            $where_sites    = "AND sg.group_name = '{$cy_info['group_name']}'";
            $sql_siteArr    = $DB->query("
            SELECT s.id AS sid, s.river_name,s.site_code,s.fix_bar_code,s.site_line,s.site_vertical,s.site_name,s.water_type,sg.id, sg.assay_values, curdate() AS create_date,sg.xcpx_values,sg.qckb_values,sg.milieu_values,sg.xcpx_milieu_values,sg.qckb_milieu_values,s.jingdu, s.weidu
            FROM `site_group` AS sg LEFT JOIN sites AS s ON s.id = sg.site_id
            WHERE sg.fzx_id='$fzx_id' $where_sites AND sg.`site_id` IN({$cy_info['sites']}) AND sg.act='1' GROUP BY s.`id` ORDER BY sg.site_sort, field(s.id,{$cy_info['sites']})");
        }
		while($rs_siteArr = $DB->fetch_assoc($sql_siteArr)){
			//监督任务的现场环境项目可能是由页面设置的，如果设置过了，这里用设置的项目，不用数据库里存储的
			if(!empty($value['milieu_values'][$rs_siteArr['sid']])){
				$rs_siteArr['milieu_values']	= $value['milieu_values'][$rs_siteArr['sid']];
			}
			if(!empty($value['xcpx_milieu_values'][$rs_siteArr['sid']])){
				$rs_siteArr['xcpx_milieu_values']	= $value['xcpx_milieu_values'][$rs_siteArr['sid']];
			}
			if(!empty($value['qckb_milieu_values'][$rs_siteArr['sid']])){
				$rs_siteArr['qckb_milieu_values']	= $value['qckb_milieu_values'][$rs_siteArr['sid']];
			}
			//监督任务的项目可能是由页面设置的，如果设置过了，这里用设置的项目，不用数据库里存储的
			if(!empty($value['sites_value'][$rs_siteArr['sid']])){
				$rs_siteArr['assay_values']	= $value['sites_value'][$rs_siteArr['sid']];
			}
			if(array_key_exists($rs_siteArr['sid'],$value['xcpx_value'])){
				//echo "没有设置{$rs_siteArr['site_name']}的现场平行项目！";
				$rs_siteArr['xcpx_values']	= $value['xcpx_value'][$rs_siteArr['sid']];
			}
			if(array_key_exists($rs_siteArr['sid'],$value['qckb_value'])){
				//echo "没有设置{$rs_siteArr['site_name']}的全程空白项目！";
				$rs_siteArr['qckb_values']	= $value['qckb_value'][$rs_siteArr['sid']];
			}
			unset($rs_siteArr['id']);
			//判断相同站码但水样类型不同的站点
			$line_vertical  = '';
			if(count($site_line_vertical[$rs_siteArr['site_code']])>1){
				//相同站码 不同水样类型的情况，如果需要提醒。就把下面代码的注释去掉
				//$line_vertical	.= "(".$water_type_name_arr[$rs_siteArr['water_type']].")";
			}
			//判断出该站点的垂线和层面
			if(count($site_line_vertical[$rs_siteArr['site_code']][$rs_siteArr['water_type']])>1){
				$str_site_line		=  $global['site_line'][$rs_siteArr['site_line']];
				$str_site_vertical	=  $global['site_vertical'][$rs_siteArr['site_vertical']];
				$line_vertical		.= "(".$str_site_line.$str_site_vertical.")";
			}
			$rs_siteArr['site_name'].= $line_vertical;
			unset($rs_siteArr['site_code']);
			unset($rs_siteArr['site_line']);
			unset($rs_siteArr['site_vertical']);
			$fix_bar_code   = $rs_siteArr['fix_bar_code'];//记录下固定的样品编号
	        unset($rs_siteArr['fix_bar_code']);
			$cy_rec_info	= array();
			$xcpx_values	= $rs_siteArr['xcpx_values'];//现场平行样检测的项目
			$qckb_values    = $rs_siteArr['qckb_values'];//全程空白检测的项目
			$xcpx_milieu_values	= $rs_siteArr['xcpx_milieu_values'];//现场平行样检测的项目
			$qckb_milieu_values    = $rs_siteArr['qckb_milieu_values'];//全程空白检测的项目
			unset($rs_siteArr['xcpx_values']);//向rec表添加数据的时候，不需要这个字段
			unset($rs_siteArr['qckb_values']);//向rec表添加数据的时候，不需要这个字段
			unset($rs_siteArr['xcpx_milieu_values']);//向rec表添加数据的时候，不需要这个字段
			unset($rs_siteArr['qckb_milieu_values']);//向rec表添加数据的时候，不需要这个字段
			$cy_rec_info	= $rs_siteArr;//站点的基本信息
			$cy_rec_info['cyd_id']	= $cyd_id;//采样单id
			$cy_rec_info['zk_flag']	= @in_array( $rs_siteArr['sid'],$xcpx_sites) ? 5 : 0;//质控标识
			$qckb_flag	= @in_array( $rs_siteArr['sid'],$qckb_sites) ? 1 : 0;//需要添加全程空白的标志
			//根据小类查出大类
			$fater_water	= $cy_rec_info['water_type'];
			/*if(!array_key_exists($fater_water,$global['bar_code']['water_type'])){
				if($water_type_all[$fater_water]!='0'){
					$fater_water	= $water_type_all[$fater_water];
				}else{
					echo "<script>alert('请联系管理员：水样类型$fater_water没有配置样品编号');</script>";
				}
			}*/
			$cy_rec_info['bar_code']= new_bar_code($cy_info['site_type'],$fater_water,$cy_info['cy_date'],$fix_bar_code);//生成样品编号,函数在cy_func.php中
 			$old_bar_code   = $cy_rec_info['bar_code'];//记录下原样的样品编号
			//统计出一共有集中 水样类型并存到数组中
			if(!in_array($rs_siteArr['water_type'],$water_type_arr)){
				$water_type_arr[]       = $rs_siteArr['water_type'];
			}
			//将默认采样容器瓶数写入采样rec表的json字段
			$rq_sql		= "SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
			$rq_query	= $DB->query($rq_sql);
			$tmp_rec_json	= array();
			if(!empty($cy_rec_old[$cy_rec_info['sid']]['json'])){
				$tmp_rec_json	= json_decode($cy_rec_old[$cy_rec_info['sid']]['json'],true);
				if($tmp_rec_json['rq']){
					unset($tmp_rec_json['rq']);//清除历史容器信息
				}
			}
			$avarr		= array_diff(explode(',',$cy_rec_info['assay_values']),$xc_exam_arr);
			while($rq_rs=$DB->fetch_assoc($rq_query)){
				$rq_value=explode(',',$rq_rs['vid']);
				if(array_intersect($avarr,$rq_value)){
					$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
				}
			}
			$cy_rec_info['json']= JSON($tmp_rec_json);
			//采样容器存储结束
			if(empty($_POST['cyd_id']) || empty($cy_rec_old[$cy_rec_info['sid']]['id'])){
				$cid	= new_record('cy_rec', $cy_rec_info);//将数据插入cy_rec表，函数在function.php中
			}else{
				$cid	= $cy_rec_old[$cy_rec_info['sid']]['id'];
				//unset($cy_rec_info['bar_code']);
				$cy_rec_info['bar_code']	= $cy_rec_old[$cy_rec_info['sid']]['bar_code'];
				$update_cy_rec_info	= $cy_rec_info;
				unset($update_cy_rec_info['create_date']);
				unset($update_cy_rec_info['create_man']);
				update_rec('cy_rec', $update_cy_rec_info,$cid);
			}
			$assay_values_arr       = explode(',',$cy_rec_info['assay_values']);//此站点所测的项目（数组）
			//获得一批中所有的项目(后面取交集用)
			$all_assay_values       = array_unique(array_merge($all_assay_values,$assay_values_arr));
			##########全程序空白样的处理
			$cy_rec_qckb_info = array();
			$qckb_zk = '';
			if($qckb_flag == 1){
					$yuan_cid	= $cid;//原样的cid
					$cy_rec_qckb_info = $cy_rec_info;//避免全程空白修改了原样的order表信息
					$qckb_bar_code	= $cy_rec_qckb_info['bar_code'];//记录全程空白原样的样品编号，后面现场项目插入order表用
					$qckb_zk = 1;//主要给样品编号编号时使用的
					$cy_rec_qckb_info['bar_code']= new_bar_code($cy_info['site_type'],$fater_water,$cy_info['cy_date'],$fix_bar_code);//qckb样的新样品编号
					$qckb_zk = '';
					$cy_rec_qckb_info['zk_flag']	= '1';//qckb质控标识
					$cy_rec_qckb_info['site_name']	= $cy_rec_qckb_info['site_name'].'全程空白';//qckb站点名称
					$qckb_assay_values     = explode(",",$rs_siteArr['assay_values']);
					$cy_rec_qckb_info['kb_sid']	= $cy_rec_qckb_info['sid'];
					$cy_rec_qckb_info['sid'] = 0;
					$cy_rec_qckb_info['river_name'] ='质控';
					$cy_rec_qckb_info['water_type'] = '0';
					if(empty($qckb_values)){
						$cy_rec_qckb_info['assay_values']	= implode(",",array_diff($qckb_assay_values,$qckb_value_arr));
					}else{
						$qckb_values_arr	= explode(",",$qckb_values);
						$cy_rec_qckb_info['assay_values']	= implode(",",array_intersect($qckb_values_arr,$qckb_assay_values));
					}
					if(empty($qckb_milieu_values)){
						$cy_rec_qckb_info['milieu_values']	= $rs_siteArr['milieu_values'];
					}else{
						$qckb_milieu_values_arr	= explode(",",$qckb_milieu_values);
						$cy_rec_qckb_info['milieu_values']	= implode(",",array_intersect($qckb_milieu_values_arr,explode(',',$rs_siteArr['milieu_values'])));
					}
					//将默认采样容器瓶数写入采样rec表的json字段
					$rq_sql		= "SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
					$rq_query	= $DB->query($rq_sql);
					$tmp_rec_json	= array();
					if(!empty($cy_rec_qckb[$cy_rec_info['sid']]['json'])){
						$tmp_rec_json	= json_decode($cy_rec_qckb[$cy_rec_info['sid']]['json'],true);
						if($tmp_rec_json['rq']){
							unset($tmp_rec_json['rq']);//清除历史容器信息
						}
					}
					$avarr      = array_diff(explode(',',$cy_rec_qckb_info['assay_values']),$xc_exam_arr);
					while($rq_rs=$DB->fetch_assoc($rq_query)){
						$rq_value=explode(',',$rq_rs['vid']);
						if(array_intersect($avarr,$rq_value)){
							$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
						}
					}
					$cy_rec_qckb_info['json'] = JSON($tmp_rec_json);
					//采样容器存储结束
					if(empty($_POST['cyd_id']) || empty($cy_rec_qckb[$cy_rec_info['sid']]['id'])){
						$qckb_cid	= new_record( 'cy_rec', $cy_rec_qckb_info);//不能使用$cid,不然会和现场平行冲突
					}else{
						$qckb_cid	= $cy_rec_qckb[$cy_rec_info['sid']]['id'];//不能使用$cid,不然会和现场平行冲突
						$cy_rec_qckb_info['bar_code']= $cy_rec_qckb[$cy_rec_info['sid']]['bar_code'];
						$update_cy_rec_info		= $cy_rec_qckb_info;
						unset($update_cy_rec_info['create_date']);
						unset($update_cy_rec_info['create_man']);
						update_rec('cy_rec', $update_cy_rec_info,$qckb_cid);

					}
			}else{
				//修改采样批次时，去除修改之前选择但现在去掉的全程空白样
				if(!empty($_POST['cyd_id']) && !empty($cy_rec_qckb[$cy_rec_info['sid']]['id'])){
					$DB->query("DELETE FROM `cy_rec` where id='{$cy_rec_qckb[$cy_rec_info['sid']]['id']}'");
					//$DB->query("DELETE FROM `assay_pay` where id='{$cy_rec_qckb[$cy_rec_info['sid']]['id']}'");
					$DB->query("DELETE FROM `assay_order` where cid='{$cy_rec_qckb[$cy_rec_info['sid']]['id']}'");//原样的质控标识在后面回进行覆盖
				}
			}
			##########现场平行站点的处理
			if($cy_rec_info['zk_flag'] == 5){
				$yuan_cid	= $cid;//原样的cid
				$xcpx_bar_code	= $cy_rec_info['bar_code'];//记录现场平行原样的样品编号，后面现场项目插入order表用
				$cy_rec_info['zk_flag'] = '-6';//现场平行质控标识
				$cy_rec_info['site_name'] .= '(平行)';//现场平站点名称
				$cy_rec_info['bar_code']= new_bar_code($cy_info['site_type'],$fater_water,$cy_info['cy_date'],$fix_bar_code);//现场平行样的新样品编号
				$rs_siteArr['assay_values']     = explode(",",$rs_siteArr['assay_values']);
				if(empty($xcpx_values)){
					$cy_rec_info['assay_values']	= implode(",",array_diff($rs_siteArr['assay_values'],$xcpx_value_arr));//该现场平行样检测的项目（取差集的形式）
				}else{
					$xcpx_values_arr	= explode(",",$xcpx_values);
					$cy_rec_info['assay_values']	= implode(",",array_intersect($xcpx_values_arr,$rs_siteArr['assay_values']));//该现场平行样检测的项目(批次表里存储的)
				}
				if(empty($xcpx_milieu_values)){
					$cy_rec_info['milieu_values']	= $rs_siteArr['milieu_values'];
				}else{
					$xcpx_milieu_values_arr	= explode(',',$xcpx_milieu_values);
					$cy_rec_info['milieu_values']	= implode(",",array_intersect($xcpx_milieu_values_arr,explode(',',$rs_siteArr['milieu_values'])));//该现场平行样检测的项目(批次表里存储的)
				}
				//将默认采样容器瓶数写入采样rec表的json字段
				$rq_sql		= "SELECT * FROM `rq_value` WHERE vid!='' AND fzx_id='".$fzx_id."'  ORDER BY id";
				$rq_query	= $DB->query($rq_sql);
				$tmp_rec_json	= array();
				if(!empty($cy_rec_old_zk[$cy_rec_info['sid']]['json'])){
					$tmp_rec_json	= json_decode($cy_rec_old_zk[$cy_rec_info['sid']]['json'],true);
					if($tmp_rec_json['rq']){
						unset($tmp_rec_json['rq']);//清除历史容器信息
					}
				}
				$avarr		= array_diff(explode(',',$cy_rec_info['assay_values']),$xc_exam_arr);
				while($rq_rs=$DB->fetch_assoc($rq_query)){
					$rq_value=explode(',',$rq_rs['vid']);
					if(array_intersect($avarr,$rq_value)){
						$tmp_rec_json['rq'][$rq_rs['id']]=$rq_rs['mr_shu'];	
					}
				}
				$cy_rec_info['json'] = JSON($tmp_rec_json);
				//采样容器存储结束
				if(empty($_POST['cyd_id']) || empty($cy_rec_old_zk[$cy_rec_info['sid']]['id'])){
					$cid	= new_record( 'cy_rec', $cy_rec_info);
				}else{
					$cid	= $cy_rec_old_zk[$cy_rec_info['sid']]['id'];
					$cy_rec_info['bar_code']= $cy_rec_old_zk[$cy_rec_info['sid']]['bar_code'];
					$update_cy_rec_info		= $cy_rec_info;
					unset($update_cy_rec_info['create_date']);
					unset($update_cy_rec_info['create_man']);
					update_rec('cy_rec', $update_cy_rec_info,$cid);

				}
			}
			//修改采样批次时，去除修改之前选择但现在去掉的现场平行样
			if(!empty($_POST['cyd_id']) && $cy_rec_info['zk_flag'] == 0 && !empty($cy_rec_old_zk[$cy_rec_info['sid']]['id'])){
				$DB->query("DELETE FROM `cy_rec` where id='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");
				//$DB->query("DELETE FROM `assay_pay` where id='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");
				$DB->query("DELETE FROM `assay_order` where cid='{$cy_rec_old_zk[$cy_rec_info['sid']]['id']}'");//原样的质控标识在后面回进行覆盖
			}
			#########现场检测项目的处理:往assay_order表里插入数据
			//采样批次修改时先去掉无关的order表记录
			if(!empty($_POST['cyd_id'])){
				//去掉被取消选择的现场检测项目
				$old_xc_value	= explode(",",$cy_rs['xc_exam_value']);
				$new_xc_value	= explode(",",$cy_info['xc_exam_value']);
				$del_xc_value	= implode(",",array_diff($old_xc_value, $new_xc_value));
				if($del_xc_value!=''){
					$DB->query("DELETE FROM `assay_order` WHERE cyd_id='{$_POST['cyd_id']}' AND vid in ({$del_xc_value})");
				}
				//再去掉站点里取消选择的现场检测项目
				if($cy_rec_info['zk_flag']	== '-6'){
					$old_cid	= $cy_rec_old_zk[$cy_rec_info['sid']]['id'];
					$old_vid	= explode(",",$cy_rec_old_zk[$cy_rec_info['sid']]['assay_values']);
					$old_sites_values_str	= implode(",",array_diff($old_vid,$assay_values_arr));
					if($old_sites_values_str!=''){
						//现场平行的原样处理
						$DB->query("DELETE FROM `assay_order` WHERE cid='{$old_cid}' and vid in ({$old_sites_values_str})");
					}
				}
				$old_cid	= $cy_rec_old[$cy_rec_info['sid']]['id'];
				$old_vid	= explode(",",$cy_rec_old[$cy_rec_info['sid']]['assay_values']);
				$old_sites_values_str	= implode(",",array_diff($old_vid,$assay_values_arr));
				if($old_sites_values_str!=''){
					//现场平行的平行样处理
					$DB->query("DELETE FROM `assay_order` WHERE cid='{$old_cid}' and vid in ({$old_sites_values_str})");
				}
			}
			if(!empty($cy_info['xc_exam_value'])){
				$insert_order	= array();
				$insert_order['cyd_id']		= $cy_rec_info['cyd_id'];//采样单id
				$insert_order['cid']		= $cid;//cy_rec表id
				$insert_order['sid']		= $cy_rec_info['sid'];//站点表id
				$insert_order['water_type']	= $cy_rec_info['water_type'];//水样类型
				$insert_order['site_name']	= $cy_rec_info['site_name'];//站点名称
				$insert_order['river_name']	= $cy_rec_info['river_name'];//河流名称
				$insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
				$insert_order['assay_over']	= '0';//化验单某样品完成状态
				$insert_order['create_date']= $cy_rec_info['create_date'];//化验单创建时间
				//将所有的现场检测项目插入到assay_order表
				$assay_values	= array_intersect($assay_values_arr,$xc_exam_arr);//取出这个站点所测的现场检测项目（取交集）
				if(empty($xc_water_type[$insert_order['water_type']])){
					$xc_water_type[$insert_order['water_type']]	= array();
				}
				$xc_water_type[$insert_order['water_type']]	= array_unique(array_merge($assay_values,$xc_water_type[$insert_order['water_type']]));
				$xcpx_value_arr_rec	= explode(",",$cy_rec_info['assay_values']);//站点所测的项目
				$qckbvalues 		= explode(',',$cy_rec_qckb_info['assay_values']);//站点所测的全程空白项目
				foreach($assay_values as $vid){
					$insert_order['vid']    = $vid;//化验项目id
					if($cy_rec_info['zk_flag'] == '-6' && !in_array($vid,$xcpx_value_arr_rec)){
						$insert_order['cid']		= $yuan_cid;
						$insert_order['hy_flag']	= '0';//质控标识
						$insert_order['bar_code']	= $xcpx_bar_code;//现场平行原样的样品编号
					}else if($cy_rec_info['zk_flag'] == '-6'){
						$insert_order['hy_flag']	= '-6';//现场平行样的hy_flag
						$insert_order['bar_code']	= $cy_rec_info['bar_code'];//样品编号
						$insert_order['cid']		= $cid;//cy_rec表id
						//修改采样任务时，现场平行样的修改
						if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
							new_record('assay_order' ,$insert_order);//将现场平行样信息插入到assay_order表
						}else{
							$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
							$update_order	= $insert_order;
							unset($update_order['bar_code']);
							unset($update_order['create_date']);
							unset($update_order['cyd_id']);
							unset($update_order['vid']);
							unset($update_order['sid']);
							update_rec('assay_order', $update_order,$order_id);
						}
						$insert_order['hy_flag']	= '5';//现场平行原样的hy_flag
						$insert_order['cid']		= $yuan_cid;
						$insert_order['bar_code']	= $xcpx_bar_code;//现场平行原样的样品编号
					}else if($qckb_flag == '1'&& in_array($vid,$qckbvalues)){
						$insert_order['hy_flag']	= '1';//qckb样的hy_flag
						$insert_order['bar_code']	= $cy_rec_qckb_info['bar_code'];//样品编号
						$insert_order['cid']		= $qckb_cid;//cy_rec表id
						$insert_order['site_name']  = $cy_rec_qckb_info['site_name'];
						$insert_order['river_name']  = $cy_rec_qckb_info['river_name'];
						$insert_order['sid']  = $cy_rec_qckb_info['sid'];
						//修改采样任务时，qckb样的修改
						if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
							new_record('assay_order' ,$insert_order);//将现场平行样信息插入到assay_order表
						}else{
							$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
							$update_order	= $insert_order;
							unset($update_order['bar_code']);
							unset($update_order['create_date']);
							unset($update_order['cyd_id']);
							unset($update_order['vid']);
							unset($update_order['sid']);
							update_rec('assay_order', $update_order,$order_id);
						}
						$insert_order['site_name']  = $cy_rec_info['site_name'];
						$insert_order['river_name']  = $cy_rec_info['river_name'];
						$insert_order['sid']  = $cy_rec_info['sid'];
						$insert_order['hy_flag']	= '0';//qckb原样的hy_flag
						$insert_order['cid']		= $yuan_cid;
						$insert_order['bar_code']	= $qckb_bar_code;//qckb原样的样品编号
					}else{
						//正常的标识恢复
						$insert_order['hy_flag']	= $cy_rec_info['zk_flag'];//质控标识
					}
					//这个站点做全程空白也做平行的情况
					if($qckb_flag == '1'&& in_array($vid,$qckbvalues)&&$cy_rec_info['zk_flag'] == '-6'){
						$insert_order['hy_flag']	= '1';//qckb样的hy_flag
						$insert_order['bar_code']	= $cy_rec_qckb_info['bar_code'];//样品编号
						$insert_order['cid']		= $qckb_cid;//cy_rec表id
						$insert_order['site_name']  = $cy_rec_qckb_info['site_name'];
						$insert_order['river_name']  = $cy_rec_qckb_info['river_name'];
						$insert_order['sid']  = $cy_rec_qckb_info['sid'];
						//修改采样任务时，qckb样的修改
						if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
							new_record('assay_order' ,$insert_order);//将现场平行样信息插入到assay_order表
						}else{
							$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
							$update_order	= $insert_order;
							unset($update_order['bar_code']);
							unset($update_order['create_date']);
							unset($update_order['cyd_id']);
							unset($update_order['vid']);
							unset($update_order['sid']);
							update_rec('assay_order', $update_order,$order_id);
						}
						$insert_order['site_name']  = $cy_rec_info['site_name'];
						$insert_order['river_name']  = $cy_rec_info['river_name'];
						$insert_order['sid']  = $cy_rec_info['sid'];
						$insert_order['hy_flag']	= '5';//qckb原样的hy_flag
						$insert_order['cid']		= $yuan_cid;
						$insert_order['bar_code']	= $qckb_bar_code;//qckb原样的样品编号
					}
					//正常样的修改
					if(empty($_POST['cyd_id']) || empty($order_old[$insert_order['cid']][$vid])){
						new_record('assay_order' ,$insert_order);//将信息插入到assay_order表
					}else{
						$order_id		= $order_old[$insert_order['cid']][$vid]['id'];
						$update_order	= $insert_order;
						unset($update_order['bar_code']);
						unset($update_order['create_date']);
						unset($update_order['cyd_id']);
						unset($update_order['vid']);
						unset($update_order['sid']);
						update_rec('assay_order', $update_order,$order_id);
					}
				}
			}
		}
		
		###############查询出需要插入assay_pay表的现场检测项目的所有信息并插入到assay_pay表中
		$water_types	= implode(',',$water_type_arr);
		$xc_exam_arr	= array_intersect($all_assay_values,$xc_exam_arr);//所选现场检测项目与所有站点项目取交集，去掉站点不测却又被选中的现场检测项目
		$cy_info['xc_exam_value']	= implode(",",$xc_exam_arr);
		if(!empty($cy_info['xc_exam_value'])){
			//取出水样类型的大类和小类
			$water_type_fenlei	= array();
			$sql_leixing		= $DB->query("SELECT * FROM `leixing` WHERE (fzx_id='$fzx_id' OR `fzx_id`='0') AND act='1' AND id in($water_types)");
			while($rs_leixing = $DB->fetch_assoc($sql_leixing)){
				if($rs_leixing['parent_id']=='0'){
					$water_type_fenlei[$rs_leixing['id']]	= $rs_leixing['id'];
				}else{
					$water_type_fenlei[$rs_leixing['id']]   = $rs_leixing['parent_id'];
				}
			}
			$insert_pay	= $values_name	= array();
			$insert_pay['cyd_id']		= $cy_rec_info['cyd_id'];
			$insert_pay['fzx_id']		= $fzx_id;
			$insert_pay['create_date']	= $cy_rec_info['create_date'];
			$insert_pay['is_xcjc']		= '1';
			//取出不同水样类型下的项目名称
			$where_water_type	= implode(",",$water_type_fenlei);
			$sql_pay_value	= $DB->query("SELECT bz.vid,bz.value_C,n_set.module_value2 FROM `n_set` INNER JOIN `assay_jcbz` AS bz ON n_set.id = bz.jcbz_bh_id
									  WHERE n_set.module_name='jcbz_bh' AND n_set.module_value2 in ($where_water_type) AND n_set.module_value3 = '1' AND bz.vid IN ({$cy_info['xc_exam_value']})");
			while($rs_pay_value = $DB->fetch_assoc($sql_pay_value)){
					//格式：arr[vid][water_type]=>项目名
					if(empty($rs_pay_value['value_C'])){//如果标准里没有此项目名就到session中去找
						$rs_pay_value['value_C']	= $_SESSION['assayvalueC'][$rs_pay_value['vid']];
					}
					$values_name[$rs_pay_value['vid']][$rs_pay_value['module_value2']] = $rs_pay_value['value_C'];
			}
			$values_fangfa  = array();
			foreach($xc_water_type as $key_water_type=>$value_vids){
				foreach($value_vids as $value_vid){
					//取出方法表配置的一些信息
					//$values_fangfa	= array();
					$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id
												WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='$key_water_type' AND xmfa.xmid='$value_vid' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
					$num_pay_xmfa	= $DB->num_rows($sql_pay_xmfa);
					//如果小类中没有方法，就取大类中的方法
					if($num_pay_xmfa<1){
						$sql_pay_xmfa   = $DB->query("SELECT xmfa . * , yiqi.yq_mingcheng,yiqi.yq_sbbianhao,me.method_number FROM `xmfa` LEFT JOIN `yiqi` AS yiqi ON xmfa.yiqi = yiqi.id LEFT JOIN `assay_method` AS me ON xmfa.fangfa=me.id WHERE  xmfa.fzx_id='$fzx_id' AND xmfa.lxid='{$water_type_fenlei[$key_water_type]}' AND xmfa.xmid='$value_vid' AND xmfa.act='1' AND xmfa.mr='1' order by xmfa.xmid");
					}
					while($rs_pay_xmfa = $DB->fetch_assoc($sql_pay_xmfa)){
						$insert_pay['vid']	= $rs_pay_xmfa['xmid'];
						//如果标准中没有此项目的名称，就到session中去找
						if(!empty($values_name[$rs_pay_xmfa['xmid']][$water_type_fenlei[$rs_pay_xmfa['lxid']]])){
							$insert_pay['assay_element']	= $values_name[$rs_pay_xmfa['xmid']][$water_type_fenlei[$rs_pay_xmfa['lxid']]];
						}else{
							$insert_pay['assay_element']	= $_SESSION['assayvalueC'][$rs_pay_xmfa['xmid']];
						}
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
						$DB->query("UPDATE `assay_order` SET tid='$tid' WHERE cyd_id='{$insert_pay['cyd_id']}' AND vid='{$insert_pay['vid']}' AND (water_type='$key_water_type' OR hy_flag='1')");
					}
				}
			}
		}
		//将统计的 水样类型 插入到cy表里
		$DB->query("UPDATE `cy` SET water_type='$water_types',`xc_exam_value`='{$cy_info['xc_exam_value']}' WHERE id='$cyd_id'");
		$cyd_id_list[]  = $cyd_id;//记录cyd_id，插入监测计划表用
		$xy_i++;
	}
}
if($_POST['submit']=='修改采样任务'){
	echo "<script>location.href='$rooturl/cy/modi_csrw_tzd.php?cyd_id={$_POST[cyd_id]}';</script>";
}
if($xy_i>0){
	$tmp_cyd_id = implode(',',$cyd_id_list);//采样单id集合
    $plan_name  = date('Y年m月d日')."监测计划";
    /*$today_plan = $DB->fetch_one_assoc("SELECT * FROM `jiance_plan` WHERE `create_user`='{$u['userid']}' AND DATE(`create_date`)='".date('Y-m-d')."' ");
    if(!empty($today_plan['id'])){
        $old_cyd_id = explode(',', $today_plan['cyd_id']);
        $tmp_cyd_id = trim(implode(',',array_unique(array_merge($cyd_id_list,$old_cyd_id))),',');
        $DB->query("UPDATE `jiance_plan` SET `cyd_id`='{$tmp_cyd_id}',`status`='0' WHERE `id`='{$today_plan['id']}'");
        $plan_id    = $today_plan['id'];
    }else{*/
        $DB->query("INSERT INTO `jiance_plan` SET `cyd_id`='$tmp_cyd_id',`plan_name`='{$plan_name}',`create_user`='{$u['userid']}',`create_date`='".date('Y-m-d H:i:s')."',`status`='未下达' ");
        $plan_id    = $DB->insert_id();
    //}
    
    echo "<script>alert('{$xy_i}{$success_info}');location.href='$rooturl/xd_cyrw/jiance_plan.php?plan_id={$plan_id}';</script>";
}else{
	echo "<script>alert('未选择站点');location.href='$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_POST['site_type']}';</script>";
}
?>
