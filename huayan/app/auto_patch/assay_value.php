<?php
$alter_status = true;
$table_msg = 'assay_order表更新';
$JcxmApp = new JcxmApp();
$JcxmApp->init_order_xm();
return error_msg($alter_status, $table_msg);
?>