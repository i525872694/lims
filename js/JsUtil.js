//扩展方法
/**
* 去掉前后空格
* " dd ".trim() == "dd"
*/
String.prototype.trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

/**
* 去掉左空格
* " dd".leftTrim() == "dd"
*/
String.prototype.leftTrim = function() {
    return this.replace(/(^\s*)/g, "");
}

/**
* 去掉右空格
* "dd ".rightTrim() == "dd"
*/
String.prototype.rightTrim = function() {
    return this.replace(/(\s*$)/g, "");
}

/**
* 去掉尾部的指定字符
* "dd,,".trimEnd(",") == "dd"
*/
String.prototype.trimEnd = function(str) {
    return this.replace(eval("/" + str + "+$/gi"), "");
}

/**
* 字符串转Date
* "2007-2-28 10:18:30".ToDate()==new Date("2007/2/28 10:18:30")
* IE new Date("2007-2-28 10:18:30")会变null
*/
String.prototype.toDate = function() {
    return new Date(this.replace(/-/g, "/"));
}

/**
* 替换全部字符串
*/
String.prototype.replaceAll = function(AFindText, ARepText) {
    var raRegExp = new RegExp(AFindText.replace(/([\(\)\[\]\{\}\^\$\+\-\*\?\.\"\'\|\/\\])/g, "\\$1"), "ig");
    return this.replace(raRegExp, ARepText);
}

/**      
* 对Date的扩展，将 Date 转化为指定格式的String      
* 月(M)、日(d)、12小时(h)、24小时(H)、分(m)、秒(s)、周(E)、季度(q) 可以用 1-2 个占位符      
* 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)      
* eg:      
* (new Date()).pattern("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423      
* (new Date()).pattern("yyyy-MM-dd E HH:mm:ss") ==> 2009-03-10 二 20:09:04      
* (new Date()).pattern("yyyy-MM-dd EE hh:mm:ss") ==> 2009-03-10 周二 08:09:04      
* (new Date()).pattern("yyyy-MM-dd EEE hh:mm:ss") ==> 2009-03-10 星期二 08:09:04      
* (new Date()).pattern("yyyy-M-d h:m:s.S") ==> 2006-7-2 8:9:4.18      
*/
Date.prototype.format = function(fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份         
        "d+": this.getDate(), //日         
        "h+": this.getHours() % 12 == 0 ? 12 : this.getHours() % 12, //小时         
        "H+": this.getHours(), //小时         
        "m+": this.getMinutes(), //分         
        "s+": this.getSeconds(), //秒         
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度         
        "S": this.getMilliseconds() //毫秒         
    };
    var week = {
        "0": "一",
        "1": "二",
        "2": "三",
        "3": "四",
        "4": "五",
        "5": "六",
        "6": "七"
    };
    if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    if (/(E+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, ((RegExp.$1.length > 1) ? (RegExp.$1.length > 2 ? "星期" : "周") : "") + week[this.getDay() + ""]);
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
}


/*DataUtil 时间工具*/
var __DataUtil = {};
/**
* 比较两时间是否相等
* "dd ".rightTrim() == "dd"
*/
__DataUtil.equal = function(date1, date2) {
    return date1.getTime() == date2.getTime();
}


/*
*   功能:实现VBScript的DateAdd功能.
*   参数:interval,字符串表达式，表示要添加的时间间隔.  年:y 季:q 月:m 周:w 天:d 小时:h 分:m 秒:s
*   参数:number,数值表达式，表示要添加的时间间隔的个数.
*   参数:date,时间对象.
*   返回:新的时间对象.
*   var   now   =   new   Date();
*   var   newDate   =   __DataUtil.dateAdd(now,"d",5);
eg. //    var now = new Date();
//    //加五天.
//    var newDate = __DataUtil.dateAdd(now,"d", 5);
//    alert(newDate.format("yyyy年MM月dd日08时"))
//    //加两个月.
//    newDate = __DataUtil.dateAdd(now,"m", 2);
//    alert(newDate.toLocaleDateString())
//    //加一年
//    newDate = __DataUtil.dateAdd(now,"y", 1);
//    alert(newDate.toLocaleDateString()) 
*---------------   DateAdd(interval,number,date)   -----------------
*/
__DataUtil.dateAdd = function(date, interval, number) {
    var tempDate = new Date(date.format("yyyy-MM-dd hh:mm:ss").replaceAll("-", "/"));
    switch (interval) {
        case "y":
            tempDate.setFullYear(tempDate.getFullYear() + number);
            return tempDate;
        case "q":
            tempDate.setMonth(tempDate.getMonth() + number * 3);
            return tempDate;
        case "m":
            tempDate.setMonth(tempDate.getMonth() + number);
            return tempDate;
        case "w":
            tempDate.setDate(tempDate.getDate() + number * 7);
            return tempDate;
        case "d":
            tempDate.setDate(tempDate.getDate() + number);
            return tempDate;
        case "h":
            tempDate.setHours(tempDate.getHours() + number);
            return tempDate;
        case "m ":
            tempDate.setMinutes(tempDate.getMinutes() + number);
            return tempDate;
        case "s ":
            tempDate.setSeconds(tempDate.getSeconds() + number);
            return tempDate;
        default:
            tempDate.setDate(d.getDate() + number);
            return tempDate;
    }
    //    switch (interval) {
    //        case "y":
    //            date.setFullYear(date.getFullYear() + number);
    //            return date;
    //        case "q":
    //            date.setMonth(date.getMonth() + number * 3);
    //            return date;
    //        case "m":
    //            date.setMonth(date.getMonth() + number);
    //            return date;
    //        case "w":
    //            date.setDate(date.getDate() + number * 7);
    //            return date;
    //        case "d":
    //            date.setDate(date.getDate() + number);
    //            return date;
    //        case "h":
    //            date.setHours(date.getHours() + number);
    //            return date;
    //        case "m ":
    //            date.setMinutes(date.getMinutes() + number);
    //            return date;
    //        case "s ":
    //            date.setSeconds(date.getSeconds() + number);
    //            return date;
    //        default:
    //            date.setDate(d.getDate() + number);
    //            return date;
    //    }
}



//iframe
//在iframe子页面中获取当前iframe的id
var __IframeUtil = {};
__IframeUtil.getCurIframeID = function() {
    var frameId = window.frameElement && window.frameElement.id || '';
    return frameId;
}

//在iframe子页面中获取当前iframe的宽
__IframeUtil.getCurIframeWidth = function() {
    return document.documentElement.scrollWidth;
}

//获取Iframe的window对象,
//eg. 1. 操作控件 __IframeUtil.getIframeWindow('ifr1').document.getElementById('txt').value='2222'; 
//    2. 调用方法 __IframeUtil.getIframeWindow('ifr1').fun();
__IframeUtil.getIframeWindow = function(iframeID) {
    return document.getElementById(iframeID).contentWindow;
}


/*__StringUtil 字符串处理工具*/
var __StringUtil = {}
/**
* format方法
* __String.format("I Love {0}, and You Love {1}","you","me")
*/
__StringUtil.format = function() {
    if (arguments.length == 0)
        return null;

    var str = arguments[0];
    for (var i = 1; i < arguments.length; i++) {
        var re = new RegExp('\\{' + (i - 1) + '\\}', 'gm');
        str = str.replace(re, arguments[i]);
    }
    return str;
}

/*__JsUtil  常用方法*/
var __JsUtil = {};
/*
*判断对象是否为 空( null,undefined,"")
*/
__JsUtil.isNullOrEmpty = function(obj) {
    if (obj == undefined
     || obj == null
     || obj.toString().trim() == "") {
        return true;
    }
    return false;
}
/*
* 判断字符串、数组、对象是否为空（null,undefined,"",{},[]）
*/
__JsUtil.isEmpty = function(obj, flag) {
    if (flag == true) {//为true时，第一个参数必须为string类型,用来判断全局的数据
        try {
            if (typeof (obj) != "undefined") {
                var o = eval(obj);
                return __JsUtil.isEmpty(o);
            }
            return true;
        } catch (ex) {
            return true;
        }
    }
    if (obj != undefined) {
        switch (typeof (obj)) {
            case "string":
                if (obj != null && obj.length > 0) {
                    return false;
                }
                break;
            case "object":
                if (Object.prototype.toString.call(obj) === '[object Array]') {
                    //数组
                    if (obj.length > 0) {
                        return false;
                    }
                } else {
                    //对象
                    var flog = true;
                    for (var key in obj) {
                        flog = false;
                    }
                    return flog;
                }
                break;
            default:
                if (obj != null) {
                    return false;
                }
                break;
        }
    }
    return true;
}
/*
*获取地址栏某个参数
* __JsUtil.GetQuery("key1");
*/
__JsUtil.GetQuery = function(strName) {
    var strHref = location.href;
    var intPos = strHref.indexOf("?");
    var strRight = strHref.substr(intPos + 1);
    var arrTmp = strRight.split("&");
    for (var i = 0; i < arrTmp.length; i++) {
        var arrTemp = arrTmp[i].split("=");
        if (arrTemp[0].toUpperCase() == strName.toUpperCase()) return arrTemp[1];
    }
    return "";
}

/*
*获取地址栏所有参数
* __JsUtil.GetAllQuery(); 返回一个object
*/
__JsUtil.GetAllQuery = function() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = decodeURI(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}

/*__DataVail  数据验证*/
var __DataVail = {}
__DataVail.regexEnum =
{
    int: "^-?[1-9]\\d*$", //整数
    intZ: "^[1-9]\\d*$", //正整数
    intF: "^-[1-9]\\d*$", //负整数
    num: "^([+-]?)\\d*\\.?\\d+$", //数字
    numZ: "^[1-9]\\d*|0$", //正数（正整数 + 0）
    numF: "^-[1-9]\\d*|0$", //负数（负整数 + 0）
    decimal: "^([+-]?)\\d*(\\.\\d+)?$", //浮点数
    decimalZ: "^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*$", //正浮点数
    decimalF: "^-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*)$", //负浮点数
    decimal3: "^-?([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0)$", //浮点数
    decimal4: "^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0$", //非负浮点数（正浮点数 + 0）
    decimal5: "^(-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*))|0?.0+|0$", //非正浮点数（负浮点数 + 0）
    email: "^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$", //邮件
    color: "^[a-fA-F0-9]{6}$", //颜色
    url: "^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$", //url
    chinese: "^[\\u4E00-\\u9FA5\\uF900-\\uFA2D]+$", //仅中文
    ascii: "^[\\x00-\\xFF]+$", //仅ACSII字符
    zipcode: "^\\d{6}$", //邮编
    mobile: "^(13|15|18)[0-9]{9}$", //手机
    ip4: "^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$", //ip地址
    notempty: "^\\S+$", //非空
    picture: "(.*)\\.(jpg|bmp|gif|ico|pcx|jpeg|tif|png|raw|tga)$", //图片
    rar: "(.*)\\.(rar|zip|7zip|tgz)$", //压缩文件
    date: "^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$", //日期
    qq: "^[1-9]*[1-9][0-9]*$", //QQ号码
    tel: "^(([0\\+]\\d{2,3}-)?(0\\d{2,3})-)?(\\d{7,8})(-(\\d{3,}))?$", //电话号码的函数(包括验证国内区号,国际区号,分机号)
    username: "^\\w+$", //用来用户注册。匹配由数字、26个英文字母或者下划线组成的字符串
    letter: "^[A-Za-z]+$", //字母
    letter_u: "^[A-Z]+$", //大写字母
    letter_l: "^[a-z]+$", //小写字母
    idcard: "^[1-9]([0-9]{14}|[0-9]{17})$"    //身份证
}

/*
*验证, 值不区别大小写	
*__DataVail.RegExp("^-?[1-9]\\d*$", -123)
*/
__DataVail.RegExp = function(strReg, val) {
    return new RegExp(strReg, "i").test(val);
}

/*
* 验证字符串是否为整数	
* __DataVail.isInt(-123) 或 __DataVail.isInt("-123") 
*/
__DataVail.isInt = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.int, val);
}

/*
* 验证字符串是否为浮点数	
* __DataVail.isDecimal(-123) 或 __DataVail.isDecimal(-123.124) 
*/
__DataVail.isDecimal = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.decimal, val);
}

/*
* 验证字符串是否邮箱
* __DataVail.isEmail("123aabb@qq.com")
*/
__DataVail.isEmail = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.email, val);
}

/*
* 验证字符串是否 由数字、英文字母或者下划线组成的字符串 (用于编号字段验证)
*/
__DataVail.isCode = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.username, val);
}

/*
* 验证字符串是否身份证
*/
__DataVail.idCard = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.idcard, val);
}

/*
* 验证字符串是否手机
*/
__DataVail.idMobile = function(val) {
    return __DataVail.RegExp(__DataVail.regexEnum.mobile, val);
}

/***对象类型判断****/
/**
* Check for string
* @param {Object} s
*/
__JsUtil.isString = function isString(s) {
    return typeof s === 'string';
}

/**
* Check for object
* @param {Object} obj
*/
__JsUtil.isObject = function isObject(obj) {
    return typeof obj === 'object';
}

/**
* Check for array
* @param {Object} obj
*/
__JsUtil.isArray = function isArray(obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
}

/**
* Check for number
* @param {Object} n
*/
__JsUtil.isNumber = function isNumber(n) {
    return typeof n === 'number';
}

__JsUtil.HtmlDecode = function HtmlDecode(str) {
    var out = new Array();
    var arr;
    var re = /\&\#(\d+)\;/gm;
    var nIndex = 0;
    while ((arr = re.exec(str)) != null) {
        out.push(str.substring(nIndex, arr.index));
        out.push(String.fromCharCode(parseInt(RegExp.$1)));
        nIndex = arr.lastIndex;
    }
    out.push(str.substr(nIndex));
    return out.join("");
}

/*Post方式打开Window
*data:数据. [{Key:"key1",Value:"value1"},...]
*/
__JsUtil.OpenPostWindow = function(url, data, name) {
    var fromId = "ff" + new Date().getTime();
    var form = '<form id="' + fromId + '" method="post" action="' + url + '" target="' + name + '" ></form>';
    $("body").append(form);

    /*添加Post的数据*/
    var postHtml = [];
    for (var property in data) {
        postHtml.push('<input type="hidden" name="' + property + '" value="' + encodeURIComponent(data[property]) + '"/>');
    }
    $("#" + fromId).append(postHtml.join(""));
    $("#" + fromId).submit();
    $("#" + fromId).remove();
};
/*
* id 为空时将生产默认的随机id 
* 打开easyui的弹出窗 返回值为窗体的id，要关闭的话需要调用：$('#' + id).window('close');
* 注意：本方法需要手动引用easyui
*/
__JsUtil.OpenWindows = function(id, title, width, height, msg, isUrl, closeCallback) {
    if (id == undefined || id == "") {
        id = "windows_" + new Date().getTime();
    }
    if (width > $(window).width()) {
        width = $(window).width();
    }
    if (height > $(window).height()) {
        height = $(window).height();
    }
    if ($('#' + id).length < 1) {
        var content = "";
        if (isUrl) {
            content = '<iframe id="' + id + '_iframe" scrolling="auto" style="border: 0px; float: left;overflow: auto;width:' + (width - 13) + 'px;height:' + (height - 34) + 'px" frameborder="0"></iframe>'; //<div class="LoadingDiv LoadingImg LoadingBgForIf" style="left:5px;top:25px;width:' + (width - 13) + 'px;height:' + (height - 34) + 'px"></div>
        } else {
            content = msg;
        }
        var WindowDiv = ' <div id="' + id + '" class="easyui-window" title="' + title + '" style=" overflow:hidden; " data-options="iconCls:\'icon-save\',modal:true,closed:true,collapsible:false,minimizable:false,resizable:false,maximizable:false">' + content + '</div>';
        $('body').append($(WindowDiv));
    }
    $('#' + id).window({
        width: width,
        height: height,
        modal: true,
        onBeforeClose: function() { //当面板关闭之前触发的事件
            if (closeCallback != undefined) {
                closeCallback();
            }
        },
        onClose: function() {
            $('#' + id).window('destroy');
        },
        onOpen: function() {
            //添加iframe 加载前添加加载进度条 wwh
            if (isUrl) {
                var ifameObj = document.getElementById(id + "_iframe");
                ifameObj.src = msg;
//                if (ifameObj.attachEvent) {
//                    ifameObj.attachEvent("onload", function() {
//                        $(".LoadingBgForIf").remove();
//                    });
//                } else {
//                    ifameObj.onload = function() {
//                        $(".LoadingBgForIf").remove();
//                    };
//                }
            }
        }
    });
    $('#' + id).window('open');
    return id;
};



/*-------------------------wwh-------------------------------------*/
__JsUtil.AjaxFunc = function(url) { //传入url地址返回一个以这个地址作为Ajax请求的 Ajax调用方法
    return function(parm, func, async, dataType) {
        if (async == undefined) { async = true; } //默认异步调用
        if (dataType == undefined) { dataType = "json"; }
        $.ajax({
            url: url,
            type: "post",
            data: parm,
            dataType: dataType,
            async: async,
            success: function(data) {
                func(data);
            }
        });
    }
};

__JsUtil.AddRadom = function(url) { //传入url地址，在这个地址上加上随机数返回
    if (url == undefined || url == null) { return ""; }
    if (url != "") {
        var r = "?r=" + Math.random();
        if (url.indexOf('?') > 0) {
            r = "&r=" + Math.random();
        }
    }
    return url + r;
};

//函数功能：json 排序  
// filed:(string)排序字段，  
// reverse: (bool) 是否倒置(是，为倒序)  
// primer (parse)转换类型
__JsUtil.SortBy = function(filed, reverse, primer) {
    reverse = (reverse) ? -1 : 1;
    return function(a, b) {
       
        a = a[filed];
        b = b[filed];
        if (typeof (primer) != "undefined" && a != undefined && b != undefined) {
            a = primer(a);
            b = primer(b);
        }

        if (a == undefined || a < b) {
            return reverse * -1;
        }
        if (b == undefined || a > b) {
            return reverse * 1;
        }
    }
}
//表格的id 或者容器id 查找该id 下面的 input textarea select的值返回一个 obj对象
__JsUtil.Serialize = function(table_id) {
    var item = {};
    var Parms = $("#" + table_id).find("input[type='text'],input[type='hidden'],textarea,select");
    $.each(Parms, function(i, n) {
        var name = $(n).attr("name");
        if (name != undefined && name != "") {
            item[name] = $(n).val();
        }
    });
    return item;
}
__JsUtil.Relocation = function() { //获取页面重定位  默认如果地址栏有url参数则进行跳转 “?”替换成 %3F  “=”替换成 %3D “&”替换成 %26
    var url = __JsUtil.GetQuery("url");
    if (!__JsUtil.isNullOrEmpty(url)) {
        url = url.replaceAll("%3F", "?").replaceAll("%3D", "=").replaceAll("%26", "&");
        var uarray = url.split("?r=");
        if (uarray.length == 1) {
            uarray = url.split("&r=");
        }
        url = __JsUtil.AddRadom(uarray[0]);
        window.location = url;
        return true;
    }
    return false;
}


/**
* 获取date所在的周信息
* @method GetWeekDates
* @for __JsUtil
* @param {Date,int} date,number 传入 日期以及当前日期所在周的 +-number 周信息
* @return {array} 返回值如[2014-01-01,2014-01-07]   前面一个是 周一所在的日期 后面一个是周天所在的日期
*/
__JsUtil.GetWeekDates = function(date, number) {
    var array = [];
    if (date == undefined) {
        date = new Date();
    }
    if (number == undefined) { number = 0; }
    var day = date.getDay() == 0 ? 7 : date.getDay(); //获取日期date 是周几  0-6  0代表周日 0转换为7
    var weekFirst = __DataUtil.dateAdd(date, "d", 1 - day);
    if (number != 0) {
        weekFirst = __DataUtil.dateAdd(weekFirst, "d", number * 7);
    }
    array.push(weekFirst);
    var weekLast = __DataUtil.dateAdd(weekFirst, "d", 6); //从周一加6天 到周天
    array.push(weekLast);
    return array;
}
/**
* 获取date所在的月份信息
* @method GetMonthDates
* @for __JsUtil
* @param {Date,int} date,number 传入 日期以及当前日期所在月份的 +-number 月份信息
* @return {array} 返回值如[2014-01-01,2014-01-31]   前面一个是 月chu所在的日期 后面一个是月末所在的日期
*/
__JsUtil.GetMonthDates = function(date, number) {
    var array = [];
    if (date == undefined) {
        date = new Date();
    }
    if (number == undefined) { number = 0; }
    var ymd = __DataUtil.dateAdd(date, "m", number).format("yyyy-MM-dd").split("-");
    var year = ymd[0];
    var month = ymd[1];
    var firstdate = year + '/' + month + '/01';
    var day = new Date(year, month, 0);
    var lastdate = year + '/' + month + '/' + day.getDate(); //获取当月最后一天日期
    //给文本控件赋值。同下
    array.push(new Date(firstdate));
    array.push(new Date(lastdate));
    return array;
}

/*****
//调用var difference = __JsUtil.dateDiff("2009-10-10 19:00:00","2009-10-10 18:00:00") 返回的是秒钟
要获取相差的小时 difference / ( 60 * 60)
获取相差的天数 difference / (60 * 60*24)
*****/
__JsUtil.dateDiff = function(btime, etime) {
    var type1 = typeof etime, type2 = typeof btime;
    if (type1 == 'string')
        etime = __JsUtil.stringToTime(etime);
    else if (etime.getTime)
        etime = etime.getTime();
    if (type2 == 'string')
        btime = __JsUtil.stringToTime(btime);
    else if (btime.getTime)
        btime = btime.getTime();
    return (etime - btime) / 1000; //结果是秒
}
__JsUtil.stringToTime = function(string) {
    var f = string.split(' ', 2);
    var d = (f[0] ? f[0] : '').split('-', 3);
    var t = (f[1] ? f[1] : '').split(':', 3);
    return (new Date(
    parseInt(d[0], 10) || null,
    (parseInt(d[1], 10) || 1) - 1,
    parseInt(d[2], 10) || null,
    parseInt(t[0], 10) || null,
    parseInt(t[1], 10) || null,
    parseInt(t[2], 10) || null
    )).getTime();
}
/*--------------------------------------------------------------*/