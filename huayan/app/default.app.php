<?php
/**
 * 功能：Defalut类
 * 作者：Mr Zhou
 * 日期：
 * 描述：
 */
class DefaultApp extends LIMS_Base {
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2017-04-29
	 * 功能描述：
	*/
	public function index(){
		return true;
	}
}