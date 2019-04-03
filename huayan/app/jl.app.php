<?php
/**
 * 功能：计量认证
 * 作者：Mr Zhou
 * 日期：2018-08-25
 * 描述：
 */
class JlApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		global $global;
		parent::__construct();
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2018-08-25
	 * 功能描述：
	*/
	public function index(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $file_id = trim($_GET['file_id']);
        global $global,$trade_global,$rooturl,$current_url;
        //导航
        $trade_global['daohang']= array(
            array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),
            array('icon'=>'','html'=>'程序文件','href'=>$current_url)
        );
        $modal = $this->get_modal($file_id);
        if(empty($modal)){
            $PublicApp = new PublicApp();
            $PublicApp->reto('请求的程序文件不存在，请重新选择。', 'ahlims.php?app=jl&act=all_files_list', 'danger', 3);
            die;
        }
        $ag_grid_data = [];
        $ag_grid_data['columnDefs'] = [
            ['headerName'=> '序号', 'field'=> 'xuhao','filter'=> 'text' , 'pinned'=> 'left', 'enableRowGroup'=> false]
        ];
        $modal['grid_config'] = json_decode($modal['grid_config'], true);
        empty($modal['grid_config']['columns']) && $modal['grid_config']=[];
        empty($modal['grid_config']['columns']) && $modal['grid_config']['columns']=[];
        foreach ($modal['grid_config']['columns'] as $key => $row) {
            $ag_grid_data['columnDefs'][] = [
                'headerName'=> $row['headerName'],
                'field'=> $row['field'],
                'filter'=> 'text',
                'editable'=>true,
                'width'=>300
            ];
        }
        $ag_grid_data['columnDefs'][] = [
            'headerName'=> '修改', 'field'=> 'edit','filter'=> 'text', 'enableRowGroup'=> false, 'cellClass'=> 'green icon-edit bigger-130'
        ];
        $ag_grid_data['columnDefs'][] = [
            'headerName'=> '删除', 'field'=> 'del','filter'=> 'text', 'enableRowGroup'=> false, 'cellClass'=> 'red icon-remove bigger-140'
        ];
        $columnDefs = json_encode($ag_grid_data['columnDefs']);
        $this->disp('jlrz_modal/jlrz_list.html', get_defined_vars());
	}
    // 查询数据
    public function get_ag_grid_data(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $file_id = intval($_GET['file_id']);

        $i = '1';
        $data = [];
        $sql = "SELECT * FROM `jlrz_modal` WHERE `file_id`='{$file_id}' AND `fzx_id`='{$fzx_id}'";
        $query = $DB->query($sql);
        while($row=$DB->fetch_assoc($query)){
            $row['xuhao'] = $i++;
            $row['edit'] = '修改';
            $row['del'] = '删除';
            $data[] = $row;
        }
        if($_GET['add-rule']){
            $modal = $this->get_modal($file_id);
            $sql = "INSERT INTO `jlrz_modal` SET `fzx_id`='{$fzx_id}',`file_id`='{$file_id}',`fileName`='{$modal['name']}'";
            $DB->query($sql);
            $id=$DB->insert_id();
            // 同时向jlrz_modal_line表插入十条数据
            for ($i=0; $i < 15; $i++) {
                $DB->query("INSERT INTO `jlrz_modal_line` SET `jmid`='{$id}'");
            }
            $new_row = $DB->fetch_one_assoc("SELECT * FROM `jlrz_modal` WHERE `id`='{$id}'");
            $new_row['xuhao'] = '新数据';
            $new_row['edit'] = '修改';
            $new_row['del'] = '删除';
            array_unshift($data, $new_row);
        }
        die(json_encode($data));
    }
    // 
    public function jlrz_modal_save(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        $file_id = intval($_GET['file_id']);
        $modal = $this->get_modal($file_id);
        $grid_config = json_decode($modal['grid_config'], true);
        if(!is_array($grid_config['columns'])){
            die(json_encode(['error'=>'1','content'=>'']));
        }
        $sql_where = [];
        foreach ($grid_config['columns'] as $key => $value) {
            $sql_where[] = "`{$value['field']}`='{$_POST[$value['field']]}'";
        }
        $sql_where = implode(',', $sql_where);
        if($id){
            $sql = "UPDATE `jlrz_modal` SET {$sql_where} WHERE `id`='{$id}'";
            $DB->query($sql);
        }else{
            $sql = "INSERT INTO `jlrz_modal` SET {$sql_where}, `file_code`='{$modal['fiel_code']}', `tpl_name`='{$modal['tpl_name']}'";
            $DB->query($sql);
            $id=$DB->insert_id();
        }
        if($id){
            $_POST['id'] = $id;
            die(json_encode(['error'=>'0','content'=>'','data'=>$_POST]));
        }else{
            die(json_encode(['error'=>'1','content'=>'']));
        }
    }
    // 查看表单
    public function modal_form_view(){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        $id = intval($_GET['id']);
        $file_id = trim($_GET['file_id']);
		global $global,$trade_global,$rooturl,$current_url;

        $modal = $this->get_modal($file_id);

        $arow = $linesData = [];
        $sql = "SELECT * FROM `jlrz_modal` WHERE `id`='{$id}' AND `fzx_id`='{$fzx_id}'";
        $arow = $DB->fetch_one_assoc($sql);
        $sql = "SELECT * FROM `jlrz_modal_line` WHERE `jmid`='{$id}'";
        $query = $DB->query($sql);
        $xuhao = 0;
        while($row=$DB->fetch_assoc($query)){
            $row['xuhao'] = ++$xuhao;
            $linesData[] = $row;
        }
        // 暂时强制添加15行数据
        $n = 15-count($linesData);
        for ($i=0; $i < $n; $i++) { 
            $DB->query("INSERT INTO `jlrz_modal_line` SET `jmid`='{$id}'");
            $jmlid = $DB->insert_id();
            $row = $DB->fetch_one_assoc("SELECT * FROM `jlrz_modal_line` WHERE `id`='{$jmlid}'");
            $row['xuhao'] = ++$xuhao;
            $linesData[] = $row;
        }

        $arowData = json_encode($arow);
        $linesData = json_encode($linesData);
        $_SESSION['token_key'][$file_id][$id] = md5(uniqid(rand()));	//加密令牌
        $this->disp('jlrz_modal/modal_form.html', get_defined_vars());
    }
    // 修改
    public function form_moal_save(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = trim($_POST['id']);
        $value = trim($_POST['value']);
        $field = trim($_POST['field']);
        $table = trim($_POST['table']);
        if(!in_array($table, ['jlrz_modal', 'jlrz_modal_line'])){
            die(json_encode(['error'=>'1','content'=>'禁止非法请求。']));
        }
        $columns = $this->get_columns($_POST['table']);
        if(!in_array($_POST['field'], $columns)){
            die(json_encode(['error'=>'1','content'=>'该字段信息不可修改。']));
        }
        $sql = "UPDATE `{$table}` SET `{$field}`='{$value}' WHERE `id`='{$id}'";
        if($DB->query($sql)){
            die(json_encode(['error'=>'0','content'=>'修改成功。']));
        }else{
            die(json_encode(['error'=>'1','content'=>'修改失败。']));
        }
    }
    // 删除数据
    public function del_modal_data(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        $file_id = intval($_GET['file_id']);
        $sql = "DELETE FROM `jlrz_modal` WHERE `id`='{$id}'";
        $DB->query($sql);
        $DB->query("DELETE FROM `jlrz_modal_line` WHERE `jmid`='{$id}'");
        // die(json_encode(['error'=>'0','content'=>'']));
        $this->index();
    }
    // 目录树
    public function all_files_list(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$rootdir;
        //引入
        $trade_global['js'] = array(
            'lims/d3/d3.js',
            'lims/d3/d3.layout.js',
            'lims/d3/tree.js',
        );
        /*$index = '0'; // 计量认证
        $width = '1000';
        $height = '3000';
        $lineHeight = '200';
        if($_GET['modal_name']=='七项制度'){
            $index = '1'; // 七项制度
            $width = '800';
            $height = '500';
        $lineHeight = '150';
        }
        //数据处理
        $all_node = $this->all_files_data();
        if(count($all_node)){
            $tree = $this->treeArray($all_node,0);
            $zNodes= json_encode($tree[$index]);
            $znode_last = max(array_keys($all_node))+1;
        }*/
        $tree = array();
        $sql ="SELECT * FROM `jlrz_files` ORDER BY `id` ASC";
        $rows = $DB->query($sql);
        while($row = $DB->fetch_assoc($rows)){
            if($row['pid']==0){
                $tree[] = array(
                    "id"=>$row['id'],
                    "isroot"=>true,
                    "topic"=>$row['name']
                );
            }else{
                $tree[] = array(
                    "id"=>$row['id'],
                    "parentid"=>$row['pid'],
                    "topic"=>$row['name']
                );
            }
        }

        $data = json_encode($tree);
        $this->disp('jlrz_modal/all_files_list.html', get_defined_vars());
    }
    // 获取配置信息
    private function get_modal($file_id){
		$u		= $this->_u;
		$DB		= $this->_db;
		$fzx_id	= $this->fzx_id;
        
        $sql = "SELECT * FROM `jlrz_files` WHERE `id`='{$file_id}' LIMIT 1";
        return $DB->fetch_one_assoc($sql);
    }
    // 检查jlrz_modal表字段是否存在
    private function create_columns(){
        // 
        // ALTER TABLE `jlrz_modal` ADD `td1` `td1` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';
    }
    // 列表页字段定义保存ag-grid配置信息修改
    public function jlrz_set(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $file_id = trim($_GET['file_id']);
        global $global,$trade_global,$rooturl,$current_url;

        $modal = $this->get_modal($file_id);
        $grid_config = json_decode($modal['grid_config']);
        empty($grid_config) && $grid_config = ['columns'=>[]];
        $grid_config = json_encode($grid_config);
        $this->disp('jlrz_modal/jlrz_set.html', get_defined_vars());
    }
    // 列表页字段定义保存ag-grid配置信息保存
    public function jlrz_set_save(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url;
        // id
        $id = trim($_GET['id']);
        $modal = $this->get_modal($id);
        $modal['grid_config'] = json_decode($modal['grid_config'], true);
        if(empty($modal['grid_config']['columns'])){
            $modal['grid_config']['columns'] = [[]];
        }
        if('all' != $_GET['type']){
            $field = trim($_GET['field']);
            $value = trim($_GET['value']);
            $index = intval($_GET['index']);

            $modal['grid_config']['columns'][$index][$field] = $value;
            $modal['grid_config']['columns'][$index]['index'] = $index;
        }else{
            $modal['grid_config']['columns'] = [[]];
            foreach ($_POST['index'] as $key => $value) {
                $modal['grid_config']['columns'][$key] = [
                    'index' => $_POST['index'][$key],
                    'field' => $_POST['field'][$key],
                    'headerName' => $_POST['headerName'][$key],
                ];
            }
        }
        $grid_config_str = JSON($modal['grid_config']);
        $sql = "UPDATE `jlrz_files` SET `grid_config`='{$grid_config_str}' WHERE `id`='{$id}'";
        $DB->query($sql);
        die(json_encode(['error'=>'0','content'=>$modal['grid_config']]));
    }
    /**
     * 功能：设置json数据
     * 作者：Mr Zhou
     * 日期：2018-08-27
     * 功能描述：
    */
    private function set_json_data($arow, $key, $data){
        $json_data = empty($arow['json']) ? array() : JSON_addslashes(json_decode($arow['json'],true));
        // 赋新值
        $json_data[$key] = $data;
        $json_data = JSON($json_data);
        return $json_data;
    }
    // 获取全部程序文件目录
    function all_files_data(){
        global $DB;
        $sql ="SELECT * FROM `jlrz_files` ORDER BY `id` ASC";
        $rows = $DB->query($sql);
        $all_node = [];
        $path = __ROOTDIR__.'/template/jlrz_modal/jExcel_text/';
        while ($row = $DB->fetch_assoc($rows)){
            $file_path = $path.$row['tpl_name'];
            if($row['tpl_name'] && file_exists($file_path)){
                $row['name'] .= '*';
            }else{
                // continue;
            }
            $all_node[$row['id']]=$row;
        }
        return $all_node;
    }
    // 将数据按照所属关系封装   类似 arr2tree
    function treeArray($data, $fid){
        $result = array();
        //定义索引数组，用于记录节点在目标数组的位置，类似指针
        $p = array();

        foreach($data as $val)
        {
            if($val['pid'] == $fid)
            {
                $i = count($result);
                $result[$i] = isset($p[$val['id']])? array_merge($val,$p[$val['id']]) : $val;
                $p[$val['id']] = & $result[$i];
            } else {
                $i = count($p[$val['pid']]['children']);
                $p[$val['pid']]['children'][$i] = $val;
                $p[$val['id']] = & $p[$val['pid']]['children'][$i];
            }
        }
        return $result;
    }
    // 初始化程序文件数据
    public function jlrz_file_init(){
        die;
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$rootdir;
        $sql ="SELECT * FROM `jlrz_files` WHERE `id` not in (select pid from jlrz_files )";
        $rows = $DB->query($sql);
        while ($row = $DB->fetch_assoc($rows)){
            $tpl_name = $row['pid'].'_'.$row['id'].'_'.date('Ym').'.txt';
            echo $row['name'],'-----',$tpl_name,'<br />';
            $grid_config = [
                'columns' => [
                    ['index'=>'0','field'=>'fileName','headerName'=>'文件名'],
                    ['index'=>'1','field'=>'beizhu','headerName'=>'备注']
                ]
            ];
            $grid_config = JSON(addslashes_deep($grid_config));
            $sql = "UPDATE `jlrz_files` SET 
                    `grid_config`='{$grid_config}',
                    `tpl_name`='{$tpl_name}'
                    WHERE `id`='{$row['id']}'";
            $DB->query($sql);
        }
    }
}