<?php
//监测计划页面 的编辑接口
/*
可以通过本页面，  修改站点的检测项目。
增加或删除 批次内的站点
*/
include("../temp/config.php"); 

include("$rootdir/inc/Pinyin.php");
        $cid=get_int($_GET['cid']);

//获取 需要 修改站点的检测项目
if($_GET['type']=='get_xm' && $_GET['cid']>0){
        //查询本站点 的检测项目

        $s_xmr=$DB->fetch_one_assoc("select * from cy_rec where id='$cid'");
        $assay_value_arr=explode(',',$s_xmr['assay_values']);

        //得到项目名称
        $xmarr=array();
        $xmR=$DB->query("SELECT xmid, value_C
        FROM  `xmfa` 
        JOIN assay_value ON assay_value.id = xmfa.xmid ");
        while($r=$DB->fetch_assoc($xmR)){
        $xmarr[$r['xmid']]=$r['value_C'];
        $all_xmid[$r['xmid']]=$r[xmid];
        }

        //得到 所有 项目中不在当前站点中的项目ID
        $no_arr=array_diff($all_xmid,$assay_value_arr);
        //得到当前选中项目列表
        $ckxmhtml=xmhtml_byxmid($assay_value_arr,$xmarr,"checked");
        //没有被选择的项目列表
        $nockxmhtml=xmhtml_byxmid($no_arr,$xmarr," ");

            echo temp("update_xm");

}

if($_GET['type']=='update_xm' &&$_GET['cid']>0 ){

$xm_str=implode(',',$_GET['xmids']);
 $DB->query("UPDATE  `cy_rec` SET  `assay_values` =  '$xm_str' WHERE   `id` ='$cid';");
echo "ok";
}

//传来一个 项目ID的数组  返回 html 显示内容
// 需要现实的项目id数组， xmarr 全部项目列表，用于获取名称，  checked 是否选中
function  xmhtml_byxmid($xmidarr,$xmarr ,$checked){
    foreach($xmidarr as $val){
        $xm_name=$xmarr[$val];
        $xmname_py=Pinyin::getShortPinyin($xm_name);

    $xmhtml.=" <label  data-val='$xmname_py $xm_name' class='xm-li'  for='xm-$val'> <input type='checkbox' name='xmids[]' id='xm-$val' $checked value='{$val}'> $xm_name </label>";

    }
    return $xmhtml;
}