<?php
/**
 * 将PDO对象封装
 * $date	2012-10-31
 *
 */
class PdoDb {
	private static $_db		=	NULL;
	private static $_pdo	=	NULL;
	
	/**
	 * 不允许外部实例化
	 */
	private function __construct()
	{}
	/**
	 * 设置pdo连接数据库
	 */
	public static function setPdoDb($dsn, $username, $passwd, $options, $charset)
	{
		if(!isset(self::$_db))
		{
			self::$_db	=	new self();
			self::$_pdo	=	new PDO($dsn, $username, $passwd, $options);
			self::$_pdo	->	exec("set names $charset");
		}
		return TRUE;
	}
	/**
	 * 返会Pdo对象
	 */
	public static function getPdoDb()
	{
		return self::$_db;
	}
	/**
	 * exec执行SQL语句
	 */
	public function exec($sql)
	{
		$exec	=	self::$_pdo	->	exec($sql);
		$exec	=	$exec === false ? false : true;
		return $exec;
	}
	/**
	 * query执行SQL语句
	 */
	public function query($sql)
	{
		return	self::$_pdo	->	query($sql);
	}
	/**
	 * 返回一条结果集
	 */
	public function fetch($sql)
	{
		$query	=	self::query($sql);
		return $query	->	fetch();
	}
	/**
	 * 返回所有结果集
	 */
	public function fetchAll($sql)
	{
		$query	=	self::query($sql);
		return $query	->	fetchAll();
	}
	/**
	 * 返回查询的数据的条数
	 */
	public function rowCount($sql)
	{
		$query	=	self::query($sql);
		return $query	->	rowCount();
	}
	/**
	 * 获取最后插入的Id
	 */
	public function lastInsertId()
	{
		return self::$_pdo->lastInsertId();
	}
	
}

?>