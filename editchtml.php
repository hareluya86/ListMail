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
  $title = 'New HTML';
  $logtxt = 'New Custom HTML set created from Default.';
 } elseif($copyx){
  $fromid = addslashes($cid);
  $rows = mysql_query("select name from $chtable where id = '".addslashes($cid)."';");
  list($n)=mysql_fetch_row($rows);
  $title = 'Copy of '.addslashes($n);
  $logtxt = 'Custom HTML set copied.';
 }
 mysql_query("insert into $chtable values('','$title');");
 $newid = mysql_insert_id();
 $rows = mysql_query("select typ,html from $chdtable where id = '$fromid'");
 while(list($t,$h)=mysql_fetch_row($rows)){
  mysql_query("insert into $chdtable values('$newid','$t','".addslashes($h)."','');");
 }
 $cid = $newid;
 $new = '';
}

if($del){
 $cid = addslashes($cid);
 mysql_query("delete from $chtable where id = '$cid';");
 mysql_query("delete from $chdtable where id = '$cid';");
 $cid = '';
 $logtxt = 'Custom HTML set deleted.';
}

// save
if($save){
 $error='';
 $cid = addslashes($cid);
 $txtname = addslashes($txtname);
 $txthtml = addslashes($txthtml);
 if($txturl) $txthtml = '';
 $pida = array('subscribe','suberror','remove','remerror','remsuccess','preconfirm');
 if(!in_array($page_id,$pida)) $error = 'Invalid page_id.  Stop hacking! :)';

 if(!$demo && !$error){
  if($txtname) mysql_query("update $chtable set name = '$txtname' where id = '$cid';");
  mysql_query("update $chdtable set html = '$txthtml', url = '$txturl' where id = '$cid' and typ = '$page_id';");
  $logtxt = "Custom HTML saved.";
 } else {
  $logtxt = "Cannot save in demo.";
  if($error){
   $noreload = 1;
   $txtname = stripslashes($txtname);
   $txthtml = stripslashes($txthtml);
   $txturl = stripslashes($txturl);
  }
 }
}

// reset
if($reset){
 if(!is_numeric($list) || !is_numeric($cid)) exit('invalid data');
 if($default){
  if($page_id=='preconfirm') $html = "<html>\r\n<head>\r\n<title>Confirmation Page</title>\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nBefore we add you to our database, we must verify your email address.  Please check your email for the confirmation message and click the link provided to finalize your subscription.  <br>\r\n\r\n</body>\r\n</html>";
  elseif($page_id=='subscribe') $html = "<html>\r\n<head>\r\n<title>Welcome to the list!</title>\r\n</head>\r\n<body bgcolor=white>\r\nThanks for signing up, !fname.<br>\r\nYour email address <u>!email</u> will be kept safe!<br><br>You will receive an introductory email in a moment.<br>\r\n</body>\r\n</html>";
  elseif($page_id=='suberror') $html = "<html>\r\n<head>\r\n<title>Signup Error</title>\r\n</head>\r\n<body bgcolor=white>\r\nThere were errors with your subscription to our mailing list.<br><br>\r\nThe error messages are as follows:<br>\r\n!data<br>\r\nPlease press the back button on your browser and re-enter your information.<br>\r\n</body>\r\n</html>";
  elseif($page_id=='remove') $html = "<html>\r\n<head>\r\n<title>Remove Confirmation Page</title>\r\n<style type=text/css rel=stylesheet> <!--\r\n.confirm_button: { border: 1px solid #000000; font: 10pt arial; }\r\n--> </style>\r\n\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour name was successfully retrieved from our database.  We apologize if our mailings were an inconvenience to you and hope you are able to find what you were looking for in the near future.  Please confirm that you would like your email address (!email) removed from our mailing list by clicking the button below.<br>\r\n!button";
  elseif($page_id=='remsuccess') $html = "<html>\r\n<head>\r\n<title>Removal Success Page</title>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour email address (!email) was successfully removed from our database.  Be assured you will never receive any more email from this list server.  We\'re sorry you decided to leave and wish you the best in the future!<br><br>\r\nManagement<br>\r\n</body>\r\n</html>";
  elseif($page_id=='remerror') $html = "<html>\r\n<head>\r\n<title>Remove Error</title>\r\n</head>\r\n<body bgcolor=white>\r\nThere were errors removing you from our mailing list(s):<br><br>!data\r\n</body>\r\n</html>";
  
  mysql_query("update $chdtable set html = '".addslashes($html)."', url = '' where id = '1' and typ = '".addslashes($page_id)."';");
   $logtxt = 'Default HTML reset to system default';
 } else {
  $rows = mysql_query("select html,url from $chdtable where id = '1' and typ = '".addslashes($page_id)."';");
  if(@mysql_num_rows($rows)>0){
   list($html,$url)=mysql_fetch_row($rows);
   mysql_query("update $chdtable set html = '".addslashes($html)."', url = '".addslashes($url)."' where id = '$cid' and typ = '".addslashes($page_id)."';");
   $logtxt = 'HTML reset to Default HTML';
  } else exit('default html not found!');
 }
}

// main page

// defaults
if(!$cid) $cid = '1';
if(!$page_id) $page_id = 'preconfirm';

adminheader('LM: Custom HTML','Custom HTML','');
echo "

<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>\n";

listmenu($list,'chtml');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

   <table border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <form name=chtmlfrm method=post action=./editchtml.php onsubmit=\"\">

      <input type=button class=button value=\"Back to List\" style=\"width: 80px\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button value=\"Custom Fields\" style=\"width: 96px\" onclick=\"hopto('./editlists.php?list=$list&efields=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button_on type=button id=ch value=\"Custom HTML\" style=\"width: 92px\" onclick=\"hopto('./editchtml.php?cid=$cid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Errors\" style=\"width: 52px\" onclick=\"hopto('./editemsg.php?eid=$eid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=sc value=\"Signup Code\" style=\"width: 86px\"  onclick=\"hopto('./editlists.php?list=$list&getcode=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
   </tr>
  </table>
  <img src=1.gif height=3><br>

<table width=630 class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Custom HTML</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('custom_html')."');\">Help</a><br></td></tr></table>
</td></tr></table>
<img src=1.gif height=2><br>
<span id=sel><select style=\"width: 240px\" class=xbox name=cid onchange=\"hopto('./editchtml.php?list=$list&cid='+this.value+'&page_id=$page_id');\">";
// get all custom html
$rows = mysql_query("select id,name from $chtable where 1 order by id");
while(list($id,$name)=mysql_fetch_row($rows)){
 if($id==$cid) $sel = ' selected'; else $sel = '';
 echo "<option value=$id$sel>$name";
}
echo "</select></span><span id=nam><input name=xname type=text class=xbox style=\"width: 240px; padding-left: 4px;\" size=12><img src=1.gif width=3><input type=button class=button value=\"Save\" onclick=\"chtmlfrm.txtname.value=xname.value; chtmlfrm.submit();\" style=\"margin-bottom: 1px\"><img src=1.gif width=2><input type=button class=button value=\"Cancel\" onclick=\"
 xname.value='';
 document.getElementById('sel').style.visibility='visible';
 document.getElementById('buttons').style.visibility='visible';
 document.getElementById('nam').style.visibility='hidden';
 sel.style.display='inline';
 buttons.style.display='inline';
 nam.style.display='none';
\" style=\"margin-bottom: 1px\"></span><script language=javascript>
 document.getElementById('nam').style.visibility='hidden';
 nam.style.display='none';
</script><span id=buttons>";
if($cid<>'1'){ echo "<img src=1.gif width=2><input type=button class=rbutton value=\"Rename\" onclick=\"
 document.getElementById('sel').style.visibility='hidden';
 document.getElementById('buttons').style.visibility='hidden';
 document.getElementById('nam').style.visibility='visible';
 sel.style.display='none';
 buttons.style.display='none';
 nam.style.display='inline';
 xname.focus();
\"><img src=1.gif width=2><input type=button class=rbutton value=\"Delete\" onclick=\"if(!confirm('Are you SURE you want to delete this entire HTML template?\\n\\nWarning: All lists and links using this template need to be updated!')) return false; else hopto('./editchtml.php?list=$list&page_id=$page_id&cid=$cid&del=1')\">"; }
echo "<img src=1.gif width=2><input type=button class=rbutton value=\"New\" onclick=\"if(confirm('Discard unsaved changes and start a new HTML template?')) hopto('./editchtml.php?list=$list&page_id=$page_id&cid=$cid&new=1')\"><img src=1.gif width=2><input type=button class=rbutton value=\"Copy\" onclick=\"hopto('./editchtml.php?list=$list&page_id=$page_id&cid=$cid&copyx=1')\"></span><br>
<img src=1.gif height=2><br>
<!-- <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td bgcolor=black height=1><img src=1.gif height=1><br></td></tr></table>
<img src=1.gif height=3><br> -->
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>\n";

if($page_id=='preconfirm') $b_prec = 'button_on'; else $b_prec = 'button';
if($page_id=='subscribe') $b_subs = 'button_on'; else $b_subs = 'button';
if($page_id=='suberror') $b_sube = 'button_on'; else $b_sube = 'button';
if($page_id=='remove') $b_remo = 'button_on'; else $b_remo = 'button';
if($page_id=='remsuccess') $b_rems = 'button_on'; else $b_rems = 'button';
if($page_id=='remerror') $b_reme = 'button_on'; else $b_reme = 'button';

echo "   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
       <input style=\"width: 102px\" class=$b_prec type=button value=\"Signup Confirm\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=preconfirm')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_prec'\">
      </td>
      <td width=3><img src=1.gif width=3></td>
      <td>
       <input style=\"width: 102px\" class=$b_subs type=button value=\"Signup Success\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=subscribe')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_subs'\">
      </td>
      <td width=3><img src=1.gif width=3></td>
      <td>
       <input style=\"width: 102px\" class=$b_sube type=button value=\"Signup Error\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=suberror')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_sube'\">
      </td>
      <td width=3><img src=1.gif width=3></td>
      <td>
       <input style=\"width: 102px\" class=$b_remo type=button value=\"Unsub Confirm\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=remove')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_remo'\">
      </td>
      <td width=3><img src=1.gif width=3></td>
      <td>
       <input style=\"width: 102px\" class=$b_rems type=button value=\"Unsub Success\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=remsuccess')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_rems'\">
      </td>
      <td width=3><img src=1.gif width=3></td>
      <td>
       <input style=\"width: 102px\" class=$b_reme type=button value=\"Unsub Error\" onclick=\"hopto('./editchtml.php?list=$list&cid=$cid&page_id=remerror')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$b_reme'\">
      </td>
     </tr>
    </table>
    <img src=1.gif height=5><br>
  </td>
 </tr>
 <tr>
  <td>\n";

$cmd = "select html,url from $chdtable where id = '$cid' and typ = '$page_id'";
$custrows = mysql_query($cmd) or die ("Could not read database.");
list($txthtml,$txturl) = @mysql_fetch_row($custrows);
if($txturl) $txthtml = '';

if($page_id=='preconfirm') $page_txt = "<span class=table_inside_normal><u>Signup Confirm Page</u>:<br>This page is shown when a user successfully enters valid information in the signup form and email confirmation is turned on for the list being subscribed to.  This page should notify the user to check their email where they will find a link to verify their subscription.  Message codes can be used in your HTML to output user information.<br><br></span>";
if($page_id=='subscribe') $page_txt = "<span class=table_inside_normal><u>Signup Success Page</u>:<br>This page is shown when a user successfully enters valid information in the signup form (email confirm turned off) or when the user clicks the confirmation link in the confirmation email (email confirm turned on).  Message codes can be used in your HTML to output user information.<br><br></span>";
if($page_id=='suberror') $page_txt = "<span class=table_inside_normal><u>Signup Error Page</u>:<br>This page is shown when users do not enter or enter invalid data in the signup form.  The code <b>!data</b> will be replaced by the error messages, which will be separated by <b>&lt;br&gt;</b>'s.  Apply your font and site formatting around this code.  Other message codes can not be used on this page.<br><br></span>";
if($page_id=='remove') $page_txt = "<span class=table_inside_normal><u>Remove Confirm Page</u>:<br> This page is shown by rem.php when a user's email address is confirmed in the database.  Message codes can be used in your HTML to output user information.  A message code of the type \"Remove Confirm\" must be used to produce a button that will continue with the removal process.  Without the button they can't finish unsubscribing, so this is very important!<br><br></span>";
if($page_id=='remsuccess') $page_txt = "<span class=table_inside_normal><u>Remove Success Page</big></u>:<br> This page is shown by rem.php when a user's email address has been removed from the database.  Message codes can be used in your HTML to output (former) user information.<br><br></span>";
if($page_id=='remerror') $page_txt = "<span class=table_inside_normal><u>Remove Not Found Page</u>:<br> This page is shown by rem.php when a user's email address is not found in the database.  The code <b>!data</b> will be replaced by error messages.  The code <b>!email</b> works with form-based removal to output the entered email address.<br><br></span>";
   
echo "<table border=0 cellspacing=0 cellpadding=0><tr><td>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>
   $page_txt
   <div class=table_inside_small>Modify your HTML and paste it below.  Make sure that your image and link paths are relative to the ListMail directory.</div>
   <textarea name=txthtml class=xarea cols=120 rows=10 style=\"width: 630px\">".htmlspecialchars($txthtml)."</textarea><br></font>
   <img src=1.gif height=2><br>
   <div class=table_inside_small>Alternately, enter a URL to forward the user to.
   <input type=text class=xbox name=txturl size=75 style=\"width: 380px\" value=\"".htmlspecialchars($txturl)."\"><br>
   <img src=1.gif height=2><br>
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
     <input type=hidden name=save value=1>
     <input type=hidden name=cid value=$cid>
     <input type=hidden name=page_id value=$page_id>
     <input type=hidden name=list value=$list>
     <input type=hidden name=txtname value=\"\">
     <input type=submit class=button value=\"Save HTML\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
     </td></form>

<!--     <td>
      <form name=ft method=post>
      <input type=hidden name=dotest value=1>
      <input type=submit class=button value=\"Test\">
     </td></form> -->
    
     <td width=3><img src=1.gif width=3></td>
     <td>
      <input type=button class=button value=\"Back to List\" onClick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td>
      <input type=button class=button value=\"Clear HTML\" onClick=\"if(confirm('This will completely clear the currently entered Custom HTML.\\nAfter entering your own HTML you must click \\'Save HTML\\' to save the changes.')){  document.chtmlfrm.txthtml.value=''; chtmlfrm.txthtml.focus(); } else return false;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td>
      <input type=button class=button value=\"Set to Default\" onClick=\"if($cid==1){
if(confirm('Reset this page to the system default?  The change will automatically be saved.')){
 hopto('./editchtml.php?list=$list&cid=$cid&page_id=$page_id&reset=1&default=1');
} else return false
} else {
if(confirm('Reset this page to Default HTML? The change will automatically be saved.')){
 hopto('./editchtml.php?list=$list&cid=$cid&page_id=$page_id&reset=1');
} else return false
};\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">
     </td>    </tr>
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