//展示修改密码界面
var modi_pwd = $("#is_modi").val();
if(modi_pwd == '1'){
    showOverlay();
    var modiDiv = $('#modiDiv');
    $("#modiDiv").css("display", "block");
    modiDiv.css({
    'top' : (getWindowInnerHeight() - modiDiv.height()) / 2 + 'px',
    'left' : (getWindowInnerWidth() - modiDiv.width()) / 2 + 'px'
    });
}

// 浏览器兼容 取得浏览器可视区高度
function getWindowInnerHeight() {
var winHeight = window.innerHeight
    || (document.documentElement && document.documentElement.clientHeight)
    || (document.body && document.body.clientHeight);
return winHeight;

}

// 浏览器兼容 取得浏览器可视区宽度
function getWindowInnerWidth() {
var winWidth = window.innerWidth
    || (document.documentElement && document.documentElement.clientWidth)
    || (document.body && document.body.clientWidth);
return winWidth;

}

function showOverlay() {
// 遮罩层宽高分别为页面内容的宽高
$('#overlay').css({'height':$(document).height(),'width':$(document).width()});
$('#overlay').show();
}

//修改密码点击提交后信息验证函数
function sub(){
    var pwd_new = $("#new_pwd").val();
    var pwd_check = $("#check_pwd").val();
    var msg = '';

    if(pwd_new.length<6 || pwd_check.length<6 || pwd_check.length>18 || pwd_new.length>18){
        msg = "请检查新密码的位数是否在6-18位之间";
    }else if(pwd_check != pwd_new){
        msg = "两次密码输入不一致!";
    }

    var data = {"pwd_new":pwd_new, "pwd_check":pwd_check};
    if(!msg){
        $.getJSON('user_manage/default_pwd_modi.php',data,function(resp){
            if(resp.status == '1'){
                alert("修改成功!");
                var rooturl = $("#rooturl").val();
                window.location.replace(rooturl);
            }else{
                return $("#warns").html(resp.msg);
            }
        })
    }
    return $("#warns").html(msg);           
}