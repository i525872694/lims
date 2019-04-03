<?php
/**
 * 功能：质控·加标
 * 作者：Mr Zhou
 * 日期：2018-08-09
 * 描述：
 */
class Zk_jiabiaoApp extends ZhikongApp {
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-08-09
     * 功能描述：
    */
    // 加标计算
    public function index(){
        // 加标配置内容说明
        $jiabiao_info = $this->get_jiabiao_info();
        $jb_info = json_encode($jiabiao_info);
        // 获取默认配置,在初次添加时使用
        $jb_conf = array(
            'id'=>'-1', 'fzx_id'=>$fzx_id, 'module_name'=>'zhikong', 'module_value1'=>'jiabiao', 'water_type'=>'', 'vid'=>'-1','value_C'=>''
        );
        $jb_conf['data'] = $this->get_jiabiao_config();
        $jb_conf = json_encode(array_merge($jb_conf, $jb_conf['data']));
        // 映射页面
        echo eval($this->get_eval_code('hyd/zhikong/jiabiao'));
    }
    public function jiabiao_list($zk_name=''){
        parent::zhikong_list('jiabiao');
    }
    public function jiabiao_save($zk_name=''){
        parent::zhikong_save('jiabiao');
    }
    public function zhikong_del($zk_name=''){
        parent::zhikong_del('jiabiao');
    }
    // 
    private function get_jiabiao_info(){
        return array(
            // 计算公式
            'formula' => [
                            'P% = [m₂-m₁]/m₀×100%',
                            'P% = (C₂-C₁)/((C₀*V₀)/V₂)×100%'
                        ],
            // 加标量小于X%时考虑体积,≥0，全考虑体积时设置为0即可
            'allow_cv' => 0,
            // 原水样是否考虑其他体积
            'yv_allow_ov' => [
                            '0' => '否',
                            '1' => '是'
                        ],
            // 做平行时使用何值计算
            'use_pingxing' => [
                            'avg' =>'使用平均值计算',
                            'data' => '使用原水样检测结果'
                        ],
            // 检测值使用何值计算
            'use_data' => [
                            '_vd0' => '原始结果',
                            'vd0' =>'修约后的结果'
                        ],
            // 理论浓度使用何值计算
            'use_li' => [
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
            // 理论浓度比检测结果多保留X位小数
            'li_add_blws' => [0, 1, 2, 3],
            // 是否显示理论浓度
            'show_li' => [
                                '0' => '不显示',
                                '1' => '显示'
                            ],
            // 是否需要体积系数
            'need_x_v' => [
                                '0' => '不需要',
                                '1' => '需要'
                            ],
            // 质量换算系数，需大于0
            'xishu_m' => 1,
            // 加标回收率保留位数设置
            'jbhsl_blws' => [
                            // <1，<10, <100, ≥100
                        ]
        );
    }
    // 
    public function get_jiabiao_config($water_type=0, $vid=0){
        return $this->get_config('jiabiao', $water_type, $vid);
    }
}