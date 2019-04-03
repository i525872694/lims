/*
 * 文件名：zhikong.js
 * 功能：化验单质控操作
 * 作者: Mr Zhou
 * 日期: 2015-05-29 2017-07-04更新
 * 描述：实现对化验单添加删除，室内平行，添加删除修改加标，空白等质控操作
*/
(function($) {
	$.fn.extend({
		zhikong : function(spF, spread, assayPay, o, options) {
			// return false;
			$.fn.zhikong.set = $.extend({}, o, options);
			$.fn.get_span = function(orid){
				return $(this).find(".hydzk[data-orid='"+orid+"']");
			}
			$(this).zhikong_init(spF, spread, assayPay, $.fn.zhikong.set);
		},
		get_zk_button : function(action){
			var zky_name = $.fn.zhikong.set.zky_name;
			var zk_title = {
				'2':'添加空白','-2':'删除空白','22':'修改空白',
				'4':'添加'+zky_name,'-4':'删除'+zky_name,'44':'修改'+zky_name,
				// '7':'添加常规平行样','-7':'删除常规平行样',
                // '8':'添加0.2C和0.8C','-8':'删除0.2C和0.8C','88':'修改0.2C和0.8C',
				'20':'添加平行','-20':'删除平行',
				'40':'添加加标','-40':'删除加标','4040':'修改加标'
			}
            if( !$.fn.zhikong.set['has_zk7'] ){
                delete(zk_title['7']);
            }
            if( !$.fn.zhikong.set['02C08C'] ){
                delete(zk_title['8']);
            }
            $.fn.zhikong.set.zk_title = zk_title;
			return ( !zk_title[action] ) ? '' : 
				'<li><a class="localize order_button" data-action="'+action+'">'+zk_title[action]+'</a></li>';
		},
		zhikong_init : function(spF, spread, assayPay, o){
			o.form	= $(this);
			$.fn.zhikong.current_form = o;
			var has_zk7 = (o.form.find("input[name='has_zk7']").length) ? true : false;
			//在页面添加两个div存放 分别存放加标和自控样所需数据
			o.form.append('<div style="display:none" id="jiaBiaoData_'+o.tid+'">,,</div><div style="display:none" id="ziKong_'+o.tid+'">,,</div>');
			//质控操作按钮
			{
				this.id			= o.id;	//assay_order表id
				this.flag		= o.hy_flag;	//质控标识
				this.code 		= o.bar_code;	//样品编号
				this.tit=titleXc=weizhi=title2=weizhi1=weizhi2=titleH='';
				switch(this.flag){
					//没有做质控的原样
					case '0':/*正常原样*/
					case '1':/*全程空白*/
					case '3':/*质控样*/
					case '5':/*现场平行A样*/
					case '-6':/*现场平行B样*/
					{
						tit  = $(this).get_zk_button('20');//添加平行
						tit += $(this).get_zk_button('40');//添加加标
						tit += $(this).get_zk_button( '2');//添加空白
						tit += $(this).get_zk_button( '4');//添加自控样
						tit += $(this).get_zk_button( '8');//添加02C08C
						if(true == has_zk7){
							tit += $(this).get_zk_button('7');
						}
					}break;
					case '-7':/*常规平行样（不同稀释倍数的统一样品）*/
					{
						tit  = $(this).get_zk_button( '7');//删除常规平行样
						tit += $(this).get_zk_button('-7');//删除常规平行样
					}break;
					//做了室内平行的原样
					case '20':/*正常样做室内平行*/
					case '21':/*全程空白做室内平行*/
					case '23':/*质控样做室内平行*/
					case '25':/*现场平行A样做室内平行*/
					{
						tit  = $(this).get_zk_button('-20');//删除平行
						tit += $(this).get_zk_button( '40');//添加加标
						tit += $(this).get_zk_button(  '2');//添加空白
						tit += $(this).get_zk_button(  '4');//添加自控样
						tit += $(this).get_zk_button( '8');//添加02C08C
						if(true == has_zk7){
							tit += $(this).get_zk_button('7');
						}
					}break;
					//室内平行样
					case '-20':/*正常样之室内平行样*/
					case '-26':/*现场平行B样之室内平行*/
					{
						tit  = $(this).get_zk_button('-20');//删除平行
						// tit += $(this).get_zk_button( '40');//添加加标
						if(true == has_zk7){
							tit += $(this).get_zk_button('7');
						}
						//现场平行B样之室内平行
						if('-26' == this.flag)
						{
							//现场平行B样得判断是否做了室内平行
							//这个是现场平行B样 做了室内平行 将原样上添加平行的按钮替换为删除平行的按钮
							if(this.code.indexOf('P')>0)
							{
								var code  = this.code.replace('P','');//去除编号后面的“P”号
								//P_B 是指现场平行B样
								var P_B_obj	= o.form.find(".hydzk[data-code='"+code+"'][data-flag='-6']");//获取当前的id
								var titleXc	= P_B_obj.attr('title');//获取当前的title
								if(''!=titleXc)
								{
									var P_B_tit  = P_B_obj.get_zk_button('-20');//删除平行
									var weizhi 	 = titleXc.indexOf('</button>')+9;//9是向后移动 字符“</button>”所占的位置
									var title2   = P_B_tit+titleXc.substring(weizhi);//
									P_B_obj.attr('title',title2);//属性定义
								}
							}
						}
					}break;
					//做了加标的原样
					case '40':/*正常样做加标*/
					case '41':/*全程空白做加标*/
					case '43':/*质控样做加标*/
					case '45':/*现场平行A样做加标*/
					{
						tit  = $(this).get_zk_button( '20');//添加平行
						tit += $(this).get_zk_button('-40');//删除加标
						tit += $(this).get_zk_button(  '2');//添加空白
					}break;
					//加标样
					case '-40':/*正常样之加标样*/
					case '-42':/*室内空白之加标样*/
					case '-46':/*现场平行B样之加标样*/
					case '-60':/*室内平行B之加标样*/
					case '-66':/*现场平行B样室内平行B样之加标样*/
					{
						this.vd28	= o.vd28 ? o.vd28 : '';	//水体积	vd28
						this.vd29	= o.vd29 ? o.vd29 : '';	//标样		vd29
						this.vd30	= o.vd30 ? o.vd30 : '';	//加标量	vd30
						this.vd31	= o.vd31 ? o.vd31 : '';	//标样单位	vd31
						this.vd32	= o.vd32 ? o.vd32 : '';	//加标量单位vd32
						$("#jiaBiaoData_"+this.tid).html(this.vd28+','+this.vd29+','+this.vd30);	//加入默认值

						tit  = $(this).get_zk_button( '-40');//删除加标
						tit += $(this).get_zk_button('4040');//修改加标
						//tit += '原水样体积：'+this.vd28+'mL，&nbsp;标液浓度：'+this.vd29+this.vd31+'，&nbsp;加标量：'+this.vd30+this.vd32+'&nbsp;';
						//
						var code  = this.code.substring(0,this.code.length-1);
						if('-46' == this.flag){
							//现场平行B样之加标样，需要将现场平行B样(-6)的'添加加标'按钮改为'删除加标'
							var hy_flag	= -6;
						}else if('-60' == this.flag){
							//室内平行B样之加标样，需要将室内平行B样(-20)的'添加加标'按钮改为'删除加标'
							var hy_flag	= -20;
						}else if('-66' == this.flag){
							//现场平行B样之室内平行B样的加标样，需要将现场平行B样之室内平行B样(-26)的'添加加标'按钮改为'删除加标'
							var hy_flag	= -26;
						}else{
							var hy_flag = o.form.find(".hydzk[data-code='"+this.code+"']").attr('data-flag');
						}
						var titleXc  = o.form.find(".hydzk[data-code='"+this.code+"'][data-flag='"+hy_flag+"']").attr('title');
						if(titleXc){
							var titleTemp= titleXc.split('</button>');
							var title2   = titleTemp[0]+'</button>'+ $(this).get_zk_button('-40');
							for(var i=2;i<titleTemp.length;i++){
								title2   += titleTemp[i]+'</button>';
							}
							o.form.find(".hydzk[data-code='"+this.code+"']").attr('title',title2);
						}
					}break;
					//室内平行和加标都做的原样
					case '60':/*正常样做室内平行+加标*/
					case '61':/*全程空白做室内平行+加标*/
					case '63':/*质控样做室内平行+加标*/
					case '65':/*现场平行A样做室内平行+加标*/
					{
						tit  = $(this).get_zk_button('-20');	//删除平行
						tit += $(this).get_zk_button('-40');	//删除加标
						tit += $(this).get_zk_button(  '2');	//添加空白
						tit += $(this).get_zk_button(  '4');	//添加自控样
						tit += $(this).get_zk_button(  '8');	//添加02C08C
					}break;
					case '-2':		//室内空白
					{
						this.vd28	= o.vd28 ? o.vd28 : '';	//信号值	vd28
						tit  = $(this).get_zk_button('-2');	//删除空白
						tit += $(this).get_zk_button('22');	//修改空白
						tit += $(this).get_zk_button('40');	//添加加标
						//tit += '信号值：'+this.vd28
					}break;
					case '-4':
					case '-8':
						this.vd28	= o.vd28 ? o.vd28 : '';	//批号			vd28
						this.vd29	= o.vd29 ? o.vd29 : '';	//标准值		vd29
						this.vd30	= o.vd30 ? o.vd30 : '';	//不确定度		vd30
						this.vd31	= o.vd31 ? o.vd31 : '';	//标液单位		vd31
						this.vd32	= o.vd32 ? o.vd32 : '';	//不确定度单位	vd32
						$("#ziKong_"+this.tid).html(this.vd28+','+this.vd29+','+this.vd30);			//加入默认值
						if( '-4' == this.flag ){
							tit   = $(this).get_zk_button('-4'); //删除自控样
							tit  += $(this).get_zk_button('44'); //修改自控样
						}else{
							tit   = $(this).get_zk_button('-8'); //删除0.2C0.8C
							tit  += $(this).get_zk_button('88'); //修改0.2C0.8C
						}
						break;
					default:
						tit  = '样品标识有误';
				}
				$("#spreadContextMenu").html(tit);
			}//End
			//为质控按钮绑定事件
			//普通质控操作
			var action_select = [
				".order_button[data-action='-2' ]",	//删除空白
				".order_button[data-action='-4' ]",	//删除自控样
				".order_button[data-action='7'  ]",	//添加常规平行样
				".order_button[data-action='-7' ]",	//删除常规平行样
                ".order_button[data-action='-8' ]", //删除0.2C和0.8C
				".order_button[data-action='20' ]",	//添加平行
				".order_button[data-action='-20']",	//删除平行
				".order_button[data-action='-40']"];//删除加标
			$(action_select.join(",")).click(function(){
				var action = $(this).attr('data-action');
				//如果type小于1证明执行的是删除操作 做删除提示
				if(action < 1 && !confirm("确定要删除吗？")){
					return false;
				}
				var zhikong_action = {
					id: o.id,
					action:action,
					flag:o.hy_flag
				};
				var sheet = spread.getActiveSheet();
				sheet.setBindingPath(assayPay.startRow, 1, "headerData.zhikong_action");
				sheet.setValue(assayPay.startRow, 1, JSON.stringify(zhikong_action));
				$(spF.saveButtonId).trigger("click");
			});
			//空白的添加及修改
			$(".order_button[data-action='2'],.order_button[data-action='22']").click(function(){
				var orid	= o.id;
				var action	= $(this).attr('data-action');
				var flag	= o.hy_flag;
				var xhz		= o.vd28;
				var data_type = "kongbai";
				var modal = $("#modal_zhikong");
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				modal.find(".zhikong_title").html("添加空白");
				if(''!=xhz&&undefined!=xhz){
					modal.find("[name='xhz']").val(xhz);
				}
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			//自控样添加及修改
			$( [".order_button[data-action='4']",
                ".order_button[data-action='44']",
                ".order_button[data-action='8']",
                ".order_button[data-action='88']"
                ].join(",")).click(function(){
				var orid	= o.id;
				var action	= $(this).attr('data-action');
				var flag	= o.hy_flag;
				var piHao	= o.vd28;
				var bzz		= o.vd29;
				var bqdd	= o.vd30;
				var bzzdw	= o.vd31;
				var bqdddw	= o.vd32;
				var bar_code = o.bar_code;

				var data_type = "zky";
				var modal = $("#modal_zhikong");
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				modal.find(".zky_addNum,.zky_editAll").addClass("hide");
				if($.inArray(action, ['44', '88']) >= 0){
					modal.find("[name='piHao']").val(piHao);
					modal.find("[name='bzz']").val(bzz);
					modal.find("[name='bqdd']").val(bqdd);
                    if('44' == action){
                        modal.find(".zky_editAll").removeClass("hide");
                    }
					modal.find(".zhikong_title").html(bar_code+"修改");
					modal.find("[name='bzzdw'] [value='"+bzzdw+"']").attr('selected',true);
					modal.find("[name='bqdddw'] [value='"+bqdddw+"']").attr('selected',true);
				}else if(orid!=modal.find("[name='data-orid']").val()){
					var zky_data = $("#ziKong_"+o.tid).html().split(',');
					modal.find("[name='piHao']").val(zky_data[0]);
					modal.find("[name='bzz']").val(zky_data[1]);
					modal.find("[name='bqdd']").val(zky_data[2]);
				}
				if($.inArray(action, ['4', '8']) >= 0){
                    // 自控样执行批量添加，0.2C和0.8C不执行批量操作
                    if('4' == action){
                        modal.find(".zky_addNum").removeClass("hide");
                    }
					modal.find(".zhikong_title").html($.fn.zhikong.set.zk_title[action]);
				}
				modal.find("[name='flag']").val(flag);
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			//加标的添加及修改
			$(".order_button[data-action='40'],.order_button[data-action='4040']").click(function(){
				var orid	= o.id;
				var action	= $(this).attr('data-action');
				var flag	= o.hy_flag;
				var qyv		= o.vd28;
				var byc		= o.vd29;
				var byv		= o.vd30;
				var bycdw	= o.vd31;
				var byvdw	= o.vd32;
				var bar_code = o.bar_code;

				var data_type = "jiabiao";
				var modal = $("#modal_zhikong");
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				if('4040'==$(this).attr('data-action')){
					modal.find("[name='qyv']").val(qyv);
					modal.find("[name='byc']").val(byc);
					modal.find("[name='byv']").val(byv);
					modal.find(".zhikong_title").html(bar_code+"修改");
					modal.find("[name='bycdw'] [value='"+bycdw+"']").attr('selected',true);
					modal.find("[name='byvdw'] [value='"+byvdw+"']").attr('selected',true);
				}else if(orid!=modal.find("[name='data-orid']").val()){
					var jia_data = $("#jiaBiaoData_"+o.tid).html().split(',');
					modal.find("[name='qyv']").val(jia_data[0]);
					modal.find("[name='byc']").val(jia_data[1]);
					modal.find("[name='byv']").val(jia_data[2]);
				}
				if('40'==$(this).attr('data-action')){
					modal.find(".zhikong_title").html(bar_code+"添加加标");
				}
				modal.find("[name='flag']").val(flag);
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			$("#zhikong_modal_submit").unbind("click").click(function(){
				var empty_error = false;
				var modal = $("#modal_zhikong");
				var tid = modal.find("[name='tid']").val();
				modal.i=function(name){
					return $(this).find("[name='"+name+"']");
				};
				modal.v=function(name){
					return $(this).find("[name='"+name+"']").val();
				};
				var FormError = modal.find(".ui-state-error");
				var data_type = modal.i("data-type").val();
				var action_url = 'id='+modal.v("data-orid")+'&action='+modal.v('action');
				if('kongbai'==data_type){
					if('NULL'==modal.v("xhz")){
						empty_error = true;
						modal.i("xhz").select();
						FormError.html(modal.i("xhz").parent("div").prev("div").html()+'不能为空').show();
					}else{
						action_url += "&vd28="+modal.v("xhz");
					}
				}else if('zky'==data_type){
					/*if(''==modal.v("bzz")){
						empty_error = true;
						modal.i("bzz").select();
						FormError.html(modal.i("bzz").parent("div").prev("div").html()+'不能为空').show();
					}else if(''==modal.v("bqdd")){
						empty_error = true;
						modal.i("bqdd").select();
						FormError.html(modal.i("bqdd").parent("div").prev("div").html()+'不能为空').show();
					}else*/{
						action_url += '&addZkyGs='+modal.v('addZkyGs')+'&add_all='+modal.find('[name=add_all]:checked').length+'&piHao='+modal.v('piHao')+'&biaoZhunZhi='+modal.v('bzz')+'&buQueDingDu='+modal.v('bqdd')+'&vd31='+modal.v('bzzdw')+'&vd32='+modal.v('bqdddw');
					}
				}else if('jiabiao'==data_type){
					if(''==modal.v("qyv")){
						empty_error = true;
						modal.i("qyv").select();
						FormError.html(modal.i("qyv").parent("div").prev("div").html()+'不能为空').show();
					}else if(''==modal.v("byc")){
						empty_error = true;
						modal.i("byc").select();
						FormError.html(modal.i("byc").parent("div").prev("div").html()+'不能为空').show();
					}else if(''==modal.v("byv")){
						empty_error = true;
						modal.i("byv").select();
						FormError.html(modal.i("byv").parent("div").prev("div").html()+'不能为空').show();
					}else{
						action_url += '&flag='+modal.v('flag')+'&vd28='+modal.v('qyv')+'&vd29='+modal.v('byc')+'&vd30='+modal.v('byv')+'&vd31='+modal.v('bycdw')+'&vd32='+modal.v('byvdw');
					}
				}else{
					empty_error==true;
					FormError.html('参数错误，请刷新页面后重试');
				}
				if(empty_error==true){return false;}

				var zhikong_action = {};
				var url = action_url.split('&');
				for(var i = 0;i < url.length; i++){
					var val = url[i].split('=');
					zhikong_action[val[0]] = val[1];
				}
				var sheet = spread.getActiveSheet();
				sheet.setBindingPath(assayPay.startRow, 1, "headerData.zhikong_action");
				sheet.setValue(assayPay.startRow, 1, JSON.stringify(zhikong_action));
				$(spF.saveButtonId).trigger("click");
				modal.modal("hide");
			})
		}
	});
	$.fn.zhikong.set = {
		zky_name : "自控样",	//自控样|单点标液
		sc_need_zky : false,	//分光法是否需要自控样
	};
})(jQuery);