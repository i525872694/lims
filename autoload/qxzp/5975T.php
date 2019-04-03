<?php
/*
 *功能：气质联用仪器导入页面
 *作者：zhengsen
 *时间：2015-01-06
 */
$arr     = @file($lujing2);//把文件 读取成数组
$xmArr = array("氯乙烯"=>'302',"二氯甲烷"=>'495',"三氯甲烷"=>'496',"四氯化碳"=>'280',"二氯一溴甲烷"=>'499',"一氯二溴甲烷"=>'498',"三溴甲烷"=>'497',"百菌清"=>'212',"六氯丁二烯"=>'301','1,4-二氯苯'=>'337',"1,2-二氯苯"=>'336',"1,3,5-三氯苯"=>'341',"1,2,4-三氯苯"=>'553',"1,2,3-三氯苯"=>'340',"六氯苯"=>'206',"α-六六六"=>'628',"β-六六六"=>'631',"γ-六六六"=>'634',"δ-六六六"=>'637',"ρ,ρ'-DDE"=>'640',"ρ,ρ'-DDD"=>'646',"ρ,ρ'-DDT"=>'649',"敌敌畏"=>'222',"乐果"=>'208',"甲基对硫磷"=>'211',"马拉硫磷"=>'203',"对硫磷"=>'209',"敌百虫"=>'227',"内吸磷"=>'228',"毒死蜱"=>"220","七氯"=>'204',"环氧七氯"=>'226',"二氯乙酸"=>'510','三氯乙酸'=>'511',"丙烯酰胺"=>'386',"三氯乙醛"=>'503',"苯"=>'315',"甲苯"=>'316',"乙苯"=>'323',"间,对-二甲苯"=>'650',"间,对二甲苯"=>'650',"间-对二甲苯"=>'650',"苯乙烯"=>'309',"邻-二甲苯"=>'320',"邻二甲苯"=>'320',"1,1-二氯乙烯"=>'303',"丙烯腈"=>'313',"反1,2-二氯乙烯"=>'306',"1,2-二氯乙烯(反)"=>'306',"1,2-二氯乙烯(顺)"=>'305',"顺1,2-二氯乙烯"=>'305',"氯丁二烯"=>'300',"1,2-二氯乙烷"=>'283',"1,1,1-三氯乙烷"=>'284',"三氯乙烯"=>'307',"四氯乙烯"=>'308',"异丙苯"=>'324',"氯苯"=>'675',"环氧氯丙烷"=>"292");
//分量项目
$fl_xmArr=array("320"=>"317","650"=>"317","305"=>'304',"306"=>'304');
$html    = array("<BR>","<BR/>","<BR />","<I>","</I>","<B>","</B>","<A NAME=2></A>","&GT;");//转成html时产生的 标签  全部替换成空
$kongGe  = array("&NBSP;","&#160;");

$unit_arr=array('MG/L'=>array('hs'=>'1','blws'=>'2'),'μG/L'=>array('hs'=>'0.001','blws'=>'7'),"NG/L"=>array('hs'=>'0.000001','blws'=>'8'));

$quzhi_zt=$get_bar='';
$zhi  =$bar_code_arr =$jcxm_arr=array();
$cishu=0;
$get_xmzt=0;//获得项目的状态
$json_arr=array();
for($i=0;$i<count($arr);$i++){
	//循环每条数据并把每条数据去除两端空白并把字符全部转换成大写
	$line  = trim(str_replace($kongGe," ",str_replace($html,"",strtoupper($arr[$i]))));
	$line = str_replace(array("（","（ ","）"," ）","，","，","、"),array("(","(",")",")",",",",",","),$line);
	//取出编号
	if(match_bar($line)){
		$bar = match_bar($line);
		if(!in_array($bar,$bar_code_arr)){
			$bar_code_arr[]=$bar; //样品编号
		}
		$get_bar='1';
		continue;
	}
	if($get_bar){
		$temp_arr=explode(" ",$line);
		//print_rr($temp_arr);
		foreach($temp_arr as $key=>$value){
			if(stristr($value,'目标化合物')){
				$get_xmzt=1;
			}
			if(stristr($value,'定量报告')){
				$get_xmzt='';
			}
			if(isset($xmArr[$value])&&$get_xmzt){
				$cishu=0;
				$xm_vid=$xmArr[$value];
				if(isset($fl_xmArr[$xm_vid])){
					$fl_xm_vid=$xm_vid;//分量的项目id
					$xm_vid=$fl_xmArr[$xm_vid];//总量的项目id
				}
				if(!in_array($value,$jcxm_arr)){
					$jcxm_arr[]=$value;//项目编号
				}
				$quzhi_zt = "start";
				continue;
			}
			if($quzhi_zt=='start' &&(stristr($value,'.')||stristr($value,'低于'))){
                //获取单位 此处就这样写，不然匹配不到单位
		        if(stristr($line,'MG/L')||stristr($line,'UG/ML')||stristr($line,'μG/ML')){
		           $unit='MG/L';
		        }
		        if(stristr($line,'μG/L')||stristr($line,'UG/L') || stristr($line,'μG/L')){
		            $unit='μG/L';
		        }
		        if(stristr($line,'NG/L')){
		            $unit='NG/L';
		        }
				$cishu++;
					if($cishu==2){
					if(stristr($value,'N.D.')||stristr($value,'#') || stristr($value,'低于')){
						$value=0;
					}else{
						$value=number_format(($value*$unit_arr[$unit]['hs']),$unit_arr[$unit]['blws']);
						$value=del0($value);
					}			
					//判断如果是分量的项目要进行存储在json里面
					if(in_array($xm_vid,$fl_xmArr)){
						$json_arr=array();
						if(empty($zhi['vd26'][$bar][$xm_vid])){
							$json_arr[$fl_xm_vid]=$value;
						}else{
							$json_arr=json_decode($zhi['vd26'][$bar][$xm_vid],true);
							$json_arr[$fl_xm_vid]=$value;
						}
						$zhi['vd26'][$bar][$xm_vid]=JSON($json_arr);
						$zhi['vd27'][$bar][$fl_xm_vid]=$value;
						$zhi['vd27'][$bar][$xm_vid]=sc_to_num(array_sum($json_arr));
					}else{
						$zhi['vd27'][$bar][$xm_vid]=$value;
					}
					$quzhi_zt='stop';
					$cishu=0;
				}
			}
		}
	}
}
return $zhi;
?>
