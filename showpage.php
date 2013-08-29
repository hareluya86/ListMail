<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

// showpage.php.. accepting:  m=$type$id,

header('Content-type: text/html');

// u userid
// m format abbb

$t = $m[0];
$m = substr($m,1,strlen($m)-1);

$t = addslashes($t);
$m = addslashes($m);
$u = addslashes($u);

switch($t){
 case '1': $cmd = "select htcontent from $ttable where id = '$m'"; break;
 case '2': $cmd = "select htcontent from $ftable where id = '$m'"; break;
 case '3': $cmd = "select welcht from $ltable where listnum = '$m'"; break;
 case '4': $cmd = "select cnfht from $ltable where listnum = '$m'"; break;
}

$mrow = mysql_query($cmd) or die('db error');
if(@mysql_num_rows($mrow)>0){
 list($html)=mysql_fetch_row(mysql_query($cmd));
 // get id
 list($id)=mysql_fetch_row(mysql_query("select id from $utable where uid = '$u'"));
 $html = processmsg($id,'','',$html,'',$t,$m);
 $html = $html[2];
 echo $html;
} else {
 echo "Error, message not found.<br>";
}

// EOF
?>
