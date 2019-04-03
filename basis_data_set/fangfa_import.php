<?php
//检测方法导入
include("../temp/config.php");
require_once "$rootdir/inc/classes/PHPExcel/IOFactory.php";
$fzx_id = FZX_ID;//分中心id
$title      = "检测方法导入";
$muban_img  = "检测方法导入模板截图.png";
//导航
$trade_global['daohang'][]  = array('icon'=>'','html'=>'检测方法导入','href'=>'./basis_data_set/fangfa_import.php');
$_SESSION['daohang']['fangfa_import']    = $trade_global['daohang'];
if(!empty($_FILES['file']['name'])){
    $xxx    = explode('.',$_FILES['file']['name']);
    $houzhui= end($xxx);
    $newname= date('ymdhis').".".end($xxx);
    $path   = "upfile/".$newname;
    $miao   = date('s');
    if($houzhui!='xls' && $houzhui!='xlsx'){
        echo "<script>alert('请选择excel格式的文件');location.href='fangfa_import.php'</script>";
        exit;
    }
    if(file_exists(trim($_FILES['file']['tmp_name']))){//判断上传的文件是否存在
        if(move_uploaded_file(trim($_FILES['file']['tmp_name']),$path)){//把上传的文件重命名并移到系统upfile目录下
            $inputFileName = $path;
            $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
            $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            /*if(!stristr($sheetData[1]['A'],'excel数据导入模板')){//文件错误判断
                echo "<script>alert('excel模板格式不对，请参考系统下载的标准模板');location.href='site_import.php'</script>";
                exit;
            }*/
            ###获取项目信息，后面对比项目名称用
            //可以将用户经常使用简写别名的初始化到数组中，省的匹配不到麻烦
            $xm_arr     = ["浊度"=>'94',"pH值"=>'99',"游离余氯(活性氯)"=>'484',"游离余氯"=>"484","铬"=>"135","六价铬"=>"135","硝酸盐氮"=>"186","化学需氧量"=>"118","五日生化需氧量"=>"119","重碳酸根"=>"188","碳酸根"=>"189","微囊藻毒素"=>"410","叶绿素"=>"86","动、植物油"=>"110","全硅"=>"192"];
            $xm_sql     = "SELECT av.`id`,av.`value_C`,aj.`value_C` AS aj_name 
                            FROM `assay_value` AS av 
                            LEFT JOIN `assay_jcbz` AS aj 
                            ON `av`.`id`=`aj`.`vid` WHERE 1";
            $xm_query   = $DB->query($xm_sql);
            while ($xm_row = $DB->fetch_assoc($xm_query)) {
                $vid    = $xm_row['id'];
                $xm_row['value_C']  = str_replace(array("，"," ","（","）"), array(",","","(",")"), $xm_row['value_C']);
                $xm_row['aj_name']  = str_replace(array("，"," ","（","）"), array(",","","(",")"), $xm_row['aj_name']);
                if(!array_key_exists($xm_row['value_C'], $xm_arr)){
                    $xm_arr[$xm_row['value_C']] = $vid;
                }
                if(!array_key_exists($xm_row['aj_name'], $xm_arr)){
                    $xm_arr[$xm_row['aj_name']] = $vid;
                }
            }
            ###获取水样类型信息，后面对比水样类型名称用
            $leixing_arr    = [];
            $leixing_sql    = "SELECT * FROM `leixing` WHERE `fzx_id`='0' OR `fzx_id`='{$fzx_id}'";
            $leixing_query  = $DB->query($leixing_sql);
            while ($leixing_row = $DB->fetch_assoc($leixing_query)) {
                $leixing_arr[$leixing_row['id']]    = $leixing_row['lname'];
            }
            ###获取仪器名称及id信息
            $yiqi_name_list = [];
            $yiqi_sql   = $DB->query("SELECT * FROM `yiqi` WHERE `fzx_id`='{$fzx_id}'");
            while ($yiqi_row = $DB->fetch_assoc($yiqi_sql)) {
                $yiqi_name_list[$yiqi_row['id']]    = $yiqi_row['yq_mingcheng'];
            }
            ###获取检测人员及id信息
            $user_name_list = [];
            $user_sql   = $DB->query("SELECT * FROM `users` WHERE `fzx_id`='{$fzx_id}' AND `group`!='0' AND `group`!='测试组'");
            while ($user_row = $DB->fetch_assoc($user_sql)) {
              $user_name_list[$user_row['id']]  = $user_row['userid'];
            }
            ###获取检测方法及标准号信息
            $fangfa_name_list = $fangfa_number_list   = [];
            $fangfa_sql   = $DB->query("SELECT * FROM `assay_method` WHERE 1");
            while ($fangfa_row = $DB->fetch_assoc($fangfa_sql)) {
                $fangfa_number_list[$fangfa_row['id']]= str_replace(array(' ','/T'), '', strtoupper($fangfa_row['method_number']));
                //$fangfa_name_list[$fangfa_row['id']]  = $fangfa_row['method_name'];
            }
            ########将需要导入的信息初始化到数组中，数组中不包含的信息，不进行导入
            $field_name = array("水样类型"=>"lxid","项目名称"=>"xmid","方法标准号"=>"method_number","检测仪器"=>"yq_mingcheng","检出限"=>"jcx","计量单位"=>"unit","主测人员"=>"userid","辅测人员"=>"userid2");//,"方法名称"=>"method_name"
            //导入时注意，分中心id，act，mr
            $json_field = array();//json字段
            $begin_get  = 'no';//开始获取数据的标识
            $data_arr   = $field_lie_arr = $no_save  = [];
            #####循环每一行的数据
            foreach ($sheetData as $key_hang => $value_hang) {
                if(in_array('项目名称', $value_hang) && in_array('方法标准号', $value_hang)){
                    $begin_get  = 'yes';
                }
                if($begin_get == 'no'){
                    continue;
                }
                #########循环每一列的数据
                foreach ($value_hang as $key_lie => $value_lie) {
                    $value_lie = trim($value_lie);
                    if(array_key_exists($value_lie, $field_name)){
                        $field_str  = $field_name[$value_lie];//数据库字段
                        $field_lie_arr[$key_lie]= $field_str;//记录列（字母）所代表的字段
                    }else{
                        if(!empty($field_lie_arr[$key_lie])){
                            $field_str  = $field_lie_arr[$key_lie];//数据库字段
                            if(empty($value_lie)){
                                continue;
                            }
                            switch ($field_str) {
                                case 'lxid'://水样类型
                                    if(!in_array($value_lie,$leixing_arr)){
                                        $no_save['water_type'][]    = $key_lie.$key_hang.":".$value_lie;
                                    }else{
                                        $value_lie = array_search($value_lie, $leixing_arr);
                                        $data_arr[$key_hang][$field_str]    = $value_lie;
                                    }
                                    break;
                                case 'xmid'://检测项目
                                    $value_lie  = str_replace(array("，"," ","（","）"), array(",","","(",")"), $value_lie);
                                    if(!empty($xm_arr[$value_lie])){//化验项目excel表key值的获取
                                        $vid    = $xm_arr[$value_lie];//项目vid集合
                                        $data_arr[$key_hang][$field_str]    = $vid;
                                    }else{
                                        $no_save['xm'][]    = $key_lie.$key_hang.":".$value_lie;
                                    }
                                    break;
                                case 'method_number'://方法标准号
                                    $fangfa_num  = str_replace(array(' ','/T'), '', strtoupper($value_lie));
                                    if(in_array($fangfa_num, $fangfa_number_list)){
                                        $fid    = array_search($fangfa_num, $fangfa_number_list);
                                        $data_arr[$key_hang]['fangfa']    = $fid;
                                    }else{
                                        //检查完毕后，可以通过一下代码直接插入缺少的检测方法
                                        $import = 'no';
                                        if($import  == 'yes'){//插入新方法
                                            $DB->query("INSERT INTO `assay_method` SET `method_number`='$value_lie',`method_name`='{$value_hang[$key_lie]}'");
                                            $fid    = $DB->insert_id();
                                            $fangfa_number_list[$fid]   = $fangfa_num;
                                            $data_arr[$key_hang]['fangfa']    = $fid;
                                        }else{
                                            $no_save['fangfa'][]    = $key_lie.$key_hang.":".$value_lie;
                                        }
                                        
                                    }
                                    break;
                                case 'yq_mingcheng'://检测仪器
                                    if(in_array($value_lie, $yiqi_name_list)){
                                        $yiqi_id    = array_search($value_lie, $yiqi_name_list);
                                    }else{
                                        //$no_save['yiqi'][]    = $key_lie.$key_hang.":".$value_lie;
                                        //插入新仪器
                                        $DB->query("INSERT INTO `yiqi` SET `fzx_id`='{$fzx_id}',`yq_mingcheng`='$value_lie'");
                                        $yiqi_id    = $DB->insert_id();
                                        $yiqi_name_list[$yiqi_id]   = $value_lie;
                                    }
                                    $data_arr[$key_hang]['yiqi']    = $yiqi_id;
                                    break;
                                case 'userid':case 'userid2'://主测人员//辅测人员
                                    if(in_array($value_lie,$user_name_list)){
                                        $user_id    = array_search($value_lie, $user_name_list);
                                        $data_arr[$key_hang][$field_str]    = $user_id;
                                    }else{
                                        $no_save['user'][]    = $key_lie.$key_hang.":".$value_lie;
                                    }
                                    break;
                                case 'jcx'://检出限
                                    preg_match_all('/(\d+)\.?(\d*)/is', $value_lie ,$tmp_jcx_list);
                                    $value_lie  = $tmp_jcx_list[0][0];
                                    if(empty($value_lie)){
                                        $value_lie  = '-';
                                    }
                                    $data_arr[$key_hang][$field_str]    = $value_lie;
                                    break;
                                default:
                                    $data_arr[$key_hang][$field_str]    = $value_lie;
                                    break;
                            }
                        }else{
                            if(empty($no_save['field']) || array_key_exists($key_hang,$no_save['field'])){
                                $no_save['field'][$key_hang][]   = "{$key_lie}{$key_hang}:{$value_lie}";//无法识别的字段
                            }
                        }
                    }
                }
            }
            //unset($no_save['field']);//忽略的提醒
            if(!empty($no_save)){//提醒用户数据填写错误的地方，不插入数据库
                //xm、cy_date、cy_time、water_type、site_name、
                foreach ($no_save as $key_name => $value_content) {
                    switch ($key_name) {
                        case 'field':
                            $value_content  = end($value_content);
                            $tixing .= "<fieldset><legend><blink>以下<span class='stress'>表头字段</span>无法识别，请检查：</blink></legend>".implode('、', $value_content)."<br /></fieldset>";
                            break;
                        case 'xm':
                            $tixing .= "<fieldset><legend><blink>以下<span class='stress'>检测项目名称</span>无法识别，请更改成系统中的名称：</blink></legend>".implode('、', $value_content)."<br /><br /><button id='xm_button' class='btn btn-xs btn-primary'>点击查看系统检测项目名称</button></fieldset>";
                            break;
                        case 'fangfa':
                            $tixing .= "<fieldset><legend><blink>以下<span class='stress'>方法标准号</span>无法识别，请检查：</blink></legend>".implode('、', $value_content)."</fieldset>";
                            break;
                        case 'water_type':
                            $tixing .= "<fieldset><legend><blink>以下<span class='stress'>水样类型</span>无法识别，请更改成系统中的名称：</blink></legend>".implode('、', $value_content)."<br /><br /><button  id='water_button' class='btn btn-xs btn-primary'>点击查看系统水样类型名称</button></fieldset>";
                            break;
                        case 'yiqi':
                            $tixing .= "<fieldset><legend><blink>以下<span class='stress'>仪器名称</span>系统中不存在，请先初始化仪器管理信息：</blink></legend>".implode('、', $value_content)."<br /></fieldset>";
                            break;
                        default:
                            # code...
                            break;
                    }
                }
                echo disp('basis_data_set/no_import_tixing.html');exit;
            }
            /*print_rr($field_lie_arr);
            print_rr($no_save);
            print_rr($data_arr);
            exit;*/
            if(!empty($data_arr)){
                $all_insert_num   = 0;
                $fangfa_jilu    = [];
                foreach ($data_arr as $key_hang => $value_list) {
                    $insert_sql_list = [];
                    if(array_key_exists('xmid', $value_list)){
                        foreach ($value_list as $key_field => $value) {
                            $insert_sql_list[] = "`{$key_field}`='{$value}'";
                        }
                        //默认一个检测方法，不然下达采样任务等部分无法选择对应项目
                        $mr_set = '';
                        if(@!in_array($value_list['xmid'], $fangfa_jilu[$value_list['lxid']])){
                            $fangfa_jilu[$value_list['lxid']][]  = $value_list['xmid'];
                            $mr_set = " ,`mr`='1'";
                        }
                        //???判断数据库是否有相应记录。如果有就更新，没有就插入
                        $insert_sql = "INSERT INTO `xmfa` SET `fzx_id`='{$fzx_id}',`act`='1'{$mr_set},".implode(',',$insert_sql_list);
                        $site_insert= $DB->query($insert_sql);
                        $all_insert_num++;
                    }else{
                        continue;
                    }
                }
                echo "共插入{$all_insert_num}个站点信息";
            }
        }

    }
}else{
    disp("basis_data_set/site_import.html");
}
?>
