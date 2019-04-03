<?php
/**
* 功能：多合一化验单配置表
* 作者：Mr Zhou
* 日期：2014-12-03
* 描述：配置多合一项目
*/
/*
	多合一化验单分主项目和副项目 主项目可以使虚拟项目也可以是真实项目
	$dhy_arr['str1'] 含有所有的多合一项目vid的变量
	$dhy_arr['str2'] 含有所有副项vid的变量
	$dhy_arr['xm'][主项目] = array(vid[,vid]);
	$dhy_arr['vd'][主项目][vid] = array('vid'=>'vd26','_vd0'=>'vd7');
	$dhy_arr['ad'][主项目][vid] = array('vd0'=>'需要比设置的保留位数多保留几位');
	$dhy_arr['table_name'] 多合一化验单模板文件名，根据模板名判断是否需要跳转到主项目
*/

$dhy_arr['str2']=$dhy_arr['str1']='';

//将多合一化验单模板的文件名添加到此处
$table_name = ['rlf_ca_mg','rlf_zjd'];

// 173钙174镁
$dhy_arr['xm'][173] = array(173,174);
$dhy_arr['vd'][173][173] = array('vd0'=>'vd11','_vd0'=>'vd10');
$dhy_arr['vd'][173][174] = array('vd0'=>'vd9','_vd0'=>'vd8');

	
//125总碱度188重碳酸盐189碳酸盐
$dhy_arr['xm'][125] = array(125,188,189);
$dhy_arr['vd'][125][125] = array('vd0'=>'vd21','_vd0'=>'vd22');
$dhy_arr['vd'][125][188] = array('vd0'=>'vd18','_vd0'=>'vd20');
$dhy_arr['vd'][125][189] = array('vd0'=>'vd17','_vd0'=>'vd19');


$str1=$str2=array();
//根据配置自动生成一些参数
foreach($dhy_arr['xm'] as $z_vid => $vid_arr){
	$f_vid = array();
	foreach ($vid_arr as $key => $vid) {
		$dhy_arr[$vid]=$z_vid;
		if($z_vid!=$vid){
			$f_vid[] = $vid;
		}
	}
	$str1 = array_merge($str1,$vid_arr);
	$str2 = array_merge($str2,$f_vid);
}
$dhy_arr['str1'] = implode(',',$str1);
$dhy_arr['str2'] = implode(',',$str2);
$dhy_arr['table_name'] = $table_name;