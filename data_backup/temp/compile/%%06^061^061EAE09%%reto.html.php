<?php /* Smarty version 2.6.26, created on 2013-06-17 11:45:46
         compiled from reto.html */ ?>
<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
 <head>
  <title>正在跳转中</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo '
 <style type="text/css">
  <!--
	body {text-align:center;
		background-color:#EEF2FB;}
	#box{
		width:500px;height:120px;
		margin:auto auto;
		margin-top:150px;
		text-align:center;
		background:url(\'images/login-content-bg.gif\');
		border:1px solid #D1D5E1;
	}
	p{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	line-height: 25px;
	color: #666666;
}
a{text-decoration:none; color:#06C;}
  -->
 </style>
 '; ?>

 <?php if ($this->_tpl_vars['js']): ?>
 <script type="text/javascript" language="javascript">
 <?php echo $this->_tpl_vars['js']; ?>

 </script>
 <?php endif; ?>
 <meta http-equiv="refresh" content="<?php echo $this->_tpl_vars['time']; ?>
;url=<?php echo $this->_tpl_vars['url']; ?>
">
 </head>
 <body>
  <div id="box">
   <p>您的页面正在跳转中请稍候。。。</p>
   <p style="color:#ff66cc; font-weight:bold;"><?php echo $this->_tpl_vars['content']; ?>
</p>
   <p><a href="<?php echo $this->_tpl_vars['url']; ?>
">如果您的页面没有跳转，请点击这里</a></p>
  </div>
 </body>
</html>