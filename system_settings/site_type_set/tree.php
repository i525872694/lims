<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-11
 * Time: 下午5:59
 */
include __DIR__ .'/../../temp/config.php';
include __DIR__ .'/func.php';
//引入
$trade_global['js'] = array(
    'lims/d3/d3.js',
    'lims/d3/d3.layout.js',
    'lims/d3/tree.js'
);
//$trade_global['css'] = array('lims/ztree/zTreeStyle.css');

//数据处理
$all_node = all_site_type_data();

if(count($all_node))
{
    $tree = treeArray($all_node,0);
    $zNodes= json_encode($tree[0]);

}else{
    $zNodes= json_encode(['name'=>'站点标签设置','id'=>1]);
}




disp('system_settings/site_type_set/tree');