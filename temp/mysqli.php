<?php
/**
 * 功能：数据库连接及数据操作
 * 作者：Mr Zhou
 * 日期：
 * 描述：
 * */
class connect_Mysql {
	private static $_link = NULL;
	private static $dbHost = '';
	private static $dbUser = '';
	private static $dbPwd = '';
	private static $dbName = '';
	private static $charset = '';
	/**
	 * 构造函数
	 * 作者：Mr Zhou
	 * 参数：string $dbHost
	 * 参数：string $dbUser
	 * 参数：string $dbPwd
	 * 参数：string $dbName
	 * 参数：string $charset
	 * */
	public function __construct($dbHost = '', $dbUser = '', $dbPwd = '', $dbName = '', $charset = 'utf8') {
		if (!isset(self::$_link)) {
			$charset = str_replace('-', '', $charset);
			self::$_link = self::conn($dbHost, $dbUser, $dbPwd, $dbName, $charset);
			if (!self::$_link) {
				die("连接错误: " . mysqli_connect_error());
			}
		}
	}
	/**
	 * 连接服务器，数据库
	 * 作者：Mr Zhou
	 * */
	private static function conn($dbHost, $dbUser, $dbPwd, $dbName, $charset) {
		self::$dbHost = $dbHost;
		self::$dbUser = $dbUser;
		self::$dbName = $dbName;
		self::$charset = $charset;
		if (!isset(self::$_link)) {
			self::$_link = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName, '3306');
			if (mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
			mysqli_query('set names ' . $charset);
		}
		return self::$_link;
	}
	/**
	 * 执行sql语句
	 * 作者：Mr Zhou
	 * 参数：string $sql
	 * */
	public function query($sql) {
		global $u, $debugbar;
		mysqli_query(self::$_link, 'SET NAMES utf8');
		$begintime2 = explode(' ', microtime());
		if ($query = mysqli_query(self::$_link, $sql)) {
			$begintime1 = explode(' ', microtime());
			if ($u['admin']) {
				if (is_object($debugbar)) {
					$begintime1 = explode(' ', microtime());

					$begintime = round($begintime1[0] - $begintime2[0] + $begintime1[1] - $begintime2[1], 4);
					$debugbar["messages"]->addMessage($this->querycount . ' sql use time ' . $begintime . ': ' . $sql, 'query');
				}
			}
			return $query;
		}
		// mysqli_error(self::$_link)
	}
	/**
	 * 查询数据
	 * 作者：Mr Zhou
	 * 参数：string $query
	 * */
	public function fetch_array($query) {
		return mysqli_fetch_array($query);
	}
	/**
	 * 查询数据
	 * 作者：Mr Zhou
	 * 参数：string $query
	 * */
	public function fetch_assoc($query) {
		return mysqli_fetch_assoc($query);
	}
	/**
	 * 查询数据
	 * 作者：Mr Zhou
	 * 参数：string $sql
	 * */
	public function fetch_one_assoc($sql) {
		$query = $this->query($sql);
		return mysqli_fetch_assoc($query);
	}
	/**
	 * 查询数据
	 * 作者：Mr Zhou
	 * 参数：string $sql
	 * */
	public function get_assoc($sql) {
		$rows = array();
		$query = $this->query($sql);
		while ($row[] = $this->fetch_assoc($query)) {
			$rows[] = $row;
		}
		return $rows;
	}
	/**
	 * 查询数据条数
	 * 作者：Mr Zhou
	 * 参数：string $sql
	 * */
	public function num_rows($query) {
		return mysqli_num_rows($query);
	}
	/**
	 * 获取刚插入的数据的id号
	 * 作者：Mr Zhou
	 */
	public function insert_id() {
		return mysqli_insert_id(self::$_link);
	}
	/**
	 * 获取刚插入的数据的id号
	 * 作者：Mr Zhou
	 */
	public function affected_rows() {
		return mysqli_affected_rows(self::$_link);
	}
	/**
	 * 转义 SQL 语句中使用的字符串中的特殊字符
	 * 作者：Mr Zhou
	 */
	public function real_escape_string($str) {
		return mysqli_real_escape_string(self::$_link, $str);
	}
	/**
	 * 关闭数据库链接
	 * 作者：Mr Zhou
	 */
	public function close() {
		return mysqli_close(self::$_link);
	}
}
?>