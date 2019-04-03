<?php
//同步站点信息到对接表里
include '../temp/config.php';
//TRUNCATE TABLE `BOTTLE_DUIJIE`;TRUNCATE TABLE `SITE_DUIJIE`;TRUNCATE TABLE `USER_DUIJIE`;
//CREATE USER 'tongbu'@'%' IDENTIFIED BY '***';GRANT ALL PRIVILEGES ON *.* TO 'tongbu'@'%' IDENTIFIED BY '***' WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
$fzx_server_arr = [];
$fzx_server_arr['kunming'] = ['name'=>'昆明分中心','server'=>'192.168.14.140','dbname'=>'kunming_water','fzx_id'=>'16'];
//$fzx_server_arr['qujing'] = ['name'=>'曲靖','server'=>'192.168.14.142','dbname'=>'water','fzx_id'=>'24'];
$fzx_server_arr['banna'] = ['name'=>'版纳','server'=>'192.168.14.144','dbname'=>'water','fzx_id'=>'4'];
$fzx_server_arr['baoshan'] = ['name'=>'保山','server'=>'192.168.14.149','dbname'=>'water','fzx_id'=>'6'];
$fzx_server_arr['chuxiong'] = ['name'=>'楚雄','server'=>'192.168.14.145','dbname'=>'water','fzx_id'=>'8'];
//$fzx_server_arr['dali'] = ['name'=>'大理','server'=>'192.168.14.152','dbname'=>'water','fzx_id'=>'10'];
$fzx_server_arr['dehong'] = ['name'=>'德宏','server'=>'192.168.14.153','dbname'=>'water','fzx_id'=>'12'];
$fzx_server_arr['honghe'] = ['name'=>'红河','server'=>'192.168.14.154','dbname'=>'water','fzx_id'=>'14'];
$fzx_server_arr['lijiang'] = ['name'=>'丽江','server'=>'192.168.14.141','dbname'=>'water','fzx_id'=>'18'];
$fzx_server_arr['lincang'] = ['name'=>'临沧','server'=>'192.168.14.150','dbname'=>'water','fzx_id'=>'20'];
$fzx_server_arr['puer'] = ['name'=>'普洱','server'=>'192.168.14.151','dbname'=>'water','fzx_id'=>'22'];
$fzx_server_arr['wenshan'] = ['name'=>'文山','server'=>'192.168.14.148','dbname'=>'water','fzx_id'=>'26'];
$fzx_server_arr['yuxi'] = ['name'=>'玉溪','server'=>'192.168.14.147','dbname'=>'water','fzx_id'=>'28'];
$fzx_server_arr['zhaotong'] = ['name'=>'昭通','server'=>'192.168.14.146','dbname'=>'water','fzx_id'=>'30'];
foreach ($fzx_server_arr as $key => $value) {
    echo "<br>以下信息为".$value['name']."导入情况";
    $server = $value['server'];
    $db_user= 'tongbu';
    $db_pass= 'tongbu123';
    $dbname = $value['dbname'];//和采样系统对接的中间库
    $charset= 'utf-8';
    $fzx_id = $value['fzx_id'];
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
    //$fzx_id         = FZX_ID;//中心
    ##################站点对接
    //查询对接表里已存在的站点
    $old_duijie_arr = [];
    $old_duijie_sql = $DB->query("SELECT `ID`,`LIMS_ID` FROM `SITE_DUIJIE` WHERE `FZX_ID`='{$fzx_id}'");
    while ($old_duijie_row = $DB->fetch_assoc($old_duijie_sql)) {
        $old_duijie_arr[$old_duijie_row['ID']]  = $old_duijie_row['LIMS_ID'];
    }
    //查询站点表信息组装并插入到对接表中
    $insert_arr = [];
    $count_num   = ['update'=>'0','insert'=>'0'];
    $duijie_arr = ['id'=>'LIMS_ID','fzx_id'=>'FZX_ID','site_code'=>'STCD','site_name'=>'STNM','site_line'=>'PRPNM','site_vertical'=>'LYNM','jingdu'=>'LGTD','weidu'=>'LTTD','site_address'=>'STLC','area'=>'BSNM','water_system'=>'HNNM','river_name'=>'RVNM'];
    $sql = $DB_CY->query("SELECT * FROM sites WHERE `fzx_id`='{$fzx_id}' OR `fp_id`='{$fzx_id}' ");//
    while ($row = $DB_CY->fetch_assoc($sql)) {
        $insert_arr = [];
        foreach ($duijie_arr as $key => $value) {
            if($key == 'fzx_id' && !empty($row['fp_id'])){
                $row[$key]  = $row['fp_id'];//如果有分配id，就以分配id为准
            }
            $insert_arr[$value]   = $row[$key];
        }
        $update_id  = array_search($row['id'], $old_duijie_arr);
        if(!empty($update_id)){
            update_record('SITE_DUIJIE',$insert_arr," `id`='{$update_id}'");
            $count_num['update']++;
        }else{
            new_record('SITE_DUIJIE',$insert_arr);
            $count_num['insert']++;
        }
    }
    echo "<br>本次新添加站点：{$count_num['insert']}个，更新站点：{$count_num['update']}个";
    ###############采样员对接
    //查询对接表里已存在的账户
    $old_duijie_arr = [];
    $old_duijie_sql = $DB->query("SELECT `ID`,`LIMS_ID` FROM `USER_DUIJIE` WHERE  `FZX_ID`='{$fzx_id}'");
    while ($old_duijie_row = $DB->fetch_assoc($old_duijie_sql)) {
        $old_duijie_arr[$old_duijie_row['ID']]  = $old_duijie_row['LIMS_ID'];
    }
    //查询站点表信息组装并插入到对接表中
    $insert_arr = [];
    $count_num  = ['update'=>'0','insert'=>'0'];
    $duijie_arr = ['id'=>'LIMS_ID','fzx_id'=>'FZX_ID','hub_name'=>'HUB_NAME','userid'=>'USER_NAME'];
    $sql = $DB_CY->query("SELECT u.*,hi.`hub_name` FROM `users` AS u INNER JOIN `hub_info` AS hi ON u.fzx_id=hi.id WHERE `group` NOT IN ('0','测试组') AND `fzx_id`='{$fzx_id}'");//`cy`='1' 
    while ($row = $DB_CY->fetch_assoc($sql)) {
        $insert_arr = [];
        foreach ($duijie_arr as $key => $value) {
            if($key == 'userid'){
                $row[$key]  = str_replace(' ', '', $row[$key]);
            }
            $insert_arr[$value]   = $row[$key];
        }
        $update_id  = array_search($row['id'], $old_duijie_arr);
        if(!empty($update_id)){
            update_record('USER_DUIJIE',$insert_arr," `id`='{$update_id}'");
            $count_num['update']++;
        }else{
            new_record('USER_DUIJIE',$insert_arr);
            $count_num['insert']++;
        }
    }
    echo "<br />本次新添加采样员：{$count_num['insert']}个，更新采样员信息：{$count_num['update']}个";
    ###################采样瓶对接
    //查询对接表里已存在的账户
    $old_duijie_arr = [];
    $old_duijie_sql = $DB->query("SELECT `ID`,`LIMS_ID` FROM `BOTTLE_DUIJIE` WHERE  `FZX_ID`='{$fzx_id}'");
    while ($old_duijie_row = $DB->fetch_assoc($old_duijie_sql)) {
        $old_duijie_arr[$old_duijie_row['ID']]  = $old_duijie_row['LIMS_ID'];
    }
    //查询站点表信息组装并插入到对接表中
    $insert_arr = [];
    $count_num   = ['update'=>'0','insert'=>'0'];
    $duijie_arr = ['id'=>'LIMS_ID','fzx_id'=>'FZX_ID','hub_name'=>'HUB_NAME','rq_name'=>'BOTTLE_NAME','rq_size'=>'BOTTLE_VOLUME'];
    $sql = $DB_CY->query("SELECT rv.*,hi.`hub_name` FROM `rq_value` AS rv INNER JOIN `hub_info` AS hi ON `rv`.`fzx_id`=`hi`.`id` WHERE `fzx_id`='{$fzx_id}'");//
    while ($row = $DB_CY->fetch_assoc($sql)) {
        $insert_arr = [];
        foreach ($duijie_arr as $key => $value) {
            $insert_arr[$value]   = $row[$key];
        }
        $update_id  = array_search($row['id'], $old_duijie_arr);
        if(!empty($update_id)){
            update_record('BOTTLE_DUIJIE',$insert_arr," `id`='{$update_id}'");
            $count_num['update']++;
        }else{
            new_record('BOTTLE_DUIJIE',$insert_arr);
            $count_num['insert']++;
        }
    }
    echo "<br />本次新添加采样瓶：{$count_num['insert']}个，更新采样瓶信息：{$count_num['update']}个";

}
?>
