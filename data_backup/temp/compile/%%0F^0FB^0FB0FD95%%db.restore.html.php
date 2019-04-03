<?php /* Smarty version 2.6.26, created on 2014-07-23 15:44:18
         compiled from db.restore.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'db.restore.html', 41, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script language="javascript">
<?php echo '
$(function(){
    $("img[backup_name]").click(function(){
        if($(this).attr(\'expanded\')=="true"){
            $(this).attr(\'src\', \'templates/default/style/images/treetable/tv-expandable.gif\');
            $("tr[parent=\'"+$(this).attr(\'backup_name\')+"\']").hide();
            $(this).attr(\'expanded\', "false");
        }
        else{
            $(this).attr(\'src\', \'templates/default/style/images/treetable/tv-collapsable.gif\');
            $("tr[parent=\'"+$(this).attr(\'backup_name\')+"\']").show();
            $(this).attr(\'expanded\', "true");
        }
    });
});
'; ?>

</script>
<div id="rightTop" >
    <p>数据库</p>
    <ul class="subnav">
        <li><a class="btn1" href="index.php?app=db&amp;act=backup">备份</a></li>
        <li><span>下载</span></li>
    </ul>
</div>
<div class="tdare info">
    <table cellspacing="0" class="dataTable">
        <!--<?php if ($this->_tpl_vars['backups']): ?>-->
        <tr class="tatr1">
            <td width="20" class="firstCell"><input type="checkbox" class="checkall" /></td>
            <td align="left" width="350">备份名</td>
            <td width="150">备份时间</td>
            <td width="100">备份大小</td>
            <td width="100">操作</td>
        </tr>
        <!--<?php endif; ?>-->
        <!--<?php $_from = $this->_tpl_vars['backups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['backup']):
?>-->
        <!-- <tr class="tatr2">
            <td class="firstCell"><input value="<?php echo $this->_tpl_vars['backup']['name']; ?>
" class='checkitem' type="checkbox" /></td>
            <td align="left" width="350"><img style="cursor:pointer" backup_name="<?php echo $this->_tpl_vars['backup']['name']; ?>
" src="templates/default/style/images/treetable/tv-expandable.gif" /> <?php echo ((is_array($_tmp=$this->_tpl_vars['backup']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
            <td><?php echo $this->_tpl_vars['backup']['date']; ?>
</td>
            <td></td>
            <td>
            <span>
            <a name="drop" href="javascript:drop_confirm('确定要删除备份吗？', 'index.php?app=db&amp;act=drop&amp;backup_name=<?php echo $this->_tpl_vars['backup']['name']; ?>
');">
            删除</a></span>
            </td>
        </tr> -->
            <!--<?php $_from = $this->_tpl_vars['backup']['vols']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['vol']):
?>-->
            <tr class="tatr2" parent="<?php echo $this->_tpl_vars['backup']['name']; ?>
">
                <td class="firstCell"><input value="<?php echo $this->_tpl_vars['backup']['name']; ?>
" class='checkitem' type="checkbox" /></td>
                <td align="left" width="350"><img style="margin-left:20px" src="templates/default/style/images/treetable/tv-item.gif" /> <?php echo ((is_array($_tmp=$this->_tpl_vars['vol']['file'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
                <td><?php echo $this->_tpl_vars['backup']['date']; ?>
</td>
                <td><?php echo $this->_tpl_vars['vol']['size']; ?>
</td>
                <td>
                <span>
                <a name="drop" href="javascript:drop_confirm('确定要删除备份吗？', 'index.php?app=db&amp;act=drop&amp;backup_name=<?php echo $this->_tpl_vars['backup']['name']; ?>
');">
                删除</a>
                </span>
                |
                <span>
                <a name="drop" href="index.php?app=db&amp;act=download&amp;backup_name=<?php echo $this->_tpl_vars['backup']['name']; ?>
&amp;file=<?php echo $this->_tpl_vars['vol']['file']; ?>
">下载</a>
                </span>
                </td>
            </tr>
            <!--<?php endforeach; endif; unset($_from); ?>-->
        <!--<?php endforeach; else: ?>-->
        <tr class="no_data">
            <td colspan="6">没有可恢复的备份</td>
        </tr>
        <!--<?php endif; unset($_from); ?>-->
    </table>
    <!--<?php if ($this->_tpl_vars['backups']): ?>-->
    <div id="dataFuncs">
        <div id="batchAction" class="left paddingT15">&nbsp;&nbsp;
            <input class="formbtn batchButton" type="button" value="删除" name="backup_name" uri="index.php?app=db&act=drop" presubmit="confirm('你确定要删除它吗？');" />
        </div>
        <div class="clear"></div>
    </div>
    <!--<?php endif; ?>-->
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>