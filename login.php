<?php
/* ListMail (c) 2002 - 2006 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include('./config.php');
include('./admin.php');

ini_set("max_execution_time","0");

$loggedin='';
$islogged='';

$islogged = islogged();

if(!$islogged){ $dologin=1; } else { $loggedin=1; }

if(!$installed){
 $dologin=1;
 $loggedin=0;
 $logout='';
 setcookie('LMadm1','',time()-2592000);
 setcookie('LMadm2','',time()-2592000);

}

if($loggedin && !$logout){
 if($r){ header('Location: ./'.urldecode($r)); exit(); }
 else header('Location: ./editlists.php');
 exit;
}

if($logout){
 if (!$loggedin){
  adminheader('Error',"Can't logout",'login');
  echo "You are not logged in. <a style=\"color: #000000\" href=./login.php>Login</a>?";
 } else {
  setcookie('LMadm1','',time()-2592000);
  setcookie('LMadm2','',time()-2592000);
  adminheader('ListMail Logout','Logged out','logout');
  echo "<span class=table_inside_normal>Admin logged out.  </span><a class=table_inside_normal href=./login.php>Re-login</a>?";
 }
}

if($chkpw){
 $adminpw='';
 $crow = mysql_query("select instid,admpw from $ctable where 1");
 if(@mysql_num_rows($crow)>0){
  list($instid,$adminpw)=mysql_fetch_row($crow);
 }
 if($adminpw=='') { $adminpw = 'install'; $xpw = 1; } else $xpw = '';

// echo "pw=$pw, adminpw=$adminpw<br>";
 if($pw == $adminpw){
  if($xpw) $adminpw='';
  setcookie('LMadm1',md5($instid));
  setcookie('LMadm2',md5($adminpw));

  if(!$installed){
   adminheader('ListMail Auto-Install','Auto-Install','autoinst');
   echo "<span class=table_inside_normal>Initiating auto-installer, please wait.</span>";
  } else {
   if($r){ header('Location: ./'.urldecode($r)); exit(); }
   adminheader('ListMail Login','Logged in','jumplists');
   echo "<span class=table_inside_normal>Admin logged in, proceed.</span>";
  }
 } else {
  $narr = getnotifs($list,'cfg');
  if($narr[1]=='1'){
   $crow = mysql_query("select ver,errchk,admmail,erraddr from $ctable where 1");
   list($ver,$errchk,$admmail,$errmail)=mysql_fetch_row($crow);
   $xmails = explode(';',$admmail);
   $admmail = $xmails[0];

   $ipaddr = getip();
   $iphost = gethostbyaddr($ipaddr);

   if($errchk == '2' || $errchk == '1') $errmail = $errmail; else $errmail = $admmail;
   $sendn = "ListMail Notification";
   $sende = "$errmail";
   $subj = 'Bad Password Entered!';
   $msg = "This is a warning because someone (you?) entered a bad password in the ListMail login page.  There is a delay of 7 seconds when you enter a wrong password but it won't completely stop would-be \"brute force\" hackers.  Your safest bet is to make sure your password can not be found in a dictionary file and is at least 8 characters in length.\n\nHere is some information about the potential intruder:\n\nIP Address: $ipaddr ($iphost)\n\nThe password they tried was \"$pw\".\n";
     $htmsg = '';
   $crlf = "\n";
   $header ="From: \"$sendn\" <$sende>$crlf";
   $header .= "Return-Path: $errmail$crlf";
   $header .= "MIME-Version: 1.0$crlf";
   $header .="X-Mailer: ListMailPRO v$ver$crlf";
   if($phpmailf) mail($admmail,$subj,$msg,$header,"-f$errmail"); else mail($admmail,$subj,$msg,$header);
 }
  sleep(7);
  adminheader('Bad password','Incorrect Password','login');
  echo '<span class=table_inside_normal>Incorrect password.  This attempt has been logged.</span>';
 }
}
if($dologin && !$logout && !$chkpw){
 adminheader('ListMail Login','Admin Login','login');
 echo "<!-- outer -->\n";
 // check upgrade
 if($installed){
  $link = mysql_connect($sqlhost,$sqluser,$sqlpass);
  mysql_selectdb($sqldb);
  // compare current_version with database
  $cmd = "select ver from $ctable where 1 limit 1";
  $cfgrow = mysql_query($cmd);
  list($cver)= @mysql_fetch_row($cfgrow);
  if(!$cver) $cver = '1.0';
  if($cver<>$current_version){
   include('./upgrade.php');
  }
 }
 echo "
<img src=1.gif height=10><br>
<table class=outer_table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg><span class=table_head_text style=\"padding: 0px 5px 0px 4px\">Enter Admin Password</span></td>
 </tr>
 <tr>
  <td>
<table width=100% border=0 cellspacing=0 cellpadding=5>
 <tr>
  <td class=bg2>
   <form name=getpw method=post>
   <center>
   <input type=hidden name=r value=\"".htmlspecialchars($r)."\">
   <input name=chkpw type=hidden value=1>
   <input name=pw id=pw type=password size=10 value=\"$inpw\" class=xbox style=\"width: 84px\"><br>
   <img src=1.gif height=3><br>
   <input class=button name=sb type=submit value=Enter onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 56px\">
  </td></form>
 </tr>
</table>
  </td>
 </tr>
</table>
<!-- end outer -->
<script language=javascript><!--
document.getElementById('pw').focus();
--></script>";
if($demo == 'yes'){ echo "<span class=table_inside_normal><br>This is a demo, enter \"demo\" as the password.</span>"; }
if(!$installed){ echo "<span class=table_inside_normal><br>To install, enter \"install\" as the password.</span>"; }
echo "</body>
</html>";
}


?>
