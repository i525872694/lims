<?php
/**
 * 功能：添加站点
 * 描述：添加新站点（可添加多条），并录入一些站点的常规信息及关联项目
*/
include '../temp/config.php';
require_once "$rootdir/inc/site_func.php";
$trade_global['daohang'][] = array('icon'=>'','html'=>'添加站点','href'=>"site/group_add_sites.php?site_type=0");
$_SESSION['daohang']['group_add_sites']	= $trade_global['daohang'];

$fzx_id	= FZX_ID;//中心
$site_type	= $_GET['site_type']?$_GET['site_type']:'1';//任务类型
$leix	= get_syleixing('','123');//获取水样类型下拉菜单
//自来水的站点添加信息
$site_content	= "<tr id=\"dzd\">
						<td align=\"center\" id=\"wuyongde\">
							站点名称：<input type=\"text\" onblur='site_nameyz(this)' name=\"site_name[]\"  placeholder=\"不能为空\" required=\"required\"  value=\"\">
							<span style='color:#ff3300' class='name_chongming_tishi'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
						</td>
						<td align=\"center\" >水样类型：<select name=\"water_type[]\" >$leix</select></td>
                        <td>站点地址：<input type='text' name='site_address[]' value='' /></td>
						<!-- <td><button type='button' class='btn btn-xs btn-primary'>更多信息</button></td> -->
					</tr>
                    <tr>
                        <td>站点编码：<input type='text' name='site_code[]' value='' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>垂线编号：<input name=\"site_line[]\" type=\"text\"value=''  placeholder=\"上:1 中:2 下:3\"></td>
                        <td>层面编号：<input name=\"site_vertical[]\" type=\"text\" value='' placeholder=\"左:1 中:2 右:3\"></td>
                    </tr>";
####################获取模板
$xmmb_sql = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id' " );
while( $row = $DB->fetch_assoc( $xmmb_sql ) ) {
	$mbxm	.= "<option value='{$row['module_value1']}'>{$row['module_value2']}</option> ";
}
###############获取检测项目
$xm_sql		= "SELECT av.id as vid,av.value_C 
				FROM `xmfa` 
				INNER JOIN `assay_value` as av on xmfa.xmid=av.id 
				WHERE xmfa.fzx_id='$fzx_id' 
				group by xmfa.xmid 
				ORDER BY xmfa.xmid";
$xm_query	= $DB->query($xm_sql);
while($xm_row = $DB->fetch_assoc($xm_query)){
	$xm	.= "<label class='xm_label'>
				<input name='xid[]'  value='{$xm_row['vid']}' type='checkbox' onclick=\"return tjxm();\">{$xm_row['value_C']}
			</label>";
}
disp('site/group_add_sites.html');
?>
