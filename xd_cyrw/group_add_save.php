<?php
/**
 * 功能：项目修改页面（包括全程空白、现场平行、站点项目的修改）
 * 作者：韩枫
 * 日期：2014-08-13
 * 描述
*/
include("../temp/config.php");
$fzx_id         = $u['fzx_id'];
$jieGuo = "no";
if($_POST['action']=='group_add' || $_POST['action']=='group_modify'){
	//如果没有选站点就添加一条 默认的记录
	/*if(empty($_POST['sites'])){
		$DB->query("INSERT INTO `sites` SET fzx_id='$fzx_id',water_type='1',site_type='{$_POST['site_type']}',site_name='未添加'");
                $new_sites_id = $DB->insert_id();
                if((int)$new_sites_id>0){
			$_POST['sites'][]	= $new_sites_id;
		}
	}*/
	//往批次表里添加数据
	if($_POST['action']=='group_modify'){
		if($_POST['group_name']!=$_POST['group_name_old']){
        		$DB->query("UPDATE `site_group` SET `group_name`='{$_POST['group_name']}' WHERE fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name_old']}'");
			$jieGuo = 'yes';
        	}
        }
	//获取就批次的批次排序,新加入的站点要更新为就批次的排序
	$old_group_sort	= $DB->fetch_one_assoc("SELECT `sort` FROM `site_group` WHERE  fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name']}' AND `sort`!='' AND `sort` is not null limit 1");
	if(empty($old_group_sort['sort'])){
		$old_group_sort['sort']	= 0;	
	}
	if(!empty($_POST['sites']) && is_array($_POST['sites'])){
		$site_id_str	= implode(",",$_POST['sites']);
		$group_site_arr	= [];
		$sql_gr_value	= $DB->query("SELECT id,fzx_id,site_id,group_name,assay_values FROM `site_group` where fzx_id='$fzx_id' AND `group_name`='{$_POST['group_name']}'");
		while($rs_gr_value = $DB->fetch_assoc($sql_gr_value)){
			$group_site_arr[]	= $rs_gr_value['site_id'];
		}
		$get_sites_sql	= "SELECT * FROM `sites` WHERE `id`  in({$site_id_str}) ";
		$get_sites_query= $DB->query($get_sites_sql);
		while ($get_sites_row = $DB->fetch_assoc($get_sites_query)) {
			if(in_array($get_sites_row['id'], $group_site_arr)){
				$DB->query("UPDATE `site_group` SET `act`='1' WHERE `site_id`='{$get_sites_row['id']}' AND `group_name`='{$_POST['group_name']}'");//重新显示该站点
			}else{
				//之前该批次没有此站点，新插入此站点
				$DB->query("INSERT INTO `site_group` SET fzx_id='{$fzx_id}',site_id='{$get_sites_row['id']}',site_type='{$_POST['site_type']}',group_name='{$_POST['group_name']}',sort='{$old_group_sort['sort']}',act='1',ctime='".date('Y-m-d H:i:s')."',cuser='{$u['userid']}',assay_values='{$get_sites_row['assay_values']}'");
			}
		}
		$jieGuo = 'yes';
		//这里后期应该把相同sites的站点，去除掉，一个批次里不应有相同的sites
		$DB->query("UPDATE `site_group` SET act='0' WHERE `fzx_id`='{$fzx_id}' AND `group_name`='{$_POST['group_name']}' AND site_id not in ({$site_id_str})");
	}
}
echo json_encode(array('jieGuo'=>$jieGuo));
?>
