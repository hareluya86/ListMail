<?php
/*
-------------------------------------------------------------------------------
  ListMail remove-xtra.php
  This file will run after a successful removal by rem.php.  it is currently not possible to run during manual removal and the variables are limited...
 
  Modify the file and copy it to your MAIN ListMail dir.  It will not be overwritten by updates!

  Place custom commands below, ListMail database link is already in effect as $link, if you need to access another database, use $link2.

  Available variables are taken directly from the signup insert command run just before this script is included.

  $uid - User's 6 character unique string.
  $list - List subscribed to.
  $fname - First name, if available.
  $email - User's email address.

  NOTE: It is recommended you perform your own list checking to make sure your custom commands run correctly.
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
echo "remove-xtra.php executing!<br><br>";

// END
?>
