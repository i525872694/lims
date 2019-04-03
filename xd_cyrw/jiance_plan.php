<?php
//监测计划页面
include("../temp/config.php");
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>'监测计划','href'=>"{$rooturl}/xd_cyrw/jiance_plan.php?plan_id={$_GET['plan_id']}");
$_SESSION['daohang']['jiance_plan'] = $trade_global['daohang'];
if(empty($_GET['plan_id'])){
    goback('请刷新页面重试');//没有传递id
}
$fzx_id     = $u['fzx_id'];
#########获取检测项目名称
$xm_name_arr    = [];
$xm_name_sql    = $DB->query("SELECT * FROM `assay_value` WHERE 1");
while ($xm_name_row = $DB->fetch_assoc($xm_name_sql)) {
    $xm_name_arr[$xm_name_row['id']]    = $xm_name_row['value_C'];
}
#########取出计划表信息
$plan_list  = $DB->fetch_one_assoc("SELECT * FROM `jiance_plan` WHERE `id`='{$_GET['plan_id']}'");
$cyd_id_str = trim($plan_list['cyd_id'],',');
if($plan_list['status'] =='已下达'){
    $xd_button_display  = "display:none;";
    $qz_display         = "";
}else{
    $xd_button_display  = "";
    $qz_display         = "display:none;";
}
if(empty($cyd_id_str)){
    goback('数据有问题，请联系系统管理员！');
}
#########取出采样详细信息
$plan_content_list  = $rec_zk_data  = [];
$plan_content_sql   = "SELECT cy.*,cr.*,cr.id as cid,cy.cy_date AS cyd_cy_date ,cr.assay_values 
                        FROM `cy` 
                        INNER JOIN `cy_rec` AS cr ON cy.id=cr.cyd_id 
                        WHERE `cy`.`id` in({$cyd_id_str})";
$plan_content_query = $DB->query($plan_content_sql);
while ($plan_content_row = $DB->fetch_assoc($plan_content_query)) {
    $cyd_id = $plan_content_row['cyd_id'];
    $sid    = $plan_content_row['sid'];
     $cid   = $plan_content_row['cid'];
    //采样时间
    if(!empty($plan_content_row['cyd_cy_date']) && $plan_content_row['cyd_cy_date']!='0000-00-00'){
        $plan_content_list['cy_date'][$plan_content_row['cyd_cy_date']]   = $plan_content_row['cyd_cy_date'];
    }
    
    if($sid > 0){
        $plan_content_list['sites_list'][$sid] = $sid;//站点数量
        $xm_id_arr=explode(',',$plan_content_row[assay_values]);
        $xm_count=count($xm_id_arr);
        $plan_content_list['group'][$cyd_id]['sites_name'][]= $plan_content_row['site_name']."(<span class='xmcount' onclick='update_xm($cid,\"{$plan_content_row['site_name']}\")'>$xm_count </span>)";//批次包含的站点名称
        $milieu_values_arr=explode(',',$plan_content_row['milieu_values']);
        if(empty($plan_content_list['group'][$cyd_id]['milieu_values'])){$plan_content_list['group'][$cyd_id]['milieu_values']=array();}
        $plan_content_list['group'][$cyd_id]['milieu_values'] = array_merge($plan_content_list['group'][$cyd_id]['milieu_values'],$milieu_values_arr);//批次包含的站点名称
    }
    
    //批次的任务类型名称
    // $plan_content_list['group']['type_name']     = ;
    //批次的采样人
    $cy_users   = $plan_content_row['cy_user'];
    if(!empty($plan_content_row['cy_user2'])){
        $cy_users    .= "、".$plan_content_row['cy_user2'];
    }
    $plan_content_list['group'][$cyd_id]['cy_users']    = $cy_users;
    //批次包含的现场检测项目
    $plan_content_list['group'][$cyd_id]['xcjc_value']  = $plan_content_row['xc_exam_value'];
    $plan_content_list['group'][$cyd_id]['all_values'] .= ",".$plan_content_row['assay_values'];
    $plan_content_list['group'][$cyd_id]['xc_huanjing']  = $plan_content_row['xc_huanjing'];
    //质量控制
    if($plan_content_row['sid']=='0'){
        $rec_zk_data['全程序空白'][]=$plan_content_row;
        //$all_bar_code_arr['全程序空白'][$plan_content_row['status']][]=$plan_content_row['bar_code'];
    }elseif($plan_content_row['sid']=='-3'){
        $rec_zk_data['标准样品'][]=$plan_content_row;
        //$all_bar_code_arr['标准样品'][$plan_content_row['status']][]=$plan_content_row['bar_code'];
    }elseif($plan_content_row['zk_flag']=='-6'){
        //$all_bar_code_arr[$plan_content_row['water_type']][$plan_content_row['status']][]=$plan_content_row['bar_code'];
        $rec_zk_data['现场平行'][]=$plan_content_row;
    }
    else{
        $rec_data[$plan_content_row['water_type']][]=$plan_content_row;
        //$all_bar_code_arr[$plan_content_row['water_type']][$plan_content_row['status']][]=$plan_content_row['bar_code'];
    }
}
//循环数组获取质控的参数
//print_rr($rec_zk_data);
$zkyq_str   = '';
if(!empty($rec_zk_data)){
    foreach($rec_zk_data as $key=>$value){
        $water_type='';
        $zk_all_jcxm_arr=array();//均需检测项目
        $bar_code_arr=$sites_name_list=array();
        $zk_way=$key;//质控类型
        foreach($value as $k=>$v){
            //获取编号和均测项目
            $bar_code_arr[]=$v['bar_code'];
            $sites_name_list[]    = $v['site_name'];
            if($zk_way=='室内平行'){
                $zk_vid_arr=explode(',',$v['snpx_item']);
            }else if($zk_way=='加标回收'){
                $zk_vid_arr=explode(',',$v['jbhs_item']);
            }else{
                $zk_vid_arr=explode(',',$v['assay_values']);
            }
            if(!empty($zk_all_jcxm_arr)){
                $zk_all_jcxm_arr= array_intersect($zk_vid_arr,$zk_all_jcxm_arr);
            }else{
                $zk_all_jcxm_arr= $zk_vid_arr;
            }
        }
        $zk_all_jcxm_vids   = $zk_all_jcxm_arr;
        if($zk_all_jcxm_vids!=''){
            //项目名称
            $xm_name_list    = [];
            foreach ($zk_all_jcxm_vids as $vid) {
                $xm_name_list[] = $xm_name_arr[$vid];
            }
            $xm_name    = implode('、',$xm_name_list);
            //站点名称
            $sites_name_str = implode('、',$sites_name_list);
            $zkyq_str   .=$zk_way.':<br/>&nbsp;&nbsp;相关站点：'.$sites_name_str.'<br>&nbsp;&nbsp;以上站点均需检测：'.$xm_name.'共'.count($zk_all_jcxm_arr).'项指标。<br/>';
            $jiance_str = '加测';
        }else{
            $zkyq_str   .=$zk_way.':<br/>';
            $jiance_str = '检测';
        }
        foreach($value as $k=>$v){
            //获取每个样品加测项目
            if($zk_way=='室内平行'){
                $zk_vid_arr=explode(',',$v['snpx_item']);
            }else if($zk_way=='加标回收'){
                $zk_vid_arr=explode(',',$v['jbhs_item']);
            }else{
                $zk_vid_arr=explode(',',$v['assay_values']);
            }
            $zk_diff_jcxm=array_diff($zk_vid_arr,$zk_all_jcxm_arr);
            if(!empty($zk_diff_jcxm)){
                $zk_add_jcxm=implode(',',$zk_diff_jcxm);
                //项目名称
                $xm_name_list    = [];
                foreach ($zk_all_jcxm_vids as $vid) {
                    $xm_name_list[] = $xm_name_arr[$vid];
                }
                $xm_name    = implode('、',$xm_name_list);
                $zkyq_str.='<font color="green"><b>&nbsp;'.$v['site_name'].'</b></font>'.$jiance_str.'项目:'.$xm_name.'计'.count($zk_diff_jcxm).'个<br/>';
            }
            
        }
    }
}
#####组装html代码
$plan_html  = '';
$tr_num = 0;
foreach ($plan_content_list['group'] as $key => $value) {
    $tr_num++;
    //批次站点名称
    $group_site_name= implode('、', $value['sites_name']);
    //现场检测项目名称
    $all_values = array_filter(array_unique(explode(',', $value['all_values'])));
    $xcjc_values= array_filter(array_unique(explode(',', $value['xcjc_value'])));
    $now_values = array_intersect($all_values, $xcjc_values);//实际现场检测项目
    $xcjc_value_name_arr    = [];
    foreach ($now_values as $vid) {
        $xcjc_value_name_arr[]  = $xm_name_arr[$vid];
    }
    $xcjc_value_name_str= implode('、', $xcjc_value_name_arr);
    $xc_huanjing = '';
    $value['milieu_values'] = array_filter(array_unique($value['milieu_values']));
    foreach($value['milieu_values'] as $k=>$v){
        $xc_huanjing .= array_search($v,$global['xc_huanjing']).'、';
    }
    $xc_huanjing = rtrim($xc_huanjing,'、');
    if($tr_num == '1'){
        $date_colspan   = count($plan_content_list['group']);//采样时间合并行
        //采样时间
        $cy_date    = '';
        if(count($plan_content_list['cy_date']) == '1'){
            $cy_date    = end($plan_content_list['cy_date']);
        }else{
            $begin_date = min($plan_content_list['cy_date']);
            $end_date   = max($plan_content_list['cy_date']);
            $cy_date    = "{$begin_date}~{$end_date}";
        }
        //站点数量
        $site_num   = count($plan_content_list['sites_list']);
        $plan_html  .= "<tr>
                        <td rowspan='{$date_colspan}'>{$cy_date}({$site_num}个水样)</td>
                        <td align=left>{$group_site_name}</td>
                        <td>{$value['cy_users']}</td>
                        <td>{$xcjc_value_name_str}</td>
                        <td>{$xc_huanjing}</td>
                    </tr>";
    }else{
        $plan_html  .= "<tr>
                        <td align=left>{$group_site_name}</td>
                        <td>{$value['cy_users']}</td>
                        <td>{$xcjc_value_name_str}</td>
                        <td>{$xc_huanjing}</td>
                    </tr>";
    }
    
}
disp('xd_cyrw/jiance_plan.html');
?>
