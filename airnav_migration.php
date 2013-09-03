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

?>
