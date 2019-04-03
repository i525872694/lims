<?php
include("../temp/config.php");
require_once("../inc/cy_func.php");
$hyd_zt = array("已复核","已审核","已完成");
if($_POST['cyd_id']){
	$cy_json = $tishi = $tishi1= '';
	$avarr = $geng = array();
	$cyd = get_cyd( $_POST['cyd_id'] );
	$xmfb = json_decode($cyd['xmfb'],true);//取出历史已有的分包数据
	if( count($xmfb) ){
		foreach($xmfb as $av=>$ff){
			$fbfzx= $ff;
			$avarr[] = $av;
		}
	}
	
	//取出用户名称
	$users = array();
	$usql = $DB->query("SELECT * from users");
	while($u = $DB->fetch_assoc($usql)){
		$users[$u['id']] = $u['userid'];
	}
	//显示分中心名称列表，必须放在这里，否则变量$fbfzx没有值
	$zxsql = $DB->query("SELECT * from hub_info ");
	$fzxarr = array();
	while($rezx = $DB->fetch_assoc($zxsql)){
		$fzxarr[$rezx['id']] = $rezx['hub_name'];
	}
	if($_POST['vid']){
		foreach($_POST['vid'] as $vv1){
			$cy_json[$vv1] = $_POST['fzx'];
			if(!in_array($vv1,$avarr)){
				$geng[]=$vv1;//哪个项目需要处理
			}else{
				if($_POST['fzx'] != $fbfzx){
					$geng[]=$vv1;
				}
			}
		}
		//撤回由本分中心检测的项目
		$che_hui = array_diff($avarr,$_POST['vid']);
		$cy_json_str = json_encode($cy_json);//更新采样表
		$merge_fb_xm = array_merge($che_hui,$geng);
		$DB->query("UPDATE cy set xmfb = '$cy_json_str' where id='{$_POST['cyd_id']}'");
		if($merge_fb_xm&&$cyd['status']>='6'){//如果已经生成化验单
			foreach($merge_fb_xm as $vv3){
				$paysql = $DB->query("SELECT * from `assay_pay` where `vid`='$vv3' and `cyd_id`='{$_POST['cyd_id']}'");
				while($pay = $DB->fetch_assoc($paysql)){
					if(!in_array($pay['over'],$hyd_zt)){
						$wt = $DB->fetch_one_assoc("SELECT lxid as wt from xmfa where id='{$pay['fid']}'");
						$fp_id = in_array($vv3, $che_hui) ? FZX_ID : $_POST['fzx'];
						$xx = $DB->fetch_one_assoc("SELECT *  from xmfa where lxid='{$wt['wt']}' and fzx_id='{$fp_id}' and mr='1' and xmid='$vv3' and act='1'");
						if($xx){
							$DB->query("UPDATE assay_pay set uid='{$xx['userid']}',uid2='{$xx['userid2']}',userid='{$users[$xx['userid']]}',userid2='{$users[$xx['userid2']]}',fid='{$xx['id']}',fp_id='{$fp_id}',`over`='未开始',`sign_01`='',`sign_012`='' where id='{$pay['id']}' and vid='$vv3'");
						}else{
							$tishi .= ','.$_SESSION['assayvalueC'][$vv3];
						}
					}else{
						$tishi1 .= ','.$_SESSION['assayvalueC'][$vv3];
					}
				}
			}
			if($tishi){
				echo "<script>alert('项目$tishi因为".$fzxarr[$fbfzx]."没有配置该项目，所以无法分配');</script>";
			}
			if($tishi1){
				echo "<script>alert('项目$tishi1已经化验完成，无法分配');</script>";
			}
		}
	}else{
		$DB->query("UPDATE cy set xmfb = '' where id='{$_POST['cyd_id']}'");//更新采样表
	}
	gotourl("$rooturl/xd_csrw/xd_csrw_list.php");
	die();
}else{
	echo "<script>alert('发生错误，请重试！');</script>";
	gotourl("$rooturl/xd_csrw/xd_csrw_list.php");
	die();
}

?>
