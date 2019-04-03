<?php
/*
*传过来一个数组，将数组内容上传到yqdaoru表中，无返回值
*$zhi,$pid必须填的参数
*$zhi为数组格式为array([编号]=>数据)，
	或array([编号]=array([vid]=>数据))
	或array([vid]=>array([编号]=>数据))
	其他格式不导入
*/
function yqdaoru($zhi,$zr_lie=''){
	global $DB,$pdf_rs,$zr_vid;
	$fzx_id=FZX_ID;
	$pid= $pdf_rs['id'];
	if($zr_lie==''){
		$zr_lie='vd0';
	}
	if(count($zhi)>0&&$pid!=''){
		$yqdaoru_query = $DB->query("SELECT * FROM `yqdaoru` WHERE pid='".$pid."' AND zr_lie='".$zr_lie."'");//查询仪器导入表中是否有这张pdf的数据
		$yqdaoru_nums   = $DB->num_rows($yqdaoru_query);
		if($yqdaoru_nums<=0){//没有这张pdf的数据就执行插入操作
			foreach($zhi as $key1=>$valZhi){//将获取到的数据全部导入到表“yqdaoru”中
				if(is_array($valZhi)){//二维数组
					foreach($valZhi as $key2=>$shuZhi){
						if(is_numeric($key2)){//区分vid和样品名称 vid是纯数字 样品编号字母-数字 用is_numeric()区分
                            $barCode = $key1;
                            $vid     = $key2;
                        }elseif(is_numeric($key1)){
                            $barCode = $key2;
                            $vid     = $key1;
                        }else{
                            $barCode = $vid = '';
                        }
						if($barCode!=''&&$vid!=''){
							//插入数据到yqdaoru表
							$queryInYq = $DB->query("INSERT INTO `yqdaoru` (fzx_id,vid,create_time,bar_code,zr_lie,data,pid) VALUES('".$fzx_id."','".$vid."','".date('Y-m-d')."','".$barCode."','".$zr_lie."','".$shuZhi."','".$pid."')");
						}
					}
				}
				else{//一维数组的情况
					if($valZhi==''){
						continue;
					}
					if($key1!=''){
						//插入数据到yqdaoru表
						$queryInYq = $DB->query("INSERT INTO `yqdaoru` (fzx_id,vid,create_time,bar_code,zr_lie,data,pid) VALUES('".$fzx_id."','".$zr_vid."','".date('Y-m-d')."','".$key1."','".$zr_lie."','".$valZhi."','".$pid."')");
					}
				}
			}
		}
	}
}
/*
*$tid化验单号
*传过来化验单号自动到“yqdaoru”表中将数据载入到化验单中
*返回值为 载入的数据个数，未载入返回0
**/
function zrsj($tid,$fzx_id){
	global $DB;
	$count    = 0;
	$aoidArr  =$pid_arr= array();
	$yq_pid_arr=[];
	$hydpdf_query=$DB->query("SELECT `pid` FROM `hydpdf` WHERE tid='{$tid}' GROUP BY `pid`");
	while($hydpdf_rs=$DB->fetch_assoc($hydpdf_query)){
		$pid_arr[]=$hydpdf_rs['pid'];
	}
	$yq2_pids = [];
	$sql = "SELECT DISTINCT yq2.pid FROM yqdaoru yq2 JOIN assay_order ao ON yq2.bar_code=ao.bar_code WHERE ao.tid={$tid}";
	$query = $DB->query($sql);
	while ($row = $DB->fetch_assoc($query)) {
		$yq2_pids[] = $row['pid'];
	}
	$yq2_pids = !count($yq2_pids) ? 0 : implode(',', $yq2_pids);
	$yq_pid_query=$DB->query("SELECT yq.pid,count(yq.bar_code) AS code_bar_num FROM yqdaoru yq WHERE yq.pid IN ({$yq2_pids}) GROUP BY yq.pid");//查询当前化验单关联的图谱
	//将编号名称不为空白、质控样，样品对应的图谱id存入数组
	while($yq_pid_res=$DB->fetch_assoc($yq_pid_query)){
		if(!in_array($yq_pid_res['pid'],$yq_pid_arr)&&(!stristr($yq_pid_res['bar_code'],'KONGBAI')||$yq_pid_res['bar_code_num']==1)&&(!stristr($yq_pid_res['bar_code'],'ZHIKONG')||$yq_pid_res['bar_code_num']==1)){
			$yq_pid_arr[]=$yq_pid_res['pid'];
		}
	}
	if(!empty($pid_arr)){
		$pid_str=implode(',',$pid_arr);
		$pid_sql=" OR yq.pid in (".$pid_str.")";
	}
	//组合sql语句，加载图谱的数据必须是当前化验单中有对应的图谱
	if(!empty($yq_pid_arr)){
		$yq_pid_str = implode(',',$yq_pid_arr);
		$yq_pid_sql= " AND yq.pid in (".$yq_pid_str.") ";
	}else{
		$yq_pid_sql=" AND yq.pid in (0) ";
	}
	$sqlDaoru = "SELECT yq.*,yq.id as yq_id,ao.id AS aid,ao.* FROM `yqdaoru` AS yq 
	INNER JOIN `assay_order` AS ao ON (yq.bar_code=replace(ao.bar_code, '*', '') AND yq.vid=ao.vid".$yq_pid_sql.")
	WHERE yq.fzx_id='".$fzx_id."' AND ao.tid='".$tid."' AND (yq.create_time>='".date("Y-m-d",strtotime("-10 day"))."' ".$pid_sql." ) ORDER BY yq.pid DESC";
    // die($sqlDaoru);
	$queryDr  = $DB->query($sqlDaoru);
	$drRowS   = $DB->num_rows($queryDr);
	//echo $drRowS;
	if($drRowS>0){//说明“yqdaoru”表中有这张化验单的数据
		while($rsDr = $DB->fetch_assoc($queryDr)){
			//echo $aoidArr[$rsDr['aid']][$rsDr['zr_lie']];
			if($rsDr['data']!=''&&$aoidArr[$rsDr['aid']][$rsDr['zr_lie']]!='is_load'){//数组的判断是为了保证获取最后一次打印的pdf的数据
				//更新assay_order表的原始数据
				if($rsDr['zr_lie']=='vd0'){
					$order_query=$DB->query("UPDATE `assay_order` SET _vd0='".$rsDr['data']."',".$rsDr['zr_lie']."='".$rsDr['data']."' WHERE id='".$rsDr['aid']."' ");
				}else{
					$order_query=$DB->query("UPDATE `assay_order` SET ".$rsDr['zr_lie']."='".$rsDr['data']."' WHERE id='".$rsDr['aid']."' ");
				}
				//$update_rows = $DB->affected_rows();//更新的条数
				if($order_query>0){//若插入数据库成功则把此pdf自动关联到化验单中
					$count++;
					$aoidArr[$rsDr['aid']][$rsDr['zr_lie']] ='is_load' ;//证明assay_order表的这条记录的这个字段已经被载入过
					//化验单关联pdf
					if(!@in_array($rsDr['pid'],$pidArr)){
						$queryHp = $DB->query("SELECT * FROM `hydpdf` WHERE pid='".$rsDr['pid']."' AND tid='".$tid."'");
						$hypRows = $DB->num_rows($queryHp);
						if($hypRows<='0'){
							$pdf_rs = $DB->fetch_one_assoc("SELECT beizhu FROM `pdf` WHERE id='".$rsDr['pid']."'");
							$DB->query("INSERT INTO `hydpdf` (tid,pid,note) VALUES('".$tid."','".$rsDr['pid']."','".$pdf_rs['beizhu']."')");
							$pidArr[] = $rsDr['pid'];
						}
					}
				}
			}
		}
	}
	return $count;
}
//去除多余的0
function del0($s)  
{  
    $s = trim(strval($s));  
    if (preg_match('#^-?\d+?\.0+$#', $s)) {  
        return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);  
    }   
    if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {  
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);  
    }  
    return $s;  
} 
/*
*功能：对传过来的参数进行正则的样品编号匹配
*返回值：匹配到后返回匹配的样品编号，否则返回false
**/
function match_bar($bar){ //0.8C_ZKY
    $bar = str_replace('*', '', $bar);
    if(preg_match("/^\d{2}?[A-Z]{2}(\d{4})?\d{2}\d{3}(加碱)?(PJ|P|J|\+)?(加碱)?$/",$bar,$bianHao)
        ||preg_match("/0.8C/",$bar,$bianHao)
        ||preg_match("/KB\d{8}/",$bar,$bianHao)
        ||preg_match("/ZKY\d{8}/",$bar,$bianHao)
		||preg_match("/0.2C/",$bar,$bianHao)
		){
        return $bianHao[0];
    }else{
        return false;
    }
}
/**
 * 功能：获得短位的编码
 * 例如：原子吸收WFX-210 编码输入不完全
 * 返回值：匹配到后返回匹配的样品编号，否则返回false
 */
function short_match_bar($bar){
	$bar = str_replace('*', '', $bar);
	if(preg_match("/[A-Z]{2}\d{5}(PJ|P|J|\+)?(加碱)?/",$bar,$bianHao)){
		return $bianHao[0];
	}else{
		return false;
	}
}
/*
*功能：对传过来的参数进行正则的样品仪器编号匹配
*返回值：匹配到后返回匹配的样品仪器编号，否则返回false
**/
function match_yqbar($bar){
	if(preg_match("/\d{8,11}[.][A-Z]{1}/",$bar,$bianHao)){
		return $bianHao[0];
	}else{
		return false;
	}
}
function match_yqbar8($bar){
	if(preg_match("/\d{8}[.][A-Z]{1}/",$bar,$bianHao)){
		return $bianHao[0];
	}else{
		return false;
	}
}
/*
*功能：载入时把获取的编号和项目存储到pdf表的pdf_detail字段
**/
function update_pdf_detail($pdf_id,$bar_code_arr,$jcxm_arr){
	global $DB;
	$detail_data=array();
	foreach($bar_code_arr as $key=>$value){
		if(stristr($value,'P')||stristr($value,'J')||stristr($value,'KB')){
			$zk_bar_arr[]=$value;
			unset($bar_code_arr[$key]);
		}
	}
	if(!empty($bar_code_arr)){
		$detail_data['bar_code']=get_short_barcode($bar_code_arr,'0');
	}
	if(!empty($zk_bar_arr)){
		$zk_bar_str='<br/>'.implode('、',$zk_bar_arr);
		$detail_data['bar_code'].=$zk_bar_str;
	}
	if(!empty($jcxm_arr)){
		$detail_data['jcxm']=implode("、",$jcxm_arr);
		$detail_data['jcxm']=str_replace(array('"',"'"),array('\"',"\'"),$detail_data['jcxm']);
	}
	if(!empty($detail_data)&&$pdf_id){
		$json_data=JSON($detail_data);
		$DB->query("UPDATE pdf SET pdf_detail='".$json_data."' WHERE id='".$pdf_id."'");
	}
	
}

//科学计数法
function NumToStr($num){
if (stripos($num,'E')===false) return $num;
//出现科学计数法，还原成字符串
$num = trim(preg_replace('/[=\'"]/','',$num,1),'"');
$result = "";
while ($num > 0){
$v = $num - floor($num / 10)*10;
$num = floor($num / 10);
$result = $v . $result;
}
return $result;
}
