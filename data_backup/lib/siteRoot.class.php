<?php
/**
 * SiteRoot	基础类
 * @date		2012-10-31
 */
abstract class SiteRoot{

	protected	$_pdo = null;//PDO库对象
	protected	$_db  = null;//数据库对象
	
	/**
	 * 基础类
	 * @param none
	 * */
	public function __construct(){
		$this->_pdo	= Config::getDb();
		$this->_db	= Config::getDb();
	}
	/**
	 * 指定默认显示内容
	 */
	abstract function index();
	/**
	 * 
	 * 执行指定方法
	 * @param  string $action(方法名)
	 */
	public function do_action($action)
	{
        if ($action && method_exists($this, $action))
        {
            $this->$action();            //运行动作
        }else
        {
            exit('操作失误');
        }
	}
	/**
	 * 跳转页
	 * @param	integer $time：隔几秒后跳转 
	 * @param	string 	$url：跳转到的地址 
	 * @param	string	$content：显示的内容
	 * @param	string	js代码,可以添加js特效
	*/
	function Reto( $time , $url , $content , $js='' )
	{
		Config::assign("js",$js);
		Config::assign("url",$url);
		Config::assign("time",$time);
		Config::assign("content",$content);
		Config::display("reto.html");
		die();
	}
	/**
	 * 警告函数
	 */
	function show_warning($msg)
	{
		header('Content-Type:text/html;charset=utf-8');
		$title = '警告--'.$msg;
echo <<<TPL
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$title}</title>
</head>
<body>
<script type="text/javascript">alert("{$msg}");window.history.go(-1);</script>
</body>
</html>
TPL;
	}
	/**
     *    显示提示消息
     *
     *    @author    Garbin
     *    @return    void
     */
    function show_message()
    {
        //$args = func_get_args();
        //call_user_func_array('show_message', $args);
    }
	
	/**
	 * 析构函数
	 */
	function __destruct(){}
}

?>