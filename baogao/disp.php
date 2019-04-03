<?php
/* $Id: disp.php,v 1.9 2006-12-17 13:06:28 liushiwei Exp $ */
if($_head=='') $_head='head';
eval('echo "'.gettemplate($_head).'";');
eval('echo "'.gettemplate($_file).'";');
eval('echo "'.gettemplate(bottom).'";');
toexit();
?>
