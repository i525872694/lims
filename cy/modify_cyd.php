<?php
/**
* 修改采样单
*/

include "../temp/config.php";

if($u[userid] == '') nologin();
switch($_GET[action]){
    case '修改采样日期':
        $DB->query("update `cy` set `cy_date`='".$_GET['cy_date']."' where `id`='".$_GET['cyd_id']."'");
        break;
    case '修改采样人':
        $DB->query("update `cy` set `cy_user`='".$_GET['cy_user']."' where `id`='".$_GET['cyd_id']."'");
        break;
    case '删除':
        $del_fzx_id = $u['fzx_id'];
        $del_bar_code_arr   = [];
        $sql    = $DB->query("SELECT * FROM `cy_rec` WHERE `cyd_id`='{$_GET['cyd_id']}'");
        while ($row = $DB->fetch_assoc($sql)) {
            $del_bar_code_arr[] = $row['bar_code'];
        }
        $del_bar_code   = "'".implode("','", $del_bar_code_arr)."'";
        $DB->query("delete from `cy` where id='".$_GET['cyd_id']."'");
        $DB->query("delete from `cy_rec` where cyd_id='".$_GET['cyd_id']."'");
        $DB->query("delete from `assay_order` where cyd_id='".$_GET['cyd_id']."'");
        $DB->query("delete from `assay_pay` where cyd_id='".$_GET['cyd_id']."'");
        echo json_encode(array('status'=>'yes','del_bar_code'=>$del_bar_code));exit;
        break;
    case '修改采样单编号':
        $DB->query("update `cy` set `cyd_bh`='".$_GET['cyd_bh']."' where `id`='".$_GET['cyd_id']."'");
        break;
}
gotourl($_SESSION['url']);

?>
