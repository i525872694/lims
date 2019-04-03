<?php
/**
 * 功能：远程执行文件
 * 作者：Mr Zhou
 * 日期：2018-08-06
 * 描述：
 */
ini_set("display_errors", '1');
error_reporting(E_ALL & ~E_NOTICE);
//本页面不检查是否已登录
$checkLogin = false;
// 接收到{$argc}个参数
// 参数变量：$argv
if(!isset($argv[1]) || 'lims' != $argv[1]){
    color_red('参数不正确!');
    die("\n");
}
// 引入框架文件
require_once './ahlims.php';

if(isset($argv[2])){
    //获取对象名
    $app_class_name	=	ucfirst($argv[2]).'App';
    // 判断类是否存在
    if(class_exists($app_class_name)){
        // 实例化对象
        $app = new $app_class_name();
        echo "实例化{$app_class_name}对象\n";
        // 判断类下的方法是否存在
        $act = (isset($argv[3]) && method_exists($app, $argv[3])) ? $argv[3] : 'index';
        // 执行动作方法
        $app->do_action($act);
        echo "执行动作方法{$act}\n";
    }else{
        color_red('参数错误！');
    }
    die("\n");
}


// audo_patch 加载并检测补丁
$patch_files = [];
$patch_path = APP_PATH . 'auto_patch';
$filesnames = scandir($patch_path); //得到所有的文件
foreach ($filesnames as $key => $file) {
    if (!in_array($file, ['.', '..']) && substr($file, -3) == 'php' && !is_dir($file)){
        $patch_files[] = [
            'name' => $file,
            'time' => (int)filemtime($patch_path . '/' . $file)
        ];
    }
}
$time = [];
foreach($patch_files as $k=>$v){
    $time[$k] = $v['time'];
}
// 按修改时间排序
array_multisort($time, SORT_DESC, SORT_NUMERIC, $patch_files);
// 实例化数据库字段等内容更新类
$App = new Update_dbApp();
$sql = "SELECT `module_value1` AS `name`, `module_value2` AS `time` FROM `n_set` WHERE `module_name`='patch_files' ORDER BY `time`+0 DESC";
$last_record = $DB->fetch_one_assoc($sql);
foreach($patch_files as $k => $file){
    // 检查文件名及修改时间,无最新修改文件时跳出
    if((int)$file['time'] <= (int)$last_record['time']){
        break;
    }
    color_moren('执行脚本文件:' . $file['name']);
    $file_path = $patch_path . '/' . $file['name'];
    if(file_exists($file_path)){
        $reback = include_once $file_path;
        if($reback){
            $sql = "INSERT INTO `n_set` SET `module_name`='patch_files', `module_value1`='{$file['name']}', `module_value2`='{$file['time']}'";
            $DB->query($sql);
        }
    }
}
echo "\n***********************************\n";
echo "-------------";
echo exec("printf \"\033[32m执行完毕\033[0m\n\"");
echo "--------------";
echo "\n***********************************\n";

// 红色
function color_red($string) {
    $cmd="printf \"\033[31m".$string."\033[0m\n\"";
    $a=exec($cmd);
    print "$a\n";
}
// 绿色
function color_green($string) {
    $cmd="printf \"\033[32m".$string."\033[0m\n\"";
    $a=exec($cmd);
    print "$a\n";
}
// 
function color_moren($string) {
    $cmd="printf \"\033[0m".$string."\033[0m\n\"";
    $a=exec($cmd);
    print "$a\n";
}
// 显示错误信息
function error_msg($status,$error_msg,$msg_only_error=false,$die=false){
    if( !$msg_only_error && $status ){
        color_green('success:');
    }else{
        color_red('error:');
    }
    color_moren($error_msg);
    if( $die ){
        die;
    }
    return $status;
}
