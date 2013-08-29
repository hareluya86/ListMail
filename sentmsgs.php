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

if($view){
 $cmd = "select subject,content,htcontent,fattach,datesent,numsent from $ttable where id = '".addslashes($sid)."'";
 $result = mysql_query($cmd);
 list($txtsubj,$txtcont,$txthtcont,$txtfatt,$datesent,$numsent) = @mysql_fetch_row($result);

 $datesent = substr($datesent,0,16).' '.date("T").' to '.$numsent.' Users';
 include('./editmsg.php');
 editmessage('sentmsg');
 exit;
}

if($del){
 mysql_query("delete from $ttable where id = '".addslashes($sid)."';");
 if(!$demo) { mysql_query($cmd); $logtxt .= 'Sent message deleted. '; } else { $logtxt .= 'Cannot delete sent messages in demo. '; }
}

$main = 1;
if($main){
 adminheader('LM: Sent Messages','Sent Messages','');
 $nsent = @mysql_num_rows(@mysql_query("select id from $ttable where 1"));

echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

listmenu($list,'sentmsgs');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>\n";

if ($nsent == '0'){
 echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
 <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Sent Messages</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('sentmsgs')."');\">Help</a><br></td></tr></table>
</td></tr></table>
<img src=1.gif height=5><br><div align=center><font size=2 color=navy face=helvetica>There are no sent messages.</font></div>";
} else {
echo "   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Sent Messages</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('sentmsgs')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
    <tr>
     <td>
     <table width=100% border=0 cellspacing=0 cellpadding=3>
      <tr class=bg2>
       <td class=table_head_col>List or Selection</td>
       <td class=table_head_col>Date & Time (".date("T").")</td>
       <td class=table_head_col>Email Subject</td>
       <td class=table_head_col># Sent</td>
       <td class=table_head_col>&nbsp;</td>
       <td class=table_head_col>&nbsp;</td>
      </tr>";

$cmd="select id,subject,datesent,numsent,sentto from $ttable where 1 order by datesent desc";
$result = mysql_query($cmd);
while(list($id,$subject,$datesent,$numsent,$sentto) = @mysql_fetch_row($result)){
 // for pre 1.84 sent msgs
 if(substr($datesent,11,5)=='00:00'){
  $datesent = substr($datesent,0,10).' · <i>Unknown</i>';
 } else {
  $datesent = substr($datesent,0,10).' / '.substr($datesent,11,5);
 }
 if($numsent=='0') $numsent = '<i>?</i>';
 
 $to = '';
 if(is_numeric($sentto)){
  // list, get title
  $lrow = mysql_query("select title from $ltable where listnum = '$sentto';");
  if(@mysql_num_rows($lrow)>0){
   list($title)=mysql_fetch_row($lrow);
   $to = $sentto.': '.$title;
  }
 } elseif(!$sentto){
 } else {
  // select, get name
  $srow = mysql_query("select name from $setable where id = '".addslashes(substr($sentto,2,strlen($sentto)-2))."';");
  if(@mysql_num_rows($srow)>0){
   list($to)=mysql_fetch_row($srow);
  }
 }
 if(!$to) $to = 'Unknown';
 echo "
      <tr>
       <td>
        <span class=sent_msg>$to</span><br>
       </td>
       <td>
        <span class=sent_msg>$datesent</span><br>
       </td>
       <td nowrap>
        <input type=text class=xbox value=\"".htmlspecialchars($subject)."\" name=txtsubj size=60 style=\"width: 200px; padding-left: 2px;\"><br>
       </td>
       <td align=center>
        <span class=sent_msg>$numsent</span><br>
       </td>
       <td width=1>
        <input class=button type=button value=View onclick=\"hopto('./sentmsgs.php?list=$list&view=1&sid=$id');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\">
       </td>
     <td width=1>
      <form name=fd method=post action=$PHP_SELF onSubmit=\"if (!confirm('Are you sure you want to remove this sent message?')) return false\">
      <input type=hidden name=del value=yes>
      <input type=hidden name=sid value=$id>
      <input type=hidden name=list value=$list>
      <input class=button type=submit value=Delete onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\">
      </form>
     </td>
    </tr>\n";
 }
 echo "</table>\n";
} // end nfups != 0

echo "  </td>
 </tr>
</table>\n";

if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text style=\"color: navy\">$logtxt</span><br>";

echo "</td></tr></table>\n";
echo "<script language=javascript>window.status='".addslashes($logtxt)."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->";

} // main
} // admin

// EOF
?>
