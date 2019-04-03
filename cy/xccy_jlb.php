<?php
/*
**用途：云南现场采样记录表
*/
include '../temp/config.php';
$fzx_id=$u['fzx_id'];
$cyd_id=$_GET['cyd_id'];
$rq_value=array();$year=date(Y);$xc_str='';
$sql_value="select id,rq_name,bcj from rq_value where fzx_id={$fzx_id}";//查询所有容器信息
$query_value=$DB->query($sql_value);
while($rs_value = $DB->fetch_assoc($query_value)){
    $data.="<td style=\"white-space:nowrap\">".$rs_value['rq_name']."</td>";
    $bcj.="<td>".$rs_value['bcj']."</td>";
    $rq_value[]=$rs_value['id'];
}
//现场项目字符串
$sql_xc="select ap.assay_element,ao.vd0,ao.cid from assay_pay ap join assay_order ao on ao.tid=ap.id where ap.cyd_id='{$cyd_id}'  and ap.is_xcjc='1'";
$query_xc=$DB->query($sql_xc);
while($rs_xc = $DB->fetch_assoc($query_xc)){
    $xc_str[$rs_xc['cid']] .=$rs_xc['assay_element'].":".$rs_xc['vd0']."; ";
}
$sql="SELECT cr.*,s.area,s.site_address,s.jingdu,s.weidu,cy.cy_user_qz,cy.ys_date,cy.sy_user_qz,cy.jy_user,cy.sh_user_qz,cy.sh_user_qz_date,cy.sc_qz,cy.sc_qz_date from cy join cy_rec cr on cy.id=cr.cyd_id join sites s on s.id=cr.sid where cr.cyd_id='$cyd_id'";
$query=$DB->query($sql);
while($rs = $DB->fetch_assoc($query)){
    $header=array();$sum=0;$duihao='';
    $header=$rs;
    $header['area']=$rs['area']?$rs['area']:"____";//流域
    $header['water_system']=$rs['water_system']?$rs['water_system']:"____";//水系
    $header['river_name']=$rs['river_name']?$rs['river_name']:"____河";//河流名称
    $header['site_address']=$rs['site_address']?$rs['site_address']:"____市 ____区 ____镇 ____村";//站址
    $header['tian_qi']=$rs['tian_qi']?$rs['tian_qi']:"晴 雨 阴";//天气
    $cydate=strtotime($rs['cy_date'].$rs['cy_time']);
    $header['cy_riqi']=date('Y',$cydate)."年".date('m',$cydate)."月".date('d',$cydate)."日 ".date('H',$cydate)."时".date('i',$cydate)."分".date('s',$cydate)."秒";
    $ysdate=strtotime($rs['ys_date']);
    $header['ys_date']=date('Y',$ysdate)."年".date('m',$ysdate)."月".date('d',$ysdate)."日";
    $json=json_decode($rs['json'],true);
    //$header['status']=$json['shuiti']['shuiti_zhuangtai']?$json['shuiti']['shuiti_zhuangtai']:'';//json取水体状态
    foreach($rq_value as $val){
       if(array_key_exists($val,$json['rq'])){
            $duihao.="<td><center>√</center></td>";
        }else{
            $duihao.="<td> </td>";
        }
    }
    if(empty($header['ping'])){
        $sum=array_sum($json['rq']);
        $header['ping']=$sum;
    }
    $header['xc_str']=$xc_str[$rs['id']];//现场项目数据
    $line.=temp("cy/xccy_jlb_line");
}
disp("cy/xccy_jlb");
?>