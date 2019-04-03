<?php
/**
 * 功能：数据库表更新
 * 作者：Mr Zhou
 * 日期：2016-04-11
 * 描述：
 */
class Update_dbApp extends LIMS_Base {
	//再退回任务单时默认清空签字日期
	private  $clear_sign_date = true;
	/**
	 * 构造函数
	 */
    function __construct() {
        parent::__construct();
        global $argv,$current_fzx_id;
        $u = $this->_u;
        if( !isset($argv[1]) || 'lims' != $argv[1] ){
            die("禁止访问！\n");
        }
    }
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-04-11
	 * 参数：
	 * 返回值：
	 * 功能描述：
	*/
	public function index(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		// $this->up_hyb_info($DB);//分中心表
		// $this->up_cy($DB);//采样单记录
		// // $this->up_py($DB);//称量配药记录
		// // $this->up_bd($DB);//标准溶液标定记录
		// $this->up_hyd($DB);//化验单原始记录
        // $this->up_leixing($DB);//
		// $this->up_qx($DB);//标准曲线原始记录

		// // 检测项目配置初始化
		// $this->jcxm_dis_init();
	}
	private function up_hyb_info($DB){
		echo '<b>分中心hub_info表</b><br />';
		$columns = $this->get_columns('hub_info');
		if( !in_array('sort_name', $columns) ){
			$error_msg = '增加sort_name字段';
			$sql = "ALTER TABLE  `hub_info` ADD  `sort_name` VARCHAR( 50 ) NOT NULL COMMENT  '缩略名称' AFTER  `hub_name`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
    private function up_hyd($DB){
        color_moren('化验单assay_pay assay_order表');
		$order_columns = $this->get_columns('assay_order');
        if( !in_array('js_gongshi', $order_columns) ){
            $error_msg = '增加js_gongshi字段';
            $sql = "ALTER TABLE `assay_order` ADD `js_gongshi` TEXT NOT NULL COMMENT '计算公式' AFTER `xiang_dui_pian_cha`";
            $query = $DB->query($sql);
            $this->error_msg($query,$error_msg);
        }else{
            // $error_msg = '修改js_gongshi字段为text类型';
            // $sql = "ALTER TABLE `assay_order` CHANGE `js_gongshi` `js_gongshi` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '计算公式'";
            // $query = $DB->query($sql);
            // $this->error_msg($query,$error_msg);
            // $error_msg = '将不合法的js_gongshi字段信息清空';
            // $sql = "UPDATE `assay_order` SET `js_gongshi`='' WHERE length(js_gongshi)>=200 and right(js_gongshi, 2)!='}}'";
            // $query = $DB->query($sql);
            // $this->error_msg($query,$error_msg);
        }
        if( !in_array('zk_data', $order_columns) ){
            $error_msg = '增加zk_data字段';
            $sql = "ALTER TABLE `assay_order` ADD `zk_data` TEXT NOT NULL COMMENT '质控数据' AFTER `vd32`";
            $query = $DB->query($sql);
            $sql = "INSERT INTO `menu` (`id`, `type`, `parent_id`, `name`, `url`, `sort`, `title`, `qx`, `icon`) VALUES (NULL, '0', '33', '质控计算设置', './huayan/ahlims.php?app=zhikong target=main', '4', '质控计算设置', '', '');";
            $DB->query($sql);
            $sql = "SELECT * FROM `assay_order` WHERE RIGHT(`bar_code`, 1)='J'";
            $query=$DB->query($sql);
            while ($row=$DB->fetch_assoc($query)) {
                $zk_data = array(
                        'id' => $row['id'],
                        'action' => '40',
                        'x_y' => 1,
                        'x_j' => 1,
                        'v_y' => $row['vd28'],
                        'c_c' => $row['vd29'],
                        'c_c_unit' => $row['vd31'],
                        'v_c' => $row['vd30'],
                        'v_c_unit' => $row['vd32'],
                        'v_o' => 0,
                        'v_o_unit' => 'mL',
                        'x_v' => 1
                    );
                $zk_data = JSON($zk_data);
                $sql = "UPDATE `assay_order` SET `zk_data`='{$zk_data}' WHERE id='{$row['id']}'";
                echo "$sql<br />";
                $result  = $DB->query($sql);
            }
        }
    }
    private function up_leixing($DB){
        echo '<b>leixing表</b><br />';
        $leixing_columns = $this->get_columns('leixing');
        if( !in_array('sort', $leixing_columns) ){
            $error_msg = '增加js_gongshi字段';
            $sql = "ALTER TABLE `leixing` ADD `sort` INT NOT NULL COMMENT '递减排序' ;";
            $query = $DB->query($sql);
            $this->error_msg($query,$error_msg);
        }
    }
	private function up_qx($DB){
		echo '<b>曲线standard_curve表</b><br />';
		$qx_columns = $this->get_columns('standard_curve');
		//echo '修改曲线表状态，将未签字状态改为已开始状态，和化验单等表，状态统一，方便管理<br />';
		$error_msg = '先增加已开始状态';
		$sql = "ALTER TABLE `standard_curve` CHANGE `status` `status` ENUM( '未签字', '被退回', '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '曲线状态'";
		$this->error_msg($DB->query($sql),$error_msg);
		$error_msg = '取消被退回和未签字状态，更改为已开始';
		$sql = "UPDATE `standard_curve` SET `status` = '已开始' WHERE `status` = '被退回' OR `status` = '未签字'";
		$this->error_msg($DB->query($sql),$error_msg);
		$error_msg = '待未签字和被退回状态修改为已开始后，取消该状态';
		$sql = "ALTER TABLE `standard_curve` CHANGE `status` `status` ENUM( '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '曲线状态'";
		$this->error_msg($DB->query($sql),$error_msg);
		$error_msg = '增加第二化验员信息';
		if( !in_array('sign_012', $qx_columns) ){
			$sql = "ALTER TABLE `standard_curve`
					ADD `sign_012` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT '第二化验员' AFTER `sign_01`,
					ADD `sign_date_012` DATETIME NULL DEFAULT NULL COMMENT '第二化验员签字日期' AFTER `sign_date_01`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		$error_msg = '修改签字日期字段为datetime属性';
		$sql = "ALTER TABLE `standard_curve`
			CHANGE `sign_date_01` `sign_date_01` DATETIME NULL DEFAULT NULL COMMENT '第一化验员签字日期',
			CHANGE `sign_date_012` `sign_date_012` DATETIME NULL DEFAULT NULL COMMENT '第二化验员签字日期',
			CHANGE `sign_date_02` `sign_date_02` DATETIME NULL DEFAULT NULL COMMENT '校核日期',
			CHANGE `sign_date_03` `sign_date_03` DATETIME NULL DEFAULT NULL COMMENT '复核日期',
			CHANGE `sign_date_04` `sign_date_04` DATETIME NULL DEFAULT NULL COMMENT '审核日期'";
		$this->error_msg($DB->query($sql),$error_msg);
	}
	private function up_cy($DB){
		echo '<b>采样单cy表</b><br />';
		echo '<b>采样cy表</b><br />';
		$columns = $this->get_columns('cy');
		if( !in_array('xmfb', $columns) ){
			$error_msg = '增加xmfb字段';
			$sql = "ALTER TABLE  `cy` ADD  `xmfb` TEXT NOT NULL COMMENT  '项目分包' AFTER  `xc_exam_value`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
	private function up_py($DB){
		echo '<b>称量配药记录jzry表</b><br />';
	}
	private function up_bd($DB){
		echo '<b>标准溶液标定记录jzry_bd表</b><br />';
		$error_msg = '正在使用和正在标定状态修改为已开始';
		$bd_columns = $this->get_columns('jzry_bd');
		$sql = "UPDATE `jzry_bd` SET `status`='已开始' WHERE `status` IN ('正在使用','正在标定')";
		$this->error_msg($DB->query($sql),$error_msg);
		if(!in_array('uid',$bd_columns)){
			$error_msg = '增加uid和uid2字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `uid` INT NOT NULL COMMENT '标定人员1' AFTER `vid` ,ADD `uid2` INT NOT NULL COMMENT '标定人员2' AFTER `uid`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		$error_msg = '修改标定表status字段属性';
		$sql = "ALTER TABLE `jzry_bd` CHANGE `status` `status` ENUM( '已开始', '已完成', '已校核', '已复核', '已审核', '已停用' ) NOT NULL DEFAULT '已开始' COMMENT '标定状态'";
		$this->error_msg($DB->query($sql),$error_msg);
		if(!in_array('bzry_nddw',$bd_columns)){
			$error_msg = '增加标准溶液浓度单位字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `bzry_nddw` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT '标准溶液浓度单位' AFTER `bzry_pznd`";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if(!in_array('assay_data',$bd_columns)){
			$error_msg = '增加化验数据assay_data字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `assay_data` TEXT NOT NULL COMMENT '化验数据' AFTER `mol_m` ";
			$this->error_msg($DB->query($sql),$error_msg);
		}
		if(!in_array('json',$bd_columns)){
			$error_msg = '增加标准溶液浓度单位字段';
			$sql = "ALTER TABLE `jzry_bd` ADD `json` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$this->error_msg($DB->query($sql),$error_msg);
		}
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2016-01-11
	 * 功能描述：根据历史数据初始化化验员所分配到的检测项目
	*/
	public function jcxm_dis_init(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$users	= array();
		$query	= $DB->query("SELECT * FROM `xmfa` WHERE 1");
		while($row=$DB->fetch_assoc($query)){
			//主测
			if(intval($row['userid']) && !isset($users[$row['userid']])){
				$users[$row['userid']]=array();
			}
			if(intval($row['userid']) && !in_array($row['xmid'],$users[$row['userid']])){
				$users[$row['userid']][]=$row['xmid'];
			}
			//辅测
			if(intval($row['userid2']) && !isset($users[$row['userid2']])){
				$users[$row['userid2']]=array();
			}
			if(intval($row['userid2']) && !in_array($row['xmid'],$users[$row['userid2']])){
				$users[$row['userid2']][]=$row['xmid'];
			}
		}
		foreach($users AS $uid => $vidArr){
			$vidStr	= implode(',', $vidArr);
			$sql = "UPDATE `user_other` SET `v4`='{$vidStr}' WHERE `uid`='{$uid}'";
			$query = $DB->query($sql);
			$affected_rows = $DB->affected_rows();
			$error_msg = "修改（{$uid}）的数据，#影响了{$affected_rows}行";
			$this->error_msg($query,$error_msg);
		}
	}
	public function error_msg($status,$error_msg,$msg_only_error=false,$die=false){
		if( !$msg_only_error && $status ){
            color_green('success:');
		}else{
            color_red('error:');
		}
        color_moren($error_msg);
        if( $die ){
            die;
        }
	}
}