<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-7-8
 * Time: 下午2:56
 */
#本文件将表 szcyresult 的内容同步到 采样结果信息表 cy_rec
error_reporting(E_ALL ^ E_NOTICE);
$checkLogin = false;

if (!$DB) include __DIR__ . '/../../temp/config.php';

$sql = "select * from  szcyresult 
where  cytime >='" . date("Y-m-01", strtotime("-2 month")) . "' 
and flag='1'
order by cytime asc
";

$rows = $DB->query($sql);
$szcyresult = [];
while ($row = $DB->fetch_assoc($rows)) {
    $tmp = [];
    foreach ($row as $index => $item) {
        $tmp[strtolower($index)] = $item;
    }
    $szcyresult[$tmp['bar_code']] = $tmp;
}

$all_rq_value = get_all_rq_value();//获取rq_value表的项目名和id的对应关系
//
$sql = "select * from cy_rec 
where bar_code in ('" . implode("','", array_keys($szcyresult)) . "') ";


$rows = $DB->query($sql);
while ($row = $DB->fetch_assoc($rows)) {
    $tmp = [];
    foreach ($row as $index => $item) {
        $tmp[strtolower($index)] = $item;
    }
    $row = $tmp;
    $set_data = [];

    $data = $szcyresult[$row['bar_code']];//要同步的数据

    //普通字段直接更新
    $set_data = rec_nomal_field_sync($data, $set_data);

    // furnished_xccdxm：　现场检测项目
    $set_data = furnished_xccdxm_jiexi($row['id'], $data, $set_data);

    //furnished_xcjbcj:   现场加保存剂
    $set_data = furnished_xcjbcj_jiexi($row['json'], $data['furnished_xcjbcj'], $set_data);

    //更新 采样批次信息表  cy, cy_user_qz  采样人
    update_set_cy_cy_user_qz($row['cyd_id'], $data['furnished_cyr']);

    if(count($set_data)) {
        $sql  = "update cy_rec set ".implode(",",$set_data)." where id='".$row['id']."' limit 1";
        $DB->query($sql);
    }
}


//获取rq_value表的项目名和id的对应关系
function get_all_rq_value()
{
    global $DB;
    $result = [];
    $sql = "select id,rq_name from rq_value ";

    $rows = $DB->query($sql);
    while ($row = $DB->fetch_assoc($rows)) {
        $result[trim($row['rq_name'])] = $row['id'];
    }
    return $result;
}


/**
 * 解析 现场加保存剂
 * $furnished_xcjbcj 数据格式 溶解氧,COD等,重金属,六价铬,酚、氰
 */
function furnished_xcjbcj_jiexi($old_json, $furnished_xcjbcj, $set_data)
{
    global $DB, $all_rq_value;

    if (!trim($furnished_xcjbcj)) return $set_data;

    $bcj_data = array_map(function ($v) {
        return trim($v);
    }, explode(',', trim($furnished_xcjbcj)));

    $result = [];

    foreach ($bcj_data as $bcj_name) {


        if ($all_rq_value[$bcj_name]) {
            $key = $all_rq_value[$bcj_name];
        } else {
            $sql = " insert into rq_value set rq_name='$bcj_name'  ";

            echo "\r\n \t 新写入项目: \t" . $bcj_name."\r\n";

            $DB->query($sql);
            $key = $DB->insert_id();
            $all_rq_value[$bcj_name] = $key;
        }
        $result[$key] = 1;
    }

    if (count($result)) {

        $old_data = json_decode($old_json, true);
        $old_data['rq'] = $result;
        $set_data[] = " `json`='" . json_encode($old_data) . "' ";
    }

    return $set_data;

}

/**
 * 解析 现场检测项目
 * $data['furnished_xccdxm'] 数据格式　PH:7.89   DO:22mg/L   电导率:310μS/cm   透明度:0.5m   风向：偏东   风速:2.0m/s
 */
function furnished_xccdxm_jiexi($cid, $data, $set_data)
{
    global $DB;
    $furnished_xccdxm = array_map(function ($v) {
        //根据数据格式进行分割
        $data = explode(':', trim($v));
        $data = array_filter($data);

        if (intval(count($data)) == 2) {
            return [trim($data[0]) => trim($data[1])];
        }

    }, explode(" ", trim($data['furnished_xccdxm'])));

    $furnished_xccdxm = array_filter($furnished_xccdxm);


    //
    $assay_order = [
        'PH' => 99,
        '水温' => 97,
        '透明度' => 98,
        '电导率' => 117,
        'DO' => 114,
    ];

    $rec_field = ['风向' => 'feng_xiang', '风速' => 'feng_su'];

    $assay_data = [];

    foreach ($furnished_xccdxm as $info) {
        foreach ($info as $name => $value) {

            if (array_key_exists($name, $assay_order)) {

                if (preg_match('/\d+/i', $value)) {
                    $assay_data[$assay_order[$name]] = $value;
                }

            } else {
                if (array_key_exists($name, $rec_field)) {

                    if ($name == '风速' && !preg_match('/\d+/i', $value)) {
                        continue;
                    }

                    $set_data[] = " `" . $rec_field[$name] . "`='$value' ";
                }
            }
        }
    }

    //水温
    if (preg_match('/\d+/i', $data['furnished_shuiwen'])) {
        $assay_data[97] = $data['furnished_shuiwen'];
    }


    //更新到 assay_order
    foreach ($assay_data as $vid => $vd0) {
        $sql = "update assay_order set vd0='$vd0' where cid='$cid' and vid='$vid' limit 1";
         $DB->query($sql);
    }

    return $set_data;
}


//采样批次信息表  cy
function update_set_cy_cy_user_qz($cyd_id, $name)
{
    global $DB;
    $sql = "update cy set `cy_user_qz`='$name' where id='$cyd_id'   limit 1";
    $DB->query($sql);
}


//将 szcyresult 表字段数据 同步到对应的 cy_rec 表字段
function rec_nomal_field_sync($data, $set_data)
{
    // szcyresult 表字段 => cy_rec 表字段
    $field = [
        "latitude" => "weidu",//纬度
        "longitude" => "jingdu",//经度
        "furnished_qyff" => "cy_way",//取样方式
        "furnished_qygj" => "cy_tool",//取样工具
        "furnished_za" => "zuo_an",//左岸
        "furnished_zh" => "zhong_hong",//中弘
        "furnished_ya" => "you_an",//右岸
        "furnished_cys" => "cy_ms",//采样深
        "furnished_smk" => "water_width",//水面宽
        "furnished_qw" => "qi_wen",//气温
        "furnished_qy" => "qi_ya",//气压
        "furnished_sw" => "water_height",//水位
        "furnished_ll" => "liu_l",//流量
        "furnished_p" => "ping",//瓶
        "furnished_t" => "tong",//桶
        "furnished_dmwrxxjsm" => "dmwrxxjsm",//断面污染现象及说明
        "furnished_ypzt" => "gg_zb",//样品状态
        "furnished_bz" => "cy_note",//备注
        "furnished_qxsw" => "tian_qi",//气象水文
        "furnished_qtyp" => "other_bar",//其他样品
    ];
    foreach ($field as $index => $item) {
        $set_data[] = " `$item`='" . trim($data[$index]) . "' ";
    }
    //cytime 　：采样时间
    $t = strtotime($data['cytime']);
    $set_data[] = " `cy_date`='" . date("Y-m-d", $t) . "' ";
    $set_data[] = " `cy_time`='" . date("H:i:s", $t) . "' ";

    return $set_data;
}
