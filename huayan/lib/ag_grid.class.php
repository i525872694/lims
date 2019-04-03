<?php
/**
 * 功能：ag-grid类
 * 作者：Mr Zhou
 * 日期：
 * 描述：
 */
class Ag_gridApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
        include_once __ROOTDIR__ . '/huayan/lib/column_set.class.php';
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2017-05-26
	 * 功能描述：
	*/
	public function index(){
		echo "Hello";
		// $this->disp('main',get_defined_vars());
	}
    // 
    public function ag_grid_list(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $sql = "SELECT * FROM `n_set` WHERE `fzx_id`='{$fzx_id}' AND `module_name`='agGridSet'";
        $query = $DB->query($sql);
        $url = __ROOTURL__ . '/huayan/ahlims.php?app=ag_grid&act=ag_grid_set';
        $table = '<table border="1" width="300">
            <tr>
                <td>
                    <a href="' . $url . '">add new</a>
                </td>
            </tr>
        ';
        while($row = $DB->fetch_assoc($query)){
            $table .= '
                <tr>
                    <td>
                        <a href="' . $url . '&key=' . $row['module_value1'] . '">' . $row['module_value1'] . '</a>
                    </td>
                </tr>
            ';
        }
        $table .= '</table>';
        echo $table;
    }
    // 
    public function ag_grid_set(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $table_key = get_str($_GET['key']);
        $set_conf = '';
        if(!$table_key){
            $table_key = 'new';
            $json_set_conf_data = '{}';
        }else{
            $json_set_conf_data = $this->get_ag_grid_set($table_key);
        }
        // 获取自定义参数
        $mainhtml = $this->temp('ag_grid/ag_grid_set',get_defined_vars());
        echo $mainhtml;
    }
    // 
    public function ag_grid_save(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $table_key = get_str($_POST['key']);
        $node_tree_map = get_str(JSON($_POST['nodes']));
        if(!trim($node_tree_map) || !$table_key){
            die("参数不全");
        }
        $num = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='agGridSet' AND `module_value1`='{$table_key}' LIMIT 1");
        $handle_sql = "`module_name`='agGridSet', `module_value1`='{$table_key}',`module_value2`='{$node_tree_map}'";
        if(!$num){
            $sql ="INSERT INTO `n_set` SET `fzx_id` = '{$fzx_id}', {$handle_sql}";
        }else{
            $id = $num['id'];
            $sql = "UPDATE `n_set` SET {$handle_sql} WHERE `id`='{$id}' limit 1";
        }
        $DB->query($sql);
        echo "ok";
    }
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2017-06-01
	 * 功能描述：获取agGrid自定义字段信息
	*/
    private function get_ag_grid_set($table_key){
        $fzx_id = $this->fzx_id;
        // 获取agGrid配置信息
        $info = $this->_db->fetch_one_assoc("SELECT * FROM `n_set` WHERE `fzx_id`='{$fzx_id}' AND `module_name`='agGridSet' AND `module_value1`='{$table_key}' LIMIT 1");
        $agGrid_conf = json_decode($info['module_value2'], true);
        // 获取数据表自定义字段
        $ColumnSet = new Column_setApp();
        $column_conf = json_decode($ColumnSet->get_column_set($table_key), true);
        // 将自定义列表的配置信息和表单自定义字段进行组合
        $zTree_name = $this->array_get_by_key($agGrid_conf, 'name');
        // $column_mark = $this->array_get_by_key($column_conf, 'mark');
        // 
        foreach($zTree_name as $key => $value){
            $name = explode('@', $value);
            if(!empty($name[0])){
                $zTree_name[$key] = $name[0];
            }
        }
        // 扩充
        if(!empty($column_conf)){
            foreach($column_conf as $key => $value){
                if($value['using'] != '1'){
                    continue;
                }
                if(!in_array($value['mark'], $zTree_name)){
                    $agGrid_conf[] = array(
                        "checked" => false,
                        'name' => $value['mark'] . '@' . $value['name']
                    );
                }
            }
        }
        return json_encode($agGrid_conf);
    }
    // 
    private function array_get_by_key(array $array, $search, $mode = 'key') {  
        $res = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key => $value) {
            if ($search === ${${"mode"}}){
                if($mode == 'key'){
                    $res[] = $value;
                }else{
                    $res[] = $key;
                }
            }
        }
        return $res;
    }
}