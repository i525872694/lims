<?php
/*{
    "name": "分中心id",字段名称（注释）
    "mark": "fzx_id",字段标识
    "formType": "input",表单类型
    "formHtml": "",表单自定义html
    "isColumn": "1",是否是单独字段，单独字段不允许删除
    "width": "col-xs-4",宽度
    "using": "1"使用状态:0未使用，1使用中2隐藏字段（包括隐藏域字段，不宜让用户看到的字段等）
}*/
return <<<jsonStr
[
{
    "name": "站点名称",
    "mark": "site_name",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "任务类型",
    "mark": "site_type",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "样品类型",
    "mark": "water_type",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "区域/河流名称",
    "mark": "river_name",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "水系",
    "mark": "water_system",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "流域",
    "mark": "area",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "省",
    "mark": "province",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "市",
    "mark": "city",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "区县",
    "mark": "district",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "站址",
    "mark": "site_address",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "2"
},
{
    "name": "行政区划码",
    "mark": "addvcd",
    "formType": "input",
    "formHtml": "<input name='addvcd' value='' readonly />",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "行政区",
    "mark": "xz_area",
    "formType": "input",
    "formHtml": "<input name='xz_area' value='' readonly />",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "水功能区名称",
    "mark": "sgnq",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "水功能区编号",
    "mark": "sgnq_code",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "水功能区类型",
    "mark": "sgnq_type",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "测站编码",
    "mark": "site_code",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "垂线编号",
    "mark": "site_line",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "层面编号",
    "mark": "site_vertical",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "站点备注",
    "mark": "note",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "经度",
    "mark": "jingdu",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "纬度",
    "mark": "weidu",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "采样范围",
    "mark": "banjing",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "默认检测标准",
    "mark": "jcbz",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
},
{
    "name": "水源限制",
    "mark": "syxz",
    "formType": "input",
    "formHtml": "",
    "isColumn": "1",
    "width": "col-xs-4",
    "using": "1"
}]
jsonStr;
// return json_decode($siteColumnSet, true);