<?php
/**
 * 功能：前台修改模板写入文件
 * 作者：罗磊
 * 日期：2014-4-14
 * 描述 获取来自前台插件修改模板内容，写入到指定文件
*/
 include '../temp/config.php';

$bg = $_POST['bg'];                //修改后的模板内容      
$lx = $_GET['lx'];                 //操作类型
$filename = $_GET['filename'];     //修改的文件名称

if($lx == 1)
     {
	  $fp = fopen("/home/www/newlims/template/$filename","w+");  //打开文件  
	  fwrite($fp,$bg);                                           //写入文件
	  fclose($fp);                                               //关闭文件
	  echo bgtemp($filename);	
    }
 if($lx == 2){

$sql="SELECT * FROM `report_template` where id ='$mbid'";
$rows = $DB->query($sql);
$row = $DB->fetch_assoc($rows);
	    //print_rr($row);
		//echo $row['fc'];
$mbname =array();
$mbname['1']=$row['fc'];              //报告封面
$mbname['2']=$row['bt'];                //表头数据
$mbname['3']=$row['audit'];            //报告签名
$mbname['4']=$row['exp'];              //报告说明

$baogao =  explode("<!--|-->",$bg);    //区分报告分割付 "<!--|-->"
           
		   
     	$i = 1;
while($baogao[$i]){
	   
		 
		  // print_rr($baogao[$i]);
         
   		  $fp = fopen("/home/www/newlims/template/$mbname[$i]","w+");  //打开文件  
           fwrite($fp,$baogao[$i]);                                   //写入文件
           fclose($fp);                                               //关闭文件
	    
		  $i++; 
	  }
	echo    '写入成功';
    exit;  
  }
  if($lx == 3){

  
   
   exit;
  }
  
  
?>
