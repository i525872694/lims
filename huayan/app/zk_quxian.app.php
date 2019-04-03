<?php
/**
 * 功能：质控·曲线
 * 作者：Mr Zhou
 * 日期：2018-08-22
 * 描述：
 */
class Zk_quxianApp extends ZhikongApp {
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-22
     * 功能描述：
    */
    // 曲线计算
    public function index(){
        $u = $this->_u;
    	$fzx_id = FZX_ID;
        // 曲线配置内容说明
        $quxian_info = $this->get_quxian_info();
        $sc_info = json_encode($quxian_info);
        // 获取默认配置,在初次添加时使用
        $sc_conf = array(
            'id'=>'-1', 'fzx_id'=>$fzx_id, 'module_name'=>'zhikong', 'module_value1'=>'quxian', 'water_type'=>'', 'vid'=>'-1','value_C'=>''
        );
        $sc_conf['data'] = $this->get_quxian_config();
        $sc_conf = json_encode(array_merge($sc_conf, $sc_conf['data']));
        // 映射页面
        echo eval($this->get_eval_code('hyd/zhikong/quxian'));
    }
    public function quxian_list($zk_name=''){
        parent::zhikong_list('quxian');
    }
    public function quxian_save($zk_name=''){
        parent::zhikong_save('quxian');
    }
    public function zhikong_del($zk_name=''){
        parent::zhikong_del('quxian');
    }
    // 获取曲线模板列表
    public function get_sc_muban(){
        global $DB;
        $fzx_id = $this->fzx_id;
        $sql = "SELECT * FROM `n_set` WHERE `module_name`='quxian_muban' AND `fzx_id`='{$fzx_id}'";
        $row = $DB->fetch_one_assoc($sql);
        if(!empty($row)){
            $muban_list = json_decode($row['module_value1'], true);
        }else{
            $muban_list = [
                'sc_001'=> '表格一',
                'sc_002'=> '表格二(适用总氮[两条波长])',
                'sc_yq'=> '仪器曲线'
            ];
            $muban_list_json = JSON($muban_list);
            $DB->query("INSERT INTO `n_set` SET `module_name`='quxian_muban', `fzx_id`='{$fzx_id}', `module_value1`='{$muban_list_json}'");
        }
        if('get_sc_muban' != AH_LIMS::$_act){
            return $muban_list;
        }else{
            echo json_encode($muban_list);
        }
    }
    // 曲线模板设置修改保存
    public function muban_set_save(){
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $muban_list_json = JSON($_GET['data']);
        $sql = "UPDATE `n_set` SET `module_value1`='{$muban_list_json}' WHERE `module_name`='quxian_muban' AND `fzx_id`='{$fzx_id}'";
        if($DB->query($sql)){
            die(json_encode(array('error'=>'0','content'=>'')));
        }else{
            die(json_encode(array('error'=>'1','content'=>"操作失败，请检查重试！")));
        }

    }
    // 
    private function get_quxian_info(){
        return array(
            // 使用液浓度值
            'round_c' => '',
            // 标液取样体积
            'round_v' => '',
            // 标准溶液含量
            'round_x' => '',
            // 吸光度
            'round_y' => '',
            // 截距（a）
            'round_a' => '',
            // 斜率（b）
            'round_b' => '',
            // 相关系数（r）
            'round_r' => '',
            // 是否截距检验
            'check_t' => ['否','是'],
            // 截距检验（t）
            'round_t' => '',
            // 因变量x取值
            'use_xy' => [
                'x' => '标液含量', 'y' => '检测中间值（如吸光度等）'
            ],
            // 曲线原始记录表格模板
            'sc_muban' => $this->get_sc_muban()
        );
    }
    // 
    public function get_quxian_config($vid=0){
        return $this->get_config('quxian', 0, $vid);
    }
}