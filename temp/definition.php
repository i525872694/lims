<?php
/**
 * 功能：系统变量定义
 * 作者: 
 * 日期: 
 * 描述: 
*/

global $script_name;
$script_name = basename( $_SERVER['SCRIPT_FILENAME'], '.php' );

// 记录历史url
if(empty($_SESSION['url_stack'])){$_SESSION['url_stack']=array('','','','','');}
//屏蔽部分页面路径
$pingbi_url = array('index.php','exit.php','login.php');
$current_page = explode('?', basename($_SERVER['REQUEST_URI']));
$a = (in_array($current_page[0],$pingbi_url)) ? false : true;
//屏蔽和上次一样的路径
$b = $_SERVER['REQUEST_URI'] != $_SESSION['url_stack'][0];
//屏蔽ajax请求路径
$c = !isset($_REQUEST['ajax']);
//根目录
$d = substr( $_SERVER['REQUEST_URI'],-1) != '/';
if ($a && $b && $c && $d) {
    array_pop($_SESSION['url_stack']);
    array_unshift($_SESSION['url_stack'],$_SERVER['REQUEST_URI']);
    $goback=$REQUEST_URI = base64_encode( $_SERVER["REQUEST_URI"] );
    $_SESSION['u']['lasturl'] = $_SERVER["REQUEST_URI"];
}
//当前路径
$current_url= $_SESSION['url_stack'][0];
//上次路径
$last_url   = empty($_SESSION['url_stack'][1]) ? 'main.php':$_SESSION['url_stack'][1];
$_u_        = 'url_stack';
$url[$_u_] = $_SESSION[$_u_];




/*end*/
//调试要用到的变量
$test   = 1;    //=1打开测试功能,2-开注释，开现实
$debug  = 1;    //在mysql类中用于调试的开关

//////////////////// 样品标志用于样品及化验任务 ////////////////////

//化验单状态
global $yp_flag, $site_zk_flag;
//质控flag
$yp_flag = array(
    '-20'   => '室内平行B样',
    '-40'   => '加标回收D样',
    '-60'   => '室内平行B样加标F样',
    '0'     => '普通',
            '20' => '室内平行A样',
            '40' => '加标回收C样',
            '60' => '加标回收E样',
    '1'     => '全程序空白',
            '21' => '全程序空白室内平行A样',
            '41' => '全程序空白加标回收C样',
            '61' => '全程序空白加标回收E样',
    '-2'    => '室内空白',
    '3'     => '标准样品',
            '23' => '标准样品室内平行A样',
            '43' => '标准样品加标回收C样',
            '63' => '标准样品加标回收E样',
    '-4'    => '自控样',
    '5'     => '现场平行A',
            '25' => '现场平行A室内平行A样',
            '45' => '现场平行A加标回收C样',
            '65' => '现场平行A加标回收E样',
    '-6'     => '现场平行B',
            '-26' => '现场平行B室内平行B样',
            '-46' => '现场平行B加标回收D样',
            '-66' => '现场平行B加标回收F样',
);
//
$site_zk_flag = array(
    '-6'    => '+现场平行',
    '-20'   => '+平行',
    '-40'   => '+加标',
    '-60'   => '+平行+加标',
    '-26'   => '+平行',
    '-46'   => '+加标',
    '-66'   => '+平行+加标',
);

define( 'PTY',              '0' );   # 普通样
define( 'QCKB',             '1' );   # 全程空白
define( 'SNKB',            '-2' );   # 室内空白
define( 'BZYP',             '3' );   # 标准样品
define( 'ZKY',             '-4' );   # 标准样品
define( 'XCPX01',           '5' );   # 现场平行 A原始样
define( 'XCPX02',          '-6' );   # 现场平行 B平行样

define( 'PTY_SNPX',        '20' );   # 普通样室内平行A样
define( 'PTY_JBHS',        '40' );   # 普通样加标回收C样
define( 'PTY_SNPX_JBHS',   '60' );   # 普通样加标回收E样

define( 'SNPX',           '-20' );   # 室内平行B样
define( 'JBHS',           '-40' );   # 加标回收D样
define( 'SNPX_JBHS',      '-60' );   # 室内平行B样加标F样

define( 'QCKB_SNPX',       '21' );   # 全程序空白室内平行A样
define( 'QCKB_JBHS',       '41' );   # 全程序空白加标回收C样
define( 'QCKB_SNPX_JBHS',  '61' );   # 全程序空白加标回收E样

define( 'BZYP_SNPX',       '23' );   # 标准样品室内平行A样
define( 'BZYP_JBHS',       '43' );   # 标准样品加标回收C样
define( 'BZYP_SNPX_JBHS',  '63' );   # 标准样品加标回收E样

define( 'XCPX01_SNPX',     '25' );   # 标准样品室内平行A样
define( 'XCPX01_JBHS',     '45' );   # 标准样品加标回收C样
define( 'XCPX01_SNPX_JBHS','65' );   # 标准样品加标回收E样

define( 'XCPX02_SNPX',     '-26');   # 标准样品室内平行A样
define( 'XCPX02_JBHS',     '-46');   # 标准样品加标回收C样
define( 'XCPX02_SNPX_JBHS','-66');   # 标准样品加标回收E样

define( 'QCKB_SITE_ID',     '0' );   # 全程序空白 的站点编号
define( 'KB01_SITE_ID',    '-1' );   # 室内空白1 的站点编号
define( 'KB02_SITE_ID',    '-2' );   # 室内空白2 的站点编号
/*
#以下虚拟站点编号仅用于精密度测验
//define( 'JMD_JBHS',        '' );   # 精密度测试中的加标回收样
define( 'SNKB_SITE_ID', '-1000' );  //室内空白 的站点编号
define( 'C01_SITE_ID',  '-1001' );  //0.1C 的站点编号
define( 'C09_SITE_ID',  '-1002' );  //0.9C 的站点编号
define( 'TRSY_SITE_ID', '-1003' );  //天然水样 的站点编号
define( 'JBSY_SITE_ID', '-1004' );  //加标水样 的站点编号
define( 'BZYP_SITE_ID', '-1005' );  //标准样品 的站点编号
*/
?>
