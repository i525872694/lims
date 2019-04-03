<?php
/**
 * 功能：质控表
 * 作者：Mr Zhou
 * 日期：2018-11-11
 * 描述：
 */
class ZkbApp extends LIMS_Base {
    public  $tid;
    public  $file_path;
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-11-11
     * 功能描述：
    */
    public function index(){
        // 
    }
    // 年质量控制数据统计分析表
    public function zkb_01(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $data = [];
        for($i = $begin_year; $i < date('Y'); $i++){
            $data[] = $i;
        }
        $this->disp('zkb/zkb_01_list', get_defined_vars());
    }
    public function zkb_01_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$fzx_id}'");
        $year = $_GET['year'] ? $_GET['year'] : date('Y');
        $data = [];
        for ($i=1; $i<=12 ; $i++) { 
            $data[] = [
                'month' => $i
            ];
        }
        echo $this->temp('zkb/zkb_01_view', get_defined_vars());
    }
    // 全年质量控制情况一览表
    public function zkb_02(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $data = [];
        for($i = $begin_year; $i < date('Y'); $i++){
            $data[] = $i;
        }
        $this->disp('zkb/zkb_02_list', get_defined_vars());
    }
    public function zkb_02_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $query = $DB->query("SELECT * FROM `hub_info` WHERE `is_zz`='0'");
        $year = $_GET['year'] ? $_GET['year'] : date('Y');
        $data = [];
        while ($row=$DB->fetch_assoc($query)) {
            $data[] = [
                'year' => $year,
                'hub_name' => $row['hub_name']
            ];
        }
        echo $this->temp('zkb/zkb_02_view', get_defined_vars());
    }
    // 表15样品采集质控情况
    public function zkb_03(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $data = [];
        for($i = $begin_year; $i < date('Y'); $i++){
            $data[] = $i;
        }
        $this->disp('zkb/zkb_03_list', get_defined_vars());
    }
    public function zkb_03_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $query = $DB->query("SELECT * FROM `hub_info` WHERE `is_zz`='0'");
        $year = $_GET['year'] ? $_GET['year'] : date('Y');
        $data = [];
        while ($row=$DB->fetch_assoc($query)) {
            $data[] = [
                'year' => $year,
                'hub_name' => $row['hub_name']
            ];
        }
        echo $this->temp('zkb/zkb_03_view', get_defined_vars());
    }
    // 表16实验室常规检测质控情况
    public function zkb_04(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $data = [];
        for($i = $begin_year; $i < date('Y'); $i++){
            $data[] = $i;
        }
        $this->disp('zkb/zkb_04_list', get_defined_vars());
    }
    public function zkb_04_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $query = $DB->query("SELECT * FROM `hub_info` WHERE `is_zz`='0'");
        $year = $_GET['year'] ? $_GET['year'] : date('Y');
        $data = [];
        while ($row=$DB->fetch_assoc($query)) {
            $data[] = [
                'year' => $year,
                'hub_name' => $row['hub_name']
            ];
        }
        echo $this->temp('zkb/zkb_04_view', get_defined_vars());
    }
    // AQC精密度偏性试验结果表
    public function zkb_05(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $this->disp('zkb/zkb_05_list', get_defined_vars());
    }
    public function zkb_05_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$fzx_id}'");
        echo $this->temp('zkb/zkb_05_view', get_defined_vars());
    }
    // AQC精密度偏性试验结果表（容量法）
    public function zkb_06(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $this->disp('zkb/zkb_06_list', get_defined_vars());
    }
    public function zkb_06_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$fzx_id}'");
        echo $this->temp('zkb/zkb_06_view', get_defined_vars());
    }
    // 精密度偏性检验表
    public function zkb_07(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $this->disp('zkb/zkb_07_list', get_defined_vars());
    }
    public function zkb_07_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$fzx_id}'");
        echo $this->temp('zkb/zkb_07_view', get_defined_vars());
    }
    // 质控图
    public function zkb_08(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $this->disp('zkb/zkb_08_list', get_defined_vars());
    }
    public function zkb_08_view(){
        global $global,$trade_global,$rooturl,$current_url,$begin_year;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $hub_info = $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$fzx_id}'");
        echo $this->temp('zkb/zkb_08_view', get_defined_vars());
    }
}