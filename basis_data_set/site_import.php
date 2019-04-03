<?php
//站点导入
include("../temp/config.php");
require_once "$rootdir/inc/classes/PHPExcel/IOFactory.php";
$fzx_id = FZX_ID;//分中心id
$title      = "站点信息导入";
$muban_img  = "站点导入模板截图.png";
//导航
$trade_global['daohang'][]  = array('icon'=>'','html'=>'站点数据导入','href'=>'./basis_data_set/site_import.php');
$_SESSION['daohang']['site_import']    = $trade_global['daohang'];
if(!empty($_FILES['file']['name'])){
    $xxx    = explode('.',$_FILES['file']['name']);
    $houzhui= end($xxx);
    $newname= date('ymdhis').".".end($xxx);
    $path   = "upfile/".$newname;
    $miao   = date('s');
    if($houzhui!='xls' && $houzhui!='xlsx'){
        echo "<script>alert('请选择excel格式的文件');location.href='site_import.php'</script>";
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
            ###获取站点信息，后面对比站点名称用
            $site_arr   = $sites_content    = [];
            $site_sql   = "SELECT `id`,`site_name`,`water_type`,`site_line`,`site_vertical`,`site_code` FROM `sites` WHERE `fzx_id`='{$fzx_id}'";
            $site_query = $DB->query($site_sql);
            while ($site_row = $DB->fetch_assoc($site_query)) {
                $site_arr[$site_row['water_type']][$site_row['id']] = $site_row['site_name'];
                $sites_content[$site_row['id']]    = $site_row;
            }
            ###获取水样类型信息，后面对比水样类型名称用
            $leixing_arr    = [];
            $leixing_sql    = "SELECT * FROM `leixing` WHERE `fzx_id`='0' OR `fzx_id`='{$fzx_id}'";
            $leixing_query  = $DB->query($leixing_sql);
            while ($leixing_row = $DB->fetch_assoc($leixing_query)) {
                $leixing_arr[$leixing_row['id']]    = $leixing_row['lname'];
            }
            ########将需要导入的信息初始化到数组中，数组中不包含的信息，不进行导入
            $field_name = array("站点名称"=>"site_name","站点编码"=>"site_code","垂线序号"=>"site_line","层面序号"=>"site_vertical","水样类型"=>"water_type","行政区"=>"xz_area","河流名称"=>"river_name","区域"=>"river_name","区域/河流名称"=>"river_name","经度"=>"jingdu","纬度"=>"weidu","地址"=>"site_address","街道（地址）"=>"site_address","备注"=>"note","专题名称"=>"group_name","拼音缩写"=>"quanpin_name");
            $json_field = array();//json字段
            $begin_get  = 'no';//开始获取数据的标识
            $data_arr   = $field_lie_arr = $no_save  = [];
            foreach ($sheetData as $key_hang => $value_hang) {
                if(in_array('站点名称', $value_hang) && in_array('站点编码', $value_hang)){
                    $begin_get  = 'yes';
                }
                if($begin_get == 'no'){
                    continue;
                }
                foreach ($value_hang as $key_lie => $value_lie) {
                    $value_lie = trim($value_lie);
                    if(array_key_exists($value_lie, $field_name)){
                        $field_str  = $field_name[$value_lie];//数据库字段
                        $field_lie_arr[$key_lie]= $field_str;//记录列（字母）所代表的字段
                    }else{
                        if(!empty($field_lie_arr[$key_lie])){
                            $field_str  = $field_lie_arr[$key_lie];//数据库字段
                            switch ($field_str) {
                                case 'site_name':
                                    if(empty($value_lie)){
                                        unset($data_arr[$key_hang]);
                                        continue;
                                    }
                                    //？？？判断站点名称是否重复
                                    $data_arr[$key_hang][$field_str]    = $value_lie;
                                    break;
                                case 'water_type':
                                    //？？？判断水样类型是否重复
                                    if(!in_array($value_lie,$leixing_arr)){
                                        $no_save['water_type'][]    = $key_lie.$key_hang.":".$value_lie;
                                    }else{
                                        $water_type = array_search($value_lie, $leixing_arr);
                                        $data_arr[$key_hang][$field_str]    = $water_type;
                                    }
                                    break;
                                case 'group_name':
                                    //？？？判断水样类型是否重复
                                    $data_arr[$key_hang][$field_str]    = $value_lie;
                                    break;
                                default:
                                    $data_arr[$key_hang][$field_str]    = $value_lie;
                                    break;
                            }
                        }else{
                            $no_save['field']   = "{$key_lie}{$key_hang}:{$value_lie}";//无法识别的字段
                        }
                        
                    }
                }
            }
            //print_rr($field_lie_arr);
            //print_rr($no_save);
            //print_rr($data_arr);
            if(!empty($data_arr)){
                $all_site_num   = 0;
                foreach ($data_arr as $key_hang => $value_list) {
                    $insert_sql_list = [];
                    if(array_key_exists('site_name', $value_list)){
                        foreach ($value_list as $key_field => $value) {
                            if($key_field != 'group_name'){
                                $insert_sql_list[] = "`{$key_field}`='{$value}'";
                            }
                        }
                       $insert_sql = "INSERT INTO `sites` SET `fzx_id`='{$fzx_id}',`fp_id`='{$fp_id}',`site_type`='1',".implode(',',$insert_sql_list);
                        $site_insert= $DB->query($insert_sql);
                        $site_id    = $DB->insert_id();
                        //插入site_group表数据
                        if(!empty($value_list['group_name'])){
                            $all_group[$value_list['group_name']]  = $value_list['group_name'];
                            $all_group_num  = count($all_group);
                            $group_sql  = "INSERT INTO `site_group` SET `fzx_id`='{$fzx_id}',`site_type`='1',`site_id`='{$site_id}',`group_name`='{$value_list['group_name']}',`cuser`='{admin导入}',`sort`='{$all_group_num}'";
                            $group_insert   = $DB->query($group_sql);
                        }
                        $all_site_num++;
                    }else{
                        continue;
                    }
                }
                echo "共插入{$all_site_num}个站点信息";
            }
        }

    }
}else{
    disp("basis_data_set/site_import.html");
}
?>
