<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

if(!function_exists('domail')) exit(); // security to make sure admin.php was loaded

echo "Upgrade detected!<br><b>Please wait while your database is updated</b><br><font color=red>It is very important that you do not interrupt this process</font><br>";
echo "DB Version: $cver&nbsp;&nbsp;New DB Version: $current_version<br>";
flush();
$diemsg = "database error, file error, or you already ran this script!";

// Test MySQL permissions
// clean up first...
@mysql_query("DROP TABLE test123;");
@mysql_query("ALTER TABLE $ctable DROP test123;");
// Test it
echo "Testing MySQL permissions... "; flush();
$error = '';

$result = mysql_query("ALTER TABLE $ctable ADD test123 char(1) not null;");
$x = mysql_error(); if($x) $error = 1;
$result = mysql_query("ALTER TABLE $ctable DROP test123;");
$result = mysql_query("CREATE TABLE test123 (x char(1) not null, primary key (x));");
$x = mysql_error(); if($x) $error = 1;
$result = mysql_query("DROP TABLE test123;");
$x = mysql_error(); if($x) $error = 1;
if($error){
  echo "<font color=red>FAIL</font><br><b>The upgrade can not continue!</b><br>Please check your MySQL permissions or ask your host about ALTER, CREATE, and DROP table permissions.<br>If desired, restore the ListMail v$cver files to ensure service to your clients is not interrupted.<br>";
 exit();
} else {
 echo " Ok<br>Upgrading (Please Wait)... "; flush();
}

if($cver<'1.1'){
 // 1.1 upgrade
 // added:  quick save features, new domail.php script, version numbers
 $cmd = "ALTER TABLE $ctable ADD ver CHAR (5) not null";
 mysql_query($cmd) or die($diemsg);
 // add saved msg table
 $cmd = "CREATE TABLE $stable (
   id smallint(5) unsigned NOT NULL auto_increment,
   subject tinytext NOT NULL,
   content mediumtext NOT NULL,
   ishtml char(1) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
);";
 mysql_query($cmd) or die($diemsg);
 // example saved msg
 $cmd = "insert into $stable values('','Example Saved Message','Example Saved Message','0');";
 mysql_query($cmd) or die($diemsg);
 // update version number
 $cmd = "update $ctable set ver = '1.1' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.1';
} // end 1.1

if($cver=='1.1'){
 // 1.2 upgrade
 // rem'd: html option for confirm/welcome emails
 // added:  MIME email for html + text + attachments all in one
 $cmd ="ALTER TABLE $ltable DROP welhtml";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $ltable DROP cnfhtml";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $stable DROP ishtml";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $stable ADD htcontent MEDIUMTEXT not null";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $stable ADD fattach TINYTEXT not null";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $ftable DROP ishtml";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $ftable ADD htcontent MEDIUMTEXT not null AFTER content";
 mysql_query($cmd) or die($diemsg);
 $cmd ="ALTER TABLE $ftable ADD fattach TINYTEXT not null AFTER htcontent";
 mysql_query($cmd) or die($diemsg);
 // update version number
 $cmd = "update $ctable set ver = '1.2' where 1";
 mysql_query($cmd) or die($diemsg);
 echo "What's new in this version:<br>MIME multi-part email.<br>Allows you to send HTML <b>and</b> TEXT at the same time!<br>Unlimited file attachments for each followup and solo mailing!<br><br>If you were using HTML email before, you will need to paste all of your messages into the added entry box and input text versions for users whose email client's don't support HTML.<br>";
 $cver = '1.2';
}

if($cver=='1.2'){
 // 1.3 upgrade
 // added: unique code for each user, link tracking, shorter remove link
 echo "<table border=0 cellspacing=0 cellpadding=0><tr><td>\n";
 $cmd = "ALTER TABLE $ctable ADD ktrack CHAR (1) not null";
 mysql_query($cmd) or die($diemsg);
 echo "..added track on/off switch<br>";
 $cmd = "ALTER TABLE $utable ADD uid CHAR (7) not null AFTER id";
 mysql_query($cmd) or die($diemsg);
 echo "..added unique user id's<br>";
 $cmd = "ALTER TABLE $utable ADD htmail char(1) not null";
 mysql_query($cmd) or die($diemsg);
 echo "..added HTML/Text for each user<br>";
 $cmd = "update $utable set htmail = '1' where 1";
 mysql_query($cmd);
 // set current users with a unique id
 $urows = mysql_query("select id from $utable where 1");
 while(list($id)=mysql_fetch_row($urows)){
  $uniq = '';
  while(!$uniq){
   $uniq_str = unique_id(7);
   if(mysql_num_rows(mysql_query("select id from $utable where uid = '$uniq_str'",$link))==0) $uniq=1;
  }
  $cmd = "update $utable set uid = '$uniq_str' where id = '$id'";
  mysql_query($cmd) or die($diemsg);
 }
 $nusers = mysql_num_rows(mysql_query("select id from $utable where 1"));
 echo "..calculated id's for $nusers existing users<br>";
 // add new tracking table
 $cmd = "CREATE TABLE $htable (
   id mediumint(8) unsigned NOT NULL auto_increment,
   uid smallint(5) unsigned DEFAULT '0' NOT NULL,
   kid smallint(5) unsigned DEFAULT '0' NOT NULL,
   date datetime NOT NULL,
   PRIMARY KEY (id)
);";
 mysql_query($cmd) or die($diemsg);
 echo "..added link hit tracking table<br>\n";
 // update version number
 $cmd = "update $ctable set ver = '1.3' where 1";
 mysql_query($cmd) or die($diemsg);
 echo "<br><br>What's new in this version:<br>HTML/Text signup/sending option.<br>Advanced link tracking option.<br>Re-did the user menu.<br>";
 echo "</td></tr></table>\n";
 $cver = '1.3';
}


if($cver=='1.3'){
 // 1.4 upgrade (Feb '02)
 // add sent msg table
 echo "upgrade detected!!<br>";
 $cmd=array("CREATE TABLE $ttable (
   id smallint(5) unsigned NOT NULL auto_increment,
   subject tinytext NOT NULL,
   content mediumtext NOT NULL,
   htcontent mediumtext NOT NULL,
   fattach tinytext NOT NULL,
   datesent date DEFAULT '0000-00-00' NOT NULL,
   msgtype char(1) NOT NULL,
   PRIMARY KEY (id)
   );",

  // user bounces
  "ALTER TABLE $utable ADD bounces TINYINT (2) not null",

  // config changes
  "ALTER TABLE $ctable ADD errhost VARCHAR (40) not null,
   add errport SMALLINT not null,
   ADD erraddr VARCHAR (40) not null,
   ADD erruser VARCHAR (40) not null,
   ADD errpass VARCHAR (20) not null,
   ADD errchk CHAR(1) DEFAULT '0' NOT NULL",
  "ALTER TABLE $ctable ADD delay1 smallint not null,
   ADD delay2 TINYINT (2) UNSIGNED not null,
   ADD delay3 TINYINT UNSIGNED not null",
  "ALTER TABLE $ctable ADD nbounce TINYINT (2) UNSIGNED not null",
  "ALTER TABLE $stable ADD kind CHAR (1) not null AFTER id",
  "ALTER TABLE $stable ADD uid mediumint(9) not null AFTER kind",
  "ALTER TABLE $ctable ADD mthost VARCHAR (40) not null , ADD mtport SMALLINT default '0' not null , ADD mtauth CHAR(1) default '0' not null, ADD mtuser VARCHAR (40) not null , ADD mtpass VARCHAR (25) not null , ADD mtchk CHAR (1) not null",
  "ALTER TABLE $ctable ADD mtphost VARCHAR (40) not null , ADD mtpport smallint not null , ADD mtpuser VARCHAR (40) not null , ADD mtppass VARCHAR (25) not null , ADD mtpchk CHAR (1) not null");

 while(list($key,$val)=each($cmd)){
  mysql_query($val);
  // echo "upgrade $key.. of sizeof($cmd)";
 }
 // update saved messages
 $cmd = "update $stable set kind = 's' where 1";
 mysql_query($cmd);

 // update version number
 $cmd = "update $ctable set ver = '1.4' where 1";
 mysql_query($cmd) or die($diemsg);


 $cmd = "update $ctable set delay1 = '500', delay2 = '4', delay3 = '500', mthost = 'mail.yoursite.com', mtport = '25', errhost = 'mail.yoursite.com', erraddr = 'errors@yoursite.com', erruser = 'errors@yoursite.com' where 1";
 mysql_query($cmd);
 //
 $cver = '1.4';
}

if($cver=='1.4'){
 // 1.41 fix
 // put helo command back in
 $cmd = "ALTER TABLE $ctable ADD mthelo VARCHAR (30) not null AFTER mtport , ADD mthelochk CHAR (1) not null AFTER mthelo";
 mysql_query($cmd);
 $cmd = "update $ctable set mthelo = 'domain', mthelochk = '1' where 1'";
 mysql_query($cmd);
 // update version number
 $cmd = "update $ctable set ver = '1.41' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.41';
}

if($cver=='1.41'){
 // 1.42 fix
 // fix tinyint db vars to smallint..
 $cmd = "ALTER TABLE $ctable CHANGE delay1 delay1 SMALLINT UNSIGNED DEFAULT '0' not null";
 mysql_query($cmd);
 $cmd = "ALTER TABLE $ctable CHANGE delay3 delay3 SMALLINT UNSIGNED DEFAULT '0' not null";
 mysql_query($cmd);
 $cmd = "ALTER TABLE $ctable CHANGE errport errport SMALLINT UNSIGNED DEFAULT '0' not null";
 mysql_query($cmd);
 $cmd = "ALTER TABLE $ctable CHANGE mtport mtport SMALLINT UNSIGNED DEFAULT '0' not null";
 mysql_query($cmd);

 // update version number
 $cmd = "update $ctable set ver = '1.42' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.42';
}

if($cver=='1.42'){
 // update version number
 $cmd = "update $ctable set ver = '1.5' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.5';
}

// oops, released the last update to some users prematurely

if($cver=='1.5'){
 // 1.51 big changes

 // html+fatts for welcome and confirm
 $cmd="alter table $ltable add welcht mediumtext not null after welcom, add welcf tinytext not null after welcht, add welcact char(1) not null after welcf, add cnfht mediumtext not null after cnfmesg, add cnff tinytext not null after cnfht";
 mysql_query($cmd);
 mysql_query("update $ltable set welcact = '1' where 1");

 // extra user fields with custom fields and codes!@#

$cmd = "CREATE TABLE $dtable (
   id smallint(5) unsigned NOT NULL auto_increment,
   list char(3) NOT NULL,
   ref varchar(40) NOT NULL,
   typ varchar(10) NOT NULL,
   data1 text NOT NULL,
   data2 text NOT NULL,
   PRIMARY KEY (id)
);";
mysql_query($cmd) or die($diemsg);

// default codes for each list
// get existing data and work it in.. date format from ltable and delete that field..
$cmd = "select datef from $ctable where 1";
$crow = mysql_query($cmd);
list($datef)=mysql_fetch_row($crow);

$cmd = "ALTER TABLE $ctable DROP datef";
mysql_query($cmd) or die($diemsg);
// put global codes in
 $cmd = "insert into $dtable (list,ref,typ) values('*','fname','fname');";
 mysql_query($cmd) or die($diemsg);
 $cmd = "insert into $dtable (list,ref,typ) values('*','lname','lname');";
 mysql_query($cmd) or die($diemsg);
 $cmd = "insert into $dtable (list,ref,typ) values('*','fullname','fullname');";
 mysql_query($cmd) or die($diemsg);
 $cmd = "insert into $dtable (list,ref,typ) values('*','email','email');";
 mysql_query($cmd) or die($diemsg);
 $cmd = "insert into $dtable (list,ref,typ,data1,data2) values('*','mytext','text','Example custom text code.','<span style=\"font: 10pt helvetica\">Example <a href=#>custom</a> text!</span>');";
 mysql_query($cmd) or die($diemsg);

 // get datef
 $datef = addslashes($datef);
 $cmd ="insert into $dtable (list,ref,typ,data1) values('*','date','date','$datef');";
 mysql_query($cmd) or die($diemsg);
 $cmd ="insert into $dtable (list,ref,typ,data1) values('*','date2','date','l, F jS, Y');";
 mysql_query($cmd) or die($diemsg);

 // get list 1 remtext and make it default
 $cmd = "select remtext from $ltable where 1 order by id limit 0,1";
 $rrow = mysql_query($cmd) or die($diemsg);
 list($rtext1)=@mysql_fetch_row($rrow);
 $cmd = "insert into $dtable (list,ref,typ,data1,data2) values('*','remove','remove','$rtext1','Unsubscribe');";
 mysql_query($cmd) or die($diemsg);


$cmd = "select listnum from $ltable where 1";
$lrows = mysql_query($cmd) or die($diemsg);
while(list($listnum)=mysql_fetch_row($lrows)){

 // get the remove text from the list and put it into the codes..
 $cmd = "select remtext from $ltable where listnum='$listnum'";
 $lrow = mysql_query($cmd) or die($diemsg);
 list($remtext)=mysql_fetch_row($lrow);
 $remtext = addslashes($remtext); // is this needed?

 $cmd = "insert into $dtable (list,ref,typ,data1,data2) values('$listnum','remove','remove','$remtext','Unsubscribe');";
 mysql_query($cmd) or die($diemsg);

}

// list fields
$cmd = "ALTER TABLE $ltable
 ADD field1 VARCHAR (25) not null ,
 ADD field2 VARCHAR (25) not null ,
 ADD field3 VARCHAR (25) not null ,
 ADD field4 VARCHAR (25) not null ,
 ADD field5 VARCHAR (25) not null ,
 ADD field6 VARCHAR (25) not null ,
 ADD field7 VARCHAR (25) not null ,
 ADD field8 VARCHAR (25) not null ,
 ADD field9 VARCHAR (25) not null ,
 ADD field10 VARCHAR (25) not null";
mysql_query($cmd);

// add a custom list field to list 1 for fun
$cmd = "update $ltable set field1 = 'Custom Field 1' where listnum = '1'";
mysql_query($cmd);

// user fields
$cmd = "ALTER TABLE $utable ADD user1 VARCHAR (50) not null AFTER email , ADD user2 VARCHAR (50) not null AFTER user1 , ADD user3 VARCHAR (50) not null AFTER user2 , ADD user4 VARCHAR (50) not null AFTER user3 , ADD user5 VARCHAR (50) not null AFTER user4 , ADD user6 VARCHAR (50) not null AFTER user5 , ADD user7 VARCHAR (50) not null AFTER user6 , ADD user8 VARCHAR (50) not null AFTER user7 , ADD user9 VARCHAR (50) not null AFTER user8 , ADD user10 VARCHAR (50) not null AFTER user9";
mysql_query($cmd);

mysql_query("update $ctable set urows = '10' where 1");

// delete remtext from database...
$cmd="ALTER TABLE $ltable DROP remtext";
mysql_query($cmd);

// add mtpdelay for checking delay..
$cmd = "ALTER TABLE $ctable ADD mtpdelay TINYINT NOT NULL AFTER mtppass;";
mysql_query($cmd);
// set default value to 19 minutes
$cmd ="update $ctable set mtpdelay = '19' where 1";
mysql_query($cmd);

$cmd = "ALTER TABLE $ctable ADD daypw TINYTEXT NOT NULL AFTER admpw";
mysql_query($cmd);

// set default value of daypw to adminpw.
$cmd = "select admpw from $ctable where 1";
$crow = mysql_query($cmd);
list($admpw)=mysql_fetch_row($crow);
$cmd = "update $ctable set daypw = '$admpw' where 1";
mysql_query($cmd);

// new maintenance options..
// how long to keep sent messages, tracked hits, dailymail logs
// how long to keep unconfirmed emails

 $cmd = "ALTER TABLE $ctable ADD keepsent CHAR(3) NOT NULL, ADD keephits CHAR(3) NOT NULL, ADD keepucnf CHAR(3) NOT NULL, ADD keeplogs CHAR(3) NOT NULL;";
 mysql_query($cmd);

 $cmd = "update $ctable set keepsent = '30', keephits = '30', keepucnf = '10', keeplogs = '10' where 1";
 mysql_query($cmd);

 // update version number
 $cmd = "update $ctable set ver = '1.51' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.51';
}

if($cver=='1.51'){
 // fix default delay setting. 1 2 3
 mysql_query("update $ctable set delay1 = '500', delay2 = '2', delay3 = '200'");

 // new user tracking fields..
 $cmd = "ALTER TABLE $utable ADD ipaddr VARCHAR (15) not null after dateadd, ADD refurl VARCHAR(75) not null after ipaddr";
 mysql_query($cmd);

 // set current users to unknown
 $cmd = "update $utable set ipaddr = '<i>unknown</i>', refurl = '<i>unknown</i>' where 1;";
 mysql_query($cmd);

 // new mtdelay for reconnecting smtp
 $cmd = "alter table $ctable add mtdelay smallint(5) not null after mtpass";
 mysql_query($cmd);

 // set default
 mysql_query("update $ctable set mtdelay = '2500' where 1");

 // update version number
 $cmd = "update $ctable set ver = '1.52' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.52';

} // end -> 1.52

if($cver=='1.52'){
 // mistake, wrong type for mtdelay :(
 $cmd = "ALTER TABLE $ctable CHANGE mtdelay mtdelay SMALLINT(5) DEFAULT '0' NOT NULL";
 mysql_query($cmd);
 mysql_query("update $ctable set mtdelay = '2500' where 1");
 // update version number
 $cmd = "update $ctable set ver = '1.53' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.53';
} // end -> 1.53


// v1.6 - June 2002

if($cver=='1.53'){

 // new scheduler table

$cmd = "CREATE TABLE $qtable (
  id smallint(5) unsigned NOT NULL auto_increment,
  type char(1) NOT NULL default '0',
  date date NOT NULL default '0000-00-00',
  subject tinytext NOT NULL,
  message mediumtext NOT NULL,
  htmessage mediumtext NOT NULL,
  fattach tinytext NOT NULL,
  list tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id)
);";
@mysql_query($cmd);

 // new admin notifs
 $crow = mysql_query("select admmail from $ctable where 1");
 list($admmail)=mysql_fetch_row($crow);

 $cmd = "alter table $ctable add notifs varchar(9) not null;";
 mysql_query($cmd);

 mysql_query("update $ctable set notifs = '0;0;0;0;0' where 1");

 $cmd = "alter table $ltable add notifs varchar(9) not null, add nmails tinytext not null";
 mysql_query($cmd);

 mysql_query("update $ltable set notifs = '0;0;0;0;0', nmails = '$admmail' where 1");

 // new sendq db
$cmd = "CREATE TABLE $otable (
  id mediumint(9) unsigned NOT NULL auto_increment,
  mtype char(1) NOT NULL default '',
  uid mediumint(9) unsigned NOT NULL default '0',
  mid varchar(10) NOT NULL default '0',
  xtra varchar(10) NOT NULL default '',
  PRIMARY KEY  (id)
)";
 mysql_query($cmd) or die("error creating sendq table: $otable");

 // update version number
 $cmd = "update $ctable set ver = '1.6' where 1";
 mysql_query($cmd) or die("error updating version number to 1.6: $ctable");
 $cver = '1.6';

} // end -> 1.6


if($cver=='1.6'){

 // fix type
 mysql_query("alter table $htable change uid uid mediumint(9) unsigned default '0' not null");

 // fix list field type for all tables req'd
 mysql_query("alter table $utable change list list tinyint(4) unsigned default '0' not null");
 mysql_query("alter table $dtable change list list tinyint(4) unsigned default '0' not null");
 mysql_query("alter table $vtable change list list tinyint(4) unsigned default '0' not null");
 mysql_query("alter table $qtable change list list tinyint(4) unsigned default '0' not null");

 // fix users per page, increased max to 255
 mysql_query("alter table $ctable change urows urows tinyint(4) unsigned default '0' not null");

 // change otable to have no index
 mysql_query("alter table $otable change id id varchar(32) not null");
 mysql_query("alter table $otable drop primary key");

 // add indexes
 mysql_query("alter table $dtable add index(list)");
 mysql_query("alter table $qtable add index(date)");
 mysql_query("alter table $vtable add index(list)");
 mysql_query("alter table $ftable add index(list)");
 mysql_query("alter table $ktable change refcode refcode varchar(255) not null");
 mysql_query("alter table $ktable add index(refcode)");
 mysql_query("alter table $stable add index(kind)");
 mysql_query("alter table $utable change email email varchar(255) not null");
 mysql_query("alter table $utable add index(list,uid,bounces,email)");
 mysql_query("alter table $ltable add index(listnum)");

 // add batch and batch type to sendq
 mysql_query("alter table $otable add bat varchar(6) not null after id");
 mysql_query("alter table $otable add battype char(1) not null after bat");

 // update version number
 $cmd = "update $ctable set ver = '1.61' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.61';

} // end -> 1.61

if($cver=='1.61'){

 // fix for table mixup.. (affected new installs)
 @mysql_query("alter table $qtable drop bat");
 @mysql_query("alter table $qtable drop battype");
 @mysql_query("alter table $otable add bat varchar(6) not null after id");
 @mysql_query("alter table $otable add battype char(1) not null after bat");

 // update version number
 $cmd = "update $ctable set ver = '1.62' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.62';

} // end -> 1.62

if($cver=='1.62'){

 @mysql_query("alter table $otable drop primary key");
 @mysql_query("alter table $ltable add index(listnum)");

 // add new instid to config
 mysql_query("alter table $ctable add instid varchar(32) not null first");

 $xid = calc32();
 mysql_query("update $ctable set instid = '$xid' where 1");

 // update version number
 $cmd = "update $ctable set ver = '1.65' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.65';

} // end -> 1.65

if($cver=='1.65'){
 // 1.7 upgrade
 // fix indices to make sure everything's nice & speedy
 @mysql_query("alter table $utable drop index list");
 @mysql_query("alter table $utable drop index uid");
 @mysql_query("alter table $utable drop index email");
 @mysql_query("alter table $utable drop index bounces");
 @mysql_query("alter table $utable add index (list)");
 @mysql_query("alter table $utable add index (uid)");
 @mysql_query("alter table $utable add index (email)");
 @mysql_query("alter table $utable add index (bounces)");

 // remove those annoying <i>'s from ip & refurl.. fix neg. delay #'s
 $urows = mysql_query("select id,ipaddr,refurl,cdel from $utable where 1");
 if(mysql_num_rows($urows)<>0){
  while(list($id,$ipaddr,$refurl,$cdel)=mysql_fetch_row($urows)){
   if(strpos('i>',' '.$refurl)>0){
    $ipaddr = str_replace('<i>','',$ipaddr);
    $ipaddr = str_replace('</i>','',$ipaddr);
    $refurl = str_replace('<i>','',$refurl);
    $refurl = str_replace('</i>','',$refurl);
    if($cdel<'0') $cdel = '0';
    mysql_query("update $utable set ipaddr = '$ipaddr', refurl = '$refurl', cdel = '$cdel' where id = '$id'");
   }
  }
 }

 // fix for bounce.cgi retries to 1..
 list($errchk,$nbounce)=mysql_fetch_row(mysql_query("select errchk,nbounce from $ctable where 1"));
 if($errchk=='2') mysql_query("update $ctable set nbounce = '1' where 1");

 // update lists welcome msg active
 mysql_query("update $ltable set welcact = '1' where 1");

 $cmd = "update $ctable set ver = '1.7' where 1";
 mysql_query($cmd) or die($diemsg);
 $cver = '1.7';
} // end -> 1.7

if($cver=='1.7'){
 // 1.75 upgrade

 // fix negative delay numbers (again).
 mysql_query("update $utable set cdel = '0' where cdel < '0'");

 // modify config & user table for new bounce process
 mysql_query("alter table $ctable change nbounce nbounce varchar(6) not null") or die('Cannot ALTER table!!');
 mysql_query("alter table $utable drop index bounces");
 mysql_query("alter table $utable change bounces bounces tinytext not null");

 // update config & user table to be compatible with new settings
 // get original value of bounces and assume 30 day delay
 list($obs)=mysql_fetch_row(mysql_query("select nbounce from $ctable where 1"));
 mysql_query("update $ctable set nbounce = '$obs:30'");
 mysql_query("update $utable set bounces = '0' where 1");

 // remove queued messages, no longer used..
 mysql_query("delete from $stable where kind = 'q'");

 mysql_query("update $ctable set ver = '1.75' where 1") or die($diemsg);
 $cver = '1.75';
} // end -> 1.75

if($cver=='1.75'){
 // 1.76 upgrade

 // new list options
 mysql_query("alter table $ltable add listopts varchar(3) not null");
 mysql_query("update $ltable set listopts = '0;0' where 1");
 mysql_query("alter table $utable add index (cnf)");

 // fix users bounce dates to be -100..
 $urows = mysql_query("select id,bounces from $utable where bounces > '0'");
 while(list($id,$bounces)=mysql_fetch_row($urows)){
  $bounces = explode(';',$bounces);
  $newstr = $bounces[0];
  while(list($key,$val)=each($bounces)){
   if($key>0){
    $y = substr($val,0,4);
    $m = substr($val,4,2);
    $d = substr($val,6,2);
    $newstr .= ';'. date("Ymd", mktime(0,0,0,$m,$d-100,$y));
   }
  }
  mysql_query("update $utable set bounces = '$newstr' where id = '$id'");
 }

 mysql_query("update $ctable set ver = '1.76' where 1") or die($diemsg);
 $cver = '1.76';
} // end -> 1.76

if($cver=='1.76'){
 // 1.77 upgrade (files only)
 mysql_query("update $ctable set ver = '1.77' where 1") or die($diemsg);
 $cver = '1.77';
} // end -> 1.77

if($cver=='1.77'){
 // 1.8 upgrade
 // 65535 custom field data length
 mysql_query("ALTER TABLE $utable CHANGE user1 user1 TEXT NOT NULL") or die('0'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user2 user2 TEXT NOT NULL") or die('1'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user3 user3 TEXT NOT NULL") or die('2'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user4 user4 TEXT NOT NULL") or die('3'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user5 user5 TEXT NOT NULL") or die('4'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user6 user6 TEXT NOT NULL") or die('5'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user7 user7 TEXT NOT NULL") or die('6'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user8 user8 TEXT NOT NULL") or die('7'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user9 user9 TEXT NOT NULL") or die('8'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE user10 user10 TEXT NOT NULL") or die('9'.mysql_error());

 // 65535 lists
 mysql_query("ALTER TABLE $ftable CHANGE list list SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('10'.mysql_error());
 mysql_query("ALTER TABLE $ltable CHANGE listnum listnum SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('11'.mysql_error());
 mysql_query("ALTER TABLE $dtable CHANGE list list SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('12'.mysql_error());
 mysql_query("ALTER TABLE $vtable CHANGE list list SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('13'.mysql_error());
 mysql_query("ALTER TABLE $qtable CHANGE list list SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('14'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE list list SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('15'.mysql_error());

 // 65535 sequence / delays
 mysql_query("ALTER TABLE $ftable CHANGE seq seq SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('16'.mysql_error());
 mysql_query("ALTER TABLE $ftable CHANGE del del SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('17'.mysql_error());

 mysql_query("ALTER TABLE $utable CHANGE cseq cseq SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('18'.mysql_error());
 mysql_query("ALTER TABLE $utable CHANGE cdel cdel SMALLINT UNSIGNED DEFAULT '0' NOT NULL") or die('19'.mysql_error());

 // change unused urows to sortopts
 mysql_query("ALTER TABLE $ctable CHANGE urows sortopts TINYTEXT NOT NULL") or die('20'.mysql_error());

 // rule based sending *** add to editconf
 mysql_query("CREATE TABLE $setable (
  id smallint(5) unsigned NOT NULL auto_increment,
  name tinytext NOT NULL,
  descr tinytext NOT NULL,
  fdupe char(1) NOT NULL default '',
  PRIMARY KEY  (id))") or die('21'.mysql_error());

 mysql_query("CREATE TABLE $sedtable (
  id mediumint(8) unsigned NOT NULL auto_increment,
  rsub char(1) NOT NULL default '',
  rsid smallint(5) unsigned NOT NULL default '0',
  cond char(1) NOT NULL default '',
  stat char(1) NOT NULL default '',
  list smallint(6) unsigned NOT NULL default '0',
  qtype char(1) NOT NULL default '',
  field char(1) NOT NULL default '',
  q tinytext NOT NULL,
  qdate tinytext NOT NULL,
  PRIMARY KEY  (id),
  KEY rsid (rsid))") or die('22'.mysql_error());

 // change scheduled message list field to accomodate selects
 mysql_query("ALTER TABLE $qtable CHANGE list list TINYTEXT NOT NULL") or die('23'.mysql_error());

 // add some indexes
 mysql_query("ALTER TABLE $otable ADD PRIMARY KEY (id)"); // or die('24'.mysql_error());
 mysql_query("ALTER TABLE $dtable ADD INDEX (typ)") or die('25'.mysql_error());
 mysql_query("ALTER TABLE $ktable ADD INDEX (refcode)") or die('26'.mysql_error());

 // drop unneeded fields
 mysql_query("ALTER TABLE $ltable DROP errmail") or die('27'.mysql_error());
 mysql_query("ALTER TABLE $ctable DROP mthelo") or die('28'.mysql_error());
 mysql_query("ALTER TABLE $ctable DROP mthelochk") or die('29'.mysql_error());

 // maint addition to config table
 mysql_query("ALTER TABLE $ctable ADD maint TINYTEXT NOT NULL") or die('30'.mysql_error());
 mysql_query("update $ctable set maint = '50;5000;10' where 1") or die('31'.mysql_error());

 // chtmlid and errorid to list table
 mysql_query("ALTER TABLE $ltable ADD chtmlid SMALLINT UNSIGNED NOT NULL") or die('32'.mysql_error());
 mysql_query("ALTER TABLE $ltable ADD errorid SMALLINT UNSIGNED NOT NULL") or die('33'.mysql_error());

 // new message code fields for options
 mysql_query("ALTER TABLE $dtable ADD data3 TEXT NOT NULL, ADD data4 TEXT NOT NULL, ADD data5 TEXT NOT NULL;") or die('34'.mysql_error());

// create tables
mysql_query("CREATE TABLE $chtable (
id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
name TINYTEXT NOT NULL ,
PRIMARY KEY ( id )
);") or die('35'.mysql_error());

mysql_query("CREATE TABLE $chdtable (
id SMALLINT UNSIGNED NOT NULL ,
typ VARCHAR( 12 ) NOT NULL ,
html MEDIUMTEXT NOT NULL ,
url TEXT NOT NULL ,
INDEX ( id ) ,
INDEX ( typ )
);") or die('36'.mysql_error());

// create tables
mysql_query("CREATE TABLE $etable (
id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
name TINYTEXT NOT NULL ,
PRIMARY KEY ( id )
);") or die('37'.mysql_error());

mysql_query("CREATE TABLE $edtable (
id SMALLINT UNSIGNED NOT NULL ,
typ VARCHAR( 12 ) NOT NULL ,
data TEXT NOT NULL ,
INDEX ( id ) ,
INDEX ( typ )
);") or die('38'.mysql_error());


// insert DEFAULT (id=1) custom HTML and error messages first, for use with new lists, etc.

mysql_query("insert into $chtable values('','Default HTML')") or die('39'.mysql_error());
$iid = mysql_insert_id();
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'subscribe', '<html>\r\n<head>\r\n<title>Welcome to the list!</title>\r\n</head>\r\n<body bgcolor=white>\r\nThanks for signing up, !fname.<br>\r\nYour email address <u>!email</u> will be kept safe!<br><br>You will receive an introductory email in a moment.<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die('40'.mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'suberror', '<html>\r\n<head>\r\n<title>Signup Error</title>\r\n</head>\r\n<body bgcolor=white>\r\nThere were errors with your subscription to our mailing list.<br><br>\r\nThe error messages are as follows:<br>\r\n!data<br>\r\nPlease press the back button on your browser and re-enter your information.<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die('41'.mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'remove', '<html>\r\n<head>\r\n<title>Remove Confirmation Page</title>\r\n<style type=text/css rel=stylesheet> <!--\r\n.confirm_button: { border: 1px solid #000000; font: 10pt arial; }\r\n--> </style>\r\n\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour name was successfully retrieved from our database.  We apologize if our mailings were an inconvenience to you and hope you are able to find what you were looking for in the near future.  Please confirm that you would like your email address (!email) removed from our mailing list by clicking the button below.<br>\r\n!button','');";
mysql_query($cmd) or die('42'.mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'remerror', '<html>\r\n<head>\r\n<title>Email Address Not Found!</title>\r\n</head>\r\n<body bgcolor=white>\r\nYour email address (!email) was not found in our database!  This error should never happen, please contact the administrator of this web site for a manual removal.<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die('43'.mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid','remsuccess', '<html>\r\n<head>\r\n<title>Removal Success Page</title>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour email address (!email) was successfully removed from our database.  Be assured you will never receive any more email from this list server.  We\'re sorry you decided to leave and wish you the best in the future!<br><br>\r\nManagement<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die('44'.mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'preconfirm', '<html>\r\n<head>\r\n<title>Confirmation Page</title>\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nBefore we add you to our database, we must verify your email address.  Please check your email for the confirmation message and click the link provided to finalize your subscription.  <br>\r\n\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die('45'.mysql_error());

mysql_query("insert into $etable values('','Default Errors')") or die('46'.mysql_error());
$iid = mysql_insert_id();
mysql_query("insert into $edtable values('$iid','fname_req','You didn\'t enter your first name.');") or die('47'.mysql_error());
mysql_query("insert into $edtable values('$iid','lname_req','You didn\'t enter your last name.');") or die('48'.mysql_error());
mysql_query("insert into $edtable values('$iid','email_req','You didn\'t enter an email address.');") or die('49'.mysql_error());
mysql_query("insert into $edtable values('$iid','email_bad','The email address you entered is in an incorrect format.');") or die('50'.mysql_error());
mysql_query("insert into $edtable values('$iid','email_dupe','That email address is already subscribed to this list!');") or die('51'.mysql_error());
mysql_query("insert into $edtable values('$iid','rem_nf','User not found in database. You should not receive any more email');") or die('52'.mysql_error());
mysql_query("insert into $edtable values('$iid','l_closed','You may not subscribe to or unsubscribe from a closed list.  Please contact your administrator.');") or die('53'.mysql_error());

// migrate data from lm_custom
$lrows = mysql_query("select listnum from $ltable where 1 order by listnum") or die('54'.mysql_error());
while(list($ln)=mysql_fetch_row($lrows)){
 mysql_query("insert into $chtable values('','List $ln HTML');") or die("1".mysql_error()) or die('55-'.$ln.mysql_error());
 $iid = mysql_insert_id();
 mysql_query("update $ltable set chtmlid = '$iid' where listnum = '$ln'") or die('56-'.$ln.mysql_error());
 $types = array('subscribe','remove','suberror','remerror','remsuccess','preconfirm');
 $remd = '';
 while(list($k,$v)=each($types)){
  $rows = mysql_query("select data,data1,data2,data3,data4,data5 from $vtable where list = '$ln' and ref = '$v';") or die('57-'.$k.mysql_error());
  list($d,$d1,$d2,$d3,$d4,$d5)=mysql_fetch_row($rows);
  mysql_query("insert into $chdtable values('$iid','$v','".addslashes($d)."','');") or die('58-'.$k.mysql_error());
  // get error messages for this list
  if($v=='remove'){ $remd = $d1; }
  if($v=='suberror'){
   // create name
   $name = "List $ln Error Messages";
   mysql_query("insert into $etable values('','$name');") or die('59-'.$k.mysql_error());
   $eid = mysql_insert_id();
   mysql_query("update $ltable set errorid = '$eid' where listnum = '$ln'") or die('60-'.$k.mysql_error());
   // insert all data
   mysql_query("insert into $edtable values('$eid','fname_req','".addslashes($d1)."');") or die('61-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','lname_req','".addslashes($d2)."');") or die('62-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','email_req','".addslashes($d3)."');") or die('63-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','email_bad','".addslashes($d4)."');") or die('64-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','email_dupe','".addslashes($d5)."');") or die('65-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','rem_nf','User not found in database. You should not receive any more email');") or die('66-'.$k.mysql_error());
   mysql_query("insert into $edtable values('$eid','l_closed','You may not subscribe to or unsubscribe from a closed list.  Please contact your administrator.');") or die('67-'.$k.mysql_error());
  }
 }
}

// INSERT default !button and !confirm message codes
mysql_query("insert into $dtable (list,ref,typ,data1) values('0','button','remcnfht','Remove Me');") or die('68'.mysql_error());

mysql_query("insert into $dtable (list,ref,typ,data1,data2) values('0','confirm','confirm','Please click the link below to confirm your subscription:','Confirm');") or die('69'.mysql_error());

// remove lm_custom table;
mysql_query("DROP table $vtable;") or die('69'.mysql_error());

mysql_query("update $ctable set ver = '1.8' where 1") or die($diemsg);

 $cver = '1.8';
} // end -> 1.8

if($cver=='1.8' || $cver=='1.82' || $cver=='1.83'){
 // 1.83 bug fix
 $rows = mysql_query("select uid,count(*) as cnt from lm_users where 1 group by uid order by cnt desc");
 if(@mysql_num_rows($rows)>0){
  while(list($u,$c)=mysql_fetch_row($rows)){
   if($c>'1'){
    $rows2 = mysql_query("select id,uid,email from $utable where uid = '$u'");
	$x = 0;
    while(list($i,$ui,$e)=mysql_fetch_row($rows2)){
	 $x++;
	 if($x>1){
      $uniq = '';
      while(!$uniq){
       $uniq_str = unique_id(7);
       if(@mysql_num_rows(mysql_query("select id from $utable where uid = '$uniq_str'"))==0) $uniq=1;
      }
      $cmd = "update $utable set uid = '$uniq_str' where id = '$i';";
      mysql_query($cmd);
	 }
    }
   }
  }
 }
 mysql_query("update $utable set dateadd = '2005-08-13' where dateadd = '0000-00-00';");
 // sent message viewing
 mysql_query("ALTER TABLE $ttable CHANGE datesent datesent DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';") or die('1.84-0 '.mysql_error());
 mysql_query("ALTER TABLE $ttable ADD numsent MEDIUMINT UNSIGNED DEFAULT '0' NOT NULL ;") or die('1.84-1 '.mysql_error());

 mysql_query("update $ctable set ver = '1.84' where 1") or die($diemsg);
 $cver = '1.84';
} // end -> 1.84

// v1.85 update
if($cver=='1.84'){
 mysql_query("ALTER TABLE $ctable ADD textonly TEXT NOT NULL;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $ftable ADD cron TINYTEXT NOT NULL;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("update $ctable set textonly = 'aol.com' where 1;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 // eliminate duplicate seq #s
 $txt = '';
 $lrows = mysql_query("select listnum from $ltable where 1 order by listnum");
 while(list($list)=mysql_fetch_row($lrows)){
  $frows = mysql_query("select count(*),seq from lm_follow where list = '1' group by seq");
   if(@mysql_num_rows($frows)>0){
   while(list($c,$s)=mysql_fetch_row($frows)){
    if($c>'1'){
     $c--;
     echo "list $list: duplicate seq #$s<br>.. deleting ";
     $frows2 = mysql_query("select id,subject,content,htcontent,fattach from $ftable where list = '$list' and seq = '$s' order by id desc limit $c;");
     while(list($id,$su,$co,$ht,$fa)=mysql_fetch_row($frows2)){
      echo "id=$id ";
      mysql_query("delete from $ftable where id = '$id';");
      $txt .= "List $list message removed.. was duplicate Seq # $s\n\nSubject:\n$su\n\nText Message:\n$co"."\n\nHTML Message:\n$ht"."\n\nFile Attachments:\n$fa\n\n---------\n\n";
     }
     echo "<br>";
    }
   }
  }
 }
 // email removed fups to admin
 list($admmail)=mysql_fetch_row(mysql_query("select admmail from $ctable where 1"));
 $admmail = explode(';',$admmail);
 $admmail = $admmail[0];
 if($txt) mail($admmail,'ListMail Upgrade',"The following followups were deleted from your installation due to being of a duplicate seq # on the same list. Please manually re-insert this data into ListMail if desired.\n\n".$txt);

 // new last dailymail field
 mysql_query("ALTER TABLE $ctable ADD daylast datetime NOT NULL AFTER daypw;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("update $ctable set daylast = '2001-01-01 01:01:01' where 1;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 // new queue protection table
 $cmd = "CREATE TABLE $ptable (
  id mediumint unsigned NOT NULL auto_increment,
  batid varchar(6) NOT NULL,
  formid varchar(14) NOT NULL,
  started datetime NOT NULL,
  lastact datetime NOT NULL,
  completed char(1) NOT NULL,
  PRIMARY KEY (id),
  KEY batid (batid)
)";
 mysql_query($cmd);
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("ALTER TABLE $ttable ADD sentto TINYTEXT NOT NULL;");
 $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("update $ctable set ver = '1.85' where 1") or die($diemsg);
 $cver = '1.85';
} // end -> 1.85

// v1.86 update
if($cver=='1.85'){
 // saved fup table
 mysql_query("CREATE TABLE $fstable (id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT, name TINYTEXT NOT NULL, PRIMARY KEY (id));"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // saved fup setid
 mysql_query("ALTER TABLE $ftable ADD sset SMALLINT UNSIGNED NOT NULL"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // scheduler entry types to allow saved set application
 mysql_query("update $qtable set type = 'm' where 1;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // additional list options
 mysql_query("ALTER TABLE $ltable ADD addopts VARCHAR(100) NOT NULL;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $ltable CHANGE listopts listopts VARCHAR(20) NOT NULL;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // possible msg code processing speedup
 mysql_query("ALTER TABLE $dtable ADD INDEX (ref);"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // daym report in queue protect table so sent when mailing completes even if daym crashes
 mysql_query("ALTER TABLE $ptable ADD qtype CHAR(1) NOT NULL AFTER batid;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $ptable ADD report TEXT NOT NULL AFTER lastact;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 // rem old import files from /tmp
 shell_exec("rm /tmp/????????.tmp");
 shell_exec("del /tmp/????????.tmp");
 shell_exec("del C:\\Temp\\????????.tmp");
 // new date/dateadd msg code options
 mysql_query("update $dtable set data2 = '0', data3 = '0', data4 = '0' where typ = 'date' or typ = 'dateadd';");
 // new dailymail option to turn on and off the new v1.85 23h55m restriction
 mysql_query("ALTER TABLE $ctable ADD dayopt CHAR(1) NOT NULL;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("update $ctable set dayopt = '1' where 1;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("update $ctable set ver = '1.86' where 1") or die($diemsg);
 $cver = '1.86';
}

// v1.87 update
if($cver=='1.86'){

 mysql_query("ALTER TABLE $ltable CHANGE field1 field1 TEXT NOT NULL,
CHANGE field2 field2 TEXT NOT NULL,
CHANGE field3 field3 TEXT NOT NULL,
CHANGE field4 field4 TEXT NOT NULL,
CHANGE field5 field5 TEXT NOT NULL,
CHANGE field6 field6 TEXT NOT NULL,
CHANGE field7 field7 TEXT NOT NULL,
CHANGE field8 field8 TEXT NOT NULL,
CHANGE field9 field9 TEXT NOT NULL,
CHANGE field10 field10 TEXT NOT NULL;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $ctable CHANGE mtchk mtchk CHAR(3) NOT NULL");  $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("ALTER TABLE $utable DROP INDEX email"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $utable CHANGE email email VARCHAR(255) NOT NULL"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';
 mysql_query("ALTER TABLE $utable ADD INDEX (email)"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("ALTER TABLE $ltable CHANGE listopts listopts VARCHAR(30) NOT NULL;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("ALTER TABLE $ltable ADD remsubj TINYTEXT NOT NULL AFTER cnfact,
ADD remmsg MEDIUMTEXT NOT NULL AFTER remsubj,
ADD remht MEDIUMTEXT NOT NULL AFTER remmsg,
ADD remf TINYTEXT NOT NULL AFTER remht,
ADD remact CHAR(1) NOT NULL AFTER remf,
ADD remopts CHAR(10) NOT NULL AFTER remact;");  $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("UPDATE $ltable SET remopts = '1;7' WHERE 1;"); $x = mysql_error(); if($x) echo $x.'<br>'; $x = '';

 mysql_query("update $ctable set ver = '1.87' where 1") or die($diemsg);
 $cver = '1.87';
}

// optimize database tables
optimizeall();

echo "<b>Done!</b><br>"; flush();
?>
