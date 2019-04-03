<?php
/**
 * 功能：质控·范围
 * 作者：Mr Zhou
 * 日期：2018-09-18
 * 描述：
 */
class Zk_fanweiApp extends ZhikongApp {
    private $canModi = false;
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global;
        $zk_set_qx = isset($global['zk']['zk_set_qx']) ? $global['zk']['zk_set_qx'] : 'is_zz';
        $this->canModi = ($u[$zk_set_qx] || $u['admin']) ? true : false;
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-09-18
     * 功能描述：
    */
    // 质控范围设置
    public function index(){
        // 映射页面
        echo eval($this->get_eval_code('hyd/zhikong/fanwei'));
    }
    public function fanwei_list($zk_name=''){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $leixing = [];
        $sql_vid = $sql_water_type = '';
        if('全部' != trim($_GET['vid'])){
            $vid = intval($_GET['vid']);
            $sql_vid = "AND `vid`='{$vid}'";
        }
        if('全部' != trim($_GET['water_type'])){
            $water_type = intval($_GET['water_type']);
            $sql_water_type = "AND `water_type`='{$water_type}'";
        }else{
            $leixing = $this->get_all_leixing();
        }

        $sql = "SELECT `z`.*,`av`.`value_C` 
            FROM `zk_set` AS `z` LEFT JOIN `assay_value` `av` ON `av`.`id`=`z`.`vid` 
            WHERE 1  {$sql_vid} {$sql_water_type}
            ORDER BY CONVERT( `value_C` USING gbk ) ASC, `z`.`water_type` ASC, `z`.`id` ASC";
        $query = $DB->query($sql);
        $rows = [];
        $assay_value_keys = array_keys($_SESSION['assayvalueC']);
        while ($row = $DB->fetch_assoc($query)) {
            if($row['vid'] && !in_array($row['vid'], $assay_value_keys)){
                continue;
            }
            if(!empty($leixing)){
                $row['lname'] = $leixing[$row['water_type']];
            }
            $rows[] = $row;
        }
        echo json_encode($rows);
    }
    // 保存
    public function fanwei_save($zk_name=''){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        $vid = intval($_GET['data']['vid']);
        $water_type = intval($_GET['data']['water_type']);
        $nd			= trim($_GET['data']['nd']);
        $jbhs		= trim($_GET['data']['jbhs']);
        $sn_jmd		= trim($_GET['data']['sn_jmd']);
        $sj_jmd		= trim($_GET['data']['sj_jmd']);
        $sn_xdwc	= trim($_GET['data']['sn_xdwc']);
        $sj_xdwc	= trim($_GET['data']['sj_xdwc']);
        $add_item	= intval($_GET['data']['add_item']);
        if(intval($_GET['id'])){
            // 查询要修改的数据是否存在
            $sql = "SELECT * FROM `zk_set` WHERE `id`='{$id}'";
            $row = $DB->fetch_one_assoc($sql);
            if(empty($row)){
                die(json_encode(array('error'=>'1','content'=>'您修改的数据不存在!')));
            }
            $zk_set['vid'] = $_SESSION['assayvalueC'][$vid];
            // 更新数据
            $up_sql = "UPDATE `zk_set` SET 
                `water_type`='{$water_type}', `vid`='{$vid}', `nd`='{$nd}', `sn_jmd`='{$sn_jmd}', `sj_jmd`='{$sj_jmd}', `jbhs`='{$jbhs}', `sn_xdwc`='{$sn_xdwc}', `sj_xdwc`='{$sj_xdwc}'
                WHERE `id`='{$id}'";
            $DB->query($up_sql);
            if($DB->affected_rows()){
                die(json_encode(array('error'=>'0','content'=>'修改成功。','id'=>$id)));
            }else{
                die(json_encode(array('error'=>'1','content'=>'数据未修改。')));
            }
        }else{
            $up_sql = "INSERT INTO `zk_set` SET 
                `water_type`='{$water_type}', `vid`='{$vid}', `nd`='{$nd}', `sn_jmd`='{$sn_jmd}', `sj_jmd`='{$sj_jmd}', `jbhs`='{$jbhs}', `sn_xdwc`='{$sn_xdwc}', `sj_xdwc`='{$sj_xdwc}'";
            $DB->query($up_sql);
            if($DB->affected_rows()){
                die(json_encode(array('error'=>'0','content'=>'添加成功。','id'=>$DB->insert_id())));
            }else{
                die(json_encode(array('error'=>'1','content'=>'添加失败！')));
            }
        }
    }
    // 删除
    public function zhikong_del($zk_name=''){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        if(is_array($_GET['ids'])){
            $id = implode(',', $_GET['ids']);
        }
        $sql = "DELETE FROM `zk_set` WHERE `id` IN({$id})";
        if($DB->query($sql)){
            die(json_encode(array('error'=>'0','content'=>'删除成功。')));
        }else{
            die(json_encode(array('error'=>'1','content'=>'删除失败。')));
        }
    }
    // 
    public function fanwei_init(){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $default_config = array(
            array('nd'=>'0～0.0001',		'sn_jmd'=>'≤50',	'jbhs'=>'70～130'),
            array('nd'=>'0.0001～0.01',	'sn_jmd'=>'≤30',	'jbhs'=>'80～120'),
            array('nd'=>'0.01～0.1',		'sn_jmd'=>'≤20',	'jbhs'=>'90～110'),
            array('nd'=>'0.1～1',		'sn_jmd'=>'≤10',	'jbhs'=>'90～110'),
            array('nd'=>'1～10',			'sn_jmd'=>'≤5',		'jbhs'=>'90～110'),
            array('nd'=>'10～100',		'sn_jmd'=>'≤2.5',	'jbhs'=>'95～105'),
            array('nd'=>'≥100',			'sn_jmd'=>'≤1',		'jbhs'=>'95～105')
        );
        $sql = "SELECT `id` FROM `zk_set` WHERE `water_type`=0 AND `vid`=0";
        if(!$DB->fetch_one_assoc($sql)){
            foreach($default_config as $row){
                $sql = "INSERT INTO `zk_set` SET  `water_type`=0, `vid`=0, `nd`='{$row['nd']}', `sn_jmd`='{$row['sn_jmd']}', `jbhs`='{$row['jbhs']}'";
                // echo $sql,'<br />';
                $DB->query($sql);
            }
        }
    }
    // 
    public function get_fanwei_config($water_type=0, $vid=0){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        // 查询设置
        $water_type_str = "0,'{$water_type}'";
        // id在7之前的都是一级水样类型默认ID
        if($water_type > 7){
            $prow = $DB->fetch_one_assoc("SELECT `parent_id` AS `pid` FROM `leixing` WHERE `id`='{$water_type}'");
            $water_type_str = "0,'{$prow['pid']}','{$water_type}'";
        }
        $config = [];
        $sql = "SELECT `z`.*,`av`.`value_C` FROM `zk_set` AS `z` LEFT JOIN `assay_value` `av` ON `av`.`id`=`z`.`vid` WHERE 1  AND `z`.`water_type` IN({$water_type_str}) AND `vid`IN('0','{$vid}') ORDER BY `av`.`id`, `z`.`water_type`";
        $query = $DB->query($sql);
        while($row = $DB->fetch_assoc($query)){
            $config[] = $row;
        }
        return $config;
    }
}