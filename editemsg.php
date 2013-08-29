<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$islogged = islogged();
if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

if($new || $copyx){
 if($new){
  $fromid = '1';
  $title = 'New Errors';
  $logtxt = 'Error message set created from Default.';
 } elseif($copyx){
  $fromid = addslashes($eid);
  $rows = mysql_query("select name from $etable where id = '".addslashes($eid)."';");
  list($n)=mysql_fetch_row($rows);
  $title = 'Copy of '.addslashes($n);
  $logtxt = 'Error message set copied.';
 }
 mysql_query("insert into $etable values('','$title');");
 $newid = mysql_insert_id();
 $rows = mysql_query("select typ,data from $edtable where id = '$fromid'");
 while(list($t,$d)=mysql_fetch_row($rows)){
  mysql_query("insert into $edtable values('$newid','$t','".addslashes($d)."');");
 }
 $eid = $newid;
}

if($del){
 $eid = addslashes($eid);
 mysql_query("delete from $etable where id = '$eid';");
 mysql_query("delete from $edtable where id = '$eid';");
 $eid = '';
 $logtxt = 'Error message set deleted.';
}

// save
if($save){
 $error='';
 $eid = addslashes($eid);
 $txtname = addslashes($txtname);
 $tfnamereq = addslashes($tfnamereq);
 $tlnamereq = addslashes($tlnamereq);
 $temailreq = addslashes($temailreq);
 $temailbad = addslashes($temailbad);
 $temaildupe = addslashes($temaildupe);
 $tremnf = addslashes($tremnf);
 $tclosed = addslashes($tclosed);
 

 if(!$demo && !$error){
  if($txtname) mysql_query("update $etable set name = '$txtname' where id = '$eid';");
  // update ALL error msgs at same time..
  mysql_query("update $edtable set data = '$tfnamereq' where id = '$eid' and typ = 'fname_req';");
  mysql_query("update $edtable set data = '$tlnamereq' where id = '$eid' and typ = 'lname_req';");
  mysql_query("update $edtable set data = '$temailreq' where id = '$eid' and typ = 'email_req';");
  mysql_query("update $edtable set data = '$temailbad' where id = '$eid' and typ = 'email_bad';");
  mysql_query("update $edtable set data = '$temaildupe' where id = '$eid' and typ = 'email_dupe';");
  mysql_query("update $edtable set data = '$tremnf' where id = '$eid' and typ = 'rem_nf';");
  mysql_query("update $edtable set data = '$tclosed' where id = '$eid' and typ = 'l_closed';");
  $logtxt = "Error messages saved.";
 } else {
  $logtxt = "Cannot save in demo.";
  if($error){
   $noreload = 1;
   $txtname = stripslashes($txtname);
   $tfnamereq = stripslashes($tfnamereq);
   $tlnamereq = stripslashes($tlnamereq);
   $temailreq = stripslashes($temailreq);
   $temailbad = stripslashes($temailbad);
   $temaildupe = stripslashes($temaildupe);
   $tremnf = stripslashes($tremnf);
   $tclosed = stripslashes($tclosed);
  }
 }
}

// defaults
if(!$eid) $eid = '1';

// main page
adminheader('LM: Error Messages','Error Messages','');
echo "

<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>\n";

listmenu($list,'emsgs');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

   <table border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <input type=button class=button value=\"Back to List\" style=\"width: 80px\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button value=\"Custom Fields\" style=\"width: 96px\" onclick=\"hopto('./editlists.php?list=$list&efields=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Custom HTML\" style=\"width: 92px\" onclick=\"hopto('./editchtml.php?cid=$cid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button_on type=button id=ch value=\"Errors\" style=\"width: 52px\" onclick=\"hopto('./editemsg.php?eid=$eid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=sc value=\"Signup Code\" style=\"width: 86px\"  onclick=\"hopto('./editlists.php?list=$list&getcode=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
   </tr>
  </table>
  <img src=1.gif height=3><br>
   <form name=emsgfrm method=post action=./editemsg.php onsubmit=\"if(xname.value) emsgfrm.txtname.value=xname.value;\">
<table width=630 class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Error Messages</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('error_msgs')."');\">Help</a><br></td></tr></table>
</td></tr></table>
<img src=1.gif height=2><br>
<span id=sel><select style=\"width: 250px\" class=xbox name=eid onchange=\"hopto('./editemsg.php?eid='+this.value+'&list=$list');\">";
// get all error messages
$rows = mysql_query("select id,name from $etable where 1 order by id");
while(list($id,$name)=mysql_fetch_row($rows)){
 if($id==$eid) $sel = ' selected'; else $sel = '';
 echo "<option value=$id$sel>$name";
}
echo "</select></span><span id=nam><input name=xname type=text class=xbox style=\"width: 199px\" size=12><img src=1.gif width=3><input type=button class=button value=\"Save\" onclick=\"emsgfrm.txtname.value=xname.value; emsgfrm.submit();\"><img src=1.gif width=2><input type=button class=button value=\"Cancel\" onclick=\"
 xname.value='';
 document.getElementById('sel').style.visibility='visible';
 document.getElementById('buttons').style.visibility='visible';
 document.getElementById('nam').style.visibility='hidden';
 sel.style.display='inline';
 buttons.style.display='inline';
 nam.style.display='none';

\"></span><script language=javascript>
 document.getElementById('nam').style.visibility='hidden';
 nam.style.display='none';
</script><span id=buttons>";
if($eid<>'1'){ echo "<img src=1.gif width=2><input type=button class=rbutton value=\"Rename\" onclick=\"
 document.getElementById('sel').style.visibility='hidden';
 document.getElementById('buttons').style.visibility='hidden';
 document.getElementById('nam').style.visibility='visible';
 sel.style.display='none';
 buttons.style.display='none';
 nam.style.display='inline';
 xname.focus();
\"><img src=1.gif width=2><input type=button class=rbutton value=\"Delete\" onclick=\"if(!confirm('Are you SURE you want to delete this entire error message template?\\n\\nWarning: All lists and links using this template need to be updated!')) return false; else hopto('./editemsg.php?list=$list&eid=$eid&del=1')\">"; }
echo "<img src=1.gif width=2><input type=button class=rbutton value=\" New\" onclick=\"hopto('./editemsg.php?list=$list&eid=$eid&new=1')\"><img src=1.gif width=2><input type=button class=rbutton value=\"Copy\" onclick=\"hopto('./editemsg.php?list=$list&eid=$eid&copyx=1')\"></span><br>
<img src=1.gif height=2><br>
<!-- <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td bgcolor=black height=1><img src=1.gif height=1><br></td></tr></table>
<img src=1.gif height=3><br> -->
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>\n";

list($tfnamereq) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'fname_req'"));
list($tlnamereq) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'lname_req'"));
list($temailreq) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'email_req'"));
list($temailbad) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'email_bad'"));
list($temaildupe) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'email_dupe'"));
list($tremnf) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'rem_nf'"));
list($tclosed) = @mysql_fetch_row(mysql_query("select data from $edtable where id = '$eid' and typ = 'l_closed'"));
   
echo "<table border=0 cellspacing=0 cellpadding=0><tr><td>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>
   <img src=1.gif height=2><br>
   <span class=table_inside_small>First Name Required (Signup Error)</span><br>
   <input type=text class=xbox size=85 name=tfnamereq value=\"".htmlspecialchars($tfnamereq)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>Last Name Required (Signup Error)</span><br>
   <input type=text class=xbox size=85 name=tlnamereq value=\"".htmlspecialchars($tlnamereq)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>Email Required (Signup Error & Unsub Error)</span><br>
   <input type=text class=xbox size=85 name=temailreq value=\"".htmlspecialchars($temailreq)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>Bad Email Format (Signup Error & Unsub Error)</span><br>
   <input type=text class=xbox size=85 name=temailbad value=\"".htmlspecialchars($temailbad)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>Duplicate Email (Signup Error)</span><br>
   <input type=text class=xbox size=85 name=temaildupe value=\"".htmlspecialchars($temaildupe)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>User Not Found (Unsub Error)</span><br>
   <input type=text class=xbox size=85 name=tremnf value=\"".htmlspecialchars($tremnf)."\"><br>
   <img src=1.gif height=4><br>
   <span class=table_inside_small>Closed List (Signup Error & Unsub Error)</span><br>
   <input type=text class=xbox size=85 name=tclosed value=\"".htmlspecialchars($tclosed)."\"><br>
   <img src=1.gif height=2><br>
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
     <input type=hidden name=save value=1>
     <input type=hidden name=eid value=$eid>
     <input type=hidden name=list value=$list>
     <input type=hidden name=txtname value=\"\">
     <input type=submit class=button value=\"Save Errors\"  onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">
     </td></form>

     <td width=3><img src=1.gif width=3></td>
     <td>
      <input type=button class=button value=\"Back to List\" onClick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
     </td>
    
    </tr>
   </table>\n";
if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text>$logtxt</span><br>";
echo "  </td>
 </tr>
</table>
</td></tr></table>
  </td>
 </tr>
</table>
</td></tr></table>\n";

} // isadmin

// EOF
?>