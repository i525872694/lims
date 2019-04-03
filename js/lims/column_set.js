// if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"script>");

// <!--自定义字段模态框-->
var column_set_box = '<div id="autoColumn_setting" class="modal fade" data-backdrop="static">' + 
    '<div class="modal-content" style="width:800px;margin:0 auto;overflow:auto;">' + 
        '<div class="modal-header">' + 
            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="关闭窗口">&times;</button>' + 
            '<h3 class="center">自定义字段设置</h3>' + 
        '</div>' + 
        '<div class="modal-body">' + 
            '<div style="text-align:right;margin-bottom:10px;">' + 
                // '<button type="button" class="btn btn-xs btn-primary addColumn">新建字段</button>' + 
            '</div>' + 
            '<h4 class="header smaller">已启用字段</h4>' + 
            '<div class="setColumnDiv"></div>' + 
            '<div style="clear:both;"></div>' + 
            '<h4 class="header smaller">已弃用字段</h4>' + 
            '<div class="setColumnDiv"></div>' + 
            '<div style="clear:both;"></div>' + 
        '</div>' + 
        '<div class="modal-footer">' + 
            '<button type="button" class="btn btn-primary btn-sm submint">确定</button>' + 
            '<button type="button" class="btn btn-sm" data-dismiss="modal" aria-hidden="true" title="关闭窗口">关闭</button>' + 
        '</div>' + 
    '</div>' + 
'</div>'
// 加载模态框
!$("#autoColumn_setting").length && $("body").append(column_set_box);
// 实例化拖动功能
dragula([$('.setColumnDiv')[0], $('.setColumnDiv')[1]]);
// 封装自定义字段方法
$.initAutoColumn = function(options){
    var myTable = options.myTable;
    var formName = options.formName;
    var infoJSON = options.infoJSON;
    var columnSet = options.columnSet;
    // 初始化form表单，加载自定义参数字段
    initMyTable();
    function initMyTable() {
        // 使用中
        var i = 0;
        var infoStr = '<tr>';
        // myTable
        $(myTable).html('');
        for (key in columnSet) {
            var that = columnSet[key];
            // 不在使用中的直接跳过
            if (that.using != "1") {
                continue;
            }
            infoStr += '<td>' + that.name + ':</td>';
            if(that.formHtml){
                infoStr += '<td>' + that.formHtml + '</td>';
            }else{
                infoStr += '<td><input name="' + that.mark + '" value="" /></td>';
            }
            if (i && i % 3 == 2) {
                infoStr += '</tr><tr>'
            }
            i++;
        }
        // 生成表单
        $(myTable).append(infoStr + '</tr>');
        // 赋值
        $(formName).not("[type=hidden]").each(function(){
            var name = $(this).attr("name");
            infoJSON[name] && $(this).val(infoJSON[name]);
        });
    };
    function initAutoColumn() {
        $(".setColumnDiv").html("");
        for (key in columnSet) {
            var that = columnSet[key];
            var index = (that.using == "1") ? 0 : 1;
            var removeButton = '';
            if (that.isColumn == "0") {
                removeButton = '<a href="javascript:void(0)" class="delColumn red" data-rel="tooltip" title="删除字段"><i class="icon-remove bigger-130"></i></a>';
            }
            $(".setColumnDiv:eq(" + index + ")").append(
                '<div class="' + that.width + '">' +
                '<span>' + that.name + '</span>:<input size="4" type="hidden" name="' + that.mark + '" value="" />' +
                removeButton +
                '</div>'
            );
        }
    }
    // 打开自定义字段模态框设置
    $("[data-action='columnSettings']").unbind("click").click(function () {
        initAutoColumn();
        $("#autoColumn_setting").modal("show");
    })//.trigger("click");
    // 提交自定义字段修改
    $("#autoColumn_setting .submint").unbind("click").click(function () {
        var newSet = {};
        $('.setColumnDiv [class*="col-"]').each(function (i) {
            var that = $(this);
            var columnName = that.find("span").html();
            var columnMark = that.find("input").attr("name");
            var using = that.parents(".setColumnDiv").is('.setColumnDiv:eq(0)') ? 1 : 0;
            newSet[i] = {
                "name": columnName,
                "mark": columnMark,
                "formType": "input",
                "formHtml": "",
                "width": "col-xs-4",
                "using": using,
                "isColumn": columnSet[columnMark].isColumn
            }
        });
        columnSet = newSet;
        $.ajax({
            type: 'get',
            dataType: 'json',
            data: {
                app: 'column_set',
                act: 'update_column_set',
                table_key: options.table_key,
                columnSet: columnSet
            },
            url: trade_global.rooturl + '/huayan/ahlims.php?ajax=1&',
            success: function (data) {
                if ('1' == data.error) {
                    initMyTable();
                    $("#autoColumn_setting").modal("hide");
                    return alert_ok("设置成功");
                } else {
                    return alert_error("设置失败");
                }
            }, error: function (data) {
                return alert_error(data.responseText);
            }
        });
    });
    $("#autoColumn_setting").on("click", ".delColumn", function () {
        var that = this;
        $.confirm({
            content: '你确定要删除该字段吗？',
            confirm: function () {
                $(that).parents("[class*='col-']").remove();
            }
        });
    });
    $(".addColumn").click(function () {
        $.prompt(defaults = {
            title: '新建字段',
            promptHtml: '' +
            '<form action="" class="formName">' +
            '<div class="form-group">' +
            '<p style="text-align:left;">字段名称</p>' +
            '<input type="text" placeholder="" class="columnName form-control" required />' +
            '<p style="text-align:left;">字段标识</p>' +
            '<input type="text" placeholder="" class="columnMark form-control" required />' +
            '</div>' +
            '</form>',
            cancel: function () { },
            confirm: function (prompt) {
                var columnName = prompt.find(".columnName").val();
                var columnMark = prompt.find(".columnMark").val();
                if (!columnName) {
                    prompt.showError('字段名称不能为空！', 0);
                    return;
                }
                if (!columnMark) {
                    prompt.showError('字段标识不能为空！', 1);
                    return;
                }
                var hasben = false;
                for (key in columnSet) {
                    if (columnSet[key].name == columnName || columnSet[key].mark == columnMark) {
                        hasben = true;
                        break;
                    }
                }
                if (hasben) {
                    prompt.showError('你输入的内容已被[' + columnSet[key].name + '(' + columnSet[key].mark + ')]占用', 0);
                    return;
                }
                addColumn(columnName, columnMark);
                prompt.modal("hide");
            }
        })
    });
    function addColumn(columnName, columnMark) {
        // console.log(JSON.stringify(columnSet))
        columnSet.push(
            {
                "name": columnName,
                "mark": columnMark,
                "formType": "input",
                "formHtml": "",
                "width": "col-xs-4",
                "using": "1",
                "isColumn": "0"
            }
        );
        $(".setColumnDiv:eq(0)").append(
            '<div class="col-xs-4">' +
            '<span>' + columnName + '</span>:<input size="4" type="hidden" name="' + columnMark + '" value="" />' +
            '<a href="javascript:void(0)" class="delColumn red" data-rel="tooltip" title="删除字段"><i class="icon-remove bigger-130"></i></a>' +
            '</div>'
        );
    }
}