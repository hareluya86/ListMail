<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include("./config.php");

// test connection
$res = mysql_connect($sqlhost);
if(!$res){
    echo "Connection fail!";
    exit;
}
echo "Connected!";

?>
