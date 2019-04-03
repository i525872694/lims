<?php
/**
 * 人员信息导入
 * 无需身份证版，所有信息直接导入，请注意文件中判断起始行部分，表头没被动过就没问题
 */
include("./temp/config.php");
require_once "$rootdir/inc/classes/PHPExcel/IOFactory.php";
$fzx_id = FZX_ID;//分中心id
$title      = "人员信息导入";
$muban_img  = "人员信息导入模板截图.png";
echo '分中心id[ '.$fzx_id." ]<br>";
//导航
$trade_global['daohang'][]  = array('icon'=>'','html'=>'人员信息导入','href'=>'./basis_data_set/user_daoru.php');
$_SESSION['daohang']['site_import']    = $trade_global['daohang'];
$pwd_default = md5("lims123");//设置默认密码
if(!empty($_FILES['file']['name'])){
    $xxx    = explode('.',$_FILES['file']['name']);
    $houzhui= end($xxx);
    $newname= date('ymdhis').".".end($xxx);
    $path   = "upfile/".$newname;
    $miao   = date('s');
    if($houzhui!='xls' && $houzhui!='xlsx'){
        echo "<script>alert('请选择excel格式的文件');location.href='site_import.php'</script>";
        exit;
    }
	if(file_exists(trim($_FILES['file']['tmp_name']))){//判断上传的文件是否存在
        if(move_uploaded_file(trim($_FILES['file']['tmp_name']),$path)){//把上传的文件重命名并移到系统upfile目录下
            $inputFileName = $path;
            $objPHPExcel   = PHPExcel_IOFactory::load($inputFileName);
            $sheetData     = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);//把表格内容转成数组
			 //print_rr($sheetData);die;
			
            $field_user = ["姓名"=>"userid","性别"=>"sex","证件号码"=>"idcard","职务"=>"zhiwu","职称"=>"zc","学历"=>"whcd","专业"=>"zy","是否具备独立熟练操作大型仪器能力"=>"dxyqcz","检验岗位证书编号"=>"jygwzsbh","管理岗位证书编号"=>"glgwzsbh","评价岗位证书编号"=>"pjgwzsbh","采样岗位证书编号"=>"cygwzsbh","采样挂牌编号"=>"cygpbh","挂牌日期"=>"gprq","离岗日期"=>"lgrq","职工性质"=>"zgxz","入职日期"=>"diaoru_date","离职日期"=>"lzrq","备注"=>"bz"];
            $field_riqi = ["挂牌日期","离岗日期","入职日期","离职日期"];
            
            $begin = $i = $j = $k = 0;
            foreach ($sheetData as $key_hang => $value_hang) {
                if(in_array('姓名', $value_hang) && in_array('证件号码', $value_hang)){//判断起始行
                    $begin = $key_hang;
                    $title = $sheetData[$begin];//表头内容
                }
				if($begin == 0) continue;
				
          		$nickname = $sex = $content = $idcard = '';
                foreach ($value_hang as $k_alpha => $v_cell) {
                	$v_cell = trim($v_cell);
                	if(array_key_exists($v_cell, $field_user)){
                		$field_user_arr[$k_alpha] = $field_user[$v_cell];
                	}else{
                		$field = $field_user_arr[$k_alpha];//数据库字段
                		//判断是否存在重名,并将基本信息插入`users`表
                		if($title[$k_alpha] == "姓名"){
							$name = $v_cell;
                			$res = $DB->fetch_one_assoc("select count(*) as num from `users` where `userid`='$v_cell'");
                			if($res['num']){//如果数据库存在重名,插入昵称
                				
                			}else{
                				echo $sql_user_in = "insert into `users`(`fzx_id`,`nickname`,`userid`,`password`,`sex`) values('$fzx_id','$v_cell','$v_cell','$pwd_default','";
                			}
                			                			
                		}else{
                			if($title[$k_alpha] == "性别") {
                				$sex = $v_cell;
                				continue;
                			}
                			if($title[$k_alpha] == "证件号码") {
                				$idcard = $v_cell;
                			}

                			if($title[$k_alpha] == "单位名称") continue;

                			if(!empty($field) && $v_cell){
                				if(in_array($title[$k_alpha], $field_riqi)){//校验日期格式
	                    			$v_cell = check_date($v_cell);
	                    		}
	                    		$content .= " `$field` = '$v_cell',";//组装sql语句内容
                			}
                		}
                	}
                }

				
                if($content){
                	$content = rtrim($content,",");
                	$uid = $sql_up = '';
					
					$sql_user_in .="$sex')";
					print_rr($sql_user_in);
					//---------------------------------|SQL执行语句
					// $res1= $DB->query($sql_user_in);
					// $uid = $DB->insert_id();
					//---------------------------------|
					$sql_up = "insert into `hn_users` set $content, `uid`='$uid'";
					print_rr($sql_up);
					//---------------------------------|SQL执行语句
					// $result = $DB->query($sql_up);
					//---------------------------------|
					if($res1 && $result) {
						echo "ok";
					}else{
						echo $res1."-".$result;
						echo "<font color='red'>fail</font>";
					}
					$result ? $j++ : $k++;
                }        
        	}
    	}
    	 echo "本次导入/更新成功".$j."条,失败".$k."条";
    }
    
}else{
    disp("site_import.html");
}

//检查时间格式,转换为Y-m-d的形式
function check_date($riqi){
	date_default_timezone_set('PRC');
	$riqi = trim($riqi);
    if(strlen($riqi) == 4){
        return $riqi;
    }elseif(strlen($riqi) == 6 && strlen(intval($riqi)) ==6){
        $y = substr($riqi, 0, 4);
        $m = substr($riqi, 4);
        return $y."-".$m;
	}

	$arr = ['.'=>'-','\\'=>'-','/'=>'-','"年"'=>'-','"月"'=>'-','"日"'=>''];
	$riqi = strtr($riqi, $arr);
	$date = date("Y-m-d",strtotime($riqi));

	//判断是否是 09-01-88 这种美式写法 转换成yyyy-mm-dd的形式
	preg_match('/^\d{2}-\d{2}-\d{2}/',$riqi,$match);
	if(!empty($match)){
		$y_m_d = explode('-',$match[0]);
		$yyyy = $y_m_d[2]>30 ? '19'.$y_m_d[2] : '20'.$y_m_d[2];
		$date = $yyyy.'-'.$y_m_d[0].'-'.$y_m_d[1];	
	}
	//如果日期长度是4位，则表示是年份，无需再格式化 
	if(strlen($riqi) == "4"){
		$date = $riqi;
    }
    
	return $date;
}
