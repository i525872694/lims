<?php
/*
 * 默认密码修改 针对用户密码是默认密码的用户,修改密码的处理界面
 *
 * @author: Hongqi Zhao 
 * @date: 2017-07-27 14:51:03 
 * @Last Modified by: Hongqi Zhao
 * @Last Modified time: 2017-07-27 16:32:51
 */
include '../temp/config.php';
if(!empty($_GET)){
    $pwd_new = get_str($_GET['pwd_new']);
    $new_md5 = md5($pwd_new);
    $status = 0;
    if($pwd_new == 'lims123'){
        $data['status'] = $status;
        $msg = "密码不能修改成初始密码 lims123";
    }else{
        $nickname = $u['nickname'];
        $res = $DB->query("update `users` set `password`='$new_md5' where `nickname`='$nickname'");
        if($res){
            $status = 1;
            $msg = "修改成功!";
        }else{
            $msg = "修改失败!";
        }
    }

    $data['status'] = $status;
    $data['msg'] = $msg;
    echo json_encode($data);
}
