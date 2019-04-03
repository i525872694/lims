<?php
/**
 * 功能：更新化验单
 * 作者：铁龙
 * 日期：2014-04-14
 * 描述：化验单的计算和保存
*/
$tid = intval($_POST['tid']);
if ($tid) {
    $arow = $DB->fetch_one_assoc("SELECT * FROM `assay_pay` WHERE `id`='{$tid}' AND ( `fp_id` = '{$fzx_id}' OR `fzx_id` = '{$fzx_id}' ) LIMIT 1");
}
//判断是否有权利修改化验单
if (!in_array($u['id'], array($arow['uid'],$arow['uid2'])) && !$u['admin']) {
    error_msg('你不是化验员本人，不能进行修改操作！');
}
if ('save_sign' != $_POST['submit_flag'] && !empty($arow['sign_01']) && !$u['admin']) {
    error_msg('该化验单已被（<span class="green">'.$arow['sign_01'].'</span>）签字，不能进行修改，请刷新页面查看。');
}
//修改化验单表头内容
$key_val_arr = array();
for ($i=0; $i <= 34; $i++) {
    if (isset($_POST['td'.$i])) {
        $key_val_arr[$i] = "`td$i` = '".trim($_POST['td'.$i])."'";
    }
}
// 任务名称
if (isset($_POST['renwu_mc'])) {
    $key_val_arr[] = "`renwu_mc`='".trim($_POST['renwu_mc'])."'";
}
// 表头数据
if (isset($_POST['btdata'])) {
    $key_val_arr[] = "`btdata`='".JSON($_POST['btdata'])."'";
}
// 曲线截距
if (isset($_POST['CA'])) {
    $key_val_arr[] = "`CA`='".trim($_POST['CA'])."'";
}
// 曲线斜率
if (isset($_POST['CB'])) {
    $key_val_arr[] = "`CB`='".trim($_POST['CB'])."'";
}
// 曲线R值
if (isset($_POST['CR'])) {
    $key_val_arr[] = "`CR`='".trim($_POST['CR'])."'";
}
// 组合sql
$key_val_str = implode(',', $key_val_arr);
if (!empty($key_val_str)) {
    $DB->query("UPDATE `assay_pay` SET {$key_val_str} WHERE `id`='{$arow['id']}' ORDER BY `id` LIMIT 1");
}
// 查询出本化验单的检测方法配置情况
$xmfa_data = $DB->fetch_one_assoc("SELECT xf.*,ap.`vid` FROM `xmfa` xf LEFT JOIN `assay_pay` ap ON ap.`fid`=xf.`id` WHERE ap.`id`='{$tid}'");
$task_id = array();
// 对每一个化验任务进行处理
foreach ($_POST['mission'] as $key => $missid) {
    $vdx = array();
    $oid = $task_id[] = $_POST['mission'][$key];
    // 处理通过js进行计算的原始结果
    if (isset($_POST['_vd0'][$key])) {
        $vdx['_vd0']=$_POST['_vd0'][$key];
    }
    // 循环取出每一列的值
    for ($i = 0; $i <= 30; $i++) {
        if (isset($_POST['vd'.$i])) {
            $vdx['vd'.$i] = trim($_POST['vd'.$i][$key]);
        }
    }
	// 取出js_gongshi,计算过程公式
	$vdx['js_gongshi'] = $_POST['js_gongshi'][$key];
	//  编码 URL 字符串
	$vdx['js_gongshi'] = urlencode(str_replace("\\", '', $vdx['js_gongshi']));
    // 数据保存
    if ($oid>1) {
        calc_a_task($oid, $vdx, $arow['id'], $arow['vid'], $xmfa_data);
    }
}
// 化验结果计算结束,检查质控
foreach ($task_id as $i => $oid) {
    check_zhi_kong($oid, $arow['td3']);
}
