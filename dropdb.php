<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './airnav_migration.php';

$utable = 'newsletter_subscribers';

// test connection
$res = mysql_connect($sqlhost,$sqluser,$sqlpass);
if(!$res){
    echo "Connection fail!";
    exit;
}
echo "Connected!";
mysql_select_db($sqldb,$res);

$sqlcmd = 'DROP TABLE '.$utable;
mysql_query($sqlcmd) or die('admin-12-' . mysql_error());
echo 'Table '.$utable.' deleted';

?>
