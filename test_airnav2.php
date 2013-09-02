<?php

$sqlhost = 'airnavsystems.com';
$sqluser = 'newsletter';
$sqlpass = 'newsletter123*';
$sqldb = 'listmail';

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
if(!$link){
	echo 'Cannot connect!';
	exit;
}

$sql = "SHOW TABLES FROM mysql";
$result = mysql_query($sql);

if (!$result) {
	echo "DB Error, could not list tables\n";
	echo 'MySQL Error: ' . mysql_error();
	exit;
}
while ($row = mysql_fetch_row($result)) {
    $tablename = $row[0];
	echo "<H3> Table: {$tablename}</H3><br/>";
	$sql2 = "SHOW COLUMNS FROM mysql.".$tablename;
	$result2 = mysql_query($sql2);
	if(!$result2){
		echo "<span>No columns in table ".$tablename."</span><br/>".mysql_error();
		exit;
	}
	while($row2 = mysql_fetch_row($result2)){
		echo "<span style='margin-left: 10px'> Field: {$row2[0]}</span><br/>";
	}
}

/*
$sql = "SELECT FROM mysql LIMIT 0,50";
$result = mysql_query($sql);
if (!$result) {
	echo "DB Error, could not list tables\n";
	echo 'MySQL Error: ' . mysql_error();
	exit;
}

while ($row = mysql_fetch_row($result)) {
	echo "<span style='margin-left: 10px'>".$dbname." Table: {$row[0]}</span><br/>";
}
*/
?>