<?php
/**
 * 功能：监测站点信息
 * 作者：Mr Zhou
 * 日期：2017-05-25
 * 描述：
 */
class SiteApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2017-05-25
	 * 功能描述：
	*/
	public function index(){
        // code
    }
	// 站点详情
	public function site_info(){
		$fzx_id = $this->fzx_id;
		$sid = intval($_GET['sid']);
		$sql = "SELECT * FROM `sites` WHERE 1 AND `id`='{$sid}'";
		$siteInfo = $DB->fetch_one_assoc($sql);
		$siteInfoJSON = json_encode($siteInfo);
		$this->disp('site/siteInfo', get_defined_vars());
	}
	// 站点列表
	public function site_list(){
		global $rooturl;
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;


		$table_key = 'sites';
		$ag_grid_data = jiexi_ag_grid_table($table_key);

		$t = json_decode($ag_grid_data['columnDefs'],true) ;
		$t[0]['cellClass']='jumpLink';
		$ag_grid_data['columnDefs']=json_encode($t);

		$ui_ag_grid = $this->temp('ag_grid/ui_ag_grid', get_defined_vars());
		// $mainhtml = $this->temp("jiexi_demo", get_defined_vars());
		$this->disp('app_modal/demo', get_defined_vars());
	}
	// 站点列表数据
	public function site_list_data(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$site_data = array();
		$sql = "SELECT * FROM `sites` WHERE 1 LIMIT 100";
		$query = $DB->query($sql);
		while($row = $DB->fetch_assoc($query)){
			$row['caozuo'] = '';
			//<a href="javascript:;">修改</a>｜<a href="javascript:;">删除</a>';
			$site_data[] = $row;
		}
		echo json_encode($site_data);
	}
	// 站点数据保存
	public function site_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
		$sid = intval($_POST['id']);
		$sql = "SELECT * FROM `sites` WHERE `id`='{$sid}' LIMIT 1";
		$siteInfo = $DB->fetch_one_assoc($sql);
		$siteInfo['otherData'] = json_decode($siteInfo['otherData'], true);
		// 获取站点表字段配置信息
		include_once __ROOTDIR__ . '/huayan/lib/column_set.class.php';
		$Column_setApp = new Column_setApp();
		$siteColumn =  $Column_setApp->get_column_set('sites');
		$siteColumn = json_decode($siteColumn, true);
		$data = array(
			'otherData' => $siteInfo['otherData']
		);
		foreach($siteColumn as $key => $row){
			$column = $row['mark'];
			if(!isset($_POST[$column])){
				continue;
			}
			if($row['isColumn']){
				$data[$column] = trim($_POST[$column]);
			}else{
				$data['otherData'][$column] = trim($_POST[$column]);
			}
		}
		print_rr($_POST);
		print_rr($siteColumn);
		print_rr($data);
		


		$data = array();
	}
}