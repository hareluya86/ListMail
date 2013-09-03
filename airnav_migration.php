<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$sqlhost = 'airnavsystems.com';
$sqluser = 'newsletter';
$sqlpass = 'newsletter123*';
$sqldb = 'ansys';


echo '<span>Setup database</span><br>';
echo '<a href="./dropdb.php">Delete the existing database</a><br>';
echo '<a href="./createtable.php">Create new empty table</a><br>';
echo '<span>Insert list</span><br>';
echo '<span>List number: </span><input type="text" /><br>';
echo '<input type="submit" name="Insert" />';

$utable = 'newsletter_subscribers';

echo '<span>Table opertaions</span><br>';
echo '<a href="./droptable.php">Drop table</a>';
echo '<a href="./createtable.php">Create table</a>';
echo '<span>Insert records</span><br>';
echo '<a href="./insertrecords.php">Create table</a>';


?>
