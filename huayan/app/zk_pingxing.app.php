<?php
/**
 * 功能：质控·平行
 * 作者：Mr Zhou
 * 日期：2018-08-08
 * 描述：
 */
class Zk_pingxingApp extends ZhikongApp {
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-08
     * 功能描述：
    */
    // 平行计算
    public function index(){
        // 平行配置内容说明
        $pingxing_info = $this->get_pingxing_info();
        $px_info = json_encode($pingxing_info);
        // 获取默认配置,在初次添加时使用
        $px_conf = array(
            'id'=>'-1', 'fzx_id'=>$fzx_id, 'module_name'=>'zhikong', 'module_value1'=>'pingxing', 'water_type'=>'', 'vid'=>'-1','value_C'=>''
        );
        $px_conf['data'] = $this->get_pingxing_config();
        $px_conf = json_encode(array_merge($px_conf, $px_conf['data']));
        // 映射页面
        echo eval($this->get_eval_code('hyd/zhikong/pingxing'));
    }
    public function pingxing_list($zk_name=''){
        parent::zhikong_list('pingxing');
    }
    public function pingxing_save($zk_name=''){
        parent::zhikong_save('pingxing');
    }
    public function zhikong_del($zk_name=''){
        parent::zhikong_del('pingxing');
    }
    // 
    private function get_pingxing_info(){
        return array(
            // 计算公式
            'formula' => [
                            'P%=(X-Avg)/Avg*100%',
                            'P%=(a-b)/Avg*100%'
                        ],
            // 检测值使用何值计算
            'use_data' => [
                            '_vd0' => '原始结果',
                            'vd0' =>'修约后的结果'
                        ],
            // 平均值使用何值计算
            'use_avg' => [
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
            'round_function' => [
                            'round' => '四舍五入',
                            '_round' => '四舍六入',
                            'ceil_round' => '只进不舍',
                            'floor_round' => '只舍不入'
                        ],
            // 检测结果多保留几位小数
            'vd0_add_blws' => [0, 1, 2, 3],
            // 平均值比检测结果多保留几位小数
            'avg_add_blws' => [0, 1, 2, 3],
            // 相对偏差保留位数设置
            'xdpc_blws' => [
                            // <1，<10, <100, ≥100
                        ],
            // 偏差为0时
            'xdpc_is_zero' => [
                            '结果为0','正常修约'
                        ],
            // 偏差处理
            'xdpc_chuli' => [
                            '保持默认','±相对偏差','取绝对值显示'
                        ],
            // 偏差显示
            'xdpc_show' => [
                            '两个样品均显示','只在平行样显示'
                        ]
        );
    }
    // 
    public function get_pingxing_config($water_type=0, $vid=0){
        return $this->get_config('pingxing', $water_type, $vid);
    }
}