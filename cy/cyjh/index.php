<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-24
 * Time: 上午11:47
 */


include __DIR__ .'/../../temp/config.php';

$ag_grid_data = jiexi_ag_grid_table('cyjh');

$t = json_decode($ag_grid_data['columnDefs'],true) ;

$editor_field = range(2,13);
foreach ($editor_field as $i)
{
    $t[$i]['cellClass']='jumpLink';
    $t[$i]['editable']=true;
}
$ag_grid_data['columnDefs']=json_encode($t);

unset($t);

$t = json_decode($ag_grid_data['gridOptions'],true) ;

$t['stopEditingWhenGridLosesFocus']=true;
$ag_grid_data['gridOptions']=json_encode($t);
unset($t);

$years = date("Y");

disp('cy/cyjh/daoru');
