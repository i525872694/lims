<?php
//删除一个站点,将删除有关该站点的所有数据
//请慎重
// $_REQUEST['sid'] 是一个数组
require_once "../temp/config.php";
// if (!$u['xd_cy_rw'] && $u['xd_csrw']){
//  die( '你没有权限' );
// }
$_GET['sgname']	= urlsafe_b64decode($_GET['sgname']);
if($_GET['sid']>0)
{
    if(empty($_GET['sgname'])){//整个站点删除
        $DB->query("UPDATE `sites` SET act ='0' WHERE `id`='$_GET[sid]'");
        $DB->query("UPDATE `site_group` SET act ='0' WHERE `site_id`='$_GET[sid]'");
        /*
        //发送数据到duijie表中
        $curl_arr = array();
        $curl_arr['FZX_ID'] = $fzx_id;
        $curl_arr['LIMS_ID'] = $_GET['sid'];
        $curl_data = array();
        $curl_data['data'] = $curl_arr;
        $curl_data['action'] = 'table';
        $curl_data['action2'] = 'del';
        $curl_data['table'] = 'SITE_DUIJIE';
        $data = curl_request($duijie_url.'xd_cyrw/cy_duijie_url.php',$curl_data);
        */
        //gotourl("$rooturl/site/site_manage_list.php");
        if($_POST['ajax'] == 'yes'){
            echo "yes";
        }else{
            goback();
        }
        exit;
    }else{//只删除某个批次中的站点
        $sqll="UPDATE `site_group` SET act ='0' WHERE `site_id`='{$_GET['sid']}' AND `group_name`='{$_GET['sgname']}'  AND `fzx_id`='$_GET[fid]'";
        $DB->query($sqll);
        if($_GET[site_type]=='0'){
            $rs_sites   = $DB->fetch_one_assoc("SELECT tjcs FROM `sites` WHERE id='$_GET[sid]'");
            if($rs_sites['tjcs']!=',,'){
                $site_tjcs=$rs_sites['tjcs'];
                $site_tjcs=Trim($site_tjcs,',');
                $bglxAr= explode(',',$site_tjcs);
                $sgname=array(0=>$_GET['sgname']);
                $sites_new_tjcs= array_diff($bglxAr, $sgname);
                $site_tj=implode(',',$sites_new_tjcs);
                $site_tj=','.$site_tj.',';
                $sqll="UPDATE `sites` SET tjcs ='$site_tj' WHERE `id`='$_GET[sid]' AND `fzx_id`='$_GET[fid]'";
                $DB->query($sqll);
            }
            gotourl("$rooturl/site/site_list_new.php?site_type={$_GET['site_type']}");
        }
    }
	
}
if($_GET[pi]>0)//说明要隐藏整批站点
{
	$sql= "UPDATE `site_group` SET act='0' WHERE `site_type`='$_GET[site_type]' AND `group_name`='$_GET[sgname]' AND `fzx_id`='$_GET[fid]' ";
	$DB->query($sql);
	/*$sq=$DB->query("SELECT `site_id` FROM `site_group` WHERE `site_type`='$_GET[site_type]' AND `group_name`='$_GET[sgname]' AND `fzx_id`='$_GET[fid]' ");
	while($yc = $DB->fetch_assoc($sq))
	{
		$sql="UPDATE `sites` SET status ='0' WHERE `id`='$yc[sid]'";
		$DB->query($sql);
	}*/
}
if($_GET['action']=='xd_cyrw'){
	gotourl("$rooturl/xd_cyrw/xd_cyrw_index.php?site_type={$_GET['site_type']}");
}else{
	$back_url = explode( '&group_name',$_SESSION['back_url'] );
	gotourl( $back_url[0] );
}
?>
