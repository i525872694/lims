<?php
/**
 * 本程序的核心文件，包含最基础的类
 * */
/*********************-------包含基本类和函数-------*********************/
//记录系统启动时间
define(STARTTIME, date('Y-m-d H:i:s'));
/**
 * 外界与程序的借口类
 * @date	2012-10-31
 */
class MAIN {
	private static $_app = '';//应用类
	private static $_act = '';//操作方法
	//禁止外部实例化
	private function __construct(){}
	/**
	 * 启动程序
	 * @param   arrary $config
	 * */
	static function startup($config = array())
	{
		//设置时区：亚洲上海（中国北京）
		date_default_timezone_set('Asia/Shanghai');
		
        /*加载基础文件*/
        foreach ($config['base_files'] as $value)
        {
        	is_file($value) && require ($value);
        }
        //配置程序信息
		Config::setConf();
		
        // 数据过滤 
        if (!get_magic_quotes_gpc())
        {
            $_GET		=	addslashes_deep($_GET);
            $_POST		=	addslashes_deep($_POST);
        }
        //设置默认的操作
        $default_app	=	$config['default_app'] ? $config['default_app'] : 'default';
        $default_act	=	$config['default_act'] ? $config['default_act'] : 'index';
        //接收操作请求
        self::$_app		=	isset($_GET['app']) ? trim($_GET['app']) : $default_app;
        self::$_act		=	isset($_GET['act']) ? trim($_GET['act']) : $default_act;
        //验证是否开启登陆
        Config::getLognOpen() && self::openLogin();
        //加载指定的类文件
        $app_file		=	$config['app_root'] .self::$_app.'.app.php';
        is_file($app_file) ? require($app_file) : exit('缺少类文件-->'.$app_file);
        //获取对象名
        $app_class_name	=	ucfirst(self::$_app).'App';
        //实例化对象
        $app			=	new $app_class_name();
        
        
        //////////////////////////////////////
        Config::assign('site_url',REAL_SITE_URL);
        $lang	=	include(ROOT_PATH.'/lang/db.lang.php');
        	Config::assign('lang',$lang);
        /////////////////////////////////////////
        //执行动作方法
        $app->do_action(self::$_act);
        //执行析构函数结束操作
        $app->__destruct();
    }
	/**
	 * 检验是否开启登陆
	 * @author:Mr Zhou
	 **/
	private static function openLogin()
	{
		if(!isset($_SESSION['uId']))
		{
			//定义在未登录时允许的操作数组
			$requirement =  array();
			//登陆请求
			self::$_app  == 'default'	&& self::$_act == 'login' && $requirement[] = true;
			//验证码操作请求
			self::$_app  == 'code'		&& $requirement[] = true;
			//……
			//判断请求是否合法
			if(!in_array(true, $requirement))
			{
				$_SESSION['spring'] = array();
	 			SiteRoot::Reto('1', 'index.php?app=default&act=login', '请您登陆后再进行管理……','setTimeout("top.location=\'index.php?app=default&act=login\';",1000)');
			}
		}
	}
}
/**
 * 创建MySQL数据库对象实例
 *
 * @return  object
 */
function &db()
{
	static $db = null;
	if ($db === null)
	{
		$cfg = parse_url(DB_CONFIG);

		if ($cfg['scheme'] == 'mysql')
		{
			if (empty($cfg['pass']))
			{
				$cfg['pass'] = '';
			}
			else
			{
				$cfg['pass'] = urldecode($cfg['pass']);
			}
			$cfg ['user'] = urldecode($cfg['user']);

			if (empty($cfg['path']))
			{
				trigger_error('Invalid database name.', E_USER_ERROR);
			}
			else
			{
				$cfg['path'] = str_replace('/', '', $cfg['path']);
			}

			$charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
			$db = new cls_mysql();
			$db->cache_dir = ROOT_PATH. '/temp/query_caches/';
			$db->connect($cfg['host']. ':' .$cfg['port'], $cfg['user'],
					$cfg['pass'], $cfg['path'], $charset);
		}
		else
		{
			trigger_error('Unkown database type.', E_USER_ERROR);
		}
	}

	return $db;
}
/**
 * 通过该函数运行函数可以抑制错误
 *
 * @author  weberliu
 * @param   string      $fun        要屏蔽错误的函数名
 * @return  mix         函数执行结果
 */
function _at($fun)
{
    $arg = func_get_args();
    unset($arg[0]);
    restore_error_handler();
    $ret_val = @call_user_func_array($fun, $arg);

    return $ret_val;
}
	/**
	 * 对变量中的特殊字符进行转义
	 * <br />如果magic_quotes_gpc开启，直接将字符串返回，如果未开启则用addslashes()函数转义
	 * <br />参数如果是数组则递归转义
	 * @param	$value
	 */
	/*function addslashes_deep( $value )
	{
	    if ( empty( $value ) || !get_magic_quotes_gpc() )
	    {
	        return $value;
	    }
	    else
	    {
	        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
	    }
	}*/
	/**
	 * 自动加载类文件
	 * @param className string
	 * */
	function __autoload($calssName)
	{
		$path = substr( strtolower($calssName) , -3 ) == 'app' ?  substr( strtolower($calssName) , 0 , -3 ) : $calssName;
		$path .= '.app.php'; 
		if(file_exists(ROOT_PATH.'/lib/'.$path))
		{
			require ROOT_PATH.'/lib/'.$path;
		}else if(file_exists(ROOT_PATH.'/app/'.$path))
		{
			require ROOT_PATH.'/app/'.$path;
		}else
		{
			exit('缺少类文件-->'.$path);
		}
	}
	/**
	 * 用js实现历史页面跳转
	 */
	function JSTurn($last=-2,$time=1000)
	{
		$js	=	'setTimeout("window.history.go('.$last.')",'.$time.');';
		return $js;
	}
	/**
	 * 将数组原样输出
	 * @param	array $array
	 * @author
	 * */
	function print_pre($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
	/**
	 *    将default.abc类的字符串转为$default['abc']
	 *
	 *    @author    Garbin
	 *    @param     string $str
	 *    @return    string
	 */
	function strtokey($str, $owner = '')
	{
	    if (!$str)
	    {
	        return '';
	    }
	    if ($owner)
	    {
	        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
	    }
	    else
	    {
	        $parts = explode('.', $str);
	        $owner = '$' . $parts[0];
	        unset($parts[0]);
	        return strtokey(implode('.', $parts), $owner);
	    }
	}/**
	 * 删除目录,不支持目录中带 ..
	 *
	 * @param string $dir
	 *
	 * @return boolen
	 */
	function lims_rmdir($dir)
	{
	    $dir = str_replace(array('..', "\n", "\r"), array('', '', ''), $dir);
	    $ret_val = false;
	    if (is_dir($dir))
	    {
	        $d = @dir($dir);
	        if($d)
	        {
	            while (false !== ($entry = $d->read()))
	            {
	               if($entry!='.' && $entry!='..')
	               {
	                   $entry = $dir.'/'.$entry;
	                   if(is_dir($entry))
	                   {
	                       ecm_rmdir($entry);
	                   }
	                   else
	                   {
	                       @unlink($entry);
	                   }
	               }
	            }
	            $d->close();
	            $ret_val = rmdir($dir);
	         }
	    }
	    else
	    {
	        $ret_val = unlink($dir);
	    }
	
	    return $ret_val;
	}
	function lims_mkdir($absolute_path, $mode = 0777)
	{
		if (is_dir($absolute_path))
		{
			return true;
		}
	
		$root_path      = ROOT_PATH;
		$relative_path  = str_replace($root_path, '', $absolute_path);
		$each_path      = explode('/', $relative_path);
		$cur_path       = $root_path; // 当前循环处理的路径
		foreach ($each_path as $path)
		{
			if ($path)
			{
				$cur_path = $cur_path . '/' . $path;
				if (!is_dir($cur_path))
				{
					if (@mkdir($cur_path, $mode))
					{
						fclose(fopen($cur_path . '/index.htm', 'w'));
					}
					else
					{
						return false;
					}
				}
			}
		}
	
		return true;
	}
?>