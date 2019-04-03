<?php
/*
**作者：高培华
**时间：2015/6/5
**作用：用来对人员的排序
**
*/
require '../temp/config.php';
$fzx_id	= $_SESSION['u']['fzx_id'];
	if(!empty($_POST['jid']) && $_POST['px_id'] >= 0 && is_numeric($_POST['jid']) && is_numeric($_POST['px_id'])){
		if($_POST['s_px']){
			//要区分分中心
			if(!empty($_POST['fzx'])){
				$fzx_id	= $_POST['fzx'];
			}
			//$all= "select `jid`,`px_id` from hn_users where (px_id>='{$_POST['px_id']}' OR px_id='') AND `jid`!='{$_POST['jid']}' ORDER BY px_id";
			$all="select a.`jid`,a.`px_id` from `hn_users` as a right join users as b on a.uid=b.id where b.group!='0' AND b.group!='测试组' AND b.fzx_id='{$fzx_id}' AND (a.px_id>='{$_POST['px_id']}' OR a.px_id='') AND a.`jid`!='{$_POST['jid']}' ORDER BY  b.fzx_id,a.px_id";
			$i	= $_POST['px_id'];
			$re = $DB->query($all);
			$update	= '';
			while($r = $DB->fetch_assoc($re)){
				$i++;
				//$update	.= "UPDATE `hn_users` SET `px_id`='{$i}' WHERE `jid`='{$r['jid']}';";
				if(!empty($r['jid'])){
					$DB->query("UPDATE `hn_users` SET `px_id`='{$i}' WHERE `jid`='{$r['jid']}';");
				}
				/*//后移
				if($_POST['px_id'] > $_POST['s_px']){
					if($r['px_id'] > $_POST['s_px'] && $r['px_id'] <= $_POST['px_id'])
						$DB->query("update hn_users set `px_id`=$r[px_id]-1 where `jid`=$r[jid] ");			
				}//前移
				elseif($_POST['px_id'] < $_POST['s_px']){
					if($r['px_id'] >= $_POST['px_id'] && $r['px_id'] < $_POST['s_px'])
						$DB->query("update hn_users set `px_id`=($r[px_id]+1) where `jid`=$r[jid] ");				
				} */
			}
			//echo $update	.= "UPDATE `hn_users` SET `px_id`='{$_POST['px_id']}' WHERE `jid`='{$_POST['jid']}';";
			//mysql_query($update);
		}
		$sql = 'update hn_users set `px_id`='.$_POST['px_id'].' where `jid`='.$_POST['jid'];
		if($DB->query($sql)){
			echo 'ok';
		}else{
			echo 'wrong';
		}
	}
?>