<?php
/**
 * 功能：
 * 作者：Mr Zhou
 * 日期：2017-05-20
 * 描述：修改站点
*/
include '../temp/config.php';
// include $rootdir . '/huayan/pinyin.php';

// 分中心ID
$fzx_id = FZX_ID;
$sid = intval($_GET['sid']);
$sql = "SELECT * FROM `sites` WHERE 1 AND `id`='{$sid}'";
$siteInfo = $DB->fetch_one_assoc($sql);
if(empty($siteInfo)){
    die("站点未找到");
}
$siteInfoJSON = json_encode($siteInfo);
disp('site/siteInfo');