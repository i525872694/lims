<?php
/**
 * 功能：根据 扫描抢 ，扫出的 样品编号 进行 样品接收的页面，
 * 作者：松森
 * 时间：2017-07-08
*/
include '../temp/config.php';

// 通过 GET  传递    样品编号（bar） 格式：A001

if($_GET['bar']){

$bar=$_GET['bar'];
echo " <tr>
                <td> $bar</td>
                <td> sd</td>
                <td>d </td>
                <td>d </td>
                <td> </td>
                <td> d</td>
                <td>d </td>
            </tr> ";

exit;
}


disp("bar_js.html");

