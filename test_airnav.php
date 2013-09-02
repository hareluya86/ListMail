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
$res = mysql_query("SHOW DATABASES");
while ($row = mysql_fetch_assoc($res)) {
    $dbname = $row['Database'];
	echo "<h3>".$dbname . "</h3>";
	$sql = "SHOW TABLES FROM $dbname";
	$result = mysql_query($sql);
	
	if (!$result) {
		echo "DB Error, could not list tables\n";
		echo 'MySQL Error: ' . mysql_error();
		exit;
	}
	while ($row = mysql_fetch_row($result)) {
		echo "<span style='margin-left: 10px'>".$dbname." Table: {$row[0]}</span><br/>";
	}

}
?>