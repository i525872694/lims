<?php
/** 功能：系统配置文件
  * 作者：
  * 时间：
 **/
$begin_year = 2016;
$server     = 'localhost';
$db_user    = 'root';		// MySQL用户名
$db_pass    = 'root';		// MySQL密码
$dbname     = 'yunnan';   // MySQL数据库名
$charset    = 'utf-8';		//字符编码
$dwname     = '云南省水文水资源局实验室信息管理系统';
$dw_biaozhi = 'lims3.0';//根据此字段来识别包含那个个性配置文件
$key        = '19kNTLlLtRHps';		//key
$rootdir    =  __DIR__ .'/..';
$rooturl    = $_SERVER['HTTP_HOST'].'/yunnan';
$rooturl    = ($_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').$rooturl;
$socket_url = $_SERVER['SERVER_NAME'];
$socket_status =true;//限制用户单点登录点开关
$set_jingdu = '-';			//实验室经度
$set_weidu  = '-';			//实验室纬度 实验室质控样
//数据库报错信息将会发送至此邮箱
$technicalemail			= '';
$date_default_timezone	= 'Asia/Shanghai';	//时区
//默认开启防SQL注入 将$addslashes_deep声明为false时表示本次请求取消防SQL注入[此配置仅在Apache配置为不防SQL注入的时候有效]
$addslashes_deep		= isset($addslashes_deep) ? false : true;

//包含系统必须的文件
require_once $rootdir.'/temp/mysql.php';
require_once $rootdir.'/temp/function.php';
require_once $rootdir.'/temp/definition.php';
require_once $rootdir.'/temp/global.inc.php';
require_once $rootdir.'/temp/gxconfig.php';
define("FZX_ID", $_SESSION['u']['fzx_id']);//分中心id
//#####导航的统一处理
$this_page      = basename($_SERVER['REQUEST_URI']);
$this_page      = @explode('.', $this_page)[0];
$prev_page      = basename($_SERVER['HTTP_REFERER']);//点击本页导航和页面刷新、页面条件筛选时都会出现问题
$prev_page      = @explode('.',$prev_page)[0];
if(!empty($_SESSION['daohang'][$prev_page]) && $this_page!=$prev_page){
    //一级页面点击进入二级页面时，走这里
    $trade_global['daohang']    = $_SESSION['daohang'][$prev_page];
}else if(!empty($_SESSION['daohang'][$this_page])){
    //页面中切换条件时，或者直接点击导航的本页链接时，会走这里
    array_pop($_SESSION['daohang'][$this_page]);
    $trade_global['daohang']    = $_SESSION['daohang'][$this_page];
}else{
    $trade_global['daohang']    = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>"index.php",'target'=>"target='_parent'"),
	array('icon'=>'','html'=>'个人任务','href'=>'main.php')
    );
}
if(file_exists($rootdir.'/vendor/autoload.php')){
    require $rootdir.'/vendor/autoload.php';
    $debugbar = new \DebugBar\StandardDebugBar();
}
register_shutdown_function( "fatal_handler" );
set_error_handler("error_handler");
define('E_FATAL',  E_ERROR | E_USER_ERROR |  E_CORE_ERROR |
    E_COMPILE_ERROR | E_RECOVERABLE_ERROR| E_PARSE );
//获取fatal error
function fatal_handler() {
    global $u,$debugbar,$rooturl ;
    $error = error_get_last();
    if($error && ($error["type"]===($error["type"] & E_FATAL))) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        error_handler($errno,$errstr,$errfile,$errline);
    }
    if($u['admin']){
        if(is_object($debugbar)){
            $debugbarRenderer = $debugbar->getJavascriptRenderer();
            //$debugbar->sendDataInHeaders();
            $debugbarRenderer_start_js = $debugbarRenderer->renderHead();
            $debugbarRenderer_start_js = strtr($debugbarRenderer_start_js ,['/vendor/maximebf/'=>$rooturl.'/vendor/maximebf/']);
            $debugbarRenderer_end_js = $debugbarRenderer->render();
            //如果是ajax请求,不进行展示
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
                return false;
            }
            echo $debugbarRenderer_start_js;
            echo $debugbarRenderer_end_js;
        }
    }
}
//获取所有的error
function error_handler($errno,$errstr,$errfile,$errline){
    //获取到错误可以自己处理，比如记Log、报警等等
    global $u,$debugbar;
    if($u['admin']){
        //一般的错误不记录
        if( substr($errstr,0,9) == 'Undefined') return false;
        if(is_object($debugbar)){
                $e = new ErrorException($errstr, 0, $errno, $errfile, $errline);
                //  $debugbar['exceptions']->addException($e);
        }
    }
}
#######导航处理结束
?>
