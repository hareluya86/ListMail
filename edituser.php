<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

ini_set("max_execution_time","0");
$islogged = islogged();

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if($isadmin == 'true'){

// debug messages
// $bugs = 1;

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_select_db($sqldb);

if($edit){
 if($save){
  //  if(!$add) $logtxt .= "Saved user. ";
  if($htxt=='html') $htadd = '1'; if($htxt=='text') $htadd = '0'; if(!$htxt) $htadd = '1';

  if(strtoupper($email)<>strtoupper($origmail) || $preverr || $add){
   // echo "preverr=$preverr add=$add<br>";
   // check dupe
   $urow = @mysql_query("select id,cnf from $utable where email like '$email' and list = '$ulist'");
   if(@mysql_num_rows($urow)>0) {
    list($xid,$xcnf)=mysql_fetch_row($urow);
    if($xcnf=='2' || $xcnf=='0'){
     if($xcnf=='2') $logtxt .= "Replaced removed user. ";
     if($xcnf=='0') $logtxt .= "Replaced unconfirmed user. ";
     mysql_query("delete from $utable where id = '$xid'");
     $failsave = '';
    } else {
     $logtxt .= "Duplicate email, not saved. ";
     $failsave = 1;
    }
   }

   if($email=='') { $logtxt .= 'Blank email, not saved. '; $failsave = 1; }
   if(!valid_email($email) && !$failsave && $email<>''){ $logtxt .= 'Bad email format, not saved. '; $failsave = 1; }
  }

  if(!$failsave && !$add){
   $email = addslashes($email);
   $fname = addslashes($fname);
   $lname = addslashes($lname);
   $user1 = addslashes($user1);
   $user2 = addslashes($user2);
   $user3 = addslashes($user3);
   $user4 = addslashes($user4);
   $user5 = addslashes($user5);
   $user6 = addslashes($user6);
   $user7 = addslashes($user7);
   $user8 = addslashes($user8);
   $user9 = addslashes($user9);
   $user10 = addslashes($user10);

   if($list<>'all') $list = $ulist;

   $cmd = "update $utable set list = '$ulist', cnf = '$status', email = '$email',fname = '$fname',lname = '$lname',user1 = '$user1',user2 = '$user2',user3 = '$user3',user4 = '$user4',user5 = '$user5',user6 = '$user6',user7 = '$user7',user8 = '$user8',user9 = '$user9',user10 = '$user10', htmail = '$htadd', cseq = '$setseq', cdel = '$setdel' where id = '$id' ";
   @mysql_query($cmd);
   $logtxt .= "User saved. ";

   $email = stripslashes($email);
   $fname = stripslashes($fname);
   $lname = stripslashes($lname);
   $user1 = stripslashes($user1);
   $user2 = stripslashes($user2);
   $user3 = stripslashes($user3);
   $user4 = stripslashes($user4);
   $user5 = stripslashes($user5);
   $user6 = stripslashes($user6);
   $user7 = stripslashes($user7);
   $user8 = stripslashes($user8);
   $user9 = stripslashes($user9);
   $user10 = stripslashes($user10);
   $txtsfor = stripslashes($txtsfor);
  }

  if(!$failsave && $add){
   $email = addslashes($email);
   $fname = addslashes($fname);
   $lname = addslashes($lname);
   $user1 = addslashes($user1);
   $user2 = addslashes($user2);
   $user3 = addslashes($user3);
   $user4 = addslashes($user4);
   $user5 = addslashes($user5);
   $user6 = addslashes($user6);
   $user7 = addslashes($user7);
   $user8 = addslashes($user8);
   $user9 = addslashes($user9);
   $user10 = addslashes($user10);

   $uniq = '';
   while(!$uniq){
    $uniq_str = unique_id(7);
    if(@mysql_num_rows(mysql_query("select id from $utable where uid = '$uniq_str'"))==0) $uniq=1; //searching for existing unique id generated
   }
   if($welcmail || (!$welcmail && !$cnfmail)) $xcnf = '1';  if($cnfmail) $xcnf = '0';
   $today = date("Y-m-d H:i:s");
   $cmd = "insert into $utable values ('null','$uniq_str','$ulist','$fname','$lname','$email','$user1','$user2','$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10','$setseq','$setdel','$xcnf','$today','Unknown','Manually Added','$htadd','0')";
   @mysql_query($cmd);
   $id = mysql_insert_id();
   $logtxt .= "Added user to list $ulist. ";

   // remove user from other lists
   $listopts = getlistopts($ulist);
   if($listopts[2]=='1') remlists($email,$ulist,2);
   if($listopts[3]=='1') addlists($email,$ulist,3);

   $add = '';
   $added = 1;
   if($welcmail || $cnfmail){
    sendwelcome($id,$ulist);
    if($welcmail) $logtxt .= " Welcome message sent."; else $logtxt .= " Confirmation message sent.";
   }
   if(!$cnfmail && $xtrmail && file_exists('./signup-xtra.php')){
    $list = $ulist;
    $uid = $uniq_str;
    $userseq = $setseq;
    $userdel = $setdel;
    $confirmed = $xcnf;
    $html = $htadd;
    $ipaddr = 'Unknown';
    $refurl = 'Manually Added';
    include('./signup-xtra.php');
    $logtxt .= " signup-xtra.php executed!";
    mysql_select_db($sqldb,$link);
   } elseif($cnfmail && file_exists('./signup-confirm-xtra.php')){
    $list = $ulist;
    $uid = $uniq_str;
    $userseq = $setseq;
    $userdel = $setdel;
    $confirmed = $xcnf;
    $html = $htadd;
    $ipaddr = 'Unknown';
    $refurl = 'Manually Added';
    include('./signup-confirm-xtra.php');
    $logtxt .= " signup-confirm-xtra.php executed!";
    mysql_select_db($sqldb,$link);
   }

   $narr = getnotifs($ulist);
   if(!$cnfmail) if($narr[2]=='1') sendnotif('manual_add',$id);
   //   echo "LIST1=$list<br>";
   if($list<>'all') $list = $ulist;
   //   echo "LIST2=$list<br>";

   $email = stripslashes($email);
   $fname = stripslashes($fname);
   $lname = stripslashes($lname);
   $user1 = stripslashes($user1);
   $user2 = stripslashes($user2);
   $user3 = stripslashes($user3);
   $user4 = stripslashes($user4);
   $user5 = stripslashes($user5);
   $user6 = stripslashes($user6);
   $user7 = stripslashes($user7);
   $user8 = stripslashes($user8);
   $user9 = stripslashes($user9);
   $user10 = stripslashes($user10);
   $txtsfor = stripslashes($txtsfor);
  } // add
 } // save

 // main edit page
 if($add) adminheader('LM: Add New User','Add New User',''); else adminheader('LM: User Details','User Details','');
 echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,'users');

 echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>\n";

 if(!$add){
  // get user variables
  $cmd = "select id,list,uid,cnf,dateadd,fname,lname,email,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,htmail,ipaddr,refurl,cseq,cdel from $utable where id = '$id'";
  $urow = mysql_query($cmd,$link);
  list($id,$ulist,$uid,$ucnf,$dateadd,$fname,$lname,$email,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$htmail,$ipaddr,$refurl,$cseq,$cdel) = mysql_fetch_row($urow);

  if($ucnf=='0') $unc_check = ' selected';
  if($ucnf=='1') $act_check = ' selected';
  if($ucnf=='2') $rem_check = ' selected';
  if($ucnf=='3') $bnc_check = ' selected';
  $cseq_chk[$cseq] = ' selected';

  if($htmail=='1') $htchk1 = ' checked'; else $htchk2 = ' checked';
 } else {
  // user add page
  $dateadd = date("Y-m-d H:i:s");
  $refurl = "Manually Added";
  $ipaddr = "Unknown";
  if($list=='all' && !$ulist){
   $lrow = mysql_query("select listnum from $ltable where 1 order by listnum limit 1",$link);
   list($ulist)=mysql_fetch_row($lrow);
  }
  if(!$ulist) $ulist = $list;
  $htchk1 = ' checked';
 }
 $user = array('',$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10);

 echo " <table width=100% border=0 class=outer_table bordercolor=#dddddd cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg_nopad colspan=2><span class=table_head_text><form name=user_frm method=post action=./edituser.php>Edit User Details</span></td>
 </tr>
</table>
<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td nowrap><span class=table_inside_small>Date Added: <b>$dateadd</b></td><td width=10><img src=1.gif width=10></td><td nowrap><span class=table_inside_small>IP: <b>$ipaddr</b></span></td></tr></table>
<span class=table_inside_small>Ref URL: <b>$refurl</b></span><br>
<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td>
<span class=table_inside_small>Member of List</span><br>
<select class=xbox name=ulist style=\"width: 250px\" onchange=\"";
if($edit && !$add){ echo "user_frm.save.value='1'; "; }
if($edit && $add) { echo "user_frm.save.value=''; "; }
echo " user_frm.submit();\">";

 $cmd = "select id,listnum,title from $ltable where 1 order by listnum";
 $result = mysql_query($cmd,$link);
 while(list($lid2,$lnu2,$lti2) = mysql_fetch_row($result)){
  $outp = "$lnu2: $lti2";
  if($lnu2==$ulist && $ulist <> 'all') $l_x = " selected"; else $l_x = "";
  echo "<option value=\"$lnu2\"$l_x>$outp";
 }

echo "</select><br>
</td><td width=10><img src=1.gif width=10><br></td><td><span class=table_inside_small>Status:</span><br><select class=xbox name=status><option value=1$act_check>Active<option value=0$unc_check>Unconfirmed<option value=2$rem_check>Removed<option value=3$bnc_check>Bounced</select><br>
</td></tr></table>
<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0><tr>
 <td><span class=table_inside_small>Email Address:</span><br><input class=xbox type=text name=email style=\"width: 200px\" value=\"".htmlspecialchars($email)."\"><br></td>
 <td width=10><img src=1.gif height=10><br></td>
 <td><span class=table_inside_small>First Name</span><br><input class=xbox type=text name=fname value=\"".htmlspecialchars($fname)."\"><br></td>
 <td width=10><img src=1.gif height=10><br></td>
 <td><span class=table_inside_small>Last Name</span><br><input class=xbox type=text name=lname value=\"".htmlspecialchars($lname)."\"><br></td></tr></table>\n";

 // get custom fields for this list
 $fields = getcfields($ulist,'users');

 $z=2;
 $outp = "<table border=0 cellspacing=0 cellpadding=0>";
 for($i=1;$i<=10;$i++){
  if($fields[$i]) {
   $isfields=1;
   if(($z%2)==0){ $outp .= "<tr><td>"; } else $outp .= "</td><td width=10><img src=1.gif width=10></td><td>";
   $outp .= "<img src=1.gif height=10><br><span class=table_inside_small>$fields[$i] <small>[$i]</small></span><br><input type=text class=xbox name=user$i value=\"".htmlspecialchars($user[$i])."\" size=50 maxlength=50><br>";
   if(($z%2)<>0) { $outp .= "</td></tr>"; }
   $z++;
  } else {
   echo "<input type=hidden name=user$i value=\"".htmlspecialchars($user[$i])."\">";
   $fields[$key] = '';
  }
 }
 if(($z%2)<>0) $outp .= "</td><td width=10><img src=1.gif width=10></td></tr>";
 $outp .= "</table>";

 if($isfields) echo $outp; else $outp='';

echo "<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Send HTML + Text or just Text Format?</span><br></td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small><input type=radio name=htxt value=html$htchk1>HTML and Text</span></td><td width=7><img src=1.gif width=7></td><td><span class=table_inside_small><input type=radio name=htxt value=text$htchk2>Text only</span><br></td></tr></table>\n";

 // followups
 $frows = @mysql_query("select seq,del,subject from $ftable where list = '$ulist' order by seq",$link);
 if(@mysql_num_rows($frows)>0){
  echo "<img src=1.gif height=7><br><table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Next Followup</span><br><select name=setseq class=xbox style=\"width: 320px\">";
  $badfup = 1;
  while(list($seq,$del,$subj)=mysql_fetch_row($frows)){
   if($add) if(!$cdel && $cdel <> "0") $cdel=$del;
   if(!$add) if($cseq==$seq || $cseq==($seq+1)) $badfup = '';
   echo "<option value=\"$seq\"$cseq_chk[$seq]>$seq: ".htmlspecialchars($subj);
   $xseq = $seq;
  }
  if($cseq == ($xseq+1)) $eos_chk = ' selected';
  echo "<option value=\"".($xseq+1)."\"$eos_chk>".($xseq+1).": End of sequence";
  if(!$add && $badfup) echo "<option value=\"$cseq\" selected>$cseq: Non-existant";

  echo "</select></td><td width=10><img src=1.gif width=10><td><span class=table_inside_small>Delay</span><br><input name=setdel type=text class=xbox size=2 value=\"$cdel\"><br></td></tr></table>";
 } else {
  // no followups
  if(!$setseq) $setseq = $cseq;
  if(!$setseq) $setseq = '1';
  if(!$setdel) $setdel = $cdel;
  if(!$setdel) $setdel = '0';

  echo "<input type=hidden name=setseq value=$setseq>\n<input type=hidden name=setdel value=$setdel>\n";
 }
 echo "<img src=1.gif height=10><br>";

if($add){
 if($failsave && $welcmail) { $xchk = ' checked'; $logtxt .= " Welcome message not sent.";  } else if(!$welcmail) $xchk = '';

 if($failsave && $cnfmail) { $x2chk = ' checked'; $logtxt .= " Confirmation message not sent."; } else if(!$cnfmail) $x2chk = '';

 if(file_exists('./signup-xtra.php')){
  if($failsave && $xtrmail) { $x3chk = ' checked'; $logtxt .= " signup-xtra.php script not executed."; } else if(!$xtrmail) $x3chk = '';
 }

 if(!$failsave){
  // default checkboxes
  if($nevercheck){
   $xchk = ''; $x2chk = ''; $x3chk = '';
  } else {
   // failed saving
   $lrow = mysql_query("select welcact,cnfact from $ltable where listnum = '$ulist'",$link);
   list($welcact,$cnfact)=mysql_fetch_row($lrow);
   if($cnfact=='1') $x2chk = ' checked'; else {
    $x2chk = '';
    if($welcact=='1') $xchk = ' checked'; else $xchk = '';
   }
   if(file_exists('./signup-xtra.php')) $x3chk = ' checked'; else $x3chk = '';
  }
 }

 echo "
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <input type=checkbox class=chkbox name=welcmail onclick=\"if(this.checked==1) user_frm.cnfmail.checked='';\" value=1$xchk>&nbsp;<span class=table_inside_small>Send the <i>Welcome</i> email to this user?</span><br>
   <img src=1.gif height=3><br>
   <input type=checkbox class=chkbox name=cnfmail onclick=\"if(this.checked==1) user_frm.welcmail.checked='';\" value=1$x2chk>&nbsp;<span class=table_inside_small>Send the <i>Confirm + Welcome</i> emails to this user?<br>\n";

 if(file_exists('./signup-xtra.php')){
  echo "   <img src=1.gif height=3><br>
   <input type=checkbox class=chkbox name=xtrmail value=1$x3chk>&nbsp;<span class=table_inside_small>Run the signup-xtra.php script after adding? (Welcome only - runs automatically <u>after</u> confirmation)<br>\n";
 }

 echo "  </td>
 </tr>
</table>
<img src=1.gif height=10><br>";
}

echo "<table border=0 cellspacing=0 cellpadding=0><tr>
 <td>
  <input type=hidden name=pgnum value=$pgnum>
  <input type=hidden name=edit value=1>
  <input type=hidden name=save value=1>
  <input type=hidden name=id value=$id>
  <input type=hidden name=pgnum value=\"$pgnum\">
  <input type=hidden name=dosearch value=\"$dosearch\">
  <input type=hidden name=sfor value=\"$sfor\">
  <input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\">
  <input type=hidden name=sby value=\"$sby\">
  <input type=hidden name=sord value=\"$sord\">
  <input type=hidden name=cnf value=\"$cnf\">
  <input type=hidden name=xrows value=$xrows>
      <input type=hidden name=list value=$list>";
if($add){
 echo "   <input type=hidden name=add value=1>";
}
echo "  <input class=button type=submit value=\"Save User\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">";
// if($failsave) echo "<input type=hidden name=preverr value=1>";

// echo "add: $add<br>save: $save<br> ulist: $ulist<br>";

echo "<input type=hidden name=origmail value=\"".htmlspecialchars($email)."\"></td></form>
 <td width=3><img src=1.gif width=3></td>
 <td>\n";

 // back button for adding or editing
 if($add)
  echo "<input class=button type=button value=\"Cancel\" onclick=\"hopto('./edituser.php?list=$list&pgnum=$pgnum&dosearch=$dosearch&sfor=$sfor&txtsfor=".urlencode($txtsfor)."&sby=$sby&sord=$sord&cnf=$cnf');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\">";
 else {
  if($added || $save) echo "<input class=button type=button value=\"Back to Users\" onclick=\"hopto('./edituser.php?list=$list&pgnum=$pgnum&dosearch=$dosearch&sfor=$sfor&txtsfor=".urlencode($txtsfor)."&sby=$sby&sord=$sord&cnf=$cnf');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"></td><td width=3><img src=1.gif width=3></td><td><input class=button type=button value=\"Add a User\" onclick=\"hopto('./edituser.php?list=$list&pgnum=$pgnum&add=1&edit=1&dosearch=$dosearch&sfor=$sfor&txtsfor=".urlencode($txtsfor)."&sby=$sby&sord=$sord&cnf=$cnf')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">";
  else
   echo "<input class=button type=button value=\"Back to Users\" onclick=\"javascript:history.back()\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"></td><td width=3><img src=1.gif width=3></td><td><input class=button type=button value=\"Add a User\" onclick=\"hopto('./edituser.php?list=$list&pgnum=$pgnum&add=1&edit=1&dosearch=$dosearch&sfor=$sfor&txtsfor=".urlencode($txtsfor)."&sby=$sby&sord=$sord&cnf=$cnf')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">";
 }
 echo " </td></tr></table>
<img src=1.gif height=10><br>";
 if($logtxt) echo "<span class=status_text>$logtxt</span>";
 echo "
<script language=javascript>user_frm.email.focus();</script>
<script language=javascript>window.status='".addslashes($logtxt)."';</script>
</body>
</html>";
 exit;
} // END USER EDIT & ADD

if($import){
 if($list=='all') $list = '1';
 if(!$gettypes && !$doinsert)  $mainpage = 1;
 $errors = 0;

 if($gettypes){
  // Import From File
  if($fromfile){
   $udata = array();

   if($upload){
    // get uploaded file
    global $_FILES;
    if ($_FILES['userfile']['tmp_name']){
     $filename = $_FILES['userfile']['tmp_name'];
     $realname = $_FILES['userfile']['name'];

     // make a new random filename
     $rfn = unique_id(9).'.tmp';
     if($windows){
      $slash = '\\\\';
      if($_SERVER['TEMP']) $tmp = $_SERVER['TEMP']; else $tmp = 'C:\Temp';
     } else {
      $slash = '/';
      if($_SERVER['upload_tmp_dir']) $tmp = $_SERVER['upload_tmp_dir']; else $tmp = '/tmp';
     }
     $targdir = getcwd() . '/attach';
     if(!move_uploaded_file($_FILES['userfile']['tmp_name'],$targdir.$slash.$rfn)) echo "Could not move file.  Files are temporarily stored in your ListMail<b>/attach</b> folder which must have the correct permissions (CHMOD 777)<br>";
     $showname = "$realname";
     $filename = $targdir.$slash.$rfn;
    } else $nofile = 1;
   } else {
    // from listmail dir
    $showname = $filename;
    $filename = './' . $filename;
   }

   // check if file exists
   if(!@fopen($filename,'r') || ($filename == './' && !$rfn)) {
    $isafile=''; $errors = 1; $nofile = 1;
   } else $isafile=1;

   if($isafile){
    // read first line of file into array
    $infile = fopen($filename,'r');
    $buffer = fgets($infile, 4096);
    // strip the line feed off the end
    $buffer = str_replace("\n",'',$buffer);
    $buffer = str_replace("\r",'',$buffer);
    // get the fields
    $udata = csv_parse($buffer);
    $reading = "input file <b>$showname</b>";
   } // file exists
   @fclose($infile);
  } // from file

  // Import From MySQL DB
  if($fromdb){
   // get fields (udata)
   $link2 = mysql_connect($dbhost,$dbuser,$dbpass,1) or die(mysql_error());
   if(!$link2 || !$dbhost || !$dbuser || !$dbpass){ $errors = 1; $badconn = 1; }

   if(!$errors){
    $etest = mysql_select_db($dbfile,$link2) or die(mysql_error());
    if(!$etest){ $errors = 1; $baddb = 1; }
   }
   if(!$errors){
    if(!$dbfile || !$dbtable){
     $errors = 1; $badftable = 1;
    }
   }

   if(!$errors){
    $sqfields = mysql_list_fields($dbfile, $dbtable, $link2) or die(mysql_error());
    $nsqf = mysql_num_fields($sqfields) or die(mysql_error());
    for ($y=0;$y<$nsqf;$y++) $udata[$y] = mysql_field_name($sqfields, $y) or die(mysql_error());
    $impfrom = "Database";
    $reading = "database <b>$dbfile</b>, table <b>$dbtable</b>";
   }
  }

  if(!$errors){
   // process udata array and display ftypes
   $nfields = count($udata);
   $fields = getcfields($list);

   // start output
    adminheader('LM: Import Users','Importing Users','');
    echo "<table width=760 border=0 cellspacing=0 cellpadding=0><tr><td valign=top width=1>\n";
    listmenu($list,'import');
    echo "  </td><td width=10><img src=1.gif width=10></td><td valign=top>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Importing Users</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('import')."');\">Help</a><br></td></tr></table></td></tr>
 <tr>
  <td class=table_inside_bg><div style=\"margin-left: 5px; padding: 3px 3px 5px 3px\">\n";

  if($fromfile){
   $fieldhead = 'Example Data';
   $matchtxt = 'data from the first line in your CSV file to the ListMail fields of your choice.';
  }
  if($fromdb){
   $fieldhead = 'Field Name';
   $matchtxt = 'fields from your source database to the ListMail fields of your choice.';
  }

   echo "<span class=table_inside_normal>Reading $reading<br><img src=1.gif height=10><br>First, match up the $matchtxt  You must be sure to make one field the \"Email Address\", the rest are optional.<br><img src=1.gif height=10><br><table border=0 cellspacing=0 cellpadding=0><tr><td><form name=ftypes method=post><input type=hidden name=filename value=\"".htmlspecialchars($filename)."\"><input type=hidden name=list value=$list><input type=hidden name=import value=1><input type=hidden name=doinsert value=1><span class=table_inside_small>Field #</span><img src=1.gif width=10></td><td><span class=table_inside_small>$fieldhead</span><img src=1.gif width=10></td><td width=5><img src=1.gif width=5><br></td><td><span class=table_inside_small>Field Type</span><br></td></tr>";

   // show rows
   for($i=0;$i<$nfields;$i++){
    $x = $i+1;
    echo "<tr style=\"padding: 2px 0px 0px 0px\"><td><span class=table_inside_small>$x.</span></td><td><span class=table_inside_small><b>".$udata[$i]."</b></span></td><td width=5><img src=1.gif width=5><br></td><td>
<select name=\"ftype[]\" class=xbox>
<option value=none>-- Choose --
<option value=email>Email Address
<option value=fname>First Name
<option value=lname>Last Name
<option value=ipaddr>IP Address
<option value=refurl>Referring URL
<option value=list>List Number
<option value=dadd>Date Added YYYY-MM-DD
<option value=seq>Sequence
<option value=del>Delay
<option value=field1>$fields[1]<option value=field2>$fields[2]<option value=field3>$fields[3]<option value=field4>$fields[4]<option value=field5>$fields[5]<option value=field6>$fields[6]<option value=field7>$fields[7]<option value=field8>$fields[8]<option value=field9>$fields[9]<option value=field10>$fields[10]</select></tr>\n";
   }
   echo "</table><img src=1.gif height=10><br><input name=deleteall type=checkbox class=chkbox value=1><img src=1.gif width=5><span class=table_inside_small>DELETE ALL USERS on current list before importing (confirmation required)</span><br><input name=noreplace type=checkbox class=chkbox value=1><img src=1.gif width=5><span class=table_inside_small>Do not overwrite unconfirmed, previously removed or bounced users (if kept in database)</span><br><input name=yesreplace type=checkbox class=chkbox value=1><img src=1.gif width=5><span class=table_inside_small>Overwrite all users whether active, unconfirmed, removed or bounced</span><br><input name=bademail type=checkbox class=chkbox value=1><img src=1.gif width=5><span class=table_inside_small>Send an email to the administrator listing bad email addresses</span><br>
<input name=customrefu type=checkbox class=chkbox value=1><img src=1.gif width=5><span class=table_inside_small>Set \"Referring URL\" to <input class=xbox type=text size=15 name=crvalue value=\"\"> (Default: \"Imported\")</span><br>\n";

 // followups
 $frows = @mysql_query("select seq,del,subject from $ftable where list = '$list' order by seq",$link);
 if(@mysql_num_rows($frows)>0){
  echo "<img src=1.gif height=10><br><table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Next Followup</span><br><select name=setseq class=xbox style=\"width: 200px\">";
  while(list($seq,$del,$subj)=mysql_fetch_row($frows)){
   if(!$del1 && $del1 <> "0") $del1=$del;
   echo "<option value=\"$seq\">$seq: ".htmlspecialchars($subj);
   $xseq = $seq;
  }
  echo "<option value=\"".($xseq+1)."\">".($xseq+1).": End of sequence";
  echo "</select><td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small>Delay<br><input name=setdel type=text class=xbox size=2 value=\"$del1\"><br></td></tr></table>";
 } else {
  echo "<input type=hidden name=setseq value=1>\n<input type=hidden name=setdel value=0>\n";
 }


 // default checkboxes
 if($nevercheck){
  $xchk = ''; $x2chk = ''; $x3chk = '';
 } else {
  // failed saving
  $lrow = mysql_query("select welcact,cnfact from $ltable where listnum = '$list'",$link);
  list($welcact,$cnfact)=mysql_fetch_row($lrow);
  if($cnfact=='1') $x2chk = ' checked'; else {
   $x2chk = '';
   if($welcact=='1') $xchk = ' checked'; else $xchk = '';
  }
  if(file_exists('./signup-xtra.php')) $x3chk = ' checked'; else $x3chk = '';
 }

 // import welcome/confirm/xtra
 echo "
<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <input type=checkbox class=chkbox name=welcmail onclick=\"if(this.checked==1) ftypes.cnfmail.checked='';\" value=1$xchk>&nbsp;<span class=table_inside_small>Send the <i>Welcome</i> email to imported users?</span><br>
   <img src=1.gif height=3><br>
   <input type=checkbox class=chkbox name=cnfmail onclick=\"if(this.checked==1) ftypes.welcmail.checked='';\" value=1$x2chk>&nbsp;<span class=table_inside_small>Send the <i>Confirm + Welcome</i> emails to imported users?<br>\n";

 if(file_exists('./signup-xtra.php')){
  echo "   <img src=1.gif height=3><br>
   <input type=checkbox class=chkbox name=xtrmail value=1$x3chk>&nbsp;<span class=table_inside_small>Run the signup-xtra.php script on each user?<br>\n";
 }

 echo "  </td>
 </tr>
</table>";

echo " </td></tr></table></div><img src=1.gif height=5><br><input class=button type=submit value=\"Continue Import\" onclick=\"if(ftypes.deleteall.checked=='1'){ if(!confirm('Are you sure you want to DELETE ALL USERS ON THIS LIST before importing? (This cannot be undone)')){ return false; } }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><img src=1.gif height=3><input type=button class=button onclick=\"hopto('./edituser.php?list=$list&import=1')\" value=\"Back to Import\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 110px\"><input type=hidden name=fromfile value=\"$fromfile\"><input type=hidden name=fromdb value=\"$fromdb\"><input type=hidden name=dbhost value=\"$dbhost\"><input type=hidden name=dbuser value=\"$dbuser\"><input type=hidden name=dbpass value=\"$dbpass\"><input type=hidden name=dbfile value=\"$dbfile\"><input type=hidden name=dbtable value=\"$dbtable\">
</form>
</td></tr></table>";
   $mainpage = '';
  } else {
   // errors
   $mainpage = 1;
  }
 } // end gettypes

 if($doinsert){
  // check if deleting all from list
  if($deleteall){
   mysql_query("delete from $utable where list = '$list'",$link);
   optimizeall();
  }

  if($fromfile){
   $infile = fopen ($filename, "r") or die('Could not open temporary file '.$filename."<br>Did you Refresh the page? Please <a href=./edituser.php?list=$list&import=1>upload and import a new file</a>.");
   $impfrom = "File";
  }
  if($fromdb){
   $impfrom = "MySQL";
  }

  // show page
  // print_r($ftype);
  // inserting progress page
  adminheader('LM: Import Users','Importing Users','');

  echo "<table width=760 border=0 cellspacing=0 cellpadding=0><tr><td valign=top width=1>\n";
  if($welcmail || $cnfmail) $cpag = 'import2'; else $cpag = 'import';
  listmenu($list,$cpag);
  echo "  </td><td width=10><img src=1.gif width=10></td><td valign=top>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg><span class=table_head_text>Importing from $impfrom</span></td>
    </tr><tr>
     <td class=table_inside_bg><div style=\"padding: 5px 5px 5px 5px\">
      <form name=imp>
      <table border=0 cellspacing=0 cellpadding=0>
       <tr>
        <td><span class=table_inside_normal>Imported <input type=text class=xbox id=nfield name=nfield value=0 size=5> users</span><br></td>
        <td width=10><img src=1.gif width=10></td>
        <td><input class=xbox id=dfield name=dfield value=0 size=5> <span class=table_inside_normal>duplicates</span></td>
        <td width=10><img src=1.gif width=10></td>
        <td><input type=text class=xbox id=bfield name=bfield value=0 size=5> <span class=table_inside_normal>bad addresses</span></td>
       </tr>
      </table>
      </form>
     </td></tr></table>
<img src=1.gif height=5><br><input type=button class=button value=\"Back to Users\" onclick=\"hopto('./edituser.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"><br>
  <img src=1.gif height=6><br>
  <span class=table_inside_small><center>Note: This script can take awhile and the counters may not update properly.  Be patient for best results.</center></span><br>
  </td></tr></table>";
  flush();

  // set up - pre loop
  srand((double)microtime()*1000000);
  $cfs = count($ftype);
  $dupes = 0;
  $x = 0;
  $badmails = 0;
  $today = date("Y-m-d");
  $randmax = getrandmax();
  if($randmax>'9999999') $randmax = '9999999';

  // set fields
  for($i=0;$i<$cfs;$i++){
   switch($ftype[$i]){
    case 'fname' : $fn = $i; break;
    case 'lname' : $ln = $i; break;
    case 'email' : $em = $i; break;
    case 'ipaddr' : $ip = $i; break;
    case 'refurl' : $ru = $i; break;
    case 'list' : $ls = $i; break;
    case 'dadd' : $da = $i; break;
    case 'seq' : $sq = $i; break;
    case 'del' : $de = $i; break;
    case 'field1' : $u0 = $i; break;
    case 'field2' : $u1 = $i; break;
    case 'field3' : $u2 = $i; break;
    case 'field4' : $u3 = $i; break;
    case 'field5' : $u4 = $i; break;
    case 'field6' : $u5 = $i; break;
    case 'field7' : $u6 = $i; break;
    case 'field8' : $u7 = $i; break;
    case 'field9' : $u8 = $i; break;
    case 'field10' : $u9 = $i; break;
   }
  }

  if($fromdb){
   $link2 = @mysql_connect($dbhost,$dbuser,$dbpass);
   mysql_select_db($dbfile,$link2) or die(mysql_error());
   $cmd = "select * from $dbtable where 1";
   $urows = mysql_query($cmd,$link2) or die(mysql_error());
   $total_sql_rows = mysql_num_rows($urows);
   mysql_select_db($sqldb,$link) or die(mysql_error());
  }

  $xcnt = 0;
  $x = 0;
  $done = '';

  @mysql_query("lock tables $utable",$link);

  if($infile || $total_sql_rows > 0){
   if($welcmail || $cnfmail){
    // set up mail queue
     $sendq = '0';
     $batid = qstart(3);
     if($welcmail){ $msg_type = '3'; $bat_type = '3'; }
     elseif($cnfmail){ $msg_type = '4'; $bat_type = '4'; }
   }

   // rem from other lists?
   $listopts = getlistopts($list);
   // if($listopts[2]=='1') $doremlists = 1; else $doremlists = '';

   // loop
   while(!$done){

    $done = '';

    // build udat
    $udat = array();
    $xcnt++;

    if($fromfile){
     // loop for each line in the file
     $buffer = fgets($infile, 1024);
     // strip the line feed off the end
     $buffer = str_replace("\n",'',$buffer);
     $buffer = str_replace("\r",'',$buffer);
     if(($buffer <> '') && ($buffer <> ' ')) $udat[] = csv_parse($buffer);
    }
    // debug
    if($fromdb){
     $row = mysql_fetch_row($urows);
     $tmparr = array();
     for($y=0; $y<=count($row); $y++){
      $tmparr[] = $row[$y];
     }
     $udat[] = $tmparr;
    }
    list($key,$udata)=each($udat);

    // generate unique id
    $uniq = '';
    while(!$uniq){
     $uniq_str = unique_id(7);
     if(@mysql_num_rows(mysql_query("select id from $utable where uid = '$uniq_str'",$link))==0) $uniq=1;
    }

    // reset vars
    $email = ''; $fname = ''; $lname = ''; $user1 = ''; $user2 = ''; $user3 = ''; $user4 = ''; $user5 = ''; $user6 = ''; $user7 = ''; $user8 = ''; $user9 = ''; $user10 = '';

    // assign vars
    $fname = $udata[$fn];
    $lname = $udata[$ln];
    $email = $udata[$em];
    $ipaddr = $udata[$ip];
    $refurl = $udata[$ru];
    $ulist = $udata[$ls];
    $dateadd = $udata[$da];
	//debug
	echo 'edituser assign vars '.$dateadd;
	//debug
    $seq = $udata[$sq];
    $del = $udata[$de];
    $user1 = $udata[$u0];
    $user2 = $udata[$u1];
    $user3 = $udata[$u2];
    $user4 = $udata[$u3];
    $user5 = $udata[$u4];
    $user6 = $udata[$u5];
    $user7 = $udata[$u6];
    $user8 = $udata[$u7];
    $user9 = $udata[$u8];
    $user10 = $udata[$u9];

    $fname = addslashes($fname); $lname = addslashes($lname);
    $user1 = addslashes($user1); $user2 = addslashes($user2); $user3 = addslashes($user3);
    $user4 = addslashes($user4); $user5 = addslashes($user5); $user6 = addslashes($user6);
    $user7 = addslashes($user7); $user8 = addslashes($user8); $user9 = addslashes($user9);
    $user10 = addslashes($user10);

    // dupe check
    if($ulist) $lis = addslashes($ulist); else $lis = $list;

    // validity check
    $isvalid = valid_email($email);

    $cmd = "select id,cnf from $utable where email like '$email' and list = '$lis'";
    $drow = @mysql_query($cmd,$link);
    if(@mysql_num_rows($drow)>0){
     list($xid,$xcnf)=mysql_fetch_row($drow);
     if((($xcnf=='2' || $xcnf=='0' || $xcnf=='3') && !$noreplace) || $yesreplace){
      $isadupe = '';
      mysql_query("delete from $utable where id = '$xid'",$link);
     } else {
      // add opts
      if($listopts[2]=='1') remlists($email,$lis,2);
      if($listopts[3]=='1') addlists($email,$lis,3);
      $isadupe = 1;
     }
    } else {
     $isadupe = '';
    }

    // check done
    if($fromfile){
     if(feof($infile)==1) {
      $done = 1;
      fclose($infile);
     }
    }
    if($fromdb){ if(($xcnt+1)==$total_sql_rows) $done = 1; }

    if(!$isadupe && $isvalid){
     $x++;
     if($seq) $setseq = $seq;
     if($del) $setdel = $del;
     if($dateadd) $today = $dateadd; else $today = date("Y-m-d H:i:s");
     if($refurl || !$customrefu) $refu = addslashes($refurl);
	elseif($customrefu) $refu = addslashes($crvalue);
	else $refu = 'Imported';
     if($ipaddr) $ipadd = $ipaddr; else $ipadd = 'Unknown';

     if($welcmail || (!$welcmail && !$cnfmail)) $xcnf = '1';  if($cnfmail) $xcnf = '0';


     $cmd = "insert into $utable values ('null','$uniq_str','$lis','$fname','$lname','$email','$user1','$user2','$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10','$setseq','$setdel','$xcnf','$today','$ipadd','$refu','1','0')";
     if($demo<>'yes'){
      @mysql_query($cmd,$link);
      if($listopts[2]=='1') remlists($email,$lis,2);
      if($listopts[3]=='1') addlists($email,$lis,3);
      if(!$cnfmail && $xtrmail && file_exists('./signup-xtra.php')){
       $xlist = $list;
       $list = $lis;
       $uid = $uniq_str;
       $userseq = $setseq;
       $userdel = $setdel;
       $confirmed = $xcnf;
       $html = '1';
       $ipaddr = $ipadd;
       $refurl = $refu;
       include('./signup-xtra.php');
       mysql_select_db($sqldb,$link);
       $list = $xlist;
      } elseif($cnfmail && file_exists('./signup-confirm-xtra.php')){
       $xlist = $list;
       $list = $lis;
       $uid = $uniq_str;
       $userseq = $setseq;
       $userdel = $setdel;
       $confirmed = $xcnf;
       $html = '1';
       $ipaddr = $ipadd;
       $refurl = $refu;
       $refurl = 'Imported';
       include('./signup-confirm-xtra.php');
       mysql_select_db($sqldb,$link);
       $list = $xlist;
      }
      // add to queue..
      if($welcmail || $cnfmail){
       $xid = calc32();
       $cmd = "insert into $otable(id,bat,battype,mtype,uid,mid) values('$xid','$batid','$bat_type','$msg_type','".mysql_insert_id()."','');";
       mysql_query($cmd,$link) or die(mysql_error());
       $sendq++;
      }

     }
    } else {
     if($isadupe){
      $dupes++;
     }
     if(!$isvalid){
      if($email <> ''){
       $badmails++;
       if($bademail){
        if(!$badem) $badem = "The following email addresses failed during your last import:\n\n";
        $badem .= "$email\n";
       }
      }
     }
    }
    if($done || (($xcnt) % 1000) == 0){
     @mysql_query("unlock tables",$link);
     if($dupes <> $last_d){
      echo "<script language=javascript>document.imp.dfield.value='$dupes'</script>\n";
      $last_d = $dupes;
     }
     if($x <> $last_x){
      echo "<script language=javascript>document.imp.nfield.value='$x'</script>\n";
      $last_x = $x;
     }
     if($badmails <> $last_b){
      echo "<script language=javascript>document.imp.bfield.value='$badmails'</script>\n";
      $last_b = $badmails;
     }
     flush();
     @mysql_query("lock tables $utable",$link);
    }
    if($done || (($xcnt+1) % 10000) == 0) @mysql_query("optimize table $utable",$link);

   } // end !done
   @unlink($filename);
   @mysql_query("unlock tables",$link);
   if($fromdb) @mysql_close($link2);
   $rows = mysql_query("select admmail,erraddr from $ctable where 1",$link);
   list($admmail,$erraddr)=mysql_fetch_row($rows);
   list($admmail,$testmail)=explode(';',$admmail);
   if($bademail && $badem) mail($admmail,'Failed to import addresses',$badem,"Return-path: $erraddr\nFrom: \"ListMail Notification\" <notif@".getdomain().">");
   // send welcome/confirmation email
   if($welcmail) $mailstr = 'welc'; elseif($cnfmail) $mailstr = 'conf';
   if(($welcmail || $cnfmail) && $sendq > 0){
    qcheckin($batid,1);
    $sentok = domail('',$mailstr,$list,$batid);
    if($sentok){
     qfinish($batid);
     echo "<br><span class=table_inside_normal>Finished sending!</span><br><br>";
     echo "<input class=button type=button value=\"Back to Users\" onclick=\"hopto('./edituser.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">
</center>
</body>
</html>\n";
    } else echo "<br>Not sent ok.<br>";
   }
  } else {
   // if($fromfile) echo "File not found<br>";
   // if($fromdb) echo "No rows in database<br>";
  }
  if(!$errors){
   $mainpage = '';
   echo "</body>\n</html>\n";
  } else $mainpage = 1;
 }

 if($mainpage){
  adminheader('LM: Import Users','Import Users','');
  echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
  listmenu($list,'import');
  echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg><span class=table_head_text>Import From CSV Text File</span></td><td align=right class=table_head_bg><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('import')."');\">Help</a><br></td></tr></table>
</td></tr>
<tr><td bgcolor=#eeeeee><table border=0 cellspacing=0 cellpadding=3><tr><td><div style=\"margin-left: 5px\">
<span class=table_inside_normal>Users may be imported in a comma-delimited (CSV) text file, one user per line.<br>There can be up to 13 fields per line in the file. (first, last, email, + 10 custom).<br>Upload your file through the script or FTP it into your ListMail directory.<br></span>
<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <form name=impform enctype=multipart/form-data method=post>
   <input type=hidden name=MAX_FILE_SIZE value=2000000>
   <input type=hidden name=import value=1>
   <input type=hidden name=gettypes value=1>
   <input type=hidden name=list value=$list>
   <input type=hidden name=upload value=1>
   <input type=hidden name=fromfile value=1><span class=table_inside_small>Browse to and upload your file:<br>
   <input type=file name=userfile class=xbox style=\"font: 8pt helvetica; cursor: hand\" name=impfile size=40>
  </td>
 </tr>
 <tr><td height=2><img src=1.gif height=2></td></tr>
 <tr>
  <td>
   <input type=submit class=button value=\"Send File\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 65px\">
  </td></form>
 </tr>
</table>
<img src=1.gif height=10><br>
<span class=table_inside_normal>
-or-<br></span>
<img src=1.gif height=10><br>
<span class=table_inside_small>File name (Should be sent by FTP to ListMail dir)<br></span>
<table border=0 cellspacing=0 cellpadding=0><tr><td><form name=locfile method=post><input type=hidden name=list value=$list><input type=hidden name=import value=1><input class=xbox type=text name=filename size=30><input type=hidden name=gettypes value=1><input type=hidden name=fromfile value=1><input type=hidden name=upload value=\"\"><br>
</td></tr><tr><td width=2><img src=1.gif width=2></td></tr><tr><td><input class=button type=submit value=\"Continue\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 70px\">"; if($errors && $nofile) echo "<img src=1.gif width=10><font color=red class=status_text>File not found</font>";
echo "</td></form></tr></table>";
echo "</div></td></tr></table><img src=1.gif height=2><br></td></tr></table>
<img src=1.gif height=5><br>
<table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg><span class=table_head_text>Import From MySQL Database</span></td><td align=right class=table_head_bg><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('import')."');\">Help</a><br></td></tr></table>
</td></tr>
<tr><td bgcolor=#eeeeee>
 <table border=0 cellspacing=0 cellpadding=3><tr><td><div style=\"margin-left: 5px\">
<span class=table_inside_normal>
Enter your MySQL database connection information.  Your fields will be automatically detected.<br>
<img src=1.gif height=10><br>
<table  border=0 cellspacing=0 cellpadding=0>
<tr><td>
<style rel=stylesheet type=text/css><!--
 .xfields { width: 100px }
--> </style>"; if(!$dbhost) $dbhost = 'localhost'; echo "<table border=0 cellspacing=0 cellpadding=0><tr><td><form name=indb method=post><input type=hidden name=list value=$list><input type=hidden name=import value=1><input type=hidden name=gettypes value=1><input type=hidden name=fromdb value=1><span class=table_inside_small>Hostname</span><br><input type=text class=xbox name=dbhost value=\"$dbhost\" class=xfields size=15 value=localhost><br></td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small>Username</span><br><input type=text class=xbox name=dbuser class=xfields size=15 value=\"$dbuser\"><br></td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small>Password</span><br>
<input type=text class=xbox name=dbpass class=xfields size=15 value=\"$dbpass\"><br></td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small>Database</span><br><input type=text class=xbox name=dbfile class=xfields size=15 value=\"$dbfile\"><br></td><td width=10><img src=1.gif width=10></td><td><span class=table_inside_small>Table</span><br><input type=text class=xbox name=dbtable class=xfields size=15 value=\"$dbtable\"><br></td></tr></table>
<img src=1.gif height=2><br>
<input class=button type=submit value=\"Continue\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 70px\">"; if($errors && $badconn) echo "<img src=1.gif width=10><font color=red class=status_text>Bad MySQL host, username, or password.</font>";
if($errors && $baddb) echo "<img src=1.gif width=10><font color=red class=status_text>Host, user, and pass OK, database incorrect!</font>";
if($errors && $badftable) echo "<img src=1.gif width=10><font color=red class=status_text>You must enter your database and table names.</font>";

echo "</td></form></tr></table>
</td></tr></table>
<img src=1.gif height=2><br></td></tr></table>
<img src=1.gif height=4><br>
<input type=button class=button value=\"Back to Users\" onclick=\"hopto('./edituser.php?list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">
</span>";
 } // end mainpage
 exit;
} // END IMPORT

// ************************************************************************************************ //

// START EXPORT
if($export){
 // nocacheheader();
 if($dl=='1'){
  if($maximizer && $max) $ext = 'MTI'; else $ext = 'CSV';
  header("Content-type: application/octet-stream");
  header('Content-Disposition: filename=Export.'.$ext);
  readfile('./attach/Export.'.$ext);
  exit;
 }
 if(!$do_export){
  // default main page - assume default
  $x_fn = 1; $x_em = 1; $x_ln = 1; $x_lnum = 1; $x_uid = 1; $x_da = 1; $x_seq = 1; $x_del = 1;
  // only check custom fields if they're ON
  $f = getcfields($list,'users');
  for($i=1;$i<count($f);$i++){
   if($f[$i]) ${"x_u".$i} = 1; else ${"x_u".$i} = '';
  }
  $x_descs = 1;
 }

 if(($do_export && $do_export <> 'null') || ($del_file && $del_file <> 'null')){
  if($x_exportto=='file' || $x_exportto=='webt' || ($del_file && $del_file <> 'null')){
   adminheader('LM: Export Users','Export Users','');

   echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

   listmenu($list,'export');

   echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Export Users</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('export')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
   </table>
   <img src=1.gif height=4><br>
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <form name=export_frm method=post>
      <input type=hidden name=list value=$list>
      <input type=hidden name=do_export value=null>
      <input type=hidden name=del_file value=null>
      <input type=hidden name=rem_rule value=null>
      <input type=hidden name=rem_num value=null>
      <input type=hidden name=addrule value=null>
      <span class=table_inside_normal>";
  }
  if($do_export && $do_export <> 'null'){
   if($x_exportto=='file') echo "         Exporting users, please wait.. ";
   elseif($x_exportto=='webp' || $x_exportto=='webt'){
    if($x_exportto=='webp'){
     echo "<html>\n<head>\n<title>ListMailPRO Export</title>\n</head><body><div style=\"font: 12pt courier-new;\">";
    } elseif($x_exportto=='webt'){
     echo "<textarea rows=30 cols=110 name=texport id=texport style=\"padding: 1px 1px 1px 1px; font: 10pt courier-new; width: 630px\">";
    }
   }
   if($x_uniq_em) $dupe_em = 'distinct '; else $dupe_em = '';
   if($x_d){
    // echo "filtering by date<br>";
    if($x_d_type == 'a') $bracket = '>';
    if($x_d_type == 'b') $bracket = '<';
    $date_cmd = " and dateadd $bracket '$year-$month-$day'";
    // echo "date_cmd=$date_cmd<br>";
   } else $date_cmd = '';

   $export_cmd = "select ".$dupe_em."email,list,uid,cseq,cdel,dateadd,fname,lname,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,cnf,ipaddr,refurl,htmail from $utable where "; $line = '';
   for($i=0;$i<$nr;$i++){
    if($i==0) $line = '('; else $line = ' or (';
    if($target[$i]=='all') $line .= '1 and '; else $line .= "list = '$target[$i]' and ";
    if($stat[$i]=='all') $line .= '';
    if($stat[$i]=='act') $line .= "cnf = '1' and ";
    if($stat[$i]=='unc') $line .= "cnf = '0' and ";
    if($stat[$i]=='rem') $line .= "cnf = '2' and ";
    if($stat[$i]=='bnc') $line .= "cnf = '3' and ";

    switch($field[$i]){
     case 'f': $fn = 'fname'; break;
     case 'l': $fn = 'lname'; break;
     case 'e': $fn = 'email'; break;
     case 's': $fn = 'seq'; break;
     case '0': $fn = 'user1'; break;
     case '1': $fn = 'user2'; break;
     case '2': $fn = 'user3'; break;
     case '3': $fn = 'user4'; break;
     case '4': $fn = 'user5'; break;
     case '5': $fn = 'user6'; break;
     case '6': $fn = 'user7'; break;
     case '7': $fn = 'user8'; break;
     case '8': $fn = 'user9'; break;
     case '9': $fn = 'user10'; break;
    }
    if($q_type[$i]=='e') $line .= "$fn = '".addslashes($q_for[$i])."'";
    if($q_type[$i]=='a') $line .= "$fn like '%".addslashes($q_for[$i])."%'";
    if($date_cmd) $line .= $date_cmd;
    $line .= ')';
    $export_cmd .= $line;
   }
   // echo "export_cmd=<input type=text class=xbox size=120 value=\"".htmlspecialchars($export_cmd)."\"><br>";

   $xrows = mysql_query($export_cmd,$link);
   $num_xrows = mysql_num_rows($xrows);
   if($maximizer && $max_export) $ext = 'MTI'; else $ext = 'CSV';
   $filename = './attach/Export.'.$ext;
   if($x_exportto=='file'){
    $xf = fopen($filename,'w');
    if(!$xf) echo "<span style=\"color: red\">Error opening $filename for writing.  Is your attach folder CHMOD to 777?</span><br>";
    flush();
   }
   // first line file descriptors
   if($x_descs && !$max_export){
    $xline = '';
    $first = 1;
    if($x_uid) { if(!$first) $xline .= ','; $xline .= "\"UID\""; $first = ''; }
    if($x_da) { if(!$first) $xline .= ','; $xline .= "\"Date Added M/D/Y\""; $first = ''; }
    if($x_lnum) { if(!$first) $xline .= ','; $xline .= "\"List\""; $first = ''; }
    if($x_seq) { if(!$first) $xline .= ','; $xline .= "\"Seq #\""; $first = ''; }
    if($x_del) { if(!$first) $xline .= ','; $xline .= "\"Del #\""; $first = ''; }
    if($x_em) { if(!$first) $xline .= ','; $xline .= "\"Email Address\""; $first = ''; }
    if($x_fn) { if(!$first) $xline .= ','; $xline .= "\"First Name\""; $first = ''; }
    if($x_ln) { if(!$first) $xline .= ','; $xline .= "\"Last Name\""; $first = ''; }

    $fields = getcfields($list,'users');
    for($i=1;$i<=10;$i++){
     if(${"x_u".$i}){
      if(!$first) $xline .= ',';
      $xline .= "\"$fields[$i]\"";
      $first = '';
     }
    }

    if($x_stat) { if(!$first) $xline .= ','; $xline .= "\"User Status\""; $first = ''; }
    if($x_ip) { if(!$first) $xline .= ','; $xline .= "\"IP Address\""; $first = ''; }
    if($x_refu) { if(!$first) $xline .= ','; $xline .= "\"Referring URL\""; $first = ''; }
    if($x_html) { if(!$first) $xline .= ','; $xline .= "\"HTML\""; $first = ''; }

    if($x_exportto=='file') fwrite($xf,$xline."\r\n");
    elseif($x_exportto=='webp') echo nl2br($xline."\n");
    elseif($x_exportto=='webt') echo $xline."\n";
   } // descs
   if($max_export){
    // maximizer header
    $max_header = str_replace("\r\n","\n",$max_header);
    $max_header = str_replace("\n","\r\n",$max_header);
    fwrite($xf,$max_header."\r\n");
    // set up fields as per config
    $max_fields = explode(',',$max_fields);

    $all_fields = array('uid','da','lnum','seq','del','em','fn','ln','u1','u2','u3','u4','u5','u6','u7','u8','u9','u10','stat','ip','refu','html');
    for($j=0;$j<count($all_fields);$j++){
     if(in_array($all_fields[$j],$max_fields)){
      ${"x_".$all_fields[$j]} = 1;
     } else {
      ${"x_".$all_fields[$j]} = '';
     }
    }
   }

   if(@mysql_num_rows($xrows)>0){
    while(list($email,$lnum,$uid,$cseq,$cdel,$dateadd,$fname,$lname,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$stat,$ip,$refu,$html)=@mysql_fetch_row($xrows)){
     $email = str_replace('"','""',$email); $fname = str_replace('"','""',$fname); $lname = str_replace('"','""',$lname);
     $user1 = str_replace('"','""',$user1); $user2 = str_replace('"','""',$user2); $user3 = str_replace('"','""',$user3);
     $user4 = str_replace('"','""',$user4); $user5 = str_replace('"','""',$user5); $user6 = str_replace('"','""',$user6);
     $user7 = str_replace('"','""',$user7); $user8 = str_replace('"','""',$user8); $user9 = str_replace('"','""',$user9);
     $user10 = str_replace('"','""',$user10); $refu = str_replace('"','""',$refu);

     $xline = ''; $first = 1;
     if($x_uid) { if(!$first) $xline .= ','; $xline .= "\"$uid\""; $first = ''; }
     if($x_da) { if(!$first) $xline .= ','; $xline .= "\"$dateadd\""; $first = ''; }
     if($x_lnum) { if(!$first) $xline .= ','; $xline .= "\"$lnum\""; $first = ''; }
     if($x_seq) { if(!$first) $xline .= ','; $xline .= "\"$cseq\""; $first = ''; }
     if($x_del) { if(!$first) $xline .= ','; $xline .= "\"$cdel\""; $first = ''; }
     if($x_em) { if(!$first) $xline .= ','; $xline .= "\"$email\""; $first = ''; }
     if($x_fn) { if(!$first) $xline .= ','; $xline .= "\"$fname\""; $first = ''; }
     if($x_ln) { if(!$first) $xline .= ','; $xline .= "\"$lname\""; $first = ''; }
     if($x_u1) { if(!$first) $xline .= ','; $xline .= "\"$user1\""; $first = ''; }
     if($x_u2) { if(!$first) $xline .= ','; $xline .= "\"$user2\""; $first = ''; }
     if($x_u3) { if(!$first) $xline .= ','; $xline .= "\"$user3\""; $first = ''; }
     if($x_u4) { if(!$first) $xline .= ','; $xline .= "\"$user4\""; $first = ''; }
     if($x_u5) { if(!$first) $xline .= ','; $xline .= "\"$user5\""; $first = ''; }
     if($x_u6) { if(!$first) $xline .= ','; $xline .= "\"$user6\""; $first = ''; }
     if($x_u7) { if(!$first) $xline .= ','; $xline .= "\"$user7\""; $first = ''; }
     if($x_u8) { if(!$first) $xline .= ','; $xline .= "\"$user8\""; $first = ''; }
     if($x_u9) { if(!$first) $xline .= ','; $xline .= "\"$user9\""; $first = ''; }
     if($x_u10) { if(!$first) $xline .= ','; $xline .= "\"$user10\""; $first = ''; }
     if($x_stat) { if(!$first) $xline .= ','; $xline .= "\"$stat\""; $first = ''; }
     if($x_ip) { if(!$first) $xline .= ','; $xline .= "\"$ip\""; $first = ''; }
     if($x_refu) { if(!$first) $xline .= ','; $xline .= "\"$refu\""; $first = ''; }
     if($x_html) { if(!$first) $xline .= ','; $xline .= "\"$html\""; $first = ''; }

     if($x_exportto=='file') fwrite($xf,$xline."\r\n");
     elseif($x_exportto=='webp') echo nl2br($xline."\n");
     elseif($x_exportto=='webt') echo $xline."\n";
    }
    $norows = '';
   } else {
    $norows = 1;
   }
   if($x_exportto=='file') fclose($xf);
  } else {
   if($del_file && $del_file <> 'null'){
    if($maximizer) $ext = 'MTI'; else $ext = 'CSV';
    @exec('rm ./attach/Export.'.$ext);
    @exec('del .\attach\Export.'.$ext);
   }
  }

  if($do_export && $do_export <> 'null'){
   if($x_exportto=='file') echo "Done<br>\n";
   elseif($x_exportto=='webt') echo "</textarea>\n<script language=javascript><!--\ntexport.select();\n--></script>\n";
   elseif($x_exportto=='webp') echo "</div>\n";
  }

  if(!$norows && (!$del_file || $del_file == 'null')){
   $href = "./edituser.php?export=1&dl=1";
   if($maximizer && $max_export) $href .= '&max=1';
   if($x_exportto=='file' || ($del_file && $del_file <> 'null')){
    echo "</span><table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_normal>To download the file, </span><a class=table_inside_normal style=\"color: blue\" href=\"$href\">Click Here</a><span class=table_inside_normal>.  You may also want to rename the file.<br>
  To delete the file from the server (a good idea), </span><a href=# class=table_inside_normal style=\"color: red\" onclick=\"export_frm.do_export.value=''; export_frm.del_file.value=1; export_frm.submit();\">Click Here</a></td></tr></table>";
   }
  }
  if($norows){
   echo "<span class=table_inside_normal style=\"color: red\">No data!</span>\n";
  }
  if($del_file && $del_file <> 'null') echo "<span class=table_inside_normal>File deleted.</span>";
  list($lmpath)=mysql_fetch_row(mysql_query("select listmailpath from $ctable where 1",$link));

  if($x_exportto=='file' || $x_exportto=='webt' || ($del_file && $del_file <> 'null')){
   echo "   <img src=1.gif height=3><br>
   <table border=0 cellspacing=0 cellpadding=0><tr><td><input type=button class=button onclick=\"hopto('".$lmpath."edituser.php?list=$list&export=1')\" value=\"Back to Export\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\"><br></td><td width=3><img src=1.gif width=3></td><td><input class=button type=button onclick=\"hopto('./edituser.php?list=$list');\" value=\"Back to Users\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"><br></td></tr></table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</td></tr></table>\n";
   flush();
   echo "</body>
</html>";
  }
  exit;
 }

 // main page
 if($year) $y = $year; if($month) $m = $month; if ($day) $d = $day;
 if(!$m || !$d || !$y) { $m = date("m"); $y = date("Y"); $d = date("d"); }

 if($x_em) $xemchk = ' checked'; else $xemchk = '';
 if($x_fn) $xfnchk = ' checked'; else $xfnchk = '';
 if($x_ln) $xlnchk = ' checked'; else $xlnchk = '';
 if($x_lnum) $xlnumchk = ' checked'; else $xlnumchk = '';
 if($x_uid) $xuidchk = ' checked'; else $xuidchk = '';
 if($x_da) $xdachk = ' checked'; else $xdachk = '';
 if($x_seq) $xseqchk = ' checked'; else $xseqchk = '';
 if($x_del) $xdelchk = ' checked'; else $xdelchk = '';
 if($x_u1) $xu1chk = ' checked'; else $xu1chk = '';
 if($x_u2) $xu2chk = ' checked'; else $xu2chk = '';
 if($x_u3) $xu3chk = ' checked'; else $xu3chk = '';
 if($x_u4) $xu4chk = ' checked'; else $xu4chk = '';
 if($x_u5) $xu5chk = ' checked'; else $xu5chk = '';
 if($x_u6) $xu6chk = ' checked'; else $xu6chk = '';
 if($x_u7) $xu7chk = ' checked'; else $xu7chk = '';
 if($x_u8) $xu8chk = ' checked'; else $xu8chk = '';
 if($x_u9) $xu9chk = ' checked'; else $xu9chk = '';
 if($x_u10) $xu10chk = ' checked'; else $xu10chk = '';
 if($x_stat) $xstatchk = ' checked'; else $xstatchk = '';
 if($x_ip) $xipchk = ' checked'; else $xipchk = '';
 if($x_refu) $xrefuchk = ' checked'; else $xrefuchk = '';
 if($x_html) $xhtmlchk = ' checked'; else $xhtmlchk = '';

 if($x_uniq_em) $xuniqemchk = ' checked'; else $xuniqemchk = '';
 if($x_descs) $xdescschk = ' checked'; else $xdescschk = '';
 if($max_export) $xmaxchk = ' checked'; else $xmaxchk = '';
 if($x_d) $xdchk = ' checked'; else $xdchk = '';
 if($x_d_type == 'a') $xdtachk = ' selected'; else $xdtachk = '';
 if($x_d_type == 'b') $xdtbchk = ' selected'; else $xdtbchk = '';

 if($rem_rule && $rem_rule <> 'null'){
  array_splice($target,$rem_num,1);
  array_splice($q_type,$rem_num,1);
  array_splice($field,$rem_num,1);
  array_splice($q_for,$rem_num,1);
  array_splice($stat,$rem_num,1);
  $nr = $nr - 1;
 }
 // echo " list=$list addrule=$addrule do_export=$do_export<br>";
 adminheader('LM: Export Users','Export Users','');

 echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1><script language=javascript><!--
 function checkyear(year) {
  return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
 }
 dx = '$d';
--></script>\n";

 listmenu($list,'export');

 echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Export Users</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('export')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
   </table>
   <img src=1.gif height=4><br>
   <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
   <form name=export_frm method=post>
   <span class=table_inside_normal>Enter fields to export:<br></span>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr align=right>
     <td>
      <span class=table_inside_small>
	 List Ref # <input type=checkbox class=chkbox value=1 name=x_lnum$xlnumchk><br>
	 Unique ID <input type=checkbox class=chkbox value=1 name=x_uid$xuidchk><br>
	 Date Added <input type=checkbox class=chkbox value=1 name=x_da$xdachk><br>
	 Email Addr <input type=checkbox class=chkbox value=1 name=x_em$xemchk><br>
	 </span>
     </td>
<!--     <td width=20><img src=1.gif width=20 height=1></td> -->
     <td>
      <span class=table_inside_small>
	 First Name <input type=checkbox class=chkbox value=1 name=x_fn$xfnchk><br>
      Last Name <input type=checkbox class=chkbox value=1 name=x_ln$xlnchk><br>
      Custom 1 <input type=checkbox class=chkbox value=1 name=x_u1$xu1chk><br>
 	 Custom 2 <input type=checkbox class=chkbox value=1 name=x_u2$xu2chk><br>
	 </span>
     </td>
<!--     <td width=20><img src=1.gif width=20 height=1></td> -->
     <td>
      <span class=table_inside_small>
      Custom 3 <input type=checkbox class=chkbox value=1 name=x_u3$xu3chk><br>
      Custom 4 <input type=checkbox class=chkbox value=1 name=x_u4$xu4chk><br>
	 Custom 5 <input type=checkbox class=chkbox value=1 name=x_u5$xu5chk><br>
      Custom 6 <input type=checkbox class=chkbox value=1 name=x_u6$xu6chk><br>
	 </span>
     </td>
<!--     <td width=20><img src=1.gif width=20 height=1></td> -->
     <td>
      <span class=table_inside_small>
      Custom 7 <input type=checkbox class=chkbox value=1 name=x_u7$xu7chk><br>
	 Custom 8 <input type=checkbox class=chkbox value=1 name=x_u8$xu8chk><br>
      Custom 9 <input type=checkbox class=chkbox value=1 name=x_u9$xu9chk><br>
      Custom 10 <input type=checkbox class=chkbox value=1 name=x_u10$xu10chk><br>
	 </span>
     </td>
<!--     <td width=20><img src=1.gif width=20 height=1></td> -->
     <td valign=top>
      <span class=table_inside_small>
	 Seq # <input type=checkbox class=chkbox value=1 name=x_seq$xseqchk><br>
      Delay # <input type=checkbox class=chkbox value=1 name=x_del$xdelchk><br>
      Status <input type=checkbox class=chkbox value=1 name=x_stat$xstatchk><br>
	 </span>
     </td>
<!--     <td width=20><img src=1.gif width=20 height=1></td> -->
     <td valign=top>
      <span class=table_inside_small>
      IP Address <input type=checkbox class=chkbox value=1 name=x_ip$xipchk><br>
      Referring URL <input type=checkbox class=chkbox value=1 name=x_refu$xrefuchk><br>
      HTML Email <input type=checkbox class=chkbox value=1 name=x_html$xhtmlchk><br>
      </span>
     </td>
    </tr>
   </table>
   <img src=1.gif height=5><br>
  </td>
 </tr>
</table>
<img src=1.gif height=1><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td height=1 bgcolor=#eeeeee><img src=1.gif height=1></td></tr></table>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <img src=1.gif height=3><br>
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <span class=table_inside_small><input type=checkbox class=chkbox value=1 name=x_uniq_em$xuniqemchk> Filter Duplicate Emails</span><br></td>
    </tr>
   </table>
   <img src=1.gif height=5><br>
  </td>
  <td width=10><img src=1.gif width=10 height=1></td>
  <td width=1 bgcolor=#eeeeee><img src=1.gif width=1></td>
  <td width=10><img src=1.gif width=10 height=1></td>
  <td>
   <img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <span class=table_inside_small><input type=checkbox class=chkbox value=1 name=x_d$xdchk> Filter By Date, Export Signups&nbsp;</span>
  </td>
  <td>\n";
 // date select
 $ty = (date("Y")-3);
 echo "<table border=0 cellspacing=0 cellpadding=0><tr><td><select name=x_d_type class=xbox><option value=a$xdtachk>After<option value=b$xdtbchk>Before</select></td><td width=5><img src=1.gif width=5></td><td><select class=xbox name=month onchange=\"mv = export_frm.month.value; if(mv=='2') { leapyear = checkyear(export_frm.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=export_frm.day.options.length-1; i>=0; i--) { if(i>27) export_frm.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { export_frm.day.options[i] = new Option(i+1,i+1); } if(export_frm.day.options[dx-1]) export_frm.day.options[dx-1].selected=true; else { export_frm.day.options[ndays-1].selected=true; dx=ndays; } \">";
 for($i=1;$i<=12;$i++){
  $itxt = date("F",mktime(0,0,0,$i,1,2002));
  if($i==$m) $sel = ' selected'; else $sel = '';
  echo "<option value=$i".$sel.">$itxt";
 }
 echo "</select></td><td width=5><img src=1.gif width=5></td><td><select class=xbox name=day onchange=\"dx = export_frm.day.selectedIndex+1;\">";
 for($i=1;$i<=date("t",mktime(0,0,0,$m,$d,$y));$i++){
  if($i==$d) $sel = ' selected'; else $sel = '';
  echo "<option value=$i".$sel.">$i";
 }
 echo "</select><br></td><td width=5><img src=1.gif width=5></td><td><select class=xbox name=year onchange=\"mv = export_frm.month.value; if(mv=='2') { leapyear = checkyear(export_frm.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=export_frm.day.options.length-1; i>=0; i--) { if(i>27) export_frm.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { export_frm.day.options[i] = new Option(i+1,i+1); } if(export_frm.day.options[dx-1]) export_frm.day.options[dx-1].selected=true; else { export_frm.day.options[ndays-1].selected=true; dx=ndays; } \">";
 for ($i=0;$i<=6;$i++) {
  if($ty==$y) $sel = ' selected'; else $sel = '';
  echo "<option value=$ty".$sel.">$ty";
  $ty++;
 }
 echo "</select><br></td></tr></table>
  </td>
 </tr>
</table>
   <img src=1.gif height=3><br>
  </td>
 </tr>
</table>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td height=1 bgcolor=#eeeeee><img src=1.gif height=1></td></tr></table>
<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <span class=table_inside_small>Export rules.  Search \"Any\" matches with a blank query to select the entire list.&nbsp;&nbsp;<b>%</b> is wildcard.</span><br>
   <img src=1.gif height=5><br>
";
 if(!$nr) { $nr = 1; }

 // ECHO "NUMRULES=$nr<BR>";
 // loop rules
 for($i=0;$i<$nr;$i++){
  $ctarget = $target[$i];
  $cq_type = $q_type[$i];
  $cfield = $field[$i];
  $cq_for = $q_for[$i];
  $cstat = $stat[$i];
  if($cstat=='act') $stat_a_chk = ' selected'; else $stat_a_chk = '';
  if($cstat=='unc') $stat_u_chk = ' selected'; else $stat_u_chk = '';
  if($cstat=='rem') $stat_r_chk = ' selected'; else $stat_r_chk = '';
  if($cstat=='bnc') $stat_b_chk = ' selected'; else $stat_b_chk = '';


  // ECHO "<small><small>CTARGET = $ctarget CQ_TYPE = $cq_type CFIELD = $cfield CQ_FOR = $cq_for<br></small></small>";

  echo "
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td nowrap>
      <select name=stat[] class=xbox style=\"width: 77px\">
      <option value=all>All
      <option value=act$stat_a_chk>Active
      <option value=unc$stat_u_chk>Unconfirmed
      <option value=rem$stat_r_chk>Removed
      <option value=bnc$stat_b_chk>Bounced
      </select><br>
     </td>
     <td width=3><img src=1.gif width=3 height=1><br></td>
     <td nowrap>
      <select name=target[] class=xbox style=\"width: 120px\">
      <option value=all>All Lists\n";

  $cmd = "select listnum,title from $ltable where 1 order by listnum";
  $result = @mysql_query($cmd,$link);
  while(list($lnu2,$lti2) = @mysql_fetch_row($result)){
   $lti2 = "$lnu2: $lti2";
   if(!$ctarget){
    if($lnu2==$list && $list <> 'all') $l_x = ' selected'; else $l_x = '';
   } else {
    if($lnu2==$ctarget) $l_x = ' selected'; else $l_x = '';
   }
   echo "       <option value=".$lnu2.$l_x.">$lti2\n";
  }
  echo "       </select>";

  $asel = ''; $esel = '';
  switch($cq_type){
   case 'a': $asel = ' selected'; break;
   case 'e': $esel = ' selected'; break;
  }

  // reset selects
  $fsel = ''; $lsel = ''; $emsel = ''; $ssel = ''; $u0sel = ''; $u1sel = ''; $u2sel = ''; $u3sel = ''; $u4sel = ''; $u5sel = ''; $u6sel = ''; $u7sel = ''; $u8sel = ''; $u9el = ''; for($m=0;$m<10;$m++) ${"u".$m."sel"} = '';

  switch($cfield){
   case 'f': $fsel = ' selected'; break;
   case 'l': $lsel = ' selected'; break;
   case 'e': $emsel = ' selected'; break;
   case 's': $ssel = ' selected'; break;
   case '0': $u0sel = ' selected'; break;
   case '1': $u1sel = ' selected'; break;
   case '2': $u2sel = ' selected'; break;
   case '3': $u3sel = ' selected'; break;
   case '4': $u4sel = ' selected'; break;
   case '5': $u5sel = ' selected'; break;
   case '6': $u6sel = ' selected'; break;
   case '7': $u7sel = ' selected'; break;
   case '8': $u8sel = ' selected'; break;
   case '9': $u9sel = ' selected'; break;
  }
  echo "
      <span class=table_inside_toolbar>Search</span>
      <select name=q_type[] class=xbox>
      <option value=a$asel>Any
      <option value=e$esel>Exact
      </select>
      <span class=table_inside_small>matches in</span>
      <select name=field[] class=xbox>
      <option value=e$emsel>Email Addr
      <option value=f$fsel>First Name
      <option value=l$lsel>Last Name
      <option value=0$u0sel>Custom 1
      <option value=1$u1sel>Custom 2
      <option value=2$u2sel>Custom 3
      <option value=3$u3sel>Custom 4
      <option value=4$u4sel>Custom 5
      <option value=5$u5sel>Custom 6
      <option value=6$u6sel>Custom 7
      <option value=7$u7sel>Custom 8
      <option value=8$u8sel>Custom 9
      <option value=9$u9sel>Custom 10
      </select>
      <span class=table_inside_small>for</span>
      <input type=text class=xbox name=q_for[] size=11 maxlength=255 value=\"".htmlspecialchars($cq_for)."\">&nbsp;<input type=button class=button value=\"Remove\" onclick=\""; if($nr>1) echo "export_frm.rem_rule.value='1'; export_frm.rem_num.value='$i'; export_frm.submit();"; else echo "alert('You can not remove the only rule.');"; echo "\"><br>
     </td>
    </tr>
   </table>
   <img src=1.gif height=3><br>";
 }
 if($x_exportto=='file') $filechk = ' selected'; else $filechk = '';
 if($x_exportto=='webt') $webtchk = ' selected'; else $webtchk = '';
 if($x_exportto=='webp') $webpchk = ' selected'; else $webpchk = '';
 echo "
  </td>
 </tr>
</table>
   <input type=hidden name=nr value=\"$nr\">
   <input type=hidden name=do_export value=null>
   <input type=hidden name=del_file value=null>
   <input type=hidden name=rem_rule value=null>
   <input type=hidden name=rem_num value=null>
   <input type=hidden name=addrule value=null>
   <input type=hidden name=list value=$list>
   <input style=\"width: 90px;\" class=button type=button value=\"Add Rule\" onclick=\"export_frm.nr.value=$nr+1; export_frm.addrule.value='1'; export_frm.do_export.value=''; export_frm.submit();\"><br>
   <img src=1.gif height=5><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td height=1 bgcolor=#eeeeee><img src=1.gif height=1></td></tr></table>

<img src=1.gif height=3><br>
<span class=table_inside_small>Export data to CSV <select class=xbox name=x_exportto><option value=file$filechk>File<option value=webp$webpchk>Web, Plain<option value=webt$webtchk>Web, Textarea</select><br><img src=1.gif height=2><br><input type=checkbox class=chkbox value=1 name=x_descs$xdescschk> Field titles on first line"; if($maximizer) echo "<img src=1.gif width=10><input type=checkbox class=chkbox value=1 name=max_export$xmaxchk> <span class=table_inside_small>Maximizer </span>"; echo "</span><br>
<img src=1.gif height=5><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <input style=\"width: 120px;\" class=button type=button value=\"Export Users\" onclick=\"if(export_frm.x_fn.checked==1 || export_frm.x_ln.checked==1 || export_frm.x_em.checked==1 || export_frm.x_lnum.checked==1 || export_frm.x_uid.checked==1 || export_frm.x_seq.checked==1 || export_frm.x_del.checked==1 || export_frm.x_u1.checked==1 || export_frm.x_u2.checked==1 || export_frm.x_u3.checked==1 || export_frm.x_u4.checked==1 || export_frm.x_u5.checked==1 || export_frm.x_u6.checked==1 || export_frm.x_u7.checked==1 || export_frm.x_u8.checked==1 || export_frm.x_u9.checked==1 || export_frm.x_u10.checked==1 || export_frm.x_da.checked==1){ export_frm.do_export.value=1; export_frm.submit(); } else { alert('You must select at least one field to export.'); return false; }\">
  </td>
  <td width=5><img src=1.gif width=5><br></td>
  <td><input style=\"width: 120px;\" class=button type=button value=\"Back to Users\" onclick=\"hopto('./edituser.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"><br>
  </td>
 </tr>
</table>

  </form>

</td></tr></table>
<script language=javascript>window.status='".addslashes($logtxt)."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->";
// </td></tr></table> </body></html>";
 exit;
} // END EXPORT

// ************************************************************************************************ //

// MAIN SCRIPT

if ($nextpage == 'yes') { $pgnum = $nnum; }
if ($prevpage == 'yes') { $pgnum = $pnum; }

// quick delete
if ($dodel == 'yes') {
 $pgnum = $dnum;
 if($pgnum == ($rcount-1)) $pgnum = $pgnum - $xrows;

 if($demo <> 'yes'){
  // get list of id
  $urow = mysql_query("select list,cnf,fname,lname,email from $utable where id = '$delid'",$link);
  list($ulist,$ucnf,$ufn,$uln,$uem)=mysql_fetch_row($urow);

  // check whether to delete or flag as removed.
  $listopts = getlistopts($ulist);
  $logtxt .= "Removed $uem from List $ulist. ";
  if($listopts[4]=='1') remlists($uem,$ulist,4);
  if($listopts[5]=='1') addlists($uem,$ulist,5);

  if($listopts[1]==1){
   if($ucnf=='2' || $ucnf=='0'){
    mysql_query("delete from $utable where id = '$delid'",$link);
   } else {
    // simply flag user
    mysql_query("update $utable set cnf = '2' where id = '$delid'",$link);
   }
  } else {
   // delete the user
   mysql_query("delete from $utable where id = '$delid'",$link);
  }

 } else {
  $logtxt .= "Cannot remove user in demo. "; }
 }

// user edit quick save
if ($doedit == 'yes') {

 // check for dupe email
 $cmd = "select id from $utable where list = '$textlist' and email = '$textemail'";
 $userows = @mysql_query($cmd,$link);
 if((@mysql_num_rows($userows)>0 && strtoupper($textemail) <> strtoupper($origmail)) || !valid_email($textemail)){
  // dupe! dont save.
  if(!valid_email($textemail)) $logtxt .= "User NOT saved, bad email format! ";
   else $logtxt .= "User NOT saved, duplicate email address! ";
 } else {
  $textemail = addslashes($textemail);
  $textfn = addslashes($textfn);
  $textln = addslashes($textln);

  $pgnum = $enum;
  if($textcdel<'0') $textcdel = '0';

  $cmd = "update $utable set email = '$textemail', list = '$textlist', fname = '$textfn', lname = '$textln', cseq = '$textcseq', cdel = '$textcdel' where id = '$editid'";
  mysql_query($cmd,$link);
  // echo "User ID $editid email written to \"$textemail\" <br><br>";
  $logtxt .= "User \"".stripslashes($textfn)." ".stripslashes($textln)."\" ($textemail) data saved. ";
 }
}

// sort save
if($setsort) {
 if($sord=='desc') $sord = 'desc'; else $sord = 'asc';
 $xopts = addslashes($xrows).';'.addslashes($sord).';'.addslashes($sby);

 mysql_query("update $ctable set sortopts = '$xopts' where 1",$link);
 $pgnum = '1';
}

// with selected or all results
if($dwselect){
 $logtxt = "doids=$doids<br>";
}

// check $pgnum (current limit value) for bad values
if (($pgnum == '') || ($pgnum < 0)) { $pgnum = 1;}

//
// default page
//

if($dodefault <> 'no'){

if(!$list) $list = firstlist();

adminheader('LM: User Edit','User Database','');

// set urows from config
$crow = mysql_query("select sortopts from $ctable where 1",$link);
list($sortopts)=mysql_fetch_row($crow);
list($xrows,$sord,$sby)=explode(';',$sortopts);

if(!$sby) $sby = 'dadd';
if(!$sord) $sord = 'desc';
if(!$cnf && $cnf <> '0') $cnf = '1';
if(!$xrows) $xrows = '10';

echo "

<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

listmenu($list,'users');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
   <tr>
    <td bgcolor=white>
     <table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr>
       <td>\n";

if($dosearch){
 $npsearch = "<input type=hidden name=dosearch value=yes><input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\"><input type=hidden name=sfor value=$sfor>\n";
 if(!$sfor) $sfor = 'email';
 if($list=='all') $wher = "1 and "; else $wher = "list = '$list' and ";
 $wher .= "$sfor like '%".addslashes($txtsfor)."%'";
 if($sfor=='fname') $sfor_fna = ' selected';
 if($sfor=='lname') $sfor_lna = ' selected';
 if($sfor=='seq') $sfor_seq = ' selected';
 if($sfor=='dateadd') $sfor_dadd = ' selected';
 if($sfor=='ipaddr') $sfor_ip = ' selected';
 if($sfor=='refurl') $sfor_refu = ' selected';

 for($i=1;$i<=10;$i++){
  if($sfor=="user".$i)
  ${"sfor_u".$i} = ' selected';
 }
} else {
 if($list=='all') $wher = "1"; else $wher = "list = '$list'";
}

// get number of results
$rcount = @mysql_num_rows(mysql_query("select id from $utable where $wher and cnf = '$cnf'",$link));

if($pgnum==1){
 if($rcount=='0') $cnum = '0'; else $cnum = '1';
} else $cnum = ((($pgnum * $xrows) - $xrows)+1);

$cnum2 = ($pgnum * $xrows);
if($cnum2 > $rcount) $cnum2 = $rcount;

if($list=='all') $listxt = "All Lists"; else $listxt = "List $list";
$xlist = $list;

if($dosearch){
 $logtxt .= "$listxt - <b>$rcount</b> results for <b>$txtsfor</b> in <b>$sfor</b>. <a class=status_link2 href=./edituser.php?list=$list&pgnum=$pgnum&sby=$sby&sord=$sord&cnf=$cnf>Stop Search</a>";
}

// $count = @mysql_num_rows(mysql_query("select id from $utable where $wher",$link));
// $numpages = ceil($count / $xrows);

$numpages = ceil($rcount / $xrows);
$testvar = $pgnum * $xrows;
$plim = $testvar - $xrows;

$prevnum = $pgnum - 1;
if($prevnum < 1) $prevnum = $pgnum;
$nextnum = $pgnum + 1;
if($nextnum > $numpages) $nextnum = $pgnum;

//  if(!$perp){ $perp = '10'; }
if($sord=='asc') { $sord_asc = " selected"; }
if($sord=='desc') { $sord_desc = " selected"; }
if($sby=='dadd') { $sby_dadd = " selected"; $ordby = "dateadd"; }
if($sby=='lnum') { $sby_lnum = " selected"; $ordby = "list"; }
if($sby=='uname') { $sby_unam = " selected"; $ordby = "fname"; }
if($sby=='ulnam') { $sby_ulna = " selected"; $ordby = "lname"; }
if($sby=='umail') { $sby_umai = " selected"; $ordby = "email"; }
if($sby=='seq') { $sby_seq = " selected"; $ordby = "cseq"; }
if($sby=='ipaddr') { $sby_ip = " selected"; $ordby = "ipaddr"; }
if($sby=='refurl') { $sby_refu = " selected"; $ordby = "refurl"; }
for($i=1;$i<=10;$i++){
 if($sby=="user".$i){
  ${"sby_u".$i} = ' selected';
  $ordby = "user".$i;
 }
}
switch($cnf){
 case '1': $cnf_act = ' selected'; $cnf_txt = 'Active'; break;
 case '0': $cnf_unc = ' selected'; $cnf_txt = 'Unconfirmed'; break;
 case '2': $cnf_rem = ' selected'; $cnf_txt = 'Removed'; break;
 case '3': $cnf_bnc = ' selected'; $cnf_txt = 'Bounced'; break;
}

echo "

        <table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
          <td width=1>

           <!-- search table -->
           <table border=0 cellspacing=0 cellpadding=0>
            <tr>
             <td nowrap>
              <form name=search_form method=post action=$PHP_SELF>
              <input type=hidden name=dosearch value=1>
              <input type=hidden name=sby value=$sby>
              <input type=hidden name=sord value=$sord>
              <input type=hidden name=cnf value=$cnf>
              <input type=hidden name=xrows value=$xrows>
              <span class=table_inside_toolbar>Search<img src=1.gif width=2></span></td>
             <td>
              <select name=sfor class=xarea style=\"width: 94px\">
              <option value=email>Email Address
              <option value=fname$sfor_fna>First Name
              <option value=lname$sfor_lna>Last Name
              <option value=cseq$sfor_seq>Seq #
	    <option value=dateadd$sfor_dadd>Date Added
	    <option value=ipaddr$sfor_ip>IP Address
	    <option value=refurl$sfor_refu>Referring URL\n";

// add customs to search box
if($xlist=='all'){
 for($i=1;$i<=10;$i++) echo "              <option value=user$i${"sfor_u".$i}>Custom #$i\n";
} else {
 $fields = getcfields($xlist,'users');
 for($i=1;$i<=10;$i++) if($fields[$i]) echo "              <option value=user$i".${"sfor_u".$i}.">$fields[$i]\n";
}

echo "              </select>
             </td>
             <td nowrap>
              <span class=table_inside_toolbar><img src=1.gif width=2>for<img src=1.gif width=2></span>
             </td>
             <td valign=top>
              <input class=xbox type=text name=txtsfor size=12 value=\"".htmlspecialchars($txtsfor)."\" style=\"width: 75px\"><br>
	      <img src=1.gif height=1><br>
             </td>
             <td>
              <span class=table_inside_toolbar><img src=1.gif width=2></span><br>
             </td>
             <td valign=top nowrap><input type=hidden name=list value=$xlist><img src=1.gif height=1><br><input class=button type=submit name=search value=Go onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 20px\"><br>
              </form>
             </td>
            </tr>
           </table>
           <!-- end search -->
          </td>
          <td align=right>

           <form name=showsort_form method=post action=$PHP_SELF>
           <!-- sort table -->
           <table border=0 cellspacing=0 cellpadding=0>
            <tr>
             <td nowrap>
              <input type=hidden name=list value=$xlist>
              <span class=table_inside_toolbar><img src=1.gif width=3>Show<img src=1.gif width=2></span><br>
             </td>
             <td nowrap>
              <input name=xrows value=\"$xrows\" size=3 class=xbox style=\"width: 24px\"><img src=1.gif width=3><br>
	      <img src=1.gif height=1><br>
             </td>
             <td><select class=xarea name=cnf onchange=\"showsort_form.submit()\" style=\"width: 86px\"><option value=1$cnf_act>Active<option value=0$cnf_unc>Unconfirmed<option value=2$cnf_rem>Removed<option value=3$cnf_bnc>Bounced</select><br></td>
<!--             <td><span class=table_inside_toolbar>Users&nbsp;</span><br></td> -->
             <td nowrap><span class=table_inside_toolbar><img src=1.gif width=2>Sort<img src=1.gif width=2></span></td>
             <td><select class=xarea name=sord onchange=\"showsort_form.submit()\"><option value=asc$sord_asc>Up<option value=desc$sord_desc>Down</select><br></td>
             <td><span class=table_inside_toolbar><img src=1.gif width=2>by<img src=1.gif width=2></span><br></td>
             <td><select class=xarea name=sby onchange=\"showsort_form.submit()\" style=\"width: 102px\"><option value=dadd$sby_dadd>Date Added<option value=lnum$sby_lnum>List #<option value=umail$sby_umai>Email Address<option value=uname$sby_unam>First Name<option value=ulnam$sby_ulna>Last Name<option value=seq$sby_seq>Seq #<option value=ip$sby_ip>IP Address<option value=refurl$sby_refu>Referring URL";
// add customs to sort box
if($xlist=='all'){
 for($i=1;$i<=10;$i++) echo "<option value=user$i${"sby_u".$i}>Custom #$i\n";
} else {
 // $fields = getcfields($xlist,'users');
 for($i=1;$i<=10;$i++) if($fields[$i]) echo "<option value=user$i".${"sby_u".$i}.">$fields[$i]\n";
}
echo "             </select><br></td>
             <td><span class=table_inside_toolbar><img src=1.gif width=3></span><br></td>
             <td>
              <input type=hidden name=setsort value=1><input type=hidden name=pgnum value=$pgnum><input type=hidden name=dosearch value=$dosearch><input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\"><input class=button type=submit name=rsh value=\"Set\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 22px\"><br>
              </form>
              <!-- end sort -->
             </td>
            </tr>
           </table>

          </td>
         </tr>
        </table>

        <img src=1.gif height=2><br>

        <table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
          <td width=1>

           <input class=button type=button value=\"Add a User\" onclick=\"hopto('./edituser.php?list=$xlist&pgnum=$pgnum&sby=$sby&sord=$sord&add=1&edit=1&sfor=$sfor&txtsfor=".urlencode($txtsfor)."&cnf=$cnf');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
          </td>
          <td width=3><img src=1.gif width=3></td>
          <td width=1><input class=button type=button value=\"Import Users\" onclick=\"hopto('./edituser.php?list=$xlist&import=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 96px\"></td>
          <td width=3><img src=1.gif width=3></td>
          <td width=1>
           <input type=button class=button value=\"Export Users\" onclick=\"hopto('./edituser.php?list=$xlist&export=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 96px\">
          </td>
<!--          <td width=3><img src=1.gif width=3></td>
          <td width=1>
           <input type=button class=button value=\"Ban Users\" onclick=\"hopto('./edituser.php?list=$xlist&ban=1');\">
          </td>
          -->
          <td>&nbsp;<br></td>
          <td nowrap width=1>
           <form name=prev_form method=post action=$PHP_SELF>$npsearch
           <input type=hidden name=prevpage value=yes>
           <input type=hidden name=pnum value=$prevnum>
           <input type=hidden name=sord value=$sord>
           <input type=hidden name=list value=$xlist>
           <input type=hidden name=sby value=$sby>
           <input type=hidden name=cnf value=$cnf>
           <input type=hidden name=xrows value=$xrows>
           <input class=button type=submit name=pp value=\"Prev Page\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 74px\"><br>
           </form>
          </td>
          <td width=3><img src=1.gif width=3></td>
          <td width=1>
           <form name=page_form method=post action=$PHP_SELF>$npsearch
           <input type=hidden name=sord value=$sord>
           <input type=hidden name=list value=$xlist>
           <input type=hidden name=sby value=$sby>
           <input type=hidden name=cnf value=$cnf>
           <input type=hidden name=xrows value=$xrows>
           <select class=xarea name=pgnum onchange=\"page_form.submit();\">";

// pages dropdown
for($z=1;$z<=$numpages;$z++){
 if($z==$pgnum) $sel = ' selected'; else $sel = '';
 echo "<option value=$z$sel>$z";
}

echo "</select>\n
           </form>
          </td>
          <td width=3><img src=1.gif width=3></td>
          <td width=1 align=right>
           <form name=next_form method=post action=$PHP_SELF>$npsearch
           <input type=hidden name=nextpage value=yes>
           <input type=hidden name=nnum value=$nextnum>
           <input type=hidden name=sord value=$sord>
           <input type=hidden name=list value=$xlist>
           <input type=hidden name=sby value=$sby>
           <input type=hidden name=cnf value=$cnf>
           <input type=hidden name=xrows value=$xrows>
           <input class=button type=submit name=np value=\"Next Page\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 74px\"><br>
           </form>
          </td>
         </tr>
        </table>

        <img src=1.gif height=2><br>

       </td>
      </tr>
      <tr>
       <td>
       <!-- border -->
        <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
          <td class=table_head_bg>
           <table width=100% border=0 cellspacing=0 cellpadding=0>
            <tr>
             <td>
              <span class=table_head_text>";

if($dosearch) echo "$listxt - <b>$rcount</b> matches for <b>$txtsfor</b> in <b>$sfor</b>";
 else echo "User Database - $listxt";

// Set up THE QUERY early so we can get the count
$cmd = "select id,list,fname,lname,email,cseq,cdel from $utable where $wher and cnf = '$cnf' order by $ordby $sord, id $sord limit $plim,$xrows";
// echo "cmd=$cmd...";
$result = mysql_query($cmd,$link);
$nresults = @mysql_num_rows($result);

echo "</span><br>
             </td>
             <td align=right><span class=table_head_text>$cnf_txt Users $cnum-$cnum2 of $rcount<img src=1.gif width=2>:</span><img src=1.gif width=2><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('user_database')."');\">Help</a><img src=1.gif width=2><br></td>
            </tr>
           </table>
          </td>
         </tr>
         <tr>
          <td>
           <table width=100% border=0 cellspacing=0 cellpadding=3>
            <tr class=bg2>
             <!-- <td valign=middle align=center nowrap><img src=1.gif width=1><input type=checkbox class=chkbox_nom onclick=\"
if(this.checked) for(i=1;i<=$nresults;i++) eval('form'+i+'.uid.checked = true'); else for(i=1;i<=$nresults;i++) eval('form'+i+'.uid.checked = false');\" style=\"width: 11px; height: 11px\"></td> -->
             <td><span class=table_head_col>List</span></td>
             <td><span class=table_head_col>First Name</span></td>
             <td><span class=table_head_col>Last Name</span></td>
             <td><span class=table_head_col>Email Address</span></td>
             <td><span class=table_head_col>Seq</span></td>
             <td><span class=table_head_col>Delay</span></td>
             <td><span class=table_head_col>&nbsp;</span></td>
             <td><span class=table_head_col>&nbsp;</span></td>
             <td><span class=table_head_col>&nbsp;</span></td>
            </tr>\n";

// THE LOOP
$formnum = 0;
while(list($id,$list,$fname,$lname,$email,$cseq,$cdel) = @mysql_fetch_row($result)) {
 $formnum++;
echo "            <tr nowrap>
<!--             <td valign=middle align=right>
             <input name=uid type=checkbox class=chkbox_nom value=$id><br>
             </td> -->
             <td align=center>
              <form name=form$formnum method=post action=$PHP_SELF>
              <input type=hidden name=doedit value=yes>
              <input type=hidden name=editid value=$id>
              <input type=hidden name=enum value=$pgnum>
              <input type=hidden name=origmail value=\"$email\">
              <input class=xbox type=text name=textlist value=\"$list\" size=2 style=\"width: 34px\">
             </td>
             <td>
              <input class=xbox type=text name=textfn size=12 value=\"".htmlspecialchars($fname)."\" style=\"width: 90px\">
             </td>
             <td>
              <input class=xbox type=text name=textln size=12 value=\"".htmlspecialchars($lname)."\" style=\"width: 90px\">
             </td>
             <td>
              <input class=xbox type=text name=textemail size=25 value=\"$email\" style=\"width: 169px\">
             </td>
             <td align=center>
              <input class=xbox type=text name=textcseq size=2 style=\"width: 34px\" value=\"$cseq\">
             </td>
             <td align=center>
              <input class=xbox type=text name=textcdel size=2 style=\"width: 34px\" value=\"$cdel\">
             </td>
             <td align=center>
              <input type=hidden name=list value=$xlist>
              <input type=hidden name=pgnum value=$pgnum>
              <input type=hidden name=dosearch value=$dosearch>
              <input type=hidden name=sby value=$sby>
              <input type=hidden name=sord value=$sord>
              <input type=hidden name=sfor value=$sfor>
              <input type=hidden name=cnf value=$cnf>
              <input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\">
              <input type=hidden name=xrows value=$xrows>
              <input class=button type=button name=savebut value=Save onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\" onclick=\"form$formnum.submit()\">
             </td></form>
             <td align=center>
              <input class=button type=button value=Edit onclick=\"hopto('./edituser.php?list=$xlist&pgnum=$pgnum&edit=1&id=$id&sby=$sby&sord=$sord&dosearch=$dosearch&sfor=$sfor&txtsfor=".htmlspecialchars($txtsfor)."&cnf=$cnf');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\">
             </td>
             <td align=center>
              <form name=f3 method=post onSubmit=\"if (!confirm('Are you sure you want to remove this user?')) return false\" action=$PHP_SELF>
              <input type=hidden name=dodel value=yes>
              <input type=hidden name=dosearch value=$dosearch>
              <input type=hidden name=sby value=$sby>
              <input type=hidden name=sord value=$sord>
              <input type=hidden name=sfor value=$sfor>
              <input type=hidden name=cnf value=$cnf>
              <input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\">
              <input type=hidden name=dnum value=$pgnum>
              <input type=hidden name=delid value=$id>
              <input type=hidden name=list value=$xlist>
              <input type=hidden name=xrows value=$xrows>
              <input class=button type=submit name=d value=Delete onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\">
             </td></form>
            </tr>\n";
}
echo "           </table>
           <!-- end bord -->
          </td>
         </tr>
        </table>
       </td>
      </tr>
     </table>
    </td>
   </tr>
  </table>\n";
/* echo "<img src=1.gif height=3><br>
<form name=dw>
<input type=hidden name=dwselect value=1>
<input type=hidden name=doids value=\"\">
<input type=hidden name=list value=$xlist>
<input type=hidden name=pgnum value=$pgnum>
<input type=hidden name=dosearch value=$dosearch>
<input type=hidden name=sby value=$sby>
<input type=hidden name=sord value=$sord>
<input type=hidden name=sfor value=$sfor>
<input type=hidden name=cnf value=$cnf>
<input type=hidden name=txtsfor value=\"".htmlspecialchars($txtsfor)."\">
<input type=hidden name=xrows value=$xrows>
<span class=table_inside_normal>With<img src=1.gif width=2><select class=xarea name=dowith>
<option value=selected>Selected
<option value=all>All $rcount Results
</select><img src=1.gif width=2><select class=xarea name=dowhat>
<option value=copy>Copy to List
<option value=move>Move to List
<option value=select>Create Selection
<option value=set>Change Data
</select><img src=1.gif width=2><input type=submit class=button value=Go style=\"width: 20px\" onclick=\"
// alert('building array of ids. $nresults forms to process.');
dw.doids.value = '';
first = 1;
for(i=1;i<=$nresults;i++){
 if(eval('form'+i+'.uid.checked')){
  if(!first) dw.doids.value += ';';
  first = false;
  dw.doids.value += eval('form'+i+'.uid.value');
 }
}
// alert('dw.doids.value='+dw.doids.value);
if(dw.doids.value=='' && dw.dowith.value=='selected'){
 alert('You must select at least one user to work with.');
 return false;
}
\"></span><br>\n"; */

if($logtxt) echo "<img src=1.gif height=5><br><span class=status_text>$logtxt</span><br>";

echo "  </td>
 </tr>
</table>
</div>
</body>
</html>
<script language=javascript>window.status='".addslashes($logtxt)."';</script>
<!-- ListMail (c) 2001 Dean Wiebe -->";
}

} // pw check
// EOF
?>
