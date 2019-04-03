<?php
/**
 * 功能：质控·标样
 * 作者：Mr Zhou
 * 日期：2018-08-13
 * 描述：
 */
class Zk_biaoyangApp extends ZhikongApp {
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-13
     * 功能描述：
    */
    // 标样计算
    public function index(){
        // 标样配置内容说明
        $biaoyang_info = $this->get_biaoyang_info();
        $by_info = json_encode($biaoyang_info);
        // 获取默认配置,在初次添加时使用
        $by_conf = array(
            'id'=>'-1', 'fzx_id'=>$fzx_id, 'module_name'=>'zhikong', 'module_value1'=>'biaoyang', 'water_type'=>'', 'vid'=>'-1','value_C'=>''
        );
        $by_conf['data'] = $this->get_biaoyang_config();
        $by_conf = json_encode(array_merge($by_conf, $by_conf['data']));
        // 映射页面
        echo eval($this->get_eval_code('hyd/zhikong/biaoyang'));
    }
    // 
    public function biaoyang_list($zk_name=''){
        parent::zhikong_list('biaoyang');
    }
    public function biaoyang_save($zk_name=''){
        parent::zhikong_save('biaoyang');
    }
    public function zhikong_del($zk_name=''){
        parent::zhikong_del('biaoyang');
    }
    // 
    private function get_biaoyang_info(){
        return array(
            // 计算公式
            'formula' => [
                            'P% = (C₂-C₀)/C₀×100%'
                        ],
            // 检测值使用何值计算
            'use_data' => [
                            '_vd0' => '原始结果',
                            'vd0' =>'修约后的结果'
                        ],
            // 小于检出限时的计算方式 jcx|_vd0|0|1(检出限的一半)
            'xy_jcx' => [
                            'jcx' => '检出限',
                            '_vd0' => '原始结果',
                            '0' => '0',
                            '1' => '检出限一半'
                        ],
            // 修约方式
            'round_function' => [
                            'round' => '四舍五入',
                            '_round' => '四舍六入'
                        ],
            // 是否判断检出限
            'check_jcx' => [
                            '0' => '不判检出限',
                            '1' => '判断检出限'
                        ],
            // 检测结果多保留X位小数
            'vd0_add_blws' => [0, 1, 2, 3],
            // 相对误差保留位数设置
            'xdwc_blws' => [
                            // <1，<10, <100, ≥100
                        ],
            // 是否显示真值及不确定度
            'show_avg' => [
                            'no' => '不显示',
                            'C_c' => '标样真值',
                            'by_buquedingdu' => '不确定度',
                            'all' => '真值±不确定度'
                        ],
            // 是否计算质控结果
            'show_zkjg' => [
                            '0' => '不计算',
                            '1' => '计算'
                        ],
            // 合格判定方式
            'hege_panding' => [
                            '根据不确定度单位自动判断','绝对误差判断','相对误差判断'
                        ]
        );
    }
    // 获取配置
    public function get_biaoyang_config($water_type=0, $vid=0){
        return $this->get_config('biaoyang', $water_type, $vid);
    }
}