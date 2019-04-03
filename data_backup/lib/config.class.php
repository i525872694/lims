<?php
/**
 * 本项目配置类
 * @date		2012-10-31
 */
class Config {
	
	private static $_conf = NULL;
	private static $_configInc = array();
	private static $_db = NULL;
	private static $_smarty = NULL;
	
	/**
	 * 构造方法  私有  不允许外部实例化
	 * */
	private function __construct()
	{
		//加载配置文件获取配置信息
		self::$_configInc	=	include (ROOT_PATH.'/config/config.inc.php');
	}
	/**
	 * 配置方法
	 * 
	 * @param	none
	 * @return	本对象
	 */
	public static function setConf()
	{
		//如果是第一次启动程序则实例化本类
		if( !isset( self::$_conf ) )
		{
			self::$_conf = new self();//内部实例化
			self::setSmarty();		  //配置Smarty
			//self::setPdoDb();		  //设置PDO连接数据库
			self::setMySqlDb();	  //设置(MySql)数据库连接
		}else 
		{
			return self::$_conf;
		}
	}
	/**
	 * 实例化PDO连接数据库
	 */
	private function setPdoDb()
	{
		$dsn		=	"mysql:host=".self::$_configInc['dbHost'].";dbname=".self::$_configInc['dbName'];
		$username	=	self::$_configInc['dbUser'];
		$passwd		=	self::$_configInc['dbPwd'];
		$options	=	array();//PDO_ATTR_PERSISTENT => true
		$charset	=	self::$_configInc['charset'];
		PdoDb::setPdoDb($dsn, $username, $passwd, $options, $charset);
		self::$_db = PdoDb::getPdoDb();
	}
	/**
	 * MySql数据库操作
	 * @param	none
	 * @return	none
	 */
	private function setMySqlDb()
	{
		self::$_db	= &db();
		//self::$_db = new Mysql(self::$_configInc['dbHost'],self::$_configInc['dbUser'],self::$_configInc['dbPwd'],self::$_configInc['dbName'],self::$_configInc['charset']);
	}
	/**
	 * 返回数据库连接对象
	 */
	public function getDb()
	{
		return self::$_db;
	}
	/**
	 * 配置Smarty
	 * @param	none
	 * @return	none
	 * */
	private function setSmarty()
	{
		require (ROOT_PATH . '/lib/smarty/Smarty.class.php');
		self::$_smarty = new Smarty();
		foreach (self::$_configInc['smarty_init'] as $key => $value)
		{
			self::$_smarty->$key = $value;
		}
		return true;
	}
	/**
	 * 映射变量
	 * @param string $v, string $k
	 */
	public static function assign( $_tpl_vars , $vlue = null )
	{
		self::$_smarty->assign( $_tpl_vars , $vlue );
	}
	/**
	 * 
	 * 映射变量到指定HTML文件
	 * @param string $pageName
	 */
	public static function display( $url )
	{
		self::$_smarty->display( $url );
	}
	/**
	 * 获取配置信息
	 */
	public static function getConfInc()
	{
		return self::$_configInc;
	}
	/**
	 * 获取登陆配置
	 */
	public static function getLognOpen()
	{
		return self::$_configInc['loginOpen'];
	}

}

?>