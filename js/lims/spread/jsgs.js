/*
 *　化验单计算公式包管理
 *　Copyright (c) 2015 Mr zhou (zhouguangli@anheng.com.cn)
*/
"use strict";
$.getArgComment = function(jsgs){
    var pattern1 = new RegExp("\\{(.| |\n)+?\\}","igm");
        var pattern2 = new RegExp("\argumentsDefaults = \{(.| |\n)+?\\}","igm");
        var matchArg = jsgs.toString().match(pattern2)[0]
                            .match(pattern1)[0]
                            .replace(/[ \{\}]/gi, '')
                            .replace(/:(.| |\n)+?\/\//gi, ':')
                            .split("\n");
        var argComment = {};
        for(var i=0; i< matchArg.length; i++){
            if(!matchArg[i]){
                continue;
            }
            var arr = matchArg[i].split(":");
            argComment[arr[0]] = arr[1];
        }
        return argComment;
};
$.jsgs = {
    add: function(o){
        var argumentsDefaults = {
            name: "加法计算（简单公式配置测试用）",//函数名称
            argA: null,// 参数１
            argB: null,// 参数２
            chengJi: null,// 乘积
            hanLiang: null//含量
        };
        if(typeof o == undefined && typeof o.argA == undefined){
            return argumentsDefaults;
        }
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        o.hanLiang = accAdd(o.argA, o.argB);
        o.chengJi = accMul(o.argA, o.argB);
        return o;
    },
    yiQiZaiRu: function(o){
        var argumentsDefaults = {
            name: "仪器载入",//函数名称
            yiQiDuShu: null,// 仪器读数
            xiShiBeiShu: null,// 稀释倍数
            nongSuoBeiShu: null,// 浓缩倍数
            hanLiang: null//含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        if(!$.isNumeric(o.yiQiDuShu)){
            return o;
        }
        if($.isNumeric(o.xiShiBeiShu)){
            o.hanLiang = accDiv(o.yiQiDuShu, o.xiShiBeiShu);
        }else if($.isNumeric(o.xiShiBeiShu)){
            o.hanLiang = accMul(o.yiQiDuShu, o.nongSuoBeiShu);
        }else{
            o.hanLiang = o.yiQiDuShu;
        }
        return o;
    },
    fenGuangGuangDuFa: function (o){
        var argumentsDefaults = {
            name: "分光光度法",//函数名称
            CA: null,// 截距
            CB: null,// 斜率
            quXianDanWei: null,// 曲线单位
            quXianLeiXing: null,// 曲线单位
            biaoYeNongDu: null,//标液浓度
            xiShiBeiShu: null,// 稀释倍数
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            xiGuangDu01: null,// 吸光度A₁
            xiGuangDu02: null,// 吸光度A₂
            kongBai: null,// 空白值A₀
            xiGuangDuJunZhi: null,// 吸光度均值A
            xiGuangDuJianKongBai: null,// 吸光度减空白(A-A₀)
            hanLiang: null// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        !o.CA && (o.CA = o.hyd.CA);
        !o.CB && (o.CB = o.hyd.CB);
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg/L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.kongBai);
        var b = $.isNumeric(o.xiGuangDu01);
        var c = $.isNumeric(o.xiGuangDu02);
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
        }
        if(b && c){
            o.xiGuangDuJunZhi = accDiv(accAdd(o.xiGuangDu01, o.xiGuangDu02),2);
        }
        var e = $.isNumeric(o.xiGuangDuJunZhi);
        if( a && e ){
            // 吸光度减空白A-A₀的值
            o.xiGuangDuJianKongBai = accsub(o.xiGuangDuJunZhi, o.kongBai);
            // 将吸光度减空白带入回归方程计算
            o.hanLiang = accDiv(accsub(o.xiGuangDuJianKongBai, o.CA), o.CB);
            // 根据曲线单位判断曲线打点的数据类型,然后再进行相关数据转换
            switch(o.quXianDanWei){
                case 'µg':
                case 'mg':
                    o.hanLiang = accDiv(o.hanLiang, o.quYangTiJi);
                    break;
                case 'mL':
                    o.hanLiang = accMul(o.hanLiang, o.biaoYeNongDu);
                    o.hanLiang = accDiv(o.hanLiang, o.quYangTiJi);
                    break; 
                default :
                    // ['度', 'NTU', 'mg/L', 'µg/mL']
                    o.xiShiBeiShu = accDiv(o.dingRongTiJi, o.quYangTiJi);
                    o.hanLiang = accMul(o.hanLiang, o.xiShiBeiShu);
            }
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = o.xiGuangDuJunZhi = o.xiGuangDuJianKongBai = '';
        }
        return o;
    },
    ziWaiFenGuangGuangDuFa: function (o){
        var argumentsDefaults = {
            name: "紫外分光光度法(总氮)",//函数名称
            CA: null,// 截距
            CB: null,// 斜率
            quXianDanWei: null,// 曲线单位
            quXianLeiXing: null,// 曲线单位
            biaoYeNongDu: null,//标液浓度
            xiShiBeiShu: null,// 稀释倍数
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            kongBai: null,// 空白值Ab
            xiGuangDu220: null,// 吸光度As₂₂₀
            xiGuangDu275: null,// 吸光度As₂₇₅
            xiGuangDuAs: null,// As(As220-2As275)
            xiGuangDuJianKongBai: null,// 吸光度减空白Ar(As-Ab)
            hanLiang: null// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        !o.CA && (o.CA = o.hyd.CA);
        !o.CB && (o.CB = o.hyd.CB);
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg／L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.kongBai);
        var b = $.isNumeric(o.xiGuangDu220);
        var c = $.isNumeric(o.xiGuangDu275);
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
        }
        if(b && c){
            o.xiGuangDuAs = accsub(o.xiGuangDu220, accMul(2, o.xiGuangDu275));
        }
        var e = $.isNumeric(o.xiGuangDuAs);
        if( a && e ){
            // 吸光度减空白As-Ab的值
            o.xiGuangDuJianKongBai = accsub(o.xiGuangDuAs, o.kongBai);
            // 将吸光度减空白带入回归方程计算
            o.hanLiang = accDiv(accsub(o.xiGuangDuJianKongBai, o.CA), o.CB);
            // 根据曲线单位判断曲线打点的数据类型,然后再进行相关数据转换
            switch(o.quXianDanWei){
                case 'µg':
                case 'mg':
                    o.hanLiang = accDiv(o.hanLiang, o.quYangTiJi);
                    break;
                case 'mL':
                    o.hanLiang = accMul(o.hanLiang, o.biaoYeNongDu);
                    o.hanLiang = accDiv(o.hanLiang, o.quYangTiJi);
                    break; 
                default :
                    // ['度', 'NTU', 'mg/L', 'µg/mL']
                    o.xiShiBeiShu = accDiv(o.dingRongTiJi, o.quYangTiJi);
                    o.hanLiang = accMul(o.hanLiang, o.xiShiBeiShu);
            }
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = o.xiGuangDuAs = o.xiGuangDuJianKongBai = '';
        }
        return o;
    },
    zhongLiangFa_1: function (o){
        var argumentsDefaults = {
            name: "重量法 溶解性总固体，悬浮物",//函数名称
            quYangTiJi: null,// 取样体积
            B1:null, //始重1
            B2:null, //始重2
            B3:null, //始重3
            B:null, //恒重B
            A1:null, //终重1
            A2:null, //终重2
            A3:null, //终重3
            A:null, //恒重A
            A_B:'', //A-B
            HCO3:'', //HCO₃⁻
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.B1).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 4 : val_split[1].length;
        o.B = '';
        if($.isNumeric(o.B1) && $.isNumeric(o.B2)){
            if(Math.abs(accsub(o.B1, o.B2)) <= 0.0004){
                o.B = o.B2;
                o.B3 = '';
            }else if($.isNumeric(o.B3)){
                if(Math.abs(accsub(o.B1, o.B3)) <= 0.0004 || Math.abs(accsub(o.B2, o.B3)) <= 0.0004){
                    o.B = o.B3;
                }
            }
        }
        o.A = '';
        if($.isNumeric(o.A1) && $.isNumeric(o.A2)){
            if(Math.abs(accsub(o.A1, o.A2)) <= 0.0004){
                o.A = o.A2;
                o.A3 = '';
            }else if($.isNumeric(o.A3)){
                if(Math.abs(accsub(o.A1, o.A3)) <= 0.0004 || Math.abs(accsub(o.A2, o.A3)) <= 0.0004){
                    o.A = o.A3;
                }
            }
        }
        // 
        if($.isNumeric(o.B) && $.isNumeric(o.A)){
            o.A_B = roundjs(accsub(o.A, o.B), baoLiuWeiShu);
        }
        if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.A_B)){
            o.hanLiang = accDiv(accMul(o.A_B, 1000000), o.quYangTiJi);
            if(o.vid.toString() != '567'){
                o.HCO3 = '-';
            }else if($.isNumeric(o.HCO3)){
                o.hanLiang = accAdd(o.hanLiang,accDiv(o.HCO3,2))
            }
        }else{
            o.hanLiang = '';
        }
        return o;
    },
    rongLiangFa_1: function (o){
        var argumentsDefaults = {
            name: "容量法 二氧化碳，总硬度，化学需氧量",//函数名称
            biaoYeNongDu:null,//标液浓度
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            diDingZhongDian1:null,// 滴定终点1
            diDingShiDian1:null,// 滴定始点1
            diDingYongLiang1:null,// 滴定用量1
            diDingZhongDian2:null,// 滴定终点2
            diDingShiDian2:null,// 滴定始点2
            diDingYongLiang2:null,// 滴定用量2
            diDingYongLiangPingJunZhi:'',// 滴定用量均值
            kongBai:'',// 空白
            junZhiJianKongBai:'',// 均值减空白
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        if(!o.vid){
            return console.log("未定义vid");
        }
        // 取样体积格式验证
        if(!$.isNumeric(o.quYangTiJi)){
            o.hanLiang = '';
            return o;
        }
        var kongBai = $.isNumeric(o.kongBai) ? o.kongBai : '0.00';
        //容量法的计算一般是：水样的含量=（标准溶液用量-空白）*标准溶液浓度 *  当量 / 取样体积
        var fzl = {
            103: 100.1, //总硬度
            114: 8, //溶解氧
            118: 8, //化学需氧量COD
            126: 22, //二氧化碳
            127: 44, //游离二氧化碳
            128: 22, //侵蚀性二氧化碳钙
            173: 40.08, //钙
            182: 35.45 //氯化物
        };
        var vid = o.vid;
        // 中间过程值设置默认保留位数
        var val_split = String(o.diDingZhongDian1).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDian1);
        var b = $.isNumeric(o.diDingShiDian1);
        var c = $.isNumeric(o.diDingZhongDian2);
        var d = $.isNumeric(o.diDingShiDian2);
        var dl_fzl = fzl[vid] ? fzl[vid] : 1; // 分子量
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
        }
        var xishi = accDiv(o.dingRongTiJi,o.quYangTiJi);
        if(a && b){
            o.diDingYongLiang1 = roundjs(accsub(o.diDingZhongDian1, o.diDingShiDian1),baoLiuWeiShu);
        }
        if(c && d){
            o.diDingYongLiang2 = roundjs(accsub(o.diDingZhongDian2, o.diDingShiDian2),baoLiuWeiShu);
        }
        if( $.isNumeric(o.diDingYongLiang1) && $.isNumeric(o.diDingYongLiang2) ){
            // 结果计算
            o.diDingYongLiangPingJunZhi=roundjs(accDiv(accAdd(o.diDingYongLiang1, o.diDingYongLiang2),2),baoLiuWeiShu);
            o.junZhiJianKongBai = Math.abs(accsub(o.diDingYongLiangPingJunZhi,kongBai)).toString();
            o.hanLiang = accMul(accDiv(accMul(accMul(o.junZhiJianKongBai,o.biaoYeNongDu),dl_fzl), o.dingRongTiJi),xishi);
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = '';
        }
        return o;
    },
    rongLiangFa_2: function (o){
        var argumentsDefaults = {
            name: "容量法 高锰酸盐指数",//函数名称
            biaoYeNongDu:null,//标液浓度
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            diDingZhongDian1:null,// 滴定终点1
            diDingShiDian1:null,// 滴定始点1
            diDingYongLiang1:null,//滴定用量1
            diDingZhongDian2:null,// 滴定终点2
            diDingShiDian2:null,// 滴定始点2
            diDingYongLiang2:null,//滴定用量2
            V2:null,//V2
            K:null,//K
            // f:null,//f
            VKongBai:null,//V空白
            hanLiang: null// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var vid=1;
        var val_split = String(o.diDingZhongDian1).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDian1);
        var b = $.isNumeric(o.diDingShiDian1);
        var c = $.isNumeric(o.diDingZhongDian2);
        var d = $.isNumeric(o.diDingShiDian2);
        var dl_fzl=1;
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
            var f=1;
        }else if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi))
        {
            var f=accDiv(accsub(o.dingRongTiJi,o.quYangTiJi),o.dingRongTiJi);
        }
        var e = $.isNumeric(o.diDingYongLiang1);
        if(e)
        {
            e = $.isNumeric(o.diDingYongLiang1);
        }
        else if(a && b){
            o.diDingYongLiang1 = jsws(accsub(o.diDingZhongDian1, o.diDingShiDian1),baoLiuWeiShu);
            e = $.isNumeric(o.diDingYongLiang1);
        }
        if(c && d){
            o.diDingYongLiang2 = jsws(accsub(o.diDingZhongDian2, o.diDingShiDian2),baoLiuWeiShu);
        }
        var g = $.isNumeric(o.diDingYongLiang2);
        if(o.K)
        {
            o.K=o.K;
        }
        else
        {
            o.K=accDiv(10,o.V2);
        }
        if( e && g ){
            // 结果计算
            o.diDingYongLiangPingJunZhi=jsws(accDiv(accAdd(o.diDingYongLiang1, o.diDingYongLiang2),2),baoLiuWeiShu);
            var zhi1=accsub(accMul(accAdd(10,o.diDingYongLiangPingJunZhi),o.K),10);
            var zhi2=accMul(accsub(accMul(accAdd(10,o.VKongBai),o.K),10),f);
            var zhi=accsub(zhi1,zhi2);
            o.hanLiang = accDiv(accMul(accMul(zhi,o.biaoYeNongDu),8000),o.quYangTiJi);
            o.hanLiang = jsws(o.hanLiang);
        }else if(e){
            var zhi1=accsub(accMul(accAdd(10,o.diDingYongLiang1),o.K),10);
            var zhi2=accMul(accsub(accMul(accAdd(10,o.VKongBai),o.K),10),f);
            var zhi=accsub(zhi1,zhi2);
            o.hanLiang = accDiv(accMul(accMul(zhi,o.biaoYeNongDu),8000),o.quYangTiJi);
            o.hanLiang = jsws(o.hanLiang);
        }
        else
        {
            o.hanLiang = null;
        }
        return o;
    },
    //容量法 五日生化需氧量
    rongLiangFa_3: function (o){
        var argumentsDefaults = {
            name: "容量法 五日生化需氧量",//函数名称
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            biaoYeNongDu:null,//标液浓度
            diDingZhongDian:null,// 五日前滴定终点
            diDingShiDian:null,// 五日前滴定始点
            diDingYongLiang:null,//五日前滴定用量
            diDingZhongDianW:null,// 五日后滴定终点
            diDingShiDianW:null,// 五日后滴定始点
            diDingYongLiangW:null,//五日后滴定用量
            diDingYongLiangCha:null,//五日前滴定用量-五日后滴定用量
            C1: null,//C1
            C2:null,//C2
            C3: null,//C3
            C4:null,//C4
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.diDingZhongDian).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDian);
        var b = $.isNumeric(o.diDingShiDian);
        var c = $.isNumeric(o.diDingZhongDianW);
        var d = $.isNumeric(o.diDingShiDianW);
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
            var f1=1;
            var f2=1;
        }else if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi))
        {
            var f1=accDiv(accsub(o.dingRongTiJi,o.quYangTiJi),o.dingRongTiJi);
            var f2=accDiv(o.quYangTiJi,o.dingRongTiJi);
        }
        if(a && b){
            o.diDingYongLiang = jsws(accsub(o.diDingZhongDian, o.diDingShiDian),baoLiuWeiShu);
        }
        var e = $.isNumeric(o.diDingYongLiang);
        if(c && d){
            o.diDingYongLiangW = jsws(accsub(o.diDingZhongDianW,o.diDingShiDianW),baoLiuWeiShu);
        }
        var g = $.isNumeric(o.diDingYongLiangW);
        if( e && g ){
            // 结果计算
            o.diDingYongLiangCha=accsub(o.diDingYongLiang,o.diDingYongLiangW);
            // o.C1=jsws(accDiv(accMul(accMul(32,o.biaoYeNongDu),o.diDingYongLiang),400),baoLiuWeiShu);
            // o.C2=jsws(accDiv(accMul(accMul(32,o.biaoYeNongDu),o.diDingYongLiangW),400),baoLiuWeiShu);

            // vs['vd12']= roundjs(accMul(accMul(0.0808,bzyr),avg1),4);
            o.C1=roundjs(accMul(accMul(0.0808,o.biaoYeNongDu),o.diDingYongLiang),baoLiuWeiShu);
            o.C2=roundjs(accMul(accMul(0.0808,o.biaoYeNongDu),o.diDingYongLiangW),baoLiuWeiShu);
        }
        if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi)){
            //f1 = (Vt-Ve)/Vt
            var Ve = parseFloat(o.quYangTiJi);
            var Vt = parseFloat(o.dingRongTiJi);
            var f1 = accDiv(accsub(Vt,Ve),Vt);
            //f2 = Ve/Vt
            var f2 = accDiv(Ve,Vt);
            if(parseFloat(o.quYangTiJi) != parseFloat(o.dingRongTiJi)){
                if(!$.isNumeric(o.C3) || !$.isNumeric(o.C4)){
                    o.hanLiang = '';
                    return o;
                }
                var p3 = parseFloat(o.C3);
                var p4 = parseFloat(o.C4);
                //f1*(p3-p4)  稀释水的溶解氧
                var f1_3_4 = accMul(f1,accsub(p3,p4));
            }else{
                var f1_3_4 = 0;
            }
            //p1-p2
            var p1_p2  = accsub(parseFloat(o.diDingYongLiang),parseFloat(o.diDingYongLiangW));
            //(p1_p2-f1_3_4)/f2
            var jg = accDiv(accsub(p1_p2,f1_3_4),f2);
            o.hanLiang = jsws(jg);
        }else{
            o.hanLiang = '';
        }
        return o;
    },
    //分光-叶绿素
    fenguang_yls: function (o){
        var argumentsDefaults = {
            name: "分光-叶绿素",//函数名称
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            biSeMinGuiGe: null,//比色皿规格L
            xiGuangDu750:null,//吸光度750
            xiGuangDu664:null,//吸光度664
            xiGuangDu647:null,//吸光度647
            xiGuangDu630:null,//吸光度630
            hanLiang: null// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
         var o = $.extend({}, argumentsDefaults, o);
         // 中间过程值设置默认保留位数
         var val_split = String(o.xiGuangDu750).split('.');
         var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
         var a=o.xiGuangDu750;
         var b=o.xiGuangDu664;
         var c=o.xiGuangDu647;
         var d=o.xiGuangDu630;
        if( a && b&& c&& d ){
            // 结果计算
            var zhi1=accMul(accsub(o.xiGuangDu664,o.xiGuangDu750),11.85);
            var zhi2=accMul(accsub(o.xiGuangDu647,o.xiGuangDu750),1.54);
            var zhi3=accMul(accsub(o.xiGuangDu630,o.xiGuangDu750),0.08);
            var zhi=accsub(accsub(zhi1,zhi2),zhi3);
            o.hanLiang = accDiv(accMul(zhi,o.dingRongTiJi),accMul(o.quYangTiJi,o.biSeMinGuiGe));
            o.hanLiang = jsws(o.hanLiang);
        }
        else
        {
            o.hanLiang  = '';
        }
        return o;
    },
    //仪器-五日生化需氧量
    yq_bod5: function (o){
        var argumentsDefaults = {
            name: "仪器-五日生化需氧量",//函数名称
            quYangTiJi: null,// 取样体积
            dingRongTiJi: null,// 定容体积
            biaoYeNongDu:null,//标液浓度
            C1: null,//C1
            C2:null,//C2
            C3: null,//C3
            C4:null,//C4
            C1C2:null,//C1-C2
            hanLiang: null// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.C1).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.C1);
        var b = $.isNumeric(o.C2);
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && !$.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
            var f1=1;
            var f2=1;
        }else if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi))
        
        {
            var f1=accDiv(accsub(o.dingRongTiJi,o.quYangTiJi),o.dingRongTiJi);
            var f2=accDiv(o.quYangTiJi,o.dingRongTiJi);
        }
        if( a && b ){
            // 结果计算
            o.C1C2=jsws(accsub(o.C1,o.C2),val_split);
            o.hanLiang = accDiv(accsub(accsub(o.C1,o.C2),accMul(f1,accsub(o.C3,o.C4))),f2);
            o.hanLiang = jsws(o.hanLiang);
        }
        else
        {
            o.hanLiang  = null;
        }
        return o;
    },
    //22号文件夹下原始记录表-Ⅱ
    fenGuangGuangDuFa_2: function (o){
        var argumentsDefaults = {
            name: "22(总磷、氰化物（含土壤）、硫化物、LAS、丁基黄原酸原始记录表格-Ⅱ)分光光度法",//函数名称
            CA: '',// 截距
            CB: '',// 斜率
            quXianDanWei: '',// 曲线单位
            quXianLeiXing: '',// 曲线类型
            quYangTiJi: '',// 取样体积V
            liuChuYeTiJi: '',//馏出液体积V1
            biSeTiJi: '',// 比色体积V2
            kongBai: '',// 空白值A₀
            xiGuangDu: '',// 吸光度A
            xiGuangDuJianKongBai: '',//减空白后吸光度A-A₀
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg/L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.kongBai);
        var b = $.isNumeric(o.xiGuangDu);
        var c = $.isNumeric(o.liuChuYeTiJi);
        var d = $.isNumeric(o.biSeTiJi);
        var e = $.isNumeric(o.quYangTiJi);
        if( a && b && c && d && e ){
            // 吸光度减空白A-A₀的值
            o.xiGuangDuJianKongBai = accsub(o.xiGuangDu, o.kongBai);
            // 将吸光度减空白带入回归方程计算
            o.hanLiang = accMul(accDiv(accsub(o.xiGuangDuJianKongBai, o.CA), o.CB),accDiv(o.liuChuYeTiJi,accMul(o.biSeTiJi,o.quYangTiJi)));
            // 根据曲线单位判断曲线打点的数据类型,然后再进行相关数据转换
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = o.xiGuangDu = o.xiGuangDuJianKongBai = '';
        }
        return o;
    },
    //8溶解氧(滴定法)
    diDingFa: function (o){
        var argumentsDefaults = {
            name: "8溶解氧(滴定法)",//函数名称
            quXianDanWei: '',// 曲线单位
            quXianLeiXing: '',// 曲线类型
            quYangTiJi: '',// 取样体积
            biaoZhunRongYeNongDu: '',//标准溶液浓度
            diDingZhongDu: '',//滴定终读
            diDingShiDu: '',//滴定始读
            diDingYongLiang: '',//滴定用量       
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg/L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDu);
        var b = $.isNumeric(o.diDingShiDu);
        var c = $.isNumeric(o.quYangTiJi);
        var e = $.isNumeric(o.biaoZhunRongYeNongDu);
        if( a && b && c && e ){
            // 滴定用量
            o.diDingYongLiang = accsub(o.diDingZhongDu, o.diDingShiDu);
            //计算结果
            o.hanLiang = accDiv(accMul(accMul(o.biaoZhunRongYeNongDu,o.diDingYongLiang),8000),o.quYangTiJi);
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = '';
        }
        return o;
    },
    //5化学需氧量(容量法)
    rongLiangFa_5: function (o){
        var argumentsDefaults = {
            name: "5容量法化学需氧量",//函数名称
            biaoYeNongDu: '',// 标液浓度
            quYangTiJi: '',// 取样体积
            dingRongTiJi: '',// 定容体积
            diDingZhongDian: '',// 滴定终点
            diDingShiDian: '',// 滴定始点
            diDingYongLiang: '',// 滴定用量
            diDingKongBai: '',// 滴定空白
            xiShiBeiShu: '',// 稀释倍数
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.diDingZhongDian).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDian);
        var b = $.isNumeric(o.diDingShiDian);
        var c = $.isNumeric(o.diDingKongBai);
        var d = $.isNumeric(o.biaoYeNongDu);
        var e = $.isNumeric(o.quYangTiJi);
        var f = $.isNumeric(o.xiShiBeiShu);
        // 定容体积
        if(f){
            o.dingRongTiJi = jsws(accMul(o.quYangTiJi,o.xiShiBeiShu),baoLiuWeiShu);       
        }else{
            o.dingRongTiJi = o.quYangTiJi;
        }
        if( a && b && c && d && e ){
            o.diDingYongLiang = jsws(accsub(o.diDingZhongDian, o.diDingShiDian),baoLiuWeiShu);
            //计算结果
            o.hanLiang = accMul(accDiv(accMul(accMul(accsub(o.diDingKongBai,o.diDingYongLiang),o.biaoYeNongDu),8000),o.quYangTiJi),o.xiShiBeiShu);
            o.hanLiang = jsws(o.hanLiang);
        }
        return o;
    },
    //仪器法
    yiQiFa: function (o){
        var argumentsDefaults = {
            name: "仪器法(22,21,9,5,2,13,6)",//函数名称
            yiQiShiZhi: '',// 仪器示值
            xiShiBeiShu: '',// 稀释倍数
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.yiQiShiZhi).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.yiQiShiZhi);
        var b = $.isNumeric(o.xiShiBeiShu);
        if( a && b ){
            //计算结果
            o.hanLiang = accMul(o.yiQiShiZhi,o.xiShiBeiShu);
            o.hanLiang = jsws(o.hanLiang);
        }
        return o;
    },
    //21总氮测定
    zongDanCeDing: function (o){
        var argumentsDefaults = {
            name: "21总氮测定",//函数名称
            CA: '',// 截距
            CB: '',// 斜率
            quXianDanWei: '',// 曲线单位
            quXianLeiXing: '',// 曲线类型
            xiShiBeiShu: '',// 稀释倍数
            quYangTiJi: '',// 取样体积
            dingRongTiJi: '',// 定容体积
            kongBai: '',// 空白值Ab
            xiGuangDu220: '',// 吸光度A₂₂₀
            xiGuangDu275: '',// 吸光度A₂₇₅
            xiGuangDuAs: '',// As(A220-2A275)
            xiGuangDuJianKongBai: '',// 吸光度减空白Ar(As-Ab)
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg/L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.kongBai);
        var b = $.isNumeric(o.xiGuangDu220);
        var c = $.isNumeric(o.xiGuangDu275);
        var d = $.isNumeric(o.quYangTiJi);
        var e = $.isNumeric(o.xiShiBeiShu);
        // 取样体积和定容体积
        if(d && e){
            o.dingRongTiJi = accMul(o.quYangTiJi,o.xiShiBeiShu);
        }
        if(b && c){
            o.xiGuangDuAs = accsub(o.xiGuangDu220, accMul(2, o.xiGuangDu275));
        }
        var f = $.isNumeric(o.xiGuangDuAs);
        if( a && f ){
            // 吸光度减空白As-Ab的值
            o.xiGuangDuJianKongBai = accsub(o.xiGuangDuAs, o.kongBai);
            // 将吸光度减空白带入回归方程计算
            o.hanLiang = accDiv(accMul(accsub(o.xiGuangDuJianKongBai, o.CA), o.xiShiBeiShu),accMul(o.CB,o.quYangTiJi));
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = o.xiGuangDuAs = o.xiGuangDuJianKongBai = '';
        }
        return o;
    },
    //2氨氮(滴定法)
    diDingFa_2: function (o){
        var argumentsDefaults = {
            name: "2氨氮(滴定法)",//函数名称
            quXianDanWei: '',// 曲线单位
            quXianLeiXing: '',// 曲线类型
            quYangTiJi: '',// 取样体积
            biaoZhunRongYeNongDu: '',//标准溶液浓度
            diDingKongBai: '',// 滴定空白
            diDingZhongDu: '',//滴定终读
            diDingShiDu: '',//滴定始读
            diDingYongLiang: '',//滴定用量       
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 曲线单位赋默认值
        !o.quXianDanWei && (o.quXianDanWei = 'mg/L');
        // 曲线类型,决定X和Y表示的内容,类型是2时,互换XY的值
        // if( '2'  == o.quXianLeiXing ){
        //    [o.CA, o.CB] = [o.CB, o.CA];
        // }
        // 中间过程值设置默认保留位数
        var val_split = String(o.xiGuangDu01).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDu);
        var b = $.isNumeric(o.diDingShiDu);
        var c = $.isNumeric(o.quYangTiJi);
        var e = $.isNumeric(o.biaoZhunRongYeNongDu);
        var f = $.isNumeric(o.diDingKongBai);
        // 滴定用量
        if(a && b){
            o.diDingYongLiang = accsub(o.diDingZhongDu, o.diDingShiDu);
        }
        if( a && b && c && e && f ){ 
            //计算结果
            o.hanLiang = accMul(accMul(accDiv(accsub(o.diDingYongLiang,o.diDingKongBai),o.quYangTiJi),o.biaoZhunRongYeNongDu),14010);
            o.hanLiang = jsws(o.hanLiang);
        }else{
            o.hanLiang = '';
        }
        return o;
    },
    //4高锰酸盐
    gaoMengSuanYan: function (o){
        var argumentsDefaults = {
            name: "容量法 高锰酸盐指数",//函数名称
            biaoYeNongDu: '',// 标液浓度
            quYangTiJi: '',// 取样体积
            dingRongTiJi: '',// 定容体积
            xiShiBeiShu: '',// 稀释倍数
            diDingBiaoZhunZhongDu1: '',// 滴定标准终读1
            diDingBiaoZhunShiDu1: '',// 滴定标准始读1
            diDingBiaoZhunYongLiang1: '',//滴定标准用量1
            diDingBiaoZhunZhongDu2: '',// 滴定标准终读2
            diDingBiaoZhunShiDu2: '',// 滴定标准始读2
            diDingBiaoZhunYongLiang2: '',//滴定标准用量2
            diDingZhongDian: '',// 滴定终点
            diDingShiDian: '',// 滴定始点
            diDingYongLiang: '',//滴定用量
            K: '',//K
            VKongBai: '',// V空白
            hanLiang: ''// 含量
        };
        if(!arguments.length || typeof o != "object"){
            return argumentsDefaults;
        }
        // 整合参数
        var o = $.extend({}, argumentsDefaults, o);
        // 中间过程值设置默认保留位数
        var val_split = String(o.diDingZhongDian1).split('.');
        var baoLiuWeiShu = (val_split.length != 2) ? 2 : val_split[1].length;
        var a = $.isNumeric(o.diDingZhongDian1);
        var b = $.isNumeric(o.diDingShiDian1);
        var c = $.isNumeric(o.diDingBiaoZhunZhongDu1);
        var d = $.isNumeric(o.diDingBiaoZhunShiDu1);
        var g = $.isNumeric(o.diDingBiaoZhunZhongDu2);
        var h = $.isNumeric(o.diDingBiaoZhunShiDu2);
        //K值计算
        if( c && d ){
            o.diDingBiaoZhunYongLiang1 = accsub(o.diDingBiaoZhunZhongDu1,o.diDingBiaoZhunShiDu1);
            var k1 = accDiv(10,o.diDingBiaoZhunYongLiang1);
        }
        if( g && h ){
            o.diDingBiaoZhunYongLiang2 = accsub(o.diDingBiaoZhunZhongDu2,o.diDingBiaoZhunShiDu2);
            var k2 = accDiv(10,o.diDingBiaoZhunYongLiang2);
        }
        var k = accDiv(accAdd(k1,k2),2);
        // 取样体积和定容体积
        if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi)){
            o.dingRongTiJi = o.quYangTiJi;
            var f=1;
        }else if($.isNumeric(o.quYangTiJi) && $.isNumeric(o.dingRongTiJi))
        {
            var f=accDiv(accsub(o.dingRongTiJi,o.quYangTiJi),o.dingRongTiJi);
        }
        if(a && b){
            o.diDingYongLiang = jsws(accsub(o.diDingZhongDian, o.diDingShiDian),baoLiuWeiShu);
            var e = $.isNumeric(o.diDingYongLiang);
        }
        if(e && f == 1){
            var zhi=accsub(accMul(accAdd(10,o.diDingYongLiang),o.K),10);
            o.hanLiang = accDiv(accMul(accMul(zhi,o.biaoYeNongDu),8000),o.quYangTiJi);
            o.hanLiang = jsws(o.hanLiang);
        }else if(e && f !== 1){
            var zhi1=accsub(accMul(accAdd(10,o.diDingYongLiang),o.K),10);
            var zhi2=accMul(accsub(accMul(accAdd(10,o.VKongBai),o.K),10),f);
            var zhi=accsub(zhi1,zhi2);
            o.hanLiang = accDiv(accMul(accMul(zhi,o.biaoYeNongDu),8000),o.quYangTiJi);
            o.hanLiang = jsws(o.hanLiang);
        }
        else
        {
            o.hanLiang = null;
        }
        return o;
    },
};