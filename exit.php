<?php
/**
 * 功能：退出登录
 * 作者：Mr Zhou
 * 日期：2014-03-31
 * 描述：
*/
//本页面不检查是否已登录
$checkLogin = false;
include "temp/config.php";
//向websocket服务器发送用户名信息 退出用户
if($_POST['uname']&&$socket_status){
	//推送的用户
	$to_uid = $_POST['uname'];
	// 推送的url地址
	$push_api_url = "$socket_url:2121/";
	$post_data = array(
	"type" => "publish",
	"logout" => "1",
	"to" => $to_uid, 
	);
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
	curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
	$return = curl_exec ( $ch );
	curl_close ( $ch );
	var_export($return);
	exit;
}
if( $u['id']!='' ) {
	$lasturl = strtr(base64_decode($_GET['goback']),array('print=1'=>''));
	$DB->query("UPDATE `users` SET `lastlogout`=now(),lasturl='{$lasturl}' WHERE `id`='{$u['id']}' limit 1");
}
$_SESSION = array();
?>
<!DOCTYPE html>
<html> 
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<title>退出登录</title>
</head> 
<body>
<script>
	// 清空Cookie
	(function clearCookie(){
		var keys=document.cookie.match(/[^ =;]+(?=\=)/g);
		if (keys) {
			for (var i = keys.length; i--;){
				document.cookie=keys[i]+'="";expires=' + new Date( 0).toUTCString()+'; path=/';
			}
		}
	})();
	var url = '<?=$rooturl?>/login.php?goback=';
	/*<?=urlencode($_SERVER[HTTP_REFERER])?>*/
	var e = document.createElement('a');
	e.href = url;
	e.target = '_top';
	document.body.appendChild(e);
	e.click();
</script>
</body>
</html>