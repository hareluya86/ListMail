<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);

$cmd = "select id,uid,list,fname,lname,email,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,cseq,cdel,cnf,dateadd,ipaddr,refurl,htmail from $utable where uid = '".addslashes($u)."'";
$urows = mysql_query($cmd);

if(mysql_num_rows($urows)==0){ echo "There was a problem processing your confirmation. You may not have responded in time, or may have accidentally subscribed to our list twice by double-clicking the signup form button or refreshing the post-signup page.  Please click the confirmation link in the most recent email you received or re-subscribe."; exit; }

list($id,$uid,$list,$fname,$lname,$email,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$userseq,$userdel,$confirmed,$today,$ipaddr,$refurl,$html) = mysql_fetch_row($urows);

// if already confirmed, show dupe message
if($confirmed=='1'){
 $chid = listopts('chtmlid',$list);
 $eid = listopts('errorid',$list);
 $data = chtml('suberror',$chid);
 list($data) = processmsg($id,$data,'','','0');
 $msg = emsg('email_dupe',$eid).'<br>';
 $data = str_replace('!data',$msg,$data);
 if(strpos(' '.$data,'URL:')==1) header('Location: '.str_replace('URL:','',$data)); else echo $data;
 exit();
}
     
$ipaddr = getip();

mysql_query("update $utable set cnf = '1', ipaddr = '".addslashes($ipaddr)."' where id = '".addslashes($id)."'");
if(file_exists('./signup-xtra.php')){
 $userid = $id;
 include('./signup-xtra.php');
 mysql_select_db($sqldb,$link);
}

$lrow = mysql_query("select welcact from $ltable where listnum = '".addslashes($list)."'",$link);
list($welcact)=mysql_fetch_row($lrow);
if($welcact=='1'){
 sendwelcome($id);
}

$narr = getnotifs($list);
if($narr[0]=='1') sendnotif('subscriber',$id);

$chid = listopts('chtmlid',$list);
$eid = listopts('errorid',$list);
$data = chtml('subscribe',$chid);
list($data) = processmsg($id,$data,'','','0');
if(strpos(' '.$data,'URL:')==1) header('Location: '.str_replace('URL:','',$data)); else echo $data;

// EOF
?>
