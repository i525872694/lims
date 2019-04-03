<?php
    include '../huayan/ahlims.php';
	$lims = new DefaultApp();
	if($_POST){
		$id=$_POST['id'];
		$yq_mingcheng=$_POST['yq_mingcheng'];
		$yq_zzcangjia=$_POST['yq_zzcangjia'];
		$yq_jiage=$_POST['yq_jiage'];
		$yq_xinghao=$_POST['yq_xinghao'];
		$yq_chucangbh=$_POST['yq_chucangbh'];
		$yq_neibubh=$_POST['yq_neibubh'];
		$yq_gouzhirq=$_POST['yq_gouzhirq'];
		$yq_baoguanren=$_POST['yq_baoguanren'];
		$yq_jiandingriqi=$_POST['yq_jiandingriqi'];
		$yq_room=$_POST['yq_room'];
    $yq_lou=$_POST['yq_lou'];
		$yq_image=$_POST['yq_new_image']?$_POST['yq_new_image']:$_POST['yq_image'];
		$is_shiwen=$_POST['is_shiwen'];
		$wendu_max=$_POST['wendu_max'];
		$wendu_min=$_POST['wendu_min'];
		$shidu_max=$_POST['shidu_max'];
		$shidu_min=$_POST['shidu_min'];
    $yq_scriqi=$_POST['yq_scriqi'];
		$yq_baofei=$_POST['yq_baofei'];
		$sql="update `yiqi` set `yq_mingcheng`='$yq_mingcheng',`yq_jiage`='$yq_jiage',`yq_scriqi`='$yq_scriqi',`yq_xinghao`='$yq_xinghao',`yq_state`='$_POST[yq_state]',`yq_chucangbh`='$yq_chucangbh',`yq_neibubh`='$yq_neibubh',`yq_baofei`='$yq_baofei',`yq_gouzhirq`='$yq_gouzhirq',`yq_baoguanren`='$yq_baoguanren',`yq_jiandingriqi`='$yq_jiandingriqi',`yq_room`='$yq_room',`yq_image`='$yq_image',`is_shiwen`='$is_shiwen',`wendu_max`='$wendu_max',`wendu_min`='$wendu_min',`shidu_max`='$shidu_max',`shidu_min`='$shidu_min',`yq_lou`='$yq_lou' where `id`='$id'";
		$DB->query($sql);
    if($_POST['return']=='pic'){
      $url=$rooturl.'/app_modal/hn_yiqimanager_admin.php';
    }else{
      $url=$rooturl.'/app_modal/yiqi_list.php';
    }
		echo "<script>location.href='$url'</script>";
		exit;
	}
  if($_GET['return']=='pic'){
    $url=$rooturl.'/app_modal/hn_yiqimanager_admin.php';
  }else{
    $url=$rooturl.'/app_modal/yiqi_list.php';
	}
	if($_GET['url']=='main'){
		$url=$rooturl.'/main.php';
	}
	$sql="select * from `yiqi` where `id`='$_GET[id]'";
	$yiqi_arr=$DB->fetch_one_assoc($sql);
	$select=$yiqi_arr['is_shiwen']=='1'?"selected='selected'":'';
	$select2=$yiqi_arr['is_shiwen']=='0'?"selected='selected'":'';
	$shiwen_option="<option value='1' $select>有要求</option><option value='0' $select2>无要求</option>";
	$yq_state_arr=array('启用','准用','封存','报废');
	$yq_state=arr_selected($yq_state_arr,$yiqi_arr['yq_state']);
	$yq_xiajiandingriqi=date("Y-m-d",strtotime("+1months",strtotime($yiqi_arr['yq_jiandingriqi'])));
	$lims->disp('app_modal/yiqi_save.html');
		
	//下拉列表默认选中
	function arr_selected($arr,$selected){
		foreach($arr as $k=>$v){
			if($v==$selected){
				$options.="<option value=$v selected>$v</option>";
			}else{
				$options.="<option value=$v>$v</option>";
			}
		}
		return $options;
	}
?>
