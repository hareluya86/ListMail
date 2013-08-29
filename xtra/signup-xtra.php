<?php
/*
-------------------------------------------------------------------------------
  ListMail signup-xtra.php
  This file will run after a successful confirmation in confirm.php and after a successful signup in signup.php
  Modify the file and copy it to your MAIN ListMail dir.  It will not be overwritten by updates!

  Place custom commands below, ListMail database link is already in effect as $link, if you need to access another database, use $link2.

  Available variables are taken directly from the signup insert command run just before this script is included.

  $uid - User's 6 character unique string.
  $list - List subscribed to.
  $fname - First name, if available.
  $lname - Last name, if available.
  $email - User's email address.
  $user1 $user2 $user3 $user4 $user5 $user6 $user7 $user8 $user9 $user10 - Custom field information.
  $html - User's Text/HTML or text-only setting, will be '1' or '0'.
  $userseq - User sequence # from first followup, if exists. Will be '1' if not.
  $userdel - User delay # from first followup, if exists.
  $confirmed - This will always be '1', as we run this script only after a successful signup/confirm.
  $today - The date, Y-m-d ie. 2002-01-30
  $ipaddr - User's IP address, if available.
  $refurl - User's Referring URL, if available.

  NOTE: This script will run after every non-manual signup to every list.  When manually adding, the script can be run optionally.  It is recommended you perform your own list checking to make sure your custom commands run correctly.
  ie:  if($list=='1'){  commands(); exit; }

-------------------------------------------------------------------------------
*/

/*
 // MYSQL CONNECT EXAMPLE
 $sql2u = 'USERNAME';
 $sql2p = 'PASSWORD';
 $sql2d = 'DATABASE';

 $link2 = mysql_connect('localhost',$sql2u,$sql2p) or die('could not connect');
 mysql_select_db($sql2d,$link2);

 // query example
 // listmail DB
 // mysql_query("select id from lm_users where 1",$link);
 // xtra DB
 // mysql_query("select id from yourtable where 1",$link2);
*/

// CUSTOM SCRIPT HERE
echo "signup-xtra.php executing!<br><br>";

// END
?>
