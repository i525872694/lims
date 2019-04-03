<?php 
/*
 * @desc		首页
 * @date		2013-06-16
 */
//开启session
//session_start();
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);ini_set('display_errors', '1');
//规范编码为utf-8格式
header("Content-type:text/html;charset=utf-8");
include '../temp/config.php';
//定义目录常量
define( 'ROOT_PATH' , str_replace( '\\' , '/' , dirname( __FILE__ ) ) );
//包含程序启动文件
require( ROOT_PATH.'/lib/main.php' );

define(DB_PREFIX,'');
define(VERSION,'1.0');
define(CHARSET,'utf-8');
define(DB_CONFIG,'mysql://'.$db_user.':'.$db_pass.'@'.$server.':3306/'.$dbname);
define(REAL_SITE_URL,$rooturl.'/data_backup');

MAIN::startup( array(
	    'default_app'   =>  'db',
	    'default_act'   =>  'index',
	    'app_root'      =>  ROOT_PATH . '/app/',
		'base_files'	=>	array(
				ROOT_PATH . '/lib/mysql.php',
				ROOT_PATH . '/lib/config.class.php',
				ROOT_PATH . '/lib/siteRoot.class.php',
				)
		)
	);
?>
