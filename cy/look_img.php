<?php
include '../temp/config.php';

$info=$DB->fetch_one_assoc("select `xc_img` from `cy_rec` where `id`='$_GET[id]'");
$img_arr=explode(',',$info['xc_img']);


$img_str=$img_div='';
if(count($img_arr)<1){
    echo "<script>alert('没有图片')</script>";
    exit;
}
foreach($img_arr as $k=>$v){
    $img_url = 'http://39.108.11.235/GuoyiSzcy_web/sys/file/downloadFile?fileId='.$v;
    $img_div.=<<<EOF
    <li class="col-xs-6 col-sm-4 col-md-3"  data-src="$img_url">
    <a href="">
        <img class="img-responsive" src="$img_url">
    </a>
</li>
EOF;
}
echo $a=temp('cy/look_img');
?>