<?php
// ini_set("display_errors", '1');
// error_reporting(E_ALL & ~E_NOTICE);
/**
 * 功能：质控计算设置
 * 作者：Mr Zhou
 * 日期：2018-08-03
 * 描述：
 */
class ZhikongApp extends LIMS_Base {
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-03
     * 功能描述：
    */
    public function index(){
        global $global,$trade_global,$rooturl,$current_url,$u;
        //导航
        $trade_global['daohang']= array(
            array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),
            array('icon'=>'','html'=>'质控计算设置','href'=>$current_url)
        );
        $this->disp('hyd/zhikong/zhikong_set',get_defined_vars());
    }
    // 获取项目
    protected function get_jcxm_select(){
        $values = $_SESSION['assayvalueC'];
        echo PublicApp::get_select('vid',$values,true,false);
    }
    // 数据列表信息
    public function zhikong_list($zk_name){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $leixing = [];
        $module_value3 = '';
        if('全部' != trim($_GET['water_type'])){
            $water_type = intval($_GET['water_type']);
            $module_value3 = "AND `module_value3`='{$water_type}'";
        }else{
            $leixing = $this->get_all_leixing();
        }

        $sql = "SELECT `id`, `fzx_id`, `module_name`, `module_value1`, `module_value2`, `module_value3` AS `water_type`, `module_value4` AS `vid` FROM `n_set` WHERE `module_name`='zhikong_set' AND `module_value1`='{$zk_name}' AND `fzx_id`='{$fzx_id}' {$module_value3} ORDER BY module_value3,module_value4";
        $query = $DB->query($sql);
        $rows = [];
        // 如果系统增加了新的配置项,同步更新到旧数据中
        $config_data = call_user_func(array($this, "get_{$zk_name}_config"));
        while ($row = $DB->fetch_assoc($query)) {
            $row['data'] = json_decode($row['module_value2'], true);
            unset($row['module_value2']);
            if(!empty($leixing)){
                $row['lname'] = $leixing[$row['water_type']];
            }
            $rows[] = array_merge($row, array_merge($config_data, $row['data']));
        }
        echo json_encode($rows);
    }
    // 保存
    public function zhikong_save($zk_name){
        // 
        if(!intval($_GET['id'])){
            $this->zhikong_save_add($zk_name);
        }else{
            $this->zhikong_save_edit($zk_name);
        }
    }
    // 修改
    public function zhikong_save_edit($zk_name){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        $vid = intval($_GET['data']['vid']);
        $_SESSION['zhikong'] = array();
        // 如果更改了检测项目,不能与其他项目配置冲突
        $sql = "SELECT * FROM `n_set` WHERE `module_name`='zhikong_set' AND `module_value1`='{$zk_name}' AND `module_value4`='{$vid}' AND `id`!='{$id}'";
        $row = $DB->fetch_one_assoc($sql);
        if(!empty($row)){
            die(json_encode(array('error'=>'1','content'=>'该项目的配置信息已存在,请重新选择检测项目。')));
        }
        // 查询要修改的数据是否存在
        $sql = "SELECT * FROM `n_set` WHERE `id`='{$id}' AND `module_name`='zhikong_set' AND `module_value1`='{$zk_name}'";
        $row = $DB->fetch_one_assoc($sql);
        if(empty($row)){
            die(json_encode(array('error'=>'1','content'=>'您修改的数据不存在!')));
        }
        $row['zk_set'] = json_decode($row['module_value2'], true);
        $zk_set = array_merge($row['zk_set'], $_GET['data']);
        $zk_set['value_C'] = $_SESSION['assayvalueC'][$vid];
        // 转换JSON数据
        $module_value2 = JSON($zk_set);
        // 修改时存储更新在$_SESSION中
        $water_type = intval($row['module_value3']);
        $_SESSION['zhikong'][$zk_name]['set'][$water_type][$vid] = $zk_set;
        // 更新数据
        $up_sql = "UPDATE `n_set` SET `module_value2`='{$module_value2}', `module_value4`='{$vid}' WHERE `id`='{$id}' AND `fzx_id`='{$fzx_id}'";
        $DB->query($up_sql);
        if($DB->affected_rows()){
            die(json_encode(array('error'=>'0','content'=>'修改成功。','id'=>$id)));
        }else{
            die(json_encode(array('error'=>'1','content'=>'数据未修改。')));
        }
    }
    // 添加
    public function zhikong_save_add($zk_name){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        $vid = intval($_GET['data']['vid']);
        $water_type = intval($_GET['data']['water_type']);

        // 查看该项目是否已配置,否则不能添加
        $sql = "SELECT * FROM `n_set` WHERE `module_name`='zhikong_set' AND `module_value1`='{$zk_name}' AND `module_value3`='{$water_type}' AND `module_value4`='{$vid}'";
        $arow = $DB->fetch_one_assoc($sql);
        if(!empty($arow)){
            die(json_encode(array('error'=>'1','content'=>'该项目的配置信息已存在,请重新选择检测项目。')));
        }
        // 获取默认数据进行合并补充
        $zk_set = call_user_func(array($this, 'get_config'), $zk_name);
        $zk_set = array_merge($zk_set, $_GET['data']);
        $zk_set['value_C'] = $_SESSION['assayvalueC'][$vid];
        $module_value2 = JSON($zk_set);
        $up_sql = "INSERT INTO `n_set` SET
            `fzx_id`='{$fzx_id}', `module_name`='zhikong_set', `module_value1`='{$zk_name}',
            `module_value2`='{$module_value2}',
            `module_value3`='{$water_type}', `module_value4`='{$vid}'";
        $DB->query($up_sql);
        if($DB->affected_rows()){
            die(json_encode(array('error'=>'0','content'=>'添加成功。','id'=>$DB->insert_id())));
        }else{
            die(json_encode(array('error'=>'1','content'=>'添加失败！')));
        }
    }
    // 删除
    public function zhikong_del($zk_name){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $id = intval($_GET['id']);
        if(is_array($_GET['ids'])){
            $id = implode(',', $_GET['ids']);
        }
        $sql = "DELETE FROM `n_set` WHERE `id` IN({$id}) AND `fzx_id`='{$fzx_id}' AND `module_name`='zhikong_set' AND `module_value1`='{$zk_name}'";
        if($DB->query($sql)){
            die(json_encode(array('error'=>'0','content'=>'删除成功。')));
        }else{
            die(json_encode(array('error'=>'1','content'=>'删除失败。')));
        }
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-03
     * 功能描述：获取所有水样类型
    */
    protected function get_all_leixing(){
        $DB     = $this->_db;
        $leixing= array('0'=>'默认设置');
        $query = $DB->query("SELECT `id`,`lname` FROM `leixing` WHERE `parent_id`='0' AND `act`='1'");
        while($lx = $DB->fetch_assoc($query)){
            $leixing[$lx['id']] = $lx['lname'];
            $sql_xleixing = $DB->query("SELECT `id`,`lname` FROM `leixing` WHERE `parent_id`='{$lx['id']}' AND `act`='1'");
            while($xlx = $DB->fetch_assoc($sql_xleixing)){
                $leixing[$xlx['id']] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$xlx['lname'];
            }
        }
        return $leixing;
    }
    // 获取质控配置信息
    public function get_config($zk_name, $water_type=0, $vid=0){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        // 第一次请求时存在SESSION中避免重复查询
        if(!empty($_SESSION['zhikong'][$zk_name]['set'][$water_type][$vid])){
            return $_SESSION['zhikong'][$zk_name]['set'][$water_type][$vid];
        }
        // 查询设置
        $water_type_str = "0,'{$water_type}'";
        // id在7之前的都是一级水样类型默认ID
        if($water_type > 7){
            $prow = $DB->fetch_one_assoc("SELECT `parent_id` AS `pid` FROM `leixing` WHERE `id`='{$water_type}'");
            $water_type_str = "0,'{$prow['pid']}','{$water_type}'";
        }
        $sql = "SELECT `id`, `fzx_id`, `module_name`, `module_value1`, `module_value2`, `module_value3` AS `water_type`, `module_value4` AS `vid` 
                FROM `n_set` WHERE `module_name`='zhikong_set' AND `module_value1`='{$zk_name}' AND `fzx_id`='{$fzx_id}' AND `module_value3`IN({$water_type_str}) AND `module_value4`IN('0','{$vid}') ORDER BY `module_value3` DESC, `module_value4` DESC";
        $config = $DB->fetch_one_assoc($sql);
        if(!empty($config)){
            $config['data'] = json_decode($config['module_value2'], true);
            !is_array($config['data']) && $config['data']=[];
        }
        $default_config = call_user_func(array($this, $zk_name.'_default_config'));
        $_SESSION['zhikong'][$zk_name]['set'][$water_type][$vid] = 
            is_array($config['data']) ? array_merge($default_config, $config['data']) : $default_config;
        return $_SESSION['zhikong'][$zk_name]['set'][$water_type][$vid];
    }
    // 平行相关默认设置
    protected function pingxing_default_config(){
        return  array(
            // 项目ID
            'vid' => $vid,
            // 水样类型
            'water_type' => $water_type,
            // 计算公式
            'formula' => '0',
            // 检测值使用何值计算
            'use_data' => '_vd0',
            // 平均值使用何值计算
            'use_avg' => '_vd0',
            // 小于检出限时的计算方式 jcx|_vd0|0|1(检出限的一半)
            'xy_jcx' => '_vd0',
            // 修约方式
            'round_function' => '_round',
            // 检测结果多保留几位小数
            'vd0_add_blws' => '1',
            // 平均值比检测结果多保留几位小数
            'avg_add_blws' => '1',
            // 相对偏差保留位数设置 <1，<10, <100, ≥100
            'xdpc_blws' => [ 2, 2, 1, 0],
            // 偏差为0时
            'xdpc_is_zero' => '0',
            // 偏差处理
            'xdpc_chuli' => '0',
            // 偏差显示
            'xdpc_show' => '0',
            // 质控类型 室内精密度
            'zhikong_type' => 'sn_jmd'
        );
    }
    // 加标相关默认设置
    protected function jiabiao_default_config(){
        return  array(
            // 项目ID
            'vid' => 0,
            // 水样类型
            'water_type' => 0,
            // 计算公式
            'formula' => 0,
            // 加标体积大于x%时考虑体积,≥0，全考虑体积时设置为0即可
            'allow_cv' => '0',
            // 原水样是否考虑其他体积
            'yv_allow_ov' => 0,
            // 做平行时使用何值计算
            'use_pingxing' => 'avg',
            // 检测值使用何值计算
            'use_data' => '_vd0',
            // 理论浓度使用何值计算
            'use_li' => '_vd0',
            // 小于检出限时的计算方式 jcx|_vd0|0|1(检出限的一半)
            'xy_jcx' => '0',
            // 修约方式
            'round_function' => '_round',
            // 是否判断检出限
            'check_jcx' => '0',
            // 检测结果多保留几位小数
            'vd0_add_blws' => '0',
            // 理论浓度比检测结果多保留几位小数
            'li_add_blws' => '0',
            // 是否显示理论浓度
            'show_li' => '1',
            // 是否需要体积系数
            'need_x_v' => '0',
            // 质量换算系数，需大于0
            'xishu_m' => '1',
            // 加标回收率保留位数设置 <1，<10, <100, ≥100
            'jbhsl_blws' => [ 2, 2, 1, 0],
            // 质控类型 加标回收
            'zhikong_type' => 'jbhs'
        );
    }
    // 标样自控样相关默认设置
    protected function biaoyang_default_config(){
        return  array(
            // 项目ID
            'vid' => 0,
            // 水样类型
            'water_type' => 0,
            // 计算公式
            'formula' => 0,
            // 检测值使用何值计算
            'use_data' => 'vd0',
            // 小于检出限时的计算方式 jcx|_vd0|0|1(检出限的一半)
            'xy_jcx' => '_vd0',
            // 修约方式
            'round_function' => '_round',
            // 是否判断检出限
            'check_jcx' => '0',
            // 检测结果多保留X位小数
            'vd0_add_blws' => 1,
            // 相对误差保留位数设置
            'xdwc_blws' => [2,2,1,0],
            // 是否显示真值及不确定度
            'show_avg' => 'no',
            // 是否计算质控结果
            'show_zkjg' => '1',
            // 合格判定方式
            'hege_panding' => 0,
            // 质控类型 标样
            'zhikong_type' => 'biaoyang'
        );
    }
    // 曲线相关默认设置
    protected function quxian_default_config(){
        return  array(
            // 计算公式
            'formula' => 0,
            // 稀释液浓度值
            'round_c' => ['blws'=>[[0, 2]]],
            // 标液取样体积
            'round_v' => ['blws'=>[[0, 2]]],
            // 标液使用量（浓度/质量）
            'round_x' => ['blws'=>[[0, 3]]],
            // 吸光度
            'round_y' => ['blws'=>[[0, 3]]],
            // 截距（a）
            'round_a' => ['blws'=>[[0, 4]]],
            // 斜率（b）
            'round_b' => ['blws'=>[[0, 4]]],
            // 相关系数（r）
            'round_r' => [
                'blws' => [[0, 4]],
                'max_num' => '',
                'round_function' => '_round',
            ],
            // 是否截距检验
            'check_t' => '1',
            // 截距检验（t）
            'round_t' => ['blws'=>[[0, 2]]],
            // 标准液（浓度/质量）代表值
            'use_xy' => 'x',
            // 曲线原始记录表格模板
            'sc_muban' => array_keys($this->get_sc_muban())[0]
        );
    }
    // 修约规则
    static function round_rule_config(){
        return array(
            'version' => '1.0',
            'xiuyue_jiange' => 1,
            'round_function' => '_round',
            'round_type' => 'yxws',
            'max_num' => 10,
            'blws' => [
                [0, 2]
            ]
        );
    }
}