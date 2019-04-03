/*
 *化验单插件 for jQuery
 *assay_form
 *assay_form_init
 *enter_next_input
 *enter_sheet_input
 *Copyright (c) 2015 Mr zhou (zhouguangli@anheng.com.cn)
*/
(typeof trade_global != "object") && (trade_global = {});
(function($) { "use strict";
	$.fn.extend({
		/**
		 * 功能：化验单调用插件
		 * 作者：Mr Zhou
		 * 日期：2010-10-10
		 * 功能描述：化验单调用插件
		*/
		assay_form2 : function(options) {
			if(typeof trade_global.assay_form == "undefined"){
				trade_global.assay_form = {};
			}
			var asF = $(this);
			asF.id = $(this).attr('data');
			asF.assay_form = $(this).find("form[name^=as_form_]");
			$(this).assay_form_init2($.extend({},asF, options));
		},
		/**
		 * 功能：化验单初始化
		 * 作者：Mr Zhou
		 * 日期：2018-10-10
		 * 功能描述：化验单初始化
		*/
		assay_form_init2 : function(asF){
			var u = trade_global.u;
			trade_global.assay_form[asF.id] = asF;
			//历史数据浮窗
			asF.find("[data-rel=popover]").popover({html:true});
			//如果data-over=over说明数据已签发，不能再修改
			asF.find("span[data-over=over]").parents("tr").find("input[type=text]").each(function(){$(this).replaceWith($(this).val());});
			//查看化验单
			$(".view_hyd_"+asF.id).unbind("click").click(function(){
				window.open(trade_global.rooturl+'/huayan/assay_form.php?tid='+asF.id, 'view_hyd');
			});//End
			//打印化验单
			$(".hyd_print_"+asF.id).unbind("click").click(function(){
				window.open(trade_global.rooturl+'/huayan/ahlims.php?app=print&act=print_hyd&ajax=1&tid='+asF.id);
			});//End
			//附件查看
			$(".pdf_files_"+asF.id).unbind("click").click(function(){
				var action_url = trade_global.rooturl+'/huayan/hydpdf.php?ajax=1&tid='+asF.id;
				$(this).qbox({title:'查看化验单的附件',src:action_url,w:700,h:400});
			});//End
			//是否隐藏签字日期
			if( typeof trade_global.hyd_config != "undefined" && typeof trade_global.hyd_config.hide_sign_date != "undefined"){
				if( true == trade_global.hyd_config.hide_sign_date ){
					asF.find(".assay_sign_form tr:last").hide();
				}
			}
			//如果具有化验单修改权限则显示仪器曲线列表，否则显示曲线表单
			asF.find(".yiqi_sc_"+asF.id).unbind("click").click(function(){
				if(!asF.canModi){
					if(asF.scid>0){
						view_sc_bd('quxian','view_sc',asF.scid);
					}else{
						alert_error("该化验单尚未关联任何曲线信息！");
					}
				}else{
					asF.change_qx('app=quxian&act=sel_sc&sc_type=2');
				}
			})//End
			//曲线切换和查看曲线函数
			asF.change_qx = function(){
				var act	= arguments[0] ? arguments[0]:'';
				var vid	= arguments[1] ? arguments[1]:'';
				if($("#sel_qx_by_"+asF.id).length==0){
					$("div[id^=sc_bd_box_]").remove();//防止曲线表单页面出现在曲线列表后面
					$("body").append('<div id="sel_qx_by_'+asF.id+'" class="modal fade" data-backdrop="static"></div>')
				}
				var sel_qx_by = $("#sel_qx_by_"+asF.id);
				var url = trade_global.rooturl+'/huayan/ahlims.php?id='+asF.scid+'&hyd_id='+asF.id+'&vid='+vid+'&hyd_vid='+asF.vid+'&bd_id='+asF.bdid+'&ajax=1';
				$.get(url+'&'+act,function(data){
					sel_qx_by.html(data);
					sel_qx_by.modal('show');
					var sc_type = sel_qx_by.find("input[name=sc_type]").val();
					var table_name = '';
					sel_qx_by.find("#sel_qx_ok").click(function(){
						var app = sel_qx_by.FN("app").val();
						var sc_bd = sel_qx_by.find("input[name=sc_bd]:checked").val();
						var goto_url = url+'&app='+app+'&act=related_to_hyd&sc_bd='+sc_bd;
						asF.assay_form.FN("goto_url").val(goto_url).submit();
						sel_qx_by.modal('hide');
					});
					//项目切换后更新列表
					sel_qx_by.FN("vid","s").change(function(){
						asF.change_qx(act,$(this).val());
					});
					//新建
					sel_qx_by.find(".create_sc").unbind("click").click(function(){
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						var vid = sel_qx_by.FN("vid","s").val();
						if(sc_type=='2'){
							view_sc_bd(app,act,0,'vid='+vid+'&table_name=sc_yq&sc_type='+sc_type+'&tid='+asF.id,sel_qx_by);
						}else{
							get_bzry_box(vid,sc_type,function(B){//新建手工法曲线时需要选择关联的标液
								view_sc_bd(app,act,0,'vid='+B.vid+'&wz_type='+B.wz_type+'&table_name='+B.table_name+'&bzry_id='+B.bzry_id,sel_qx_by);
							});
						}
					});
					//曲线查看和编辑
					sel_qx_by.find("a.icon-zoom-in,a.icon-edit").unbind("click").click(function(){
						var id	= $(this).attr('data-id');
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						view_sc_bd(app,act,id,'',sel_qx_by);
					});
					//删除
					sel_qx_by.find("a.icon-remove").unbind("click").click(function(){
						var id	= $(this).attr('data-id');
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						asF.delete_sc_bd(app,act,$(this).attr('data-id'));
					});
				});
			}
			asF.delete_sc_bd = function(app,act,id){
				var sel_qx_by = $("#sel_qx_by_"+asF.id);
				$.confirm({
					content: '你确定要删除吗？',
					confirm: function(){
						$.ajax({
							type: 'get',data: {app:app,act:act,id:id},dataType: 'json',
							url: trade_global.rooturl+'/huayan/ahlims.php?ajax=1&',
							success: function(data){
								if('1'==data.error){
									return alert_error(data.content);
								}else{
									sel_qx_by.FN("vid","s").trigger("change");
								}
							},error: function(data){
								return alert_error(data.responseText);
							}
						});
					}
				});
			}
			//查看[曲线|标液标定]/切换[曲线|标液标定]
			asF.find(".blue_a").andSelf().find(".view_qx,.view_bd,.view_pz,.change_qx,.change_bd").prop('title',function(){
				if($(this).is('.view_qx')){
					return '点击查看曲线';
				}else if($(this).is('.view_bd')){
					return '点击查看标液标定';
				}else if($(this).is('.view_pz')){
					return '点击查看标液配置';
				}else if($(this).is('.change_qx')){
					return '点击关联其他曲线';
				}else if($(this).is('.change_bd')){
					return '点击关联其他标液';
				}
			}).unbind("click").click(function(){
				if($(this).is('.view_qx')){
					view_sc_bd('quxian','view_sc',asF.scid);
				}else if($(this).is('.view_bd')){
					window.open(trade_global.rooturl+'/jcsy/bybd/bzry_bd.php?action=view&bd_id='+asF.bdid);
				}else if($(this).is('.view_pz')){
					$.alert({content:'标液配置查看，正在开发中。。。'});
				}else if($(this).is('.change_qx')){
					var sc_type = parseInt(asF.find(".yiqi_sc_"+asF.id).length)+1;
					asF.change_qx('app=quxian&act=sel_sc&sc_type='+sc_type);
				}else if($(this).is('.change_bd')){
					asF.change_qx('app=biaoding&act=sel_bd');
				}
			});
			//化验单退回
			$(".tui_Hui_"+asF.id).unbind("click").click(function() {
				assay_return_back(asF,'hyd','');
				return false;
			});//End
			// 显示隐藏样品编号
			$(".toggle_bar_"+asF.id).unbind("click").click(function(){
				asF.find("[data-rel=popover][data-show-bar-code=1]").removeClass("tooltip-info").addClass("tooltip-info").tooltip({
					placement: "left",
					html: true,
					trigger: "click"
				}).tooltip('toggle');
				if($(this).data("show")){
					$(this).data("show",'');
					$(this).html('显示编号');
				}else{
					$(this).data("show",'1');
					$(this).html('隐藏编号');
				}
			});//End
			//化验单签字
			asF.find("form[name^='shehe_'] input[type=button]").unbind("click").click(function(){
				assay_sign(asF,'hyd',$(this).attr('name'),'');
				return false;
			});//End
			//是否超标
			asF.find(".hydzkpj[data-chaobiao!='']").each(function(){
				try{
					var is_chaobiao = $.parseJSON($(this).attr("data-chaobiao"));
					if('1' == is_chaobiao.status){
						var prev = $(this).parents("td").prev();
						prev.add_warning('根据【'+is_chaobiao.pd_bz+'】判定数据超标，检测限值是：'+is_chaobiao.jc_xz+'');
					}
				}catch(e){
					// 
					console.log('超标数据信息错误')
					return;
				}
			});
			// 显示质控计算公式
			// 质控是否合格
			asF.find(".hydzkpc").each(function(){
				if(''==$(this).html() || '' == $(this).data("formula")){
					return true;
				}
				try{
					var formula = $.parseJSON($.parseJSON($(this).data("formula")));
					var msg = $.extend({},{nd:'',fw:'',formula:'',ping_jia:''}, formula.zhikong);
					var msg_content =	"<div style='text-align:left'>浓度范围:"+msg.nd+
										"<br />合格范围:"+msg.fw
										+"<br />质控判定:"+msg.ping_jia
										//  style='white-space:nowrap;'
										+"<br />计算公式:<br /><span>"+msg.formula+"</span></div>";
					if('合格' == msg.ping_jia){
						$(this).add_success(msg_content);
					}else{
						$(this).add_warning(msg_content);
					}
					// 平均值列显示内容
					var that = $(this).parents("tr").find(".hydzkpj");
					var msg2 = $.extend({},{title:'',text:''}, formula.show_avg);
					var msg_content2 =	"<div style='text-align:left'>"
										+msg2.title
										+"<br />"+msg2.text
										+"</div>";
					that.add_success(msg_content2);
				}catch(e){
					// 
					console.log('计算公式存储错误')
					return;
				}
			});
			//如果此张化验不允许修改，下面的事件将不再执行
			if(!asF.canModi){
				asF.find("input[type=hidden]").remove();
				asF.find("select[class^=fa_change_]").remove();
				$("#hyd_tabs_"+asF.id+" .tabs_2").addClass("hide");
				asF.find("input[type=text],select").each(function(){$(this).replaceWith($(this).val());});
				asF.find(".blue_a").andSelf().find(".change_qx,.change_bd").unbind("click").removeClass("blue_a").attr('title','');
				//进行数据溯源记录,必须是上面处理完成之后才能执行溯源操作
				dataSuYuan(asF,'assay');
				return false;
			}
			//切换方法
			asF.find(".fa_change_"+asF.id).change(function(){
				reloade_hyd(asF.id,'upfid='+$(this).val());
			});//End
			//模板切换（admin账户）
			if(parseInt(u.admin)){
				asF.find(".mb_change_"+asF.id).change(function(){
					reloade_hyd(asF.id,'up_table_id='+$(this).val());
				});
			}//End
			//质控
			$(this).zhikong(asF.id,asF.zhikong);
			//日期插件 年-月-日
			asF.find('.date-picker,.date_Ymd').datepicker({autoclose:true}).next().on(
				ace.click_event, function(){
				$(this).prev().focus();
			});
			//时间插件 时:分:秒 不显示秒
			asF.find('.timepicker1,.time_Hi').timepicker({
				minuteStep: 1,
				//showSeconds: true,
				showMeridian: false}).next().on(ace.click_event, function(){
				$(this).prev().focus();
			});//End
			//时间格式限制
			asF.find('.timepicker1').mask('99:99');
			asF.find('.month_day').mask('99月99日');
			asF.find('.input-mask-mdh').mask('99月99日99时');
			asF.find('.input-mask-date').mask('99月99日99时99分');
			//仪器载入
			asF.find(".reloade_"+asF.id).unbind("click").click(function(){
				var action_url='/autoload/loadtable.php?ajax=1&tid='+asF.id+'&fid='+$(this).attr('data')+'&vid='+asF.vid;
				asF.assay_form.FN("goto_url").val(trade_global.rooturl+action_url).submit();
			});//End
			asF.find(".reloade2_"+asF.id).unbind("click").click(function(){
				var action_url='/autoload/loadtable_print.php?ajax=1&tid='+asF.id+'&fid='+$(this).attr('data')+'&vid='+asF.vid;
				asF.assay_form.FN("goto_url").val(trade_global.rooturl+action_url).submit();
			});//End
			//双击化验单标题拆分化验
			asF.find("h1").dblclick(function(){
				//多合一化验单不允许拆分
				var dhy_len = asF.find(".hydzk[data-dhy!=0]").length;
				if(dhy_len>0){
					return alert_error("本张化验单，暂不支持拆分！");
				}else{
					if($("#assay_form_chaifen").length==0){
						$("body").append('<div id="assay_form_chaifen"></div>');
					}
					var url =trade_global.rooturl+'/huayan/assay_chaifen.php?hyd_id='+asF.id;
					$.get(url+'&action=chaifen&ajax=1',function(data){
						$("#assay_form_chaifen").html(data);
						$("#modal_chaifen").modal('show');
					});
				}
			});//End
			//化验单AJAX无刷新式提交及获取最新化验单表格
			asF.assay_form.submit(function(){
				asF.alert_obj = $.alert({
					content: '',
					title: '化验单保存并更新中'
				});
				window.post_data.ajax = 1;
				window.post_data.yuanYin = asF.assay_form.FN('yuanYin').val();
				window.post_data.goto_url = asF.assay_form.FN('goto_url').val();
				window.post_data.token_key = asF.assay_form.FN('token_key').val();
				window.post_data.submit_flag = asF.assay_form.FN('submit_flag').val();
				$.ajax({
					url: trade_global.rooturl+'/huayan/assay_form_modi.php',
					type: "post",
					data: post_data,
					dataType: 'json',
					success: function(data){
						if(data.error == '0'){
							trade_global.reloade = {
								content: data.content,
								alert_obj: asF.alert_obj
							}
							return reloade_hyd(post_data.tid, function(){
								var reloade = trade_global.reloade;
								delete trade_global.reloade;
								alert_ok(reloade.content, reloade.alert_obj)
							});
						}else if(data.error == '1'){
							return alert_error(data.content, asF.alert_obj);
						}else if(data.error == '2'){
							alert_warning({
								massage: '页面已失效，原因是该化验单在其他窗口打开过<br>（数据可能已被修改）<br><strong>您可以点击确定强制提交数据，也可以点击取消并刷新页面后重新提交！</strong>',
								confirm: function(Back){
									Back[0].FN('token_key').val(Back[1]).submit();
								}
							},asF.alert_obj,asF.assay_form,data.token_key)
						}else{
							alert_error('系统错误，请刷新页面重试！', asF.alert_obj);
						}
					},
					error: function(data){
						alert_error(data.responseText);
					}
				});
				return false; //阻止表单自动提交事件
			});//End
			//数据提交
			asF.find("input[type=button].hyd_sub_"+asF.id).click(function(){
				asF.assay_form.submit();
			});
		},
		/**
		 * 功能：化验单调用插件
		 * 作者：Mr Zhou
		 * 日期：2015-06-18
		 * 功能描述：化验单调用插件
		*/
		assay_form : function(options) {
			if(typeof trade_global.assay_form == "undefined"){
				trade_global.assay_form = {};
			}
			var asF = $(this);
			asF.id = $(this).attr('data');
			asF.assay_form = $(this).find("form[name^=as_form_]");
			$(this).assay_form_init($.extend({},asF, options));
			// console.log(options)
			// 显示化验单计算公式
			asF.find("input[name*=vd0], span[data-name*=vd0]").each(function(n){
				var that = $(this);
				// 先将td的提示清空
				var td_title = that.parents("td").attr("title");
				that.parents("td").removeAttr("title");
				if(that.prop("tagName") == "INPUT"){
					that.wrap('<span data-name="vd0['+n+']"></span>');
					that = that.parents("span");
					that.append('<input type="hidden" name="js_gongshi['+n+']" value="" />');
				}
				that.attr('data-content', '');
				that.attr('data-rel', 'popover');
				that.attr('data-original-title', '计算公式');
				that.addClass("tooltip-warning").attr('data-placement', '');
				// 点击vd0触发自动计算,并关闭其他显示的公式
				that.click(function(event){
					// 关闭其他的popover
					$(".formula-select-area").remove();
					$(".popover.fade.in").parents("td").find("[data-name]").not(that).popover('hide');
					// 触发自动计算
					var regex = /([0-9]+)/g;
					var cc = that.attr('data-name').match(regex);
					$("input[name*='["+cc[1]+"]'].hyd:first").trigger('blur');
					that.find("input[name^=vd0]").focus();
					event.stopPropagation();
				});
				// 绑定popover方法
				that.popover({html:true});
				// 如果没有定义公式内容,则不显示
				that.click(function(event){
					if(!that.attr('data-content')){
						that.popover('hide');
					}
					event.stopPropagation();
				});
			});
			// 点击其他地方时关闭计算公式的popover
			$(document).on("click", function(){
				$(".formula-select-area").remove();
				$(".popover.fade.in").parents("td").find("[data-name]").popover('hide');
			});
			// 在popover中点击阻止关闭
			asF.find("td").on("click", ".popover.fade.in", function(event){
				event.stopPropagation();
			})
			// 加载化验popover展示数据,包括数据计算公式,质控计算公式
			$.ajax({
				type: 'get',
				dataType: 'json',
				data: {tid:options.id,ajax:1},
				url: trade_global.rooturl+'/huayan/ahlims.php?app=assay&act=get_hyd_popover',
				success: function(data){
					$(data.content).each(function(k,v){
						var rowObj = asF.find("[data-orid="+v.id+"]").parents("tr");
						// 公式
						var formula = v.js_gongshi;
						// 检测结果计算公式
						rowObj.find("span[data-name^='vd0[']").attr('data-content', formula.jsgs_msg);
						// 质控结果计算公式
						if(formula.zhikong){
							var msg = $.extend({},{nd:'',fw:'',formula:'',ping_jia:''}, formula.zhikong);
							var msg_content =	"浓度范围:"+msg.nd+
												"<br />合格范围:"+msg.fw
												+"<br />质控判定:"+msg.ping_jia
												//  style='white-space:nowrap;'
												+"<br />计算公式:<br /><span>"+msg.formula+"</span>";

							rowObj.find('.hydzkpc').attr('data-rel', 'popover')
							.attr('data-animation', 'true')
							.attr('data-placement', 'top')
							.attr('data-trigger', 'hover focus')
							.attr('data-original-title', '质控计算')
							.attr('data-content', msg_content)
							if('合格' == msg.ping_jia){
								rowObj.find('.hydzkpc ').addClass('tooltip-info');
							}else{
								rowObj.find('.hydzkpc ').addClass('tooltip-error');
							}
						}
						// 平均值列显示内容
						if(formula.show_avg){
							var msg2 = $.extend({},{title:'',text:''}, formula.show_avg);
							rowObj.find('.hydzkpj').attr('data-rel', 'popover')
							.attr('data-animation', 'true')
							.attr('data-placement', 'top')
							.attr('data-trigger', 'hover focus')
							.attr('data-original-title', msg2.title)
							.attr('data-content', msg2.text)
							.addClass('tooltip-info');
						}
					});
					$('.hydzkpc[data-rel=popover],.hydzkpj[data-rel=popover]').popover({html:true});
				},
				error: function(data){
					console.log(data.responseText);
				}
			});
		},
		/**
		 * 功能：化验单初始化
		 * 作者：Mr Zhou
		 * 日期：2015-06-18
		 * 功能描述：化验单初始化
		*/
		assay_form_init : function(asF){
			var u = trade_global.u;
			trade_global.assay_form[asF.id] = asF;
			//.noinputc的输入框不允许修改
			asF.find(".noinputc").prop("readonly","readonly");
			//历史数据浮窗
			asF.find("[data-rel=popover]").popover({html:true});
			//如果data-over=over说明数据已签发，不能再修改
			asF.find("span[data-over=over]").parents("tr").find("input[type=text]").each(function(){$(this).replaceWith($(this).val());});
			//查看化验单
			$(".view_hyd_"+asF.id).unbind("click").click(function(){
				window.open(trade_global.rooturl+'/huayan/assay_form.php?tid='+asF.id);
			});//End
			//打印化验单
			$(".hyd_print_"+asF.id).unbind("click").click(function(){
				window.open(trade_global.rooturl+'/huayan/ahlims.php?app=print&act=print_hyd&ajax=1&tid='+asF.id);
			});//End
			//附件查看
			$(".pdf_files_"+asF.id).unbind("click").click(function(){
				var action_url = trade_global.rooturl+'/huayan/hydpdf.php?ajax=1&tid='+asF.id;
				$(this).qbox({title:'查看化验单的附件',src:action_url,w:700,h:400});
			});//End
			//是否隐藏签字日期
			if( typeof trade_global.hyd_config != "undefined" && typeof trade_global.hyd_config.hide_sign_date != "undefined"){
				if( true == trade_global.hyd_config.hide_sign_date ){
					asF.find(".assay_sign_form tr:last").hide();
				}
			}
			//如果具有化验单修改权限则显示仪器曲线列表，否则显示曲线表单
			asF.find(".yiqi_sc_"+asF.id).unbind("click").click(function(){
				if(!asF.canModi){
					if(asF.scid>0){
						view_sc_bd('quxian','view_sc',asF.scid);
					}else{
						alert_error("该化验单尚未关联任何曲线信息！");
					}
				}else{
					asF.change_qx('app=quxian&act=sel_sc&sc_type=2');
				}
			})//End
			//曲线切换和查看曲线函数
			asF.change_qx = function(){
				var act	= arguments[0] ? arguments[0]:'';
				var vid	= arguments[1] ? arguments[1]:'';
				if($("#sel_qx_by_"+asF.id).length==0){
					$("div[id^=sc_bd_box_]").remove();//防止曲线表单页面出现在曲线列表后面
					$("body").append('<div id="sel_qx_by_'+asF.id+'" class="modal fade" data-backdrop="static"></div>')
				}
				var sel_qx_by = $("#sel_qx_by_"+asF.id);
				var url = trade_global.rooturl+'/huayan/ahlims.php?id='+asF.scid+'&hyd_id='+asF.id+'&vid='+vid+'&hyd_vid='+asF.vid+'&bd_id='+asF.bdid+'&pz_id='+asF.pzid+'&ajax=1';
				$.get(url+'&'+act,function(data){
					sel_qx_by.html(data);
					sel_qx_by.modal('show');
					var sc_type = sel_qx_by.find("input[name=sc_type]").val();
					var table_name = '';
					sel_qx_by.find("#sel_qx_ok").click(function(){
						var app = sel_qx_by.FN("app").val();
						var sc_bd = sel_qx_by.find("input[name=sc_bd]:checked").val();
						var goto_url = url+'&app='+app+'&act=related_to_hyd&sc_bd='+sc_bd;
						asF.assay_form.FN("goto_url").val(goto_url).submit();
						sel_qx_by.modal('hide');
					});
					//项目切换后更新列表
					sel_qx_by.FN("vid","s").change(function(){
						asF.change_qx(act,$(this).val());
					});
					//新建
					sel_qx_by.find(".create_sc").unbind("click").click(function(){
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						var vid = sel_qx_by.FN("vid","s").val();
						if(sc_type=='2'){
							view_sc_bd(app,act,0,'vid='+vid+'&table_name=sc_yq&sc_type='+sc_type+'&tid='+asF.id,sel_qx_by);
						}else{
							get_bzry_box(vid,sc_type,function(B){//新建手工法曲线时需要选择关联的标液
								view_sc_bd(app,act,0,'vid='+B.vid+'&wz_type='+B.wz_type+'&table_name='+B.table_name+'&bzry_id='+B.bzry_id,sel_qx_by);
							});
						}
					});
					//曲线查看和编辑
					sel_qx_by.find("a.icon-zoom-in,a.icon-edit").unbind("click").click(function(){
						var id	= $(this).attr('data-id');
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						view_sc_bd(app,act,id,'',sel_qx_by);
					});
					//删除
					sel_qx_by.find("a.icon-remove").unbind("click").click(function(){
						var id	= $(this).attr('data-id');
						var act	= $(this).attr('data-act');
						var app	= $(this).attr('data-app');
						asF.delete_sc_bd(app,act,$(this).attr('data-id'));
					});
				});
			}
			asF.delete_sc_bd = function(app,act,id){
				var sel_qx_by = $("#sel_qx_by_"+asF.id);
				$.confirm({
					content: '你确定要删除吗？',
					confirm: function(){
						$.ajax({
							type: 'get',data: {app:app,act:act,id:id},dataType: 'json',
							url: trade_global.rooturl+'/huayan/ahlims.php?ajax=1&',
							success: function(data){
								if('1'==data.error){
									return alert_error(data.content);
								}else{
									sel_qx_by.FN("vid","s").trigger("change");
								}
							},error: function(data){
								return alert_error(data.responseText);
							}
						});
					}
				});
			}
			//查看[曲线|标液标定]/切换[曲线|标液标定]
			asF.find(".blue_a").andSelf().find(".view_qx,.view_bd,.view_pz,.change_qx,.change_bd,.change_pz").prop('title',function(){
				if($(this).is('.view_qx')){
					return '点击查看曲线';
				}else if($(this).is('.view_bd')){
					return '点击查看标液标定';
				}else if($(this).is('.view_pz')){
					return '点击查看标液配置';
				}else if($(this).is('.change_qx')){
					return '点击关联其他曲线';
				}else if($(this).is('.change_bd')){
					return '点击关联其他标液';
				}else if($(this).is('.change_pz')){
					return '点击关联其他标液';
				}
			}).unbind("click").click(function(){
				if($(this).is('.view_qx')){
					view_sc_bd('quxian','view_sc',asF.scid);
				}else if($(this).is('.view_bd')){
					window.open(trade_global.rooturl+'/jcsy/bybd/bzry_bd.php?action=view&bd_id='+asF.bdid);
				}else if($(this).is('.view_pz')){
					$.alert({content:'标液配置查看，正在开发中。。。'});
				}else if($(this).is('.change_qx')){
					var sc_type = parseInt(asF.find(".yiqi_sc_"+asF.id).length)+1;
					asF.change_qx('app=quxian&act=sel_sc&sc_type='+sc_type);
				}else if($(this).is('.change_bd')){
					asF.change_qx('app=biaoding&act=sel_bd');
				}else if($(this).is('.change_pz')){
					asF.change_qx('app=bzry_pz&act=sel_pz');
				}
			});
			//化验单退回
			$(".tui_Hui_"+asF.id).unbind("click").click(function() {
				assay_return_back(asF,'hyd','');
				return false;
			});//End
			// 查看质控
			$(".view_zk_"+asF.id).unbind("click").click(function() {
				console.log('ddd')
				if($("#assay_form_view_zk").length==0){
					$("body").append('<div id="assay_form_view_zk"></div>');
				}
				var url =trade_global.rooturl+'/huayan/ahlims.php?app=assay&hyd_id='+asF.id;
				console.log(url)
				$.get(url+'&act=assay_form_view_zk&ajax=1',function(data){
					$("#assay_form_view_zk").html(data);
					$("#modal_view_zk").modal('show');
				});
				return false;
			});//End
			// 显示隐藏样品编号
			$(".toggle_bar_"+asF.id).unbind("click").click(function(){
				asF.find("[data-rel=popover][data-show-bar-code=1]").removeClass("tooltip-info").addClass("tooltip-info").tooltip({
					placement: "left",
					html: true,
					trigger: "click"
				}).tooltip('toggle');
				if($(this).data("show")){
					$(this).data("show",'');
					$(this).html('显示编号');
				}else{
					$(this).data("show",'1');
					$(this).html('隐藏编号');
				}
			});//End
			//化验单签字
			asF.find("form[name^='shehe_'] input[type=button]").unbind("click").click(function(){
				assay_sign(asF,'hyd',$(this).attr('name'),'');
				return false;
			});//End
			//是否超标
			asF.find(".hydzkpj[data-chaobiao!='']").each(function(){
				try{
					var is_chaobiao = $.parseJSON($(this).attr("data-chaobiao"));
					if('1' == is_chaobiao.status){
						var prev = $(this).parents("td").prev();
						prev.add_warning('根据【'+is_chaobiao.pd_bz+'】判定数据超标，检测限值是：'+is_chaobiao.jc_xz+'');
					}
				}catch(e){
					// 
					console.log('超标数据信息错误')
					return;
				}
			});
			//如果此张化验不允许修改，下面的事件将不再执行
			if(!asF.canModi){
				asF.find("input[type=hidden]").remove();
				asF.find("select[class^=fa_change_]").remove();
				$("#hyd_tabs_"+asF.id+" .tabs_2").addClass("hide");
				asF.find("input[type=text],select").each(function(){
					$(this).replaceWith(
						'<span data-name="'+$(this).attr('name')+'">'
						+ $(this).val() + '</span>');
				});
				asF.find(".blue_a").andSelf().find(".change_qx,.change_bd,.change_pz").unbind("click").removeClass("blue_a").attr('title','');
				//进行数据溯源记录,必须是上面处理完成之后才能执行溯源操作
				dataSuYuan(asF,'assay');
				return false;
			}
			//切换方法
			asF.find(".fa_change_"+asF.id).change(function(){
				reloade_hyd(asF.id,'upfid='+$(this).val());
			});//End
			// 更新方法到最新配置
			asF.find(".fa_change_up_"+asF.id).click(function(){
				reloade_hyd(asF.id,'upfid='+asF.find(".fa_change_"+asF.id).val());
			});//End
			//质控
			$(this).zhikong(asF.id,asF.zhikong);
			//表头表格中实现上下左右键切换输入框
			asF.find("tr").not(".bt_hidden").enter_next_input({find:"input[type='text'][name^=td]"});
			//数据表格中实现上下左右键切换输入框
			asF.find(".huayandan").enter_sheet_input({find:"input[type='text'][name^=vd]"});
			//日期插件 年-月-日
			asF.find('.date-picker,.date_Ymd').datepicker({autoclose:true}).next().on(
				ace.click_event, function(){
				$(this).prev().focus();
			});
			//时间插件 时:分:秒 不显示秒
			asF.find('.timepicker1,.time_Hi').timepicker({
				minuteStep: 1,
				//showSeconds: true,
				showMeridian: false}).next().on(ace.click_event, function(){
				$(this).prev().focus();
			});//End
			//时间格式限制
			asF.find('.timepicker1').mask('99:99');
			asF.find('.month_day').mask('99月99日');
			asF.find('.input-mask-mdh').mask('99月99日99时');
			asF.find('.input-mask-date').mask('99月99日99时99分');
			//仪器载入
			asF.find(".reloade_"+asF.id).unbind("click").click(function(){
				var action_url='/autoload/loadtable.php?ajax=1&tid='+asF.id+'&fid='+$(this).attr('data')+'&vid='+asF.vid;
				asF.assay_form.FN("goto_url").val(trade_global.rooturl+action_url).submit();
			});//
			//双击化验单标题拆分化验
			asF.find("h1").dblclick(function(){
				//多合一化验单不允许拆分
				var dhy_len = asF.find(".hydzk[data-dhy!=0]").length;
				if(dhy_len>0){
					return alert_error("本张化验单，暂不支持拆分！");
				}else{
					if($("#assay_form_chaifen").length==0){
						$("body").append('<div id="assay_form_chaifen"></div>');
					}
					var url =trade_global.rooturl+'/huayan/assay_chaifen.php?hyd_id='+asF.id;
					$.get(url+'&action=chaifen&ajax=1',function(data){
						$("#assay_form_chaifen").html(data);
						$("#modal_chaifen").modal('show');
					});
				}
			});//End
			//绑定_aItem函数的数据双击时仍然可以调用_aItem
			asF.find("input[onclick='_aItem(this)']").dblclick(function(){
				_aItem(this,true);
			})
			//转换jsgs函数为hyd_jsgs_+asF.id的形式，避免多张化验单在一个页面时冲突
			var script_jsgs = asF.assay_form.find('script');
			if(script_jsgs.length==1){
				var script_jsgsCode = script_jsgs.html().replace('function jsgs(','window.jsgs=function(');
				script_jsgs.remove();
				eval(script_jsgsCode);
			}
			if(typeof window.jsgs == "function"){
				eval('window.hyd_jsgs_'+asF.id+'=jsgs;window.jsgs=null;');
				//化验单计算
				asF.find(".hyd").on("blur", function() {
					var regex = /([0-9]+)/g;
					var cc = $(this).attr('name').match(regex);
					// 清空当前结果的计算过程
					jsgs_msg(cc[1], '');
					form_get_v(asF.assay_form,$(this).prop("name"),'hyd_jsgs_'+asF.id);
				});
			}else{
				eval('window.hyd_jsgs_'+asF.id+' = window.jsgs=null;');
			}
			//化验单AJAX无刷新式提交及获取最新化验单表格
			asF.assay_form.submit(function(){
				//找到每一行里面的第一个.hyd输入框触发blur事件进行自动计算
				var first_td = asF.find(".single tr input[type=text].hyd:first");
				if(first_td.length){
					var vd_name = first_td.attr('name').split('[')[0];
					asF.find(".single input[name^='"+vd_name+"[']").trigger("blur");
					// 存储最新的计算过程信息
					$("span[data-name^='vd0[']").each(function(n){
						$(this).find("[name^='js_gongshi']").val($(this).attr('data-content'));
					});
				}
				var alert_obj = $.alert({
					content: '',
					title: '化验单保存并更新中',
					//icon: 'icon icon-spinner icon-spin'
				});
				asF.assay_form.ajaxSubmit({
					type: 'post',dataType:'json',data: {'ajax': 1},
					url: trade_global.rooturl+'/huayan/assay_form_modi.php',
					success: function(data) {
						if(data.error == '0'){
							var sec = parseInt(data.sec);
							var sec = $.isNumeric(sec) && sec > 2 ? sec : 2;
							if(alert_ok(data.content,alert_obj, sec)){
								return reloade_hyd(asF.id);
							}
						}else if(data.error == '1'){
							return alert_error(data.content,alert_obj);
						}else if(data.error == '2'){
							alert_warning({
								massage: '页面已失效，原因是该化验单在其他窗口打开过<br>（数据可能已被修改）<br><strong>您可以点击确定强制提交数据，也可以点击取消并刷新页面后重新提交！</strong>',
								confirm: function(Back){
									Back[0].FN('token_key').val(Back[1]).submit();
								}
							},alert_obj,asF.assay_form,data.token_key)
						}else{
							alert_error('系统错误，请刷新页面重试！',alert_obj);
						}
					},
					error: function(data){
						return alert_error(data.responseText,alert_obj);
					}
				});
				return false; //阻止表单自动提交事件
			});//End
			//数据提交
			asF.find("input[type=button].hyd_sub_"+asF.id).click(function(){
				asF.assay_form.submit();
			});
		},
		/**
		 * 功能：
		 * 作者：Mr Zhou
		 * 日期：2015-09-17
		 * 功能描述： 标准曲线调用插件
		*/
		quxian_form : function(options) {
			if(typeof trade_global.quxian_form == "undefined"){
				trade_global.quxian_form = {};
			}
			var scF = $(this);
			scF.assay_form = $(this).find("form[name^=sc_form_]");
			$(this).quxian_form_init($.extend({},scF, options));
		},
		/**
		 * 功能：
		 * 作者：Mr Zhou
		 * 日期：2015-09-17
		 * 功能描述：化验单初始化
		*/
		quxian_form_init : function(scF){
			var u		= trade_global.u;
			trade_global.quxian_form[scF.id] = scF;
			//.noinputc的输入框不允许修改
			scF.find(".noinputc").prop("readonly","readonly");
			//打印
			scF.find(".sc_print_"+scF.id).unbind("click").click(function(){
				window.open(trade_global.rooturl+'/huayan/ahlims.php?app=quxian&act=index&print=1&id='+scF.id);
			});//End
			//复制化验单表头数据
			scF.find(".sc_copy_hyd_"+scF.id).unbind("click").click(function(){
				view_sc_bd('quxian','create_sc',scF.id,'vid='+scF.vid+'&table_name='+scF.table_name+'&sc_type='+scF.type+'&tid='+$(this).data('tid'));
			});
			//曲线退回
			scF.find(".quxian_th_"+scF.id).unbind("click").click(function() {
				assay_return_back(scF,'qx','app=quxian&act=view_sc');
				return false;
			});//End
			//签字表单执行签字
			scF.find("form[name='shehe_"+scF.id+"'] input[type='button']").unbind("click").click(function(){
				assay_sign(scF,'qx',$(this).attr('name'),'app=quxian&act=view_sc');
				return false;
			});//End

			//如果此张化验不允许修改，下面的事件将不再执行
			if(!scF.canModi || '1' == scF.print){
				scF.find("input[type=hidden]").remove();
				scF.find(".blue_a").unbind("click").removeClass("blue_a").attr('title','');
				scF.find("input[type=text],select").each(function(){$(this).replaceWith($(this).val());});
				//进行数据溯源记录,必须是上面处理完成之后才能执行溯源操作
				dataSuYuan(scF,'quxian');
				return false;
			}
			//表头表格中实现上下左右键切换输入框
			scF.enter_next_input({find:"input[type='text'][name^=td]"});
			//数据表格中实现上下左右键切换输入框
			scF.enter_sheet_input({find:"input[type='text'][name^=vd]"});
			//日期插件 年-月-日
			scF.find('.date-picker,.date_Ymd').datepicker({autoclose:true}).next().on(
				ace.click_event, function(){
				$(this).prev().focus();
			});
			//切换项目和标准溶液
			scF.find(".blue_a").andSelf().find(".change_xm,.change_by").unbind("click").click(function(){
				get_bzry_box(scF.vid,scF.sc_type,function(B){
					scF.FN("vid").val(B.vid);
					scF.find(".change_xm").html(B.valueC);
					$.ajax({
						type: 'get',
						dataType: 'json',
						data: {bzry_id:B.bzry_id,wz_type:B.wz_type,ajax:1},
						url: trade_global.rooturl+'/huayan/ahlims.php?app=quxian&act=ajaxRequest&method=get_bzry_info',
						success: function(data){
							for(var key in data.content){
								scF.FN(key).val(data['content'][key]);
							}
							scF.find(".b:first").trigger("blur");
							scF.find(".change_by").html(data['content']['td7']);
							if(B.table_name!=scF.FN("table_name").val()){
								$.alert({
									content:"更改表格需要先进行数据保存操作，你确定要执行此操作吗？",
									confirm:function(){
										scF.FN('table_name').val(B.table_name);
										scF.assay_form.submit();
								}})
							}
						},
						error: function(data){
							alert_error(data.responseText);
						}
					});
				});
			});
			//绑定_aItem函数的数据双击时仍然可以调用_aItem
			scF.find("input[onclick='_aItem(this)']").dblclick(function(){
				_aItem(this,true);
			})
			//标准曲线统一计算公式
			scF.find(".b").unbind("blur").blur(function(){
				//标液浓度
				var by_obj = scF.find("input[name='td8']");
				scF.by_c = by_obj.val();
				//获取并计算标准使用液浓度
				var roundedByC = scF.by_c = parseFloat(scF.by_c);
				by_obj.check_isNumeric('请填写正确的储备液浓度');
				if(!$.isNumeric(scF.by_c)){
					scF.by_c = 0;
					by_obj.add_warning();
					return false;
				}else{
					by_obj.remove_warning();
				}
				//稀释计算
				var td10 = scF.FN("td10").val();
				var td11 = scF.FN("td11").val();
				var td13 = scF.FN("td13").val();
				var td14 = scF.FN("td14").val();
				var td16 = scF.FN("td16").val();
				var td17 = scF.FN("td17").val();
				if($.isNumeric(td10)&&$.isNumeric(td11)){
					scF.by_c /= parseFloat(parseFloat(td11) / parseFloat(td10));
					scF.FN("td12").val(auto_round(scF.by_c, scF.config.round_c));
					var roundedByC = scF.FN("td12").val();
				}else{
					scF.FN("td12").val('');
				}
				if($.isNumeric(td13)&&$.isNumeric(td14)){
					scF.by_c /= parseFloat(parseFloat(td14) / parseFloat(td13));
					scF.FN("td15").val(auto_round(scF.by_c, scF.config.round_c));
					var roundedByC = scF.FN("td15").val();
				}else{
					scF.FN("td15").val('');
				}
				if($.isNumeric(td16)&&$.isNumeric(td17)){
					scF.by_c /= parseFloat(parseFloat(td17) / parseFloat(td16));
					scF.FN("td18").val(auto_round(scF.by_c, scF.config.round_c));
					var roundedByC = scF.FN("td18").val();
				}else{
					scF.FN("td18").val('');
				}
				//使用修约后的数据进行计算
				scF.by_c = roundedByC;
				//触发计算
				scF.find("input[name^='vd0[']").trigger("blur");
			});
			//初始化标准使用液浓度
			scF.find(".b:first").trigger("blur");
			//曲线数据单位 unit
			scF.sc_unit = scF.find("select[name='unit']").val();
			scF.find("select[name='unit']").change(function(){
				scF.sc_unit = $(this).val();
				scF.find("input[name^='vd0[']").trigger("blur");
			});//End
			scF.sc_jsgs = function (cc) {
				//定容体积
				var ding_v  = parseFloat(scF.FN("td3").val());
				if(!$.isNumeric(ding_v)){
					return false;
				}
				//系统以µg为参照标准进行换算
				var k = 1;
				// ########
				switch(scF.sc_unit){
					case 'µg'	:k=1;break;
					case 'mg/L'	:k=1/ding_v;break;
					case 'µg/mL':k=1/ding_v;break;
					case 'mg'	:k=0.001;break;
					case 'µg/L'	:k=1000/ding_v;break;
					case '度'	:
					case 'NTU'	:k=1/ding_v;break;
					// GB/T 7484-1987 水质 氟化物的测定 离子选择电极法 
					// 氟化物做曲线时需要求标液用量（μg）的对数即log M与电位值求截距斜率，为了保持代码统一，这里先除以标液浓度和标液体积，在下面的计算公式里再乘回来
					case 'logM' :k=Math.log10(vs['vd0'])/scF.by_c/(vs['vd0'] || 1);break;
					default : k=1;
				}
				if($.isNumeric(scF.by_c)&&$.isNumeric(vs['vd0'])){
					vs['vd0']	= auto_round(vs['vd0'], scF.config.round_v);
					vs['vd1']	= auto_round(scF.by_c * vs['vd0']*k, scF.config.round_x);
				}else{
					vs['vd1']	= '';
				}
				//加 accAdd 减 accsub 乘 accMul 除 accDiv
				if($.isNumeric(vs['vd2'])){
					vs['vd2'] = auto_round(vs['vd2'], scF.config.round_y);
					if(typeof vs['vd3'] == "undefined"){
						var avg_1 = parseFloat(vs['vd2']);
					}else if($.isNumeric(vs['vd3'])){
						vs['vd3'] = auto_round(vs['vd3'], scF.config.round_y);
						var avg_1 = vs['vd4'] = auto_round(accDiv(accAdd(vs['vd2'], vs['vd3']), 2), scF.config.round_y);
					}else{
						vs['vd4'] = '';
					}
				}else if(typeof vs['vd4'] == "undefined"){
					vs['vd4'] = '';
				}
				if($.isNumeric(vs['vd7'])){
					vs['vd7'] = auto_round(vs['vd7'], scF.config.round_y);
					if(typeof vs['vd8'] == "undefined"){
						var avg_2 = parseFloat(vs['vd7']);
					}else if($.isNumeric(vs['vd8'])){
						vs['vd8'] = auto_round(vs['vd8'], scF.config.round_y);
						var avg_2 = vs['vd9'] = auto_round(accDiv(accAdd(vs['vd7'],vs['vd8']), 2), scF.config.round_y);
					}else{
						vs['vd9'] = '';
					}
				}else if(typeof vs['vd9'] != "undefined"){
					vs['vd9'] = '';
				}
				var sc_kb = scF.find("input[name=td29]");
				if($.isNumeric(avg_1) && !sc_kb.length){
					vs['vd5'] = auto_round(vs['vd5'], scF.config.round_y);
					vs['vd6'] = auto_round(accsub(avg_1,vs['vd5']), scF.config.round_y);
				}else if($.isNumeric(avg_2) && $.isNumeric(sc_kb.val())){
					//A220-2*A275-空白
					vs['vd6'] = auto_round(accsub(accsub(avg_1,accMul(2,avg_2)),sc_kb.val()), scF.config.round_y);
				}else {
					vs['vd6'] = '';
				}
			}//End
			eval('window.sc_jsgs_'+scF.id+'='+scF.sc_jsgs+';');
			//曲线计算
			scF.find(".hyd").on('blur', function() {
				form_get_v(scF.assay_form,$(this).attr('name'),'sc_jsgs_'+scF.id);
			});//End
			scF.find("input[type=text].btjs").blur(function(){
				var td23 = scF.find("input[name=td23]").val();
				if(typeof td23 == "undefined"){
					var avg_1 = scF.find("input[name=td25]").val();
					var avg_2 = scF.find("input[name=td28]").val();
				}else{
					var avg_1 = avg_2 = '';
					var td24 = scF.find("input[name=td24]").val();
					var td26 = scF.find("input[name=td26]").val();
					var td27 = scF.find("input[name=td27]").val();
					if($.isNumeric(td23)&&$.isNumeric(td24)){
						var avg_1 = auto_round(accDiv(accAdd(td23,td24),2), scF.config.round_x);
						scF.find("input[name=td25]").val(avg_1);
					}else{
						scF.find("input[name=td25]").val('');
					}
					if($.isNumeric(td26)&&$.isNumeric(td27)){
						var avg_2 = auto_round(accDiv(accAdd(td26,td27),2), scF.config.round_x);
						scF.find("input[name=td28]").val(avg_2);
					}else{
						scF.find("input[name=td28]").val('');
					}
				}
				if($.isNumeric(avg_1)&&$.isNumeric(avg_2)){
					var avg_3 = auto_round(accsub(avg_1,accMul(2,avg_2)), scF.config.round_x);
					scF.find("input[name=td29]").val(avg_3);
				}else{
					scF.find("input[name=td29]").val('');
				}
				//触发计算
				scF.find("input[name^='vd0[']").trigger("blur");
			})//End
			//数据提交
			scF.find("input[type=button].sc_sub_"+scF.id).click(function(){
				scF.assay_form.submit();
			});
			//化验单AJAX无刷新式提交及获取最新化验单表格
			scF.assay_form.submit(function(){
				//标液信息触发修改
				scF.find(".b:first").trigger("blur");
				scF.find(".kb_data .hyd:first").trigger("blur");
				//空白计算触发修改
				scF.find(".btjs:first").trigger("blur");
				var alert_obj = $.alert({
					//icon: 'icon icon-spinner icon-spin',
					title: '化验单保存并更新中',
					content: ''
				});
				scF.assay_form.ajaxSubmit({
					type: 'post',dataType:'json',data: { 'ajax': 1 },
					url: trade_global.rooturl+'/huayan/ahlims.php?app=quxian&act=modi_sc',
					success: function(data) {
						if(data.error == '1'){
							return alert_error(data.content,alert_obj);
						}else{
							//如果是在基础实验里面新建曲线需要重新加载
							if($("div[id^='sc_tabs_']").length && data.sc_id != scF.id){
								//自动跳转至曲线页面
								var autourl = '/huayan/ahlims.php?app=quxian&act=index&id='+data.sc_id;
								document.location.href = trade_global.rooturl+'/huayan/ahlims.php?app=public&act=reto&sec=2&content='+data.content+'&autourl='+encodeURIComponent(autourl);
							}else{
								if(alert_ok(data.content,alert_obj)){
									reloade_hyd(data.sc_id,'app=quxian&act=view_sc','qx');
								}
							}
						}
					},error: function(data){
						return alert_error(data.responseText,alert_obj);
					}
				});
				return false; //阻止表单自动提交事件
			});//End
		},/**End**/
		/**
		 * 功能：添加提示
		 * 作者：Mr Zhou
		 * 日期：201８-08-16
		 * 参数：
		*/
		add_success : function(){
			var that = $(this);
			if(that.prop("tagName") == "INPUT"){
				that.focus();
			}else if(that.prop("tagName") == "TD" && that.find("span").length==0){
				that.html('<span>'+that.html()+'</span>').attr("title",'');
				that = that.find("span:first");
			}
			if(arguments[0]){
				that.addClass("tooltip-success").addClass('data_success');
				that.parents("td").addClass('form-group').addClass('has-success');
				that.attr('data-original-title',arguments[0]).data("html",true).tooltip();
			}
		},
		/**
		 * 功能：添加警告
		 * 作者：Mr Zhou
		 * 日期：2015-09-30
		 * 参数：
		*/
		add_warning : function(){
			var that = $(this);
			if(that.prop("tagName") == "INPUT"){
				that.focus();
			}
			if(arguments[0]){
				that.addClass('tooltip-error form-group has-error');
				that.attr('data-original-title',arguments[0]).data('html', true);
				$(that).on('mouseover', function(){
					$('.hyd-tooltip').remove();
					var tooltip = '<div class="hyd-tooltip tooltip fade top in" style="display:none;"><div class="tooltip-arrow"></div><div class="tooltip-inner">'+ $(that).data('original-title') +'</div></div>';
					$(tooltip).appendTo('body');
					var top = $(that).offset().top - $('.hyd-tooltip').outerHeight();
					var left = parseInt($(this).offset().left) + parseInt($(that).outerWidth())/2 - $('.hyd-tooltip').outerWidth()/2;
					$('.hyd-tooltip').css({
						top: top + 'px',
						left: left + 'px' 
					}).show();
					$('.hyd-tooltip .tooltip-arrow').css({'border-top-color': '#c94d32'});
					$('.hyd-tooltip .tooltip-inner').css({'background-color': '#c94d32'});
				});
				$(that).on('mouseout', function(){
					$('.hyd-tooltip').remove();
				});
			}
		},
		/**
		 * 功能：移除警告
		 * 作者：Mr Zhou
		 * 日期：2015-09-30
		 * 参数：
		*/
		remove_warning : function(){
			$(this).unbind('mouseover mouseover');
			$(this).attr('data-original-title','');
			$(this).parents('td').removeClass('form-group has-error');
		},
		/**
		 * 功能：添加警告
		 * 作者：Mr Zhou
		 * 日期：2015-09-30
		 * 参数：
		*/
		add_warning1 : function(){
			var that = $(this);
			if(that.prop("tagName") == "INPUT"){
				that.focus();
			}else if(that.prop("tagName") == "TD" && that.find("span").length==0){
				that.html('<span>'+that.html()+'</span>').attr("title",'');
				that = that.find("span:first");
			}
			if(arguments[0]){
				that.addClass('tooltip-error');
				that.parents('td').addClass('form-group').addClass('has-error');
				that.attr('data-original-title',arguments[0]).data("html",true).tooltip();
			}
		},
		/**
		 * 功能：移除警告
		 * 作者：Mr Zhou
		 * 日期：2015-09-30
		 * 参数：
		*/
		remove_warning1 : function(){
			$(this).attr('data-original-title','').tooltip();
			$(this).parents('td').removeClass('form-group has-error');
		},
		/**
		 * 功能：检查是否是数字并且处理警告
		 * 作者：Mr Zhou
		 * 日期：2015-09-30
		 * 参数：
		*/
		check_isNumeric : function(){
			var msg = arguments[0] ? arguments[0] : '请填写正确的数字！';
			$(this).blur(function(){
				if(!$.isNumeric($(this).val())){
					$(this).add_warning(msg);
				}else{
					$(this).remove_warning();
				}
			});
			$(this).focus(function(){
				$(this).remove_warning();
			});
		},
		/**
		 * 功能：按键切换输入框
		 * 作者：Mr Zhou
		 * 日期：2015-06-18
		 * 参数：	options obj {find:"input[type='text'][name^=vd]",not:".noinputc",focus:-1}
		 *			find：允许切换的输入框的属性选择器,not:不允许进入的输入框的属性选择器
		 *			focus：默认进入第几个输入框，-1代表不进入
		 * 功能描述： 输入框切换插件，本插件只是实现上一个，下一个输入框的切换
		 * 回车键和下方向键跳转到下一个输入框，上方向键转至上一个输入框
		*/
		enter_next_input : function(options){
			var defaults = {find:"input[type='text']",not:".noinputc",focus:-1};
			var opts = $.extend({}, defaults, options);
			var inputs = $(this).find(opts.find).not(opts.not);
			if(opts.focus>=0){
				inputs[opts.focus].select();
			}
			inputs.keydown(function(e) {
				//shift组合键可以取消此监听
				if(e.shiftKey || e.ctrlKey){
					return true;
				}
				var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
				//13 回车键 37左方向键 38上方向键 39右方向键 40下方向键
				if (keyCode == 13 || keyCode == 39 || keyCode == 40){
					var idx = inputs.index(this);	// 获取当前焦点输入框所处的位置
					if (idx == inputs.length - 1){	// 判断是否是最后一个输入框
						var index = 0;
					}else {
						var index = idx + 1;
					}
				}else if(keyCode == 37 || keyCode == 38){
					var idx = inputs.index(this);	// 获取当前焦点输入框所处的位置
					if (idx == 0){					//判断是否是最后一个输入框
					var index = inputs.length - 1;
					}else {
					var index = idx - 1;
					}
				}else{
					return true;
				}
				inputs[index].select();	//设置焦点
				$(".datepicker").hide();//如果有日期选择框，取消显示
				if(keyCode == 13){
					return false;//取消默认的enter提交行为
				}
			});
		},//End
		/**
		 * 功能：
		 * 作者：Mr Zhou
		 * 日期：2015-12-03
		 * 参数：options obj {find:"input[type='text'][name^=vd]",not:".noinputc"}
		 * find:允许切换的输入框的属性选择器,not:不允许进入的输入框的属性选择器
		 * 功能描述： 实现上下左右键切换输入框功能，仅限制在table表格中使用
		*/
		enter_sheet_input : function(options){
			var that = $(this);
			var defaults = {find:"input[type='text']",not:".noinputc"};
			var opts = $.extend({}, defaults, options);
			if($(this)[0].tagName!='TABLE'){
				if($(this).find("table").length==1){
					var sheet = $(this).find("table");
				}else if($(this).find("table.single").length==1){
					var sheet = $(this).find("table.single");
				}else{
					console.log('不是table表格！');
					return false;
				}
			}else{
				var sheet = $(this);
			}
			//由ZB建立一个tr*td的方形矩阵坐标系数组
			//由ZBF记录ZB矩阵中每个坐标点实际的tr，td所在位置
			//通过遍历每一行每一列来标记坐标点
			var ZB = [] , ZBF = [];
			for (var tr = 0; tr < sheet.find("tr").length; tr++) {
				(typeof ZB[tr] == "undefined")&&(ZB[tr] = []);
				for (var td = 0; td < sheet.find("tr:eq("+tr+") td").length;td++) {
					//查看该td单元格的合并行数以及合并列数
					var rowspan = parseInt(sheet.find("tr:eq("+tr+") td:eq("+td+")").prop("rowspan"));
					(!$.isNumeric(rowspan)) && (rowspan = 1);
					var colspan = parseInt(sheet.find("tr:eq("+tr+") td:eq("+td+")").prop("colspan"));
					(!$.isNumeric(colspan)) && (colspan = 1);
					//如果当前td单元格已经标记过坐标，说明该单元格被之前的单元格合并了，不需要重新标记
					for (var new_td = td; new_td < ZB[tr].length; new_td++) {
						if(typeof ZB[tr][new_td] == "undefined"){
							break;
						}
					};
					//根据该单元格的合并行数以及合并列数进行坐标点标记
					for (var tdi = 0; tdi < colspan; tdi++) {
						for (var tri = 0; tri < rowspan; tri++) {
							var m = parseInt(tr)+parseInt(tri);
							var n = parseInt(new_td)+parseInt(tdi);
							(typeof ZB[m] == "undefined")&&(ZB[m] = []);
							(typeof ZBF[tr] == "undefined")&&(ZBF[tr] = []);
							//标记ZB坐标系，记录该坐标系下实际对应的tr，td位置
							(typeof ZB[m][n] == "undefined")&&(ZB[m][n] = [tr,td]);
							//标记ZBF位置坐标，记录tr，tr位置在ZB坐标系中的位置
							(typeof ZBF[tr][td] == "undefined")&&(ZBF[tr][td] = [m,n]);
						}
					};
				};
			};
			//End
			var x = 0 , y = 0;
			var rows = sheet.find("tr").length;
			var cols = sheet.find("tr:eq(0) td").length;
			var inputs = sheet.find(opts.find).not(opts.not);
			inputs.unbind("keydown,keypress,keyup").keyup(function(e) {
				//Shift、Ctrl组合键可以取消此监听
				if(e.shiftKey || e.ctrlKey){ return true; }
				var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
				var trID = sheet.find("tr").index($(this).parents("tr"));
				var tdID = $(this).parents("tr").find("td").index($(this).parent("td"));
				y = ZBF[trID][tdID][0];
				x = ZBF[trID][tdID][1];
				changeItem(keyCode);
				//如果有日期选择框，取消显示
				$(".datepicker").hide();
				//取消默认的enter提交行为
				if(keyCode == 13){ return false; }
			});
			var changeItem = function(keyCode){
				if(keyCode == 37){
					x--;//左键37
				}else if (keyCode == 38){
					y--;//上键38
				}else if(keyCode == 39){
					x++;//右键39
				}else if(keyCode == 40 || keyCode == 13){
					y++;//下键40 回车键13
				}else{
					return false;
				}
				if(x>=cols){ y++; x=0;} else if(x<0) { y--; x=cols-1; }
				if(y>=rows){ x++; y=0;} else if(y<0) { x--; y=rows-1; }
				if(x>=cols){ x=0;} else if(x<0) { y--; x=cols-1; }
				if(y>=rows){ y=0;} else if(y<0) { x--; y=rows-1; }
				if(typeof ZB[y][x] == "undefined"){
					console.log('不存在x:'+x+'y:'+y);
					return;
				}
				var next_input = sheet.find("tr:eq("+ZB[y][x][0]+")").find("td:eq("+ZB[y][x][1]+") "+opts.find).not(opts.not);
				if(next_input.length > 0){
					next_input.focus();
					next_input.select();
				}else{
					changeItem(keyCode);
				}
			}
		},//End
		/**
		 * 功能：
		 * 作者：Mr Zhou
		 * 日期：2015-06-18
		 * 参数：value,tagName,name
		 * 功能描述： 扩展jQuery的find功能$(obj).FN("vid",'i','n');可以匹配<input name="vid" value="" />
		*/
		FN : function(value,tagName,name){
			if(typeof value == "undefined"){
				return false;
			}
			var Name = {
				n:"name",
				t:"type",
				v:"value"
			}
			var TagName = {
				i:"input",
				s:"select",
				c:"checkbox",
				r:"radio"
			}
			if(typeof name == "undefined"){
				name = 'name';
			}else{
				if(typeof Name[name] != "undefined"){
					name = Name[name];
				}
			}
			if(typeof tagName == "undefined"){
				tagName = 'input';
			}else{
				if(typeof TagName[tagName] != "undefined"){
					tagName = TagName[tagName];
				}
			}
			return $(this).andSelf().find(tagName+"["+name+"='"+value+"']");
		}//End
	});
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-06-18
	 * 功能描述：化验单计算取值，赋值函数
	*/
	var form_get_v = function (f,vname,func_name){
		var k = vname.indexOf('[');
		var w = vname.indexOf(']');
		var lc= vname.substring(k,w+1);
		var cc= parseInt(vname.substring(k+1,w));
		//vs变量在每个计算公式里面会用到，所以定义为了全局变量
		// 表头数据中的td
		var vtk = Array();
		var tdinput=f.find("input[name*=td]");
		// 存储td数据并在jsgs中进更新
		window.vt = new Array(tdinput.length);
		// 因为旧的计算程序里面没有vt，使用的是其他方式给td赋值，这时会跳过vt的操作，当jsgs函数执行完毕后，实际上td可能已经发生了更改，但是vt依然是旧值，这时程序如果将vt作为最新值再将td覆盖就会导致td的修改无效，所以为了兼容已有的旧有计算程序，做出兼容改正，增加vt_old，在jsgs执行完后与vt对比，当他们不一致时再给td赋值
		window.vt_old = new Array(tdinput.length);
		tdinput.each(function(index) {
			vtk[index]=$(this).attr('name');
			vt[$(this).attr('name')] = $.trim($(this).val());
			vt_old[$(this).attr('name')] = $.trim($(this).val());
		});
		// 行数据中的vd
		var vk=0;
		var vsk=Array();
		var vdinput=f.find("input[name*='["+cc+"]']");
		window.vs = new Array(vdinput.length);
		vdinput.each(function(index) {
			vk=$(this).attr('name').indexOf('[');
			vs[$(this).attr('name').substring(0,vk)]=$.trim($(this).val());
			vsk[index]=$(this).attr('name').substring(0,vk);
			//调用plan 模版中的
		});
		if(eval('$.isFunction('+func_name+');')){
			try{
				eval(func_name+'('+cc+')');
			}catch(e){
				console.log('计算公式函数错误:');
				console.log(e);
			}
			// 更新表头数据
			if($.isArray(vt)){
				for(var key in vtk){
					var tdname = vtk[key];
					if(vt[tdname] != vt_old[tdname]){
						f.find("input[name='"+tdname+"']").val(vt[tdname]);
					}
				}
			}
			// 更新行数据
			if($.isArray(vs)){
				for(var key in vsk)
				{
					var vkey = vsk[key];
					var vdname = vkey+ lc;
					f.find("input[name='"+vdname+"']").val(vs[vkey]);
				}
			}
		}
	}
	/**
	 * 功能：加载化验单
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：
	*/
	window.reloade_hyd = function(id,options,sign_type){
		var reback_function = null;
		if( typeof options == "function" ){
			reback_function = options;
		}
		(typeof options != "string") && (options = "");
		(typeof sign_type != "string") && (sign_type = "hyd");
		//允许签字的数据表
		var allow_sign_tables = {
			"py" : ['称量配药记录表', 'jzry'],
			"bd" : ['标准溶液标定记录表', 'jzry_bd'],
			"hyd": ['化验单表原始记录表', 'assay_pay'],
			"qx" : ['标准曲线原始记录表', 'standard_curve']
		};
		switch(sign_type){
			case "qx" :
				var form_box = '#quxian_form_'+id;
				//新建曲线返回数据重新加载时更改盒子id
				$('#sc_bd_box_0').prop('id','sc_bd_box_'+id);
				$('#quxian_form_0').prop('id','quxian_form_'+id);
				var url = trade_global.rooturl+'/huayan/ahlims.php?id='+id;break;
			case "hyd" :
				var form_box = '#assay_form_'+id;
				var url = trade_global.rooturl+'/huayan/assay_form.php?tid='+id;break;
			default :
				return $.alert_error("【"+sign_type+"】是不支持的签字表单类型，请检查重试！");
		}
		$.ajax({
			url: url+'&ajax=1&'+options,
			type: "GET", data: '', dataType: 'json',
			success: function(data){
				if('0'==data.error){
					$("button.order_button[data-tid="+id+"]").parents("div.zk_tooltip").remove();
					$(data.html).replaceAll(form_box);
					if( typeof reback_function == "function" ){
						reback_function();
					}else{
						var massage = '';
						if(typeof data.content == "string"){
							massage = data.content;
						}
						if('' != massage){
							return alert_ok(massage);
						}
					}
				}else{
					return alert_error(data.content);
				}
			},
			error: function(data){
				return alert_error(data.responseText);
			}
		});
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：查看曲线标液
	*/
	window.view_sc_bd = function(app,act,id,options,selQxByBox){
		(typeof options != "string") && (options = '');
		$.ajax({
			type: 'get',data: {app:app,act:act,id:id},dataType: 'json',
			url: trade_global.rooturl+'/huayan/ahlims.php?ajax=1&'+options,
			success: function(data){
				if('1'==data.error){
					return alert_error(data.content);
				}
				//曲线，标定显示的modal
				if($("#sc_bd_box_"+id).length == 0 );{
					var sc_bd_box_html = '<div id="sc_bd_box_'+id+'" class="modal fade" data-backdrop="static"><div style="width:800px;margin:0 auto;overflow:auto;" class="modal-content"><div class="modal-header"><button type="button" class="close close_sc_bd" data-dismiss="modal" aria-hidden="true" title="关闭窗口">&times;</button><h3></h3></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-sm close_sc_bd" data-dismiss="modal" aria-hidden="true" title="关闭窗口">关闭</button></div></div></div>';
					$("body").append(sc_bd_box_html);
				}
				var sc_bd_box = $("#sc_bd_box_"+id);
				sc_bd_box.modal('show').find(".modal-body").html(data.html);
				$("#sc_bd_box_"+id).find(".close_sc_bd").click(function(){
					if(typeof selQxByBox == 'object'){
						selQxByBox.FN("vid","s").trigger("change");
					}
				});
			},error: function(data){
				return alert_error(data.responseText);
			}
		});
	}
	/**
	 * 功能：原始记录签字
	 * 作者：Mr Zhou
	 * 日期：2016-03-22
	 * 参数：f				[object] 数据表单对象
	 * 参数：assay_type		[string] 原始记录类型
	 * 参数：sign_type		[string] 签字类型
	 * 参数：load_options	[string] 回调函数需要传递的参数
	 * 功能描述：
	*/
	var assay_sign = function(f,assay_type,sign_type,load_options){
		//签字表单提交函数
		var assay_sign_submit = function(reback_function){
			$.ajax({
				type: "post",
				dataType: "json",
				data: {
					id: f.id,
					sign_type: sign_type,
					assay_type: assay_type,
					yuanYin: f.FN("yuanYin").val()
				},
				url: trade_global.rooturl+'/huayan/ahlims.php?app=assay_sign&act=assay_sign&ajax=1',
				success: function(data){
					if( '0' == data.error ){
						if( typeof reback_function == "function" ){
							reback_function();
						}
						//更新已签字数目，以下内容是在校核复核页面使用的
						if( 'hyd' == assay_type && $("#signTotal").length ){
							//解决#13582.13：签字一次性签不完的问题，签完之后更新LIMIT起始位置
							if(typeof window.limit_start == "undefined"){
								window.limit_start = 0;
							}else{
								//起始位置大于0时才进行减减，否则SQL报错
								( window.limit_start > 0 ) && ( window.limit_start-- );
							}
							//$(".collapse_"+f.id).trigger("click");//当前化验已签字，自动隐藏
							var hydItem = $("a[href='#hydItem_"+f.id+"']");//当前化验的表单Item
							$("#signTotal").html(parseInt($("#signTotal").html())+1);//总签字数加1
							//更新签字状态样式并且执行签字总数加1操作
							var hydRowSign = hydItem.parents("li[id^='nav_rows']").find("span.hydRowSign");
							hydItem.find("i").removeClass('icon-check-empty').addClass('icon-check green');
							hydRowSign.html(parseInt(hydRowSign.html())+1);
							//找出当前化验单的位置
							var index = $(".assay_form_item").index($(".assay_form_item[data="+f.id+"]"));
							//如果存在下一张化验单，则跳入下一个位置
							if($(".assay_form_item").eq(index+1).length){
								window.location.hash="#hydItem_"+$(".assay_form_item").eq(index+1).attr('data');
							}
						}
						return true;
					}else{
						return alert_error(data.content);
					}
				},error: function(data){
					return alert_error(data.responseText);
				}
			});
		}
		//进行签字操作
		if( f.canModi && 'fx_qz' == sign_type ){
			var column_json = ( typeof f.json != "object" ) ? {} : f.json;
			if( null != column_json && typeof column_json['退回'] != "undefined" && column_json['退回'].length ){
				$.prompt({
					title: '修改理由',
					errorText: '修改理由不能为空！',
					placeholder: '该原始记录经过退回，请输入修改理由',
					confirm: function(modi_reason){
						f.FN("yuanYin").val(modi_reason);
						assay_sign_submit(function(){
							f.FN("submit_flag").val('save_sign');
							f.assay_form.submit();
						});
					}
				});
			}else{
				$.confirm({
					content: '你确定保存并签字吗？',
					confirm: function(){
						f.FN("submit_flag").val('save_sign');
						assay_sign_submit(function(){
							f.FN("submit_flag").val('save_sign');
							f.assay_form.submit();
						});
					}
				});
			}
		}else{
			$.confirm({
				content: '你确定要签字吗？',
				confirm: function(){
					assay_sign_submit(function(){
						reloade_hyd(f.id,load_options,assay_type);
					});
				}
			});
		}
	}
	/**
	 * 功能：原始记录退回
	 * 作者：Mr Zhou
	 * 日期：2016-03-22
	 * 参数：f				[object]
	 * 参数：assay_type		[string]
	 * 参数：load_options	[string]
	 * 功能描述：
	*/
	var assay_return_back = function(f,assay_type,load_options){
		$.prompt({
			title: '【<strong class="red">'+f.assay_element+'</strong>】'+f.id+'号原始记录退回',
			placeholder: '请输入退回原因',
			errorText: '退回原因不能为空！',
			confirm: function(back_reason){
				$.ajax({
					type: 'post',dataType:'json',
					data: {
						ajax: 1,
						id: f.id,
						yuanYin: back_reason,
						assay_type: assay_type
					},
					url: trade_global.rooturl+'/huayan/ahlims.php?app=assay_sign&act=assay_return_back',
					success: function(data){
						if('0'==data.error){
							reloade_hyd(f.id,load_options,assay_type);
						}else{
							return alert_error(data.content);
						}
					},error: function(data){
						return alert_error(data.responseText);
					}
				});
			}
		});
	}
	/**
	 * 功能：创建曲线
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：因为这个方法在曲线列表页面新建曲线的时候也会用到，所以定义为了全局函数
	*/
	window.get_bzry_box = function(vid,sc_type,callBack){
		var open_sc_get_bzry_box = function(callBack){
			var sc_gb_box = $("#sc_get_bzry_box");
			sc_gb_box.modal("show");
			sc_gb_box.find(".sub_bzry").unbind("click").click(function(){
				var bzry_id = sc_gb_box.find("input[type=radio][name=bzry_id]:checked");
				if(bzry_id.length==0){
					$.alert({content:'<div class="alert alert-danger"><strong>请选择标准溶液</strong></div>'});
					return false;
				}
				sc_gb_box.modal("hide");
				var bzry_id		= bzry_id.val();
				var vid			= sc_gb_box.FN("vid","s").val();
				var wz_type		= sc_gb_box.find("[name=wz_type]").val();
				var valueC		= sc_gb_box.find("select[name=vid] option:selected").text();
				var table_name	= sc_gb_box.find("select[name=table_name]").val();
				callBack({vid:vid,valueC:valueC,wz_type:wz_type,table_name:table_name,bzry_id:bzry_id});
			});
		};
		if($("#sc_get_bzry_box").length==0){
			$.get(trade_global.rooturl+'/huayan/ahlims.php?ajax=1&app=quxian&act=getBzryBox&vid='+vid+'&sc_type='+sc_type,
				function(content){
					$("body").append(content);
					var sc_gb_box = $("#sc_get_bzry_box");
					if($.isNumeric(vid)){
						sc_gb_box.FN("vid","s").find("[value="+vid+"]").prop("selected","selected");
					}
					open_sc_get_bzry_box(callBack);
			});
		}else{
			open_sc_get_bzry_box(callBack);
		};
	}
	/**
	 * 功能：数据溯源
	 * 作者：Mr Zhou
	 * 日期：2015-10-03
	 * 功能描述：进行修改记录的存储
	*/
	var dataSuYuan = function(f,app){
		if(typeof trade_global.u != "object" ){
			return false;
		}
		var u = trade_global.u;
		//如果未传递合法参数则返回false
		if(typeof f != "object" || typeof app != "string"){
			return false;
		}
		if(typeof f.uid == "undefined"){
			if(f.userid == u.userid || f.userid2 == u.userid){
				f.uid = u.id;
			}else{
				f.uid = '';
			}
		}
		if(u.id == f.uid && '' != f.sign_01 && '' == f.sign_02){
			$.ajax({
				type: 'post',
				dataType: 'json',
				data: {id:f.id,content:f.prop("outerHTML"),ajax:1},
				url: trade_global.rooturl+'/huayan/ahlims.php?app='+app+'&act=dataSuYuan',
				success: function(data){
					if('1'==data.error){
						return alert_error(data.content);
					}
				},error: function(data){
					console.log(data.responseText);
				}
			});
		}
	}
	/**
	 * 功能：prompt
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：替代js原生prompt
	*/
	$.prompt = function(options){
		var defaults = {
			title: '',
			errorText: '',
			promptHtml: '',
			placeholder: '',
			cancel: function(){},
			confirm: function(){}
		};
		var prompt = $.extend({}, defaults, options);
		if(''!=prompt.promptHtml){
			var promptHtml = prompt.promptHtml;
		}else{
			var promptHtml = '<input type="text"autofocus="autofocus" name="prompt" placeholder="'+prompt.placeholder+'" autocomplete="off" class="form-control" />';
		}
		var ahBootBox_html = '<div style="z-index:99999999999" role="dialog" tabindex="-1" class="ahBootBox modal fade" data-backdrop="static"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button aria-hidden="true" class="close" type="button" data-bb-handler="cancel">×</button><h4 class="modal-title">'+prompt.title+'</h4></div><div class="modal-body">'+promptHtml+'<p class="text-danger" style="display:none;text-align:left;">'+prompt.errorText+'</p></div><div class="modal-footer"><button class="btn btn-primary btn-sm" type="button" data-bb-handler="confirm">确定</button><button class="btn btn-default btn-sm" type="button" data-bb-handler="cancel">取消</button></div></div></div></div>';
		if($(".ahBootBox").length==0){
			$("body").append(ahBootBox_html);
		}else{
			$(ahBootBox_html).replaceAll(".ahBootBox");
		}
		$(".ahBootBox").modal("show");
		setTimeout('$(".ahBootBox input[type=text]:first").select();',500);
		$(".ahBootBox").find("button[data-bb-handler=confirm]").unbind("click").click(function(){
			if(prompt.promptHtml == ''){
				var promptInput = $(".ahBootBox input[type=text]:first");
				if(promptInput.val() == ''){
					promptInput.select();
					$(".ahBootBox").find('.text-danger').show();
				}else{
					$(".ahBootBox").modal("hide");
					return prompt.confirm(promptInput.val());
				}
			}else{
				return prompt.confirm($(".ahBootBox"));
			}
		});
		$(".ahBootBox").find("button[data-bb-handler=cancel]").unbind("click").click(function(){
			$(".ahBootBox").modal("hide");
			prompt.cancel();
		});
	}
	/**
	 * 功能：
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：警告提示
	*/
	window.alert_warning = function(options,alert_obj){
		if(typeof options == "string"){
			var options = {massage:options};
		}
		if(arguments.length > 2){
			var Back = [];
			for(var i=2;i<=arguments.length;i++){
				Back[i-2] = arguments[i];
			}
		}
		var defaults = {
			title: '警告',
			cancel: function(){},
			confirm: function(B){}
		};
		var warn = $.extend({}, defaults, options);
		( typeof alert_obj == "object" ) && ( alert_obj.close() );
		warn.content = '<div class="alert alert-danger"><p style="text-align:left"><strong>警示信息：</strong></p>'+warn.massage+'</div>';
		$.confirm({
			title: warn.title,
			content: warn.content,
			icon: 'icon-warning-sign red bigger-130',
			cancel: function(){ warn.cancel(); },
			confirm: function(){ warn.confirm(Back); }
		});
	}
	/**
	 * 功能：成功提示
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：
	*/
	window.alert_ok = function(massage,alert_obj,sec){
		var ok_title = '操作成功';
		var ok_icon = 'icon-ok green bigger-130';
		var ok_content = '<div class="alert alert-success">'+massage+'</div>';
		var ok_autoClose = 'confirm|'+( (typeof sec == "undefined") ? 2000 : sec * 1000 );
		if(typeof alert_obj != "object"){
			$.alert({
				icon: ok_icon,
				title: ok_title,
				autoClose: ok_autoClose,
				content: ok_content
			});
		}else{
			alert_obj.setContent(ok_content);
			alert_obj.backgroundDismiss = true;
			$(alert_obj.$el).find('div.title').html('<i class="'+ok_icon+'"></i> '+ok_title);
			if(''==massage){
				alert_obj.close();
			}else{
				alert_obj.autoClose = ok_autoClose;
				alert_obj._startCountDown();
			}
		}
		return true;
	}
	/**
	 * 功能：错误显示
	 * 作者：Mr Zhou
	 * 日期：2015-10-06
	 * 功能描述：
	*/
	window.alert_error = function(massage,alert_obj){
		//为防止js污染，屏蔽返回错误提示里面的js代码
		//为防止输出json数据错误，仅截取json数据前的错误提示。
		//json返回数据的格式是 {"error":"0","html":"<script language。。。
		massage = massage.split('{"error":')[0].replace( /<script.*>(.*)<\/script>/g, "" );
		var error_title = '错误';
		var error_icon = 'icon-remove red bigger-130';
		var error_content = '<div class="alert alert-danger"><p style="text-align:left"><strong>错误信息：</strong></p>'+massage+'</div>';
		if(typeof alert_obj != "object"){
			$.alert({
				autoClose: false,
				icon: error_icon,
				title: error_title,
				content: error_content
			});
			return false;
		}else{
			alert_obj.backgroundDismiss = false;
			alert_obj.setContent(error_content);
			$(alert_obj.$el).find('div.title').html('<i class="'+error_icon+'"></i> '+error_title);
			return false;
		}
	}
	/**
	 * 功能：系统操作状态提示框
	 * 作者：Mr Zhou
	 * 日期：2018-08-06
	 * 功能描述：保存成功与失败的状态提示 alert_style error|success
	*/
	window.save_status_msg = function(msg,alert_style){
		clearTimeout(window.save_status_msg_myVar);
		var tpl = '<div class="save_status_msg alert-top-wrapper hide"><div class="alert alert-workaround alert-dismissable"><button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button><div class="content"><span class="glyphicon  glyphicon-ok"></span><span >操作成功。</span></div></div></div>';
		if(!$(".save_status_msg").length){
			$("body").append(tpl);
		}
		(!msg) && (msg='操作成功。');
		(!alert_style) && (alert_style='success');
		var content = '<span class="glyphicon  glyphicon-ok">&nbsp;</span><span >'+msg+'</span>';
		if(alert_style !== 'success'){
			var content = '<span >'+msg+'</span>';
		}
		$(".alert-workaround").addClass("alert-"+alert_style);
		$(".alert-top-wrapper").removeClass("hide").find(".content").html(content);
		window.save_status_msg_myVar = setTimeout(function(){
			$(".alert-top-wrapper").addClass("hide");
			$(".alert-workaround").removeClass("alert-"+alert_style);
		}, 3000);
	}
	// 先定义好颜色的顺序
	window.colorPalette = ["#0d6c54", "#c7983e", "#fb6929", "#b34f97", "#18a281", "#dbca34", "#b66b56", "#7a4271", "#446769", "#b7d02c", "#c6023a", "#cb7fb0", "#6c858c", "#ffb400", "#822f1d", "#bc69f7", "#1fcd92", "#918b3d", "#8b8b8b", "#875c89"];
	// 化验单计算公式显示
	window.jsgs_msg = function(cc){
		// 清空
		if('' == arguments[1]){
			// 计算公式参数索引,用来定义不同的颜色
			window.jsgs_arg_id = 0;
			$("span[data-name='vd0["+cc+"]']").attr('data-content', '');
			return;
		}
		try{
			var str = '';
			for(var i=1; i<arguments.length; i++){
				if(arguments[i]){
					var reg = /([a-zA-Z\$_][a-zA-Z\d_\[\]\'\"]*)/g;
					var match = arguments[i].match(reg);
					if(match){
						for(var j=0; j<match.length; j++){
							if(eval('typeof ' + match[j]) != "undefined"){
								var rep_str = eval(match[j]);
								var match_vd = match[j].match(/vd[\d]+/);
								if(match_vd){
									rep_str = "<span class='jsgs_arg' data-jsgs_arg_id='"+jsgs_arg_id+"' data-cc='"+cc+"' data-name='"+match_vd[0]+"' style='color:"+colorPalette[jsgs_arg_id]+"'>"+rep_str+"</span>";

									var cell = $("input[name='"+match_vd[0]+"["+cc+"]']").parents("td");
				// var formulaArea = document.createElement('div');
				// $(formulaArea).prop('class', 'formula-select-area');
				// $(formulaArea).css({
				// 	display: 'block',
				// 	'border-color': colorPalette[jsgs_arg_id],
				// 	width: $(cell).outerWidth() + 'px',
				// 	height: $(cell).outerHeight() + 'px',
				// 	top: $(cell).offset().top - 1 + 'px',
				// 	left: $(cell).offset().left - 1 + 'px'
				// });
				// $('body').append(formulaArea);

									jsgs_arg_id++;
								}
								arguments[i] = arguments[i].replace(match[j], rep_str)
							}
						}
					}
				}
				str += String(arguments[i]);
			}
			str += '<br />';
			var that = $("span[data-name='vd0["+cc+"]']");
			that.attr('data-content', that.attr('data-content') + str);
		}catch(e){
			console.log('计算公式显示函数,参数错误,错误如下:');
			console.log(e)
		}
	}
	// JS操作cookies
	// 写cookies
	window.setCookie = function(c_name, value, exdays){
		var d = new Date();
		// 默认缓存1个小时
		if( typeof exdays == "undefined" ){
			var expires = 0;
		}else{
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			var expires = d.toUTCString();
		}
		document.cookie = c_name+ "=" + escape(value) + ";expires=" + expires + ";path=/";
	}
	// 读取cookies
	window.getCookie = function(name){
		var arr,reg = new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr = document.cookie.match(reg)){
			return unescape(arr[2]);
		}else{
			return '';
		}
	}
	// 删除cookies
	window.delCookie = function(name){
		var exp = new Date();
		exp.setTime(exp.getTime() - 1);
		var cval = getCookie(name);
		if( '' != cval ){
			document.cookie= name + "="+cval+";expires="+exp.toGMTString();
		}
	}
	/**
	 * 功能：ajaxLogin
	 * 作者：Mr Zhou
	 * 日期：2016
	 * 功能描述：
	*/
	window.ajaxLogin = function(token_key,options){
		$(".jconfirm").remove();// 关闭$.alert
		var loginHtml = '<div class="alert alert-danger"><button data-dismiss="alert" class="close" type="button"><i class="icon-remove"></i></button><strong><i class="icon-warning-sign red"></i></strong>&nbsp;因长时间未操作您已退出系统，为了账户安全请重新确认登录信息！</div><form><label class="block clearfix"><span class="block input-icon input-icon-right"><input type="text" name="nickname" class="form-control" placeholder="用户名" value="'+trade_global.u.nickname+'" /><i class="icon-user"></i></span></label><label class="block clearfix"><span class="block input-icon input-icon-right"><input type="password" name="password" class="form-control" placeholder="密码" /><i class="icon-lock"></i></span></label></form>';
		$.prompt({
			title: '登录系统',
			errorText: '登录失败，请重试！',
			promptHtml: loginHtml,
			confirm: function(prompt){
				$.ahAjax({
					type: 'post',
					dataType: 'json',
					data: {
						ajax: 1,
						token_key: token_key,
						nickname: prompt.FN('nickname').val(),
						password: prompt.FN('password').val()
					},
					url: trade_global.rooturl+'/login.php',
					success: function(data){
						if(data.error == '0'){
							if(trade_global.u.id == data.uid){
								prompt.modal("hide");
								if( typeof options == "object" ){
									// 成功登陆后继续执行之前未完成的请求
									$.ahAjax(options);
								}
								$.alert({
									icon: 'icon-ok green bigger-130',
									title: '温馨提示',
									autoClose: 'confirm|2000',
									content: '<strong>登录成功！</strong>'
								});
							}else{
								window.open(trade_global.rooturl,'_top');
							}
						}else{
							prompt.find('.text-danger').show();
						}
					},error: function(data){
						return alert_error(data.responseText);
					}
				});
			}
		});
	}
})(jQuery);
// 克隆对象数组
function clone(obj){
    var o;
    switch(typeof obj){
    case 'undefined': break;
    case 'string'   : o = obj + '';break;
    case 'number'   : o = obj - 0;break;
    case 'boolean'  : o = obj;break;
    case 'object'   :
        if(obj === null){
            o = null;
        }else{
            if(obj instanceof Array){
                o = [];
                for(var i = 0, len = obj.length; i < len; i++){
                    o.push(clone(obj[i]));
                }
            }else{
                o = {};
                for(var k in obj){
                    o[k] = clone(obj[k]);
                }
            }
        }
        break;
    default:
        o = obj;break;
    }
    return o;
}
// 先替换jQuery原有的ajax方法
$.ahAjax = $.ajax;
// 重新定义jQuery的ajax方法
$.ajax = function(url, options){
	if ( typeof url === "object" ) {
		options = url;
		url = undefined;
	}
	// Force options to be an object
	options = options || {};
	if( typeof options.success == "function" ){
		// 将自定义的success方法提取出来
		options.jQueryAjaxSuccess = options.success;
		// 重新定义success方法，加入登陆判断
		options.success = function(data, textStatus, jqXHR){
			// 如果返回的错误状态为3表示登陆失效，需要重新登陆
			if( typeof data.error != "undefined" && data.error == '3' ){
				// 根据返回的令牌值进行重新登陆
				// 登陆成功后，重新执行之前未完成的ajax操作，之前的所有请求参数均在$(this)[0]里面
				ajaxLogin(data.token_key,$(this)[0]);
			}else{
				// 如果登陆未失效，则直接执行自定义的success方法
				$(this)[0].jQueryAjaxSuccess(data, textStatus, jqXHR);
			}
		};
	}
	return $.ahAjax(url, options);
};
$(document).ready(function(){
	$("body").append('<script type="text/javascript" src="'+trade_global.rooturl+'/js/bootstrap.min.js"></script>');
	//bootstrap模态框和select2合用时input无法获取焦点解决办法
	$.fn.modal.Constructor.prototype.enforceFocus = function() {};
});
// jExcel初始化方法
$.fn.jExcel_init = function(clientVars){
	var data = clientVars.data;
	var arowData = clientVars.arowData;
	var linesData = clientVars.linesData;
	data.wordWrap = false;
	data.showRowHeader = false;
	data.showColumnHeader = false;
	data.editable = arowData.canModi
	data.columnSorting = false;
	data.columnResize = false;
	data.rowResize = false;
	data.rowDrag = false;
	data.allowInsertRow = false;
	data.allowInsertColumn = false;
	data.allowManualInsertRow = false;
	data.allowManualInsertColumn = false;
	data.contextMenu = function(){return false;};
	if(data.config.fileType=='excel' && linesData){
		// 根据行数据个数关联更新配置信息
		data.tpl_data = [];
		data.tpl_style = [];
		// 模板数据，模板样式
		for (var i = 0; i < data.config.lineCount; i++) {
			var j = i + parseInt(data.config.lineNum);
			if(data.data[j] && data.cellDataArray[j]){
				// 数据模板
				data.tpl_data.push(clone(data.data[j]));
				// 样式模板
				data.tpl_style.push(clone(data.cellDataArray[j]))
			}
		}
		// 添加行数据
		if(data.tpl_data.length && data.tpl_style.length){
			data = add_line(data, linesData);
		}
	}
	// 新版数据更新
	for (var i = 0; i < data.cellDataArray.length; i++) {
		for (var j = 0; j < data.cellDataArray[i].length; j++) {
			var currData = data.cellDataArray[i][j];
			// 是否有数据绑定
			if(!currData.link){
				currData.readOnly = true;
				continue;
			}
			// 数据更新和解析
			// 化验单数据行对应index
			var lineIndex = Math.floor((i-data.config.lineNum)/data.config.lineCount);
			// 给vd0绑定修约函数
			if(currData.link[0] == 'linesData.vd0'){
				data.cellDataArray[i][j].mask = 'round_hyd_vd0';
				data.cellDataArray[i][j].maskOptions = {
					cellId: j + '-' + i,
					lineIndex: lineIndex
				}
			}
			// 更新计算公式
			if(arowData.canModi && data.data[i][j].indexOf('=') > -1){
				currData.readOnly = false;
				eval('var match = data.data[i][j].match(/[a-zA-Z]+' + (parseInt(data.config.lineNum)+1) + '/g);');
				for (var k = 0; match && k < match.length; k++) {
					data.data[i][j] = data.data[i][j].replace(match[k], match[k].replace(/[0-9]+/i, i+1));
				}
				continue;
			}
			if(linesData && linesData[lineIndex]){
				linesData[lineIndex].xuhao = lineIndex + 1;
				currData.link[0] = currData.link[0].replace('linesData', 'linesData['+ lineIndex +']');
			}
			if(currData.format){
				var rep_arr = [];
				for (var k = 0; k < currData.link[0].split(',').length; k++) {
					rep_arr.push('(arowData\\.[a-zA-Z0-9_]+)');
				}
				eval('currData.link[0] = currData.link[0].replace(/'+ rep_arr.join('\\,') +'/g, currData.format)');
				match = currData.link[0].match(/\{([a-zA-Z0-9_\.]{10,})\}/g);
				for (var k = 0; match && k < match.length; k++) {
					currData.link[0] = currData.link[0].replace(match[k], eval(match[k].replace(/[\{\}]+/g, '')));
				}
				data.data[i][j] = currData.link[0];
			}else{
				data.data[i][j] = eval(currData.link[0]);
			}
		}
	}
	return $(this).jexcel(data);
}
// 添加行数据
function add_line(data, linesData){
	// 加载数据
	for (var i = 0; i < linesData.length; i++) {
		var rows = linesData[i];
		var newData = data.tpl_data.concat();
		var newTplStyle = clone(data.tpl_style);
		for (var j = 0; j < newData.length; j++) {
			var index = data.config.lineNum + i*data.config.lineCount + j;
			if(i==0){
				data.data[index] = newData[j].concat();
				data.cellDataArray[index] = clone(newTplStyle[j]);
			}else{
				// splice在数组指定位置index处插入元素
				data.data.splice(index, 0, newData[j].concat());
				data.cellDataArray.splice(index, 0, clone(newTplStyle[j]));
				// 给新数据行追加自定义高度
				data.rowHeights.splice(index, 0, clone(data.rowHeights[j+data.config.lineNum]));
			}
		}
		if(i==0){
			continue;
		}
		// 模板中的单元格合并情况
		var start_num = parseInt(data.config.lineNum)+parseInt((i-1)*data.config.lineCount);
		var end_num = parseInt(start_num)+parseInt(data.config.lineCount)-1;
		data.config.lineNumEnd =parseInt(data.config.lineNum)+parseInt(data.config.lineCount);
		var oldSpans = clone(data.spans)
		for (var si = 0; si < oldSpans.length; si++) {
			var col = parseInt(oldSpans[si].col);
			var row = parseInt(oldSpans[si].row);
			if(row>=data.config.lineNum && row <data.config.lineNumEnd){
				var newSpans = clone(oldSpans[si]);
				newSpans.row = parseInt(newSpans.row) + parseInt(i*data.config.lineCount);
				data.spans.push(clone(newSpans));
			}else if(row > end_num){
				data.spans[si].row = row + parseInt(data.config.lineCount);
			}
		}
	}
	return data;
}
$(document).ready(function(){
	// 扩展BootstrapTable加载成功后添加字段说明
	if(!$.fn.bootstrapTable){
		return;
	}
	$.fn.bootstrapTable.methods.push('columnsAddTooltipInfo');
	$.fn.bootstrapTable.Constructor.prototype.columnsAddTooltipInfo = function () {
		if($('#'+this.$body.context.id).find('.glyphicon.tooltip-info').length){
			return;
		}
		for(k=0; k< this.options.columns[0].length; k++){
			var column = this.options.columns[0][k];
			if(column.tooltipInfo){
				$('#'+this.$body.context.id + ' [data-field='+column.field+'] .th-inner').append('&nbsp;<a href="javascript:void(0);" class="glyphicon glyphicon-question-sign tooltip-info" style="cursor: pointer;" data-original-title="'+column.title+'" data-rel="popover" data-content="<p>'+column.tooltipInfo+'<p>" data-animation="true" data-placement="right" data-trigger="hover focus"></a>');
			}
		}
		$('#'+this.$body.context.id + ' [data-rel=popover]').popover({html:true});
	};
})