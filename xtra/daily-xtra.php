<?php
/*
-------------------------------------------------------------------------------
  ListMail daily-xtra.php

  This file will run AFTER dailymail runs, just before notifying the admin with the dailymail report.
  To enable it, modify the file and copy it to your MAIN ListMail dir. It will not be overwritten by updates!

  Place custom commands below, ListMail database link is already in effect as $link, if you need to access another database, use $link2.

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


// END
?>
