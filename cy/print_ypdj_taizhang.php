<?php 
/**
 * 功能：样品登记台账 显示
 * 作者：gongyanxiao
 * 时间：2017-07-05
*/
include '../temp/config.php';
$fzx_id=$u['fzx_id'];
$i = $j = 0;
//判断是月查看还是单批次查看
if($_GET['cyd_id']=='all'){
    $sqlsee = "SELECT cy.json,cy.id,cy.sh_user_qz,cy.sh_user_qz_date,cy_rec.ys_result,cy_rec.bar_code,cy_rec.site_name FROM `cy` join cy_rec on cy.id=cy_rec.cyd_id where cy.fzx_id='{$fzx_id}' and cy.cy_date like '{$_GET['cy_date']}%' order by sh_user_qz_date desc";
}else{
    $sqlsee = "SELECT cy.json,cy.id,cy.sh_user_qz,cy.sh_user_qz_date,cy_rec.ys_result,cy_rec.bar_code,cy_rec.site_name FROM `cy` join cy_rec on cy.id=cy_rec.cyd_id where cy.fzx_id='{$fzx_id}' and cy.id='{$cyd_id}' order by sh_user_qz_date desc";
}

//表格数 行数
$per_line = 10;

$cy_users = $sy_danweis = array();
$rsee = $DB->query($sqlsee);
while($r = $DB->fetch_assoc($rsee)){
    //获取采样人 和单位
    if(!array_key_exists($r['id'],$cy_users)){
        $cy_user_info = $DB->fetch_one_assoc("SELECT concat_ws(',',`cy_user`,case when `cy_user2` = '' or `cy_user2` is null then null else `cy_user2` end) as `cy_user` FROM `cy` WHERE `id` = '$r[id]'");
        $cy_users[$r['id']] = $cy_user_info['cy_user'];
        //采样单位
        if(!empty($cy_user_info['cy_user'])&&!array_key_exists($cy_user_info['cy_user'],$sy_danweis)){
            $users_info = $DB->fetch_one_assoc("SELECT `bz` FROM `hn_users` WHERE `uid` = (SELECT `id` FROM `users` WHERE `userid` ='$cy_user_info[cy_user]')");
            $sy_danweis[$cy_user_info['cy_user']] = $users_info['bz'];
        }
    }
    $r['cy_user'] = $cy_users[$r['id']];
    $r['danwei'] = $sy_danweis[$r['cy_user']];
    if($j == $per_line){
        $i++ ;
        $j = 0 ;
    }
    $data[$i][$j++] = $r;
}
for($j;$j<$per_line;$j++){
    $data[$i][$j] = array();
}
//循环表头
foreach($data as $table_key=>$ji_lu){
    $ye = $table_key+1;
    $lines.="<div style='text-align:center;width:26cm;margin:0 auto;'>
            <div style='float:right;width:50px;font-size:16px;'> 监—4 </div>
            <h2>样品室样品登记台账</h2></div>
            <table style='margin-bottom:2px;padding:0;width:26cm;'>
              <tr>
                <td style='float:right;width:50px;font-size:15px;'>第{$ye} 页</td>
              </tr>
            </table>
            <table class='single'width='50%' style='text-align:center' align='center'>
                <tr>
                    <td width='10%'>编号</td>
                    <td width='20%'>样品名称</td>
                    <td width='20%'>送样单位</td>
                    <td width='10%'>送样人</td>
                    <td width='10%'>收样日期</td>
                    <td width='10%'>收样人</td>
                    <td width='12%'>检查结果</td>
                </tr> ";
    //循环行数据
    foreach ($ji_lu as $key => $value){
        $sy = json_decode($value['json'],true);
        $sy_shuju = $sy['sy'];
        if($sy_shuju!=''){
            $sy_danwei = $sy['sy']['sy_danwei'];
            $sy_user = $sy['sy']['sy_user'];
        }
        //检查结果 默认值
        if(empty($value['ys_result'])&&!empty($value['site_name'])){
            $value['ys_result'] = '合格';
        }
        $lines.="<tr>
        <td>{$value['bar_code']}</td>
        <td>{$value['site_name']}</td>
        <td>{$value['danwei']}</td>
        <td>{$value['cy_user']}</td>
        <td>{$value['sh_user_qz_date']}</td>
        <td>{$value['sh_user_qz']}</td>
        <td>{$value['ys_result']}</td>
       </tr>";
    }
$lines.= "</table><div style=\"page-break-before: always;\"><br />";
}
disp("cy/ypdj_taizhang.html");