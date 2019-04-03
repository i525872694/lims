<?php
/*
 * 数据统计获取、评价、相关函数
*/
/**
 *功能：获取出系统中各检测项目的名称
 *参数： 无
 *返回：arr[
            [vid]   => 项目默认名称
            ['jcbz_xm_name'][water_type][vid] => 
 **/
function get_xm_name(){
    global $DB;
    $xm_name_list = [];
    //查询出所有的项目名称
    $xm_sql="SELECT id,value_C FROM assay_value";
    $xm_query=$DB->query($xm_sql);
    while($xm_rs=$DB->fetch_assoc($xm_query)){
        $xm_name_list[$xm_rs['id']]=$xm_rs['value_C'];
    }
    //查询出每种水样类型下项目的名称
    $jcbz_sql="SELECT aj.vid,aj.dw,n.module_value2 as water_type,aj.value_C,aj.xz,aj.panduanyiju FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
    $jcbz_query=$DB->query($jcbz_sql);
    while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
        $xm_name_list['jcbz_xm_name'][$jcbz_rs['water_type']][$jcbz_rs['vid']]=$jcbz_rs['value_C'];
    }
    return $xm_name_list;
}
//检测次数、合格次数
function count_detect_num($group,$xm_pingjia_result)
{
	$result = [];
	foreach($group as $group_name=>$cid_list)
	{
		foreach($cid_list as $cid)
		{
			$data_group = $xm_pingjia_result[$cid];
			foreach($data_group as $xmid=>$info)
			{
				$result[$group_name][$xmid][$info['status']]++;
				$result[$group_name][$xmid]['total']++;
			}
		}
	}
	foreach($result as $group_name=>$info_list)
	{
		foreach($info_list as $xmid=>$info)
		{
			$result[$group_name][$xmid]['hegelv']= _round($info['yes']/$info['total']*100,2);
			if(!array_key_exists('yes',$info)) $result[$group_name][$xmid]['yes']=$info['total']-$info['no'];
			if(!array_key_exists('no',$info)) $result[$group_name][$xmid]['no']=$info['total']-$info['yes'];
		}
	}
	return $result;
}

//水质类别 个数统计、长度统计
function count_quality_num($tmp_group ,$site_pingjia_result,$site_data){
	$result=[];

	foreach($tmp_group as $group_name=>$cid_list)
	{
		foreach($cid_list as $cid)
		{
			$now_quality = $site_pingjia_result[$cid];
	
			$result[$group_name][$now_quality['now_quality']]['num']++;
			$result[$group_name][$now_quality['now_quality']]['cids'][]=$cid;
			
			$length_info = $site_data[$cid];
		    $result[$group_name][$now_quality['now_quality']]['viver_length']+=$length_info['viver_length'];
			
		}
	}
	return  $result;
}
 
//$a = max_min_avg($tmp_group ,$data_order);

/**
 *功能：获取父级水样类型
 *参数：group:函数group_allot返回值
 *参数：data:函数report_get_order返回值
 *返回：[分组项]=>[
 *          [项目id]=>[
 *              'unit'=>?,
 *              'value>['cid'=>?,...],
 *              'max'=>?,
 *              'min'=>?,
 *              'avg=>?
 *          ]
 * ]
 **/
function max_min_avg($group,$data)
{
    $result=[];
    foreach($group as $group_name=>$cid_list)
    {
        $one_group = [];
        foreach($cid_list as $cid)
        {
            $one_data = $data[$cid];
            foreach($one_data as $xmid=>$value)
            {
                $one_group[$xmid]['unit']=$value['unit'];
                $one_group[$xmid]['value'][$value['cid']]=$value['vd0'];
                //$one_group[$xmid]['tid'][]=$value['tid'];
            }
        }
        
            
        foreach($one_group as $xmid=>$value)
        {
            $v_list = $value['value'];
            $tag_xiaoyu=false;
            $tag_zhongwen=0;
            $tag_zhongwen_content='';
            
            $float_length = 0 ;//小数点修约
        
            //整理数据
            foreach($v_list as $k=>$item)
            {
                //小于<
                if($item[0] == '<')
                {
                    $tag_xiaoyu = floatval(substr($item,1));
                    $v_list[$k]=$tag_xiaoyu/2;
                }
                elseif(preg_match("/[\x7f-\xff]/", $item)) {
                    $tag_zhongwen = intval($tag_zhongwen)+1;
                    
                    if(!$tag_zhongwen_content) $tag_zhongwen_content=$item;
                    
                    $v_list[$k]=0;
                }else{
                    
                     $split = explode('.',$item);
                     if(count($split )>1){
                         $leng_1 = strlen($split[count($split )-1]);
                         if($float_length<$leng_1)
                         {
                             $float_length=$leng_1;
                         }
                     }
                    $v_list[$k]=floatval($item);
                }
            }
            //正常情况下
            $one_group[$xmid]['max']=max($v_list);
            $one_group[$xmid]['min']=min($v_list);
            
            $avg = array_sum($v_list)/count($v_list);
            
            $avg = _round($avg,$float_length);
            $one_group[$xmid]['avg']=$avg;
                
            //对于有<符号的情况(假设为'<10'),最小值就是'<10',
            //平均值如果小于10,那么平均值也为'<10'
            //最大值如果小于10,那么最大值也为'<10'
            if($tag_xiaoyu)
            {
                $one_group[$xmid]['min']='<'.$tag_xiaoyu;
                if($one_group[$xmid]['max']<$tag_xiaoyu){
                    $one_group[$xmid]['max']='<'.$tag_xiaoyu;
                    $one_group[$xmid]['avg']='<'.$tag_xiaoyu;
                }
            }
            
            //对于有中文的情况(假设为'无法测量'),最小值就是'无法测量',
            //如果全部值都是'无法测量',那么平均值和最大值也是'无法测量'
            if($tag_zhongwen)
            {
                $one_group[$xmid]['min']=$tag_zhongwen_content;
                if($tag_zhongwen==count($v_list)){
                    $one_group[$xmid]['max']=$tag_zhongwen_content;
                    $one_group[$xmid]['avg']=$tag_zhongwen_content;
                }
            }
        }
        $result[$group_name]=$one_group;
    }
    return $result;
}
/**
 *功能：获取父级水样类型
 *参数：water_type:水样类型
 *返回：arr[water_type] =>parent_id
 **/
function get_parent_type($water_type_list){
    global $DB;
    //生成 sql获取条件
    $sql_where      = '';
    if(count($water_type_list)){
        $sql_where      .= " AND `id` in (".implode(',',$water_type_list).")";
    }
    //查询父级水样类型
    $leixing_arr    = array();
    $leixing_sql    = $DB->query("SELECT * FROM `leixing` WHERE 1 {$sql_where}");
    while($leixing_rs = $DB->fetch_assoc($leixing_sql)){
        $parent_id      = '';
        if(!empty($leixing_rs['parent_id'])){
            $parent_id      = $leixing_rs['parent_id'];
        }else{
            $parent_id      = $leixing_rs['id'];
        }
        $leixing_arr[$leixing_rs['id']] = $parent_id;
    }
    return $leixing_arr;
}
/**
 * 获取检测标准列表（名称及id）
 * @param $water_type_list  水样类型id数组
 * @param $vid_list   项目id数组
 * @return array   以水样类型id为键名的检测标准列表
 */
/*function report_get_bzbh($water_type_list)
{
    global $DB;
    if (empty($water_type_list)) {
        return '请传入水样类型id';
    }
    $jcbz_bh = array();
    $sql = "SELECT * FROM `n_set` WHERE `module_name`='jcbz_bh' AND module_value2 in (" . implode(',', $water_type_list) . ")";
    $query = $DB->query($sql);
    while ($row = $DB->fetch_assoc($query)) {
        $jcbz_bh[$row['module_value2']][$row['id']] = $row;
    }
    return $jcbz_bh;
}*/
/**
 * 获取检测标准限值
 * @param $bzbh_id_list  检测标准编号id ,n_set表id数组
 * @param $vid_list   项目id数组
 * @return array   以标准编号id为键名的检测标准
 */
function get_jcbz($water_type_list,$vid_list = []){
    global $DB;
     if (empty($water_type_list)) {
        return '请传入水样类型id';
    }
    //生成 sql获取条件
    $where_sql = [];
    $where_sql['water_type']   = "AND n.module_value2 in (" . implode(',', $water_type_list) . ") ";
    if (count($vid_list)) {
        $where_sql['vid'] = " AND bz.`vid` in (" . implode(',', $vid_list) . ")";
    }
    //获取标准限值数据(子水样类型没有限值时，取父级水样类型的限值)
    $jcbz_arr = [];
    $sql = "SELECT bz.*,n.module_value1 AS jcbz_name,n.module_value2 AS bz_water_type,n.module_value4 AS bz_mark 
            FROM `assay_jcbz` AS bz
            LEFT JOIN `n_set` AS n ON bz.jcbz_bh_id=n.id
            WHERE n.`module_name`='jcbz_bh' ".implode(' ',$where_sql)."";
        $jcbz_sql = $DB->query($sql);
    while ($jcbz_rs = $DB->fetch_assoc($jcbz_sql)) {
        if (empty($jcbz_rs['panduanyiju'])) {
            $jcbz_rs['panduanyiju'] = $jcbz_rs['xz'];
        }
        $jcbz_arr[$jcbz_rs['bz_water_type']]['jcbz_content']['id']          = $jcbz_rs['jcbz_bh_id'];//标准id
        $jcbz_arr[$jcbz_rs['bz_water_type']]['jcbz_content']['jcbz_name']  = $jcbz_rs['jcbz_name'];//标准名称
        $jcbz_arr[$jcbz_rs['bz_water_type']]['jcbz_content']['jcbz_mark']  = $jcbz_rs['bz_mark'];//标准标识（II类 等标识）
        $jcbz_arr[$jcbz_rs['bz_water_type']][$jcbz_rs['vid']][$jcbz_rs['bz_mark']]['use_xz']    = $jcbz_rs['panduanyiju'];//判定依据
        $jcbz_arr[$jcbz_rs['bz_water_type']][$jcbz_rs['vid']][$jcbz_rs['bz_mark']]['show_xz']   = $jcbz_rs['xz'];//限值标准
        $jcbz_arr[$jcbz_rs['bz_water_type']][$jcbz_rs['vid']][$jcbz_rs['bz_mark']]['unit']      = $jcbz_rs['dw'];//限值单位
    }
    return $jcbz_arr;
}
/**
 * @param $begin_date
 * @param $end_date
 * @param $sid_list  站点id集合
 * @return array    现场记录单id对应的信息
 */
function report_get_rec($begin_date, $end_date, $sid_list)
{
    global $DB;
    $report_rec = [];
    $sql = "select s.*,cr.* from cy_rec AS cr 
            LEFT JOIN `sites` AS s ON cr.sid=s.id
            where cy_date >='$begin_date' AND cy_date <='$end_date'
            AND sid in ('" . implode("','", $sid_list) . "')
            and (`zk_flag`>='0' or `zk_flag`='-6')
            ORDER BY cr.cy_date
    ";
    $rows = $DB->query($sql);
    while ($row = $DB->fetch_assoc($rows)) {
        $report_rec[$row['id']] = $row;
        $report_rec[$row['id']]['viver_length'] = '300';//暂时增加河长字段，方便后面程序测试
        $report_rec['water_type_list'][]    = $row['water_type'];//集中获取水样类型id
    }
    return $report_rec;
}
/**
 * @param $cid_list  cy_rec表id集合
 * @param $vid_list   assay_value表集合(项目集合)
 * @return array|string  现场记录单id对应的项目结果
 */
function report_get_order($cid_list, $vid_list = [])
{
    global $DB;
    if (empty($cid_list)) {
        return '请传入必要参数cid';
    }
    //查询下化验单数据在什么状态下能显示到报告上
    $show_shuju_arr = array();
    $show_shuju_old = $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
    if(!empty($show_shuju_old['module_value1'])){
            $show_shuju_arr = explode(",",$show_shuju_old['module_value1']);
    }
    //组装sql条件
    $sql_where = " AND ao.cid in (" . implode(',', $cid_list) . ")";
    if (count($vid_list)) {
        $sql_where .= " AND ao.vid in (" . implode(',', $vid_list) . ")";
    }
    /*if(!empty($show_shuju_arr)){
        $sql_where .= " AND ap.over in ('" . implode("','", $show_shuju_arr) . "')";
    }*/
    //获取结果数据
    $result_arr = array();
    $sql = "SELECT ap.unit,ap.over,ao.cid,ao.vid,ao.vd0,ao.ping_jun,ao.tid
                      FROM `assay_order` AS ao 
                      INNER JOIN `assay_pay` AS ap ON ao.tid=ap.id 
                      and `ao`.sid > 0
                      and (`ao`.hy_flag >= 0 or `ao`.`hy_flag` in('-6','-20','-26'))
                      WHERE 1 {$sql_where}";
    $result_que = $DB->query($sql);
    while ($result_rs = $DB->fetch_assoc($result_que)) {
        if(empty($result_rs['ping_jun']) && in_array($result_rs['hy_flag'],array('-6','-20','-26'))){
            continue;
        }
        if(!empty($result_rs['ping_jun'])){
            $result_rs['vd0'] = $result_rs['ping_jun'];
        }
        if(!in_array($result_rs['over'],$show_shuju_arr)){
            $result_rs['vd0']   = '';
        }
        unset($result_rs['ping_jun']);
        $result_arr[$result_rs['cid']][$result_rs['vid']] = $result_rs;
    }
    return $result_arr;
}
/**
 * 检测标准判断前的特殊处理函数（特殊项目，特殊标准均在这里处理）
 * @param $vid  项目id
 * @param $jcbz   jcbz限值
 * @return array|string  判定标识及标准中的数字集合
 */
function jcbz_Pretreatment($vid,$jcbz){
    /*
    //以下特殊项目在数据库里 pandingyiju中填写。不写到代码中
    switch ($vid) {
        case 96:
            $jcbz   = "=无";
            break;
        case 95:
            $jcbz   = "=0";
            break;
        case 1:
            $jcbz   = "≤0";
            break;
    }*/
    $jcbz = trim($jcbz);
    //统一特殊字符
    $jcbz = str_replace(array('＜','＞','～','＝'), array('<','>','~','='), $jcbz);
    //转换科学计数法(仅适用于一个比较符号的情况)
    if(stristr($jcbz,"E")){
        preg_match("/(<|≤|>|≥)/",$jcbz,$pp_jcxz);
        //去掉比较符号
        $jcbz   = str_replace(array('<','>','≤','≥'), '', $jcbz);
        //获取转换后应该保留几位小数
        $count  = strlen(explode('E',$jcbz)[0]) + abs(explode('E',$jcbz)[1]);
        $jcbz   = $pp_jcxz[0].number_format($jcbz,$count);
    }
    preg_match_all('/(\d+\.?\d*)|[~|&]/',$jcbz,$xz_value);//找出限值中的数字和特殊连接标识
    preg_match_all('/[<≤>≥~=|&]/',$jcbz,$mark_value);//找出限值中的判定标识
    $jcbz           = implode('',$xz_value[0]);
    $panding_mark   = implode('',$mark_value[0]);
    return array($panding_mark,$jcbz);
}
/**
 * 超标判定函数
 * @param $panding_mark  判定标识字符串（ <,≤,>,≥,~ 或 以 &,| 为连接符的任意组合）
 * @param $jcbz   检测标准（不带判定标识）
 * @param $result  检测结果
 * @return array|string  判定结果及超标倍数
 */
function result_is_chaobiao($panding_mark,$jcbz,$result){
    $result   = str_replace('＜', '<', $result);//转换下小于号，防止用户手动输入的数据无法判断
    $beishu = '';//超标倍数
    $over   = 4;//0合格、1不合格、2无检测标准、3无结果值、4无法判定
    if(in_array($jcbz,array('','-','\\','--'))){
        $over   = 2;//无检测标准
    }else if('' == $result){
        $over   = 3;//无结果值
    }else if(stristr($result,'<') || '未检出' == $result){
        $over   = 0;//小于检出限的结果，直接判定为合格
    }else if(stristr($panding_mark,"|")){//多个或条件的标准 如：pH 地下水V类标准 <5.5|>9
        $mark_arr   = explode("|",$panding_mark);
        $jcbz_arr   = explode('|',$jcbz);
        foreach ($mark_arr as $key=>$value_mark) {
            $chaobiao_status    = result_is_chaobiao($value_mark,$jcbz_arr[$key],$result);
            // |条件判定时，有一个条件符合即算合格
            if($chaobiao_status['status'] == '0'){
                $over   = 0;
                break;
            }else{
                $over   = 1;
            }
        }
    }else if(stristr($panding_mark,"&")){//多个并列条件的标准 如：6.5<x<8.5
        $mark_arr       = explode("&",$panding_mark);
        $jcbz_arr       = explode('&',$jcbz);
        $chaobiao_count = array();
        foreach ($mark_arr as $key=>$value_mark) {
            $chaobiao_status    = result_is_chaobiao($value_mark,$jcbz_arr[$key],$result);
            $chaobiao_count[]   = $chaobiao_status['status'];
        }
        $over   = (array_sum($chaobiao_count)<1) ? 0 : 1;//每个判定条件均合格，才能判定为该数据合格
    }else{
        switch ($panding_mark) {
            case '=':
                $over = ($jcbz=$result)    ? 0 : 1;
                break;
            case '<':
                $over = ($result<$jcbz)    ? 0 : 1;
                break;
            case '≤':
                $over = ($result<=$jcbz)   ? 0 : 1;
                break;
            case '>':
                $over = ($result>$jcbz)   ? 0 : 1;
                break;
            case '≥':
                $over = ($result>=$jcbz)   ? 0 : 1;
                break;
            case '~':
                //不小于前一个数，不大于后一个数
                $jcbz = explode('~',$jcbz);
                $over = ($result>=$jcbz[0] && $result<=$jcbz[1]) ? 0 : 1;
                break;
            default:
                # code...
                break;
        }
        //超标倍数判断(范围的情况暂时没法算超标倍数)
        $beishu = '';
        if($over == '1' && !is_array($jcbz)){
            $beishu = ($result-$jcbz)/$jcbz;
            //超标倍数 数据大于等于0.1时，小数点后保留1位，小于0.1时小数点后保留2位
            if($beishu < '0.1'){
                $beishu = _round($beishu,2);
            }else{
                $beishu = _round($beishu,1);
            }
        }
    }
    return array('status'=>$over,'beishu'=>$beishu);
}
/**
 * 单项目水质类别判定
 * @param $vid  项目id
 * @param $result  结果值
 * @param $jcbz_list jcbz数组 arr[$vid][I类]=>标准值
 * @param $intent  水质目标 （n_set表module_value4字段I类II类...V类）
 * @return 数组(目标水质、当前水质、是否符合、超标倍数)
 */
function xm_water_quality($vid,$result,$jcbz_list,$intent_quality=''){
    if(!empty($jcbz_list[$vid])){
        ksort($jcbz_list[$vid]);//按照排序，这样I类水会最先判断
        //从I类开始循环每个标准类别并判断结果值属于哪个标准类别
        $quality_name   = $pingjia_status = '';//水质类别名称、是否超标
        $chaobiao_beishu= [];//超标倍数
        foreach ($jcbz_list[$vid] as $key_mark => $value_jcbz) {
            //判断单位是否一样
            $tmp_panding    = jcbz_Pretreatment($vid,$value_jcbz['use_xz']);//特殊项目处理
            $is_chaobiao    = result_is_chaobiao($tmp_panding[0],$tmp_panding[1],$result);//合格判断
            //记录水质目标类别的超标倍数
            if($key_mark == $intent_quality){
                $chaobiao_beishu    = $is_chaobiao;
            }
            //记录当前水质
            if($is_chaobiao['status'] != '1'){
                $quality_name   = $key_mark;//评价结果名称：几类水
                break;
            }
        }
        //劣五类判断
        if(count($jcbz_list[$vid]) >1 && $quality_name == ''){
            $quality_name   = '劣Ⅴ类';
        }
        //是否超标判定
        $pingjia_status = ($chaobiao_beishu['status']=='1')?'no':'yes';
        //生活饮用水等单一标准时，将当前水质的文字转换成 合格、不合格
        if(count($jcbz_list[$vid]) == 1){
            $quality_name   = ($pingjia_status =='yes')?'合格':'不合格';
        }
    }
    //目标水质、当前水质、是否符合、超标倍数
    return array("intent_quality"=>$intent_quality,"now_quality"=>$quality_name,"status"=>$pingjia_status,"beishu"=>$chaobiao_beishu['beishu'],"pingding_yiju"=>$jcbz_list[$vid]);
}
/**
 *功能：判断特殊项目是否参与评价
 *参数：goto_pingjia_xm_list:参评项目id，sid:站点id，result_arr:结果值数组arr[vid]=>结果;
 *返回值：最终参评项目 arr[vid1,vid2...]
 **/
function goto_pingjia_xm($goto_pingjia_xm_list,$sid,$result_list){
    global $global;
    //没有检测结果时，不参与评价
    $is_pingjia = "no";
    $goto_pingjia_xm_arr    = [];
    foreach ($goto_pingjia_xm_list as $vid) {
        if($result_list[$vid] != '' || $result_list[$vid]=='0'){
            switch ($vid) {
                case '118'://化学需氧量, 化学需氧量和高锰酸盐指数同时检测时，化学需氧量结果大于30时才参与评价
                    if(empty($result_list['104']) || (!stristr($result_list['118'],'<') && $result_list['118'] > '30')){
                        $is_pingjia = 'yes';
                    }
                    break;
                case '104'://高锰酸盐指数，化学需氧量和高锰酸盐指数同时检测时，化学需氧量<=30时，高锰酸盐才参与评价
                    if(empty($result_list['118']) || stristr($result_list['118']['vd0'],'<') || $result_list['118']['vd0'] <= '30'){
                        $is_pingjia = 'yes';
                    }
                    break;
                case '121'://总氮
                    if(@in_array($sid,$global['zongdan_pingjia_sites'])){
                        $is_pingjia = 'yes';
                    }
                    break;
                default:
                    $is_pingjia = 'yes';
                    break;
            }
            //记录下符合条件的参评项目
            if($is_pingjia == 'yes'){
                $goto_pingjia_xm_arr[]  = $vid;
            }
        }
    }
    //参与评价的项目
    return $goto_pingjia_xm_arr;
}
/**
 * 站点、水功能区等水质类别判定
 * @param xm_quality_list 评价水质类别的项目数组 arr([vid]=>类别、超标倍数、是否超标等)
 * @param goto_pingjia_xm_list   参评项目
 * @return 当前水质、超标污染物及超标倍数
 */
function site_water_quality($xm_quality_list,$goto_pingjia_xm_list){

    $xm_arr=$_SESSION['assayvalueC'];
    $quality_int    = array("Ⅰ类"=>1,"Ⅱ类"=>2,"Ⅲ类"=>3,"Ⅳ类"=>4,"Ⅴ类"=>5,"劣Ⅴ类"=>6,"合格"=>7,"不合格"=>8);
    $pingjia_status = 'yes';//超标状态
    $now_quality    = '1';//当前水质代表数字
    $chaobiao_xm    = [];//超标项目
    foreach ($goto_pingjia_xm_list as $vid) {
        $xm_name    = ($xm_arr[$vid])?$xm_arr[$vid]:$vid;//项目名称
        $xm_quality_int    = $quality_int[$xm_quality_list[$vid]['now_quality']];//水质类别的代表数字
        $xm_chaobiao_status= $xm_quality_list[$vid]['status'];//超标状态
        if($xm_quality_int > $now_quality){
            $now_quality    = $xm_quality_int;//记录下项目中最差的水质类别
        }
        //记录下超标污染物和超标倍数
        if($xm_chaobiao_status =='no'){
            $pingjia_status = 'no';
            $chaobiao_xm[$vid]['chaobiao_xm']   = $xm_name;//超标污染物名称
            $chaobiao_xm[$vid]['beishu']        = $xm_quality_list[$vid]['beishu'];//超标倍数
        }
    }
    $now_quality_name   = array_search($now_quality,$quality_int);//水质类别名称
    //当前水质、是否超标、超标项目及超标倍数
    return array('now_quality'=>$now_quality_name,'status'=>$pingjia_status,'chaobiao_xm'=>$chaobiao_xm);
}
/**
 * 根据分类需要将cid分组
 * @param $group_basis 分组依据 'cy_date'、'site_sgnq'...
 * @param $cid_list。 cid及站点基础属性的数组。 函数report_get_rec的返回值
 * @return arr[分组依据] = [cid1、cid2、cid3]
 */
function group_allot($group_basis,$cid_list){
    $group_cid  = [];
    foreach ($cid_list as $cid => $value_arr) {
        $group_cid[$value_arr[$group_basis]][]    = $cid;
    }
    return $group_cid;
}
/**
 * 需要分类的报表统一入口
 * @param $group_list 分组后的cid
 * @param return_type_list 需要返回的结果 [max_min_avg、detect_count、quality_count]
 * @return 根据需要组装的数组 arr[分组] = 极值、均值、次数、长度等
 */
function report_entrance($group_list,$return_type_list){
    $result = [];
    foreach ($return_type_list as $return_type) {
        switch ($return_type) {
            case 'max_min_avg'://极值（最大值、最小值）、平均值
                $group_result   = max_min_avg($group_list);
                break;
            case 'detect_count'://检测次数、合格次数
                $group_result   = count_detect_num($group_list);
                break;
            case 'quality_count'://水质类别个数统计、长度统计
                $group_result   = count_quality_num($group_list);
                break;
        }
        $result[$return_type]=$group_result;
    }
    return $result;
}
/**
 * 功能：根据报告统计周期 返回 时间选择控件
 * 返回值：时间选择控件的html代码
 * 描述：
*/
function choose_date_html($set_content,$type='月报'){
    switch ($type) {
        case '日报':
            if(empty($set_content['before_days'])){
                $set_content['before_days'] = 0;
            }
            $choose_date_html   = "起始日期：获取<input type='number' field='result_set' name='choose_date[before_days]' value='{$set_content['before_days']}' min='0' class='before_days' style='width:100px;' />天前的数据(默认当天)";
            /*$choose_date_html = "起始日期：获取<div class=\"ace-spinner touch-spinner\" style=\"width: 140px;\">
                                                    <div class=\"input-group\">
                                                        获取<input type=\"text\" class=\"input-mini spinner-input form-control\" id=\"spinner2\" maxlength=\"5\">天前的数据(默认当天)
                                                    </div>
                                                </div>";
                                                */
            break;
        case '周报':
            $week1      = $set_content['week1'];
            $week2      = empty($set_content['week2'])?'7':$set_content['week2'];
            $week_type  = $set_content['week_type'];
            //天数
            $week1_options  = $week2_options    = '';
            $week_arr   = array('周一','周二','周三','周四','周五','周六','周日');
            foreach($week_arr as $value){
                $selected1      = ($week1==$value)?'selected':'';
                $selected2      = ($week2==$value)?'selected':'';
                $week1_options  .= "<option value='{$value}' {$selected1}>{$value}</option>";
                $week2_options  .= "<option value='{$value}' {$selected2}>{$value}</option>";
            }
            $selected_month     = ('上周'==$week_type)?'selected':'';
            $choose_date_html   = "起始日期:<select name='choose_date[week_type]' field='result_set' ><option value='本周'>本周</option></select>
                                            <select name='choose_date[week1]' id='week1' field='result_set' >{$week1_options}</select><span style='letter-spacing:20px'>&nbsp;</span>
                                    终止日期:<select field='result_set'><option value='本周'>本周</option></select>
                                            <select name='choose_date[week2]' id='week2' field='result_set'>{$week2_options}</select>";
            break;
        case '月报':
            $day1       = $set_content['day1'];
            $day2       = $set_content['day2'];
            $month_type = $set_content['month_type'];
            //天数
            $day1_options   = $day2_options = "";
            $month_arr      = ['月初','月末'];
            foreach ($month_arr as $value){
                $selected1      = ($day1==$value)?'selected':'';
                $selected2      = ($day2==$value)?'selected':'';
                $day1_options   .= "<option value='{$value}' {$selected1}>{$value}</option>";
                $day2_options   .= "<option value='{$value}' {$selected2}>{$value}</option>";
            }
            for($t=1;$t<=31;$t++){
                if($t<10){
                    $t  = '0'.$t;
                }
                $selected1      = ($day1==$t)?'selected':'';
                $selected2      = ($day2==$t)?'selected':'';
                $day1_options   .= "<option value='{$t}' {$selected1}>{$t}</option>";
                $day2_options   .= "<option value='{$t}' {$selected2}>{$t}</option>";
            }
            $selected1_month        = ('上月'==$month_type)?'selected':'';
            $selected2_month        = ('本月'==$month_type)?'selected':''; 
            $choose_date_html   = "起始日期:<select name='choose_date[month_type]' field='result_set'><option value=''>--请选择--</option><option value='本月' {$selected2_month}>本月</option><option value='上月' {$selected1_month}>上月</option></select>
                                            <select name='choose_date[day1]' id='day1' field='result_set'><option value=''>--请选择--</option>{$day1_options}</select><span style='letter-spacing:20px'>&nbsp;</span>
                                    终止日期:<select><option value='本月'>本月</option></select>
                                            <select name='choose_date[day2]' id='day2' field='result_set'><option value=''>--请选择--</option>{$day2_options}</select>";
            break;
        case '年报':
            //起始时间
            if(!empty($set_content['begin_date'])){
                if(substr_count($set_content['begin_date'],'-') < 2){
                    $set_content['begin_date']  = date('Y')."-".$set_content['begin_date'];
                }
                $begin_date = $set_content['begin_date'];
            }else{
                $begin_date = date('Y-01-01');
            }
            //终止时间
            if(!empty($set_content['end_date'])){
                if(substr_count($set_content['end_date'],'-') < 2){
                    $set_content['end_date']    = date('Y')."-".$set_content['end_date'];
                }
                $end_date   = $set_content['end_date'];
            }else{
                $end_date   = date('Y-12-31');
            }
            $choose_date_html   = "起始日期:<input type=\"text\" size=\"10\" name=\"choose_date[begin_date]\" id=\"begin_date\" field='result_set' class=\"date-picker\" value=".$begin_date." />
                                    终止日期:<input type=\"text\" size=\"10\" name=\"choose_date[end_date]\" id=\"end_date\" field='result_set' class=\"date-picker\"   value=".$end_date." />";
            break;
        default:
            //起始时间
            if(!empty($set_content['begin_date'])){
                $begin_date = $set_content['begin_date'];
            }else{
                $begin_date = date('Y-m-d',strtotime('- 1 month'));
            }
            //终止时间
            if(!empty($set_content['end_date'])){
                $end_date   = $set_content['end_date'];
            }else{
                $end_date   = date('Y-m-d');
            }
            $choose_date_html   = "起始日期:<input type=\"text\" size=\"10\" name=\"choose_date[begin_date]\" id=\"begin_date\" class=\"date-picker\" field='result_set' value=".$begin_date." />
                                    终止日期:<input type=\"text\" size=\"10\" name=\"choose_date[end_date]\" id=\"end_date\"  class=\"date-picker\"  field='result_set' value=".$end_date." />";
            break;
    }
    return $choose_date_html;
}






