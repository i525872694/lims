<?php
/**
 * 功能：汇总报告预览查看下载程序
 * 作者：罗磊
 * 日期：2014-05-30
 * 描述：
*/
include '../temp/config.php';
include INC_DIR . "cy_func.php";
include('bg_func.php');
if(empty($u['userid'])){
	nologin();
}

$fzx_id=$u['fzx_id'];
$bgbh  = date("Y-m-d");	//报告编号
if(!empty($_POST['cids'])&&!empty($_POST['cyd_id'])){
	$cydid=$_POST['cyd_id'];
	$rec_id_str=implode(",",$_POST['cids']);
	if($_POST['view_hz']||$_POST['view_e_hz']){
		$lx=1;//查看预览标识
	}elseif($_POST['load_word_hz']||$_POST['load_e_word_hz']){
		$lx=2;//下载word标识
	}else{
		$lx=3;//下载excel标识
	}
	if($_POST['view_e_hz']||$_POST['load_e_word_hz']){
		$is_eglish=1;//$is_eglish为1是英文报告，0是中文报告
	}
}
//print_rr($_POST);exit();

//查询下化验单数据在什么状态下能显示到报告上
$show_shuju_arr	= array();
$show_shuju_old	= $DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE `module_name`='show_shuju' ORDER BY id DESC LIMIT 1");
if(!empty($show_shuju_old['module_value1'])){
	$show_shuju_arr	= explode(",",$show_shuju_old['module_value1']);
}
//查询设置的报告项目
$bg_xm_arrs= array();
$report_sql="SELECT cy_rec_id,bg_xm,json FROM report WHERE cyd_id='".$cydid."' AND cy_rec_id IN (".$rec_id_str.")";
$report_query=$DB->query($report_sql);
while($report_rs=$DB->fetch_assoc($report_query)){
	if(!empty($report_rs['bg_xm'])){
		$bg_xm_arrs[$report_rs['cy_rec_id']]=explode(',',$report_rs['bg_xm']);
	}else{
		$bg_xm_arrs[$report_rs['cy_rec_id']]=array();
	}
}
//查询报告信息页的内容
$detail_rs=$DB->fetch_one_assoc("SELECT * FROM report WHERE cyd_id='".$cydid."' AND cy_rec_id IN (".$rec_id_str.")");
$report_json	= array();
if(!empty($detail_rs['json'])){
	$report_json	= json_decode($detail_rs['json'],true);
}
//print_rr($detail_rs);
//报告编号
$nian=$detail_rs['year'];
if(empty($nian)){
	$nian="<span style=\"padding-left:40px\"></span>";
}
if(!empty($detail_rs['bg_bh'])&&!empty($detail_rs['bg_lx'])){
	$bh=$detail_rs['bg_lx'].get_bgbh($detail_rs['bg_bh']);
} 
if(empty($bh)){
	$bh="<span style=\"padding-left:40px\"></span>";
}
//批准签发人职位
$eglish_position_arr=array('&nbsp;&nbsp;&nbsp;&nbsp;负责人'=>'&nbsp;&nbsp;&nbsp;&nbsp; Director','技术负责人'=>'Technical Director','质量负责人'=>'Quality Director');
if(empty($detail_rs['qf_user_position'])){
	$qf_user_position='&nbsp;&nbsp;&nbsp;&nbsp;负责人';
}else{
	$qf_user_position=$detail_rs['qf_user_position'];
}
//报告打印日期
if($detail_rs['bg_date']!=''&&$detail_rs['bg_date']!='0000-00-00'){
	if($is_eglish){
		$bgrq=date('jS F Y',strtotime($detail_rs['bg_date']));
	}else{
		$bgrq=date('Y年m月d日',strtotime($detail_rs['bg_date']));
	}
}

//中文报告和英文报告分别对应不同的模板路径
if($is_eglish){
	$qf_user_position=$eglish_position_arr[$qf_user_position];
	$add_dir='bg/eglish_bg/';
}else{
	//$add_dir='bg/';
}


//查询汇总报告模板的信息
$mbrows=$DB->fetch_one_assoc("SELECT * FROM report_template WHERE water_type='0' AND is_eglish='0'");
//print_rr($mbrows);

$fm_mb = $add_dir.$mbrows['fm_mb'];        //  封面模板
$sm_mb = $add_dir.$mbrows['sm_mb'];       //  说明模板
$one_page_mb = $add_dir.$mbrows['one_page_mb']; //  报告检测数据模版第一页
$two_page_mb = $add_dir.$mbrows['two_page_mb']; //  报告检测数据模版第二页
$sj_mb = $add_dir.$mbrows['sj_line_mb'];     //  数据页模板
$qm_mb = $add_dir.$mbrows['qm_mb'];     //  签名模板

if(empty($mbrows['hang1'])){
	$hang1=20;
}else{
	$hang1=$mbrows['hang1'];
}
if(empty($mbrows['hang2'])){
	$hang2=28;
}else{
	$hang2=$mbrows['hang2'];
}
if($lx == ''){
	$lx =1;
}//默认为预览
if($lx==2){
	$fenye="<br  style=\"page-break-before:always\">";
}else{
	$fenye="<div style=\"page-break-before: always\"></div>";
}

//定义检测报告的高度
$table_height='min-height:26cm';

// 查询中水质名称
$bzlx_query=$DB->query("SELECT * FROM leixing WHERE (fzx_id='".$fzx_id."' OR fzx_id='0')");
while($bzlx_rs=$DB->fetch_assoc($bzlx_query)){
	if($is_eglish){
		$szlx_arr[$bzlx_rs['id']]=$bzlx_rs['e_lname'];
	}else{
		$szlx_arr[$bzlx_rs['id']]=$bzlx_rs['lname'];
	}
}
$sql_rep="SELECT * FROM report WHERE cyd_id='".$cydid."' AND cy_rec_id in (".$rec_id_str.")";
$query_rep=$DB->query($sql_rep);
$szlx_name_arr=$yplb_arr=array();
while($rs_rep=$DB->fetch_assoc($query_rep)){
	$yplb_arr[$rs_rep['cy_rec_id']]=$szlx_arr[$rs_rep['water_type']];
	if(!in_array($szlx_arr[$rs_rep['water_type']],$szlx_name_arr)){
		$szlx_name_arr[]=$szlx_arr[$rs_rep['water_type']];
	}
}
$szlx=implode('、',$szlx_name_arr);

$jc_date=$fx_date=array();
if($lx == 2){
	header("Content-Type:   application/msword");        
	header("Content-Disposition:   attachment;   filename=汇总报告.doc");        
	header("Pragma:   no-cache");        
	header("Expires:   0");  

	}
if($lx == 3 ){
	header("Content-Type:   application/msexcel");        
	header("Content-Disposition:   attachment;   filename=汇总报告.xls");        
	header("Pragma:   no-cache");        
	header("Expires:   0");   

}

//查询检测标准
$jcbz_sql="SELECT n.module_value1,n.module_value2,aj.* FROM n_set n JOIN assay_jcbz aj ON n.id=aj.jcbz_bh_id WHERE module_name='jcbz_bh' AND module_value3='1' ";
$jcbz_query=$DB->query($jcbz_sql);
while($jcbz_rs=$DB->fetch_assoc($jcbz_query)){
	if(empty($jcbz_arr[$jcbz_rs['module_value2']]['zxbz'])){
		$jcbz_arr[$jcbz_rs['module_value2']]['zxbz']=$jcbz_rs['module_value1'];
	}
	if(!empty($jcbz_rs['panduanyiju'])){
		$pdyj_arr=json_decode($jcbz_rs['panduanyiju'],true);
		if(!empty($pdyj_arr)){
			$pd_jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]	= $pdyj_arr;
			/*if($pdyj_arr[$lx_id]){
				$pd_jcbz_arr[$jcbz_rs['vid']]=$pdyj_arr[$lx_id];
			}else{
				$pd_jcbz_arr[$jcbz_rs['vid']]=$pdyj_arr[$pd_water_type];
			}*/
		}else{
			$pd_jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]	= $jcbz_rs['panduanyiju'];
		}
		//$pd_jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]=$jcbz_rs['panduanyiju'];
	}else{
		$pd_jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
	if($is_eglish&&!empty($jcbz_rs['eglish_xz'])){
		$jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]=$jcbz_rs['eglish_xz'];
	}else{
		$jcbz_arr[$jcbz_rs['module_value2']]['jcxz'][$jcbz_rs['vid']]=$jcbz_rs['xz'];
	}
}
//print_rr($jcbz_arr);
//查询检测单位信息
$jcdw =$DB->fetch_one_assoc("SELECT * FROM hub_info WHERE id = '".$fzx_id."'");

//查询委托单位
$cyd=get_cyd($cydid);
$wtdw  = str_replace("\n","<br/>",$cyd['cy_dept']); //委托单位


$bgnr .="<html><head><style type=\"text/css\">body{margin:0px} .td_border td{border:1px solid}.hidden_pingjia{display:none;}</style></head><body>";//去空白
$bgnr .="<div style=\"background-color: #000000;padding-top:50px;padding-bottom:50px\">";

if($lx != 3){
	$bgnr .= temp($fm_mb);       //报告封面模板
	$bgnr.=$fenye;
}
$z_page=0;
$rec_sql = "SELECT id,sid FROM cy_rec WHERE cyd_id = '".$cydid."' AND id IN (".$rec_id_str.") AND sid>'0' AND zk_flag>='0'";
$rec_query = $DB->query($rec_sql);
while($row=$DB->fetch_assoc($rec_query)){
	$arr=array();
	$cyid = $row['id'];        
	$ssid = $row['sid'];
	//$order_sql	= "SELECT ao.site_name,ao.bar_code,ao.hy_flag,ao.ping_jun,ao.create_date,ao.sid,ao.vid,ao.tid,ao.vd0,ap.assay_element,ap.td2,ap.td3,ap.td4,ap.td5,ap.td32,ap.td33,ap.over,ap.create_date as ap_create_date,ap.unit,ap.sign_01,ap.sign_date_01,ap.sign_date_03,ap.sign_date_04,ap.jc_xz,ap.time_01 FROM assay_order as ao JOIN assay_pay as ap on  ao.cyd_id=ap.cyd_id AND ao.tid=ap.id WHERE ap.cyd_id='".$cydid."' AND ao.cid='".$cyid."' AND ao.hy_flag>='0' AND ao.sid > '0'";//现场平行样的报告也需要显示
	$order_sql	= "SELECT s.syxz,ao.site_name,ao.bar_code,ao.hy_flag,ao.ping_jun,ao.create_date,ao.sid,ao.vid,ao.tid,ao.vd0,ap.assay_element,ap.td2,ap.td3,ap.td4,ap.td5,ap.td32,ap.td33,ap.over,ap.create_date as ap_create_date,ap.unit,ap.sign_01,ap.sign_date_01,ap.sign_date_03,ap.sign_date_04,ap.jc_xz,ap.time_01,ap.is_xcjc,ao.cyd_id FROM `sites` AS s RIGHT JOIN assay_order as ao ON s.id=ao.sid LEFT JOIN assay_pay as ap ON  ao.tid=ap.id  WHERE ao.cyd_id='".$cydid."' AND ao.cid='".$cyid."' AND ao.hy_flag>='0' AND ao.sid > '0'";
	$order_query=$DB->query($order_sql);//查询assay_order和assay_pay表，得到报告上面需要的数据
	while($v=$DB->fetch_assoc($order_query)){
		$arr[$v['vid']]  = $v;
		if(!empty($arr[$v['vid']]['syxz'])){
			$arr[$v['vid']]['syxz']	= explode(',',$arr[$v['vid']]['syxz']);
		}
		if(!empty($bg_xm_arrs[$cyid])){
			if(!in_array($v['vid'],$bg_xm_arrs[$cyid])){
				unset($arr[$v['vid']]);
			}
		}
	}
	$vid_nums=count($arr);
	if($vid_nums==0){
		continue;
	}
	if($vid_nums<=$hang1&&$j==1){
		$z_page+=1;
	}else{
		$z_page+=ceil(($vid_nums-$hang1)/$hang2)+1;	
	}
}
$page=$ypsl=0;
$rec_query2 = $DB->query($rec_sql);
while ($row=$DB->fetch_assoc($rec_query2)){
	$xh++;
	$bgline = '';
	$cyid = $row['id'];        
	$ssid = $row['sid'];
	$arr ='';
	$detail_rs=$DB->fetch_one_assoc("SELECT * FROM report WHERE cyd_id='".$cydid."' AND cy_rec_id='".$cyid."'");
	$show_pingjia	= json_decode($detail_rs['json'],true);		//是否在最后一列显示 评价信息
	$pingjia_style	= '';
	$jielun_col		= '5';
	if($show_pingjia['show_pingjia']	== 'no'){
		$pingjia_style	= " class='hidden_pingjia' style='display:none;' ";
		$jielun_col		= '4';
	}
	if($detail_rs['sj_date']=='0000-00-00'){
		$detail_rs['sj_date']='';
	}else{
		if($is_eglish){
			$detail_rs['sj_date']=date('jS F Y',strtotime($detail_rs['sj_date']));
		}
	}
	if(empty($detail_rs['date_lx'])){
		if($is_eglish){
			$date_lx='Take sample date';
		}else{
			$date_lx='采样日期';
		}
	}else{
		$date_lx=$detail_rs['date_lx'];
	}
	$ywc = $wwc = 0;
	//$sql	= "SELECT ao.site_name,ao.bar_code,ao.hy_flag,ao.ping_jun,ao.create_date,ao.sid,ao.vid,ao.tid,ao.vd0,ap.assay_element,ap.td2,ap.td3,ap.td4,ap.td5,ap.td32,ap.td33,ap.over,ap.create_date as ap_create_date,ap.unit,ap.sign_01,ap.sign_date_04,ap.jc_xz,ap.time_01 FROM assay_order as ao JOIN assay_pay as ap on ao.cyd_id=ap.cyd_id	 AND ao.tid=ap.id WHERE ap.cyd_id='".$cydid."' AND ao.cid='".$cyid."' AND ao.hy_flag>='0' AND ao.sid > '0'";
	$sql	= "SELECT s.syxz,ao.site_name,ao.bar_code,ao.hy_flag,ao.ping_jun,ao.create_date,ao.sid,ao.vid,ao.tid,ao.vd0,ap.assay_element,ap.td2,ap.td3,ap.td4,ap.td5,ap.td32,ap.td33,ap.over,ap.create_date as ap_create_date,ap.unit,ap.sign_01,ap.sign_date_01,ap.sign_date_03,ap.sign_date_04,ap.jc_xz,ap.time_01,ap.is_xcjc,ao.cyd_id FROM `sites` AS s RIGHT JOIN assay_order as ao ON s.id=ao.sid LEFT JOIN assay_pay as ap ON  ao.tid=ap.id   WHERE ao.cyd_id='".$cydid."' AND ao.cid='".$cyid."' AND ao.hy_flag>='0' AND ao.sid > '0'";//现场平行样的报告也需要显示

	$vd=$DB->query($sql);//查询assay_order和assay_pay表，得到报告上面需要的数据
	while($v=$DB->fetch_assoc($vd)){
		$arr[$v['vid']]  = $v;
		if(!empty($arr[$v['vid']]['syxz'])){
			$arr[$v['vid']]['syxz']	= explode(',',$arr[$v['vid']]['syxz']);
		}
		if(!empty($bg_xm_arrs[$cyid])){
			if(!in_array($v['vid'],$bg_xm_arrs[$cyid])){
				unset($arr[$v['vid']]);
			}
		}
		if(!empty($v['td32'])){
				$wd[] 	     = $v['td32'];		  //温度	
		}
		if(!empty($v['td33'])){
				$sd[] 	     = $v['td33'];		  //湿度	
		}
		if(!empty($v['sign_date_01'])&&!in_array($v['sign_date_01'],$jc_date)){
			$jc_date[] = $v['sign_date_01'];//结束日期
		}
		if(!empty($v['time_01'])&&!in_array($v['time_01'],$fx_date)&&$v['time_01']!='0000-00-00'){
			$fx_date[] = $v['time_01'];//结束日期
		}
		$pay['over'] == $qzjb ? $ywc++ : $wwc++;			            //统计站点完成数量
		
	}
	$vid_nums=count($arr);
	$jcwd='';
	$jcrq='';
	$jcsd='';
	if(!empty($wd)){
		if(min($wd)==max($wd)){
			$jcwd = "温度（".min($wd).")°C";   //报告显示温度具体值 
		}else{
			$jcwd = "温度（".min($wd)."~".max($wd).")°C";   //报告显示温度区间  
		}
	}
	if(!empty($jc_date)&&!empty($fx_date)){
		if(min($fx_date)==max($jc_date)){
			$jcrq=date("Y年m月d日",strtotime(min($fx_date)));
		}else{
			$min_date=min($fx_date);
			$max_date=max($jc_date);
			$jcrq=date("Y年m月d日",strtotime($min_date))."～".date("Y年m月d日",strtotime($max_date));  // 检测日期区间
		}
	}
	if(!empty($sd)){
		if(min($sd)==max($sd)){
			$jcsd = "湿度（".min($sd).")";    //湿度值	
		}else{
			$jcsd = "湿度（".min($sd)."~".max($sd).")";    //湿度区间
		}
	}

	// 获得表头的数据
	$xc=$DB->fetch_one_assoc("SELECT cy.sh_user_qz_date,cy.yp_count,cy.fzx_id,cy.cy_dept,cy.jc_dept,cy.cy_date,cy.site_type,cy.cy_rwjs_qz_date,cy_rec.site_name,cy_rec.cy_note,cy_rec.water_type,cy_rec.bar_code as rec_bar_code FROM cy,cy_rec,sites  WHERE cy.id=cy_rec.cyd_id  and cy.id='".$cydid."' and cy_rec.sid='".$ssid."' and cy_rec.id='".$cyid."' ");

	if(empty($xc['water_type'])){
		$water_type_bh=substr($xc['jj'],1,1);
		$water_type=array_search($water_type_bh,$global['bar_code']['water_type']);
		$xc['water_type']=get_water_type_max($water_type,$fzx_id);
	}
	$yplb=$yplb_arr[$cyid];
	$ypzt=$detail_rs['yp_zt'];	           //样品状态			
	$cydw  = $xc['cy_dept'];               //采样单位
	$ypbh  = $xc['rec_bar_code'];          //样品编号
	$jssj  = $xc['sh_user_qz_date'];       //样品接收时间
	$ypmc  = $xc['site_name'];             //样品名称
	$cysj  = $xc['cy_date']; 			   //采样时间
	$ypsl++;   		   //采样样品数量
	
	//站点的水样类型
	if($detail_rs['water_type']){
		$lx_id=$detail_rs['water_type'];
	}else{
		$lx_id=$xc['water_type'];
	}

	//print_rr($xc);


	//查询报告所用模板

	$water_type_max=get_water_type_max($xc['water_type'],$fzx_id);
	//如果是英文模板查询项目的英文名称
	$e_item_arr=array();
	if($is_eglish){
		$e_item_sql="SELECT av.id,av.eglish_item,aj.eglish_item as aj_eglish_item  FROM assay_value av JOIN assay_jcbz aj ON av.id=aj.vid JOIN n_set n ON aj.jcbz_bh_id=n.id WHERE n.module_name='jcbz_bh' AND module_value3='1' AND module_value2='".$water_type_max."' ";
		$e_item_query=$DB->query($e_item_sql);
		while($e_item_rs=$DB->fetch_assoc($e_item_query)){
			if($e_item_rs['aj_eglish_item']){
				$e_item_arr[$e_item_rs['id']]=$e_item_rs['aj_eglish_item'];
			}else{
				$e_item_arr[$e_item_rs['id']]=$e_item_rs['eglish_item'];
			}
		}
		
	}
	//如果子类没有检测标准就执行父类的检测标准
	if(!empty($jcbz_arr[$xc['water_type']]['jcxz'])&&!empty($xc['water_type'])){
		$pd_water_type=$xc['water_type'];
	}else{
		$pd_water_type=$water_type_max;
	}
	$zxbz= $jcbz_arr[$pd_water_type]['zxbz'];//执行标准名称
	  
	//这里开始显示报告
	//print_rr($arr);exit();
	$page2=0;//每个站点的页数
	$i=0;
	$jcname_arr=array();
	$jcyq_arr=array();
	//进行项目排序
	$sql_px="SELECT module_value1 FROM n_set WHERE module_value3='".$lx_id."' AND module_value1!=''";
	$rs_px=$DB->fetch_one_assoc($sql_px);
	if(empty($rs_px)){
		$sql_px="SELECT module_value1 FROM n_set WHERE module_value3='".$water_type_max."' AND module_value1!=''";
		$rs_px=$DB->fetch_one_assoc($sql_px);
	}
	if(!empty($rs_px)){
		$xm_px_arr=explode(",",$rs_px['module_value1']);
		$arr_temp=array();
		foreach($xm_px_arr as $key=>$value){
			if(!empty($arr[$value])){
				$arr_temp[$value]=$arr[$value];
				unset($arr[$value]);
			}
		}
		$arr=$arr_temp+$arr;

	}
	foreach($arr as $key =>$value){
		$i++;
		$pd='';
		/*assay_pay表字段含义：assay_element(项目名称),td2(检测方法),td3(检出限),td4(仪器名称),td5(仪器编号),td32(温度),td33(湿度),create_date(开始检测	日期)sign_date_04(结束日期),unit(单位),sign_01(检测人员)*/	
		$xmid=$key; //项目的id
		$yiju   = $value['td2']; 	 //检测标准 
		$jcname = $value['sign_01']; //项目检测人员名称
		if(!in_array($jcname,$jcname_arr)&&!empty($jcname)){
			$jcname_arr[]=$jcname;
		}
		if($is_eglish&&!empty($e_item_arr[$xmid])){
			$xmname=$e_item_arr[$xmid];//英文项目名称
		}else{
			$xmname = $value['assay_element'];//项目名称
		}
		$unit   = $value['unit'];    //检测项目单位	
		$jcyq   = $value['td4'];     //检测仪器
		//$tid 只用作 查看化验单show_hyd函数传参使用
		if($value['is_xcjc'] == '1'){
			$tid	= "'cyd_id:{$value['cyd_id']}'";
		}else{
			$tid	= "'{$value['tid']}'";     //化验单id
		}
		if(!in_array($jcyq,$jcyq_arr)&&!empty($jcyq)){
			$jcyq_arr[]=$jcyq;
		}
		$jc_xz  = $jcbz_arr[$pd_water_type]['jcxz'][$xmid];//检测限值
		if(!empty($show_shuju_arr) && !in_array($value['over'],$show_shuju_arr)){
			$jie	= '';
		}else{
			//室内平行项目需要取平均值   
			if($value['ping_jun'] != ''&&$global['bg_pingjun']){
				$jie = $value['ping_jun'];//检测结果值
			}else{
				$jie = $value['vd0'];     //检测结果值
			} 
			if(@in_array($xmid,$global['modi_data_vids'])&&$jie<='0'&&$jie!=''){
				$jie='未检出';
			}
		}
		if($jc_xz==''||$jc_xz=='-'||$jc_xz=='--'){
			$jc_xz='--';	
		}
		if($jie!=''){
			if($jc_xz=='--'||preg_match("/^(-|－|—){1,}$/",$jie)){
				$pd='--';	
			}else{
				//$pdyj_arr=array();
				//$pdyj_arr=json_decode($pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid],true);
				//匹配标准限值
				$jcxz	= '';
				if(is_array($pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid])){
					foreach((array)$value['syxz']	as $value_syxz){
						//按照站点特殊限制区分
						if(!empty($pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$value_syxz])){
							$jcxz	= $pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$value_syxz];
							continue;
						}
					}
					//按照水样类型区分
					if(empty($jcxz)){
						if($pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$lx_id]){
							$jcxz	= $pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$detail_rs['water_type']];
						}else{
							if(!empty($pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$pd_water_type])){
								$jcxz	= $pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid][$pd_water_type];
							}else{
								$jcxz	= $pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid]['其他'];
							}
						}
					}
				}else{
					$jcxz	= $pd_jcbz_arr[$pd_water_type]['jcxz'][$xmid];
				}
				/*if(!empty($pdyj_arr)){
					if($pdyj_arr[$detail_rs['water_type']]){
						$pd_jcbz_arr_end[$pd_water_type]['jcxz'][$xmid]=$pdyj_arr[$detail_rs['water_type']];
					}else{
						$pd_jcbz_arr_end[$pd_water_type]['jcxz'][$xmid]=$pdyj_arr[$pd_water_type];
					}
				}*/
				//$pd_jcbz_arr_end[$pd_water_type]['jcxz'][$xmid]
				$return_data=is_chaobiao($xmid,$pd_water_type,$jcxz,$jie,$is_eglish);
				if($return_data['status']){
					$jie='<span style="color:red">'.$jie.'</span>';
					$pd='<span style="color:red">'.$return_data['info'].'</span>';
				}else{
					$pd=$return_data['info'];//合格判定
				}
			}
			if($jie=='未检出'&&$is_eglish){
				$jie='Not detected';
			}
			if($jie=='无'&&$is_eglish){
				$jie='No';
			}
		}
		$bgline.= temp($sj_mb);  //数据模版
		if($i==count($arr)){
			if($is_eglish){
				$bgline.="<tr style=\"font-family:Times New Roman;font-size:12pt;height:1.5cm\" align=\"center\"><td colspan=\"2\">Inspection<br/>conclusion</td><td colspan=\"$jielun_col\">".$detail_rs['jc_yj']."</td></tr>";
			}else{
				$bgline.="<tr style=\"font-family:宋体;font-size:12pt;height:1.5cm\" align=\"center\"><td colspan=\"2\">检验结论</td><td colspan=\"$jielun_col\">".$detail_rs['jc_yj']."</td></tr>";
			}
		}
		if(($i==count($arr)&&$i<$hang1) || $i==$hang1){
			$page++;
			$page2++;
			$bgnr.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';
			$bgnr.= temp($one_page_mb);
			if($page!=$z_page){

				$bgnr.="</div><div style=\"height:3px;width:19cm\"></div>";
				$bgnr.=$fenye;
			}
			$bgline = ""; 
		}
		if($i>$hang1&&($i==count($arr)||($i==$hang1+$hang2*$page2))){
			$page++;
			$page2++;
			$bgnr.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';	
			$bgnr.= temp($two_page_mb);
			if($page<$z_page){
				$bgnr.="</div><div style=\"height:3px;width:19cm\"></div>";
				$bgnr.=$fenye;
			}
			$bgline = "";

		}
		if($i==count($arr)&&$page==$z_page){
			if($page2>=2){
				$last_page_lines=$i-(($page2-2)*$hang2+$hang1);//最后一页的行数
			}else{
				$last_page_lines=$i;
			}
			$end_html="</div>";
			if($last_page_lines>=25){
				$bgnr.=$end_html.'<div style="height:3px;width:19cm"></div>'.$fenye;
				$bgnr.='<div style="background-color: #FFFFFF; width:20cm;margin:0 auto;'.$table_height.'" >';
				$bgnr.=temp($qm_mb);
			}else{
				$bgnr.=temp($qm_mb);
			}
			$bgnr.=$end_html.'<div style="height:3px;width:19cm"></div>'.$fenye;
		}

	}//循环模版结束

}
$bgnr .= temp($sm_mb);     //说明模板
$bgnr .="</div></body></html>";

echo $bgnr; 
  ?>



