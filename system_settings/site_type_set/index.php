<?php
#设置标签的层级关系

include __DIR__ .'/../../temp/config.php';
include __DIR__ .'/func.php';
//引入
$trade_global['js'] = array(
    'lims/ztree/jquery.ztree.core.js',
    'lims/ztree/jquery.ztree.excheck.js',
    'lims/ztree/jquery.ztree.exedit.js',
);
$trade_global['css'] = array('lims/ztree/zTreeStyle.css');
//数据处理
$all_node = all_site_type_data();

if(count($all_node))
{
    $tree = treeArray($all_node,0);
    $zNodes= json_encode($tree);
    $znode_last = max(array_keys($all_node))+1;
}else{
    $znode_last = 2;//默认给一个
    $zNodes= json_encode(['name'=>'站点标签设置','id'=>1]);
}

//$info = $DB->fetch_one_assoc("SELECT * FROM `menu` WHERE `parent_id` = 33 limit 1 ");

disp('system_settings/site_type_set/demo');
