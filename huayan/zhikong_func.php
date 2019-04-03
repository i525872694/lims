<?php
include_once $rootdir.'/huayan/ahlims.php';
/**
 * 函数名：check_zk
 * 功能：检查质控并计算质控结果
 * 作者：Mr Zhou
 * 日期：
 * 参数：int $ao_id $flag供本函数内部递归调用，传入true则不再递归
 * 返回值：
 * 功能描述：
*/
function check_zhi_kong( $ao_id,$jcx,$flag=false ){
    global $DB,$u;
    $fzx_id = FZX_ID;
    $r = $DB->fetch_one_assoc("SELECT * FROM `assay_order` WHERE `id`='{$ao_id}'");
    // 只有检测结果为数字时才做以下的质控计算
    if( !is_numeric($r['_vd0']) ){
        return clear_ao_zk_data($r['id']);
    }
    $tid        = $r['tid'];        //化验单id
    $cid        = $r['cid'];        //cy_rec表id
    $vid        = $r['vid'];        //项目id
    $hy_flag    = $r['hy_flag'];    //质控标识
    $bar_code   = $r['bar_code'];   //样品编号
    $r['jcx']   = trim($jcx);       //检出限
    switch( $r['hy_flag'] ) {
        case -20: //室内平行
            //找出此室内平行样的原样
            $sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND (`hy_flag` BETWEEN 20 AND 39 OR `hy_flag` >= 60) LIMIT 1";
            $sample = $DB->fetch_one_assoc($sql);
            if(!$sample['id']){
                error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品找不到平行原样,请与技术人员联络,出错行号:'.__LINE__);
            }
            update_pingxing2ao(calc($tid, $sample, $r, $jcx, $vid));
            break;
        case -40: case -46: case -60: case -66://加标
            if($r['hy_flag'] == -40){
                //找出加标原样 室内空白的flag是-2
                $sql_add_where = " AND (`hy_flag` BETWEEN 40 AND 69 OR `hy_flag`='-2')";
            }else if($r['hy_flag'] == -46){
                //这是现场平行B做加标，平行B的hy_flag是-6
                $sql_add_where = " AND `hy_flag` ='-6'";
            }else if($r['hy_flag'] == -66){
                //这是现场平行B做室内平行，室内平行B的hy_flag是-26
                $sql_add_where = " AND `hy_flag` ='-26'";
            }else{
                //这是室内平行做加标，室内平行B的hy_flag是-20
                $sql_add_where = " AND `hy_flag` ='-20'";
            }
            $sql    = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} {$sql_add_where} LIMIT 1";
            $sample = $DB->fetch_one_assoc($sql);
            if(!$sample['id']){
                error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品找不到加标原样,请与技术人员联络,出错行号:'.__LINE__);
            }
            update_jiabiao2ao(jbhs($sample,$r));
            break;
        case    1: //全程序空白
            //查找是否存在两条室内空白
            $sql    = "SELECT * FROM `assay_order` WHERE `tid`=$tid AND `vid`=$vid AND `hy_flag`='-2' ORDER BY `bar_code`";
            $_SNKB  = $DB->query($sql);
            if($DB->rows==2){
                $_snkb_1=$DB->fetch_assoc($_SNKB);
                $_snkb_2=$DB->fetch_assoc($_SNKB);
                // vd28是过程值，如果都存在过程值则使用过程值计算
                if($r['vd28'] != '' && $_snkb_1['vd28'] != '' && $_snkb_2['vd28'] != ''){
                    $r['vd0'] = $r['_vd0'] = $r['vd28'];
                    $_snkb_1['vd0'] = $_snkb_1['_vd0'] = $_snkb_1['vd28'];
                    $_snkb_2['vd0'] = $_snkb_2['_vd0'] = $_snkb_2['vd28'];
                }
                // 两室内空白计算平均值和相对偏差
                $jie_guo = calc($tid, $_snkb_1, $_snkb_2, $jcx, $vid);
                // 因为全程序空白和两室内空白的平均值计算相对偏差，所以vd0代表修约后的平均值，_vd0代表没有修约的
                $data_2 = array(
                    'vd0' => $jie_guo['avg']['vd0'],
                    '_vd0' => ($_snkb_1['_vd0']+$_snkb_2['_vd0'])/2,
                    'water_type' => 0
                );
                update_pingxing2ao(calc($tid, $data_2, $r, $jcx, $vid));
            }else{
                return clear_ao_zk_data($r['id']);
            }
            break;
        case    -2: //室内空白
            //同时找出两条室内空白
            $sql    = "SELECT * FROM `assay_order` WHERE `tid`=$tid AND `vid`=$vid AND `hy_flag`='-2' ORDER BY `bar_code`";
            $_SNKB  = $DB->query($sql);
            if($DB->rows==2){
                $_snkb_1=$DB->fetch_assoc($_SNKB);
                $_snkb_2=$DB->fetch_assoc($_SNKB);
                if($_snkb_1['vd28'] != '' && $_snkb_2['vd28'] != ''){
                    $xinhao = true;
                    $_snkb_1['vd0'] = $_snkb_1['_vd0'] = $_snkb_1['vd28'];
                    $_snkb_2['vd0'] = $_snkb_2['_vd0'] = $_snkb_2['vd28'];
                }
                update_pingxing2ao(calc($tid, $_snkb_1, $_snkb_2, $jcx, $vid));
            }else{
                return clear_ao_zk_data($r['id']);
            }
            break;
        case    3: case 23: case 43: case 63://标准样品
            //标准样品判断是否合格
            $sql = "SELECT bzwz_detail.*
                    FROM `bzwz_detail`
                        LEFT JOIN `cy_rec` ON `bzwz_detail`.`wz_id`=`cy_rec`.`by_id`
                        LEFT JOIN `assay_order` ON `assay_order`.`cid`=`cy_rec`.`id`
                    WHERE `assay_order`.`id`=$r[id] AND `bzwz_detail`.`vid`=$r[vid]";
            $by=$DB->fetch_one_assoc($sql);
            if($by['id']){
                //preg_match('/[\d\.]+/',$by['eligible_bound'],$result);
                $e_bound    = floatval(trim($by['eligible_bound']));
                $consistence= floatval(trim($by['consistence']));
                $t_bound    = abs($r['_vd0']-$consistence);
                $pj      = ($t_bound <= $e_bound)?'合格':'不合格';
                $pc      = round((abs(($r['_vd0']-$consistence))/$consistence)*100,2);
                $DB->query("UPDATE `assay_order` SET `ping_jia`='$pj',`xiang_dui_pian_cha`='$pc' WHERE `id`={$r['id']} ORDER BY `id` DESC LIMIT 1");
            }
            break;
        case    -4: //自控样
        case    -8: //02C和0.8C
            if('' != $r['vd29'] && '' != $r['vd30']){
                $zky = array(
                    'by_id'=>'',
                    'by_type'=>'',
                    'C_c' => $r['vd29'],
                    'by_unit'=>$r['vd31'],
                    'by_buquedingdu'=>$r['vd30'],
                    'by_buquedingdu_unit'=>$r['vd32'],
                );
                update_biaoyang2ao(xdwc($tid, $r, $zky, $jcx, $vid));
            }
            break;
        case    -6: //现场平行B样
            //找到现场平行A样
            $sql = "SELECT `id`,`vd0`,`_vd0`,`hy_flag` FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` IN(5,25,45,65)";
            $sample = $DB->fetch_one_assoc($sql);
            if(!$sample['id']){
                error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品找不到现场平行原样,请与技术人员联络,出错行号:'.__LINE__);
            }
            $vd0_r = $vd0_s = '';
            //检查现场平行A样是否做了质控，需要将其质控信息先计算完
            if($sample['hy_flag'] != 5){
                //如果现场平行A样做了室内平行需要计算出平均值 45是做加标 25,65表示做了平行
                if($sample['hy_flag']==25||$sample['hy_flag']==65){
                    $sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` ='-20'";
                    $snpx = $DB->fetch_one_assoc($sql);
                    // 记录原始的vd0的值
                    $vd0_s = $sample['vd0'];
                    $sample['vd0']  = $snpx['ping_jun'];
                    $sample['_vd0'] = ($sample['_vd0']+$snpx['_vd0'])/2;
                }
            }
            $sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `hy_flag` IN('-26','-46')";
            $query = $DB->query($sql);
            while ($zk_yang=$DB->fetch_assoc($query)) {
                if($zk_yang['id']){
                    //现场平行B样做室内平行
                    if($zk_yang['hy_flag'] == '-26'){
                        $jie_guo = calc($tid, $r, $zk_yang, $jcx, $vid);
                        update_pingxing2ao($jie_guo);
                        //现场平行做了室内平行的时候需要该样的平均值与另一个现场平行的值求相对偏差
                        // 记录原始的vd0的值
                        $vd0_r      = $r['vd0'];
                        $r['vd0']   = $jie_guo['avg']['vd0'];
                        $r['_vd0']  = $jie_guo['avg']['_vd0'];
                    }else if($zk_yang['hy_flag'] == '-46'){
                        //现场平行B样做加标
                        update_jiabiao2ao(jbhs($r,$zk_yang));
                    }
                }
            }
            $jieguo = calc($tid, $sample,$r,$jcx,$vid);
            // 如果中间处理过vd0的值恢复之
            '' != $vd0_s && $jieguo['data_1']['vd0'] = $vd0_s;
            '' != $vd0_r && $jieguo['data_2']['vd0'] = $vd0_r;
            update_pingxing2ao($jieguo);
            break;
        case    -7: //平行样品（不同稀释倍数的样品）
            //找出此样品的平行样品
            $sql = "SELECT * FROM `assay_order` WHERE `tid`={$r['tid']} AND `sid`={$r['sid']} AND `bar_code`='{$r['bar_code']}'";
            $sum=$n=0;
            $query = $DB->query($sql);
            $reliable=$ao_id=array();
            while ($row = $DB->fetch_assoc($query)){
                if($row['id']==$_POST['reliable'][$row['id']]){
                    $n++;
                    $sum+=$row['_vd0'];
                    $vd27sum+=$row['vd27'];//甲第鞭毛虫和隐孢子虫
                }else{
                    $reliable[]=$row['id'];
                }
                $id_arr[] = $row['id'];
                $ao_id = ('-7'!=$row['hy_flag'])?$row['id']:$ao_id;
            }
            if(count($id_arr)==0){
                error_msg('化验单【'.$r['tid'].'】【'.$bar_code.'】号样品数据有误,请与技术人员联络,出错行号:'.__LINE__);
            }
            //round_value 函数在 huayan/assay_form_func.php
            if($n>0){
                 $_avg = $sum/$n;
                //修约
                $avg = round_value($_avg,$r['tid']);
            }else{
                $_avg=$avg='';
            }
            $id_str = implode(',', $id_arr);
            $sql = "UPDATE `assay_order` SET `reliable`='1',`vd0`='$avg',`_vd0`='$_avg',`assay_over`='1' WHERE `id`IN($id_str)";
            $DB->query($sql);
            //舍弃的数据，将可信度调整为2
            if(count($reliable)>0){
                $id_str = implode(',', $reliable);
                $sql = "UPDATE `assay_order` SET `reliable`='2',`vd27`=concat(replace(`vd27`,'(舍)',''),'(舍)') WHERE `id` IN ($id_str)";
            }
            $DB->query($sql);
            if(false==$flag){
                check_zhi_kong( $ao_id,$jcx ,true);
            }
            break;
    }
}
#####################相对误差计算相关函数#####################
function xdwc($tid, $data_1,$data_2,$jcx,$vid){
    // 参数格式
    $biaoyang_data = array(
        // 水样检测结果
        'C_y' => '',
        // 标液浓度
        'C_c' => $data_2['C_c'],
        // 标液浓度单位
        'by_unit'=>$data_2['by_unit'],
        // 标液id
        'by_id'=>$data_2['id'],
        // 标液类型
        'by_type'=>'',
        // 不确定度
        'by_buquedingdu'=>$data_2['by_buquedingdu'],
        // 不确定度单位 mg/L OR %
        'by_buquedingdu_unit'=>$data_2['by_buquedingdu_unit'],
        // 化验单号
        'tid' => $tid,
        // 检出限
        'jcx' => floatval($jcx),
        // 项目id
        'vid' => $vid,
        // 水样类型
        'water_type' => $data_1['water_type'],
        // 相对误差
        'xdwc'=>'',
        // 计算公式
        'formula'=>'',
        // 是否显示真值及不确定度
        'show_avg'=>['title'=>'', 'text'=>''],
        // order表提供的数据
        'data_1' => ['id' => $data_1['id'], 'vd0' => $data_1['vd0'], '_vd0' => $data_1['_vd0']]
    );
    $ZK_biaoyang = new Zk_biaoyangApp();
    $set = $ZK_biaoyang->get_biaoyang_config($data_1['water_type'], $vid);
    $data = biaoyang_calc($set, $biaoyang_data);
    // 根据配置修改化验单上面的显示状态内容
    $data = chuli_data($set, $data);
    return $data;
}
// 计算相对误差
function biaoyang_calc($set, $data){
    // 赋值0和检出限一半
    $data['data_1'][0] = 0;
    $data['data_1']['half_jcx'] = $data['jcx']/2;
    // 计算公式函数
    $data['calc_function'] = ['biaoyang_calc1'];
    // 检测结果多保留X位小数
    $set['vd0_add_blws'] = intval($set['vd0_add_blws']);
    if($set['vd0_add_blws'] > 0){
        $data['data_1']['vd0'] = round_value($data['data_1']['_vd0'], $data['tid'], $set['vd0_add_blws']);
    }
    // 检测值选择
    $data['C_y'] = $data['data_1'][$set['use_data']];
    // 检查结果值的合法性
    if('' === $data['C_y']){
        return $data;
    }
    // 小于检出限判断
    if(stristr($data['data_1']['vd0'], '<')){
        $data['C_y'] = $data['data_1'][$set['xy_jcx']];
    }
    // 如果结果小于0则使用0计算
    ($data['C_y'] < 0) && ($data['C_y'] = 0);
    // 公式计算 P% = (C₂-C₀)/C₀×100%
    $data = $data['calc_function'][$set['formula']]($set, $data);
    $data['formula'] = array(
        'formula' => $data['formula'],
        'ping_jia' => $data['ping_jia'],
        'nd' => $data['C_c'].$data['by_unit'],
        'fw' => '±'.$data['by_buquedingdu'].$data['by_buquedingdu_unit']
    );
    return $data;
}
// 公式计算 P% = (C₂-C₀)/C₀×100%
function biaoyang_calc1($set, $data){
    // 标液单位判断及转换
    $xishu = '';
    switch(strtoupper($data['by_unit'])){
        case 'MG/L':
        case 'UG/ML':
        case 'μG/ML':
            $xishu = '';
            break;
        case 'UG/L':
        case 'μG/L':
            $xishu = '/1000';
            break;
    }
    // 原样计算
    $formula = "\$data['xdwc']=({$data['C_y']} - {$data['C_c']}{$xishu})/{$data['C_c']}{$xishu}*100;;";
    @eval($formula);
    // 避免小数位数过长
    if(explode('.', $data['xdwc'])[1] > 6){
        $data['xdwc'] = round($data['xdwc'], 6);
    }
    // 替换特殊字符
    $data['formula'] = str_replace([
        "\$data['xdwc']", '*', '/', ';;'
    ] , [$data['xdwc'].'%', '×', '÷', '%'], $formula);
    $data['xdwc'] = round_zhikong($data['xdwc'], $set['round_function'], $set['xdwc_blws']);
    $data['ping_jia'] = biaoyang_pingjia($set, $data);
    return $data;
}
// 标样评价
function biaoyang_pingjia($set, &$data){
    switch($set['hege_panding']){
        case '0':
            if('%'==$data['by_buquedingdu_unit']){
                $ping_jia = zk_by_check_xdwc($set, $data);
            }else{
                $ping_jia = zk_by_check_jdwc($set, $data);
            }
            break;
        case '1':
            $ping_jia = zk_by_check_jdwc($set, $data);
            break;
        case '2':
            $ping_jia = zk_by_check_xdwc($set, $data);
            break;
    }
    return $ping_jia;
}
// 绝对误差判断
function zk_by_check_jdwc($set, &$data){
    // 
    switch(strtoupper($data['by_buquedingdu_unit'])){
        case 'MG/L':
        case 'UG/ML':
        case 'μG/ML':
        case 'µG/L':
        case 'μS/CM':
        case '无量纲':
            $jdwc = floatval($data['C_y']-$data['C_c']);
            $ping_jia = abs($jdwc) <= abs($data['by_buquedingdu']) ? '合格':'不合格';
            break;
        case '%':
            $jdwc = floatval($data['C_y']-$data['C_c']);
            $by_buquedingdu = $data['C_c']*$data['by_buquedingdu'];
            $ping_jia = abs($jdwc) <= abs($by_buquedingdu) ? '合格':'不合格';
            break;
        default:
            $ping_jia = '';
    }
    $data['jdwc'] = $jdwc;
    return $ping_jia;
}
// 相对误差判断
function zk_by_check_xdwc($set, $data){
    // 
    switch(strtoupper($data['by_buquedingdu_unit'])){
        case 'MG/L':
        case 'UG/ML':
        case 'μG/ML':
        case 'μG/L':
        case 'μS/CM':
        case '无量纲':
            $by_buquedingdu = $data['C_c']*$data['by_buquedingdu'];
            $ping_jia = abs($data['xdwc']) <= abs($by_buquedingdu) ? '合格':'不合格';
            break;
        case '%':
            $ping_jia = abs($data['xdwc']) <= abs($data['by_buquedingdu']) ? '合格':'不合格';
            break;
        default:
            $ping_jia = '';
    }
    return $ping_jia;
}
#####################加标回收计算相关函数#####################
// 计算加标 原水样数据 加标样数据
function jbhs($data_1, $data_2){
    global $arow;
    $zk_data = json_decode($data_2['zk_data'], true);
    // 单位转换
    $hyd_unit = strtolower($arow['unit']);
    $jb_unit = strtolower($zk_data['c_c_unit']);
    if($hyd_unit != $jb_unit){
        //若化验单中的单位 与 标液浓度单位不同，则进行换算
        if($hyd_unit=='µg/l' && $jb_unit=='mg/l'){
            $zk_data['c_c'] *= 1000;
        }elseif($hyd_unit=='mg/l' && $jb_unit=='µg/l'){
            $zk_data['c_c'] /= 1000;
        }
    }
    $jiabiao_data = array(
        // 原水样浓度
        'C_y' => '',
        // 加标样浓度
        'C_j' => '',
        // 理论浓度相关 原值,修约值,计算公式
        'C_i'=>['_vd0'=>'', 'vd0'=>'', 'formula'=>''],
        // 化验单id
        'tid' => $data_2['tid'],
        // 检出限
        'jcx' => floatval($data_2['jcx']),
        // 项目id
        'vid' => $data_2['vid'],
        // 水样类型
        'water_type' => $data_2['water_type'],
        // 加标回收率
        'jbhsl'=>'',
        // 计算公式
        'formula'=>'',
        // order表提供的数据
        'data_1' => $data_1,
        // 'data_1' => ['id' => $data_1['id'], 'vd0' => $data_1['vd0'], '_vd0' => $data_1['_vd0'], 'xdpc' => '',  'ping_jia'=> '', 'formula' => ''],
        'data_2' => $data_2,
        // 'data_2' => ['id' => $data_2['id'], 'vd0' => $data_2['vd0'], '_vd0' => $data_2['_vd0'], 'xdpc' => '',  'ping_jia'=> '', 'formula' => ''],
        // 原水样体积
        'V_y'=>$zk_data['v_y'],
        // 加标样总体积
        'V_j'=>'',
        // 加标液浓度
        'C_c'=>$zk_data['c_c'],
        // 加标液体积
        'V_c'=>$zk_data['v_c'],
        // 其他体积
        'V_o'=>$zk_data['v_o'],
        // 原水样稀释倍数
        'X_y'=>$zk_data['x_y'],
        // 加标样稀释倍数
        'X_j'=>$zk_data['x_j'],
        // 体积系数
        'X_v'=>$zk_data['x_v'],
        // 质量系数
        'X_m'=>'1'
    );
    $ZK_jiabiao = new Zk_jiabiaoApp();
    $set = $ZK_jiabiao->get_jiabiao_config($data_1['water_type'], $data_1['vid']);
    $data = jiabiao_calc($set, $jiabiao_data);
    // 根据配置修改化验单上面的显示状态内容
    $data = chuli_data($set, $data);
    return $data;
}
// 计算加标回收率
function jiabiao_calc($set, $data){
    global $DB;
    // 检出限相关内容值
    $data['data_1']['0'] = 
    $data['data_2']['0'] = 0;
    $data['data_1']['1'] = 
    $data['data_2']['1'] = $data['jcx']/2;
    $data['data_1']['jcx'] = 
    $data['data_2']['jcx'] = $data['jcx'];
    // 质量系数
    $data['X_m'] = $set['xishu_m'];
    // 计算公式函数
    $data['jbhsl_function'] = ['jiabiao_calc1', 'jiabiao_calc2'];
    // 如果原样做了室内平行，则需要判断是使用平均值还是检测值进行计算
    if(in_array($data['data_1']['hy_flag'], array('-20', '60')) && 'avg' == $set['use_pingxing']){
        $get_hy_flag = ($data['data_1']['hy_flag']=='60') ? '-20' : '60';
        $sql = "SELECT * from `assay_order` where `tid`={$data['tid']} AND `sid`={$data['data_1']['sid']} AND `hy_flag` ='{$get_hy_flag}' LIMIT 1";
        $yy = $DB->fetch_one_assoc($sql);
        $px_jieguo = calc($data['tid'], $yy, $data['data_1'], $data['jcx'], $data['vid']);
        $data['data_1']['vd0'] = $px_jieguo['avg']['vd0'];
        $data['data_1']['_vd0'] = $px_jieguo['avg']['_vd0'];
    }
    // 检测结果多保留X位小数
    $set['vd0_add_blws'] = intval($set['vd0_add_blws']);
    if($set['vd0_add_blws'] > 0){
        $data['data_1']['vd0'] = round_value($data['data_1']['_vd0'], $data['tid'], $set['vd0_add_blws']);
        $data['data_2']['vd0'] = round_value($data['data_2']['_vd0'], $data['tid'], $set['vd0_add_blws']);
    }
    // 检测值选择
    $data['C_y'] = $data['data_1'][$set['use_data']];
    $data['C_j'] = $data['data_2'][$set['use_data']];
    // 检查结果值的合法性
    if('' === $data['C_y'] || '' === $data['C_j']){
        return $data;
    }
    // 小于检出限判断
    if(stristr($data['data_1']['vd0'], '<')){
        // 原样
        $data['C_y'] = $data['data_1'][$set['xy_jcx']];
    }
    if(stristr($data['data_2']['vd0'], '<')){
        // 加标样
        $data['C_j'] = $data['data_2'][$set['xy_jcx']];
    }
    // 如果结果小于0则使用0计算
    ($data['C_y'] < 0) && ($data['C_y'] = 0);
    ($data['C_j'] < 0) && ($data['C_j'] = 0);
    // 原水样体积=原水样体积+其他体积
    $V_y = jiabiao_get_v_y($set, $data);
    // $data['V_y'] = $V_y['data'];
    $data['V_y_formula'] = $V_y['formula'];
    // 根据是否考虑体积，获取加标样水样体积
    // 加标体积=原水样体积+加入标液的体积+其他体积
    $V_j = jiabiao_get_v_j($set, $data);
    $data['V_j'] = $V_j['data'];
    // $V_j['formula'] = ['原数值','乘法原始','除法运算']
    $data['V_j_formula'] = $V_j['formula'];
    // 理论浓度计算
    $data['C_i']['_vd0'] = ($data['C_c']*$data['V_c'])/$data['V_j']*$data['X_j'];
    // 避免小数位数过长
    if(explode('.', $data['C_i']['_vd0'])[1] > 6){
        $data['C_i']['_vd0'] = round($data['C_i']['_vd0'], 6);
    }
    // 理论浓度修约
    $data['C_i']['vd0'] = round_value($data['C_i']['_vd0'], $data['tid'], $set['li_add_blws']);
    // 理论浓度的计算公式中加标样体积的公式是全部的分母，所以需要去除外围的括号
    $C_i_V_j_formula = str_replace(['(', ')'], '', $data['V_j_formula']);
    // 重新组合计算公式
    $data['C_i']['formula'] = "<table><tr><td>{$data['C_i']['_vd0']} = </td><td>{$data['C_c']}*{$data['V_c']}<hr>{$C_i_V_j_formula}</td>";
    // 如果加标液的稀释倍数不为1则需要除以稀释倍数
    if(1 == $data['X_j']){
        $data['C_i']['formula'] .= "</table>";
    }else{
        $data['C_i']['formula'] .= "<td>*{$data['X_j']}</td></tr></table>";
    }
    // 替换特殊字符
    $data['C_i']['formula'] = str_replace(['*', '/', '<÷'] , ['×', '÷', '</'], $data['C_i']['formula']);
    // 加标回收率计算
    $data = call_user_func($data['jbhsl_function'][$set['formula']], $set, $data);
    // 合格判定
    $ping_jia = zhikong_pingjia($data['vid'], $data['water_type'], $data['C_y'], $data['jbhsl'], $set['zhikong_type']);
    $data['ping_jia'] = $ping_jia['ping_jia'];
    // 重新赋值计算公式
    $data['formula'] = array(
        'nd' => $ping_jia['nd'],
        'fw' => $ping_jia['fw'],
        'formula' => $data['formula'],
        'ping_jia' => $ping_jia['ping_jia']
    );
    return $data;
}
// 公式1 P% = [m₂-m₁]/m₀×100%
function jiabiao_calc1($set, $data){
    // 获取体积系数
    $X_v = get_jiabiao_x_v($set, $data);
    // 获取质量转换系数
    $X_m = get_jiabiao_x_m($set, $data);
    // 获取原水样稀释倍数
    $X_y = get_jiabiao_xsbs($data['X_y']);
    // 获取加标样稀释倍数
    $X_j = get_jiabiao_xsbs($data['X_j']);
    if(!is_numeric($data['C_j']) ||
        !is_numeric($data['C_y']) ||
        !is_numeric($data['V_y']) ||
        !is_numeric($data['V_c']) ||
        !is_numeric($data['C_c']) ){
        return [];
    }
    // (C_j*V_j/X_j-C_y*V_y/X_y*X_v)/(V_c*C_c*X_m)×100%
    $formula = "\$data['jbhsl']=({$data['C_j']}*{$data['V_j_formula']}{$X_j['formula']}- {$data['C_y']}*{$data['V_y_formula']}{$X_y['formula']}{$X_v['formula']}) / ({$data['V_c']}*{$data['C_c']}{$X_m['formula']})*100;";
    @eval($formula);
    if(explode('.', $data['jbhsl'])[1] > 6){
        $data['jbhsl'] = round($data['jbhsl'], 6);
    }
    // 因为加标回收的计算公式比较长重新组合计算公式
    $data['formula'] = "<table><tr><td>{$data['jbhsl']}%=</td><td>{$data['C_j']}×{$data['V_j_formula']}{$X_j['formula']} - {$data['C_y']}*{$data['V_y_formula']}{$X_y['formula']}{$X_v['formula']}<hr>{$data['V_c']}×{$data['C_c']}{$X_m['formula']}</td><td>×100%</td></tr></table>";
    // 替换特殊字符
    $data['formula'] = str_replace(['*', '/', '<÷'] , ['×', '÷', '</'], $data['formula']);
    $data['jbhsl'] = round_zhikong($data['jbhsl'], $set['round_function'], $set['jbhsl_blws']);
    return $data;
}
// 公式2 Cᵢ = (C₀*V₀)/V₂；P% = (C₂-C₁)/Cᵢ×100%
function jiabiao_calc2($set, $data){
    // 获取体积系数
    $X_v = get_jiabiao_x_v($set, $data);
    // 获取质量转换系数
    $X_m = get_jiabiao_x_m($set, $data);
    // 获取原水样稀释倍数
    $X_y = get_jiabiao_xsbs($data['X_y']);
    // 获取加标样稀释倍数
    $X_j = get_jiabiao_xsbs($data['X_j']);
    // (C_j/X_j-C_y/X_y*X_v)/(C_i*X_m)×100%
    $formula = "\$data['jbhsl']=({$data['C_j']} - {$data['C_y']})/({$data['C_i'][$set['use_li']]})*100;";
    @eval($formula);
    if(explode('.', $data['jbhsl'])[1] > 6){
        $data['jbhsl'] = round($data['jbhsl'], 6);
    }
    // 因为加标回收的计算公式比较长重新组合计算公式
    $data['formula'] = "<table><tr><td>{$data['jbhsl']}%=</td><td>{$data['C_j']} - {$data['C_y']}<hr>{$data['C_i'][$set['use_li']]}</td><td>×100%</td></tr></table>";
    // 替换特殊字符
    $data['formula'] = str_replace(['*', '/', '<÷'] , ['×', '÷', '</'], $data['formula']);
    $data['jbhsl'] = round_zhikong($data['jbhsl'], $set['round_function'], $set['jbhsl_blws']);
    return $data;
}
// 获取原水样体积
function jiabiao_get_v_y($set, $data){
    $formula = $data['V_y'];
    // 其他体积大于0并且原水样考虑其他体积
    if($data['V_o']>0 && '1' == $set['yv_allow_ov']){
        // 原水样体积+=其他体积
        $formula = "({$data['V_y']}+V_o)";
        $data['V_y'] += $data['V_o'];
        // 替换计算公式中的V_o为相应的体积值
        $formula = str_replace('V_o', $data['V_o'], $formula);
    }
    // 返回加标样体积
    return [
        'data' => $data['V_y'],
        'formula' => $formula
    ];
}
// 获取加标样体积
function jiabiao_get_v_j($set, $data){
    // 计算加标体积与水样体积比率
    $jbl = $data['V_c']/$data['V_y'];
    // 加标体积大于x%时考虑加标体积
    if($jbl>$set['allow_cv']){
        $data['V_j'] = $data['V_y']+$data['V_c'];
        $formula = "{$data['V_y']}+{$data['V_c']}+V_o";
    }else{
        // 加标样体积=水样体积
        $data['V_j'] = $data['V_y'];
        $formula = "{$data['V_y']}+V_o";
    }
    // 是否有其他体积#24379
    if($data['V_o']>0){
        $data['V_j'] += $data['V_o'];
        // 替换计算公式中的V_o为相应的体积值
        $formula = str_replace('V_o', $data['V_o'], $formula);
    }else{
        // 无其他体积时将公式中的+V_o剔除
        $formula = str_replace('+V_o', '', $formula);
    }
    // 如果含有加法运算则用括号括起来
    if(stristr($formula, '+')){
        $formula = '('.$formula.')';
    }
    // 返回加标样体积
    return [
        'data' => $data['V_j'],
        'formula' => $formula
    ];
}
// 获取体积系数
function get_jiabiao_x_v($set, $data){
    // 是否需要体积系数,默认不需要
    $X_v = ['data' => 1, 'formula' => ''];
    if($data['V_y']!=$data['V_j'] && $set['need_x_v']){
        $X_v = [
            'data' => $data['V_y']/$data['V_j'],
            'formula' => "*({$data['V_y']}/{$data['V_j_formula']})"
        ];
    }
    return $X_v;
}
// 获取质量转换系数
function get_jiabiao_x_m($set, $data){
    // 质量转换系数,系数为1时不需要处理
    $data['X_m'] = floatval($data['X_m']);
    $X_m = ['data' => 1, 'formula' => ''];
    if(1!=$data['X_m'] && 0!=$data['X_m']){
        $X_m = [
            'data' => $data['X_m'],
            'formula' => "*{$data['X_m']}"
        ];
    }
    return $X_m;
}
// 获取稀释倍数
function get_jiabiao_xsbs($xsbs){
    // 质量转换系数,系数为1时不需要处理
    $xsbs = floatval($xsbs);
    $xsbs_arr = ['data' => 1, 'formula' => ''];
    if(1!=$xsbs && 0!=$xsbs){
        $xsbs_arr = [
            'data' => $xsbs,
            'formula' => "/{$xsbs}"
        ];
    }
    return $xsbs_arr;
}

#####################相对偏差计算相关函数#####################
function calc($tid, $data_1,$data_2,$jcx,$vid){
    $pingxing_data = array(
        // 参与计算的原水样浓度
        'vd0_1' => '',
        // 参与计算的平行样浓度
        'vd0_2' => '',
        // 平均值
        'avg' => ['_vd0'=>'', 'vd0'=>'', 'formula'=>''],
        // 化验单id
        'tid' => $tid,
        // 检出限
        'jcx' => floatval($jcx),
        // 项目id
        'vid' => $vid,
        // 水样类型
        'water_type' => $data_1['water_type'],
        // order表提供的数据
        'data_1' => ['id' => $data_1['id'], 'vd0' => $data_1['vd0'], '_vd0' => $data_1['_vd0'], 'xdpc' => '',  'ping_jia'=> '', 'formula' => ''],
        'data_2' => ['id' => $data_2['id'], 'vd0' => $data_2['vd0'], '_vd0' => $data_2['_vd0'], 'xdpc' => '',  'ping_jia'=> '', 'formula' => ''],
    );
    $ZK_PingXing = new Zk_pingxingApp();
    $set = $ZK_PingXing->get_pingxing_config($data_1['water_type'], $vid);
    // 获取use_data
    $pingxing_data['data_1'][$set['use_data']] = $data_1[$set['use_data']];
    $pingxing_data['data_2'][$set['use_data']] = $data_2[$set['use_data']];
    // 平行计算相对偏差
    $data = pingxing_calc($set, $pingxing_data);
    // 根据配置修改化验单上面的显示状态内容
    $data = chuli_data($set, $data);
    return $data;
}
// 计算相对偏差
function pingxing_calc($set, $data){
    // 检出限相关内容值
    $data['avg']['0'] = 
    $data['data_1']['0'] = 
    $data['data_2']['0'] = 0;
    $data['avg']['1'] = 
    $data['data_1']['1'] = 
    $data['data_2']['1'] = $data['jcx']/2;
    $data['avg']['jcx'] = 
    $data['data_1']['jcx'] = 
    $data['data_2']['jcx'] = $data['jcx'];
    // 计算公式函数
    $data['calc_function'] = ['pingxing_calc1', 'pingxing_calc2'];
    // 检测结果多保留X位小数
    $set['vd0_add_blws'] = intval($set['vd0_add_blws']);
    if($set['vd0_add_blws'] > 0){
        $data['data_1']['vd0'] = round_value($data['data_1']['_vd0'], $data['tid'], $set['vd0_add_blws']);
        $data['data_2']['vd0'] = round_value($data['data_2']['_vd0'], $data['tid'], $set['vd0_add_blws']);
    }
    // 检测值选择
    $data['vd0_1'] = $data['data_1'][$set['use_data']];
    $data['vd0_2'] = $data['data_2'][$set['use_data']];
    // 检查结果值的合法性
    if('' === $data['vd0_1'] || '' === $data['vd0_2']){
        return $data;
    }
    if(in_array($set['use_data'], ['vd0', '_vd0'])){
        // 小于检出限判断
        if(stristr($data['data_1']['vd0'], '<')){
            $data['vd0_1'] = $data['data_1'][$set['xy_jcx']];
        }
        if(stristr($data['data_2']['vd0'], '<')){
            $data['vd0_2'] = $data['data_2'][$set['xy_jcx']];
        }
    }
    // 如果结果小于0则使用0计算
    ($data['vd0_1'] < 0) && ($data['vd0_1'] = 0);
    ($data['vd0_2'] < 0) && ($data['vd0_2'] = 0);
    // 平均值计算
    $avg_formula = "\$data['avg']['_vd0']=({$data['vd0_1']}+{$data['vd0_2']})/2;";
    // die($avg_formula);
    @eval($avg_formula);
    // 替换特殊字符
    $data['avg']['formula'] = str_replace(["\$data['avg']['_vd0']", '/', ';'], [$data['avg']['_vd0'], '÷', ''], $avg_formula);
    // 平均值修约
    $data['avg']['vd0'] = round_value($data['avg']['_vd0'], $data['tid'], $set['avg_add_blws']);
    if(stristr($data['avg']['vd0'], '<')){
        $set['use_avg'] = $set['xy_jcx'];
    }
    // 公式计算 P% = (X-Avg)/Avg×100%
    $data = $data['calc_function'][$set['formula']]($set, $data);
    // 合格判定
    $ping_jia = zhikong_pingjia($data['vid'], $data['water_type'], $data['avg'][$set['use_avg']], $data['data_1']['xdpc'], $set['zhikong_type']);
    $data['data_1']['ping_jia'] = $ping_jia['ping_jia'];
    // 原样和平行样的计算公式中的数值可能是不一样的,所以需要分别计算分别存储
    $data['data_1']['formula'] = array(
        'nd' => $ping_jia['nd'],
        'fw' => $ping_jia['fw'],
        'ping_jia' => $ping_jia['ping_jia'],
        'formula' => $data['data_1']['formula']
    );
    // 合格判定
    $ping_jia = zhikong_pingjia($data['vid'], $data['water_type'], $data['avg'][$set['use_avg']], $data['data_2']['xdpc'], $set['zhikong_type']);
    $data['data_2']['ping_jia'] = $ping_jia['ping_jia'];
    // 重新赋值计算公式
    $data['data_2']['formula'] = array(
        'nd' => $ping_jia['nd'],
        'fw' => $ping_jia['fw'],
        'ping_jia' => $ping_jia['ping_jia'],
        'formula' => $data['data_2']['formula']
    );
    return $data;
}
// 公式1
// P%=(X-Avg)/Avg*100%
function pingxing_calc1($set, $data){
    // 小于检出限判断
    if(stristr($data['avg']['vd0'], '<')){
        $avg = $data['avg'][$set['xy_jcx']];
    }else{
        $avg = $data['avg'][$set['use_avg']];
    }
    // 原样和平行样的计算公式中的数值可能是不一样的,所以需要分别计算分别存储
    // 原样计算
    $formula = "\$data['data_1']['xdpc']=({$data['vd0_1']} - {$avg})/{$avg}*100;";
    if($avg == 0){
        $data['data_1']['xdpc'] = 0;
    }else{
        @eval($formula);
        // 避免小数位数过长
        if(explode('.', $data['data_1']['xdpc'])[1] > 6){
            $data['data_1']['xdpc'] = round($data['data_1']['xdpc'], 6);
        }
    }
    // 替换特殊字符
    $data['data_1']['formula'] = str_replace(
        ["\$data['data_1']['xdpc']", '*', '/', '<÷'] , 
        [$data['data_1']['xdpc'].'%', '×', '÷', '</'], $formula);
    $data['data_1']['xdpc'] = round_zhikong($data['data_1']['xdpc'], $set['round_function'], $set['xdpc_blws']);
    // 平行样计算
    $formula = "\$data['data_2']['xdpc']=({$data['vd0_2']} - {$avg})/{$avg}*100;";
    if($avg == 0){
        $data['data_2']['xdpc'] = 0;
    }else{
        @eval($formula);
        // 避免小数位数过长
        if(explode('.', $data['data_2']['xdpc'])[1] > 6){
            $data['data_2']['xdpc'] = round($data['data_2']['xdpc'], 6);
        }
    }
    // 替换特殊字符
    $data['data_2']['formula'] = str_replace(
        ["\$data['data_2']['xdpc']", '*', '/', '<÷'] , 
        [$data['data_2']['xdpc'].'%', '×', '÷', '</'], $formula);
    $data['data_2']['xdpc'] = round_zhikong($data['data_2']['xdpc'], $set['round_function'], $set['xdpc_blws']);
    return $data;
}
// 公式2
// P%=(a-b)/Avg*100%
function pingxing_calc2($set, $data){
    // 小于检出限判断
    if(stristr($data['avg']['vd0'], '<')){
        $avg = $data['avg'][$set['xy_jcx']];
    }else{
        $avg = $data['avg'][$set['use_avg']];
    }
    // 原样和平行样的计算公式中的数值可能是不一样的,所以需要分别计算分别存储
    // 原样计算
    $formula = "\$data['data_1']['xdpc']=({$data['vd0_1']}- {$data['vd0_2']})/{$avg}*100;";
    @eval($formula);
    $data['data_1']['formula'] = str_replace(
        ["\$data['data_1']['xdpc']", '*', '/', '<÷'] , 
        [$data['data_1']['xdpc'].'%', '×', '÷', '</'], $formula);
    $data['data_1']['xdpc'] = round_zhikong($data['data_1']['xdpc'], $set['round_function'], $set['xdpc_blws']);
    // 平行样计算
    $formula = "\$data['data_2']['xdpc']=({$data['vd0_2']}- {$data['vd0_1']})/{$avg}*100;";
    @eval($formula);
    $data['data_2']['formula'] = str_replace(
        ["\$data['data_2']['xdpc']", '*', '/', '<÷'] , 
        [$data['data_2']['xdpc'].'%', '×', '÷', '</'], $formula);
    $data['data_2']['xdpc'] = round_zhikong($data['data_2']['xdpc'], $set['round_function'], $set['xdpc_blws']);
    return $data;
}
    
/**
 * 功能：获取质控范围
 * 参数：int   $vid        项目id
 * 参数：int   $water_type 水样类型id
 * 参数：float $nd         浓度
 * 参数：float $jieguo     需要判断是否在质控范围的数值。
 * 参数：string    $leixing    jieguo数值的数据类型，如：加标回收率（j），室内精密度(snjmd)，室间精密度（sjjmd），室内相对误差（snxdwc）,室间相对误差（sjxdwc）。
 * 返回值：传入项目id和水体类型未必传参数，
 * 功能描述：jieguo参数和leixing参数必须同时存在。传入项目id和水体类型为必传参数，如果传入浓度返回质控范围数组，传入jieguo和leixing，返回是否合格信息
*/
function zhikong_pingjia($vid,$water_type,$nd,$jieguo='',$leixing=''){
    global $DB,$global,$u,$fzx_id,$rootdir;
    //vid nd 未传参时返回空
    if(!intval($vid)||''===$nd){ return ''; }
    //判断质控类型是否匹配
    $lx_arr = array('jbhs','sn_jmd','sj_jmd','sn_xdwc','sj_xdwc');
    if(!in_array($leixing,$lx_arr)){ return ''; }
    // 质控只配置顶级水样类型的合格范围，所以需要找到顶级水样类型
    if(!function_exists('get_water_type_max')){
        include_once "{$rootdir}/inc/cy_func.php";
    }
    $water_type=get_water_type_max($water_type, $fzx_id);
    // 根据水样类型倒序排，找到本水样类型或者默认水样类型的配置信息
    $sql = "SELECT * FROM `zk_set` WHERE `vid` in('0', '{$vid}') AND `water_type` IN ('0','{$water_type}') AND `{$leixing}`!='' AND `{$leixing}` IS NOT NULL ORDER BY `vid` DESC,`water_type` DESC";
    $result = $DB->query($sql);
    if($DB->num_rows($result)==0){
        $Zk_fanweiApp = new Zk_fanweiApp();
        $Zk_fanweiApp->fanwei_init();
        $result = $DB->query($sql);
    }
    $row = array();
    while ( $r = $DB->fetch_assoc($result)) {
        foreach ($r as $key => $value) {
            $r[$key] = trim($r[$key]);
        }
        $row[] = $r;
    }
    //根据浓度判断适用哪个质控范围
    foreach($row as $k){
        $f = $p = 0;
        $k['nd'] = trim($k['nd']);
        //取出浓度范围
        if(strstr($k['nd'],'>')){
            $a = substr($k['nd'],1);
            if(floatval($nd) > floatval($a)){ $f = 1; }
        }else if(strstr($k['nd'],'≥')){
            $a = substr($k['nd'],3);
            if(floatval($nd) >= floatval($a)){ $f = 1; }
        }else if(strstr($k['nd'],'<')){
            $a = substr($k['nd'],1);
            if(floatval($nd) < floatval($a)){ $f = 1; }
        }else if(strstr($k['nd'],'≤')){
            $a = substr($k['nd'],3);
            if(floatval($nd) <= floatval($a)){ $f = 1; }
        }else{
            $arr = array();
            $arr = preg_split('/[^\d.]/',$k['nd']);
            $arr_count = count($arr)-1;
            if(floatval($nd) >= floatval($arr['0']) && floatval($nd) < floatval($arr[$arr_count])){
                $f = 1;
            }
        }
        $error = '';
        if($f == 1){
            //如果传入的参数没有jieguo和leixing，输出该浓度下的所有质控范围
            if($jieguo === ''){
                return $k[$leixing];
            }else if($k[$leixing]=='' || $k[$leixing] == '-'){
                break;//质控范围未设置
            }else{
                $jieguo = floatval(abs($jieguo));
                // 提取出数字
                preg_match_all('/[-\d.]+/',$k[$leixing],$match);
                if(count($match[0]) == 2){
                    if(is_numeric($match[0][0]) && is_numeric($match[0][1])){
                        $match[0][1] = abs($match[0][1]);
                        $jieguo >= floatval($match[0][0]) && $jieguo <= floatval($match[0][1]) && $p = 1;
                    }
                }else{
                    if(is_numeric($match[0][0])){
                        // 运算符号
                        $calc_sign = str_replace(['≥', '≤'], ['>=', '<='], $k[$leixing]);
                        $calc_sign = str_replace([' ', $match[0][0]], [''], $k[$leixing]);
                        if(!in_array($calc_sign, ['>', '<', '>=', '<='])){
                            $calc_sign = '<=';
                        }
                        eval("{$jieguo} >= 0 && $jieguo {$calc_sign} floatval({$match[0][0]}) && (\$p = 1);");
                    }
                }
                $ping_jia = ($p == 1)? '合格' : '不合格';
                break;
            }
        }
    }
    return array(
        'nd' => $k['nd'],
        'fw' => $k[$leixing],
        'ping_jia' => $ping_jia
    );
}

// 加标回收率,相对偏差质控修约
function round_zhikong($result, $round_function, $blws){
    // 非数值直接返回
    if(!is_numeric($result) ){
        return '';
    }
    // 取绝对值进行取值范围判断
    $abs_result = abs($result);
    // 判断修约函数是否正确
    if(!in_array($round_function, array('round', '_round', 'floor_round', 'ceil_round'))){
        $round_function = '_round';
    }
    // 根据取值范围进行修约<1，<10, <100, ≥100
    if($abs_result < 1){
        return $round_function($result, $blws[0]);
    }else if($abs_result < 10){
        return $round_function($result, $blws[1]);
    }else if($abs_result < 100){
        return $round_function($result, $blws[2]);
    }
    // if($abs_result >= 100)
    // 该范围不再判定以保证有返回值
    return $round_function($result, $blws[3]);
}
// 根据配置修改化验单上面的显示状态内容
function chuli_data($set, $data){
    // 
    switch($set['zhikong_type']){
        case 'biaoyang':
            // 是否显示真值及不确定度
            if($set['show_avg'] == 'C_c'){
                // 显示标液真值
                $data['show_avg'] = [
                    'title' => '标液真值',
                    'text' => $data['C_c']
                ];
            }else if($set['show_avg'] == 'by_buquedingdu'){
                // 显示不确定度
                $data['show_avg'] = [
                    'title' => '不确定度',
                    'text' => '±'.$data['by_buquedingdu'].$data['by_buquedingdu_unit']
                ];
            }else if($set['show_avg'] == 'all'){
                // 显示真值±不确定度
                $data['show_avg'] = [
                    'title' => '不确定度',
                    'text' => $data['C_c'].'±'.$data['by_buquedingdu'].$data['by_buquedingdu_unit']
                ];
            }else if($set['show_avg'] == 'no'){
                // 不显示
                $data['show_avg'] = [
                    'title' => '',
                    'text' => ''
                ];
            }
            // 不计算质控结果
            // if(!$set['show_zkjg']){
            //     $data['xdwc'] = '';
            // }
            break;
        case 'sn_jmd':
            // 偏差为0时如何处理,0表示处理为0,1表示正常修约,下面的处理就不需要执行
            if(!$set['xdpc_is_zero']){
                '' !== $data['data_1']['xdpc'] && 0 == $data['data_1']['xdpc'] && $data['data_1']['xdpc'] = 0;
                '' !== $data['data_2']['xdpc'] && 0 == $data['data_2']['xdpc'] && $data['data_2']['xdpc'] = 0;
            }
            // 偏差处理 使用str_replace不用abs是因为abs会抹掉末尾小数位数保留的0
            if('2' == $set['xdpc_chuli']){
                // 取绝对值显示
                '' !== $data['data_1']['xdpc'] && $data['data_1']['xdpc'] = str_replace('-', '', $data['data_1']['xdpc']);
                '' !== $data['data_2']['xdpc'] && $data['data_2']['xdpc'] = str_replace('-', '', $data['data_2']['xdpc']);
            }else if('1' == $set['xdpc_chuli']){
                // 显示正负号
                '' !== $data['data_1']['xdpc'] && $data['data_1']['xdpc'] = '±'.str_replace('-', '', $data['data_1']['xdpc']);
                '' !== $data['data_2']['xdpc'] && $data['data_2']['xdpc'] = '±'.str_replace('-', '', $data['data_2']['xdpc']);
            }else{
                // 不处理
                // '0' == $set['xdpc_chuli'] 不需要处理
            }
            // 如果两个水样检测结果都小于检出限，平均值也小于检出限
            if(in_array($set['use_data'], ['vd0', '_vd0'])){
                if(stristr($data['data_1']['vd0'], '<') && stristr($data['data_2']['vd0'], '<')){
                    $data['avg']['vd0'] = $data['data_1']['vd0'];
                    $data['avg']['formula'] = "{$data['avg']['vd0']}=({$data['avg']['vd0']} + {$data['avg']['vd0']})/2";
                }
            }
            // $set['xdpc_show']
            // 两个水样的偏差都存储，如何显示在查看化验单处控制
            // 见assay_form_func.php  get_zhikong函数
            break;
        case 'jbhs':
            // $set['show_li']
            // 是否显示理论浓度，如何显示在查看化验单处控制
            // 见assay_form_func.php  get_zhikong函数
            break;
    }
    return $data;
}
/**
 * 功能：更新平均值,相对偏差及评价函数
 * 作者：Mr Zhou
 * 日期：2014-05-19
 * 参数：array $data   计算结果
 * 返回值：修约后的结果
 * 功能描述：更新平均值,相对偏差及评价函数
*/
function update_pingxing2ao($data=array()){
    global $DB;
    if(intval($data['data_1']['id'])){
        $formula_json = set_formula_json_data($data['data_1']['id'], 'zhikong', $data['data_1']['formula']);
        // 原水样的偏差无值时可能是不需要显示均值及相对偏差
        if('' === $data['data_1']['xdpc']){
            $data_1_avg = '';
        }else{
            $formula_json = set_formula_json_data($formula_json, 'show_avg', 
                ['title' => '平均值计算公式', 'text' => $data['avg']['formula']]
            );
        }
        $sql = "UPDATE `assay_order` SET 
                    `js_gongshi`='{$formula_json}',
                    `vd0`='{$data['data_1']['vd0']}',
                    `ping_jia`='{$data['data_1']['ping_jia']}',
                    `ping_jun`='{$data_1_avg}',
                    `xiang_dui_pian_cha`='{$data['data_1']['xdpc']}' WHERE `id`='{$data['data_1']['id']}' LIMIT 1";
        $DB->query($sql);
    }
    if(intval($data['data_2']['id'])){
        $formula_json = set_formula_json_data($data['data_2']['id'], 'zhikong', $data['data_2']['formula']);
        $formula_json = set_formula_json_data($formula_json, 'show_avg', 
            ['title' => '平均值计算公式', 'text' => $data['avg']['formula']]
        );
        $sql = "UPDATE `assay_order` SET 
                    `js_gongshi`='{$formula_json}',
                    `vd0`='{$data['data_2']['vd0']}',
                    `ping_jia`='{$data['data_1']['ping_jia']}',
                    `ping_jun`='{$data['avg']['vd0']}',
                    `xiang_dui_pian_cha`='{$data['data_2']['xdpc']}' WHERE `id`='{$data['data_2']['id']}' LIMIT 1";
        $DB->query($sql);
    }
}
function update_jiabiao2ao($data){
    global $DB;
    $id = $data['data_2']['id'];
    $formula_json = set_formula_json_data($id, 'zhikong', $data['formula']);
    // 是否需要显示理论浓度
    if('' !== $data['C_i']['_vd0']){
        $formula_json = set_formula_json_data($formula_json, 'show_avg', 
            ['title' => '理论浓度', 'text' => $data['C_i']['formula']]
        );
    }
    // 原水样检测值可能会进行数据位数修整，重新保存
    $DB->query("UPDATE `assay_order` SET `vd0`='{$data['data_1']['vd0']}' WHERE `id`='{$id}' LIMIT 1");
    // 保存加标样数据信息
    $sql = "UPDATE `assay_order` SET 
                `js_gongshi`='{$formula_json}',
                `vd0`='{$data['data_2']['vd0']}',
                `ping_jia`='{$data['ping_jia']}',
                `ping_jun`='{$data['C_i']['vd0']}',
                `xiang_dui_pian_cha`='{$data['jbhsl']}' WHERE `id`='{$id}' LIMIT 1";
    $DB->query($sql);
}
function update_biaoyang2ao($data){
    global $DB;
    $id = $data['data_1']['id'];
    $formula_json = set_formula_json_data($id, 'zhikong', $data['formula']);
    if('' !== $data['show_avg']['title']){
        $formula_json = set_formula_json_data($formula_json, 'show_avg', $data['show_avg']);
    }
    // 
    if(is_numeric($data['xdwc']) && $data['xdwc']==0){
        $data['xdwc'] = 0;
    }
    $sql = "UPDATE `assay_order` SET 
                `js_gongshi`='{$formula_json}',
                `vd0`='{$data['data_1']['vd0']}',
                `ping_jia`='{$data['ping_jia']}',
                `ping_jun`='{$data['show_avg']['text']}',
                `xiang_dui_pian_cha`='{$data['xdwc']}' WHERE `id`='{$id}' LIMIT 1";
    $DB->query($sql);
}

// 设置formula_json字段里面的数据
function set_formula_json_data($data, $key, $value){
    global $DB;
    // 存在json字段中
    if(is_numeric($data)){
        $sql = "SELECT `js_gongshi` FROM `assay_order` WHERE `id`='{$data}'";
        $arow = $DB->fetch_one_assoc($sql);
        $data = $arow['js_gongshi'];
    }
    $data = empty($data) ? array() : JSON_addslashes(json_decode($data,true));
    // 赋新值
    $data[$key] = $value;
    return JSON($data);
}
// 清空质控数据
function clear_ao_zk_data($id){
    global $DB;
    $sql = "UPDATE `assay_order` SET `js_gongshi`='',`ping_jun`='',`xiang_dui_pian_cha`='',`ping_jia`='' WHERE `id`='{$id}'";
    return $DB->query($sql);
}
// 只进不舍修约函数
function ceil_round($afloat, $c){
	if(!is_numeric($afloat)){
		return '';
    }
    $ex = explode('.', strval($afloat));
    if(intval($ex[1]) > $c){
        $a = (float)$afloat < 0 ? -1 : 1;
        $afloat = abs($afloat);
        $afloat *= pow(10, $c);
        $afloat = ceil($afloat);
        $afloat /= pow(10, $c);
        $afloat *= $a;
    }
    $ex = explode('.', strval($afloat));
    if(intval($ex[1]) < $c){
        return _round($afloat, $c);
    }
    return $afloat;
}
// 只舍不进修约函数
function floor_round($afloat, $c){
	if(!is_numeric($afloat)){
		return '';
    }
    $ex = explode('.', strval($afloat));
    if(intval($ex[1]) > $c){
        $a = (float)$afloat < 0 ? -1 : 1;
        $afloat = abs($afloat);
        $afloat *= pow(10, $c);
        $afloat = floor($afloat);
        $afloat /= pow(10, $c);
        $afloat *= $a;
    }
    $ex = explode('.', strval($afloat));
    if(intval($ex[1]) < $c){
        return _round($afloat, $c);
    }
    return $afloat;
}
