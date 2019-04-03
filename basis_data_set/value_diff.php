<?php
//历史数据导入界面
include '../temp/config.php';
if($u['userid'] == ''){
	nologin();
}
$fzx_id		= $u['fzx_id'];
switch ($_POST['action']) {
    case 'xm':
        $lines  = "";
        //获取检测标准中的名称
        $xm_arr     = [];
        $xm_sql     = "SELECT av.`id`,av.`value_C`,aj.`value_C` AS aj_name 
                        FROM `assay_value` AS av 
                        LEFT JOIN `assay_jcbz` AS aj 
                        ON `av`.`id`=`aj`.`vid` WHERE 1 ORDER BY `value_C`";
        $xm_query   = $DB->query($xm_sql);
        while ($xm_row = $DB->fetch_assoc($xm_query)) {
            $vid    = $xm_row['id'];
            if(!array_key_exists($xm_row['value_C'], $xm_arr)){
                $xm_arr[$xm_row['value_C']] = $vid;
                $lines  .= "<span class='btn btn-white editable' style='-moz-user-select: text;'>{$xm_row['value_C']}</span>";
            }
            if(!empty($xm_row['aj_name']) && !array_key_exists($xm_row['aj_name'], $xm_arr)){
                $xm_arr[$xm_row['aj_name']] = $vid;
                $lines  .= "<span class='btn btn-white editable' style='-moz-user-select: text;'>{$xm_row['aj_name']}</span>";
            }
        }
        break;
    case 'water_type':
        $leixing_sql    = "SELECT * FROM `leixing` WHERE `fzx_id`='0' OR `fzx_id`='{$fzx_id}'";
        $leixing_query  = $DB->query($leixing_sql);
        while ($leixing_row = $DB->fetch_assoc($leixing_query)) {
            $lines  .= "<span class='btn btn-white editable' style='-moz-user-select: text;'>{$leixing_row['lname']}</span>";
        }
        break;
    default:
        $lines = "传递参数有误";
        break;
}
echo temp("jczx/value_diff");
?>