<?php

$to = "luolei@anheng.com.cn";
$subject = "Test mail";
$message = "<h1>Hello! This is a simple email message.</h1>";
$from = "luolei@anheng.com.cn";
$headers = "From: $from";
mail($to,$subject,$message,$headers);
echo "Mail Sent.";


?>