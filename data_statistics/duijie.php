<?php
//月报数据上传到duijie 表中
include '../temp/config.php';
$set_id = intval($_POST['set_id']);
//初始化
$curl = curl_init();
//设置抓取的url
curl_setopt($curl, CURLOPT_URL, "$rooturl/data_statistics/tjbg_cgmonth_bg.php?action=json&set_id=$set_id");
//设置头文件的信息作为数据流输出
curl_setopt($curl, CURLOPT_HEADER, 0);
//设置获取的信息以文件流的形式返回，而不是直接输出。
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//执行命令
$data = curl_exec($curl);
//关闭URL请求
curl_close($curl);
//显示获得的数据
$data = json_decode($data,true);
//获取项目的英文标识
$query = $DB->query("select * from `assay_value`");
$xm_arr = array();
while($row = $DB->fetch_assoc($query)){
    $xm_arr[$row['id']] = $row['englishMark'];
    $xm_value_C[$row['id']] = $row['value_C'];
}
$up_i = 0;
$add_i = 0;
//根据月报中涉及的cy表id 取出目前duijie表所存在的数据 然后进行更新和新增
$cyd_ids = array();
foreach($data as $k=>$v){
    $cyd_ids[] = $k;
}
if(count($cyd_ids)<1){
    exit;
}
//现场环境项目
$xc_bs = array(
	'AIRT',//气温
	'ATM',//气压
	'Q',//流量
	'WNDV',//风速
	'WNDDIR',//风向
	'WTH',//天气
	'FLWV',//流速
	'Z'//水位
);
$xc_bs_unit = array(
	'AIRT'=>'℃',//气温
	'ATM'=>'kPa',//气压
	'Q'=>'m³/s',//流量
	'WNDV'=>'m/s',//风速
	'WNDDIR'=>'',//风向
	'WTH'=>'',//天气
	'FLWV'=>'m/s',//流速
	'Z'=>'m'//水位
);
//根据涉及到的cy表id  查询当前duijie表的数据
$cyd_id = "'".implode("','",$cyd_ids)."'";
$query = $DB->query("select * from `duijie` where `cyd_id` in ($cyd_id)");
$arr = array();
while($row = $DB->fetch_assoc($query)){
    $arr[$row['cyd_id']][$row['STCD']][$row['vid']] = $row['id'];
    if(in_array($row['englishMark'],$xc_bs)){
        $arr[$row['cyd_id']][$row['STCD']][$row['englishMark']] = $row['id'];
    }
} 
//如果duijie表中没有这条数据 记录下来统一执行新增 如果存在 则update数据
$insert_sql2 = '';
$not_found_vids = array();
foreach($data as $cyid=>$cy_v){
    foreach($cy_v as $site_code=>$v){
        foreach($v as $vid=>$value){
            if(in_array($vid,array('SPT','PRPNM','LYNM','STNM'))){ 
                continue;
            }
            //现场环境项目
            if(in_array($vid,$xc_bs)){
                $tmp_unit = $xc_bs_unit[$vid];
                if(empty($arr[$cyid][$site_code][$vid])){
                    $insert_sql2 .= "('$site_code','$v[STNM]','$v[PRPNM]','$v[LYNM]','$v[SPT]','$cyid','','$vid','$value','$tmp_unit','','',''),";
                    $add_i ++;
                }else{
                    $id = $arr[$cyid][$site_code][$vid];
                    $DB->query("update `duijie` set `result`='{$value}',`unit`='$tmp_unit' where `id`='$id'");
                    $up_i ++;
                }
                continue;
            }
            //没有英文标识的项目记录下来 发邮件
            if(empty($xm_arr[$vid])){
                $not_found_vids[]= $vid; 
                continue;
            }else{
                $englishMark = $xm_arr[$vid];
            }
            $pingjia_arr = $value['pingjia'];
            if(empty($arr[$cyid][$site_code][$vid])){
                $add_i ++;
                $insert_sql2 .= "('$site_code','$v[STNM]','$v[PRPNM]','$v[LYNM]','$v[SPT]','$cyid','$vid','$englishMark','$value[vd0]','$value[unit]','$pingjia_arr[now_quality]','$pingjia_arr[status]','$pingjia_arr[beishu]'),";
            }else{
                
                $DB->query("update `duijie` set `result`='{$value[vd0]}',`unit`='$value[unit]',`quality`='$pingjia_arr[now_quality]',`qualified`='$pingjia_arr[status]',`beishu`='$pingjia_arr[beishu]' where `cyd_id`='$cyid' and `STCD`='$site_code' and `vid`='$vid'");
                $up_i ++;
            }
        }
    }
}
//如果有找不到的英文标识的项目 记录下来发邮件
if(!empty($not_found_vids)){
    $headers = "From: =?utf-8?B?".base64_encode($u[userid])."?= <www-data@limstest.anheng.com.cn>\r\n";
    $not_found_vid ='';
    $not_found_vids = array_unique($not_found_vids);
    foreach($not_found_vids as $k=>$v){
        $not_found_vid .= $xm_value_C[$v]."($v)";
    }
    $content = "找不到英文标识的项目:".$not_found_vid;
    $content = strip_tags($content);
    @mail($technicalemail,'=?utf-8?B?'.base64_encode('没有英文标识的项目').'?=',$content,$headers);
}

$insert_sql2 = rtrim($insert_sql2,',');
$insert_sql = "insert into `duijie`(`STCD`,`STNM`,`PRPNM`,`LYNM`,`SPT`,`cyd_id`,`vid`,`englishMark`,`result`,`unit`,`quality`,`qualified`,`beishu`)values".$insert_sql2;
if($add_i>=1){
    $DB->query($insert_sql);
}
//更改月报信息 记录同步时间
$baogao_info = $DB->fetch_one_assoc("select * from `baogao_list` where `id`='$set_id'");
$gx_set = json_decode($baogao_info['gx_set'],true);
$gx_set['duijie_date'] = (string)time();
$gx_set_v = json_encode($gx_set);
$DB->query("update `baogao_list` set `gx_set`='$gx_set_v' where `id`='$baogao_info[id]'");

$return_arr['add_i'] = $add_i;
$return_arr['up_i'] = $up_i;
$return_arr['up_i'] = $up_i;
$return_arr['not_found'] = $not_found_vid;
$return_arr['not_found_num'] = count($not_found_vids);
echo json_encode($return_arr,JSON_UNESCAPED_UNICODE);
//echo "<script>alert('新上传{$add_i}条，更新{$up_i}条,{$content}')</script>";
//echo "<script>window.location.href='tjbg_cgmonth_bg.php?action=see&set_id={$set_id}'</script>";
?>