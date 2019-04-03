<?php /* Smarty version 2.6.26, created on 2013-11-03 11:22:32
         compiled from db.backup.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="rightTop">
    <p>数据库</p>
    <ul class="subnav">
        <!--<?php if ($_GET['act'] == 'backup'): ?>-->
        <li><span>备份</span></li>
        <!--<?php else: ?>-->
        <li><a class="btn1" href="index.php?app=db&amp;act=backup">
        备份</a></li>
        <!--<?php endif; ?>-->
        <!--<?php if ($_GET['act'] == 'restore'): ?>-->
        <li><span>下载</span></li>
        <!--<?php else: ?>-->
        <li><a class="btn1" href="index.php?app=db&amp;act=restore">
        下载</a></li>
        <!--<?php endif; ?>-->
    </ul>
</div>

<div class="info">
    <form method="post" enctype="multipart/form-data">
        <table class="infoTable">
            <tr>
              <th class="paddingT15">备份方式:</th>
              <td class="paddingT15 wordSpacing5">
                <input name="backup_type" type="hidden" id="backup_all" value="backup_all" />
                <label for="backup_all">备份全部数据</label></td>
            </tr>

            <input id="no" type="hidden" name="ext_insert" value="1" />
            <input name="vol_size" type="hidden" value="512000" />

            <tr>
                <th class="paddingT15">备份名:</th>
                <td class="paddingT15 wordSpacing5">
                 <input name="backup_name" value="<?php echo $this->_tpl_vars['backup_name']; ?>
" />
                 <label class="field_notice">备份名字由1到20位数字、字母或下划线组成</label>
                 </td>
            </tr>
            <tr>
                <th></th>
                <td class="ptb20">
                    <input class="formbtn" type="submit" value="提交" onclick="return drop_confirm('为保证数据完整性请确保您的站点处于关闭状态，您确定要马上执行当前操作吗？');"/>
                    <input class="formbtn" type="reset" name="Submit2" value="重置" />            </td>
            </tr>
        </table>
  </form>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>