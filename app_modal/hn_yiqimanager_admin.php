<?php
include '../huayan/ahlims.php';
$lims = new DefaultApp();
//导航
$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'实验室仪器一览','href'=>"$rooturl/app_modal/hn_yiqimanager_admin.php")
);
$title='';
if(!empty($_POST['action'])){
	$sql="SELECT * FROM `yiqi` WHERE `id` = '$_POST[id]' limit 0,1";
     $A=$DB->query($sql);
	while($r=$DB->fetch_assoc($A)){
		if(!empty($r['yq_jiage'])){
			$jiage=$r['yq_jiage'].'万元';
		}else{
			$jiage='';
		}
    $id=$r['id'];
    $riqi=date('m-d',time());
    $sql="select `shiwen`,`wendu` from `shiwen_yiqi` where `yiqi_id`='$id' and `riqi`='$riqi' order by riqi asc limit 0,1";
    $notice_info=$DB->fetch_one_assoc($sql);
    if($notice_info[shiwen]<=$r[shidu_max]&&$notice_info[shiwen]>=$r[shidu_min]){
      $Notice='';
    }else{
      $Notice="color:red";
    }
    if($notice_info[wendu]<=$r[wendu_max]&&$notice_info[shiwen]>=$r[wendu_min]){
      $Notice2='';
    }else{
      $Notice2="color:red";
    }
    if(count($notice_info)=='2'){
      $shiwen="<ul><li><span>室温:$notice_info[shiwen]°C</span> &nbsp&nbsp&nbsp&nbsp室温上限:$r[shidu_max]°C &nbsp&nbsp&nbsp&nbsp室温下限：$r[shidu_min]°C</li><li><span style='$Notice2'>湿度: $notice_info[wendu]%</span> &nbsp&nbsp&nbsp&nbsp湿度上限: $r[wendu_max]%&nbsp&nbsp&nbsp&nbsp 湿度下限:$r[wendu_min]%</li></ul>";
    }else{
      if($r[shidu_max]!=''){
        $shiwen=" &nbsp&nbsp&nbsp&nbsp室温上限:$r[shidu_max]°C &nbsp&nbsp&nbsp&nbsp室温下限：$r[shidu_min]°C";
      }else{
        $shiwen="  &nbsp&nbsp&nbsp&nbsp室温要求未设置";
      }
      if($r[shidu_max]!=''){
        $shidu=" &nbsp&nbsp&nbsp&nbsp湿度上限: $r[wendu_max]%&nbsp&nbsp&nbsp&nbsp 湿度下限:$r[wendu_min]%";
      }else{
        $shidu="  &nbsp&nbsp&nbsp&nbsp湿度要求未设置";
      }
      $shiwen="<ul><li><span>没有记录</span>$shiwen</li><li>没有记录$shidu</li></ul>";
    }
    $lines.=temp('hn_yiqimanager_line_admin.html');
	}
	echo $lines;
	exit();
}


$content='';
$sql="select * from `yiqi`";
$query=$DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
  $arr[]=$row;
}
$query_lou=$DB->query("select distinct `yq_lou` from `yiqi` where `fzx_id`=$u[fzx_id] order by `yq_lou` asc ");
while($row=$DB->fetch_assoc($query_lou)){
    $lou_arr[]=$row[yq_lou];
}
$lou_arr=array_filter($lou_arr);
$color_arr=array('rgb(232, 177, 13)','#307ecc','rgb(130,175,111)');
$color_i=0;
$riqi=date('m-d',time());
foreach ($lou_arr as $key => $value) {
     $num_arr=$DB->fetch_one_assoc("select count(id)as num from `yiqi` where `yq_lou`='$value' and `fzx_id`=$u[fzx_id]");
     if(empty($value)){
        $lou_show  = '未标楼';
     }else{
        $lou_show  = $value;
     }
     $content.='<div class="widget-box no-border center btn-group" style="border:none;width:100%">
    			<div class="widget-header header-color-blue zhedie" style="background:'.$color_arr[$color_i].' none repeat scroll 0% 0%; border-color: rgb(217, 237, 247); color: white; ! important; cursor: pointer;text-align:left">
    			   <h3 style="font-family:微软雅黑; font-size:25px;">'.$lou_show.'层&nbsp;&nbsp;（'.$num_arr[num].'台仪器）</h3>
    			    <span class="widget-toolbar">
    			    	<a href="#" style="color: rgb(119, 119, 119);">
    			    		<i class="1 bigger-125 icon-chevron-down"></i>
    			    	</a>
    			    </span>
    			</div>
    			<div class="widget-body" style="border: medium none; display: none;">
    				<table class="table table-hover table-bordered  center"><tr  style="height: 100%;">';
          $room_query=$DB->query("select distinct `yq_room` from `yiqi` where `yq_lou`='$value' and `fzx_id`=$u[fzx_id] order by `yq_room` asc");
          while($row=$DB->fetch_assoc($room_query)){
            if(empty($value)){
                $room_show  = '未标科';
            }else{
                $room_show  = $row['yq_room'];
            }
            //房间名称
            $room_num_arr=$DB->fetch_one_assoc("select count(id) as room_num from `yiqi` where `yq_room`='$row[yq_room]' AND `yq_lou`='$value' and `fzx_id`=$u[fzx_id]");
            $content.='
    						<td style="text-align:center;width:49%;height: 100%;" >
    						<div style="height: 100%;">
    						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
    								  <h1><b>'.$room_show.'室('.$room_num_arr[room_num].'台仪器)</b></h1>
    							</div>
    							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">';
                  //房间内仪器
                  $room_content_query=$DB->query("select * from `yiqi` where `yq_room`='$row[yq_room]' AND `yq_lou`='$value' and `fzx_id`=$u[fzx_id] order by yq_room asc");
                  while($row=$DB->fetch_assoc($room_content_query)){
                    if($row[is_shiwen]=='1')
                    {
                      $shiwen_content="<ul><li style='font-size:10px;'>室温上限:$row[shidu_max]°C  室温下限:$row[shidu_min]°C </li><li style='font-size:10px;'>湿度上限:$row[wendu_max]% 湿度下限:$row[wendu_min]%</li></ul>";
                    }else{
                      $shiwen_content="<span style='font-size:10px'>没有温湿度要求</span>";
                    }
                    $content.=<<<ETG
                    <button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($row,'$row[yq_chucangbh]','$row[id]',$(this),'$row[yq_mingcheng]');" data-toggle="modal" data-target="#myModal">
                              <img  style="width:100%;height:150px;" src='$row[yq_image]' title='$row[yq_mingcheng]'>
                              <p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$row[yq_mingcheng]</p>
                              $shiwen_content
                          </button>
ETG;
                  }
                  $content.='</div></div></td>';
          }


          $content.='</tr></table></div></div>';
          if($color_i==2){
            $color_i=0;
          }else{
            $color_i++;
          }

}



/*
$lines_4='';
$lines_5='';
$lines_6='';
////////////////////////////////////////////////////////////////////////////////4层////////////////////////////////////////////////////////////////////////////////
$sql="SELECT * FROM yiqi WHERE yq_lou LIKE '4%'  ORDER BY yq_lou ";
$re=$DB->query($sql);
$i_4=0;
$arr_4=array();
while($data=$DB->fetch_assoc($re)){
	if($data['yq_room']==$room){
			$arr_4[0][$room][$i_4]=$data;
	}else{
	$arr_4[0][$data['yq_room']][$i_4]=$data;
	}
	$room=$data['yq_room'];
$i_4++;
}
$lines_4='<div class="widget-box no-border center btn-group" style="border:none;width:100%">
			<div class="widget-header header-color-blue zhedie" style="background:  rgb(232, 177, 13) none repeat scroll 0% 0%; border-color: rgb(217, 237, 247); color: white; ! important; cursor: pointer;text-align:left">
			   <h3 style="font-family:微软雅黑; font-size:25px;">4层&nbsp;&nbsp;仪器室&nbsp;&nbsp;（'.$i_4.'台仪器）</h3>
			    <span class="widget-toolbar">
			    	<a href="#" style="color: rgb(119, 119, 119);">
			    		<i class="1 bigger-125 icon-chevron-down"></i>
			    	</a>
			    </span>
			</div>
			<div class="widget-body" style="border: medium none; display: none;">
				<table class="table table-hover table-bordered  center">';
$a_4=0;
foreach($arr_4 as $key=>$value){
	foreach($value as $k=>$val){
		if($a_4%2==0){
			if(count($val)>1){
				$lines_4.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $j=>$z){
					$lines_4.=<<<ETH
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$z[yq_chucangbh]','$z[id]',$(this));" data-toggle="modal" data-target="#myModal">
									<img  style="width:100%;height:150px;" src="$z[yq_image]" title="$z[yq_mingcheng]">
									<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$z[yq_mingcheng]</p>
							</button>
ETH;
				}
				$lines_4.='</div></div></div></td>';
			}else{
				$lines_4.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_4.=<<<ETG
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]",$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETG;
				}
				$lines_4.='</div></div></td>';
			}
		}else{
			if(count($val)>1){
				$lines_4.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $j=>$z){
					$lines_4.=<<<ETF
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$z[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
									<img  style="width:100%;height:150px;" src="$z[yq_image]" title="$z[yq_mingcheng]">
									<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$z[yq_mingcheng]</p>
							</button>
ETF;
				}
				$lines_4.='</div></div></div></td>';
			}else{
				$lines_4.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_4.=<<<ETE
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETE;
				}
				$lines_4.='</div></div></td></tr>';
			}
		}
		$a_4++;
	}
}
$lines_4.='</table>
		</div>
	</div>';
////////////////////////////////////////////////////////////////////////////////5层////////////////////////////////////////////////////////////////////////////////
$sql="SELECT * FROM yiqi WHERE yq_room LIKE '5%' ORDER BY yq_room" ;
$re=$DB->query($sql);
$i_5=0;
$arr_5=array();
while($data=$DB->fetch_assoc($re)){
	if($data['yq_room']==$room){
			$arr_5[0][$room][$i_5]=$data;
	}else{
	$arr_5[0][$data['yq_room']][$i_5]=$data;
	}
	$room=$data['yq_room'];
$i_5++;
}

$lines_5='<div class="widget-box no-border center btn-group" style="border:none;width:100%">
			<div class="widget-header header-color-blue zhedie" style="background: #307ecc none repeat scroll 0 0 border-color: rgb(217, 237, 247); color: white; ! important; cursor: pointer;text-align:left">
			   <h3 style="font-family:微软雅黑; font-size:25px;">5层&nbsp;&nbsp;理化室&nbsp;&nbsp;（'.$i_5.'台仪器）</h3>
			    <span class="widget-toolbar">
			    	<a href="#" style="color: rgb(119, 119, 119);">
			    		<i class="1 bigger-125 icon-chevron-down"></i>
			    	</a>
			    </span>
			</div>
			<div class="widget-body" style="border: medium none; display: none;">
				<table class="table table-hover table-bordered  center">';
$a_5=0;
foreach($arr_5 as $key=>$value){
	foreach($value as $k=>$val){
		if($a_5%2==0){
			if(count($val)>1){
				$lines_5.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $j=>$z){
					$lines_5.=<<<ETA
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$z[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
									<img  style="width:100%;height:150px;" src="$z[yq_image]" title="'.$z[yq_mingcheng].'">
									<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$z[yq_mingcheng]</p>
							</button>
ETA;
				}
				$lines_5.='</div></div></div></td>';
			}else{
				$lines_5.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_5.=<<<ETT
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETT;
				}
				$lines_5.='</div></div></td>';
			}
		}else{
			if(count($val)>1){
				$lines_5.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_5.=<<<ETO
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETO;
				}
				$lines_5.='</div></div></div></td>';
			}else{
				$lines_5.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_5.=<<<ETC
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETC;

				}

				$lines_5.='</div></div></td></tr>';
			}
		}
		$a_5++;
	}
}
$lines_5.='</table>
		</div>
	</div>';
////////////////////////////////////////////////////////////////////////////////6层////////////////////////////////////////////////////////////////////////////////
$sql="SELECT * FROM yiqi WHERE yq_room LIKE '6%'  ORDER BY yq_room ";
$re=$DB->query($sql);
$arr_6=array();
$i_6=0;
while($data=$DB->fetch_assoc($re)){
	if($data['yq_room']==$room){
			$arr_6[0][$room][$i_6]=$data;
	}else{
	$arr_6[0][$data['yq_room']][$i_6]=$data;
	}
	$room=$data['yq_room'];
$i_6++;
}

$lines_6='<div class="widget-box no-border center btn-group" style="border:none;width:100%">
			<div class="widget-header header-color-blue zhedie" style="background: rgb(130,175,111) none repeat scroll 0% 0%; border-color: rgb(217, 237, 247); color: white; ! important; cursor: pointer;text-align:left">
			   <h3 style="font-family:微软雅黑; font-size:25px;">6层&nbsp;&nbsp;生物室&nbsp;&nbsp;（'.$i_6.'台仪器） </h3>
			    <span class="widget-toolbar">
			    	<a href="#" style="color: rgb(119, 119, 119);">
			    		<i class="1 bigger-125 icon-chevron-down"></i>
			    	</a>
			    </span>
			</div>
			<div class="widget-body" style="border: medium none; display: none;">
				<table class="table table-hover table-bordered  center">';
$a_6=0;
foreach($arr_6 as $key=>$value){
	foreach($value as $k=>$val){
		if($a_6%2==0){
			if(count($val)>1){
				$lines_6.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $j=>$z){
					$lines_6.=<<<ETW
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$z[yq_chucangbh]",$(this));" data-toggle="modal" data-target="#myModal">
									<img  style="width:100%;height:150px;" src="$z[yq_image]" title="$z[yq_mingcheng]">
									<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$z[yq_mingcheng]</p>
							</button>
ETW;
				}
				$lines_6.='</div></div></div></td>';
			}else{
				$lines_6.='<tr  style="height: 100%;">
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_6.=<<<ETX
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="$n[yq_mingcheng]">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETX;
				}

				$lines_6.='</div></div></td>';
			}
		}else{
			if(count($val)>1){
				$lines_6.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $x=>$z){
					$lines_6.=<<<ETY
							<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$z[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
									<img  style="width:100%;height:150px;" src="$z[yq_image]" title="$z[yq_mingcheng]">
									<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$z[yq_mingcheng]</p>
							</button>
ETY;
				}
				$lines_6.='</div></div></div></td>';
			}else{
				$lines_6.='
						<td style="text-align:center;width:49%;height: 100%;" >
						<div style="height: 100%;">
						 <div class="page-header" style="background-color: rgb(217, 237, 247) !important;">
								  <h1><b>'.$k.'室('.count($val).'台仪器)</b></h1>
							</div>
							<div class="well well-lg" style="text-align:left;font-size:20px; padding: 0px; border-width: 0px 0px 0px;">
							';
				foreach($val as $m=>$n){
					$lines_6.=<<<ETZ
						<button style="width:30%;overflow:hidden;border-width:0;margin-left:20px;margin-bottom:10px;margin-top:10px;padding:0;color:black !important;background-color:white !important;" class="btn btn-primary btn-lg" onclick="show1($k,'$n[yq_chucangbh]',$(this));" data-toggle="modal" data-target="#myModal">
								<img  style="width:100%;height:150px;" src="$n[yq_image]" title="'.$n[yq_mingcheng].'">
								<p style="font-size:14px;width:100%;overflow:hidden;border-width:0;padding:0">$n[yq_mingcheng]</p>
						</button>
ETZ;
				}

				$lines_6.='</div></div></td></tr>';
			}
		}
		$a_6++;
	}
}
$lines_6.='</table>
		</div>
	</div>';*/
disp('hn_yiqimanager_admin.html');
