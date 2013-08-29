<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */
   
include("./config.php");
include("./admin.php");

$islogged = islogged();

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);

if(!$list || $list == 'all') $list = firstlist();

if($add && !$save && !$ql_load && !$ql_del && !$preview){
 // defaults
 list($year,$month,$day) = explode('-',date("Y-m-d"));
}

// setup
// default to email  
if(!$email && !$fups){ $email = 1; $fups = ''; }
if($email){
 $eb = '_on'; // top buttons
 $tx = 'Email'; // titles
 $txu = 'email'; // varnames
} elseif($fups){
 $ef = '_on';
 $tx = 'Followups';
 $txu = 'fups';
}

$fdate = addslashes(dateparse(array($year,$month,$day),'form'));

if(($add || $edit) && $save ){
 // echo "(ADD||EDIT)&&SAVE ";
 if(!$demo){
  if($fups){
   $typ = 'f';
   $txtsubj = $fsetid;
   $txtcont = ''; $txthtcont = ''; $txtfatt = '';
  } elseif($email) $typ = 'm'; else die('fup a|e&s error');
  
  if($edit){
   $cmd = "update $qtable set type = '$typ', list = '".addslashes($tolist)."', date = '".addslashes($fdate)."', subject = '".addslashes($txtsubj)."', message = '".addslashes($txtcont)."', htmessage = '".addslashes($txthtcont)."', fattach = '".addslashes($txtfatt)."' where id = '".addslashes($saveid)."'";
   mysql_query($cmd);
   $editid = $saveid;
   $logtxt = 'Scheduled message saved.  Your message will be sent by dailymail.';
  }

  if($add){
   $cmd = "insert into $qtable (type,date,list,subject,message,htmessage,fattach) values('$typ','".addslashes($fdate)."','".addslashes($tolist)."','".addslashes($txtsubj)."','".addslashes($txtcont)."','".addslashes($txthtcont)."','".addslashes($txtfatt)."');";
   // echo "CMD=$cmd<Br>";
   mysql_query($cmd);
   $editid = mysql_insert_id();
   $add = '';
   $edit = 1;
   if($email) $logtxt = "Added scheduled message.";
   elseif($fups){
    $logtxt = "Added followup schedule.";
    header("Location: ./scheduler.php?list=$list&fups=1&xl=$tolist&xi=$fsetid&xd=$fdate");
   }
  }
 } else {
  if($add){
   $logtxt = 'Cannot add in demo.';
  }
  if($edit) $logtxt = 'Cannot save in demo.';
 }
}

if($del){
 @mysql_query("delete from $qtable where id = '$delid'");
 if($email) $logtxt = 'Scheduled message deleted.';
 elseif($fups) $logtxt = 'Followup schedule deleted.';
}

if($add || $edit){
 if($email){
  if($edit && !$ql_save && !$ql_del && !$ql_load && !$preview){
   // echo "LOADING DATA ";
   // get editid
   // echo "editid=$editid ";
   $qrows = mysql_query("select date,list,subject,message,htmessage,fattach from $qtable where id = '$editid'");
   list($fdate,$tolist,$txtsubj,$txtcont,$txthtcont,$txtfatt)=mysql_fetch_row($qrows);
   list($year,$month,$day)=dateparse($fdate,'break');
  }
  $edit = 1;
  $sendwhen = 'date';
  include('./editmsg.php');
  editmessage('scheduler');
  exit;
 }elseif($fups){
  // followup schedule add/edit page
  adminheader('LM: Schedule '.$tx,'New Followup Schedule','');
  echo "<script language=javascript><!--
 function checkyear(year) {
  return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
 }
--></script>
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
  listmenu($list,'scheduler');
  echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>\n";

  // top buttons
  echo "<input class=button$eb type=button value=\"Email\" onclick=\"hopto('./scheduler.php?list=$list&email=1');\" style=\"width: 90px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$eb'\"><img src=1.gif width=3><input class=button$ef type=button value=\"Followups\" onclick=\"hopto('./scheduler.php?list=$list&fups=1');\" style=\"width: 100px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$ef'\"><br>
<img src=1.gif height=3><br>\n";

  echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr class=table_head_bg_nopad>
  <td>
   <span class=table_head_text>Scheduled $tx</span><br>
  </td>
  <td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('scheduler')."');\">Help</a><br></td>
 </tr>
</table>
<img src=1.gif height=5><br>
<span class=table_inside_normal>This feature allows you to schedule application of a <u>saved followup set</u> to a list.  Before applying the saved followup set, <u>all followups on the target list are removed</u>.  That means if you have not saved the current followups into a new set of their own, <b>they will be lost</b>!  All users are reset to the new lowest seq # along with its delay.  A delay of 0 on your first followup will result in an email happening <u>that day</u>. It is up to you to determine whether or not enough time has passed to complete your current sequence.<br>
<img src=1.gif height=10><br>
<span class=table_inside_small>Date to Apply<br>";
  if(!$m || !$d || !$y) list($m,$d,$y)=explode('-',date("m-d-Y"));
  $ty = date("Y");
  $eid = ''; // ignore this
  echo "   <table border=0 cellspacing=0 cellpadding=0>
    <tr bgcolor=white>
     <td><form name=edit_frm method=post action=".$_SERVER['PHP_SELF'].">
	<input type=hidden name=list value=$list>
	<input type=hidden name=fups value=1>
	<input type=hidden name=save value=\"\">
	<input type=hidden name=add value=\"\">
	<input type=hidden name=edit value=\"\">
     <script language=javascript> dx$eid = '$d'; </script><select class=xbox name=month onchange=\"mv = edit_frm$eid.month.value; if(mv=='2') { leapyear = checkyear(edit_frm$eid.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm$eid.day.options.length-1; i>=0; i--) { if(i>27) edit_frm$eid.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm$eid.day.options[i] = new Option(i+1,i+1); } if(edit_frm$eid.day.options[dx$eid-1]) edit_frm$eid.day.options[dx$eid-1].selected=true; else { edit_frm$eid.day.options[ndays-1].selected=true; dx$eid=ndays; } \" style=\"width: 88px\">";
  for($j=1;$j<=12;$j++){
   $eidtxt = date("F",mktime(0,0,0,$j,1,2002));
   if($j==$m) $sel = ' selected'; else $sel = '';
   echo "<option value=$j".$sel.">$eidtxt";
  }
  echo "</select><img src=1.gif width=5><select class=xbox name=day onchange=\"dx$eid = edit_frm$eid.day.selectedIndex+1;\" style=\"width: 40px\">";
  for($j=1;$j<=date("t",mktime(0,0,0,$m,$d,$y));$j++){
   if($j==$d) $sel = ' selected'; else $sel = '';
   echo "<option value=$j".$sel.">$j";
  }
  echo "</select><img src=1.gif width=5><select class=xbox name=year onchange=\"mv = edit_frm$eid.month.value; if(mv=='2') { leapyear = checkyear(edit_frm$eid.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm$eid.day.options.length-1; i>=0; i--) { if(i>27) edit_frm$eid.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm$eid.day.options[i] = new Option(i+1,i+1); } if(edit_frm$eid.day.options[dx$eid-1]) edit_frm$eid.day.options[dx$eid-1].selected=true; else { edit_frm$eid.day.options[ndays-1].selected=true; dx$eid=ndays; } \" style=\"width: 54px\">";
  for ($j=0;$j<=4;$j++) {
   if($ty==$y) $sel = ' selected'; else $sel = '';
   echo "<option value=$ty".$sel.">$ty";
   $ty++;
  }
  echo "</select></td></tr></table>
<img src=1.gif height=10><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td><span class=table_inside_small>Followup Set<br>
<select size=10 class=xbox name=fsetid style=\"width: 210px; vertical-align: bottom\">";
$srows = mysql_query("select id,name from $fstable where 1 order by id;");
if(@mysql_num_rows($srows)>0){
 while(list($i,$n)=mysql_fetch_row($srows)){
  echo "<option value=\"$i\">".htmlspecialchars($n);
 }
} else {
 echo "<option value=\"\">None";
}
echo "</select><br></span>
  </td>
  <td width=10><img src=1.gif width=10><br></td>
  <td><span class=table_inside_small>
   Target List<br>
   <select size=10 class=xbox name=tolist style=\"width:210px\">";
   $lrows = @mysql_query("select listnum,title from $ltable where 1 order by listnum");
   while(list($listnum,$ltitle) = @mysql_fetch_row($lrows)){
    if($tolist==$listnum) $sel = " selected"; else $sel = "";
    echo "<option value=\"".$listnum."\"".$sel.">$listnum: ".htmlspecialchars($ltitle);
   }
   echo "</select><br></td>
 </tr>
</table>
<img src=1.gif height=5><br>
<input type=button class=button value=\"Save / Schedule\" onclick=\"if(!edit_frm.fsetid.value || !edit_frm.tolist.value){ alert('You must choose a followup set and a target list.'); return false; } edit_frm.save.value='1'; edit_frm.edit.value=''; edit_frm.add.value=1; edit_frm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 114px\"><img src=1.gif width=3><input type=button class=button value=\"Cancel / Back\" onclick=\"hopto('./scheduler.php?list=$list&fups=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"><br>
</form>
</span>";
   
  echo "
  </td>
 </tr>
</table>\n";
exit;
 } // fupedit&add
}

// end add / edit

// quicksave
if($qsave){
 $fdate = dateparse(array($year,$month,$day),'form');
 if($email){
  // check values, quick save.
  // check date?
  if($subject){
   $cmd = "update $qtable set list = '".addslashes($mailto)."', date = '".addslashes($fdate)."', subject = '".addslashes($subject)."' where id = '".addslashes($saveid)."'";
   mysql_query($cmd);
   $logtxt = "Message "; if($subject) $logtxt .= "\"".htmlspecialchars($subject)."\" "; $logtxt .= "saved.";
  } else {
   $logtxt = "No subject, not saved!";
  }
 } elseif($fups){
  // followup schedule quicksave, same as email but with 'fsetid' instead of subject.
  $cmd = "update $qtable set list = '".addslashes($mailto)."', date = '".addslashes($fdate)."', subject = '".addslashes($fsetid)."' where id = '".addslashes($saveid)."'";
  mysql_query($cmd);
  $logtxt = "Followup schedule saved.";
 }
}

// main page
adminheader('LM: Schedule '.$tx,'Scheduled '.$tx,'');
echo "<script language=javascript><!--
 function checkyear(year) {
  return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
 }
--></script>
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
listmenu($list,'scheduler');
echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>\n";


// top buttons
echo "<input class=button$eb type=button value=\"Email\" onclick=\"hopto('./scheduler.php?list=$list&email=1');\" style=\"width: 90px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$eb'\"><img src=1.gif width=3><input class=button$ef type=button value=\"Followups\" onclick=\"hopto('./scheduler.php?list=$list&fups=1');\" style=\"width: 100px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$ef'\"><br>
<img src=1.gif height=3><br>\n";

if($email){
 $cmd = "select id,list,date,subject from $qtable where type = 'm' order by date asc";
 $erows = mysql_query($cmd);
} elseif($fups){
 $cmd = "select id,list,date,subject from $qtable where type = 'f' order by date asc";
 $erows = mysql_query($cmd);
}

if(@mysql_num_rows($erows)==0){
  echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr class=table_head_bg_nopad>
  <td>
   <span class=table_head_text>Scheduled $tx</span><br>
  </td>
  <td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('scheduler')."');\">Help</a><br></td>
 </tr>
</table>
<img src=1.gif height=5><br><center><span class=status_text>"; if($email){ echo "There are no scheduled messages."; } elseif($fups){ echo "No followup sets are scheduled to be applied."; } echo "</span></center>";
} else {
 // listing
 echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr class=table_head_bg>
     <td>
      <span class=table_head_text>Scheduled $tx</span><br>
     </td>
     <td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('scheduler')."');\">Help</a><br></td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td bgcolor=white>";
  
 if($email || $fups){
  if($email){
   $dts = 'Date to Send';
   $los = 'List or Selection';
   $es = 'Email Subject';
  } else {
   $dts = 'Date to Apply';
   $los = 'Target List';
   $es = 'Followup Set';
  }
  // email listing
  echo "   <table width=100% border=0 cellspacing=0 cellpadding=3>
    <tr class=bg2>
     <td><span class=table_head_col>$dts<br></span></td>
     <td><span class=table_head_col>$los<br></span></td>
     <td><span class=table_head_col>$es<br></span></td>
     <td><img src=1.gif height=1></td>
    "; if($email) echo " <td><img src=1.gif height=1></td>"; echo "
     <td><img src=1.gif height=1></td>
    </tr>";
  while(list($eid,$tolist,$fdate,$txtsubj)=mysql_fetch_row($erows)){
   $ty = date("Y");
   list($y,$m,$d)=dateparse($fdate,'break');
   echo "    <tr bgcolor=white>
     <td nowrap><form name=edit_frm$eid method=post action=".$_SERVER['PHP_SELF'].">
      <script language=javascript> dx$eid = '$d'; </script><select class=xbox name=month onchange=\"mv = edit_frm$eid.month.value; if(mv=='2') { leapyear = checkyear(edit_frm$eid.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm$eid.day.options.length-1; i>=0; i--) { if(i>27) edit_frm$eid.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm$eid.day.options[i] = new Option(i+1,i+1); } if(edit_frm$eid.day.options[dx$eid-1]) edit_frm$eid.day.options[dx$eid-1].selected=true; else { edit_frm$eid.day.options[ndays-1].selected=true; dx$eid=ndays; } \" style=\"width: 52px\">";
   for($j=1;$j<=12;$j++){
    $eidtxt = date("M",mktime(0,0,0,$j,1,2002));
    if($j==$m) $sel = ' selected'; else $sel = '';
    echo "<option value=$j".$sel.">$eidtxt";
   }
   echo "</select><img src=1.gif width=5><select class=xbox name=day onchange=\"dx$eid = edit_frm$eid.day.selectedIndex+1;\" style=\"width: 40px\">";
   for($j=1;$j<=date("t",mktime(0,0,0,$m,$d,$y));$j++){
    if($j==$d) $sel = ' selected'; else $sel = '';
    echo "<option value=$j".$sel.">$j";
   }
   echo "</select><img src=1.gif width=5><select class=xbox name=year onchange=\"mv = edit_frm$eid.month.value; if(mv=='2') { leapyear = checkyear(edit_frm$eid.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm$eid.day.options.length-1; i>=0; i--) { if(i>27) edit_frm$eid.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm$eid.day.options[i] = new Option(i+1,i+1); } if(edit_frm$eid.day.options[dx$eid-1]) edit_frm$eid.day.options[dx$eid-1].selected=true; else { edit_frm$eid.day.options[ndays-1].selected=true; dx$eid=ndays; } \" style=\"width: 54px\">";
   for ($j=0;$j<=4;$j++) {
    if($ty==$y) $sel = ' selected'; else $sel = '';
    echo "<option value=$ty".$sel.">$ty";
    $ty++;
   }
   if($email) $sz = '128'; else $sz = '176';
   echo "</select></td>
     <td><select class=xbox name=mailto style=\"width:".$sz."px\" onchange=\"edit_frm$eid.submit();\">";
     
   if($email) {
    $rrows = @mysql_query("select id,name from $setable where 1 order by id");
    if(@mysql_num_rows($rrows)>0){
     while(list($rid,$rname)=mysql_fetch_row($rrows)){
      if($tolist=='rs'.$rid) $sel = " selected"; else $sel = '';
      echo '<option value=rs'.$rid.$sel.'>'.$rname;
     }
    }
   }  
   $lrows = @mysql_query("select listnum,title from $ltable where 1 order by id");
   while(list($listnum,$ltitle) = @mysql_fetch_row($lrows)){
    if($tolist==$listnum) $sel = ' selected'; else $sel = '';
    echo "<option value=\"".$listnum."\"".$sel.">$listnum: ".htmlspecialchars($ltitle);
   }
   echo "</select></td>
     <td>";
   if($email) echo "<input type=text class=xbox name=subject value=\"".htmlspecialchars($txtsubj)."\" size=29 style=\"width: 192px\">";
   elseif($fups){
    echo "<select class=xbox name=fsetid style=\"width: 176px\">";
    $srows = mysql_query("select id,name from $fstable where 1 order by id;");
    if(@mysql_num_rows($srows)>0){
     while(list($i,$n)=mysql_fetch_row($srows)){
      if($txtsubj==$i) $sel = ' selected'; else $sel = '';
      echo "<option value=\"$i\"$sel>".htmlspecialchars($n);
     }
    } else {
     echo "<option value=\"\">None";
    }
    echo "</select>";
   }
   echo "</td>
     <td width=1><input type=hidden name=saveid value=$eid><input type=hidden name=qsave value=1><input type=hidden name=$txu value=1><input type=submit class=button value=\"Save\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\"></td></form>\n";
   if($email){
    echo "     <td width=1><input type=button class=button value=\"Edit\" onclick=\"hopto('./scheduler.php?list=$list&email=1&edit=1&editid=$eid');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\"></td>\n";
   }
   echo "     <td width=1><input type=button class=button value=\"Delete\" onclick=\"if(confirm('Are you sure you want to delete this "; if($email) echo "mailing"; elseif($fups) echo "followup schedule"; echo "?')) hopto('./scheduler.php?list=$list&$txu=1&del=1&delid=$eid');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\"></td>
    </tr>\n";
  }
  echo "   </table>
  </td>
 </tr>
</table>";
 } // email
} // numrows>0
echo "<img src=1.gif height=3><br>\n";

if($email) echo "<input type=button class=button value=\"Add Scheduled Email\" onclick=\"hopto('./scheduler.php?list=$list&add=1&email=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 144px\"><br>\n";
elseif($fups) echo "<input type=button class=button value=\"Add New Schedule\" onclick=\"hopto('./scheduler.php?list=$list&add=1&fups=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 140px\"><br>\n";

if($xi && $xl){
 // added a fup schedule
 // get name 
 list($n)=mysql_fetch_row(mysql_query("select name from $fstable where id = '".addslashes($xi)."';"));
 $logtxt .= "Followup schedule saved. &nbsp;Saved set <b>$n</b> will be applied to list <b>$xl</b> on <b>$xd</b>. ";
}
echo "
<img src=1.gif height=5><br>
<span class=status_text>$logtxt</span><br>";

echo "  </td>
 </tr>
</table>\n";
if(!is_integer(strpos($txtsubj,"'")) && !is_integer(strpos($logtxt,'"'))) echo "<script language=javascript>window.status=\"$logtxt\";</script>";

echo "</body>
</html>";

} // isadmin

?>
