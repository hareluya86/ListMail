<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include 'airnav_migration.php';

$res = mysql_connect($sqlhost,$sqluser,$sqlpass);
if(!$res){
    echo "Connection fail!";
    exit;
}
echo "Connected!<br>";
mysql_select_db($sqldb,$res);


?>
