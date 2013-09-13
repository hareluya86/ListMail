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

$sqldebug = '';

ini_set("max_execution_time","0");

// phpinfo();

$islogged = islogged();
if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){


if(!$list) $list = firstlist();

if(!$sendit && !$resume && !$delete_queue){
 include('./editmsg.php');
 editmessage('domail');
 exit;
}

// solo email
if ($sendit || $resume){
 if(is_numeric($tolist)) $list = $tolist;
 if($resume) $restxt = 'resume'; else $restxt = 'domail';
 adminheader("Sending Email..","Sending Email",$restxt);
 flush();

 echo "<span class=table_inside_normal>";
 
 if(!$resume){
  echo "<span class=table_inside_normal>";
  // check if this formid was recently submitted (within 72 hrs)
  $prows = mysql_query("select id from $ptable where formid = '".addslashes($doformid)."' and lastact > '".date("Y-m-d H:i:s",time()-259200)."';");
  if(@mysql_num_rows($prows)>0){
   echo "<br>This message has already been sent.  Did you refresh the page?<br><br><input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><br>";
   exit;
  }
  
  echo "Please wait while email is queued to send... ";
  flush();

  $batid = qstart(1);
 
  $sendq = 0;
  $groupby = '';
  if(is_numeric($tolist)){ //This is the part where we need to change to retrieve email addresses from the client's database
   $cmd = "select email,id from $utable where list = '$tolist' and cnf = '1'";
  } else {
   // selection
   $tors = substr($tolist,2,strlen($tolist)-2);
   include('./selfunc.php');
   $cmd = make_rquery('send',$tors);
  }
  $sqlerror = '';
  // Check if remote
    $remotecmd = "select remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where id=$tolist";
    $result = mysql_query($remotecmd) or die(mysql_error());
    list($remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = mysql_fetch_row($result);
    if($remote){
        try {
            $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
            $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
            $dbh_query = $dbh->query($cmd);echo $cmd.'<br>';//debug
        } catch (PDOException $e) {
            die('domail-' . $e->getMessage());
        }
        $sendtousers = $dbh_query; //Store for later use
        $urows = $sendtousers->fetchAll();
    }else{
        $sendtousers = mysql_query($cmd) or $sqlerror = mysql_error();
        $urows = array();
        while($r = mysql_fetch_assoc($sendtousers)) {
            $urows[$r['config_name']] = $r['config_value'];
        }
    }
    $numsent = count($urows);echo $numsent.'<br>';//debug
    
  $cmd = "insert into $ttable values('','".addslashes($txtsubj)."','".addslashes($txtcont)."','".addslashes($txthtcont)."','".addslashes($txtfatt)."','".date("Y-m-d H:i:s")."','1','$numsent','".addslashes($tolist)."')";
  @mysql_query($cmd);echo $cmd.'<br>';//debug
  if($sqldebug) echo "CMD=<b>$cmd</b><br>";
  $msgid = mysql_insert_id();

  if($sqlerror){
   echo "<br><br><font color=red>Your message could not be sent because there is an error with your SQL query!</font><br><br><font face=courier>$sqlerror</font><br><br>Please fix your Custom Query and send your message again.<br><br>Use the button below to go back to the sending page where you can save your message, if necessary.<br><br><input class=button type=button value=\"Go Back!\" onclick=\"back();\" style=\"width: 100px\"><br>";
   flush();
   exit;
   
  }  
  // insert into sendq
  //while(list($em,$uid) = @mysql_fetch_row($urows)){
  while(list($em,$uid) = $urows){//doesn't cater for local lists
      echo 'uid='.$uid;
   $xid = calc32();
   $cmd = "insert into $otable(id,bat,battype,mtype,uid,lid,mid) values('$xid','$batid','1','1','$uid','$list','$msgid');";
   echo $cmd.'<br>';//debug
   // echo "CMD=$cmd<br>";
   if($sqldebug) echo "CMD=<b>$cmd</b><br>";
   @mysql_query($cmd) or die(mysql_error());
   $sendq++;
   // echo "UID: $uid EMAIL: $em<br>";
  }
  echo "Done!<br><br>";
  flush();
  qcheckin($batid,1);
 }
 // end !resume

 if(!$resume){
     echo 'batch id = '.$batid;//debug
     echo 'msg id = '.$msgid;//debug
  $sentok = domail('','solo',$msgid,$batid);
 } elseif($resume){
  // check if this mailing was completed
  $prows = mysql_query("select id from $ptable where batid = '".addslashes($batid)."' and completed = '1';");
  if(@mysql_num_rows($prows)>0){
   echo "<br>This mailing has been completed.<br><br><input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><br>";
   exit();
  }
  // check that this mailing is not being queued 
  $prows = mysql_query("select id from $ptable where batid = '".addslashes($batid)."' and completed = 'q';");
  if(@mysql_num_rows($prows)>0){
   echo "<br>This mailing appears to be in the process of queueing.<br>You can try to <input class=button type=button onclick=\"hopto('./domail.php?resume=1&batid=$batid&list=$list')\" name=bs value=\"Resume\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"> it.  If you believe the queue process has failed you should <input class=button type=button onclick=\"hopto('./domail.php?list=$list&delete_queue=1&batid=$batid')\" name=cs value=\"Cancel\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"> this mailing and resend it.<br><br><input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><br>";
   exit();
  }
  // check that this mailing has not been active in the last minute
  $prows = mysql_query("select lastact from $ptable where batid = '".addslashes($batid)."' and completed = '0' and lastact >= '".date("Y-m-d H:i:s",time()-60)."' ;");
  if(@mysql_num_rows($prows)>0){
   list($la)=mysql_fetch_row($prows);
   $la = dt2epoch($la);
   $sc = time()-$la;
   if($sc>22) $sct = " <span style=\"color: darkred; font-weight: bold;\">and seems to be failing</span>";
   elseif($sc<=22) $sct = " <span style=\"color: darkgreen; font-weight: bold;\">normally</span>";
   else $sct = '';
   echo "<br>This mailing appears to be in the process of sending$sct. It has responded within 1 minute ($sc seconds ago).<br><br>You may continue to try to <input class=button type=button onclick=\"hopto('./domail.php?resume=1&batid=$batid&list=$list')\" name=bs value=\"Resume\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"> this mailing but won't be able to until it fails for 1 minute.<br><br>It is not recommended that you cancel this mailing as messages could be sent to only part of your list.<br><br><input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><br>";
   exit();
  }
  // get number to send by counting queue table
  $rows = mysql_query("select id from $qtable where batid = '$batid';");
  $ntosend=@mysql_num_rows($rows);
  qcheckin($batid);
  $sentok = domail('','solo','resume',$batid);
 }
 if($sentok){
  qfinish($batid);
  // echo "<br><span class=table_inside_normal>Finished sending!</span><br><br>";

/*  echo "<input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\">\n"; */
  echo "
</center>
</body>
</html>\n";
 } else echo "<br>Not sent ok.<br>";
 // optimizeall();
} //sendit

if($delete_queue){
 adminheader('ListMail','Queue Deleted','jumplists',$list);
 mysql_query("delete from $otable where bat = '$batid'");
 echo "<span class=table_inside_normal>Queue entry deleted.</span></body></html>";
 mysql_query("optimize table $otable");

}

} // auth
// EOF
?>
