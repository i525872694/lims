/*lims.spread*/
// "use strict";
// spread相关文件
/*if(typeof GC == "undefined"){
    document.write("<link href='"+trade_global.rooturl+"/js/lims/spread/lims.spread.css' rel='stylesheet' type='text/css' />");
    document.write("<link href='"+trade_global.rooturl+"/js/lims/spread/css/gc.spread.sheets.excel2013white.10.0.1.css' rel='stylesheet' type='text/css' />");

    document.write("<script src='"+trade_global.rooturl+"/js/lims/spread/js/gc.spread.sheets.all.10.0.1.min.js'>"+"<"+"script>");
    document.write("<script src='"+trade_global.rooturl+"/js/lims/spread/js/gc.spread.sheets.resources.zh.10.0.1.min.js'>"+"<"+"script>");
    document.write("<script src='"+trade_global.rooturl+"/js/lims/spread/jsgs.js'>"+"<"+"script>");
}
// hyd.js
if(!$.fn.assay_form){
    document.write("<script src='"+trade_global.rooturl+"/js/lims/hyd.js'>"+"<"+"script>");
}
$.getScript(trade_global.rooturl+"/js/lims/spread/jsgs.js");
$.getScript(trade_global.rooturl+"/js/lims/spread/js/gc.spread.sheets.all.10.0.1.min.js", function(){
    $.getScript(trade_global.rooturl+"/js/lims/spread/js/gc.spread.sheets.resources.zh.10.0.1.min.js",function(){
        // 
    });
});*/
var spread, sheet,
    spreadNS = GC.Spread.Sheets,
    SheetArea = spreadNS.SheetArea;
// 中文本地化设置
GC.Spread.Common.CultureManager.culture("zh-cn");
(function($) {
    $.fn.extend({
        /**
         * 功能：spreadForm插件
         * 作者：Mr Zhou
         * 日期：2017-06-11
         * 功能描述：化验单调用插件
        */
        spreadForm : function(options) {
            var default_conf = {
                saveButtonId: "",
                spreadBoxId: "",
                requestSaveUrl: "",
                requestDataUrl: "",
                error: function(){},
                success: function(){}
            }
            // 
            if(!window.spreadForm){
                window.spreadForm = [];
            }
            window.spreadForm[window.spreadForm.length] = [];
            // 
            var spF = $.extend({}, default_conf, options);
            var mustSetKey = ["saveButtonId", "spreadBoxId", "requestDataUrl"];
            for(var key in mustSetKey){
                if(!options[mustSetKey[key]]){
                    return console.log("必要的" + mustSetKey[key] + "未定义！")
                }
            }
            // 
            if(!$(".spread_loading").length){
                $(spF.spreadBoxId).after('<div class="spread_loading"></div>').hide();
            }
            $.getJSON(spF.requestDataUrl, function(data){
                if(!data.data || !data.data.sp_style){
                    $(".spread_loading").html(data.content ? data.content : "无数据");
                    options.error(spF);
                    return;
                }
                initSpread(data.data, spF);
                // options.success(spF);
            });
            // 数据保存
            if(!spF.saveButtonId){
                return;
            }
            $(spF.saveButtonId).unbind("click").on("click", function(){
                var spread = $(spF.spreadBoxId).data('workbook');
                var sheet = spread.getActiveSheet();
                var dataSource = sheet.getDataSource();
                var alert_obj = $.alert({
                    content: '',
                    title: '数据保存并更新中'
                });
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {data: dataSource.xf},
                    url: spF.requestSaveUrl,
                    success: function(data){
                        if('0'==data.error){
                            if(data.data){
                                initSpread(data.data, spF);
                            }
                            alert_ok(data.content,alert_obj)
                        }else{
                            alert_error('保存失败，请刷新页面重试！',alert_obj);
                        }
                    },error: function(data){
                        alert_error('保存失败，请刷新页面重试！',alert_obj);
                        console.log(data.responseText);
                    }
                });
            });
        }
    });
    // 初始化Spread
    function initSpread(data, spF){
        if(typeof spF.spreadBoxId == "undefined"){
            return console.log("未定义Spread容器筛选器：spF.spreadBoxId");
        }
        // Spread 只初始化一次
        if(!$(spF.spreadBoxId).data("workbook")){
            spread = new GC.Spread.Sheets.Workbook($(spF.spreadBoxId)[0]);
        }
        // 导入sp_style表格样式
        if(typeof data.sp_style == "undefined"){
            return console.log("Spread样式没有定义！");
        }
        spread = $(spF.spreadBoxId).data("workbook");
        spread.fromJSON(data.sp_style);
        sheet = spread.getActiveSheet();
        // 获取模板配置信息
        if(typeof data.sp_style.assayPay == "undefined"){
            return  console.log("Spread模板配置信息没有定义！");
        }
        var assayPay = data.sp_style.assayPay;
        // 挂起重绘机制,待所有样式赋值完毕后一次性渲染
        spread.suspendPaint();
        // 定义工作簿名称　记录表ID
        sheet.name("记录表ID" + data.headerData.id);
        // 导入化验单数据资源
        if(!data.headerData || !data.linesData){
            return　console.log("未定义正确的headerData和linesData数据！");
        }
        sheet.setDataSource(
            new spreadNS.Bindings.CellBindingSource({
                linesData: data.linesData,
                headerData: data.headerData
            })
        );
        // 允许编辑的单元格
        var canModi = !data.headerData.canModi ? "0" : data.headerData.canModi.toString();
        if( canModi == "1" && assayPay.canEditCell){
            for (i in assayPay.canEditCell) {
                var currentStyle = sheet.getStyle(assayPay.canEditCell[i][0], assayPay.canEditCell[i][1]);
                if(!currentStyle){
                    currentStyle = new GC.Spread.Sheets.Style();
                }
                currentStyle.locked = false;
                currentStyle.formatter = "@";
                currentStyle.align = "center";
                currentStyle.backColor = "#eee";
                sheet.setStyle(assayPay.canEditCell[i][0], assayPay.canEditCell[i][1], currentStyle);
                // 垂直水平居中
                sheet.getCell(assayPay.canEditCell[i][0], assayPay.canEditCell[i][1])
                    .vAlign(spreadNS.VerticalAlign.center)
                    .hAlign(spreadNS.HorizontalAlign.center);
            }
        }
        // 绑定表头参数
        for(var key in assayPay.headerBind ){
            var bindKey = 'headerData.' + key; // 绑定的字段键值
            sheet.setBindingPath(assayPay.headerBind[key][0], assayPay.headerBind[key][1], bindKey);
        }
        // 声明自定义函数,必须定义正确的vd0,函数名称以及函数内容才可以绑定函数
        if(assayPay.lineBind.vd0 && assayPay.functionName && assayPay.customFunction){
            var FactorialFunction = customFunction(spread, sheet, assayPay);
            sheet.addCustomFunction(new FactorialFunction());
            // 绑定自定义函数
            if(assayPay.lineBind.vd0){
                sheet.setFormula( assayPay.lineBind.vd0[0], assayPay.lineBind.vd0[1], assayPay.customFunction);
            }
        }
        // 设置起始行
        var startRow = assayPay.startRow;
        // 添加行数据,绑定行数据参数
        sheet.addRows(startRow + 1, data.linesData.length * assayPay.preRowCount);
        // 填充数据
        for( var i = 0; i < data.linesData.length; i++ ){
            var xuHao = i + 1;
            data.linesData[i].xuHao = xuHao;
            // 当前行数
            var currentRow = startRow + xuHao;
            // 复制样式
            sheet.copyTo(startRow, 0, startRow + xuHao, 0, assayPay.preRowCount, assayPay.perColCount, GC.Spread.Sheets.CopyToOptions.all);
            // 设置行高
            sheet.setRowHeight(currentRow, sheet.getRowHeight(startRow));
            // 绑定行数据参数
            for( key in assayPay.lineBind ){
                var bindKey = 'linesData.' + i + '.' + key; // 绑定的字段键值
                sheet.setBindingPath(currentRow, assayPay.lineBind[key][1], bindKey);
            }
        }
        // 数据行
        var range = sheet.getRange(assayPay.startRow, 0, assayPay.preRowCount*(data.linesData.length+1), assayPay.perColCount);
        range.setBorder(new spreadNS.LineBorder("black", spreadNS.LineStyle.thin), { all: true });
        // 垂直居中,水平居中
        range.vAlign(GC.Spread.Sheets.VerticalAlign.center);
        range.hAlign(GC.Spread.Sheets.HorizontalAlign.center);
        // 由于默认的数据行绑定自定义函数时会自动计算，故不使用它，只是用来获取位置和样式，使用完毕后隐藏
        // 不删除它是因为删除后下面的数据行的位置会发生变化，从而导致计算公式自动调用进行计算，这样含量里面的值就变成了原始结果而不是修约后的数值
        sheet.setValue(assayPay.startRow, 0, 0);
        sheet.setRowHeight(assayPay.startRow, 0);
        // sheet.deleteRows(assayPay.startRow, assayPay.preRowCount);
        // 至此所有行列都已加载完毕，获取总行数和总列数
        var rowCount = sheet.getRowCount(SheetArea.viewport);
        var colCount = sheet.getColumnCount(SheetArea.viewport);
        // 获取整个工作簿所有有效的行和列
        // var range = sheet.getRange(0, 0, rowCount, colCount);
        // 垂直居中,水平居中
        // range.vAlign(GC.Spread.Sheets.VerticalAlign.center);
        // range.hAlign(GC.Spread.Sheets.HorizontalAlign.center);
        // 设置边线　不再默认设置黑色边线
        // range.setBorder(new spreadNS.LineBorder("black", spreadNS.LineStyle.thin), { all: true });
        // 设置边框
        // 右
        sheet.getRange(0, colCount-1, rowCount, 1)
            .setBorder(new spreadNS.LineBorder("black", spreadNS.LineStyle.thin), { right: true });
        // 下
        sheet.getRange(0, 0, rowCount, 1)
            .setBorder(new spreadNS.LineBorder("black", spreadNS.LineStyle.thin), { left: true });
        // 不复制粘贴Excel样式
        spread.options.allowCopyPasteExcelStyle = false;
        // 当 Spread 失去焦点时隐藏选择效果
        spread.options.hideSelection = true;
        // 隐藏表单标签
        spread.options.newTabVisible = false;
        spread.options.tabEditable  = false;
        spread.options.tabNavigationVisible  = false;
        spread.options.tabStripVisible = false;
        // 空白区域背景色
        spread.options.grayAreaBackColor = 'white';
        // 隐藏滚动条
        spread.options.showHorizontalScrollbar = false;
        spread.options.showVerticalScrollbar = false;
        // scrollbarMaxAlign: 滚动条是否对齐视图中表单的最后一行或一列
        // scrollbarShowMax: 是否基于表单全部的行列总数显示滚动条
        spread.options.scrollbarMaxAlign = true;
        spread.options.scrollbarShowMax = true;
        // 设置网格线
        // sheet.options.gridline = {showVerticalGridline: true, showHorizontalGridline: true, color: 'black'};
        // 隐藏行头区域和列头区域
        sheet.options.rowHeaderVisible = false;
        sheet.options.colHeaderVisible = false;
        // 禁止单元格内容溢出显示
        sheet.options.allowCellOverflow = true;
        // 表单名称标签与表单保护
        var option = {
            allowFilter: false,
            allowSort: false,
            allowResizeRows: true,
            allowResizeColumns: false,
            allowEditObjects: false,
            allowSelectLockedCells: true,
            allowSelectUnlockedCells: true
        };
        sheet.options.isProtected = true;
        sheet.options.protectionOptions = option;
        // 重新激活重绘机制
        spread.resumePaint();
        // 调整化验的的宽高
        (function(){
            var width = 0;
            var height = 0;
            var spans = getSpans(sheet);
            var rowCount = sheet.getRowCount(SheetArea.viewport);
            var colCount = sheet.getColumnCount(SheetArea.viewport);
            var getWidth = function(){
                var width = 0;
                for (var col = 0; col < colCount; col++) {
                    var letterIndex = getLetterIndex(0, col);
                    // 隐藏的单元格直接跳过
                    if($.inArray(letterIndex, spans.hideSpans) >= 0){
                        continue;
                    }
                    var cellRect = sheet.getCellRect(0, col);
                    // 当前宽高不满足时被隐藏的单元格会回去不到高度和宽度，需要临时增加宽高并刷新spread
                    for(;true;){
                        if(!cellRect.width){
                            $(spF.spreadBoxId).width($(spF.spreadBoxId).width() + 100);
                            spread.refresh();
                            var cellRect = sheet.getCellRect(0, col);
                        }else{
                            break;
                        }
                    }
                    width += cellRect.width;
                }
                return width;
            }
            // 第一获取宽度与容器宽度求比例
            width = getWidth();
            // 如果设定了宽度则进行比例缩放
            if($.isNumeric(spF.width)){
                // 调用 zoom 方法改变当前表单的缩放比
                sheet.zoom(roundjs(spF.width/width, 2));
                // 获取缩放后的宽度
                width = getWidth();
            }
            for (var row = 0; row < rowCount; row++) {
                var letterIndex = getLetterIndex(row, 0);
                // 隐藏的单元格直接跳过
                if($.inArray(letterIndex, spans.hideSpans) >= 0){
                    continue;
                }
                var cellRect = sheet.getCellRect(row, 0);
                // 当前宽高不满足时被隐藏的单元格会回去不到高度和宽度，需要临时增加宽高并刷新spread
                if(!cellRect.height){
                    $(spF.spreadBoxId).height($(spF.spreadBoxId).height() + 300);
                    spread.refresh();
                    var cellRect = sheet.getCellRect(row, 0);
                }
                height += cellRect.height;
            }
            $(spF.spreadBoxId).width(width).height(height);
            spread.refresh();
        })();
        // 显示化验单
        $(spF.spreadBoxId).show();
        $(".spread_loading").hide();
    }
    // 获取合并的单元格
    function getSpans(sheet){
        // 获取合并的单元格
        var spans = sheet.getSpans();
        // 获取合并单元格中显示和隐藏的单元格
        var showSapns = [], hideSpans = [];
        for(var i = 0; i < spans.length; i++){
            showSapns.push(getLetterIndex(spans[i].row, spans[i].col));
            for( var j = 0; j < spans[i].rowCount; j++){
                for( var k = 0; k < spans[i].colCount; k++){
                    var l = getLetterIndex(spans[i].row + j, spans[i].col + k);
                    if($.inArray(l, showSapns)== -1){
                        hideSpans.push(l);
                    }
                }
            }
        }
        return {showSapns: showSapns, hideSpans: hideSpans};
    }
    // 自定义函数
    function customFunction(spread, sheet, assayPay){
        function FactorialFunction() {
            this.name = "jsgs";
        }
        FactorialFunction.prototype = new GC.Spread.CalcEngine.Functions.Function();
        FactorialFunction.prototype.evaluate = function () {
            // 获取序号的位置，通过序号来定位当前单元格位置
            var index = $.inArray("xuHao", assayPay.funArguments);
            if(index == -1){
                return console.log("公式参数中没有序号！");
            }
            var xuHao = parseInt(arguments[index]);
            var currentCell = {
                args: [],
                col: assayPay.lineBind.vd0[1],
                row: xuHao + assayPay.startRow
            };
            // 获取当前公式参数
            var jsgsArguments = getArguments(sheet.getFormula(currentCell.row, currentCell.col));
            // 定义计算参数
            var options = {};
            for (var i = 0; i < arguments.length; i++) {
                options[assayPay.funArguments[i]] = arguments[i];
            }
            // vid
            var dataSource = sheet.getDataSource();
            options.vid = dataSource.xf.headerData.vid;
            // 计算公式执行数据计算
            if(typeof assayPay.functionName == "undefined"){
                return console.log("未定义公式");
            }else if(typeof $.jsgs[assayPay.functionName] == "undefined"){
                return console.log("未定义公式");
            }
            options = $.jsgs[assayPay.functionName](options);
            // 赋值其他数据单元格
            if(assayPay.setArguments){
                setTimeout(function(){
                    spread.suspendPaint();
                    for(i in assayPay.setArguments){
                        var cell = assayPay.setArguments[i];
                        // 根据第一行与序号锁定当前位置，数据行可能不是一行，故不用startRow
                        if(isHeaderRow(cell[0], assayPay)){
                            sheet.setValue(cell[0], cell[1], options[i]);
                        }else{
                            sheet.setValue((xuHao + cell[0]), cell[1], options[i]);
                        }
                    }
                    spread.resumePaint();
                },0);
            }
            // 计算结果含量
            return $.isNumeric(options.hanLiang) ? options.hanLiang : '';
        }
        return FactorialFunction;
    }
    // 判断是否是表头数据
    function isHeaderRow(row, assayPay){
        var minRow = assayPay.startRow;
        if(assayPay.startRow){
            var maxRow = accAdd(assayPay.startRow - 1 , assayPay.preRowCount);
            (typeof row == "object") && ( row = row.row || row[0]);
            return ( row < minRow || row > maxRow );
        }
        var text = sheet.getText(row, 0);
        return text && !$.isNumeric(text) ? true : false;
    }
    // 字母转换为数字
    function str2Num(str) {
        var base = 'A'.charCodeAt(0);//找到A的码表大小
        var r = 0;
        for (var i = 0; i < str.length; i++) {//遍历每个位置
            r = r * 26 + str.charCodeAt(i) - base + 1;
        }
        return r - 1;
    };
    // 将数字索引转换为英文字母
    function num2Str(colIndex) {
        colIndex += 1;
        if (colIndex <= 0) {
            return "";
        }
        var str = "";
        var result = "";
        var A = 'A';
        while (colIndex != 0) {
            var num = colIndex % 26; // 取最后一位
            var c = A.charCodeAt(0) + num - 1;
            colIndex = Math.floor(colIndex / 26); //返回值小于等于其数值参数的最大整数值。
            // 对于26的特殊处理
            if (num == 0) {
                    c = A.charCodeAt(0) + 26;
                str = 'Z';
                colIndex -= 1; //退位
            } else {
                str = String.fromCharCode(c);
            }
            // 3.插入
            result += str;
        }
        if (result.length > 1) {
            result = result.split("").reverse().join("");
        }
        return result;
    };
    // 根据字母索引返回数字索引对象
    function getNumIndex(str){
        str = str.replace(/[^0-9A-Z]/g,"");
        var col = str.match(/[A-Z]+/)[0];
        var row = str.match(/[0-9]+/)[0]-1;
        !$.isNumeric(col) && (col = str2Num(col));
        return {col: parseInt(col), row: parseInt(row)};
    }
    // 根据数字索引返回字母索引
    function getLetterIndex(row, col){
        return num2Str(col)+(row+1);
    }
    // 返回公式参数
    function getArguments(str){
        // 匹配出括号内的参数内容
        pattern =new RegExp("\\((.| )+?\\)","igm");
        //取出匹配正则表达式的内容
        str.match(pattern) && (str = str.match(pattern)[0]);
        // 只留下大写字母、数字和逗号,去掉空格和$等字符
        return str.replace(/[^0-9A-Z,]/g,"").split(",");
    }
})(jQuery);