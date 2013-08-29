<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("./config.php");
include("./admin.php");

ini_set("max_execution_time","0");

// check password
$isadmin='';
if($argv[1]) $pw = $argv[1];
if($pw) $isadmin = checkpw($pw);

$debug = false;

// resume
if($isadmin=='true'){
 if($debug) echo "admin = true<br>";
 // cycle through active queues (completed = 0)
 $rows = mysql_query("select batid from $ptable where completed = '0';");
 if(@mysql_num_rows($rows)>0){
  while(list($batid)=mysql_fetch_row($rows)){
   if($debug) echo "batid <b>$batid</b> found not completed.. checking if it's active.. ";
   // check that this mailing has not been active in the last minute
   $prows = mysql_query("select lastact from $ptable where batid = '".addslashes($batid)."' and completed = '0' and lastact >= '".date("Y-m-d H:i:s",time()-60)."' ;");
   if(@mysql_num_rows($prows)==0){
    if($debug) echo "NO - checking in and resuming...<br>";
    qcheckin($batid);
    if($debug) echo "checked in.. starting email<br>";
    $sentok = domail('','daym','',$batid);
    if($debug) echo "done emailing, finish up<br>";
    if($sentok) qfinish($batid);
   } else {
    if($debug) echo "YES - don't resume<br>";
   }
  }
 } else {
  if($debug) echo "no incomplete mailings<br>";
 }
} else {
 if($debug) echo "admin = false<br>";
}
?>