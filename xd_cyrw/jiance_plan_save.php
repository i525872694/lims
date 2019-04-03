<?php
//监测计划下达
include("../temp/config.php");
$plan_id    = $_GET['plan_id'];
if(empty($plan_id)){
    goback("请传递必要参数");
}
$old_plan   = $DB->fetch_one_assoc("SELECT * FROM `jiance_plan` WHERE `id`='{$plan_id}'");
$cyd_id = trim($old_plan['cyd_id'],',');
///更改计划状态
$DB->query("UPDATE `jiance_plan` SET `status`='已下达' WHERE `id`='{$plan_id}'");
////下达采样任务
$DB->query("UPDATE `cy` SET `cy_rwxd_user`='{$u['userid']}', `cy_rwxd_user_qz_date`='".date('Y-m-d')."',`status`='1',`csrw_xdcy_user`='{$u['userid']}',`xdcy_qz_date`='".date('Y-m-d')."' WHERE `id` in({$cyd_id}) AND `status`='0'");
################往中间对接表里插入数据
//获取采样任务信息
$rec_arr  = [];
$old_cy_sql = "SELECT cr.*,cy.cy_date,cy.cy_user,cy.cy_user2,cy.xc_exam_value,milieu_values FROM `cy_rec` AS cr INNER JOIN `cy` ON cr.cyd_id=cy.id WHERE 1 AND `cr`.`cyd_id` in ({$cyd_id}) AND `cy_user` NOT IN ('','委托方')";
$old_cy_query = $DB->query($old_cy_sql);
while ($old_cy_row = $DB->fetch_assoc($old_cy_query)) {
    $rec_arr[]  = $old_cy_row;
}
echo json_encode(array('status'=>'yes','rec_arr'=>$rec_arr,`sql`=>$old_cy_sql));
?>
