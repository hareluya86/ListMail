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

$id = $_GET['id'];

// if(isset($id)) echo "ID set. value=\"$id\"<br>"; else echo "ID not set<br>";

if(isset($id)){
 $link = mysql_connect($sqlhost,$sqluser,$sqlpass);
 mysql_selectdb($sqldb);

 // check if tracking users
 list($ktrk) = mysql_fetch_row(mysql_query("select ktrack from $ctable where 1"));
 if($ktrk==1) {
  $userid = substr($id,0,7); $rcode = substr($id, 7, strlen($id)+1); }
 else $rcode = $id;

 // get link data
 $krow = mysql_query("select id as kid,refcode,title,http,hits from $ktable where refcode = '$rcode' limit 0,1");
 if(mysql_num_rows($krow)==0){ $krow = mysql_query("select id as kid,refcode,title,http,hits from $ktable where refcode = '$id' limit 0,1"); }
 if(mysql_num_rows($krow)==0){ $userid = substr($id,0,7); $rcode = substr($id, 7, strlen($id)+1); $krow = mysql_query("select id as kid,refcode,title,http,hits from $ktable where refcode = '$rcode' limit 0,1"); }
 if(mysql_num_rows($krow)==0) echo "link not found..";
 while(list($kid,$refcode,$title,$http,$hits) = @mysql_fetch_row($krow)){
  // exit();
  $newhit = $hits + 1;
  $cmd = "update $ktable set hits = '$newhit' where id = '$kid'";
  mysql_query($cmd);

  // track hit
  $tdate = date("Y-m-d H:i:s");
  if($ktrk==1){
   // get the user's data
   $result = mysql_query("select id from $utable where uid = '$userid'");
   list($uid) = mysql_fetch_row($result);
  }
  // insert id,uid,linkid
  mysql_query("insert into $htable values('','$uid','$kid','$tdate')");

  // parse the URL in case of http
  if (!strstr($http, "://")) { $http = "http://" . $http; }

  // process msg code in link
  if($ktrk==1){ list($http) = processmsg($uid,$http,'','','0'); }

  /* $http = urlencode($http);
    
  // fix : / ? & =
  $http = str_replace('%3A',':',$http);
  $http = str_replace('%2F','/',$http);
  $http = str_replace('%3F','?',$http);
  $http = str_replace('%3D','=',$http);
  $http = str_replace('%26','&',$http); */

  // show the redirect page
  echo "<html>
<head>
<title>Linking to $title... Please stand by...</title>
<noscript>
<meta http-equiv=\"refresh\" content=\"3; URL=$http\">
</noscript>
<script language=\"JavaScript\"><!--
var sTargetURL = \"$http\";
function doRedirect(){
    setTimeout( \"window.location.href = sTargetURL\", 2*1000 );
}
//--></script>

<script language=\"JavaScript\"><!--
function doRedirect(){
    window.location.replace(sTargetURL);
}
//--></script>
</head>
<body>
<br>
<div align=center style=\"font: 12pt san-serif\">Please <a href=\"$http\">Click Here</a> if you are not forwarded automatically.</div>
<script language=\"JavaScript\"><!--
doRedirect();
//--></script>
</body>
</html>";
 }
}

// show and edit links
if (!isset($id)){
$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);

if(!$list) $list = 'all';

$islogged = islogged();

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

if($doadd == 'yes'){
 // id,refcode,title,http,hits
 $cmd = "insert into $ktable values('null','','','','0')";
 if($demo<>'yes'){
  mysql_query($cmd);
  $logtxt = "Link added.";
 } else {
  $logtxt = "Cannot add link in demo.";
 }
}

if($dodel == 'yes'){
 $cmd = "delete from $ktable where id = '$delid'";
 if($demo<>'yes'){
  mysql_query($cmd);
  $logtxt = "$dtitle link deleted.";
 } else {
  $logtxt = "Cannot delete link in demo.";
 }
}

if($dosave == 'yes'){
 // check if dupe or not
 $rows = mysql_query("select id from $ktable where refcode like '$textref'");
 if(@mysql_num_rows($rows)>0 && strtoupper($textref) <> strtoupper($origref)){
  $dupe = 1;
  $logtxt = 'Duplicate link name, not saved.';
 } else {
  $dupe = '';
  $texttitl = addslashes($texttitl);
  $texthttp = addslashes($texthttp);

  $cmd = "update $ktable set refcode = '$textref', title = '$texttitl', http = '$texthttp', hits = '$texthits' where id = '$saveid'";
  if($demo<>'yes'){
   mysql_query($cmd);
   $logtxt = stripslashes($texttitl)." link saved.";
  } else {
   $logtxt = "Cannot save link in demo.";
  }
 }
}


 if($hitpage){
  if($clearhits){
	@mysql_query("delete from $htable where 1");
	$nrows = mysql_affected_rows();
	$logtxt = $nrows . ' tracked hits removed from database.';
  }
  adminheader("LM: Link Hits","Link Hits",'');

  if(!$cpage) $cpage = '1';
  if(!$perp){ $perp = '10'; }
  if(!$sord){ $sord = 'desc'; }
  if(!$sby){ $sby = 'date'; }
  if(!$slist && $slist <> '0'){ $slist = 'all'; }
  if($sord=='asc') { $sord_asc = " selected"; }
  if($sord=='desc') { $sord_desc = " selected"; }
  if($sby=='date') { $sby_date = " selected"; $ordby = "$htable.date"; }
  if($sby=='kname') { $sby_knam = " selected"; $ordby = "$ktable.refcode"; }
  if($sby=='uname') { $sby_unam = " selected"; $ordby = "$utable.fname"; }
  if($sby=='umail') { $sby_emai = " selected"; $ordby = "$utable.email"; }
  $count = mysql_num_rows(mysql_query("select id from $htable where 1"));
  $numpages = ceil($count / $perp);
  $testvar = $cpage * $perp;
  $plim = $testvar - $perp;

  echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,'links');
echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <form name=add method=post action=$PHP_SELF>
      <input type=hidden name=list value=$list>
      <input class=button type=submit value=\"Back to Links\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\">
     </td></form>
     <td>
      <!-- sort -->
      <table border=0 cellspacing=0 cellpadding=0><tr><td>
      <form name=disp method=post><span class=table_inside_toolbar>&nbsp;Show&nbsp;</span></td>
       <td><input class=xbox type=text name=perp size=1 value=\"$perp\"></td>
       <td><span class=table_inside_toolbar>&nbsp;per page. Sort&nbsp;</span></td>
       <td><select class=xarea name=sord><option value=asc$sord_asc>Up</option><option value=desc$sord_desc>Down</option></select></td>
       <td><span class=table_inside_toolbar>&nbsp;by&nbsp;</span></td>
       <td><select class=xarea name=sby><option value=date$sby_date>Date/Time</option><option value=kname$sby_knam>Link</option><option value=uname$sby_unam>User Name</option><option value=umail$sby_emai>Email</option></select></td>
       <td><span class=table_inside_toolbar><input type=hidden name=hitpage
value=1>&nbsp;</span></td><td><input class=button type=submit name=dosort value=\"Show\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 45px\"></td></form>
      </tr></table>
      <!-- end sort -->
     </td>
    </tr>
   </table>
<img src=1.gif height=2><br>
<table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Tracked Link Hits</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('tracked_hits')."');\">Help</a><br></td></tr></table>
</td></tr></table>
<img src=1.gif height=5><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      </font><font size=1 face=helvetica color=navy>$hlogtxt</font>
     </td>
     <td align=right><span class=table_inside_small>Pages:&nbsp;</span>\n";

     for($i=1;$i<=$numpages;$i++){
      echo "<a class=table_inside_small";
      if($i <> $cpage){ echo " href=$PHP_SELF?hitpage=y&cpage=$i&sord=$sord&sby=$sby&perp=$perp"; }
      echo ">$i</a> ";
     }

echo "     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr><td height=3><img src=1.gif height=3></td></tr>
 <tr>
  <td>
   <!-- border --> <table class=thinbord width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
   <table width=100% border=0 cellspacing=0 cellpadding=2>
    <tr class=bg2>
     <td><span class=table_head_col>Date<br></span></td>
     <td><span class=table_head_col>Time<br></span></td>
     <td><span class=table_head_col>Link<br></span></td>
     <td><span class=table_head_col>Name<br></span></td>
     <td><span class=table_head_col>Email<br></span></td>
     <td><span class=table_head_col>List<br></span></td>
    </tr>\n";

     $result = mysql_query("select $htable.id,$htable.uid,$htable.kid,$htable.date,$utable.list,$utable.fname,$utable.lname,$utable.email,$ktable.refcode,$ktable.http from $htable left join $utable on $htable.uid = $utable.id left join $ktable on $htable.kid = $ktable.id where 1 order by $ordby $sord limit $plim,$perp;");
   while(list($hid,$uid,$kid,$dat,$lis,$fna,$lna,$ema,$kref,$http) = mysql_fetch_row($result)){
    if($uid) {
     if($lna && $fna) $nam = "$fna $lna";
     if($fna && !$lna) $nam = $fna;
     if(!$fna && !$lna) $nam = '';
     if(!$nam) { $nam = '?'; }
     if(!$ema) { $ema = '?'; }
     if(!$lis) { $lis = '?'; }

    }
    if(!$uid) { $nam = '?'; $ema = '?'; $lis = '?';}

    // parse the URL in case of http
    if (!strstr($http, "://")) { $http = "http://" . $http; }
    $dat = explode(" ",$dat);
    $thedate = str_replace('-','&middot;',$dat[0]);
    $thetime = $dat[1];
    echo "
    <tr>
     <td><span class=link_tracked_text>$thedate</span><br></td>
     <td><span class=link_tracked_text>$thetime</span><br></td>
     <td><a class=link_tracked_text href=\"$http\">$kref</a><br></td>
     <td><span class=link_tracked_text>$nam</span><br></td>
     <td><span class=link_tracked_text>$ema</span><br></td>
     <td><span class=link_tracked_text>$lis</span><br></td>
    </tr>\n";
   }
echo "   </table>
   <!-- end border --> </td></tr></table>
  </td>
 </tr>
</table>
<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td><form method=post action=./link.php><input type=hidden name=list value=$list><input type=hidden name=hitpage value=$hitpage><input type=hidden name=clearhits value=1><input type=submit class=button value=\"Remove All\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 85px\"></td></form></tr></table>\n";
if($logtxt) echo "<img src=1.gif height=5><br><span class=status_text>$logtxt</span><br>";
echo "</td></tr></table>\n";
  exit;
 }

// main page
if($tracksub=='Set'){
 if($trcheck<>1) $trcheck = 0;
 $cmd = "update $ctable set ktrack = '$trcheck' where 1";
 if($trcheck<>1) $stat = "OFF"; else $stat = "ON";
 mysql_query($cmd);
 $logtxt = "Tracking option set to $stat.";
}
if($resethits){
 mysql_query("update $ktable set hits = '0' where 1");
 $logtxt = 'All link hits reset.';
 list($ktrk) = mysql_fetch_row(mysql_query("select ktrack from $ctable where 1"));
 if($ktrk==1) $logtxt .= ' Tracked hits must be removed separately.';
}
list($keych,$linkch)=mysql_fetch_row(mysql_query("select keycode,linkcode from $ctable where 1"));

adminheader("LM: Global Links","Global List Links",'');
echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,'links');
echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <form name=add method=post action=$PHP_SELF>
      <input type=hidden name=doadd value=yes>
      <input type=hidden name=list value=$list>
      <input class=button type=submit name=asub value=\"Add Link\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 80px\">
     </td></form>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <form name=vhits method=post>
      <input type=hidden name=hitpage value=1>
      <input type=hidden name=list value=$list>
      <input class=button type=submit value=\"Tracked Hits\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">
     </td></form>
     <td align=right>";

     // read track variable from config
     $cmd = "select ktrack from $ctable where 1";
     $ktrow = mysql_query($cmd);
     list($ktrack) = mysql_fetch_row($ktrow);
     if($ktrack==1) $tchkval = ' checked'; else $tchkval = '';

echo "      <form name=sett method=post>
      <input type=checkbox name=trcheck value=1$tchkval><span class=table_inside_toolbar>Track&nbsp;users&nbsp;</span><input class=button type=submit name=tracksub value=\"Set\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\">
      <input type=hidden name=list value=$list>
     </td></form>
    </tr>
   </table>
  </td>
 </tr>
 <tr><td height=3><img src=1.gif height=3></td></tr>
 <tr>
  <td>
   <!-- border -->
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Link Codes</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('link_codes')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
   <tr>
   <td>
   <table width=100% border=0 cellspacing=0 cellpadding=3>
    <tr class=bg2>
     <td><span class=table_head_col>Message<br>Code<br></span></td>
     <td><span class=table_head_col>Ref.<br>Code<br></span></td>
     <td><span class=table_head_col>Link<br>Title<br></span></td>
     <td><span class=table_head_col>URL (Hidden in email)<br><b>http://</b> is <u>optional</u>.<br></span></td>
     <td><span class=table_head_col>Link<br>Hits</span></td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
    </tr>\n";
$cmd = "select id as kid,refcode,title,http,hits from $ktable where 1 order by refcode";
$result = mysql_query($cmd);
while(list($kid,$refcode,$title,$http,$hits) = @mysql_fetch_row($result)){
 // parse the URL in case of http
 if (!strstr($http, "://")) { $nhttp = "http://" . $http;} else { $nhttp = $http; }
 // show the redirect page
 echo "   <tr>
    <td nowrap><span class=code_example>" . $keych . $linkch . $refcode . "</span><br></td>
    <td width=1>
     <form name=links method=post action=$PHP_SELF>
     <input type=hidden name=origref value=\"".htmlspecialchars($refcode)."\">
     <input class=xbox type=text name=textref value=\"".htmlspecialchars($refcode)."\" size=6>
    </td>
    <td width=1><input class=xbox type=text name=texttitl value=\"".htmlspecialchars($title)."\" size=12></td>
    <td width=1><input class=xbox type=text name=texthttp value=\"".htmlspecialchars($http)."\" size=28></td>
    <td width=1><input class=xbox type=text name=texthits value=\"$hits\" size=5></td>
    <td width=1>
     <input type=hidden name=dosave value=yes>
     <input type=hidden name=saveid value=$kid>
     <input class=button type=submit name=ssub value=\"Save\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\">
     <input type=hidden name=list value=$list>
    </td></form>
    <td width=1>
     <input class=button type=button name=tsub value=\"Test\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\" onclick=\"window.open('$nhttp','LM_Link_Preview_$kid','toolbar=no,directories=no,status=yes,menubar=no,copyhistory=no,scrollbars=yes,resizable=yes,location=yes');\">
    </td>
    <td width=1>
     <form name=dele method=post action=$PHP_SELF onSubmit=\"if (!confirm('".htmlspecialchars(addslashes($title))."\\n".htmlspecialchars(addslashes($http))."\\n\\nAre you sure you want to delete this link?')) return false\">
     <input type=hidden name=dodel value=yes>
     <input type=hidden name=delid value=$kid>
     <input type=hidden name=dtitle value=\"$title\">
     <input class=button type=submit name=dsub value=Delete onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\">
     <input type=hidden name=list value=$list>
    </td></form>
   </tr>\n";
}
echo "   </table>
   <!-- end border --> </td></tr></table>
   <img src=1.gif height=3><br>
   <table border=0 cellspacing=0 cellpadding=0><tr><td><form method=post action=./link.php><input type=hidden name=list value=".$list."><input type=hidden name=hitpage value=".$hitpage."><input type=hidden name=resethits value=1><input type=submit class=button value=\"Reset All Hits\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 95px\"></td></form></tr></table>\n";

if($logtxt) echo "<img src=1.gif height=5><br><span class=status_text>$logtxt</span><br>\n";
echo "  </td>
 </tr>
</table>
</td></tr></table>
<script language=javascript>window.status='".addslashes($logtxt)."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->\n";
} // end checkpw
} // end !id
// EOF
?>
