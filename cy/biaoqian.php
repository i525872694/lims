<?php
include '../temp/config.php';


$query=$DB->query("SELECT cr.*,s.site_code FROM `cy_rec` as cr left join `cy` on `cy`.id = `cr`.cyd_id LEFT JOIN `sites` AS s ON cr.sid=s.id WHERE `cy`.`fzx_id`='$u[fzx_id]' and `cy`.cy_date LIKE '{$_GET['cy_date']}%' AND cr.`sid`>='0'  ORDER BY id");
$arr = array();
$i = 1;
while($row = $DB->fetch_assoc($query)){
    $lines .= <<<EOF
    <tr>
        <td>$i</td>
        <td>$row[site_name]</td>
        <td>$row[bar_code]</td>
        <td>$row[site_code]</td>
    </tr>
EOF;
    $i++;
}

disp('cy/biaoqian');