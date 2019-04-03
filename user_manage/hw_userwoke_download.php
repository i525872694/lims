<?php
include '../temp/config.php';
header("Content-type: application/octet-stream;charset=gbk");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=上岗项目统计_".$_GET['fzx_name'].'.xls');
$fzx_id = $_GET['fzx_id'];
$fzx_name = $_GET['fzx_name'];
if(!empty($fzx_id) && $fzx_id !='全部'){
	$fzx_sql = " AND u.`fzx_id` = '{$fzx_id}'";
}else{
	$fzx_sql = "";
}
$sql="SELECT u.userid,u.id uid,  value_C,x.id as fid,if(u.id = x.`userid`,sgz_date,sgz_date2) sgz_date
FROM users u
LEFT JOIN xmfa x ON ( u.id = x.`userid`
OR u.id = x.userid2 )
-- LEFT JOIN assay_method m ON x.fangfa = m.id method_number,
LEFT JOIN assay_value v ON x.xmid = v.id
where 1 $fzx_sql and u.group!='0' AND u.group!='测试组' and value_C !=''  
 GROUP BY u.userid, x.xmid
";
$re = $DB->query($sql);
$i = 0;
while($data = $DB->fetch_assoc($re)){
	if($data['uid'] == $uid){
		$arr[$data['uid']][$i+1] = $data;
	}else{
		$arr[$data['uid']][$i] = $data;
	}
	$i++;
	$uid = $data['id'];
}
$i = 1;
foreach($arr as $key=>$value){
	$xcont = count($value);
	foreach($value as $k=>$v){
		$name = $v['userid'];
	}
	$lines .= temp('user_manager/hw_userwork_down_line');
	$i++;
}
disp ("user_manager/hw_userwork_down");