<?php
/**
 * 功能：公共类
 * 作者：Mr Zhou
 * 日期：2015-10-15
 * 描述：
 * */
class Column_setApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	public function index(){
		#code
	}
    // 
    public function get_column_set($table_key=''){
        $fzx_id = $this->fzx_id;
        empty($table_key) && ($table_key = trim($_GET['table_key']));
        
        /*$columnSet = array();
        $column = $this->get_columns($table_key, true);
        foreach($column as $key => $row){
            if(empty($row['comment'])){
                continue;
            }
            $siteColumim[$row['name']] = $row['comment'];
            $columnSet[] = array(
                'name' => $row['comment'],
                'mark' => $row['name'],
                'formType' => 'input',
                'formHtml' => '',
                'isColumn' => 'true',
                'width' => 'col-xs-4',
                'using' => 'true'
            );
        }
        // $columnSet[11]['formHtml'] = '<select name="province"></select>&nbsp;&nbsp;<select name="city"></select>&nbsp;&nbsp;<select name="district"></select>&nbsp;&nbsp;<input name="site_address" value="" />';
        die(JSON($columnSet));*/
        $sql = "SELECT * FROM `n_set` WHERE `module_name`='columnSet' AND `module_value1`='{$table_key}' AND `fzx_id`='{$fzx_id}'";
        $row = $this->_db->fetch_one_assoc($sql);
        $columnSet = empty($row['module_value2']) ?
                        '' : json_encode(json_decode($row['module_value2'], true));
        if(empty($columnSet)){
            $file_name = INC_DIR . 'column_set.' . $table_key . '.php';
            if(is_file($file_name)){
                $columnSet = include $file_name;
            }
        }
        if(IS_AJAX){
            die($columnSet);
        }
        return $columnSet;
    }
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2017-05-25
	 * 功能描述：
	*/
	public function update_column_set(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $newSet = $_GET['columnSet'];
        $table_key = trim($_GET['table_key']);
        if(empty($table_key)){
            die(json_encode(array('error'=>'0','content'=>'数据表令牌不正确！')));
        }
        $sql = "SELECT * FROM `n_set` WHERE `module_name`='columnSet'AND `module_value1`='{$table_key}' AND `fzx_id`='{$fzx_id}'";
        $columnSet = $DB->fetch_one_assoc($sql);
        $columnSetJSON = JSON($newSet);
        if(empty($columnSet)){
            $sql = "INSERT INTO `n_set` SET `module_value2`='{$columnSetJSON}', `module_name`='columnSet', `module_value1`='{$table_key}', `fzx_id`='{$fzx_id}'";
        }else{
            $sql = "UPDATE `n_set` SET `module_value2`='{$columnSetJSON}' WHERE `module_name`='columnSet' AND `module_value1`='{$table_key}' AND `fzx_id`='{$fzx_id}'";
        }
        $DB->query($sql);
        die(json_encode(array('error'=>'1','content'=>'保存成功！')));
    }
}