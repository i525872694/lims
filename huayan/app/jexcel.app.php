<?php
/**
 * 功能：jExcel
 * 作者：Mr Zhou
 * 日期：2018-10-10
 * 描述：
 */
class jExcelApp extends LIMS_Base {
    public  $tid;
    public  $file_path;
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
        $global = $this->_global;
        //模板文件路径
        $this->file_path = $global['hyd']['plan_file_path'];
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-10-10
     * 功能描述：
    */
    public function index(){
        global $global;
        $fileName = $_GET['fileName'];
        !$fileName && ($fileName = 'fenguang');
        $filePath = __ROOTDIR__.'/template/'.$this->file_path.'/'. $fileName.'.txt';
        $initialAttributedText = file_get_contents($filePath);
        echo $this->temp('hyd/jexcel/jexcel',get_defined_vars());
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-10-10
     * 功能描述：模板文件保存
    */
    public function save_muban(){
        // 错误提示及数据检验
        $error_msg = '';
        $data_check = true;
        // 接收数据
        $data = json_decode(str_replace('\"', '"', $_POST['data']), true);
        if(empty($data) || !is_array($data)){
            $data_check = false;
            $error_msg = '提交数据为空,保存失败,请重试!';
        }else{
            // 文件名
            $fileName = $_GET['fileName'];
            $filePath = __ROOTDIR__.'/template/'.$this->file_path.'/'. $fileName.'.txt';
            if (!file_exists($filePath)) {
                $data_check = false;
                $error_msg = '模板文件不存在。';
            }else{
                // 检查数据格式
                if(!$data['data'] || count($data['data']) != count($data['cellDataArray'])){
                    $data_check = false;
                    $error_msg = '数据传输错误,保存失败,请重试!。';
                }
                if(!$data['config'] || !$data['config']['lineNum'] || !$data['config']['lineCount']){
                    $data_check = false;
                    $error_msg = '数据行为设置,请检测数据行配置!。';
                }
                if('linesData.xuhao' != $data['cellDataArray'][$data['config']['lineNum']][0]['link'][0]){
                    $data_check = false;
                    $error_msg = '数据行配置错误或者您忘记了绑定"序号"这一字段!。';
                }
                if(true === $data_check){
                    $myfile = fopen($filePath, 'w');
                    if($myfile){
                        $file_content = json_encode($data);
                        $error_code = fwrite($myfile, $file_content) ? true : false;
                        fclose($myfile);
                    }else{
                        $error_msg = '您无权限修改此文件,请联系管理员。';
                    }
                }
            }
        }
        $error_code = true === $data_check ? '0' : '1';
        die(json_encode(array('error'=>$error_code,'content'=>$error_msg)));
    }
}