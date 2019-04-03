<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-24
 * Time: 上午11:51
 */
include __DIR__ .'/../../temp/config.php';
include __DIR__ .'/func.php';

$ajax = false;
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $ajax= true;
}

if(!file_exists( __DIR__.'/../../vendor/autoload.php'))
{
    $ajax? die("请先加载composer,和phpexcel"): gotourl($rooturl.'/cy/cyjh/index.php','请先加载composer,和phpexcel');

}

if(file_exists($_FILES['daoru']['tmp_name'])){//excel文件批量上传
    $path = $_FILES['daoru']['tmp_name'];
    $years = intval($_POST['jhyear'] );

    if($years<= date("Y"))  gotourl($rooturl.'/cy/cyjh/index.php','年份不合理,不应小于'.date("Y"));


    $result = cyjh_data_from_excel($path,$years);

    if(count($result)){
        $sql = "select * from cy_jh";
        $rows = $DB->query($sql);
        while ($row = $DB->fetch_assoc($rows))
        {
            $key = $row['sname'].':'.$row['jhdate'];
            unset($result[$key]);
        }
        $result = array_values($result);
        if(count($result))
        {
            $sql = "insert into cy_jh ".batch_write_sql($result,['sname','jhdate']);
            $DB->query($sql);
            gotourl($rooturl.'/cy/cyjh/index.php','导入成功');
        }
        gotourl($rooturl.'/cy/cyjh/index.php','数据已经存在');
    }else{
        gotourl($rooturl.'/cy/cyjh/index.php','没有有效数据');
    }
}else{//单个添加或者修改

    $jhdate = get_str($_POST['jhdate']);
    $sname = get_str($_POST['sname']);
    $years = intval(date("Y",strtotime($jhdate)) );


    $error_msg = [];
    if($_POST['type'] != 'del' && !$jhdate)
    {

        if($years< date("Y")) $error_msg[] = '年份不合理,不应小于'.date("Y");
        if(!$jhdate)  $error_msg[] = '无效的日期参数';
      if(count($error_msg))
      {
          $ajax?die(implode(',',$error_msg)):goback(implode(',',$error_msg));
      }
    }


    $sql_map = [
        'del'=>"delete from cy_jh where id='".intval($_POST['id'])."' limit 1 ",
        'update'=>"update  cy_jh  set `jhdate`='$jhdate'  where id='".intval($_POST['id'])."' limit 1 ",
        'insert'=>"insert into cy_jh  set `jhdate`='$jhdate',`sname`='$sname'   ",
    ];

    $sql = $sql_map[$_POST['type']];
    $DB->query($sql);
    $ajax?die('ok'):goback('设置成功');

}



