<?php

/**
 * 功能：对 site_type 进行一系列变换,得到可能需要的变量
 * 作者：罗磊
 * 日期：2014-04-30
 * 描述：
*/

//任务类型
$rw_type = array(
    '站网' => '计划任务',
    '委托' => '委托任务',
    '临时' => '临时任务',
    '全部' => '全部'
);

$_sql_1=array(
    '站网' => ' and `sites`.`site_type`=0 ',
    '临时' => ' and `sites`.`site_type`=1 ',
    '委托' => ' and `sites`.`site_type`=2 ',
    '全部' => '',
);
$_sql_2=array(
    '站网' => ' and `site_type`=0 ',
    '临时' => ' and `site_type`=3 ',//由1修改为3
    '委托' => ' and `site_type`=2 ',
    '全部' => '',
);

//如果得到一个 cyd_bh 自动得出站点性质
$site_type_flag = array(
    'A' => '站网',
    'B' => '委托',
    'C' => '临时'
);

if( $_REQUEST['cyd_bh'] && $_REQUEST['cyd_bh'] != '全部' ) {
    $site_type_key = $_REQUEST['cyd_bh']{0};
    if ( in_array( $site_type_key, array( "A", "B", "C" ) ) )
        $_GET['site_type'] = $_POST['site_type'] = $_REQUEST['site_type'] = $site_type_flag[$site_type_key];
    else
        exit( '有问题的采样单编号' . $_REQUEST['cyd_bh'] );
}

if( $_REQUEST['cyd_id'] ) { 
    $cyd = $DB->fetch_one_assoc("SELECT * FROM `cy` WHERE `id` = {$_REQUEST['cyd_id']}" );
    $_GET['site_type'] = $_POST['site_type'] = $_REQUEST['site_type'] = $flag_site[$cyd['site_type']];
    $cyd['flag']=$flag[$cyd['status']];
}

if( !$_REQUEST['site_type'] ) {
    $_REQUEST['site_type'] = $_GET['site_type'] = $_POST['site_type'] = '站网';
}else 
    $_GET['site_type'] = $_POST['site_type'] = $_REQUEST['site_type'];


#至此,已确保 $_REQUEST['site_type'] 不会为空
#$_REQUEST['site_type'] 取值范围: 全部,站网,临时,委托
if( $_REQUEST['site_type'] != '全部' ) { 
    $temporder = $rw_type[$_REQUEST['site_type']];
    $SITE_TYPE = $site_flag[$_REQUEST['site_type']]; //数字形式的 site_type 0,1或2

    //事先准备好样品编号前缀
    $flag_pre = array('A','A','B'); //站网A 临时A 委托B
    $_pre = $flag_pre[$SITE_TYPE] . date('Ym');
}
//得到按站点类别查询的 sql 片段
$_site_type = $_sql_2[$_REQUEST['site_type']];
$_SITE_TYPE = $_sql_1[$_REQUEST['site_type']];

while( $aSiteType = each( $rw_type ) ) {
    $aSiteType = $aSiteType['key'];
    if( $aSiteType != $_REQUEST['site_type']) 
        $_site_types .= "<option value='$aSiteType'>$aSiteType</option>\n"; //含全部的站点类型列表
    if( $aSiteType != $_REQUEST['site_type'] && $aSiteType!='全部') 
        $_s_t .= "<option value='$aSiteType'>$aSiteType</option>\n"; //不含全部
}
reset($rw_type);

//$rw="<option value='$_REQUEST[taskType]'>$_REQUEST[taskType]"; //周期
//while($a=each($rw_type)) if($a['value']!=$_REQUEST['taskType']) $rw.="<option vlaue='$a[value]'>$a[value]\n";


/*在必要时得到样品编号的全称*/
function get_full_bar_code($bar_code){
    global $_pre;
    if( strlen( $bar_code ) < 6 ) 
        return $_pre . $bar_code; 
    else 
        return $bar_code;
}



?>
