<?php
//上传
include "../temp/config.php";
$fzx_id = $u['fzx_id'];
$date   = date("Y-m-d H:i:s");
//上传处理
if(!empty($_POST['fsub'])&&!empty($_FILES['pifile']['name']))
{
    set_time_limit(0);
    $flag == '';
    include '../temp/PHPExcel/IOFactory.php';
	############分批站点excel导入
	if(!empty($_FILES['pifile']['name']))
	{
		$xxx     = explode('.',$_FILES[pifile][name]);
		$cnt     = count($xxx);
		$newname = date(ymdhis).".".$xxx[$cnt-1];
		$path    = "upfile/".$newname;
		$miao    = date('s');
		if($xxx[$cnt-1]!='xls'&&$xxx[$cnt-1]!='xlsx'){
			echo "<script>alert('请上传excel格式的文件');location.href='upwz.php'</script>";
            exit;
        }
		if(file_exists($_FILES[pifile][tmp_name]))
		{//判断上传的文件是否存在
			if(move_uploaded_file($_FILES[pifile][tmp_name],$path))
			{//把上传的文件重命名并移到系统upfile目录下
               $inputFileName = $path;
               $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
               $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
               if($sheetData[1][A]!='物质编号' && $sheetData[1][A]!='wz_bh')
               {//文件错误判断
               		echo "<script>alert('上传文件内容不附');location.href='upwz.php'</script>";
               		exit;
               }
	        }
	    }
	}
	//取类型
	$leixing = $_POST['leixing'];
	//取项目id
	$vid_arr =array();
	$sql_vid = $DB->query("SELECT id,value_C FROM `assay_value`");
	while($re1 = $DB->fetch_assoc($sql_vid)){
		$vid_arr[$re1['value_C']] = $re1['id'];
	}
	$kaishi = '';//判断用户是否自己复制了模板
	if($sheetData[1][A] == 'wz_bh' && $sheetData[1][C] == 'guobiao'){
		$lie = $sheetData[1];
		$kaishi = 2;
	}else{ 	 	 	
		$lie = array('A'=>'wz_bh','B'=>'wz_name','C'=>'guobiao','D'=>'wz_type','E'=>'time_limit','F'=>'manufacturer','G'=>'amount','H'=>'dilution','I'=>'consistence','J'=>'eligible_bound','K'=>'c_bound','L'=>'create_man');
		$kaishi = 1;
	}
	//开始循环
	$i = 1;
	$error_water_type   = '';
	foreach($sheetData as $kk => $vv)
	{
	   if($i>$kaishi)
	   {
	   		if($vv[A] == '' && $vv[B] == '' && $vv[C] == ''){
	   			continue;
	   		}
			foreach($vv as $zimu => $row)
			{
				$wz_arr[$lie[$zimu]] = $row;
			}

			///根据国标和wz_bh判断重复
			$sql_wz_old   = $DB->fetch_one_assoc("SELECT id,wz_bh FROM `bzwz` WHERE wz_bh = '{$wz_arr['wz_bh']}' AND guobiao='{$wz_arr['guobiao']}' ");
            $update = 'no';
            if(!empty($sql_site_old['id'])){
	            //旧有的id
	            $update = $sql_wz_old['id'];
            }
            //将项目替换成id
			if(isset($wz_arr['vid'])&&$wz_arr['vid']!=''){
	            $value = vidname($wz_arr['vid']);
	            $vids  = $vid_arr[$value];
        	}
            if($update){
            	$guanlian = $DB->fetch_one_assoc("SELECT id,wz_bh FROM `bzwz_detail` WHERE wz_bh = '{$wz_arr['wz_bh']}' AND guobiao='{$wz_arr['guobiao']}' ");
            	$DB->query("update bzwz set amount= amount+".$wz_arr['amount']." WHERE id = '$update' AND guobiao='{$wz_arr['guobiao']}'");
            }else{
            	$a	= $DB->query("insert into `bzwz` set `fzx_id`='$fzx_id',`wz_type`='$leixing',`wz_bh`='{$wz_arr['wz_bh']}',`guobiao`='{$wz_arr['guobiao']}',`wz_name`='{$wz_arr['wz_name']}',`time_limit`='{$wz_arr['time_limit']}',`manufacturer`='{$wz_arr['manufacturer']}',`amount`='{$wz_arr['amount']}',`unit`='{$wz_arr['amount']}',`create_man`='{$wz_arr['create_man']}',`create_date`='".date("Y-m-d H:i:s")."'");
				$insert_id	= $DB->insert_id();
				//插入到关联项目表里
				$b	= $DB->query("insert into `bzwz_detail` set `wz_bh`='{$insert_id}',`vid`='$vids',`consistence`='".$wz_arr['consistence'].'mg/L'."',`eligible_bound`='".$wz_arr['eligible_bound'].'mg/L'."',`create_date`='".date("Y-m-d")."'");
            }
            

		}
	}
}else{
	disp("bzwz/upwz");exit;
}
function vidname($value){
	if($value=='菌落总数'){
		$value	= '细菌总数';
	}
	if($value=='硝酸盐(以N计)'){
		$value	= '硝酸盐氮';
	}
	if($value=='硝酸盐'){
		$value	= '硝酸盐氮';
	}
	if($value=='总硬度（以CaCO3计）'){
		$value	= '总硬度';
	}
	if($value=='耗氧量（CODMn法，以O2计）'){
		$value	= '高锰酸盐指数（CODMn法,以O₂计)';
	}
	if($value=='挥发酚类（以苯酚计）'){
		$value	= '挥发酚';
	}
	if($value=='阴离子合成洗涤剂'){
		$value	= '阴离子表面活性剂';
	}
	if($value=='阴离子合成洗涤剂'){
		$value	= '阴离子表面活性剂';
	}
	if($value=='石油类'){
			$value	= '石油类(总量)';
	}
	if($value=='氟'){
		$value	= '氟化物';
	}
	if($value=='硝酸盐'){
		$value	= '硝酸盐氮';
	}
	if($value=='亚硝酸盐'){
		$value	= '亚硝酸盐氮';
	}
	if($value=='一溴二氯甲烷'){
		$value	= '二氯一溴甲烷';
	}
	if($value=='二溴一氯甲烷'){
		$value	= '一氯二溴甲烷';
	}
	if($value=='三氯苯混合标准物质'){
		$value	= '三氯苯';
	}
	if($value=='四氯苯混合标准物质'){
		$value	= '四氯苯';
	}
	if($value=='有机磷混合标准物质'){
		$value	= '有机磷';
	}
	if($value=='6种多环芳烃混合标准物质'){
		$value	= '多环芳烃（总量）';
	}
	if(stristr($value,'多氯联苯')){
		$value	= '多氯联苯';
	}
	if($value=='TOC'){
		$value	= '总有机碳(TOC)';
	}
	if($value=='邻苯二甲酸二正辛酯'){
		$value	= '邻苯二甲酸二辛酯';
	}
	if(stristr($value,'氯仿中')){
		$value	= str_replace('氯仿中', '', $value);
	}
	if($value=='四氯苯'){
		$value	= '四氯苯(总量)';
	}
	if($value=='有机磷'){
		$value	= '有机磷农药';
	}
	if($value=='乙二胺四乙酸二钠'){
		$value	= '乙二胺四乙酸';
	}
	if($value=='氯化锌'){
		$value	= '氯化物';
	}
	if($value=='硫酸根'){
		$value	= '硫酸盐';
	}
	if($value=='氯根'){
		$value	= '氯酸盐';
	}
	if($value=='十二烷基苯磺酸钠'){
		$value	= '十二烷基苯磺酸盐';
	}
	return $value;
}
