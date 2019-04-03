<?php /* Smarty version 2.6.26, created on 2013-11-05 09:11:58
         compiled from db.message.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_tpl_vars['charset']; ?>
" />
<title>数据导入</title>
<link href="<?php echo $this->_tpl_vars['site_url']; ?>
/templates/default/style/admin.css" rel="stylesheet" type="text/css" />
<style>
<!--
<?php echo '
body {background: none}
h1 {font-size: 12px; color: #444; line-height: 55px; background: url(templates/style/images/welcome_h1.gif); padding-left: 2%}
dl { background: url(templates/style/images/welcome.gif) no-repeat left 10px; padding-left: 40px; margin: 35px 0 45px 15%}
dt {line-height: 60px; color: #009de6; font-weight:bold;}
dd {line-height: 18px; color: #444;}
a {color: #06c}
p {color: #999; border-top: 1px solid #cbe4f5; text-align: center; padding-top: 20px;}
'; ?>

-->
</style>
</head>

<body>
<div id="rightTop">
    <p>数据库</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=db&amp;act=backup">备份</a></li>
        <li><a class="btn1" href="index.php?app=db&amp;act=restore">下载</a></li>
    </ul>
</div>
<div class="info">
<dl>
    <dt><?php echo $this->_tpl_vars['title']; ?>
</dt>
    <!--<?php if ($this->_tpl_vars['auto_redirect']): ?>-->
    <script>setTimeout("window.location.replace('<?php echo $this->_tpl_vars['auto_link']; ?>
');", 1250);</script>
    <!--<?php else: ?>-->
    <!--<?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>-->
    <dd><a target="_blank" href="<?php echo $this->_tpl_vars['file']['href']; ?>
"><?php echo $this->_tpl_vars['file']['name']; ?>
</a></dd>
    <!--<?php endforeach; endif; unset($_from); ?>-->
    <!--<?php endif; ?>-->
</dl>
</div>
<p></p>
</body>
</html>