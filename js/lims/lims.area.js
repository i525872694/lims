/**
 *  省,市,县 3级联动js
 * @author  
 * @date    2017-05-31
 * @version Ver 0.0.1
 */

function initComplexArea(f, pro, cit, dis) {
    var p = PRC_area_data;
    var a = f.find("select[name=province]");//省
    var b = f.find("select[name=city]");//市下拉框
    var c = f.find("select[name=district]");//区县

    a.html('<option>请选择</option>');
    b.html('<option>请选择</option>');
    c.html('<option>请选择</option>');
    make_option(a, p);

    // 改变省下拉框事件
    $(a).unbind("change").on("change", function(){
        var pro_key = a.find("option:selected").data("key");
        if(pro_key == undefined){
            return;
        }
        b.html('<option>请选择</option>');
        c.html('<option>请选择</option>');
        if(!p[pro_key]['child']){
            b.hide();
            c.hide();
            return;
        }
        b.show();
        c.show();
        var city = p[pro_key]['child'];
        make_option(b, city);
    });
    // 改变市下拉框事件
    $(b).unbind("change").on("change", function(){
        var pro_key = a.find("option:selected").data("key");
        if(pro_key == undefined){
            return b.html('<option>请选择</option>');
        }
        var cit_key = b.find("option:selected").data("key");
        if(cit_key == undefined){
            return c.html('<option>请选择</option>');
        }
        c.html('');
        if(!p[pro_key]['child'][cit_key]['child']){
            c.hide();
            return;
        }
        c.show();
        var district = p[pro_key]['child'][cit_key]['child'];
        make_option(c, district);
    });
    function set_addvcd(){
        var addvcd = get_addvcd();
        var d = f.find("input[name=addvcd]");//行政区划码
        var e = f.find("input[name=xz_area]");//行政区名称
        d.length && d.val && d.val(addvcd[0]); // 行政区划码
        e.length && e.val && e.val(addvcd[1]); // 行政区名称
    }
    $(a).on("change", function(){
        set_addvcd()
    });
    $(b).on("change", function(){
        set_addvcd()
    });
    $(c).on("change", function(){
        set_addvcd()
    });
    // 默认选中省市区县
    if (pro != undefined) {
        var province = $(a).find("option[data-name='" + pro + "']");
        if(province){
            province.prop("selected", true);
            a.trigger("change");
        }
    }
    if (cit != undefined) {
        var city = $(b).find("option[data-name='" + cit + "']");
        if(city){
            city.prop("selected", true);
            b.trigger("change");
        }
    }
    if (dis != undefined) {
        var district = $(c).find("option[data-name='" + dis + "']");
        if(district){
            district.prop("selected", true);
            c.trigger("change");
        }
    }
    function make_option(obj, data){
        for (i = 0; i < data.length; i++) {
            if (data[i] == undefined) {
                continue;
            }
            obj.append('<option data-key="' + i + '" data-code="' + data[i].code + '" data-name="' + data[i].name + '" value="' + data[i].id + '">' + data[i].name + '</option>')
        }
    }
    function get_addvcd(){
        var pro = a.find("option:selected").data("key");
        var cit = b.find("option:selected").data("key");
        var dis = c.find("option:selected").data("key");
        var addvcd = [];
        if(dis != undefined && c.find("option:selected").text() != '市辖区'){
            addvcd = [p[pro]['child'][cit]['child'][dis]['code'], p[pro]['child'][cit]['child'][dis]['name']];
        }
        if(!addvcd.length){
            if(cit != undefined && b.find("option:selected").text() != '市辖区'){
                [p[pro]['child'][cit]['code'], p[pro]['child'][cit]['name']];
            }
        }
        if(!addvcd.length){
            if(pro != undefined){
                return [p[pro]['code'], p[pro]['name']];
            }
        }
        return addvcd;
    }
}