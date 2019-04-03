<?php
/**
 * 功能：样品流转登记表
 * 作者：gongyanxiao
 * 时间：2017-07-06
*/
include '../temp/config.php';
include_once INC_DIR.'/cy_func.php';
//导航
$trade_global['daohang'][]  = array('icon'=>'','html'=>'样品流转登记表','href'=>"./xd_csrw/yp_liuzhuan_dengji_list.php?cy_date={$_GET['cy_date']}&year={$_GET['year']}&month={$_GET['month']}");
$_SESSION['daohang']['yp_liuzhuan_dengji_list']  = $trade_global['daohang'];
if(empty($u['userid'])){
    nologin();
}
$fzx_id=$u['fzx_id'];//分中心id
//当前年和当前月
$y= date('Y');
$m = date('m');

if( !$_GET['cy_date'] ){
    $_GET['cy_date'] = date( "Y-m" );
}
if( !$_GET['year'] ){
    $_GET["year"] = date( "Y" );
}
if( !$_GET['month'] ){
    $_GET["month"] = date( "m" );
}
$sql ="SELECT cy_rec.cy_date,cy_rec.json,cy.sh_user_qz,ap.userid,ap.td31,ap.assay_element,ao.bar_code,ao.vid 
from assay_order as ao 
left join assay_pay as ap on ao.tid=ap.id 
left join cy_rec on ao.cid=cy_rec.id 
left join cy on ao.cyd_id=cy.id";
$sql .= " where cy.fzx_id='{$fzx_id}' AND (ao.hy_flag>=0 or ao.hy_flag=-6) AND cy_rec.cy_date LIKE '{$_GET['cy_date']}%' ORDER BY ap.td31 desc ";
$shuju = $DB->query($sql);
while($row = $DB->fetch_assoc($shuju)){
    //$guihuan = json_decode($row['json'],true);
    $guihuan_date = '';
    if(!empty($row['td31'])){
        $guihuan_date = date('Y-m',strtotime($row['td31'])).'-'.date('t');
    }
    
    $cy_in[$row['cy_date']][$row['userid']][$guihuan_date]['vid'][$row['vid']]   = $row['assay_element'];
    $cy_in[$row['cy_date']][$row['userid']][$guihuan_date]['bar_code'][]=$row['bar_code'];
    $cy_in[$row['cy_date']][$row['userid']][$guihuan_date]['ly_date']=$row['td31'];
    $cy_in[$row['cy_date']][$row['userid']][$guihuan_date]['yp_gl_user']=$row['sh_user_qz'];
  
}
foreach($cy_in as $cy_date=>$userid){
    foreach($userid as $ly_ren=>$guihuan_date){
        foreach($guihuan_date as $gui_date=>$v){
            $xm_name    = implode('、', $v['vid']);
            $bar_code_num = count($v['bar_code']);
            sort($v['bar_code']);
            $bar_code = get_short_barcode(array_unique($v['bar_code']));
            $lines   .= temp("xd_csrw/yp_liuzhuan_list_line.html");
        }
        
    } 
}
//所有年
$year_data[] = $_GET["year"];
for($i=$begin_year;$i<=date('Y');$i++ ){
    if( $i != $_GET['year'] ){
        $year_data[] = $i;
    }
}
$month_data[] = $_GET["month"];
$year_list = disp_options( $year_data );
//所有月
$rs_month = $DB->fetch_one_assoc("SELECT month(cy_date) as m FROM `cy_rec` WHERE `fzx_id`='$fzx_id' AND year(cy_date)='{$_GET['year']}' AND month(cy_date)>'".date('m')."' GROUP BY month(cy_date) ORDER BY month(cy_date) DESC LIMIT 1");
if($rs_month['m']){
    $month_max  = $rs_month['m'];
}else{
    $month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
}
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );


$tab_active=$_GET['tab']?$_GET['tab']:0;
if($_GET['type']){
$type=$_GET['type'];
$hide='is_hide';
$title=$_GET['title'];
$mysql_table='files';
$id = $_GET['id'];
//循环数据 查看
$info=$DB->query("select * from `files` where `fzx_id`='$u[fzx_id]' and `type`='$type'");
$i=1;
while($row = $DB->fetch_assoc($info)){
	$tid = $row['id'];
	$files=$caozuo='';
	$caozuo.="<a class='red icon-remove bigger-130' onClick=\"del('$mysql_table','$tid','$type')\" title='删除'></a> ";
	
	$path=$rooturl.'/app_modal/upload_file';
	$files = json_decode($row['data'],true);
	$files_str = '';
	if(count($files)>=1){
		foreach($files as $f_k=>$f_v){
			$url=$path.'/'.$f_v['newname'];
			$files_str.="<center><a href='$url' target='_blank' download='$f_v[oldname]'>$f_v[oldname]</a></center>";
		}
	}

	$files_lines.=<<<EOF
	<tr>
		<td>$i</td>
		<td class='bdname'>$row[name]</td>
		<td class='file'>$files_str</td>
		<td class='beizhu'>$row[name2]</td>
		<td>$caozuo</td>
	</tr>
EOF;
	$i++;
}
$mysql_ziduan=$type;
$content_str='files_content'.$tab_active;
$$content_str=temp('user_manager/file'); 
}


disp("xd_csrw/yp_liuzhuan_dengji_list.html");
?>