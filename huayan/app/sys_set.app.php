<?php
/**
 * 功能：系统设置
 * 作者：Mr Zhou
 * 日期：2018-10-22
 * 描述：
 */
class Sys_setApp extends LIMS_Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
    // 
    public function index(){
        global $global;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        // $this->disp('sys_set', get_defined_vars());
        echo $this->temp('sys_set', get_defined_vars());
    }
    // 保存
    public function save_sys_set(){
        global $global;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $name = trim($_GET['name']);
        $value = trim($_GET['value']);
        $sql = "SELECT * FROM `n_set` WHERE `fzx_id`='{$fzx_id}' AND `module_name`='global.inc' AND `module_value4`='{$name}'";
        $row = $DB->fetch_one_assoc($sql);
        if($row['id']){
            $sql = "UPDATE `n_set` SET `module_value1`='{$value}' WHERE `id`='{$row['id']}'";
        }else{
            $sql = "INSERT INTO `n_set` SET `fzx_id`='{$fzx_id}', `module_name`='global.inc', `module_value1`='{$value}', `module_value4`='{$name}'";
        }
        if($DB->query($sql)){
            eval("\$global{$name} = '{$value}';");
            die(json_encode(['error'=>'0', 'content'=>'已保存']));
        }
        die(json_encode(['error'=>'1', 'content'=>'保存失败']));
    }
}