<?php
/**
 * 数据库连接及数据操作
 * @date		2012-10-31
 */

class Mysql{
	
	private static $_link	=	NULL;//本对象
	
	/**
	 * 构造函数
	 * @param	string $dbHost
	 * @param	string $dbUser
	 * @param	string $dbPwd
	 * @param	string $dbName
	 * @param	string $charset
	 * */
	public function __construct($dbHost='', $dbUser='', $dbPwd='', $dbName='',$charset='utf8')
	{
		if (!isset(self::$_link))
		{
			$charset = str_replace('-', '', $charset);
			self::$_link = self::conn($dbHost, $dbUser, $dbPwd, $dbName,$charset);
		}
	}
	/**
	 * 连接服务器，数据库
	 * */
	private static function conn($dbHost,$dbUser,$dbPwd,$dbName,$charset){
		if (!isset(self::$_link))
		{
			self::$_link = mysql_connect($dbHost,$dbUser,$dbPwd);
			mysql_select_db($dbName,self::$_link);
			mysql_query('set names '.$charset,self::$_link);
		}
		return self::$_link;
	}
	
	/**
	 * 执行sql语句
	 * @param	string $sql
	 * */
	public function query($sql)
	{
		return mysql_query($sql,self::$_link);
	}
	
	/**
	 * 查询数据
	 * @param	string $sql
	 * @return	Array
	 * */
	public function fetch_array($sql)
	{
		$query	=	$this->query($sql);
		while($row=mysql_fetch_array($query))
		{
			$arr[]=$row;
		}
		return $arr;
	}
	/**
	 * 查询数据
	 * @param	string $sql
	 * @return	array
	 * */
	public function fetch_assoc($sql)
	{
		$query	=	$this->query($sql);
		while($row=mysql_fetch_assoc($query))
		{
			$arr[]=$row;
		}
		return $arr;
	}
	
	/**
	 * 查询数据条数
	 * @param	string $sql
	 * @return	integer
	 * */
	public function num_rows($sql)
	{
		return mysql_num_rows(mysql_query($sql));
	}
	/**
	 * 获取刚插入的数据的id号
	 */
	public function insert_id()
	{
		return mysql_insert_id(self::$_link);
	}
}
?>