<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$today = date('Ymd');
$dt1 = date('Ymd',strtotime('2013-09-28 02:09:09'));

if($dt1>=$today) echo 'Later';
else echo 'Earlier';
?>
