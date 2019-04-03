<?php
#本文件同步云南项目的采样结果信息表szcyresult
//synctime 同步数据时间

error_reporting(E_ALL ^ E_NOTICE);
$checkLogin = false;
include(	__DIR__ ."/../../temp/config.php");




$ms_driver = 'mssql';
$ms_server = '121.42.237.115';
$ms_user ='cydata';
$ms_password ='anhenglims';
$ms_database ='cydata';

$table_name = 'szcyresult';
$fields = table_fiedls($table_name);


try{
    $ms = new PDO("dblib:host=$ms_server;dbname=$ms_database", $ms_user, $ms_password);
}catch(PDOEXception $e){
    echo 'Connection failed: '."\r\n\t" . $e->getMessage()."\r\n";
    die;
}

$sql = "select * from  $table_name 
where  cytime >='" . date("Y-m-01", strtotime("-2 month")) . "' 
order by cytime asc
";

$recordSet = $ms->query($sql);

$recordSet->setFetchMode(PDO::FETCH_ASSOC);

foreach($recordSet as $row)
{

    $data = array();
    foreach ($row as $key => $value)
    {
        $data[$key]=iconv( "GBK" ,  "UTF-8", $value);
    }
    $result_data[] = $data;
}

if(count( $result_data))
{
	$sql = "replace into $table_name ".batch_write_sql($result_data,$fields );
	$DB->query($sql);
	echo "\r\n";
	echo "insert is ok ".count( $result_data);
	echo "\r\n";
}



function table_fiedls($tablename)
{
	global $DB;
	$sql = "desc ".$tablename;
	$rows = $DB->query($sql);
	$result = [];
	while($row=  $DB->fetch_assoc($rows))
	{
		$result[]=$row['Field'];
	}
	return $result;
}
