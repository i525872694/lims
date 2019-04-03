<?php
$_SESSION = array();
$nickname = trim($_GET['username']);		//用户昵称
$access_token = $_GET['access_token'];
$_SESSION['token_key']['login'] = $access_token;

$u = $DB->fetch_one_assoc( "SELECT u.*,h.hub_name,h.is_zz FROM `users` u LEFT JOIN `hub_info` h ON u.fzx_id=h.id WHERE `nickname` = '{$nickname}' LIMIT 1" );

/*验证是否能找出相同资料的用户，不能则不存在*/
if( !$u ){
    echo json_encode(['error'=>['code'=>401,'message'=>'用户名不存在']]);exit;
}

//审核配置
$user_other=$DB->fetch_one_assoc("SELECT * FROM `user_other` WHERE `uid`='{$u['id']}'");
empty($user_other['v1'])&&$user_other['v1']=0;
empty($user_other['v2'])&&$user_other['v2']=0;
empty($user_other['v3'])&&$user_other['v3']=0;
empty($user_other['v4'])&&$user_other['v4']=0;

$u['test']		= $test;	//将test全局变量也放入权限数组。
$u['lasturl']	= '';
$u['password']	= '******';
$u['user_other']= $user_other;
$userid			= $u['userid'];
$u['ip']		= $_SERVER["REMOTE_ADDR"]; 
$u['lims_system_bar']	= $lims_system_bar;
$_SESSION['u']	= $u;
if($u['is_zz'] == 1){
        $sql	= "SELECT distinct v.`id`,v.`value_C` FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid` WHERE xf.id!='' ORDER BY CONVERT( `value_C` USING gbk )";
}else{
        $sql	= "SELECT v.`id`,v.`value_C`,xf.`unit`,xf.`lxid`,xf.`fangfa`
                FROM `assay_value` AS v LEFT JOIN `xmfa` AS xf ON v.`id` = xf.`xmid`
                WHERE xf.`fzx_id`={$u['fzx_id']} AND xf.`act`=1 ORDER BY CONVERT( `value_C` USING gbk )";
}
$R = $DB->query($sql);
//下面这段代码将全部化验项目存到两个数组中,key='id' value=中英文化验项目 名称,并将这两个数组注册为session变量,这样就可以在任意地方引用这些数据.
$av_unit=$assayvalueC=array();
while($r=$DB->fetch_assoc($R)){
    $assayvalueC[$r['id']]	= $r['value_C'];
    $av_unit[$r['id']][$r['lxid']][$r['fangfa']]	= $r['unit'];
}
//用户登录一次 记录一次 用户名字 ip地址 登录时间 以便 以后好查找
$ip=$_SERVER["REMOTE_ADDR"];
$sql = "INSERT INTO `userlog` SET `uid` = '".$u['id']."', `uname`= '".$u['userid']."',`uptime`='".date("Y-m-d H:i:s")."',`ip`='".$ip."'";
$DB->query($sql);
$_SESSION['av_unit']		= $av_unit;
$_SESSION['assayvalueC']	= $assayvalueC;

echo json_encode(['data'=>[$nickname]]);exit;