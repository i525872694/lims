<?php
//默认成果统计表模板(项目横向排列)
$page_tr    = '12';//每页显示行数  //打印行数为12
$page_td    = empty($_GET['cols_num'])?'13':$_GET['cols_num'];//每页显示列数（数据不足时补空白列） //打印列数为13


$return_result_html = <<<EOF
<header class="bs-docs-nav navbar navbar-static-top Noprint" id="top"></header>
<div style="width:100%;text-align:center" class='Noprint'> 
<input type='text' id='cols_num'>
<button class='btn btn-primary btn-sm' onclick='set_cols()'>设置</button> 
<button onclick="return window.print();" class="btn btn-primary btn-sm">打印</button>
</div>
EOF;
$result_bt_list = ['site_name'=>'采样地点','cy_date'=>'采样日期','tian_qi'=>'天气','qi_wen'=>'气温','qi_ya'=>'气压','liu_l'=>'流量','feng_su'=>'风速','feng_xiang'=>'风向','liu_s'=>'流速','water_height'=>'水位'];//,'xz_area'=>'分区'
$result_bt_unit = array(
    'qi_wen'=>'℃',
    'qi_ya'=>'kPa',
    'liu_l'=>'m³/s',
    'feng_su'=>'m/s',
    'feng_xiang'=>'o',
    'liu_s'=>'m/s',
    'water_height'=>'m'
);
$allow_show = array('site_name','cy_date');
$tr_cid_num = count($report_rec_list);
$td_bt_num  = count($result_bt_list)+1;//增加一列序号
$td_xm_num  = count($xm_px_arr);

$all_cols   = $page_td;//总合并列
$xm_cols    = $td_xm_num;
$mintd_width= 50;//td最小宽度
if($fenye == 'no'){//不分页
    $page_tr    = $tr_cid_num;
    $page_td    = $td_xm_num+$td_bt_num;
}
$tr_page_num= ceil($tr_cid_num/$page_tr);
$one_xm_page_td = $page_td - $td_bt_num;
$two_xm_page_td = $page_td - (count($allow_show)+1);
$td_page_num= ceil(($td_xm_num-$one_xm_page_td)/$two_xm_page_td)+1;
//print_rr($report_rec_list);
//print_rr($report_order_list);
//print_rr($xm_name_list);
//print_rr($xm_px_arr);
$tr_num = 0;
for ($tr_page_i=1; $tr_page_i <= $tr_page_num; $tr_page_i++) {
    $tr_begin_num   = ($tr_page_i-1)*$page_tr;
    $tr_arr = array_slice($report_rec_list,$tr_begin_num,$page_tr,true);
    for ($td_page_i=1; $td_page_i <= $td_page_num; $td_page_i++) {
        $xm_page_td = $td_page_i ==1 ? $one_xm_page_td : $two_xm_page_td ;
        $td_begin_num   = ($td_page_i-1)*$one_xm_page_td;
        if($td_page_i > 2){
            $td_begin_num   = (($td_page_i-2)*$xm_page_td) + $one_xm_page_td;
        }
        $td_arr = array_slice($xm_px_arr,$td_begin_num,$xm_page_td);
        $result_html_line  = $xm_danwei_td = '';
        $jilu_bt    = 'yes';
        $bt_td      = "<td rowspan='2'>序号</td>";
        foreach ($tr_arr as $key_cid => $value_rec_list) {
            $tr_num++;
            $result_html_line .= "<tr><td>{$tr_num}</td>";
            //循环表格的现场信息
            foreach ($result_bt_list as $key_filed => $value_filed_name) {
                if(!in_array($key_filed,$allow_show)&&$td_page_i!=1){
                    continue;
                }
                if($jilu_bt == 'yes'){
                    $bt_rowspan = 2;
                    if(in_array($key_filed,array_keys($result_bt_unit))){
                        $bt_rowspan = 1;
                        $xm_danwei_td .= "<td>".$result_bt_unit[$key_filed]."</td>";
                    }
                    $bt_td   .= "<td rowspan='$bt_rowspan'>{$value_filed_name}</td>";
                }
                $result_html_line .= "<td>{$value_rec_list[$key_filed]}</td>";
            }
            //循环站点的检测项目信息
            foreach ($td_arr as $value_vid) {
                $tmp_result_list    = $report_order_list[$key_cid][$value_vid];
                if(in_array($key_cid,array_keys($px_arr))){
                    $xcpx_value = $report_order_list[$px_arr[$key_cid]][$value_vid];
                    if(!empty($xcpx_value['vd0'])){
                        $tmp_result_list = $xcpx_value;
                    }
                }
                if($jilu_bt == 'yes'){
                    $tmp_result_list['unit']=$tmp_result_list['unit']?$tmp_result_list['unit']:'/';
                    $bt_td          .= "<td>{$xm_name_list[$value_vid]}</td>";
                    $xm_danwei_td   .= "<td>{$tmp_result_list['unit']}</td>";
                }
                $vd0_td_attr  = '';
                if(!empty($tmp_result_list['tid'])){
                    if($tmp_result_list['vd0'] == ''){
                        $tmp_result_list['vd0'] = "<font color='#9F9D97'>{$tmp_result_list['over']}</font>";
                    }
                    if(in_array($value_vid,array('97','99','114','117','94','98'))){
                        $tmp_result_list['tid'] = '';
                    }
                    //超标项目
                    if($tmp_result_list['pingjia']['status'] == 'no'){
                        $tmp_result_list['vd0'] = "<span class='chaobiao_vd0'>{$tmp_result_list['vd0']}</span>";
                        $vd0_td_attr  = "class='vd0_button chaobiao_vd0' tid='{$tmp_result_list['tid']}' cyd_id='{$value_rec_list['cyd_id']}' title='点击查看具体化验单'";
                    }else{
                        $vd0_td_attr  = "class='vd0_button' tid='{$tmp_result_list['tid']}' cyd_id='{$value_rec_list['cyd_id']}'";
                    }
                }else{
                    $tmp_result_list['vd0'] = '/';
                }
                $result_html_line .= "<td {$vd0_td_attr}>{$tmp_result_list['vd0']}</td>";
            }
            //填充空白列
            if(count($td_arr) < $xm_page_td){
                for ($i=0; $i < ($xm_page_td-count($td_arr)); $i++) {
                    if($jilu_bt == 'yes'){
                        $bt_td          .= "<td></td>";
                        $xm_danwei_td   .= "<td></td>";
                    }
                    $result_html_line   .= "<td></td>";
                }
            }
            $jilu_bt    = 'no';
            $result_html_line .= "</tr>";
        }
        $return_result_html .= temp('data_statistics/result_moren_mb.html');
        //除最后一页，每页都好加打印换行
        if($tr_page_i != $tr_page_num || $td_page_i!=$td_page_num){
            $return_result_html .= "<div class='print_fenye'></div>";
        }
    }
}
?>
