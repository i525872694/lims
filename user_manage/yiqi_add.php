<?php
    include '../huayan/ahlims.php';
	$lims = new DefaultApp();
	if($_POST){
        $sql="insert into `yiqi`(`yq_mingcheng`,`yq_zzcangjia`,`yq_jiage`,`yq_xinghao`,`yq_chucangbh`,`yq_neibubh`,`yq_gouzhirq`,`yq_baoguanren`,`is_shiwen`,`shidu_max`,`shidu_min`,`wendu_max`,`wendu_min`,`yq_jiandingriqi`,`yq_scriqi`,`yq_image`,`yq_lou`,`yq_room`,`fzx_id`)values('$_POST[yq_mingcheng]','$_POST[yq_zzcangjia]','$_POST[yq_jiage]','$_POST[yq_xinghao]','$_POST[yq_chucangbh]','$_POST[yq_neibubh]','$_POST[yq_gouzhirq]','$_POST[yq_baoguanren]','$_POST[is_shiwen]','$_POST[shidu_max]','$_POST[shidu_min]','$_POST[wendu_max]','$_POST[wendu_min]','$_POST[yq_jiandingriqi]','$_POST[yq_scriqi]','$_POST[yq_new_image]','$_POST[yq_lou]','$_POST[yq_room]','$u[fzx_id]')";
		$DB->query($sql);
		echo "<script>location.href='../app_modal/yiqi_list.php'</script>";
		exit;
	}
    $lims->disp('app_modal/yiqi_add.html');
   ?>
