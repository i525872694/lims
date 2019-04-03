<?php
/**
 * 功能：原始记录表
 * 作者：Mr Zhou
 * 日期：2015-10-29
 * 描述：
 */
class AssayApp extends LIMS_Base {
    public  $tid;
    public  $file_path;
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
        $global = $this->_global;
        include_once AH_PATH . '/assay_form_func.php';
        //模板文件路径
        $this->file_path = $global['hyd']['plan_file_path'];
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2018-09-29
     * 功能描述：
    */
    public function index(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $tid = intval($_GET['tid']);
        global $global,$trade_global,$rooturl,$current_url,$arow;
        $_SESSION['begin_url'] = $current_url;
        //########导航
        $trade_global['daohang'][]  = array('icon'=>'','html'=>'化验单'.$tid,'href'=>$current_url);
        $_SESSION['daohang']['assay_form']  = $trade_global['daohang'];
        //js/css 文件引用
        $trade_global['css']        = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
        $trade_global['js']         = array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','jquery.maskedinput.min.js');
        $trade_global['hyd_config'] = $global['hyd'];
        // 记录下本页面的导航到 session中
        $_SESSION['daohang']['ahlims']  = $trade_global['daohang'];

        // $arow = $this->get_headerData($tid);
        $arow = get_hyd_data($tid);
        $assay_form = get_assay_form($arow);
        // $assay_form = $this->temp('hyd/assay_form_hyd',get_defined_vars());
        $this->disp('hyd/assay_form', get_defined_vars());
    }
    /**
     * 功能：表头设置
     * 作者: Mr Zhou
     * 日期: 2018-10-16
     * 描述
    */
    public function bt_set(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        global $bt_muban,$global,$zong_biao,$heng_biao,$dwname,$tid,$rooturl,$rootdir;
        $tid = intval($_GET['tid']);
        $fid = intval($_GET['fid']);
        if($fid == 0 || $tid == 0){
            echo json_encode(array('error'=>'1','content','必要参数化验单号，检测方法号错误！'));die;
        }
        $sql = "SELECT `bt_muban`.*,`bt_muban`.`id` AS `hyd_bg_id`,bt.*,ap.`td1`,ap.`td2`,ap.`td3`,ap.`td4`,ap.`td5`,ap.`yq_bh`,ap.`unit`,ap.`assay_element` FROM `assay_pay` ap LEFT JOIN `bt` ON bt.`fid`=ap.`fid` LEFT JOIN `bt_muban` ON `bt_muban`.id=ap.`table_id` WHERE ap.`id` = '$tid'";
        $arow = $DB->fetch_one_assoc($sql);
        //bt表没有和xmfa表对应的数据 则在bt表新建对应数据
        if(!intval($arow['fid'])){
            $sql2 = "INSERT INTO bt (`fid`,`zongheng`) SELECT {$fid}, `zongheng` FROM `bt_muban` WHERE `id`= '{$arow['hyd_bg_id']}' ";
            if(!$DB->query($sql2)){
                echo json_encode(array('error'=>'1','notice'=>'获取表头配置信息失败，请联系管理员。','html'=>''));
                die;
            }
        }
        $hjtj_bt = $aline='';
        $zong = $heng = '';
        $$arow['zongheng'] = 'checked';
        $arow['btdata'] = json_decode($arow['btdata'],true);
        $zongheng = $arow['zongheng'].'_biao';//表格纵横板式
        $zongheng = $$zongheng;//表格纵横板式的宽度
        $arow['zhanming'] = $zhanming = '样品编号';
        // 化验单模板文件地址
        $plan_file_path = $global['hyd']['plan_file_path'];
        $filePath = "{$rootdir}/template/{$plan_file_path}/{$arow['table_name']}.txt";
        $isJexcel = false;
        if(is_file($filePath)){
            $isJexcel = true;
            $arowData = json_encode($arow);
            $hyd = $this->temp('hyd/assay_form_hyd_new', get_defined_vars());
            $$arow['zongheng'] = 'checked';
            echo $this->temp('hyd/bt_shezi', get_defined_vars());
        }else{
            // 环境表头设置
            eval('$hjtj_bt = "'.gettemplate("{$plan_file_path}hjtj_bt").'";');
            // plan模板
            eval('$plan = "'.gettemplate("{$plan_file_path}plan_{$arow['table_name']}").'";');
            // 清除里面的js代码
            $plan = preg_replace('/<script.*>(.*)<\/script>/isU', '', $plan);
            // 内容输出
            echo $this->temp('hyd/bt_shezi', get_defined_vars());
        }
    }
    /**
     * 功能：表头设置
     * 作者: Mr Zhou
     * 日期: 2018-10-16
     * 描述: 实现对化验单表头编辑的修改和保存等操作
    */
    public function bt_modify(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $sql = array();
        $tid = intval($_POST['tid']);
        $fid = intval($_POST['fid']);
        if(isset($_POST['isJexcel']) && $_POST['isJexcel']){
            // $_POST['zongheng'] = in_array($_POST['zongheng'], array('zong', 'heng')) ? $_POST['zongheng'] : 'heng';
            // `zongheng`='{$_POST['zongheng']}'
            $fileName = $_POST['fileName'];
            $fileValue = $_POST['fileValue'];
            $sql_str = "`{$fileName}`='{$fileValue}'";
            $DB->query("UPDATE `bt` SET {$sql_str} WHERE `fid`='{$fid}'");
            $columns = $this->get_columns('assay_pay');
            if(in_array($fileName, $columns) && '' !== $fileValue){
                $DB->query("UPDATE `assay_pay` SET {$sql_str} WHERE `fid`='{$fid}' AND `fzx_id`={$fzx_id} AND `id`='$tid' AND `over` IN ('未开始','已开始')");
            }
            echo json_encode(['error'=> 0]);
        }else{
            for($i = 6; $i <= 33; $i++){
                if(isset($_POST["td$i"])){
                    $td_value = $_POST["td{$i}"];
                    $sql[] = "`td{$i}`='{$td_value}'";
                    ('' !== $td_value) && ($sql2[] = "`td{$i}`='{$td_value}'");
                }
            }
            // 更新pay表的btdata字段
            if(isset($_POST['btdata'])){
                //查询pay表中的bt_data数据，并转化为数组
                $paydata = $DB->fetch_one_assoc("SELECT `btdata` FROM `assay_pay` WHERE `fid`='{$fid}' AND `fzx_id`='{$fzx_id}' AND `id`='{$tid}'");
                if(isset($paydata)){
                    $paydata = json_decode(reset($paydata),true);
                }else{
                    $paydata = [];
                }
                foreach ($_POST['btdata'] as $k => $v) {
                    if('' !== $v){
                        //若在表头设置中设置了bt_data，则替换掉pay表中对应的数据
                        $paydata[$k] = $v;
                    }
                }
                $sql[] = "`btdata`='".JSON($_POST['btdata'])."'";
                $sql2[] = "`btdata`='".JSON($paydata)."'";
            }
            if(!empty($sql)){
                $sql_str = implode(',', $sql);
                $sql_str2 = implode(',', $sql2);
                $_POST['zongheng'] = in_array($_POST['zongheng'], array('zong', 'heng')) ? $_POST['zongheng'] : 'heng';
                $DB->query("UPDATE `bt` SET {$sql_str}, `zongheng`='{$_POST['zongheng']}' WHERE `fid`='{$fid}'" );
                if('' != $sql_str2){
                    $DB->query("UPDATE `assay_pay` SET $sql_str2 WHERE `fid`='{$fid}' AND `fzx_id`={$fzx_id} AND `id`='$tid' AND `over` IN ('未开始','已开始')");
                }
            }
            header("location:ahlims.php?app=assay&tid={$tid}");
        }
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2019-02-24
     * 功能描述：化验单查看质控
    */
    public function assay_form_view_zk(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $tid = intval($_GET['hyd_id']);
        $arow = $DB->fetch_one_assoc("SELECT * FROM assay_pay WHERE id='{$tid}'");

        $sql_cy = "SELECT count(id) c_cy FROM assay_order WHERE `tid`='{$tid}' AND sid>0  and hy_flag>0";
        $data_cy = $DB->fetch_one_assoc($sql_cy);
        $sql_px = "SELECT count(id) c_px FROM assay_order WHERE `tid`='{$tid}' AND `sid`>0 AND (`hy_flag` BETWEEN 20 AND 39 OR `hy_flag` >= 60)";
        $data_px = $DB->fetch_one_assoc($sql_px);
        $sql_jb = "SELECT count(id) c_jb FROM assay_order WHERE `tid`='{$tid}' AND `sid`>0 AND `hy_flag` BETWEEN 40 AND 69 ";
        $data_jb = $DB->fetch_one_assoc($sql_jb);
        echo $this->temp('hyd/view_zk', get_defined_vars());
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2015-10-29
     * 功能描述：进行修改记录的存储
    */
    public function dataSuYuan($a='',$b='',$c='',$d='',$e=''){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $tid    = intval($_POST['id']);
        $content    = $_POST['content'];
        $arow = $DB->fetch_one_assoc("SELECT `id`, `json` FROM `assay_pay` WHERE `id`='{$tid}' AND `fzx_id`='{$fzx_id}'");
        $bzqx_Json  = json_decode($arow['json'],true);
        if(empty($bzqx_Json['退回'])){
            $xiuGaiLiYou = '';
        }else{
            $huiTuiLiYou = end($bzqx_Json['退回']);
            $xiuGaiLiYou = $huiTuiLiYou['xiuGaiLiYou'];
        }
        //在溯源文件里面删除掉js代码
        $html = preg_replace('/<script.*>(.*)<\/script>/isU','',$content);
        $html = str_replace('\\"', '"', $html);
        parent::dataSuYuan($arow['id'], $u['userid'], $html, $xiuGaiLiYou, 'tid');
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2019-03-13
     * 功能描述：加载化验单展示数据
    */ 
    public function get_hyd_popover(){
        ini_set("display_errors", '1');
        error_reporting(E_ALL & ~E_NOTICE);
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $tid = intval($_GET['tid']);
        $data = array();
        $sql = "SELECT `id`,`tid`,`js_gongshi` FROM `assay_order` WHERE `tid`='{$tid}'";
        $query = $DB->query( $sql );
        while( $row = $DB->fetch_assoc($query) ) {
            !empty($row['js_gongshi']) && $row['js_gongshi'] = json_decode($row['js_gongshi'], true);
            if(isset($row['js_gongshi']['jsgs_msg'])){
                // 反编码 URL 字符串
                $row['js_gongshi']['jsgs_msg'] = urldecode($row['js_gongshi']['jsgs_msg']);
            }
            $data[] = $row;
        }
        echo json_encode(['error'=>0, 'content'=>$data]);
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2019-03-15
     * 功能描述：样品编号排序
    */ 
    public function order_bar_code(){
        global $rooturl;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $tid  = intval($_GET['tid']);
        // 默认排序规则
        $order_str = '`order_id` ASC';
        // 指定排序规则
        if(in_array($_GET['order_type'], ['ASC', 'DESC'])){
            $len1 = intval($_GET['len1']);
            $len2 = intval($_GET['len2']);
            $order_str = "RIGHT(LEFT(`bar_code`, {$len1}), {$len2}) {$_GET['order_type']}";
        }else{
            $sql = "SELECT `module_value1` AS len1, `module_value2` AS len2 FROM `n_set` WHERE `module_name`='bar_code_length' AND `fzx_id`='{$fzx_id}'";
            $code_len = $DB->fetch_one_assoc($sql);
            // 如果没有设置数据则初始化一条
            if(empty($code_len)){
                $code_len = [
                    'len1' => 13, 'len2' => 4
                ];
                $DB->query("INSERT INTO `n_set` SET `fzx_id`='{$fzx_id}', `module_name`='bar_code_length', `module_value1`=13, `module_value2`=4");
            }
        }
        $bar_codes = array();
        $sql = "SELECT `id`, `bar_code` FROM `assay_order` WHERE `tid`='{$tid}' ORDER BY {$order_str} ,`id` ASC";
        $query = $DB->query($sql);
        while ($row = $DB->fetch_assoc($query)) {
            $bar_codes[] = array(
                'id' => $row['id'],
                'bar_code' => $row['bar_code']
            );
        }
        if(!$_GET['order_type']){
            $this->disp('hyd/order_bar_code', get_defined_vars());
        }else{
            die(json_encode(['error'=>'0', 'content'=>$bar_codes]));
        }
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2019-03-15
     * 功能描述：样品编号排序保存
    */ 
    public function order_bar_code_save(){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        if(empty($_POST['order_values'])){
            die(json_encode(['error'=>1]));
        }
        foreach($_POST['order_values'] as $id => $i){
            $DB->query("UPDATE `assay_order` SET `order_id`='{$i}' WHERE `id`='{$id}'");
        }
        // 更新样品编号长度配置信息
        $len1 = intval($_POST['len1']);
        $len2 = intval($_POST['len2']);
        $DB->query("UPDATE `n_set` SET `module_value1`='{$len1}', `module_value2`='{$len2}' WHERE `fzx_id`='{$fzx_id}' AND`module_name`='bar_code_length'");
        die(json_encode(['error'=>0]));
    }
}