<?php
//将云南实际服务器的数据上传到云服务器中间库中，等着龙慧公司来读取采样任务数据
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
$checkLogin = false;
include("../temp/config.php");
$server = 'localhost';
$db_user= 'root';
$db_pass= '63508790';
$dbname = 'yunnan_cy_result';//和采样系统对接的中间库
$charset= 'utf-8';
if(in_array($_POST['action'], array('cyToLims','del_cy','','table'))){
    if(function_exists('mysql_connect')){
        $DB_CY = new DB_MySQL;
        $DB_CY->servername = $server;
        $DB_CY->dbname     = $dbname;
        $DB_CY->dbpassword = $db_pass;
        $DB_CY->dbusername = $db_user;
        $DB_CY->connect();
    }else{
        require_once $rootdir.'/temp/mysqli.php';
        $DB_CY = new connect_Mysql($server,$db_user,$db_pass,$dbname,$charset);
    }
}
switch ($_POST['action']) {
    case 'cyToLims'://从对接表拿最新的数据
        $fzx_id     = $_POST['fzx_id'];
        $bar_code_str   = str_replace('\\', '', $_POST['bar_code_str']);
        $status = 'no';
        if(!empty($bar_code_str)){
            $sql_where  = "";
            if($_POST['is_force'] == 'false'){
                $sql_where  = " AND `IS_IN_LIMS`='0' ";
            }
            //获取人员对接表的数据
            $duijie_user_arr = [];
            $duijie_user_sql = $DB_CY->query("SELECT `ID`,`LIMS_ID`,`USER_NAME` FROM `USER_DUIJIE` WHERE  `FZX_ID`='{$fzx_id}'");
            while ($duijie_user_row = $DB_CY->fetch_assoc($duijie_user_sql)) {
                $duijie_user_arr[$duijie_user_row['ID']]  = $duijie_user_row['USER_NAME'];
            }
            $duijie_sql = "SELECT * FROM `CY_DUIJIE` WHERE `FZX_ID`='{$fzx_id}' AND `SAMPLE_CODE` IN ({$bar_code_str}) AND `CY_FINISH_INSERT`='1' {$sql_where}";
            $duijie_query = $DB_CY->query($duijie_sql);
            $cy_arr = $duijie_id    = [];
            while ($duijie_row  = $DB_CY->fetch_assoc($duijie_query)) {
                //将签字人转换成用户名
                if(!empty($duijie_row['CY_QZ'])){
                    $CY_QZ = array();
                    $cy_qz_arr = explode(",",$duijie_row['CY_QZ']);
                    foreach($cy_qz_arr as $k=>$v){
                        $v = trim($v);
                        if(!empty($v)&&array_key_exists($v,$duijie_user_arr)){
                            $CY_QZ[] = $duijie_user_arr[$v];
                        }
                    }
                    $duijie_row['CY_QZ'] = implode(',',$CY_QZ);
                }
                if(!empty($duijie_row['SY_QZ'])&&array_key_exists($duijie_row['SY_QZ'],$duijie_user_arr)){
                    $duijie_row['SY_QZ'] = $duijie_user_arr[$duijie_row['SY_QZ']];
                }
                if(!empty($duijie_row['SH_QZ'])&&array_key_exists($duijie_row['SH_QZ'],$duijie_user_arr)){
                    $duijie_row['SH_QZ'] = $duijie_user_arr[$duijie_row['SH_QZ']];
                }
                $cy_arr[]   = $duijie_row;
                $duijie_id[]= $duijie_row['ID'];
            }
            if(!empty($duijie_id)){
                $DB_CY->query("UPDATE `CY_DUIJIE` SET `IS_IN_LIMS`='1' WHERE `ID` in (".implode(',', $duijie_id).")");//标识对接表，这些记录已同步
            }
            if(!empty($cy_arr)){
                $status = 'yes';
            }else{
                $status = 'not_result';
            }
        }
        echo json_encode(array('status'=>$status,'cy_arr'=>$cy_arr,'sql'=>$duijie_sql));
        break;
    case 'lims_insert'://将采样系统数据写入lims系统
        $cyd_id = $_POST['cyd_id'];
        $cy_arr = $_POST['cy_arr'];//对接表相关数据
        //审核人已签字的情况下 不同步数据
        $cy_info = $DB->fetch_one_assoc("select * from `cy` where `id`='$cyd_id'");
        if(!empty($cy_info['sh_user_qz'])){
            exit;
        }
        //气温AIRT、气压ATM、水位Z、流量Q、风速WNDV、风向WNDDIR、天气WTH、流速FLWV、风力WNDPWR
        $huan_jing_arr  = array('AIRT','ATM','Z','Q','WNDV','WNDDIR','WTH','FLWV','WNDPWR');//现场环境信息
        //查询出所有现场检测项目以及对接标识
        $assay_value_arr    = [];
        $assay_value_sql    = $DB->query("SELECT * FROM `assay_value` WHERE `is_xcjc`='1'");
        while ($assay_value_row = $DB->fetch_assoc($assay_value_sql)) {
            $assay_value_arr[$assay_value_row['englishMark']]  = $assay_value_row['id'];
        }
        //$bar_arr = $DB->fetch_one_assoc("SELECT group_concat(\"'\",`bar_code`,\"'\") as bar_code_str FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}' GROUP BY `cyd_id`");
        $bar_arr    = [];
        $bar_sql = $DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}'");
        while ($bar_row = $DB->fetch_assoc($bar_sql)) {
            $bar_arr[$bar_row['bar_code']]  = $bar_row['json'];
        }
        $bar_code_str   = "'".implode("','", array_keys($bar_arr))."'";
        //print_rr($bar_arr);
        $update_num = 0;
        if(!empty($cy_arr)){
            //采样系统与lims系统数据库字段对应关系
            $duijie_field_arr   = ["SAMPLE_CODE"=>"bar_code","SPT_DATE"=>"cy_date","SPT_TIME"=>"cy_time","LON"=>"jingdu","LAT"=>"weidu","TAKE_METHOD"=>"cy_way","TAKE_TOOLS"=>"cy_tool","LEFT_DISTANCE"=>"zuo_an","CENTER_DISTANCE"=>"zhong_hong","RIGHT_DISTANCE"=>"you_an","TAKE_DEEP"=>"cy_ms","WATER_WIDTH"=>"water_width","WEATHER"=>"tian_qi","AIRT"=>"qi_wen","ATM"=>"qi_ya","Z"=>"water_height","FLWV"=>"liu_l","WNDDIR"=>"feng_xiang","WNDV"=>"feng_su","POLLUTE_DESC"=>"dmwrxxjsm","SAMPLE_BOTTLE_NUMS"=>"ping","SAMPLE_PAIL_NUMS"=>"tong","SAMPLE_STATUS"=>"json","SAMPLE_COLOR"=>"json","REMARK"=>"cy_note","ELEMENT_DATA"=>"assay_order","IS_HAVE_SAMPLE"=>"status","SY_QZ"=>"sy_user_qz","SY_TIME"=>"sy_user_qz_date","SH_QZ"=>"sh_user_qz","SH_TIME"=>"sh_user_qz_date","CY_QZ"=>"CY_QZ","CY_TIME"=>"cy_user_qz_date","IMG_ID"=>"xc_img"];
            $rec_json_field = ["SAMPLE_STATUS"=>"shuiti_zhuangtai","SAMPLE_COLOR"=>"shuiti_yanse"];
            $insert_rec_arr = $insert_order_arr = [];
            $cyd_id_arr = array();
            foreach ($cy_arr as $duijie_row ) {
                $new_json_arr    = [];
                $bar_code   = $duijie_row['SAMPLE_CODE'];
                foreach ($duijie_row as $key => $value) {
                    $lims_field = $duijie_field_arr[$key];
                    switch ($lims_field) {
                        case 'jingdu':case 'weidu'://经纬度
                            $value    = str_replace(array("度","分","秒","°","′","″"), '|', $value);
                            if(stristr($value,"|")){
                                $jingdu_du  = $jingdu_fen = $jingdu_miao   = 0;
                                $tmp_jingdu = explode("|",$value);
                                if(!empty($tmp_jingdu[0])){
                                    $jingdu_du  = $tmp_jingdu[0];
                                }
                                if(!empty($tmp_jingdu[1])){
                                    $jingdu_fen = $tmp_jingdu[1];
                                }
                                if(!empty($tmp_jingdu[2])){
                                    $jingdu_miao= $tmp_jingdu[2];
                                }
                                $insert_rec_arr[$lims_field]     = ($jingdu_miao/60+$jingdu_fen)/60+$jingdu_du;
                            }else{
                                $insert_rec_arr[$lims_field] = $value;
                            }
                            break;
                        case 'json'://存储在json里面的数据
                            if(!array_search($key, $rec_json_field)){
                                $field  = $rec_json_field[$key];
                                switch ($field) {
                                    case 'shuiti_zhuangtai':
                                        $new_json_arr['shuiti']['shuiti_zhuangtai'] = $value;
                                        $insert_rec_arr['ys_zt'] = $value;
                                        break;
                                    case 'shuiti_yanse':
                                        $new_json_arr['shuiti']['shuiti_yanse'] = $value;
                                        break;
                                    default:
                                        echo "请开发人员标注，此字段更新json那个字段里";
                                        break;
                                }
                            }else{
                                echo "字段：".$key."的数据未导入";
                            }
                            break;
                        case 'status'://无水标识
                            if($value == '0'){
                                $value  = '-1';//无水
                            }else{
                                $value  = '1';//有水
                            }
                            $insert_rec_arr[$lims_field]   = $value;
                            break;
                        case 'assay_order'://现场检测项目
                            $value = str_replace('\\', '', $value);
                            $xc_result  = json_decode($value,true);
                            foreach ($xc_result as $key_mark => $value_result) {
                                $vid    = $assay_value_arr[$key_mark];
                                $vd0    = !empty($value_result['avg'])?$value_result['avg']:$value_result['data1'];
                                $vd27   =  $pingjun = '';
                                if(!empty($value_result['data1'])&&!empty($value_result['data2'])){
                                    $vd27 = ($value_result['data1']+$value_result['data2'])/2;
                                    $pingjun = ($value_result['data1']+$value_result['data2'])/2;
                                    $pingjun = ELEMENT_round($key_mark,$pingjun);
                                    if($key_mark=='DOX'){
                                        $vd27 = _round(($value_result['data1']+$value_result['data2'])/2,2);
                                    }
                                }
                                $vd0 =ELEMENT_round($key_mark,$vd0);
                                if($vid != ''){
                                    $DB->query("UPDATE `assay_order` SET `vd0`='{$vd0}',`ping_jun`='{$pingjun}',`vd27`='$vd27',`assay_over`='1' WHERE `bar_code`='{$bar_code}' AND `vid`='{$vid}'");
                                    $DB->query("UPDATE `assay_pay` set `over`='已审核' where `cyd_id`='{$cyd_id}' and `vid`='{$vid}'");
                                }else{
                                    if(!in_array($key_mark, $huan_jing_arr)){
                                        echo "注意：该项目标识“".$key_mark."”无法识别";
                                    }
                                }
                            }
                            break;
                        case "CY_QZ":
                            if($lims_field=='CY_QZ'){
                                $cy_qz_arr = explode(",",$value);
                                if(!empty($cy_qz_arr[0])){
                                    $cyd_id_arr['cy_user_qz']['value'] = $cy_qz_arr[0];
                                }else{
                                    $cyd_id_arr['cy_user_qz']['status'] = 'no';
                                }
                                if(!empty($cy_qz_arr[1])){
                                    $cyd_id_arr['cy_user_qz2']['value'] = $cy_qz_arr[1];
                                }else{
                                    $cyd_id_arr['cy_user_qz2']['status'] = 'no';
                                }
                            }
                            break;
                        case "sy_user_qz":case "sh_user_qz":
                            if(empty($value)){
                                $cyd_id_arr[$lims_field]['status'] = 'no';
                            }else{
                                $cyd_id_arr[$lims_field]['value']   = $value;
                            }
                            break;
                        case 'sh_user_qz_date':case 'sy_user_qz_date':case 'cy_user_qz_date':
                            if(empty($value)){
                                $cyd_id_arr[$lims_field]['status'] = 'no';
                            }else{
                                $cyd_id_arr[$lims_field]['value']   = date('Y-m-d',strtotime($value));
                            }
                            break;
                        case ''://没有统计在内的字段
                            
                            break;
                        default://其他正常字段
                            if(in_array($lims_field,array('qi_wen','qi_ya'))&&!empty($value)){
                                $value = _round($value,1);
                            }
                            $insert_rec_arr[$lims_field]   = $value;
                            break;
                    }
                    //echo $lims_field;
                }
                $old_json_arr   = $merge_json_arr   = [];
                if(!empty($bar_arr[$bar_code])){
                    $old_json_arr   = json_decode($bar_arr[$bar_code],true);
                }
                $merge_json_arr = array_merge($old_json_arr,$new_json_arr);
                $insert_rec_arr['json'] = JSON($merge_json_arr);
                update_record('cy_rec',$insert_rec_arr," `bar_code`='{$bar_code}' AND `sid`>=0 ");
                $update_num++;
                //print_rr($insert_rec_arr);
            }
            $update_cy_arr = array();
            foreach($cyd_id_arr as $field=>$v){
                if($v['status']!="no"){
                    $update_cy_arr[$field] = $v['value'];
                }
            }
            $cy_update = rtrim($cy_update,',');
            if(!empty($update_cy_arr)){
                $update_cy_arr['status']='3';
                //有审核人的情况 下 状态改为5 验收人使用审核人
                if(!empty($update_cy_arr['sh_user_qz'])){
                    $update_cy_arr['status']='5';
                    $update_cy_arr['jy_user'] = $update_cy_arr['ys_user'] = $update_cy_arr['sh_user_qz'];
                    $update_cy_arr['ys_date'] = $update_cy_arr['sh_user_qz_date'];
                }
                //状态判断
                if($cy_info['status']>$update_cy_arr['status']){
                    $update_cy_arr['status'] = $cy_info['status'];
                }
                update_record('cy',$update_cy_arr,"`id`='$cyd_id'");
            }
        }
        //计算现场项目的平均值
        $sql = "select 
            `ao`.`sid`,`ao`.vid,`ao`.`vd0`,`ao`.`hy_flag`,`ao`.id,`assay_value`.`englishMark` 
            from `assay_pay` as `ap`
            left join `assay_order` as `ao`
            on `ao`.`tid`=`ap`.`id`
            left join `assay_value`
            on `ao`.`vid`=`assay_value`.`id`
            where
            `ap`.`cyd_id`='$cyd_id' 
            and (`ao`.`hy_flag`>0 or `ao`.`hy_flag` = '-6')
            and `ap`.`is_xcjc`='1'
            order by `hy_flag` desc";
        $query = $DB->query($sql);
        $orders = array();
        while($row = $DB->fetch_assoc($query)){
            //再次计算评价值 放到pingjun里
            if($row['hy_flag']=='-6'){
                $ping_jun = ELEMENT_round($row['englishMark'],($orders[$row['sid']][$row['vid']]+$row['vd0'])/2);
                $DB->query("update `assay_order` set `ping_jun`='$ping_jun' where `id`='$row[id]'");
                continue;
            }
            $orders[$row['sid']][$row['vid']] = $row['vd0'];
        }

        echo json_encode(array('update_num'=>$update_num));
        break;
    case 'del_cy'://将中间表的采样数据删除
        $status = 'no';
        if(!empty($del_fzx_id) && !empty($del_bar_code)){
            $DB_CY->query("DELETE FROM `CY_DUIJIE` WHERE `FZX_ID`='{$del_fzx_id}' AND `SAMPLE_CODE` IN ({$del_bar_code})");
            $status = 'yes';
        }
        echo json_encode(array('status'=>$status));
    break;
    case 'table'://数据库信息更新
        if(!in_array($_POST['action2'],array('add','del','update'))){
            break;
        }
        $data = $_POST['data'];
        $table = $_POST['table'];
        $_where = "where `FZX_ID`='$data[FZX_ID]' and `LIMS_ID`='$data[LIMS_ID]'";
        if($table == 'CY_DUIJIE'){
            $site_info = $DB_CY->fetch_one_assoc("SELECT * FROM `SITE_DUIJIE` where `LIMS_ID`='$data[STID]'");
            $_where = "where `SAMPLE_CODE`='$data[SAMPLE_CODE]' and `STID`='$site_info[ID]' and `FZX_ID`='$data[FZX_ID]' ";
        }
        //从对接表删除用户
        if($_POST['action2'] == 'del'){
            $DB_CY->query("delete from  `$table` $_where");
            break;
        }
        //查询分中心信息
        $hub_info_arr = array();
        $query = $DB_CY->query("select * from `hub_info`");
        while($row = $DB_CY->fetch_assoc($query)){
            $hub_info_arr[$row['id']] = $row['hub_name'];
        }
        if(in_array($table,array('USER_DUIJIE','BOTTLE_DUIJIE'))){
            $data['HUB_NAME'] = $hub_info_arr[$data['FZX_ID']];
        }
        //遍历字段信息
        $fields = $add_v = '';
        foreach($data as $k=>$v){
            $v = trim($v);
            $fields .= "`$k`,";
            $add_v .= "'$v',";
            if(!in_array($k,array('FZX_ID','LIMS_ID'))){
                $up_v .= "`$k`='$v',"; 
            }
        }
        $fields = rtrim($fields,',');
        $add_v = rtrim($add_v,',');
        $up_v = rtrim($up_v,',');
        //修改到对接表
        if($_POST['action2'] == 'update'){
            $DB_CY->query("update `$table` set $up_v $_where");
            break;
        }
        //新增到对接表
        if($_POST['action2'] == 'add'){
            $sql = "insert into `$table`($fields)values($add_v)";
            $DB_CY->query($sql);
            break;
        }
    break;
    default://将lims数据写入对接中间表
        $fzx_id     = $_POST['fzx_id'];
        $cy_rec_arr = $_POST['rec_arr'];
        //print_rr($_POST);
        if(empty($cy_rec_arr)){
            echo json_encode(array('status'=>'no_shuju'));
            exit;
        }
        //获取站点对接表的数据
        $duijie_site_arr = [];
        $duijie_site_sql = $DB_CY->query("SELECT `ID`,`LIMS_ID` FROM `SITE_DUIJIE` WHERE `FZX_ID`='{$fzx_id}'");
        while ($duijie_site_row = $DB_CY->fetch_assoc($duijie_site_sql)) {
            $duijie_site_arr[$duijie_site_row['LIMS_ID']]  = $duijie_site_row['ID'];
        }
        //获取人员对接表的数据
        $duijie_user_arr = [];
        $duijie_user_sql = $DB_CY->query("SELECT `ID`,`LIMS_ID`,`USER_NAME` FROM `USER_DUIJIE` WHERE  `FZX_ID`='{$fzx_id}'");
        while ($duijie_user_row = $DB_CY->fetch_assoc($duijie_user_sql)) {
            $duijie_user_arr[$duijie_user_row['ID']]  = $duijie_user_row['USER_NAME'];
        }
        //获取采样瓶对接表的数据
        $duijie_bottle_arr = [];
        $duijie_bottle_sql = $DB_CY->query("SELECT `ID`,`LIMS_ID` FROM `BOTTLE_DUIJIE` WHERE  `FZX_ID`='{$fzx_id}'");
        while ($duijie_bottle_row = $DB_CY->fetch_assoc($duijie_bottle_sql)) {
            $duijie_bottle_arr[$duijie_bottle_row['LIMS_ID']]  = $duijie_bottle_row['ID'];
        }
        //获取检测项目的唯一标识
        $duijie_value_arr = [];
        $duijie_value_sql = $DB_CY->query("SELECT `id`,`value_C`,`englishMark` FROM `assay_value` WHERE `is_xcjc`='1' ");
        while ($duijie_value_row = $DB_CY->fetch_assoc($duijie_value_sql)) {
            $duijie_value_arr[$duijie_value_row['id']]['mark']  = $duijie_value_row['englishMark'];
            $duijie_value_arr[$duijie_value_row['id']]['name']  = $duijie_value_row['value_C'];
        }
        //获取采样任务信息
        $cy_insert_arr  = [];
        $cy_insert_arr['FZX_ID']   = $fzx_id;
        foreach ($cy_rec_arr as $old_cy_row) {
            $cy_insert_arr['STID']  = $duijie_site_arr[$old_cy_row['sid']];//站点对接表ID
            switch ($old_cy_row['zk_flag']) {
                case '-6':
                    $cy_insert_arr['SAMPLE_MARK']   = 'xcpx';//现场平行水样
                    break;
                case '1':
                    $cy_insert_arr['SAMPLE_MARK']   = 'qckb';//全程空白水样
                    $cy_insert_arr['STID']  = $duijie_site_arr[$old_cy_row['kb_sid']];//站点对接表ID
                    break;
                default:
                    $cy_insert_arr['SAMPLE_MARK']   = 'zcsy';//正常水样
                    break;
            }
            $cy_insert_arr['SAMPLE_CODE']  = $old_cy_row['bar_code'];//样品编号
            $cy_insert_arr['PLAN_DATE']  = $old_cy_row['cy_date'];//计划采样日期
            //采样人ID的获取
            $cy_user_id_arr = [];
            $cy_user_id_arr[] = array_search($old_cy_row['cy_user'], $duijie_user_arr);
            if(!empty($old_cy_row['cy_user2'])){
                $cy_user_id_arr[] = array_search($old_cy_row['cy_user2'], $duijie_user_arr);
            }
            $cy_insert_arr['SAMPLE_STAFF_ID']  = implode(',', $cy_user_id_arr);//采样人ID
            //现场检测项目标识的获取
            $xc_value_arr   = explode(',', $old_cy_row['xc_exam_value']);
            $jc_value_arr   = explode(',', $old_cy_row['assay_values']);
            $xc_value_id    = array_intersect($xc_value_arr, $jc_value_arr);
            $insert_value_str   = [];
            foreach ($xc_value_id as $value) {
                $insert_value_str[$value]   = !empty($duijie_value_arr[$value]['mark'])?$duijie_value_arr[$value]['mark']:$duijie_value_arr[$value]['name'];
            }
            if(!array_key_exists('milieu_values',$old_cy_row)){
                $huan_jing_arr  = array('AIRT','ATM','Z','Q','WNDV','WNDDIR');//现场环境信息
            }else{
                $huan_jing_arr = explode(',',$old_cy_row['milieu_values']);
            }
            $insert_value_str   = array_merge($insert_value_str,$huan_jing_arr);
            $cy_insert_arr['ELEMENT']  = implode(',', $insert_value_str);//现场检测项目信息
            //采样瓶信息的获取
            $insert_bottle_arr  = [];
            $old_cy_row['json'] = str_replace('\\', '', $old_cy_row['json']);
            $cy_rongqi_arr  = json_decode($old_cy_row['json'],true);
            foreach ((array)$cy_rongqi_arr['rq'] as $key => $value) {
                $insert_bottle_arr[$duijie_bottle_arr[$key]]    = $value;
            }
            $cy_insert_arr['SAMPLE_BOTTLE']  = JSON($insert_bottle_arr);//采样瓶信息
            //应该加一个是否该数据已经存在，再插入！！！
            CY_new_record('CY_DUIJIE',$cy_insert_arr);
        }
        echo json_encode(array('status'=>'yes','num'=>count($cy_rec_arr)));
        break;
}
function CY_new_record( $table_name, $data ) {
    global $DB_CY;
    //如果数据库中存在这个样品编号，则为更新
    $where = "where `SAMPLE_CODE`='{$data['SAMPLE_CODE']}' and `FZX_ID`='{$data['FZX_ID']}' and `STID` = '{$data['STID']}'";
    $sql = "select * from $table_name $where";
    $query = $DB_CY->query($sql);
    $num = $DB_CY->num_rows($query);

    $sql2 = '';
    //组装sql语句
    while( list( $key, $value ) = each( $data ) ) {
        $value = check_input($value);
        $sql2 .= "`$key` = '$value',";
    }
    $sql2 = rtrim( $sql2, ',' );
    if($num != 0){
        $sql = "update $table_name SET $sql2 $where";
        $DB_CY->query( $sql );
        return $DB_CY->affected_rows();
    }else{
        $sql = "INSERT INTO $table_name SET $sql2";
        $DB_CY->query( $sql );
        return $DB_CY->insert_id();
    }
}
//修约函数
function ELEMENT_round($type,$value){
    if($type=='WT'&&!empty($value)){//水温 1位
        return _round($value,1);
    }
    if($type=='PH'&&!empty($value)){//ph 2位
        return _round($value,2);
    }
    //电导率 小于1000保留3位有效数字 大于1000保留整数
    if($type=='COND'&&!empty($value)){
        if($value<10){
            return _round($value,2);
        }
        if($value<100){
            return _round($value,1);
        }
        return _round($value,0);
    }//透明度 保留2位小数
    if($type=='CLARITY'&&!empty($value)){
        return _round($value,2);
    }
    if($type=='DOX'&&!empty($value)){//溶解氧1位
        return _round($value,1);
    }
}
?>
