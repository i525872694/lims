<?php
/**
 * 功能：标准溶液，标准样品
 * 作者: Mr Zhou
 * 日期: 2014-10-17
 * 描述: 标准溶液，标准样品 添加,修改,删除
*/
define('__maxline__',10);
include ('../temp/config.php');
$fzx_id = FZX_ID;
$wz_name = empty($_GET['wz_name'])? '':$_GET['wz_name'];
$tabs = ($_GET['wz_type']=='标准溶液') ? '#tabs-1':'#tabs-2';
//导航
$trade_global['daohang'] = array(array('icon'=>'icon-home home-icon','html'=>'首页','href'=>$rooturl.'/main.php'),array('icon'=>'','html'=>$_GET['wz_type'],'href'=>$rooturl.'/bzwz/bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs),array('icon'=>'','html'=>$_GET['wz_type'].$wz_name.$_GET['action'],'href'=>$current_url));
$trade_global['js'] = array('jquery.date_input.js');
$trade_global['css'] = array('lims/main.css','date_input.css');

if(''==$_GET['action']){
    $_GET['action']='新增';
}
$action_style = ' class="btn btn-primary btn-sm" ';
$note = ($_GET['wz_type']=='标准溶液') ? '备注' : '浓度范围';
//查询出细分类型可供选择
$sql = "SELECT * FROM `bzwz` WHERE `wz_type` = '$_GET[wz_type]' GROUP BY `wz_type_subdivide`";
$re = $DB->query($sql);
$type_subdivide_select = "<option disabled>请选择</option>";
while($data = $DB->fetch_assoc($re)){
	if(!empty($data['wz_type_subdivide'])){
		$type_subdivide_select .= "<option value='$data[wz_type_subdivide]'>$data[wz_type_subdivide]</option>";	
	}
}
//获取所有的项目
$xm_arr = array('请选择');
$query = $DB->query("select * from `assay_value`");
while($row = $DB->fetch_assoc($query)){
    $xm_arr[$row['id']] = $row['value_C'];
}
switch($_GET['action']){
    case '新增': //显示 标准物质登记表 画面
        $class      = 'class="class"';
        $_unit      = '<select name="单位"><option value="支">支</option><option value="瓶">瓶</option><option value="套">套</option></select>';
        $_action    = '<input '.$action_style.' type="submit" name="action" value="保存">';
        $_dilution_method   = '<textarea name="稀释方法" class="inputl">'.$r['dilution_method'].'</textarea>';
        $item_list  = '<select name="vid[]"><option></option>';
        foreach ($xm_arr as $key => $value) {
            $item_list.='<option value="'.$key.'">'.$value.'</option>';
        }
        $item_list.='</select>';
        for($i=0;$i<__maxline__;$i++){
            $lines.=temp('bzwz/bzwz_line');
        }
        disp('bzwz/bzwz');
        break;
    case '保存': //保存 新的 标准物质
        $sql = "INSERT INTO `bzwz` (`fzx_id`,`wz_type`,`guobiao`,`wz_bh`,`wz_name`,`time_limit`,`manufacturer`,`amount`,`unit`,`dilution_method`,`create_man`,`create_date`,`limit_num`,`wz_type_subdivide`)
            VALUES ('$fzx_id','{$_GET['wz_type']}','{$_GET['guobiao']}','{$_GET['编号']}','{$_GET['名称']}','{$_GET['有效期']}','{$_GET['生产单位']}','{$_GET['数量']}','{$_GET['单位']}','{$_GET['稀释方法']}','{$u['userid']}',curdate(),'{$_GET['limit_num']}', '{$_GET['wz_type_subdivide']}')";
        $DB->query($sql);
        $id=$DB->insert_id();
        foreach ($_GET['vid'] as $i => $_vid) {
            if(empty($_vid)){
                continue;
            }
            $_c_bound       = $_GET['c_bound'][$i];
            $_consistence   = $_GET['nong_du'][$i];
            $_eligible_bound= $_GET['bound'][$i];
            $_bw_note= $_GET['bw_note'][$i];
            $sql = "INSERT INTO `bzwz_detail` (`wz_id`,`vid`,`consistence`,`eligible_bound`,`c_bound`,`create_date`,bw_note) 
                VALUES ($id,'$_vid','$_consistence','$_eligible_bound','$_c_bound',curdate(),'$_bw_note')";
            $DB->query($sql);
        }
        $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`dealer`,`op_date`,`jie_cun`,`ls_note`) 
            VALUES ($id,'{$_GET['wz_type']}','入库','{$_GET['数量']}','{$u['userid']}','{$u['userid']}',curdate(),'{$_GET['数量']}','')");
        gotourl('bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs);
        break;
    case '修改': //显示修改画面
        $_action= '<input '.$action_style.' type="submit" name="action" value="编辑完成">';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        $_unit  ='<select name="单位"><option value="'.$r['unit'].'">'.$r['unit'].'</option><option value="支">支</option><option value="瓶">瓶</option><option value="套">套</option></select>';
        $_dilution_method='<textarea name="稀释方法" class="inputl">'.$r['dilution_method'].'</textarea>';
        $sql    = "SELECT * from `bzwz_detail` where `wz_id`=$r[id] order by `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        foreach ($xm_arr as $key => $value) {
            $_item_list.='<option value="'.$key.'">'.$value.'</option>';
        }
        $i=0;
        while($rd=$DB->fetch_assoc($RD)){
            $value_C=$xm_arr[$rd['vid']];
            $item_list="<select name=vid[]><option value=$rd[vid]>$value_C</option><option></option>".$_item_list."</select>";
            $lines .= temp('bzwz/bzwz_line');
            $i++;
        }
        $item_list="<select name=vid[]><option></option>".$_item_list."</select>";
        $k  = intval(__maxline__-$n);
        $rd = array();
        if($k>0){
            for($i;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz');
        break;
    case '编辑完成':
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        $DB->query("UPDATE `bzwz` SET `wz_bh`='{$_GET['编号']}', `wz_name`='{$_GET['名称']}', guobiao='{$_GET['guobiao']}' , `time_limit`='{$_GET['有效期']}' , `manufacturer`='{$_GET['生产单位']}' ,`amount`='{$_GET['数量']}',`unit`='{$_GET['单位']}',`dilution_method`='{$_GET['稀释方法']}',`modify_man`='{$u['userid']}' , `modify_date`=curdate(),`limit_num`='{$_GET['limit_num']}', `wz_type_subdivide`='{$_GET['wz_type_subdivide']}' WHERE `id`='{$_GET['wz_id']}'");
        if($r['amount']!=$_GET['数量']){
            $k=$r['amount']-$_GET['数量'];
            if($k>0) {
                $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`op_date`,`jie_cun`,`ls_note`) VALUES ('{$_GET['wz_id']}','{$_GET['wz_type']}','出库',$k,'{$u['userid']}',curdate(),'{$_GET['数量']}','{$_GET['wz_type']}盘亏')");
            }else{
                $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`op_man`,`op_date`,`jie_cun`,`ls_note`) VALUES ('{$_GET['wz_id']}','$_GET[wz_type]','入库',$k,'$u[userid]',curdate(),'$_GET[数量]','{$_GET['wz_type']}盘盈')");
            }
        }
        foreach($_GET['_id'] as $i => $value){
            $_id    = intval($_GET['_id'][$i]);
            $_vid   = intval($_GET['vid'][$i]);
            $_consistence    = trim($_GET['nong_du'][$i]);
            $_eligible_bound = trim($_GET['bound'][$i]);
            $_c_bound        = trim($_GET['c_bound'][$i]);
            $_bw_note= trim($_GET['bw_note'][$i]);
            if($_id){
                if($_consistence){
                    $DB->query("UPDATE `bzwz_detail` SET `wz_id`='{$_GET['wz_id']}',`vid`='$_vid',`consistence`='$_consistence',`eligible_bound`='$_eligible_bound',`c_bound`='$_c_bound',`bw_note`='$_bw_note' WHERE `id`=$_id");
                }else{
                    $value_C=$xm_arr[$_vid];
                    prompt("$value_C 项目信息不完整,从数据库中删除!");
                    $DB->query("delete from `bzwz_detail` where `id`=$_id");
                }
            }else{
                if($_consistence){
                    $DB->query("INSERT INTO `bzwz_detail` (`wz_id`,`vid`,`consistence`,`eligible_bound`,`c_bound`,`create_date`) VALUES ('{$_GET['wz_id']}',$_vid,'$_consistence','$_eligible_bound','$_c_bound',curdate())");
                }
            }
        }
        gotourl("bzwz.php?action=修改&wz_id=$_GET[wz_id]&wz_type=$_GET[wz_type]");
    case '查看':
        $_action    = '<a class="blue icon-print bigger-130" href="bzwz.php?action=打印&wz_id='.$_GET['wz_id'].'&wz_type='.$_GET['wz_type'].'" target="_blank">打印</a>';
        $readonly   = 'readonly';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        $_unit  = $r['unit'];
        $_dilution_method   = $r['dilution_method'];
        $sql    = "SELECT * FROM `bzwz_detail` WHERE `wz_id`='{$r['id']}' ORDER BY `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        for($i=1;$rd=$DB->fetch_assoc($RD);$i++){
            $item_list=$xm_arr[$rd['vid']];
            $rd['consistence']      = (!$u['bzwz_manage']) ? '-' : $rd['consistence'];
            $rd['eligible_bound']   = (!$u['bzwz_manage']) ? '-' : $rd['eligible_bound'];
            $lines  .= temp('bzwz/bzwz_line');
        }
        $k  = intval(__maxline__-$n);
        $rd = array();
        $item_list  = '&nbsp;';
        if($k>0){
            for($i=0;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz');
        break;
    case '打印':$readonly   = 'readonly';
        $sql    = "SELECT * FROM `bzwz` WHERE `fzx_id`='$fzx_id' AND `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        $_unit  = $r['unit'];
        $_dilution_method   = $r['dilution_method'];
        $sql    = "SELECT * FROM `bzwz_detail` WHERE `wz_id`='{$r['id']}' ORDER BY `id`";
        $RD     = $DB->query($sql);
        $n      = $DB->num_rows($RD);
        for($i=1;$rd=$DB->fetch_assoc($RD);$i++){
            $item_list=$xm_arr[$rd['vid']];
            $rd['consistence']      = (!$u['bzwz_manage']) ? '-' : $rd['consistence'];
            $rd['eligible_bound']   = (!$u['bzwz_manage']) ? '-' : $rd['eligible_bound'];
            $lines.='<tr align=center>
                        <td align=left>'.$item_list.'</td>
                        <td>'.$rd['consistence'].'</td>
                        <td>'.$rd['eligible_bound'].'</td>
                        <td>'.$rd['c_bound'].'</td>
                        <td>'.$rd['bw_note'].'</td>
                    </tr>';
    
        }
        $k  = intval(__maxline__-$n);
        $rd = array();
        $item_list  = '&nbsp;';
        if($k>0){
            for($i=0;$i<$k;$i++){
                $lines .= temp('bzwz/bzwz_line');
            }
        }
        disp('bzwz/bzwz','head_print');
        break;
    case '删除':
  		$sql="SELECT wz_status FROM `bzwz` WHERE id='$_GET[wz_id]'";
        $re=$DB->query($sql);
        $data=$DB->fetch_assoc($re);
        //wz_status 等于0的时候将其放入回收站中，等于1的时候，就不让他显示了
        if($data['wz_status']==0){
            $sql="UPDATE `bzwz` SET `wz_status`=1 WHERE `id`='$_GET[wz_id]'";
            $DB->query($sql);
            if($_GET['wz_type']=='标准溶液'){
                $tabs = '#tabs-1';
            }else{
                $tabs = '#tabs-2';
            }
            gotourl("bzwz_list.php?wz_type=$_GET[wz_type]".$tabs);
        }else if($data['wz_status']==1){
            $sql="UPDATE `bzwz` SET `wz_status`=2 WHERE `id`='$_GET[wz_id]'";
            $DB->query($sql);
            $sql="SELECT * FROM `bzwz` WHERE `wz_status`=1";
            $re=$DB->query($sql);
            $num=$DB->num_rows($re);
            if($num==0){
                echo "<script>alert('回收站已空！');window.colse();</script>";
            }else{
                gotourl("bzwz_rubbish.php?wz_type=$_GET[wz_type]");
            }
        }
        gotourl('bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs);
        break;
    //     $DB->query("DELETE FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
    //     $DB->query("DELETE FROM `bzwz_detail` WHERE `wz_id`='{$_GET['wz_id']}'");
    //     $DB->query("DELETE FROM `bzwz_ls` WHERE `wz_id`='{$_GET['wz_id']}'");
    //     gotourl('bzwz_list.php?wz_type='.$_GET['wz_type'].$tabs);
    //     break;
    case "入库":
        $_action= '<input '.$action_style.' type="submit" name="action" value="入库提交" />';
        $_user  = '经手人';
        $user_  = $u['userid'];
        $date   = date('Y-m-d');
        $useralllist = '';
        $R = $DB->query("SELECT `id`, `userid` FROM `users` WHERE fzx_id='{$u['fzx_id']}' AND `group` !='0' AND `group` != '测试组' ORDER BY `userid` DESC");   /*找出用户资料*/
        while ( $ru = $DB->fetch_assoc( $R ) ) {
            $useralllist .= "<option value='{$ru['userid']}'>{$ru['userid']}</option>";
        }
        $r      = $DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        disp('bzwz/bzwz_in_out');
        break;
    case "入库提交":
        $DB->query("UPDATE `bzwz` SET `amount`=`amount`+$_GET[数量] WHERE `id`='{$_GET['wz_id']}'");
        $sql    = "SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'";
        $r      = $DB->fetch_one_assoc($sql);
        $op_date= trim($_GET['date']) ? trim($_GET['date']):date('Y-m-d');
        $sql    = "INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`dealer`,`op_man`,`jie_cun`,`op_date`) VALUES ('{$_GET['wz_id']}','{$r['wz_type']}','入库','$_GET[数量]','$_GET[_user]','$u[userid]',$r[amount],'$op_date')";
        $DB->query($sql);
         echo "<script>history.go(-2);</script>";
        // gotourl('bzwz_list.php?wz_type='.$r['wz_type'].$tabs);
        break;
    case "出库":
        $_action='<input '.$action_style.' type="submit" name="action" value="出库提交" />';
        $_user='领用人';
        $user_  = $u['userid'];
        $date   = date('Y-m-d');
        $R = $DB->query("SELECT `id`, `userid` FROM `users` WHERE fzx_id='{$u['fzx_id']}' AND `group` !='0' AND `group` != '测试组' ORDER BY `userid` DESC");   /*找出用户资料*/
        while ( $ru = $DB->fetch_assoc( $R ) ) {
            $useralllist .= "<option value='{$ru['userid']}'>{$ru['userid']}</option>";
        }
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        disp('bzwz/bzwz_in_out');
        break;
    case "出库提交":
        get_int($_GET[数量]);
        $op_date= trim($_GET['date']) ? trim($_GET['date']):date('Y-m-d');
        if($_GET[数量]<1) {$_GET[数量]=0;}
        $DB->query("UPDATE `bzwz` SET `amount`=`amount`-{$_GET['数量']} WHERE `id`='{$_GET['wz_id']}'");
        $r=$DB->fetch_one_assoc("SELECT * FROM `bzwz` WHERE `id`='{$_GET['wz_id']}'");
        $DB->query("INSERT INTO `bzwz_ls` (`wz_id`,`wz_type`,`op_type`,`amount`,`dealer`,`op_man`,`jie_cun`,`op_date`) VALUES ($_GET[wz_id],'$r[wz_type]','出库','$_GET[数量]','$u[userid]','$_GET[_user]',$r[amount],'$op_date')");
        echo "<script>history.go(-2);</script>";
        // gotourl('bzwz_list.php?wz_type='.$r['wz_type'].$tabs);
        break;
        case "还原":
        $DB->query("UPDATE `bzwz` SET `wz_status` = 0 WHERE id='$_GET[wz_id]'");
        $sql="SELECT * FROM `bzwz` WHERE `wz_status`=1 AND `wz_type`='$_GET[wz_type]'";
        $re=$DB->query($sql);
        $data=$DB->fetch_assoc($re);
        if(empty($data)){
            if($_GET['wz_type']=='标准溶液'){
                $tabs = '#tabs-1';
            }else{
                $tabs = '#tabs-2';
            }
            gotourl("bzwz_list.php?wz_type=$_GET[wz_type]".$tabs);
        }else{
            gotourl("bzwz_rubbish.php?wz_type=$_GET[wz_type]");
        }        
        break;
}
?>
