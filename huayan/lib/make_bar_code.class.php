<?php
/**
 * 功能：公共类
 * 作者：Mr Zhou
 * 日期：2015-10-15
 * 描述：
 * */
class MakeBarCodeAPP extends LIMS_Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        #code
    }
    // 获取新空白
    public function get_new_kongbai($sample, $add=1){
        global $global;
        if (method_exists($this, $global['hyd']['danwei'].'_kongbai')) {
            return call_user_func(array($this, $global['hyd']['danwei'].'_kongbai'), $sample, $add);
        }else{
            return $this->default_kongbai($sample, $add);
        }
    }
    // 默认空白编号的生成
    public function default_kongbai($sample, $add){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $sql    = "SELECT * FROM `assay_order` WHERE `hy_flag` = -2 AND `tid` = '{$sample['tid']}' ";
        $snkb   = $DB->query($sql);
        $rows   = $DB->num_rows($snkb) + $add;
        if($rows == 0)
        {
            return array(
                'error' => 0,
                'sid' => -1,
                'bar_code' => '空白1'
            );
        }else if($rows == 1){
            $kong   = $DB->fetch_assoc($snkb);
            if($kong['sid']==-2)
            {
                return array(
                    'error' => 0,
                    'sid' => -1,
                      'bar_code' => '空白1'
                );
            }else{
                return array(
                    'error' => 0,
                    'sid' => -2,
                    'bar_code' => '空白2'
                );
            }
        }else if($rows >= 2){
            return array(
                'error' => 1,
                'content' => '本化验单已存在两条室内空白，不可再添加！'
            );
        }
    }
    // 云南文空白编号的生成
    public function yunnan_kongbai($sample, $add){
        return $this->hb_sw_kongbai($sample, $add);
    }
    // 湖北水文空白编号的生成
    public function hb_sw_kongbai($sample, $add){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id` = {$fzx_id}");
        $sql = "SELECT `ao`.* FROM `assay_order` AS `ao` LEFT JOIN `assay_pay` AS `ay` ON `ao`.`tid`=`ay`.`id` WHERE `hy_flag` = -2 AND `fzx_id`='{$fzx_id}' AND `ay`.`vid`='{$sample['vid']}' AND LEFT(`ay`.`create_date`, 7) = LEFT('{$sample['create_date']}', 7) ORDER BY `ao`.`id` DESC";
        $snkb = $DB->query($sql);
        $any_one_kb = $DB->fetch_assoc($snkb);
        $rows   = $DB->num_rows($snkb);
        if($rows > 0){
            // 如果已经有空白则取出最后一个空白的流水号+1
            $liushui = intval(substr($any_one_kb['bar_code'], -2)) + $add;
        }else{
            // 没有添加过空白时
            $liushui =  1;
        }
        if($liushui < 10){
            $liushui = '0' . $liushui;
        }
        return array(
            'error' => 0,
            'sid' => '-' . $rows,
            'bar_code' => "KB{$hub_info['bar_code']}" . substr($sample['create_date'], 2, 2) . substr($sample['create_date'], 5, 2) . $liushui
        );
    }
    // 自控样编号的生成
    public function get_new_zikongyang($sample, $add=1){
        global $global;
        if (method_exists($this, $global['hyd']['danwei'].'_zikongyang')) {
            return call_user_func(array($this, $global['hyd']['danwei'].'_zikongyang'), $sample, $add);
        }else{
            return $this->default_zikongyang($sample, $add);
        }
    }
    // 默认自控样
    public function default_zikongyang($sample, $add){
        $zk_set = $global['zk']['zhikong'];
        !empty($_GET['barCode']) && $zk_set['zky_name'] = $_GET['barCode'];
        $bar    = (''==$zk_set['zky_name']) ? '自控样' : $zk_set['zky_name'];
        return array(
            'error' => 0,
            'sid' => '-4',
            'bar_code' => $bar
        );
    }
    // 湖北文自控样编号的生成
    public function hb_sw_zikongyang($sample, $add){
        $bar_code = $this->yunnan_zikongyang($sample, $add);
        // 湖北的自控样用字母ZK表示，不是ZKY
        $bar_code['bar_code'] = str_replace('ZKY', 'ZK', $bar_code['bar_code']);
        return $bar_code;
    }
    // 云南自控样编号的生成
    public function yunnan_zikongyang($sample, $add){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id` = {$fzx_id}");
        $sql = "SELECT `ao`.* FROM `assay_order` AS `ao` LEFT JOIN `assay_pay` AS `ay` ON `ao`.`tid`=`ay`.`id` WHERE `hy_flag` = -4 AND `fzx_id`='{$fzx_id}' AND `ay`.`vid`='{$sample['vid']}' AND LEFT(`ay`.`create_date`, 7) = LEFT('{$sample['create_date']}', 7) ORDER BY `ao`.`id` DESC";
        $query = $DB->query($sql);
        $any_one_zky = $DB->fetch_assoc($query);
        $rows   = $DB->num_rows($query);
        if($rows > 0){
            // 如果已经有则取出最后一个的流水号+1
            $liushui = intval(substr($any_one_zky['bar_code'], -2)) + $add;
        }else{
            // 没有添加过空白时
            $liushui =  1;
        }
        if($liushui < 10){
            $liushui = '0' . $liushui;
        }
        return array(
            'error' => 0,
            'sid' => '-4',
            'bar_code' => "ZKY{$hub_info['bar_code']}" . substr($sample['create_date'], 2, 2) . substr($sample['create_date'], 5, 2) . $liushui
        );
    }

}