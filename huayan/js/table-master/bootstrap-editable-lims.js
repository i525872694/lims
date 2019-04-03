// x-editable 设置保留位数插件
/*$('#Blws').editable({
    url: '/post',
    type: 'Blws',
    title: '设置保留位数',
    // <1，<10, <100, ≥100
    value: [2,1,0,0]
});*/
(function($) {
    "use strict";
    // 保留位数设置
    var Blws = function(options) {
        this.init('Blws', options, Blws.defaults);
    };
    $.fn.editableutils.inherit(Blws, $.fn.editabletypes.abstractinput);
    $.extend(Blws.prototype, {
        render: function() {
            this.$input = this.$tpl.find('input');
        },
        value2html: function(value, element) {
            if (!value) {
                $(element).empty();
                return;
            }
            var html = $('<div>').text(value[0]).html() + ', ' + $('<div>').text(value[1]).html() + ',' + $('<div>').text(value[2]).html() + ',' + $('<div>').text(value[3]).html();
            $(element).html(html);
        },
        html2value: function(html) {
            return null;
        },
        value2str: function(value) {
            var str = '';
            if (value) {
                for (var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },
        str2value: function(str) {
            return str;
        },
        value2input: function(value) {
            if (!value) {
                return;
            }
            this.$input.filter('[name="v0"]').val(value[0]);
            this.$input.filter('[name="v1"]').val(value[1]);
            this.$input.filter('[name="v2"]').val(value[2]);
            this.$input.filter('[name="v3"]').val(value[3]);
        },
        input2value: function() {
            return [this.$input.filter('[name="v0"]').val(), this.$input.filter('[name="v1"]').val(), this.$input.filter('[name="v2"]').val(), this.$input.filter('[name="v3"]').val()];
        },
        activate: function() {
            this.$input.filter('[name="v0"]').focus();
        },
        autosubmit: function() {
            this.$input.keydown(function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });
    Blws.defaults = $.extend({},
    $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-Blws"><label><span>0≤&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="number" min="0" max="10" name="v0" class="input-mini">&nbsp;<1</label></div>' + '<div class="editable-Blws"><label><span>1≤&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="number" min="0" max="10" name="v1" class="input-mini">&nbsp;<10</label></div>' + '<div class="editable-Blws"><label><span>10≤&nbsp;&nbsp;&nbsp;</span><input type="number" min="0" max="10" name="v2" class="input-mini">&nbsp;<100</label></div>' + '<div class="editable-Blws"><label><span>100≤&nbsp;</span><input type="number" min="0" max="10" name="v3" class="input-mini">&nbsp;≤&nbsp;<span style="font-size:25px;">∞</span></label></div>',

        inputclass: ''
    });
    $.fn.editabletypes.Blws = Blws;
    // 修约规则设定
    // 2018-08-22
    var Round_rule = function(options) {
        this.init('round_rule', options, Round_rule.defaults);
    };
    $.fn.editableutils.inherit(Round_rule, $.fn.editabletypes.abstractinput);
    $.extend(Round_rule.prototype, {
        render: function() {
            this.$input = this.$tpl.find('[name]');
        },
        value2html: function(value, element) {
            if (!value) {
                $(element).empty();
                return;
            }
            var br = '', html = '';
            for (var i = 0; i < value.blws.length; i++) {
                html += br + '≥' + value.blws[i][0] + '：' + value.blws[i][1];
                br = '<br />';
            }
            $(element).html(html);
            return html;
        },
        html2value: function(html) {
            return null;
        },
        value2str: function(value) {
            var str = '';
            if (value) {
                for (var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },
        value2input: function(value) {
            if (!value) {
                return;
            }
            // 程序版本
            this.$input.filter('[name="version"]').val(value.version);
            // 最大小数位数
            this.$input.filter('[name="max_num"]').val(value.max_num);
            // 修约方式 小数位数|有效位数
            this.$input.filter('[name="round_type"][value='+value.round_type+']').prop("checked", true);
            // 进舍规则，四舍五入|四舍六入五考虑
            this.$input.filter('[name="round_function"][value='+value.round_function+']').prop("checked", true);
            // 数据修约位数区间设置
            for (var i = 0; i < value.blws.length; i++) {
                this.$input.filter('[name="xiuyue_qujian['+i+']"]').val(value.blws[i][0]);
                this.$input.filter('[name="xiuyue_weishu['+i+']"]').val(value.blws[i][1]);
            }
        },
        input2value: function() {
            var data = {}, that = this;
            data.type = 'round_rule';
            // 程序版本
            data.version = this.$input.filter('[name="version"]').val();
            // 最大小数位数
            data.max_num = this.$input.filter('[name="max_num"]').val();
            // 修约方式 小数位数|有效位数
            data.round_type = this.$input.filter('[name="round_type"]:checked').val();
            // 进舍规则，四舍五入|四舍六入五考虑
            data.round_function = this.$input.filter('[name="round_function"]:checked').val();
            // 数据修约位数区间设置
            data.blws = [];
            var i = 0;
            this.$input.filter('[name^="nongdu_qujian"]').each(function(n){
                var qujian = $(this).val();
                var weishu = that.$input.filter('[name^="xiuyue_weishu"]').eq(n).val();
                if($.isNumeric(qujian) && $.isNumeric(weishu)){
                    data.blws[i++] = [qujian, weishu];
                }
            });
            if(data.blws.length){
                // 根据数据区间从大到小排序
                data.blws.sort(function(a, b){
                    return a[0]-b[0];
                });
            }
            return data;
        },
        activate: function() {
            var that = this;
            this.$input.filter('[name]:first').focus();
            this.$input.filter('[name="round_type"]').unbind("change").on("change", function(){
                var value = that.$input.filter('[name="round_type"]:checked').val();
                var label_str = (value == 'yxws') ? '有效位数：' : '修约间隔：';
                that.$input.filter('[name^="xiuyue_weishu"]').prev().html(label_str);
            }).trigger("change");
        },
        autosubmit: function() {
            this.$input.keydown(function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });
    Round_rule.defaults = $.extend({},
    $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-input"><input type="hidden" name="version" value=""><!--<div class="editable-round_rule"><label>进舍规则：</label><label><input type="radio" name="round_function" value="round" /><span> 四舍五入</span></label><label><input type="radio" name="round_function" value="_round" /><span> 四舍六入五考虑</span></label></div><div class="editable-round_rule"><label>保留方式：</label><label><input type="radio" name="round_type" value="xsws" /><span> 小数位数</span></label><label><input type="radio" name="round_type" value="yxws" /><span> 有效位数</span></label></div><div class="editable-round_rule"><label><span>最大位数：</span><input type="number" name="max_num" min="0" max="10" class="input-small" /></label></div>--><div class="editable-round_rule"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[0]" min="0" max="1000" value="0" required="required" class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[0]" min="-10" max="10" step="1" required="required" class="input-mini" /></label></div><div class="editable-round_rule"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[1]" min="0" max="1000" class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[1]" min="-10" max="10" step="1" class="input-mini" /></label></div><div class="editable-round_rule"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[2]" min="0" max="1000" class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[2]" min="-10" max="10" step="1" class="input-mini" /></label></div><div class="editable-round_rule"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[3]" min="0" max="1000" class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[3]" min="-10" max="10" step="1" class="input-mini" /></label></div><div class="editable-round_rule"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[4]" min="0" max="1000" class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[4]" min="-10" max="10" step="1" class="input-mini" /></label></div></div>',
        inputclass: ''
    });
    // 修改bootstrap-editable的validate默认认函数，验证修约规则数据的有效性
    $.fn.editable.defaults.validate = function(value) {
        if(typeof value !== 'object' || value.type != 'round_rule'){
            return;
        }
        console.log(value)
        if(!value.blws.length){
            return '至少设置一个浓度区间及修约位数。';
        }
        if(value.blws[0][0] != 0){
            return '第一个区间必须设置为≥0。';
        }
        var blws_obj = [],blws_step = [], has_error = false, error_mag = '',that = this;
        for(var i=0; i<value.blws.length; i++){
            var qujian = value.blws[i][0];
            var weishu = value.blws[i][1];
            if( $.inArray(qujian, blws_step) >= 0 ){
                return '数据区间"'+qujian+'"重复。';
            }
            blws_step[i] = qujian;
        };
        return;
    }
    $.fn.editabletypes.round_rule = Round_rule;
    // 曲线CR值修约规则设置插件
    // 2018-11-16
    var Sccr_round = function(options) {
        this.init('sccr_round', options, Sccr_round.defaults);
    };
    $.fn.editableutils.inherit(Sccr_round, $.fn.editabletypes.abstractinput);
    $.extend(Sccr_round.prototype, {
        render: function() {
            this.$input = this.$tpl.find('[name]');
        },
        value2html: function(value, element) {
            if (!value) {
                $(element).empty();
                return;
            }
            var br = '', html = '';
            for (var i = 0; i < value.blws.length; i++) {
                html += br + '≥' + value.blws[i][0] + '：' + value.blws[i][1];
                br = '<br />';
            }
            $(element).html(html);
            return html;
        },
        html2value: function(html) {
            return null;
        },
        value2str: function(value) {
            var str = '';
            if (value) {
                for (var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },
        value2input: function(value) {
            if (!value) {
                return;
            }
            // 程序版本
            this.$input.filter('[name="version"]').val(value.version);
            // 最大系数
            this.$input.filter('[name="max_num"]').val(value.max_num);
            // 进舍规则，四舍五入|四舍六入五考虑
            this.$input.filter('[name="round_function"][value='+value.round_function+']').prop("checked", true);
            // 数据修约位数区间设置
            // this.$input.filter('[name="xiuyue_qujian[0]"]').val(0);
            this.$input.filter('[name="xiuyue_weishu[0]"]').val(value.blws[0][1]);
        },
        input2value: function() {
            var data = {}, that = this;
            data.type = 'sccr_round';
            // 程序版本
            data.version = this.$input.filter('[name="version"]').val();
            // 最大系数
            data.max_num = this.$input.filter('[name="max_num"]').val();
            // 进舍规则，四舍五入|四舍六入五考虑
            data.round_function = this.$input.filter('[name="round_function"]:checked').val();
            // 数据修约位数区间设置
            data.blws = [];
            var i = 0;
            this.$input.filter('[name^="nongdu_qujian"]').each(function(n){
                var qujian = $(this).val();
                var weishu = that.$input.filter('[name^="xiuyue_weishu"]').eq(n).val();
                if($.isNumeric(qujian) && $.isNumeric(weishu)){
                    data.blws[i++] = [qujian, weishu];
                }
            });
            if(data.blws.length){
                // 根据数据区间从大到小排序
                data.blws.sort(function(a, b){
                    return a[0]-b[0];
                });
            }
            return data;
        },
        activate: function() {
            // code
        },
        autosubmit: function() {
            this.$input.keydown(function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });
    Sccr_round.defaults = $.extend({},
    $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-input"><input type="hidden" name="version" value=""><div class="editable-sccr_round"><label>相关系数Ｒ值进舍规则：</label></div><div><label><input type="radio" name="round_function" value="floor" /><span>只舍不入</span></label><br /><label><input type="radio" name="round_function" value="round" /><span>四舍五入</span></label><br /><label><input type="radio" name="round_function" value="_round" /><span>四舍六入五考虑</span></label></div><div class="editable-sccr_round"><label><span>最大系数<a href="javascript:void(0);" class="glyphicon glyphicon-question-sign" data-rel="tooltip" title="设置R值的最大值'+"\n"+'如果质控要求R值不允许超过3个9可设置为0.999,'+"\n"+'如果修约后结果≥1时如果不需要修约或者不允许含有小数位数时R值都可以强制修约为1"></a>：</span><input type="number" name="max_num" min="0" max="2" class="input-small" />(默认值为空)</label></div><div class="editable-sccr_round"><label><span>浓度区间：≥</span><input type="number" name="nongdu_qujian[0]" min="0" max="1000" value="0" required="required" readonly class="input-mini" /></label><label><span>修约间隔：</span><input type="number" name="xiuyue_weishu[0]" min="-10" max="10" step="1" required="required" class="input-mini" /></label></div></div>',
        inputclass: ''
    });
    $.fn.editabletypes.sccr_round = Sccr_round;
    // 质控计算时检测值选择
    // 2018-12-25
    var Use_data = function(options) {
        this.init('use_data', options, Use_data.defaults);
    };
    $.fn.editableutils.inherit(Use_data, $.fn.editabletypes.abstractinput);
    $.extend(Use_data.prototype, {
        render: function() {
            this.$input = this.$tpl.find('[name]');
        },
        value2html: function(value, element) {
            console.log(value)
            if (!value) {
                $(element).empty();
                return;
            }
            var list = {
                '_vd0': '原始结果',
                'vd0': '修约后结果'
            };
            var html = list[value] || value;
            $(element).html(html);
            return html;
        },
        html2value: function(html) {
            return null;
        },
        value2str: function(value) {
            var str = '';
            str = value;
            return str;
        },
        value2input: function(value) {
            if (!value) {
                return;
            }
            if(!this.$input.filter('[name="other_data"]').html()){
                for (let index = 0; index <= 28; index++) {
                    var opt = '<option value="vd'+index+'">vd'+index+'</option>';
                    this.$input.filter('[name="other_data"]').append(opt);
                }
            }
            // 检测值选择
            if($.inArray(value, ['vd0', '_vd0']) == -1){
                this.$input.filter('[name="other_data"]').val(value);
                this.$input.filter('[name="use_data"]').eq(2).val(value);
            }
            this.$input.filter('[name="use_data"][value='+value+']').prop("checked", true);
        },
        input2value: function() {
            var that = this;
            // 检测值选择
            return this.$input.filter('[name="use_data"]:checked').val();
        },
        activate: function() {
            var that = this;
            this.$input.filter('[name="other_data"]').unbind("change").on("change", function(){
                that.$input.filter('[name="use_data"],[name="other_data"]').eq(2).prop("checked", true);
                var value = that.$input.filter('[name="use_data"],[name="other_data"]').eq(2).val($(this).val());
            });
        },
        autosubmit: function() {
            this.$input.keydown(function(e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });
    Use_data.defaults = $.extend({},
    $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-input"><div class="editable-use_data"></div><div><label><input type="radio" name="use_data" value="_vd0" /><span>原始结果</span></label><br /><label><input type="radio" name="use_data" value="vd0" /><span>修约后结果</span></label><br /><label><input type="radio" name="use_data" value="" /><span>其他字段：</span><select name="other_data"></select></label></div></div>',
        inputclass: ''
    });
    $.fn.editabletypes.use_data = Use_data;
} (window.jQuery));