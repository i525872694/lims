<?php
/**
* 功能：全局变量定义文件
* 作者：Mr Zhou
* 日期：2014-04-16
* 描述：将系统中使用的全局变量定义在$global数组中
*/
//是否开始验收模块
include_once "dhy_hyd.php";
require_once $rootdir.'/temp/check_sql.php';
$duijie_url = 'http://121.42.140.28/yunnan/';//龙慧对接服务器处理地址
$technicalemail			= '1349142355@qq.com,534063009@qq.com';
$yanshou_peizhi = "有验收";//为了防止后续变量覆盖后出错，判断值给定为中文：有验收，没有验收；
$yp_status_xinxi = array("清澈透明","浑浊","沉淀","气泡","其他","包装完好","包装破损");
$yp_status_xinxi1 = array("清澈透明","浑浊","沉淀","气泡","其他");//采样记录表里的
//页面初始化 在变量$trade_global中声明导航和需要引入的css和js文件等其他信息
$fzx_id=$u['fzx_id'];
$gx_jingdu='120.387522';
$gx_weidu='36.106301';
$global	= $trade_global		= array();
$trade_global['daohang']	= array(
	array(	'icon'	=> 'icon-home home-icon',
			'html'	=> '首页',
			'href'	=> 'index.php',
			'target'=>'_parent'
		)
);
$trade_global['u']	= $u;
$trade_global['rooturl'] = $rooturl;
global $global,$trade_global;
//将系统中使用的全局变量定义在$global数组中
$global = array(
	'version'	=> '2.0',//系统版本号,
	'firm_type'	=> 'sw',//企业类型（自来水"zls"，水文"sw"）
	'pdf_file_way'=>'/home/files/',
	'load_way'	=> array(1=>'pdf',2=>'excel',3=>'txt'),//载入方式
	'rq_size'	=> array('50mL','100mL','200mL','250mL','300mL','400mL','500mL','1L','2L','2.5L','5L'),//容器规格
	'unit'		=> array('mg/L','μg/L','℃','CFU/mL','CFU/100mL','CFU/L','MPN/mL','MPN/100mL','MPN/L','万个/L','个/mL','个/100mL','个/L','个/10L','NTU','级','μS/cm','Bq/L','%','g/cm³','mg/m³','mg/g','mg/kg','度','cm','m','m³','m³/秒','无量纲'),//项目单位
	'site_type'	=> array(0=>'新版下达任务',1=>'常规任务',2=>'内部任务',3=>'委托任务'),//站点类别//总中心的 站点类别在下面
	'cy_flag_site_type'=>array('3'),//采样单位需要默认为委托单位的 任务类型
	//'tjcs'		=> array(0=>'省界',1=>'重点',2=>'水源地',3=>'排污口',4=>'地下水'),//统计参数
	'pzzd'		=> array(0=>'',1=>'',2=>'',3=>'',4=>''),//配置站点显示参数
	'bar_code'	=> array(
				'site_type'	=> array(0=>'J',1=>'C',2=>'L',3=>'W'),//任务类型对应的样品编号标识
				'water_type'	=> array(1=>'B',3=>'X',5=>'S',7=>'F',55=>'W',70=>'T',73=>'K'),//水样类型对应的样品编号标识
				'water_type_barcode'	=> '0'//不同水样类型（大类） 是否分开编号1是0否
				),
	'jcrw_list' =>array(
					'fp_csrw',	//'修改测试任务'
					// 'rwjs',		//'任务接收单'
					// 'yply',		//'样品领用单'
					'pay_list',	//'查看化验单'
				),
	'hyd'		=>array(
				 'v'=> '2.2.4' //在hyd.js和hyd.css更新时增加版本号防止缓存
				,'danwei'=> 'yunnan'
				,'wendu' => '20'//化验单表头默认温度
				,'shidu' => '50'//化验单表头默认湿度
				,'plan_file_path'=>'hyd/plan/yunnan/'//化验单模板文件路径
				,'sign_can_same' => true//签字是否允许相同
				,'hide_sign_date'=>false
				,'clear_sign_date'=>false
				,'yq_bh'=>'yq_neibubh'
				,'tuihui'=>array(
					'clear_sign_date'=>false
				)
				,'sh_set'=> array(
						'02'=>array('jh','校核','v1'),
						'03'=>array('fh','复核','v2'),
						'04'=>array('sh','审核','v3')
					)//审核设置
				,'sh_config'=> array(
						'jh'=>array('v1','校核','已完成','02'),
						'fh'=>array('v2','复核','已校核','03'),
						'sh'=>array('v3','审核','已复核','04')
					)
				,'code_jiema'=> array(
						'is_jiema' => '1',	//站点是否解码  0不解码|1解码
						'sign' => 'sign_02'	//哪一级解码	sign_01|sign_02|sign_03|sign_04
					)
				,'jcxm_set_mr_lx' => '5'
				,'check_jcx_with_value' => 'vd0' //使用哪个值进行检出限判断，_vd0表示先判定检出限再修约，vd0表示先修约再判定
				,'sc_check_CT' => 'shuang_bian' //曲线T值检验,分为双边和单边,默认单边
				),
	'zk'		=> array(
				'zk_set_qx'=>'zk_set' //质控范围设置权限,is_zz只有总站能设置,zk_set分中心具有质控设置权限的就可以修改
				,'zhikong'=>array('zky_name'=>'自控样','sc_need_zky'=>false,'has_zk7'=>false,'02C08C'=>false)//针对zhikong.js做的配置
				),
	'status'	=> array(
				'0'	=> '采样任务未确认',
                '1'	=> '采样任务已下达',
                '2'	=> '采样任务已接受',
                '3'	=> '已采样',
                '4'	=> '样品已审核',
                '5'	=> '样品已接收',
                '6'	=> '检测任务已下达',
                '7'	=> '已完成化验',
                '8'	=> '报告已签发',
				),
	'duijie'	=> '1',//是否需要与其他系统对接 1为需要，0为不需要
	'site_line'	=> array('1'=>'左','2'=>'中','3'=>'右'),
	'site_vertical'	=> array('1'=>'上','2'=>'中','3'=>'下'),
	//气温AIRT、气压ATM、水位Z、流量Q、风速WNDV、风向WNDDIR、天气WTH、流速FLWV、风力WNDPWR
	'xc_huanjing' => array(
		'气温' => 'AIRT',
		'气压' => 'ATM',
		'流量' => 'Q',
		'风速' => 'WNDV',
		'风向' => 'WNDDIR',
		'天气' => 'WTH',
		'流速' => 'FLWV',
		'水位' => 'Z',
	),
	'cy_record_bt'=>array(
					'moren'=>array(
						'取样<br/>方式'	=>'cy_way',
						'采样工具'		=>'cy_tool',
						'左岸'			=>'zuo_an',
						'中弘'			=>'zhong_hong',
						'右岸'			=>'you_an',
						'采样深'		=>'cy_ms',
						'水面宽'		=>'water_width',
						'天气'			=>'tian_qi',
						'气温<br/>(℃)'	=>'qi_wen',
						'气压<br/>(kPa)'=>'qi_ya',
						'水位<br/>(m)'	=>'water_height',
						'流量<br/>(m³/s)'=>'liu_l',
						'风向<br/>(o)'=>'feng_xiang',
						'风速<br/>(m/s)'=>'feng_su',
						'污染现象观察'	=>'dmwrxxjsm',
						'采样数量(瓶)'	=>'ping',
						'采样数量(桶)'	=>'tong',
						),
					),
	'cy_record_bt_order'=>array(
						'埋深<br/>(m)'=>'cy_ms',
						'采样<br/>方式'=>'cy_way',
						'天气'=>'tian_qi',
						'气温<br/>(℃)'=>'qi_wen',
						'水位<br/>(m)'=>'water_height',
						'流量/蓄水量<br/>(m³/s/亿m³)'=>'liu_l',
						'流速<br/>(m/s)'=>'liu_s',
						'感官指标'=>'gg_zb'
					),
	'cy_cx'            =>array('左','中','右'),
	'cy_way'           =>array('桥上','船上','岸边','涉水','冰上'),
	'cy_tool'          =>array('有机玻璃采样器','塑料桶','不锈钢器皿'),
	'cy_wrxx'          =>array('无','垃圾','油污','浮萍'),
	'tian_qi'		   =>array('晴','多云','阴','雾','霾','雨','雪'),
	'xcjc_ymxs'        =>array('检测结果'),
	'shuiti_zhuangtai' =>array('清澈','较混浊','浑浊'),
	'shuiti_yanse'     =>array('无色','有色'),
	//不同水样类型对应采样记录表中的不同表头设置(1:地表水,3:地下水,5:生活饮用水,7:废污水)
	'not_need_zk'      =>array('38','39','49','50','56','58','73'),
	'snpx_flag'        =>array('20','21','23','60','61','63','25','65'),
	'jbhs_flag'        =>array('40','41','43','60','61','63','45','65'),
	'xcpx_flag'        =>array('5','25','45','65'),
	'qckb_flag'		   =>array('1','21','41','61'),
	'cgb_bt_cs'        =>array('水功能区号'=>'water_area_nums','水功能区名称'=>'water_area_name','站点名称'=>'site_name','采样日期'=>'cy_date','采样时间'=>'cy_time','水位'=>'water_height','流量/蓄水量'=>'liu_l','气温'=>'qi_wen'),
	//定义的项目如果检测值是0则显示为未检出//在这里将69 70这两个项目删掉如果需要可再添加上
	'modi_data_vids'=>array(1,2,3,6,569),
	'bg_pingjun'=>'1',
	'related_value'=>'|&121&187&186&198&|&173&174&103&|&120&119&118&114&|&100&117&|&100&103&|',//相关项目,在报告上关联显示//配置方式 “|&关联项目1&关联项目2&关联项目3&|另外一组关联项目|”
);
if($u['is_zz']){
	//0永远是监督任务，没有监督任务请空着
	$global['site_type']=array(1=>'常规任务',3=>'委托任务');
}
