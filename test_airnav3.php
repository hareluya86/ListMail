<?php

$sqlhost = 'airnavsystems.com';
$sqluser = 'newsletter';
$sqlpass = 'newsletter123*';
$sqldb = 'ansys';

$sqltable = 'newsletter_subscribers';

$res = mysql_connect($sqlhost,$sqluser,$sqlpass);
if(!$res){
	echo "Connection error: ".mysql_error();
	exit;
}
/*
$sql = 'SHOW TABLES FROM '.$sqldb;
$result = mysql_query($sql);
if (!$result) {
    echo "DB Error, could not list tables\n";
    exit;
}

while ($row = mysql_fetch_row($result)) {
    echo "<span>Table: {$row[0]}</span><br/>";
}*/
//select from table
$sql = 'SELECT * FROM '.$sqldb.'.'.$sqltable.' LIMIT 0,100';
$result = mysql_query($sql);
echo $sql;
if(!$result){
	echo 'Error executing SQL: '.mysql_error();
	exit;
}
while($row = mysql_fetch_row($result)){
	foreach($row as $cell)
		echo "<span>$cell</span>";
}
//select table columns
$sql_column = 'SHOW COLUMNS FROM '.$sqldb.'.'.$sqltable;
$result_column = mysql_query($sql_column);
if(!$result_column){
	echo 'Error executing SQL: '.mysql_error();
	exit;
}
while($row = mysql_fetch_row($result_column)){
	echo '<span> '.$row[0].' </span>';
}
/*
echo '<table>';
while($row = mysql_fetch_row($result)){
	echo '<tr>';
	while($row_column = mysql_fetch_row($result_column)){
		echo '<td>'.$row[$row_column].'</td>';
	}
	echo '</tr>';
}
echo '</table>';
*/
?>