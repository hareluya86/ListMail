<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

function editmessage($msgtype,$wclistid=''){
 // echo "BEGIN EDITMESSAGE<Br>";
 global $link;
 global $windows;

 // for loading scheduled mailings
 
 global $month;
 global $day;
 global $year;

 $m = $month;
 $d = $day;
 $y = $year; 
 
 if(!$m || !$d || !$y) list($m,$d,$y)=explode('-',date("m-d-Y"));
 
 // globals
 global $logtxt;
 global $focus;
 global $list;

 global $add;
 global $edit;
 global $editid;
 global $save;
 global $fid;
 global $udcheck;
 global $overwrite;
 global $createnew;
 global $ql_del;
 global $ql_load;
 global $ql_save;
 global $sendit;
 global $preview;
 global $tolist;
 global $datesent;
 if(!$tolist) $tolist = $list;
 global $sendwhen;
 global $txtseq;
 global $txtdel;
 global $txtcron;
// global $crdel;
 global $editwc;
 global $followup_set;
 global $loadedid;
 global $loadid;
 global $smtp_debug;
 
 // echo "list=$list "; echo "add=$add ";echo "edit=$edit "; echo "editid=$editid "; echo "save=$save "; echo "fid=$fid "; echo "udcheck=$udcheck "; echo "overwrite=$overwrite "; echo "createnew=$createnew "; echo "ql_del=$ql_del "; echo "ql_load=$ql_load "; echo "txtseq=$txtseq "; echo "txtdel=$txtdel "; echo "sendwhen=$sendwhen "; echo "tolist=$tolist "; echo "preview=$preview "; echo "sendit=$sendit "; echo "ql_save=$ql_save "; echo "loadid=$loadid ";

 // followups
 global $sets;
 global $sid;
 global $ftable;
 global $fstable;
 
 global $stable;
 global $utable;
 global $ltable;
 global $ctable;
 global $setable;
 
 
 // incoming data ?
/* global $xlist;
 global $xsubj;
 global $xmsg;
 global $xhtmsg;
 global $xfatt;
 global $xseq;
 global $xdel; */
 
 // form data used in overwrite n save ?
 global $txtsubj;
 global $txtcont;
 global $txthtcont;
 global $txtfatt;
  
 // send preview message
 if($preview){
  include('./mimeclass.php');
  $crow = mysql_query("select admmail,errchk,erraddr,ver,listmailpath from $ctable where 1",$link);
  list($admmail,$errchk,$errmail,$ver,$lmpath)=mysql_fetch_row($crow);
  $xmails = explode(';',$admmail);
  $admmail = $xmails[0];
  if($errchk<>'1' && $errchk<>'2') $errmail = $admmail;
  list($xsubj,$xmsg,$xhtmsg)=processmsg('preview',$txtsubj,$txtcont,$txthtcont);
  // echo "subj=$xsubj<br>msg=$xmsg<br>";
  $mail = new html_mime_mail();
  if($xhtmsg) $mail->add_html($xhtmsg, $xmsg, '');
  if($xmsg && !$xhtmsg) $mail->add_text($xmsg);
  if($fatt){
   $xfatt = explode(',',$fatt);
   while(list($key2,$filen) = each($txtfatt)){
    setfiletype($filen);
    global $atype;
    global $etype;
    if(!$atype) $atype = 'application/binary';
    $result = file_exists("./attach/" . $filen);
    if($result==true){
     $attachment = $mail->get_file("./attach/$filen");
     $mail->add_attachment($attachment, $filen, $atype, $etype);
    }
   }
  }
  $dcharset = "iso-8859-1";
  if($charset) $bchar = $charset; else $bchar = $dcharset;
  $build_params = array();
  $build_params['html_charset'] = $bchar;
  $build_params['text_charset'] = $bchar;
  $mail->build_message($build_params);
  $body = $mail->output;
  $headers = $mail->headers;
  $header = '';
  $crlf = "\n";
  $header .="From: \"ListMail Preview\" <preview@".getdomain().">$crlf";
  // $header .= "To: $email$crlf";
  $header .= "Return-Path: $errmail$crlf";
  $header .= "MIME-Version: 1.0$crlf";
  while(list($key2,$data) = each($headers)){
   $header .= $data.$crlf;
  }
  $header .="X-Mailer: ListMail v$ver";
  if($xhtmsg || $fatt){
   $header .= $crlf."This is a multipart message in MIME format.";
  }
  $body = str_replace("\r\n","\n",$body);
  if($windows){ $header .= "\nTo: \"ListMail Admin\" <$admmail>"; $email = $admmail; } else $email = "\"ListMail Admin\" <$admmail>";
  if($phpmailf){
   mail($email,$xsubj,$body,$header,"-f$errmail");
  } else {
   mail($email,$xsubj,$body,$header);
  }
  $logtxt .= 'Sent preview to admin at '.$admmail;
 }

 // page display - setup variables for different types of messages
 switch($msgtype){
  case 'welcome':
   $welcnf = 'w';
   $admin_title = "List $list Welcome Email";
   $admin_page = 'lists_w';
   $cancpage = "window.location='./editlists.php?list=$list';";
   $help_page = 'list_settings';
   $back_but = 'Back to List';
  break;

  case 'confirm':
   $welcnf = 'c';
   $admin_title = "List $list Confirm Email";
   $admin_page = 'lists_c';
   $cancpage = "window.location='./editlists.php?list=$list';";
   $help_page = 'list_settings';
   $back_but = 'Back to List';
  break;

  case 'remind':
   $welcnf = 'r';
   $admin_title = "List $list Reminder Email";
   $admin_page = 'lists_r';
   $cancpage = "window.location='./editlists.php?list=$list';";
   $help_page = 'list_settings';
   $back_but = 'Back to List';
  break;
  
  case 'domail':
   $domail = 1;
   $admin_title = 'Send Email';
   $admin_titlebar = 'LM: Send Email';
   $admin_page = 'domail';
   // $cancpage = "domail.php?list=$list&edit=1";
   $help_page = 'send_email';
   
  break;
  case 'scheduler':
   $scheduler = 1;
   $nonow = 1;
   if($edit) $admin_title = 'Edit Scheduled Email';
   if($add) $admin_title = 'New Scheduled Email';
   $admin_titlebar = 'LM: Scheduler';
   $admin_page = 'schedadd';
   $help_page = 'scheduler';
   if($add){
    $cancpage = "history.back();";
    $back_but = 'Cancel / Back';
    $back_but_size = '100';
    $save_but = 'Save / Schedule!';
    $save_but_size = '114';
   } elseif($edit){
    $cancpage = "window.location='scheduler.php?list=$list&email=1';";
    $back_but = 'Back to Main';
    $back_but_size = '90';
    $save_but = 'Save & Schedule!';
    $save_but_size = '114';   }
  break;

  case 'followup':
   $followup = 1;
   if($edit) $admin_title = 'Edit Followup';
    elseif($add) $admin_title = 'Add Followup';
   $admin_titlebar = 'LM: Followups';
   $admin_page = 'fups';
   $help_page = 'followups';
   if($add){
    $cancpage = "window.location='editfups.php?list=$list&sets=$sets&sid=$sid';";
    $back_but = 'Cancel / Back';
    $back_but_size = '100';
    $save_but = 'Save / Add Followup';
    $save_but_size = '145';
   } elseif($edit){
    $cancpage = "window.location='editfups.php?list=$list&sets=$sets&sid=$sid';";
    $back_but = 'Back to Main';
    $back_but_size = '90';
    $save_but = 'Save Followup';
    $save_but_size = '100';
   }
 // echo "sets=$sets sid=$sid<br>";
  break;
  
  case 'sentmsg':
   $edit = 1;
   $sentmsg = 1;
   $admin_title = 'View Sent Message';
   $admin_titlebar = 'LM: Sent Messages';
   $admin_page = 'sentmsgs';
   $help_page = 'sent_messages';
   $cancpage = "window.location='sentmsgs.php?list=$list';";
   $back_but = 'Back to Main';
   $back_but_size = '110';
   $save_but = 'N/A (Save)';
   $save_but_size = '100';
  break;
 }
 if($msgtype=='sentmsg') $msg_head = 'Sent '.$datesent; else $msg_head = 'Message Composition';
 
 if($ql_del){
  $cmd = "delete from $stable where id = '$loadid'";
  mysql_query($cmd);
  if($sqldebug) echo "CMD=<b>$cmd</b><br>";
  $logtxt .= "Saved message deleted. ";
  $loadid = $loadedid;
  $edit = 1;
 }


 if($ql_save){

  if(($loadedid && ($overwrite || $createnew)) || !$loadedid){
   // echo "(loadedid && (overwrite || createnew)) || !loadedid<br>";
  
  if($overwrite){
   // echo "OVERWRITE ";
   $cmd = "update $stable set subject = '".addslashes($txtsubj)."', content = '".addslashes($txtcont)."', htcontent = '".addslashes($txthtcont)."', fattach = '".addslashes($txtfatt)."' where id = '$loadedid'";
   // echo "CMD=$cmd <br>";
   mysql_query($cmd);
   if($sqldebug) echo "CMD=<b>$cmd</b><br>";
   $logtxt .= "Message: \"$txtsubj\" re-saved. ";
   $loadid = $loadedid;
  }

  if($createnew || !$loadedid){
   $svnew = 1;
   $cmd = "insert into $stable values('','s','','".addslashes($txtsubj)."','".addslashes($txtcont)."','".addslashes($txthtcont)."','".addslashes($txtfatt)."');";
   mysql_query($cmd);
   // echo "CMD=$cmd <br>";
   if($sqldebug) echo "CMD=<b>$cmd</b><br>";
   $logtxt .=  "New Message: \"$txtsubj\" saved. ";
   $loadid = mysql_insert_id();
  }
   if($sendwhen=='date') $logtxt .= '<br>Note: You should also Send / Schedule. ';

 }

} // ql save

 
if($loadid && !$ql_del && !$ql_save && !$preview){
 // echo "loadid<br>";
 $cmd = "select subject,content,htcontent,fattach from $stable where id = '$loadid'";
 // echo "loadid - CMD running = $cmd<br>";
 $loadrow = mysql_query($cmd);
 if($sqldebug) echo "CMD=<b>$cmd</b><br>";
 list($txtsubj,$txtcont,$txthtcont,$txtfatt) = mysql_fetch_row($loadrow);
 if(!$overwrite && !$svnew && !$preview) $logtxt .= "Message: \"$txtsubj\" loaded. ";
 $edit = 1;
}

 if ($edit && !$sendit) {

 // send an email to list form page
 // count users
/* if($list=='all') $xx = "1"; else $xx = "list = '$lnu'";

 $cmd = "select id from $utable where $xx and cnf = '1'";
 $result = mysql_query($cmd);
 if($sqldebug) echo "CMD=<b>$cmd</b><br>";
 $nmem = @mysql_num_rows($result); */

 // display email editing page
 adminheader($admin_titlebar,$admin_title,$admin_page);

 echo "

<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,$admin_page);
echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>

<script language=javascript><!--
 function checkyear(year) {
  return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
 }
 dx = '$d';
--></script>

<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td valign=top>
<!-- message composition -->
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>
   <form name=form1 method=post>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg_nopad><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>$msg_head</span><br></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink($help_page)."');\">Help</a><br></td></tr></table></td>
    </tr>
    <tr>
     <td height=1 bgcolor=black><img src=1.gif height=1></td>
    </tr>
    <tr>
     <td class=bg2>\n";

if(!$welcnf && !$sentmsg){
 if($sets) $targ = 'Followup Set';
 else $targ = 'Target List';
 echo "<img src=1.gif height=3><br><table border=0 cellspacing=0 cellpadding=0 width=100%><tr><td width=50 nowrap><table border=0 cellspacing=0 cellpadding=0><tr><td nowrap><img src=1.gif width=3><span class=table_inside_small>$targ"; if(!$followup) echo " or Selection"; echo ":</span></td></tr></table><select class=xbox name=tolist style=\"width: 229px; margin-left: 3px\"><!-- <option value=\"0\">All Lists -->";

 if($domail || $scheduler){
  $rrows =  @mysql_query("select id,name from $setable where 1 order by id");
  if(@mysql_num_rows($rrows)>0){
   while(list($rid,$rname)=mysql_fetch_row($rrows)){
   if($tolist=='rs'.$rid) $sel = " selected"; else $sel = "";
    echo '<option value=rs'.$rid.$sel.'>'.$rname;
   }
  }
 } 

 if(!$sets){ 
  $lrows = @mysql_query("select listnum,title from $ltable where 1 order by listnum");
  while(list($listnum,$title) = @mysql_fetch_row($lrows)){
   if($tolist==$listnum) $sel = " selected"; else $sel = "";
   if($followup){
    if($listnum == $list) echo '<option value='.$listnum.$sel.">$listnum: $title";
   }
   else echo '<option value='.$listnum.$sel.">$listnum: $title";
  }
  if($sendwhen=='now') $now_sel = ' selected'; else $now_sel = '';
  if($sendwhen=='date') $dat_sel = ' selected'; else $dat_sel = '';

 } elseif($sets){
  $fsrows = @mysql_query("select id,name from $fstable where id = '".addslashes($sid)."';");
  if(@mysql_num_rows($fsrows)>0){
   list($fsid,$fsname)=mysql_fetch_row($fsrows);
   if($sid==$fsid) $sel = " selected"; else $sel = "";
   echo '<option value=set'.$fsid.$sel.'>'.$fsname;
  }
 }


 echo "</select></td><td width=8><img src=1.gif width=8></td><td nowrap>\n";
 
 if($domail || $scheduler){
  echo "<span id=sw><span class=table_inside_small>Schedule:</span><br><table border=0 cellspacing=0 cellpadding=0><tr><td nowrap><select class=xbox name=sendwhen style=\"width: 52px\" onchange=\"if(this.value=='now'){  
 document.getElementById('showdate').style.visibility='hidden'; showdate.style.display='none'; } if(this.value=='date'){ document.getElementById('showdate').style.visibility='visible'; showdate.style.display='inline'; } return true;\">\n";
  if(!$nonow) echo "<option value=now$now_sel>Now"; echo "<option value=date$dat_sel>Date</select></span>";

  // date select
  $ty = date("Y");
  echo "<span id=showdate><img src=1.gif width=5><select style=\"width: 88px\" class=xbox name=month onchange=\"mv = form1.month.value; if(mv=='2') { leapyear = checkyear(form1.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=form1.day.options.length-1; i>=0; i--) { if(i>27) form1.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { form1.day.options[i] = new Option(i+1,i+1); } if(form1.day.options[dx-1]) form1.day.options[dx-1].selected=true; else { form1.day.options[ndays-1].selected=true; dx=ndays; } \">";
  for($i=1;$i<=12;$i++){
   $itxt = date("F",mktime(0,0,0,$i,1,2002));
   if($i==$m) $sel = ' selected'; else $sel = '';
   echo "<option value=$i".$sel.">$itxt";
  }
  echo "</select><img src=1.gif width=5><select style=\"width: 41px\" class=xbox name=day onchange=\"dx = form1.day.selectedIndex+1;\">";
  for($i=1;$i<=date("t",mktime(0,0,0,$m,$d,$y));$i++){
   if($i==$d) $sel = ' selected'; else $sel = '';
   echo "<option value=$i".$sel.">$i";
  }
  echo "</select><img src=1.gif width=5><select style=\"width: 55px\"  class=xbox name=year onchange=\"mv = form1.month.value; if(mv=='2') { leapyear = checkyear(form1.year.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=form1.day.options.length-1; i>=0; i--) { if(i>27) form1.day.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { form1.day.options[i] = new Option(i+1,i+1); } if(form1.day.options[dx-1]) form1.day.options[dx-1].selected=true; else { form1.day.options[ndays-1].selected=true; dx=ndays; } \">";
  for ($i=0;$i<=4;$i++) {
   if($ty==$y) $sel = ' selected'; else $sel = '';
   echo "<option value=$ty".$sel.">$ty";
   $ty++;
  }
  echo "</select><img src=1.gif width=2><br></span></td></tr></table>\n";

  echo "<script language=javascript> <!--
if(document.form1.sendwhen.value=='now') { document.getElementById('showdate').style.visibility='hidden'; showdate.style.display='none'; }
--> </script>";

/* if($msgtype=='followup'){
  echo "<script language=javascript> <!--
document.getElementById('sw').style.visibility='hidden'; sw.style.display='none';
--> </script>";
 }
*/
}
elseif($followup){
 // record seq #s for dupe checking
 if($sets) $zcmd = "sset = '".addslashes($sid)."'";
 else $zcmd = "list = '".addslashes($list)."'";
 $fstr = '';
 $cmd = "select id,seq from $ftable where $zcmd group by seq";
 $frows = mysql_query($cmd);
 if(@mysql_num_rows($frows)>0){
  $first = true;
  while(list($fi,$s)=mysql_fetch_row($frows)){
   if(!$add && $fi == $fid){
   } else {
    if(!$first) $fstr .= ';';
    $fstr .= $s;
    $first = false;
   }
  }
 }
 echo "<script language=javascript><!--
 var fstr='$fstr';
 if(fstr){
  var fupids=fstr.split(';');
 } else {
  var fupids=false;
 }
--></script>";

 if($txtcron){ $show = 'cron'; $hide = 'del'; } else { $show = 'del'; $hide = 'cron'; }
 echo "<script language=javascript><!--
function crondel(v)
{
 if(v=='cron'){
  document.getElementById('k_cron').style.visibility='hidden'; k_cron.style.display='none';
  document.getElementById('n_cron').style.visibility='visible'; n_cron.style.display='inline';
  document.getElementById('n_del').style.visibility='hidden'; n_del.style.display='none';
  document.getElementById('k_del').style.visibility='visible'; k_del.style.display='inline';
  document.getElementById('s_cron').style.visibility='visible'; s_cron.style.display='inline';
  if(loaded){ form1.txtcron.focus(); form1.crdel.value='cron'; if(form1.txtcron.value==''){ form1.txtcron.value='* * *'; } }
 }
 if(v=='del'){
  document.getElementById('k_del').style.visibility='hidden'; k_del.style.display='none';
  document.getElementById('n_del').style.visibility='visible'; n_del.style.display='inline';
  document.getElementById('n_cron').style.visibility='hidden'; n_cron.style.display='none';
  document.getElementById('k_cron').style.visibility='visible'; k_cron.style.display='inline';
  document.getElementById('s_cron').style.visibility='hidden'; s_cron.style.display='none';
  if(loaded){ form1.txtdel.focus(); form1.crdel.value='del'; }
 }
}
--></script>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td><span class=table_inside_small>Seq #:</span><br></td>
  <td width=10><img src=1.gif width=10></td>
  <td><span class=table_inside_small><span id=k_del><a class=tis href=\"javascript:void(0)\" onclick=\"crondel('del')\">Delay</a></span><span id=n_del>Delay</span> / <span id=k_cron><a class=tis href=\"javascript:void(0)\" onclick=\"crondel('cron');\">Cron</a></span><span id=n_cron>Cron</span>:</span><br></td> 
 </tr>
 <tr>
  <td><input type=hidden name=oseq value=\"".htmlspecialchars($txtseq)."\"><input class=xbox type=text size=2 name=txtseq value=\"".htmlspecialchars($txtseq)."\"  style=\"width: 35px\"><br></td>
  <td width=10><img src=1.gif width=10></td>
  <td><input class=xbox type=text size=2 name=txtdel value=\"".htmlspecialchars($txtdel)."\" style=\"width: 35px\"><span class=table_inside_small style=\"width: 20px\">+1d</span><img src=1.gif width=5><span id=s_cron><input class=xbox type=text size=34 style=\"width: 143px\" name=txtcron value=\"".htmlspecialchars($txtcron)."\"></span><br><script language=javascript><!--\ncrondel('$show'); var loaded=1;\n--></script></td>
 </tr>
</table>\n";
}
echo " </td></tr></table>";

} // end !welcnf
echo "
 <img src=1.gif height=1><br>
      <!-- padding --><table width=100% border=0 cellspacing=0 cellpadding=3><tr><td>
      <table width=100% border=0 cellspacing=0 cellpadding=0>
       <tr>
        <td>
         <span class=table_inside_small>Subject:</span><br>\n";
if(!$sentmsg) echo "        <td valign=top align=right>
        <img src=1.gif height=2><br>
        <a href=# class=hml onclick=\"popitup('popup.php?list=$list&p=message_codes','','400','410');\"><img src=help.gif border=0 height=10> View Message Codes</small></a><br>\n";
        
echo "        </td>
       </tr>
       <tr>
        <td colspan=2>
                 <input type=text class=xbox name=txtsubj size=85 style=\"width: 488px\" value=\"".htmlspecialchars($txtsubj)."\"><br>
        </td>
       </tr>
      </table>
      <span class=table_inside_small>Text Message:</span><br><textarea style=\"width: 488px\" class=xarea name=txtcont rows=8 cols=85>".htmlspecialchars($txtcont)."</textarea><br>
      <span class=table_inside_small>HTML Message: (blank for text-only)</span>&nbsp;&nbsp;<a href=# class=hml onclick=\"popitup('popup.php?list=$list&p=html_mail','','210','410');\"><img src=help.gif border=0 height=10></a><br>
      <textarea style=\"width: 488px\" class=xarea name=txthtcont rows=8 cols=85>".htmlspecialchars($txthtcont)."</textarea><br>

      <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=1><table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>File Attachments: (blank for none)</span>&nbsp;&nbsp;<a href=# class=hml onclick=\"popitup('popup.php?list=$list&p=fatt_mail','','310','410');\"><img src=help.gif border=0 height=10></a><br>
      <input type=text class=xbox name=txtfatt value=\"$txtfatt\" size=40><br></td><td valign=bottom><input type=button class=button name=justfatt onclick=\"popitup('upload.php?p=mail','','300','300');\" value=Add style=\"margin-bottom: 1px; margin-left: 4px; width: 30px\"  onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\"><br></td></tr></table>
      </td>
      <td width=10>
       <img src=1.gif width=10><br>
      </td>
      <td align=right valign=top>\n";
 list($smtpchk)=mysql_fetch_row(mysql_query("select mtchk from $ctable where 1"));
 list($smtpchk,$smtplog)=explode(';',$smtpchk);
 if($smtp_debug || $smtplog) $sd_chk = ' checked'; else $sd_chk = '';
 if($domail){
  if($smtpchk) echo "<input type=checkbox value=1 name=smtp_debug class=chkbox$sd_chk><span class=table_inside_small> Write SMTP Logfile </span><br>\n";
 }
 echo "      <img src=1.gif height=2><br>
      <table border=0 cellspacing=0 cellpadding=0>
       <tr>

        <td>\n";
if($followup && !$sets){
 echo "<img src=1.gif height=3><br><table border=0 cellspacing=0 cellpadding=0><tr><td valign=middle><input type=checkbox class=chkbox name=updatedel$udcheck></td><td><span class=table_inside_small style=\"margin-left: 5px\">Update all users on this<br><span style=\"margin-left: 6px\">followup to new delay</span></span><br></td><td width=2><img src=1.gif width=2></td></tr></table>\n";
}
if($welcnf=='c'){
 echo "<img src=1.gif height=3><br><table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small style=\"margin-left: 5px;\">* Be sure to use a message code of<br><span style=\"margin-left: 6px\">type <i>Confirmation Link</i> in your email!</span></span><br></td><td width=2><img src=1.gif width=2></td></tr></table>\n";
}
         
echo "         <input type=hidden name=loadedid value=$loadid>
         <input type=hidden name=doformid value=\"".date("YmdHis")."\">
         <input type=hidden name=list value=$list>
         <input type=hidden name=overwrite value=\"\">
         <input type=hidden name=createnew value=\"\">
         <input type=hidden name=sendit value=\"\">
         <input type=hidden name=ql_save value=\"\">
         <input type=hidden name=preview value=\"\">
         <input type=hidden name=add value=\"\">
         <input type=hidden name=save value=\"\">
         <input type=hidden name=saveid value=\"\">
         <input type=hidden name=fid value=\"$fid\">
         <input type=hidden name=edit value=\"\">
         <input type=hidden name=editwc value=\"\">
         <input type=hidden name=frearr value=\"1\">
         <input type=hidden name=normal value=\"\">
         <input type=hidden name=insert value=\"\">
         <input type=hidden name=makedupe value=\"\">\n";
if($followup) echo "         <input type=hidden name=crdel value=\"$show\">\n";
echo "        </td>
       </tr>
      </table>
      <!-- end padding --></td></tr></table>
      </td></tr></table>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>\n";

 echo "<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0 width=100%>
 <tr>\n";
 
  $admpreview = "  <td width=1>
   <input class=button type=button value=\"Preview\" onClick=\"if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; } if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; } form1.edit.value='$edit'; form1.add.value='$add'; form1.preview.value=1; form1.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 80px\">
  </td>\n
  <td width=3><img src=1.gif width=3></td>
";

 if($scheduler){
  echo "
  <td width=1>
   <input type=button class=button value=\"$save_but\" onclick=\"if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; } if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; } form1.saveid.value='$editid'; form1.edit.value='$edit'; form1.save.value=1; form1.add.value='$add'; form1.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$save_but_size."px\">
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=button class=button value=\"$back_but\" onclick=\"$cancpage\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$back_but_size."px\">
  </td>\n";
 } elseif($followup){
  $eos = getlisteos();
  echo "
  <td width=1>
   <input type=button class=button value=\"$save_but\" onclick=\"
if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; }
if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; }
if(isNaN(form1.txtseq.value) || !form1.txtseq.value){ alert('You must enter an integer for the Sequence #.'); return false; }
if(form1.txtseq.value<=0){ alert('The Sequence # must be greater than 0.'); return false; }
if(isNaN(form1.txtdel.value) || !form1.txtdel.value){ alert('You must enter an integer for the Delay #.'); return false; }
if(form1.txtdel.value<0){ alert('The Delay # must be 0 or greater.'); return false; } 
if(fupids && form1.oseq.value != form1.txtseq.value){
	for(i=0;i<fupids.length;i++){
		if(fupids[i]==form1.txtseq.value){
			if(confirm('You are saving to a Seq # that already exists.  Do you want to INSERT this followup BEFORE the target Seq (#'+form1.txtseq.value+')"; if(!$sets) echo " and automatically re-arrange affected users"; echo "?\\n\\nOK = Yes, insert followup into sequence"; if(!$sets) echo " and re-arrange users"; echo "\\nCancel = No, create a duplicate Seq #')){
				form1.insert.value='1';
			} else {
				form1.makedupe.value='1';
			}
		}
	}
	if(!form1.insert.value && !form1.makedupe.value){
		form1.normal.value='1';
	}
} else {
	form1.normal.value='1';
}
if(form1.txtseq.value > $eos){
 alert('You have specified a Seq # greater than the end of sequence (last followup + 1). Your followup will be placed at the end of sequence.');
}  
form1.edit.value='$edit';
form1.save.value=1;
form1.add.value='$add';
form1.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$save_but_size."px\">
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=button class=button value=\"$back_but\" onclick=\"$cancpage\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$back_but_size."px\">
  </td>\n";
  if(!$add){
   echo "  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=button class=button value=\"New Followup\" onclick=\"if(confirm('Discard unsaved changes and start a new followup?')){ hopto('./editfups.php?list=$list&sets=$sets&sid=$sid&add=1'); } else return false;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 96px\">
  </td>\n";

  }
 } elseif($domail){
  echo "
  <td width=1>
   <input class=button type=button value=\"Send this Message!\" onClick=\"if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; } if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; }
   if(form1.sendwhen.value=='now'){ 
    if (!confirm('Are you sure you want to send this message?\\nThis will send a live email to the list or selection!')) return false;
    form1.sendit.value=1;
    form1.action='./domail.php';
    form1.submit(); 
   }
   if(form1.sendwhen.value=='date'){ 
    if (!confirm('Your message will be entered into the Scheduler and sent by dailymail when it runs on the chosen day.\\nUntil sending time, you can modify the message from the Schedule Email page.')) return false;
    form1.add.value=1;
    form1.save.value=1;
    form1.action='./scheduler.php';
    form1.submit();
   }    
    \" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 140px\">\n";
 } elseif($welcnf){
  echo "
  <td width=1>
   <input class=button type=button value=\"Save Message\" onClick=\"if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; } if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; }
   form1.editwc.value='$welcnf';
   form1.save.value=1;
   form1.action='./editlists.php';
   form1.submit();
  \" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 110px\"><br>
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=button class=button value=\"$back_but\" onclick=\"$cancpage\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
  </td>\n";
 
 } elseif($sentmsg){
  echo "
  <td width=1>
   <input type=button class=button value=\"$back_but\" onclick=\"$cancpage\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$back_but_size."px\">
  </td>\n";
 }   
echo "\n  </td>
  <td>&nbsp;</td>
  $admpreview
  <td align=right width=1>
   <input class=button type=button value=\"Quick Save\" onclick=\"if(!form1.txtsubj.value){ alert('You must enter a subject.'); return false; } if(!form1.txtcont.value){ alert('You must enter a text message.'); return false; } if(form1.loadedid.value){ if(confirm('You are saving a loaded message.  Overwrite?\\n\\nPress OK to Overwrite.\\n\\nPress CANCEL to Save New.')){ form1.overwrite.value=1; } else { form1.createnew.value=1; }  } form1.edit.value='$edit'; form1.add.value='$add'; form1.ql_save.value=1; form1.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 80px\">

  </td>
 </tr>
</table>
</td>
<td width=10><img src=1.gif width=10></td>
<td align=center valign=top width=1>
<!-- quick load -->
<script language=javascript><!--
form1.txtsubj.focus();
origQLW = 122; newQLW = origQLW;
--> </script>
<table class=outer_table width=120 border=0 cellspacing=0 cellpadding=0>
<tr>
 <td class=table_head_bg_nopad><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Quick Load</span></td><td align=right nowrap><span id=minbut><a class=table_head_help href=# onclick=\"document.getElementById('minbut').style.visibility='hidden'; minbut.style.display='none'; newQLW=origQLW; form1.loadid.style.width=origQLW+'px';\">&#171;</a></span><script language=javascript> <!--
 document.getElementById('minbut').style.visibility='hidden'; minbut.style.display='none'; 
--> </script><a class=table_head_help href=# onclick=\"if(document.getElementById('minbut').style.visibility=='hidden' || minbut.style.display=='none'){ document.getElementById('minbut').style.visibility='visible'; minbut.style.display='inline'; } newQLW=newQLW+50; form1.loadid.style.width=newQLW+'px';\"><img src=1.gif width=2 border=0>&#187;</a><br></td></tr></table></td>
</tr>
<tr><td height=1 bgcolor=black><img src=1.gif height=1></td></tr>
<tr>
 <td class=bg2>
 <!-- quick load pad -->
 <table width=120 border=0 cellspacing=0 cellpadding=0><tr><td>
  <select name=loadid size=13 class=xbox style=\"width: 122px\">";

 $cmd = "select id,subject from $stable where kind = 's' order by id desc";
 $savrows = mysql_query($cmd);
 if($sqldebug) echo "CMD=<b>$cmd</b><br>";
 while(list($sid,$ssubj) = mysql_fetch_row($savrows)){
  echo "<option value=\"".htmlspecialchars($sid)."\">$ssubj</option>\n";
 }
 echo "
  </select><br>
  <input type=hidden name=list value=$list>
  <input type=hidden name=ql_load value=\"\">
  <input type=hidden name=ql_del value=\"\">
  <input type=hidden name=editwc value=\"$editwc\">
  <table border=0 cellspacing=0 cellpadding=2>
   <tr>
    <td>
     <table border=0 cellspacing=0 cellpadding=0>
      <tr>
       <td>
     <input name=qlload class=button type=submit value=Load onclick=\"if(!form1.loadid.value){ alert('You must select a message to load.'); return false; } form1.edit.value='$edit'; form1.add.value='$add'; form1.ql_load.value=1; \" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 58px\">";
 if($sentmsg) echo "<script>form1.qlload.disabled=true;</script>";
 echo "<br>
    </td>
    <td width=2><img src=1.gif width=2></td>
    <td>
     <input class=button type=submit name=delbut value=Delete onclick=\"if(!form1.loadid.value){ alert('You must select a message to delete.'); return false; } if(!confirm('Are you sure you want to delete this saved message?')) { return false; } form1.edit.value='$edit'; form1.add.value='$add'; form1.ql_del.value=1;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 58px\"><br>
       </td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
  </td></tr></table> <!-- end quick load -->
 </td>
</tr>
</table>
<script language=javascript>window.status='".addslashes(str_replace('<br>',' ',$logtxt))."';</script>
</form></td></tr></table>
\n";

if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text style=\"color: navy\">$logtxt</span><br>";
if($focus) echo $focus;

echo "
</td></tr></table>
</td></tr></table>
</body>
</html>";
} // end editmail  
      
}
?>