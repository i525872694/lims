<?php 
/**
 * 配置信息
 */
return array(
	'dbHost'		=>	'localhost',	//服务器
	'dbUser'		=>	'',			//用户名
	'dbPwd'			=>	'',				//密码
	'dbName'		=>	'',	//数据库
	'charset'		=>	'utf8',			//编码
	'loginOpen'		=>	false,			//是否开启登陆
	'smarty_init'	=>	array(
		//静态文件的存放位置
		'template_dir'		=>	ROOT_PATH.'/templates/default/',
		//编译文件的存放位置
		'compile_dir'		=>	ROOT_PATH.'/temp/compile/',
		//配置文件的存放位置
		'config_dir'		=>	ROOT_PATH.'/config/',
		//是否开启缓存
		'caching'			=>	false,
		//缓存周期
		'cache_lifetime'	=>	0,
		//设置缓存文件的存放位置
		'cache_dir'			=>	ROOT_PATH.'/temp/cache/',
		//html页面显示动态数据的左定界符
		'left_delimiter'  	=>  '{',
		//html页面显示动态数据的右定界符
		'right_delimiter' 	=>  '}',),
);