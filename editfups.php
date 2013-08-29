<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$islogged = islogged();

// $DEBUG = true;

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);

$focus = '';
$logtxt = '';

if(!$list || $list == 'all') $list = firstlist();

// set up cmds
if(!$sets) $xcmd = "list = '".addslashes($list)."'";
elseif($sets && $sid) $xcmd = "sset = '".addslashes($sid)."'";

elseif($sets && !$sid && (!$add && !$edit)){
 list($sid)=mysql_fetch_row(mysql_query("select id from $fstable where 1 order by id limit 1"));
 $xcmd = "sset = '".addslashes($sid)."'";
}
// !$sets = regular LIST FOLLOWUPS processing
if($updatedel && ($ql_save || $ql_load || $ql_del || $preview)) $udcheck = ' checked'; else $udcheck = '';

if($add && !$save && !$ql_load && !$ql_del && !$preview){
 // defaults
 $result = mysql_query("select id,seq from $ftable where $xcmd order by seq desc limit 1");
 if(mysql_num_rows($result) == '0'){
  $nseq = '1';
 } else {
  list($id,$seq) = @mysql_fetch_row($result);
  $nseq = $seq+1;
 }
 $txtseq = $nseq;
 $tolist = $list;
 $txtdel = '0';
 $txtcron = '';
 $udcheck = ' checked';
}
if($edit && !$save){
 if(!$updatedel) $udcheck = '';
}
if (($add || $edit) && $save) {
 $error = '';
 $udcheck = '';
 if($demo<>'yes'){
//  echo "crdel=$crdel<br>";
  if($crdel=='cron'){
   $error = checkcron($txtcron);
   if($error){
    $error = "ERROR: Invalid cron format.  Enter 3 fields, * or numeric, for DOM M DOW.  See the Help page.";
    $focus = "<script language=javascript><!--\nform1.txtcron.style.color='red'; form1.txtcron.focus();\n--></script>";
   }
  } elseif(!$crdel){
   $error = 'Missing data (crdel). ';
  }

  if($crdel=='del') $txtcron = '';
  if(!is_numeric($txtseq)) $error .= "Seq # must be numeric. ";
  if(!is_numeric($txtdel)) $error .= "Delay # must be numeric. ";

  if($updatedel && !$error){
   @mysql_query("update $utable set cdel = '$txtdel' where list = '$tolist' and cseq = '$txtseq'");
   $nrows = @mysql_affected_rows();
   $logtxt .= "Updated $nrows user delays. ";
  } elseif($updatedel && $error){
   $udcheck = ' checked';
  }

  if($edit && !$add && !$error){
   if($frearr) movefup($fid,$txtseq);
   mysql_query("update $ftable set list = '".addslashes($tolist)."', subject = '".addslashes($txtsubj)."', content = '".addslashes($txtcont)."', htcontent = '".addslashes($txthtcont)."', fattach = '".addslashes($txtfatt)."', seq = '".addslashes($txtseq)."', del = '".addslashes($txtdel)."', cron = '".addslashes($txtcron)."' where id = '".addslashes($fid)."'") or die(mysql_error());
   $logtxt .= "Followup #$txtseq saved. ";
  }
  if($add && !$error){
   $fid = addfup($tolist,$txtsubj,$txtcont,$txthtcont,$txtfatt,$txtseq,$txtdel,$txtcron);
   $logtxt .= "Followup #$txtseq added. ";
   $add = ''; $edit = 1;
  }
  if(!$error && $frearr){
/*   // process users on this fup
   echo "original seq = $oseq!<br>";
   list($ns,$nd) = nextfup($oseq);
   mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where cseq = '".addslashes($oseq)."' and list = '".addslashes($list)."';"); */
  }
  if($error) $logtxt .= $error;
 } else {
  if($add) $logtxt .= "Cannot add followup in demo. ";
  if($edit) $logtxt .= "Cannot save followup in demo. ";
 }
}

if($dodel){
 if(!$demo){
  // get seq # from id
  $frows = mysql_query("select id,seq from $ftable where id = '$delid';");
  if(@mysql_num_rows($frows)>0){
   list($i,$s)=mysql_fetch_row($frows);
   // do the delete
   mysql_query("delete from $ftable where id = '$delid'");
   delfup($s);
   $logtxt .= "Followup deleted. ";
  } else {
   $logtxt .= 'Error: Followup not found (already deleted?) ';
  }
 } else $logtxt .= 'Cannot delete followup in demo. ';
}

if($edit || $add) {
 // display full editing page for one followup with submit link
 if($edit && !$ql_save && !$ql_del && !$ql_load && !$preview  && !$error){
  $cmd = "select list,subject,content,htcontent,fattach,seq,del,cron from $ftable where id = '$fid'";
  $result = mysql_query($cmd);
  list($tolist,$txtsubj,$txtcont,$txthtcont,$txtfatt,$txtseq,$txtdel,$txtcron) = @mysql_fetch_row($result);
 } elseif ($add) $edit = 1;
 if($DEBUG) echo "sets=$sets<br>sid=$sid<br>";
 if($sets && $sid) $tolist = "set$sid";
 if($DEBUG) echo "tolist = $tolist<br>";
 include('./editmsg.php');
 editmessage('followup');
 exit;
} // end edit

if($quicksave){
 // check cron
 if($DEBUG) echo "txtseq=$txtseq<br>";
 if($txtcron) $cerror = checkcron($txtcron);

  // check for blank subject or seq #
 if((!$txtseq || $txtseq <= '0') || !$txtsubj || $dupe){
  $logtxt .= "Invalid Seq # - use integers above 0. Not saved. ";
 } elseif($cerror){
  $logtxt .= "Invalid cron format.  Enter 3 fields, * or numeric, for DOM M DOW.  Not saved.";
  $focus = "<script language=javascript><!--\nf_frm$fid.txtcron.style.color='red'; f_frm$fid.txtcron.focus();\n--></script>";
 } else {
  if(!$demo){
   if($DEBUG) echo "fupid=$fupid txtseq=$txtseq \$oseq not needed<br>";
   if($qfrearr) movefup($fupid,$txtseq);
   if($sets) $txtlist = '0';
   $cmd = "update $ftable set list = '".addslashes($txtlist)."', subject = '".addslashes($txtsubj)."', seq = '".addslashes($txtseq)."', del = '".addslashes($txtdel)."', cron = '".addslashes($txtcron)."' where id = '".addslashes($fid)."'";
   if($DEBUG) echo "CMD=$cmd<Br>";
   mysql_query($cmd);
   $logtxt .= "Followup #$txtseq saved. ";
  } else {
   $logtxt .= "Cannot save in demo. ";
  }
 }
 $txtsubj = stripslashes($txtsubj);
}

if($ru_submit){
 if(is_numeric($ru_seq) && is_numeric($ru_del) && is_numeric($list)){
  mysql_query("update $utable set cseq = '$ru_seq', cdel = '$ru_del' where list = '$list';");
  $aff = mysql_affected_rows($link);
  $logtxt .= "Updated $aff users on this list to Seq $ru_seq Delay $ru_del. ";
 } else {
  $logtxt .= 'Error updating users. ';
 }
}
if($ss_submit){
 // copy current list followups to saved set named ss_name
 mysql_query("insert into $fstable values('','".addslashes($ss_name)."');");
 $sid = mysql_insert_id();
 if($sets){
  $xcmd = "sset = '".addslashes($sid)."'";
  $xlist = '0';
 } else {
  $xcmd = "list = '".addslashes($list)."'";
  $xlist = $list;
 }

 // loop creation
 $frows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron from $ftable where $xcmd order by seq;");
 if(@mysql_num_rows($frows)>0){
  while(list($sub,$con,$htc,$fat,$seq,$del,$cro)=mysql_fetch_row($frows)){
   // insert new followups
   mysql_query("insert into $ftable values('','','0','".addslashes($sub)."','".addslashes($con)."','".addslashes($htc)."','".addslashes($fat)."','".addslashes($seq)."','".addslashes($del)."','".addslashes($cro)."','$sid')");
  }
  $logtxt .= "New followup set ($ss_name) created. <a class=status_link2 href=./editfups.php?list=$list&sets=1&sid=$sid>Edit it now</a>";
 } else {
  // mysql_query("delete from $fstable where id = '$sid';");
  $logtxt .= 'Error: No followups on this list to save ';
 }
}
if($ls_delete){
 // delete saved set ls_id
 if(is_numeric($ls_id)){
  $srow = mysql_query("select id,name from $fstable where id = '".addslashes($ls_id)."';");
  if(@mysql_num_rows($srow)>0){
   list($i,$n)=mysql_fetch_row($srow);
   mysql_query("delete from $ftable where sset = '".addslashes($ls_id)."';");
   mysql_query("delete from $fstable where id = '$ls_id';");
   $logtxt .= 'Followup set ('.$n.') deleted.';
  } else {
   $logtxt .= 'Error: Followup set not found (already deleted?). ';
  }
 }
}
if($ls_submit){
 if(is_numeric($ls_id)){
  // delete current list followups
  mysql_query("delete from $ftable where $xcmd;");
  // get fups with this sset
  $frows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron from $ftable where sset = '$ls_id' order by seq;");
  if(@mysql_num_rows($frows)>0){
   while(list($sub,$con,$htc,$fat,$seq,$del,$cro)=mysql_fetch_row($frows)){
    // insert new followups
    mysql_query("insert into $ftable values('','','".addslashes($list)."','".addslashes($sub)."','".addslashes($con)."','".addslashes($htc)."','".addslashes($fat)."','".addslashes($seq)."','".addslashes($del)."','".addslashes($cro)."','')");
   }
   $frow = mysql_query("select name from $fstable where id = '$ls_id';");
   list($n)=mysql_fetch_row($frow);
   $logtxt .= 'Saved followup set ('.$n.') loaded onto current list.  You may want to Reset Users. ';
  }
 }
}

// SAVED SET processing
if($sets){
 if(!$sid){
  list($sid)=mysql_fetch_row(mysql_query("select id from $fstable where 1 order by id limit 1"));
  $xcmd = "sset = '".addslashes($sid)."'";
 }
 // new / copy set
 if($snew || $scopy){
  if($snew){
   $titl = 'New Saved Set';
   $logtxt = 'New saved followup set created.';
  } elseif($scopy){
   $fromid = addslashes($sid);
   $rows = mysql_query("select name from $fstable where id = '".addslashes($sid)."';");
   list($n)=mysql_fetch_row($rows);
   $titl = 'Copy of '.addslashes($n);
   $logtxt = 'Saved followup set copied.';
  }
  mysql_query("insert into $fstable values('','".addslashes($titl)."');");
  $sid = mysql_insert_id();
  $xcmd = "sset = '".addslashes($sid)."'";
  if($scopy){
   $rows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron from $ftable where sset = '$fromid';");
   if(@mysql_num_rows($rows)>0){
    while(list($sub,$con,$htc,$fat,$seq,$del,$cro)=mysql_fetch_row($rows)){
     mysql_query("insert into $ftable values('','','0','".addslashes($sub)."','".addslashes($con)."','".addslashes($htc)."','".addslashes($fat)."','".addslashes($seq)."','".addslashes($del)."','".addslashes($cro)."','$sid');");
    }
	}
  }
  $logtxt = 'New saved followup set created.';
 }

 // del set
 if($sdel){
  $sid = addslashes($sid);
  mysql_query("delete from $fstable where id = '$sid';");
  mysql_query("delete from $ftable where sset = '$sid';");
  // get next lower id, or lowest id failing that
  $fr = mysql_query("select id from $fstable where id < '".addslashes($sid)."' order by id desc limit 1;");
  if(@mysql_num_rows($fr)>0) list($sid)=mysql_fetch_row($fr);
  else {
   $fr = mysql_query("select id from $fstable where 1 order by id limit 1;");
   if(@mysql_num_rows($fr)>0) list($sid)=mysql_fetch_row($fr);
   else {
    $logtxt = 'No saved set to load!';
    $sid = '';
   }
  }
  $logtxt = 'Saved followup set deleted. ';
  $xcmd = "sset = '".addslashes($sid)."'";
  $sdel = '';
 }

 // save set
 if($ssave){
  $sid = addslashes($sid);
  $xcmd = "sset = '".addslashes($sid)."'";
  $txtname = addslashes($txtname);
  if(!$demo){
   if($txtname) mysql_query("update $fstable set name = '$txtname' where id = '$sid';");
   $logtxt = "Followup set (name only) saved.";
  } else {
   $logtxt = "Cannot save in demo.";
  }
 }
 if(!$sid){
  list($sid)=mysql_fetch_row(mysql_query("select id from $fstable where 1 order by id limit 1"));
  $xcmd = "sset = '".addslashes($sid)."'";
 }
} // end SAVED SET processing

// MAIN page

if(!$sets) $tx = "Followup Messages: List $list";
elseif($sets) $tx = "Saved Followup Sets";
adminheader('LM: Followup Messages',$tx,'');

echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

listmenu($list,'fups');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table width=100% border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>";
//      <form id=form1 method=post action=$PHP_SELF>";
if(!$more && !$sets) $on = '_on'; else $on = '';
echo "      <input class=button$on type=button value=\"Current List\" onclick=\"hopto('./editfups.php?list=$list');\" style=\"width: 100px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$on'\"><br>
     </td>
     <td width=3><img src=1.gif width=3></td>
	  <td width=1>";
if($sets) $on = '_on'; else $on = '';
echo "
      <input class=button$on type=button value=\"Saved Sets\" onclick=\"hopto('./editfups.php?list=$list&sets=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$on'\" style=\"width: 100px\"><br>
     </td>
  	  <td><img src=1.gif width=1></td><!--
     <td width=1>";
if($more) $on = '_on'; else $on = '';
echo "
      <input class=button$on type=button value=\"More Options\" onclick=\"hopto('./editfups.php?list=$list&more=1');\" style=\"width: 104px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button$on'\"><br>
     </td> -->

	 </tr>
	</table>
	<img src=1.gif height=3><br>\n";
if(!$sets) $tx = "Followup Messages - List $list";
elseif($sets) $tx = "Saved Followup Sets";

echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
	<tr>
		<td class=table_head_bg_nopad>
			<table width=100% border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td><span class=table_head_text>$tx</span></td>
					<td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('followups')."');\">Help</a><br></td>
				</tr>
			</table>\n";

 // Build query
$cmd = "select id,list,subject,content,seq,del,cron from $ftable where $xcmd order by seq, id";

// get counts
$result = mysql_query($cmd);
$nfups = @mysql_num_rows($result);

// echo "CMD=$cmd nfups=$nfups<br>";
if(!$xxxxxxx){
 if($sets){
  echo "</td></tr></table>";
 } elseif($nfups>0){
  echo "
     </td>
    </tr>
    <tr>
     <td>\n";
 }

 $fstr = '';
 $cmd = "select id,seq from $ftable where $xcmd order by seq";
 $frows = mysql_query($cmd);
 if(@mysql_num_rows($frows)>0){
  $first = true;
  while(list($fi,$s)=mysql_fetch_row($frows)){
   if(!$first) $fstr .= ';';
   $fstr .= $s;
   $first = false;
  }
 }
 echo "<script language=javascript><!--
 var fstr='$fstr';
 if(fstr){
  var fupids=fstr.split(';');
 } else {
  var fupids='';
 }
 function dupeseq(os,oi){
  if(fupids){
   for(i=0;i<fupids.length;i++){
    if(fupids[i]!=os && fupids[i]==eval('f_frm'+oi+'.txtseq.value')){
     return true;
    }
   }
   return false;
  }
 }
--></script>";

 if($sets){
  echo "<form name=setfrm method=post>
<input type=hidden name=ssave value=1>
<input type=hidden name=list value=$list>
<input type=hidden name=sets value=1>
<input type=hidden name=sid value=$sid>
<input type=hidden name=txtname value=\"\">
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span id=sel><select style=\"width: 240px; margin: 2px 0px 0px 0px\" class=xbox name=sid onchange=\"hopto('./editfups.php?list=$list&sets=1&sid='+this.value);\">";
  $rows = mysql_query("select id,name from $fstable where 1 order by id");
  while(list($id,$name)=mysql_fetch_row($rows)){
   if($id==$sid) $sel = ' selected'; else $sel = '';
   echo "<option value=$id$sel>$name";
  }
  echo "</select></span><span id=nam><input name=xname type=text class=xbox style=\"width: 240px; margin: 2px 0px 0px 0px; padding-left: 4px\"  size=12><img src=1.gif width=3><input type=button class=button value=\"Save\" onclick=\"setfrm.txtname.value=xname.value; setfrm.submit();\" style=\"margin-bottom: 1px;\"><img src=1.gif width=2><input type=button class=button value=\"Cancel\" onclick=\"
 xname.value='';
 document.getElementById('sel').style.visibility='visible';
 document.getElementById('buttons').style.visibility='visible';
 document.getElementById('nam').style.visibility='hidden';
 sel.style.display='inline';
 buttons.style.display='inline';
 nam.style.display='none';

\" style=\"margin-bottom: 1px;\"></span><script language=javascript>
 document.getElementById('nam').style.visibility='hidden';
 nam.style.display='none';
</script><span id=buttons><img src=1.gif width=2><input type=button class=rbutton value=\"Rename\" onclick=\"
 document.getElementById('sel').style.visibility='hidden';
 document.getElementById('buttons').style.visibility='hidden';
 document.getElementById('nam').style.visibility='visible';
 sel.style.display='none';
 buttons.style.display='none';
 nam.style.display='inline';
 xname.focus();
\" style=\"margin-bottom: 1px;\"><img src=1.gif width=2><input type=button class=rbutton value=\"Delete\" onclick=\"if(!confirm('Are you SURE you want to delete this ENTIRE saved followup set?\\n\\nWarning: All of these followups will be lost!')) return false; else hopto('./editfups.php?list=$list&sets=1&sdel=1&sid=$sid')\"><img src=1.gif width=2><input type=button class=rbutton value=\"New\" onclick=\"if(confirm('Start a new saved followup set?')) hopto('./editfups.php?list=$list&sets=1&snew=1')\"><img src=1.gif width=2><input type=button class=rbutton value=\"Copy\" onclick=\"hopto('./editfups.php?list=$list&sets=1&sid=$sid&scopy=1')\"></span><br>
<img src=1.gif height=2><br>
</form>\n";
  if($nfups>0) echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
	<tr>
		<td>";
 } // end SET SELECTION
 if($sets){
  $rows = mysql_query("select id from $fstable where 1 limit 1");
  if(@mysql_num_rows($rows)==0){
   $nosets = 1;
   echo "</td></tr></table><img src=1.gif height=3><br><div align=center><span class=status_text>There are no saved sets - click \"New\" to create one!</span></div>";
  }
 }
 if($nfups==0 && !$nosets){
  if($sets) $tx = 'saved set';
  elseif(!$sets) $tx = 'list';
  echo "</td></tr></table><img src=1.gif height=3><br><div align=center><span class=status_text>There are no followups configured for this $tx.</span></div>";
 } elseif(!$nosets) {

 // Start Table
 echo "     <table width=100% border=0 cellspacing=0 cellpadding=3>
      <tr class=bg2>\n";
 if(!$sets) echo "      <td width=1><span class=table_head_col>List</span><br></td>";
 echo "       <td width=1><span class=table_head_col>Subject</span><br></td>
       <td width=25><span class=table_head_col>Seq</span><br></td>
       <td width=25><span class=table_head_col>Delay</span><br></td>
       <td width=25><span class=table_head_col>Cron</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
      </tr>";


 $first = 1;
 // defaults
 $reset_seq = '1';
 $reset_del = '0';
 if($sets && $sid && ((!$ssave && !$scopy && !$snew && !$save && !$del) || $sdel)){
  list($sname)=mysql_fetch_row(mysql_query("select name from $fstable where id = '".addslashes($sid)."';"));
  // $logtxt .= "Loaded set \"$sname\". ";
 }
 $lastseq = 0;
 while(list($id,$flist,$subject,$content,$seq,$del,$cron) = @mysql_fetch_row($result)){
  if($seq==$lastseq) $dupestyle = ' /* text-decoration: line-through; */ color: #cccccc'; else $dupestyle = '';
  $lastseq = $seq;
  if($first){ $reset_seq = $seq; $reset_del = $del; $first=false; }
  echo "
     <tr>
      <td align=center><form name=f_frm$id method=post action=$PHP_SELF>
       <input type=hidden name=quicksave value=1>
       <input type=hidden name=fid value=$id>\n";
  if(!$sets){
   echo "       <!-- <input class=xbox type=text name=txtlist size=1 value=$list> -->
       <select class=xbox name=txtlist onchange=\"if(confirm('Warning: You must manually eliminate duplicate Seq #s when moving followups between lists.\\n\\nAre you sure you want to move this followup to List '+f_frm$id.txtlist.value+'?')) f_frm$id.submit(); else { f_frm$id.txtlist.value='$flist'; return false; }\" style=\"width: 45px\">";

   $lrows = @mysql_query("select listnum from $ltable where 1 order by listnum");
   while(list($listnum) = @mysql_fetch_row($lrows)){
    if($flist==$listnum && $flist <> '0') $sel = " selected"; else $sel = "";
    echo "<option value=".$listnum.$sel.">$listnum\n";
   }
   echo "
       </select>
      </td>
     <td nowrap>\n";
	$subj_size = '308';
  } else $subj_size = '358';
  $eos = getlisteos();
  echo "    <input type=text class=xbox value=\"".htmlspecialchars($subject)."\" name=txtsubj size=63 style=\"width: ".$subj_size."px\"><br>
     </td>
     <td align=center>
      <input type=hidden name=oseq value=$seq>
	<input type=hidden name=fupid value=$id>
      <input type=hidden name=qfrearr value=\"1\">
      <input type=hidden name=insert value=\"\">
      <input type=hidden name=makedupe value=\"\">
      <input type=hidden name=normal value=\"\">
      <input class=xbox type=text name=txtseq size=2 style=\"width: 33px\" value=$seq><br>
     </td>
     <td align=center>
      <input class=xbox type=text name=txtdel size=2 style=\"width: 33px;$dupestyle\" value=$del><br>
     </td>
     <td align=left>
      <input class=xbox type=text name=txtcron size=2 style=\"width: 45px\" value=\"$cron\"><br>
     </td>
     <td width=1>
      <input type=hidden name=list value=$list>
	<input type=hidden name=sets value=$sets>
	<input type=hidden name=sid value=$sid>
      <input class=button type=submit name=qe value=Save onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\" onclick=\"
if(dupeseq(f_frm$id.oseq.value,'$id')){
	if(confirm('You are saving to a Seq # that already exists.  Do you want to INSERT this followup BEFORE the target Seq (#'+f_frm$id.txtseq.value+')"; if(!$sets) echo " and automatically re-arrange affected users"; echo "?\\n\\nOK = Yes, insert followup into sequence"; if(!$sets) echo " and re-arrange users"; echo "\\nCancel = No, create a duplicate Seq #')){
		f_frm$id.insert.value='1';
	} else {
		f_frm$id.makedupe.value='1';
	}
} else {
	f_frm$id.normal.value='1';
}
if(f_frm$id.txtseq.value > $eos){
 alert('You have specified a Seq # greater than the end of sequence (last Seq + 1).\\nYour followup will be placed at the end of sequence.');
}
return true;
\">
     </td></form>
     <td width=1>
      <input class=button type=button value=Edit onclick=\"hopto('./editfups.php?list=$list&edit=1&fid=$id"; if($sets) echo "&sets=1&sid=$sid"; echo "');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\">
     </td>
     <td width=1>
      <form name=fd method=post action=$PHP_SELF onSubmit=\"if (!confirm('Are you sure you want to delete followup #$seq?')) return false\">
      <input type=hidden name=dodel value=1>
      <input type=hidden name=delid value=$id>
      <input type=hidden name=list value=$list>
		<input type=hidden name=sets value=$sets>
		<input type=hidden name=sid value=$sid>
      <input class=button type=submit name=delit value=Delete onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\">
     </td></form>
    </tr>\n";
  }
 echo "</table>\n";
 }
}


echo "  </td>
 </tr>
</table>
<img src=1.gif height=2><br>
<script language=javascript><!--
 function ss_on(){
  ls_off(); "; if(!$sets) echo "ru_off();"; echo "
  // hide Save Set button, show textfield, save, and cancel
  document.getElementById('ss1').style.visibility='hidden'; ss1.style.display='none';
  document.getElementById('ss_button').style.visibility='hidden'; ss_button.style.display='none';
  document.getElementById('ls_button').style.visibility='hidden'; ls_button.style.display='none';
  document.getElementById('ss_other').style.visibility='visible'; ss_other.style.display='inline';
  sslsfrm.ss_name.focus();
 }
 function ss_off(){
  // hide textfield, save, and cancel - show save set button
  document.getElementById('ss1').style.visibility='visible'; ss1.style.display='inline';
  document.getElementById('ss_other').style.visibility='hidden'; ss_other.style.display='none';
  document.getElementById('ls_button').style.visibility='visible'; ls_button.style.display='inline';
  document.getElementById('ss_button').style.visibility='visible'; ss_button.style.display='inline';
 }
 function ls_on(){
  ss_off(); "; if(!$sets) echo "ru_off();"; echo "
  // hide Load Set button, show selectbox, load, delete, and cancel
  document.getElementById('ls_button').style.visibility='hidden'; ls_button.style.display='none';
  document.getElementById('ss_button').style.visibility='hidden'; ss_button.style.display='none';
  document.getElementById('ls_other').style.visibility='visible'; ls_other.style.display='inline';
 }
 function ls_off(){
  // hide selectbox, load, delete, and cancel, show Load Set button
  document.getElementById('ls_other').style.visibility='hidden'; ls_other.style.display='none';
  document.getElementById('ss_button').style.visibility='visible'; ss_button.style.display='inline';
  document.getElementById('ls_button').style.visibility='visible'; ls_button.style.display='inline';
 }\n";
 if(!$sets){
  echo "function ru_on(){
  ss_off(); ls_off();
  document.getElementById('ru1').style.visibility='hidden'; ru1.style.display='none';
  document.getElementById('ru_button').style.visibility='hidden'; ru_button.style.display='none';
  document.getElementById('ru_other').style.visibility='visible'; ru_other.style.display='inline';
 }
 function ru_off(){
  document.getElementById('ru1').style.visibility='visible'; ru1.style.display='inline';
  document.getElementById('ru_other').style.visibility='hidden'; ru_other.style.display='none';
  document.getElementById('ru_button').style.visibility='visible'; ru_button.style.display='inline';
 }\n";
 }
 echo "

--></script>\n";
if(!$nosets){
echo "
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td width=104 valign=top>
   <img src=1.gif height=1><br><input class=button type=button name=gosub value=\"Add Followup\" onclick=\"hopto('./editfups.php?list=$list&add=1"; if($sets) echo "&sets=1&sid=$sid"; echo "')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 104px\"><br>
  </td>\n";

if($sets) echo "
  <td width=3><img src=1.gif width=3></td>
  <td width=90>
   <img src=1.gif height=1><br><input type=button class=button value=\"Back to List\" style=\"width: 90px\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\"><br></td>
  <td>&nbsp;</td>\n";
 if(!$sets){
  echo "  <td width=3><img src=1.gif width=3><br></td>
  <td valign=top><span id=ru1><img src=1.gif height=1><br></span><form name=rufrm method=post action=".$_SERVER['PHP_SELF']."><input type=hidden name=list value=\"".htmlspecialchars($list)."\"><input type=hidden name=ru_submit value=\"\"><span id=ru_button ><input class=button type=button value=\"Reset Users\" onclick=\"ru_on();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"></span><span id=ru_other style=\"display: none\"><span class=table_inside_small>Set to</span><img src=1.gif width=3><span class=table_inside_small>Seq</span><img src=1.gif width=3><input type=text name=ru_seq class=xbox style=\"width: 33px; vertical-align: top\" value=\"$reset_seq\"><img src=1.gif width=3><span class=table_inside_small>Delay</span><img src=1.gif width=3><input type=text name=ru_del class=xbox style=\"width: 33px\" value=\"$reset_del\"><img src=1.gif width=3><input class=button type=button value=\"Go\" onclick=\"if(rufrm.ru_seq.value != parseInt(rufrm.ru_seq.value)){ alert('Seq # must be an integer greater than 0'); return false; } if(rufrm.ru_seq.value<='0'){ alert('Please enter a Seq # greater than 0.'); return false; } if(rufrm.ru_del.value != parseInt(rufrm.ru_del.value)){ alert('Delay must be an integer 0 or greater.'); return false; } if(rufrm.ru_del.value<'0'){ alert('Please enter a Delay 0 or greater.'); return false; } if(!confirm('Are you sure you want to set all users on the current list to Seq '+rufrm.ru_seq.value+' Delay '+rufrm.ru_del.value+'?')){ return false; } rufrm.ru_submit.value='1'; rufrm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 36px;\"><img src=1.gif width=3><input class=button type=button value=\"Cancel\" onclick=\"ru_off();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"></span><br></td></form> \n";

  echo "
  <td align=right valign=top>
   <span id=ss1><img src=1.gif height=1><br></span>
   <form name=sslsfrm method=post action=".$_SERVER['PHP_SELF']."><input type=hidden name=sets value=$sets><input type=hidden name=sid value=$sid><input type=hidden name=list value=\"".htmlspecialchars($list)."\"><input type=hidden name=ss_submit value=\"\"><input type=hidden name=ls_submit value=\"\"><input type=hidden name=ls_delete value=\"\"><span id=ss_button><input class=button type=button value=\"Save Set\" onclick=\"ss_on();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 70px\"><img src=1.gif width=3></span><span id=ss_other style=\"display: none\"><span class=table_inside_small>Name</span><img src=1.gif width=3><input type=text class=xbox name=ss_name style=\"width: 100px;\"><img src=1.gif width=3><input class=button type=button value=\"Save\" onclick=\"if(!sslsfrm.ss_name.value){ alert('Please enter a name for this set of messages to be saved as.'); sslsfrm.ss_name.focus(); return false; } if($nfups==0){ alert('There are no followups to save!'); return false; } sslsfrm.ss_submit.value='1'; sslsfrm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 50px; margin-top: 1px;\"><img src=1.gif width=3><input class=button type=button value=\"Cancel\" onclick=\"ss_off();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px; margin-top: 1px;\"></span><span id=ls_button><input class=button type=button value=\"Load Set\" onclick=\"ls_on();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 70px\"></span><span id=ls_other style=\"display: none\"><span class=table_inside_small>Choose</span><img src=1.gif width=3><select class=xarea name=ls_id style=\"width: 120px; vertical-align: bottom\">";
  $srows = mysql_query("select id,name from $fstable where 1 order by id;");
  if(@mysql_num_rows($srows)>0){
   while(list($i,$n)=mysql_fetch_row($srows)){
    echo "<option value=\"$i\">".htmlspecialchars($n);
   }
  } else {
   echo "<option value=\"\">None";
  }
  echo "</select><img src=1.gif width=3><input class=button type=button value=\"Load\" onclick=\"if(sslsfrm.ls_id.value==''){ alert('No set to load.'); return false; } if(!confirm('This will DELETE ALL FOLLOWUPS ON THIS LIST.  If you have not saved these followups as a set of their own they will be LOST PERMANENTLY.  Are you sure you want to REPLACE followups on the current list with the ones from the selected saved set?')){ return false; } sslsfrm.ls_submit.value='1'; sslsfrm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 50px\"><img src=1.gif width=3><input class=button type=button value=\"x\" onclick=\"if(sslsfrm.ls_id.value==''){ alert('No set to delete'); return false; } if(!confirm('Are you sure you want to delete the selected saved set of followups?  You will not be able to load this set of messages again! (This will not affect followups already applied your lists).')){ return false; } sslsfrm.ls_delete.value='1'; sslsfrm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 17px\"><img src=1.gif width=3><input class=button type=button value=\"Cancel\" onclick=\"ls_off();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"></span><br>
  </td>\n";

 }
 echo " </tr></form>
</table>";
}

if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text style=\"color: navy\">$logtxt</span><br>";
if($focus) echo $focus;

echo "</td></tr></table>\n";
echo "<script language=javascript>window.status='".addslashes($logtxt)."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->";


} // end pw check

function checkcron($tc){
 $ar = explode(' ',$tc);
 $fail = false;
 if(count($ar)<>3){
  $fail = 1;
 } else for($i=0;$i<3;$i++){
  if(strpos(' '.$ar[$i],'*')>0 && strlen($ar[$i])==1){
   // good
  } else {
   if(!is_numeric(str_replace(',','',str_replace('-','',$ar[$i])))) $fail = 1;
   if(substr_count($ar[$i],'-,')<>0) $fail = 1;
   if(substr_count($ar[$i],',,')<>0) $fail = 1;
   if($ar[$i][0] == ',') $fail = 1;
   if($ar[$i][0] == '-') $fail = 1;
   if(substr($ar[$i],strlen($ar[$i])-1,1)==',') $fail = 1;
   if(substr($ar[$i],strlen($ar[$i])-1,1)=='-') $fail = 1;
  }
 }
 if($fail) return 1; else return false;
}


// move
function movefup($from,$to){
 global $list; global $ftable; global $utable; global $txtseq; global $txtdel; global $txtcron; global $logtxt; global $xcmd; global $sets; global $makedupe; global $insert; global $normal;
 // get from seq from $from/fupid
 $fupid = $from;
 list($from)=mysql_fetch_row(mysql_query("select seq from $ftable where id = '".addslashes($fupid)."';"));
 $fr = mysql_query("select id from $ftable where seq = '".addslashes($from)."' and $xcmd;") or die(mysql_error());
 if(@mysql_num_rows($fr)>1) $from_multi = 1; else $from_multi = '';

 if($DEBUG) echo "movefup():txtseq=$txtseq fromseq=$from fupid=$fupid from_multi=$from_multi<br>";

 //$fr = mysql_query("select id from $ftable where seq = '".addslashes($from)."' and $xcmd;");

 fixnonexist();
 // check whether inserting or making a dupe or normal
 // if(!$makedupe) $insert = 1; else $insert = '';
 if($DEBUG) echo "insert=$insert makedupe=$makedupe normal=$normal<br>";
 $eos = getlisteos();
 if($DEBUG) echo "EOS=$eos<br>";
 // $eos = getlisteos();

 if($to<$from){
  if(!$from_multi){
   if($DEBUG) echo "!from_multi<br>";
   list($ns,$nd) = nextfup($from);
   if(!$sets) mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where cseq = '".addslashes($from)."' and list = '".addslashes($list)."';");
   // rearrange users and seq #s
   if($normal){
    // DONE
    // do nothing
    if($DEBUG) echo "normal mode - do nothing<br>";
    // if not last fup decrease fups above it
    if(!islastseq($from)){
     if($DEBUG) echo "!islastseq, decreasing seq+users above<br>";
     $fr = mysql_query("select seq from $ftable where seq > '".addslashes($from)."' and $xcmd group by seq order by seq desc;");
     if(@mysql_num_rows($fr)>0){
      while(list($s)=mysql_fetch_row($fr)){
       $nseq = $s+1;
       if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
       mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
       if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
      }
     }
    }
   } elseif($insert){
   // DONE
    if($DEBUG) echo "insert mode<br>";
    $fr = mysql_query("select seq from $ftable where seq < '".addslashes($from)."' and seq >= '".addslashes($to)."' and $xcmd group by seq order by seq desc;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s+1;
      if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
   } elseif($makedupe){
    // DONE
    if($DEBUG) echo "makedupe mode<br>";
    $fr = mysql_query("select seq from $ftable where seq  > '".addslashes($from)."' and $xcmd group by seq order by seq;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s-1;
      if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
   }
  } elseif($from_multi){
   if($DEBUG) echo "from_multi<br>";
   if($normal){
    // do nothing
    if($DEBUG) echo "normal mode - do nothing<br>";
   } else
   if($insert){
    if($DEBUG) echo "insert<br>";
    // >= to + 1
    $fr = mysql_query("select seq from $ftable where seq  >= '".addslashes($to)."' and $xcmd group by seq order by seq desc;") or die(mysql_error());
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s+1;
      if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
    // $to--;
   } elseif($makedupe){
    if($DEBUG) echo "makedupe- no rearranging..<br>";
    // no rearranging - this actually will never be called
   }
  }
  // mysql_query("update $ftable set seq = '".addslashes($to)."' where id = '".addslashes($fupid)."';");

 } elseif($to>$from){
  if(!$from_multi){
   if($DEBUG) echo "!from_multi<br>moving users out of hole<br>";
   // move users out of the hole
   list($ns,$nd) = nextfup($from);
   if(!$sets) mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where cseq = '".addslashes($from)."' and list = '".addslashes($list)."';");

   if($normal){
    $fr = mysql_query("select seq from $ftable where seq > '".addslashes($from)."' and seq < '".addslashes($to)."' and $xcmd group by seq order by seq;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s-1;
      if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
    if($to>$eos){ $to = $eos; }
    $to--;
    // if(!islastseq($from)){ echo "!islastseq<br>"; $to--; }
    // $to--;
    if($DEBUG) echo "final to=$to<br>";
   } elseif($insert){
    if($DEBUG) echo "insert mode<br>";
    $fr = mysql_query("select seq from $ftable where seq > '".addslashes($from)."' and seq < '".addslashes($to)."' and $xcmd group by seq order by seq;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s-1;
      if($DEBUG) echo "CMD=update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;<br>";
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
    $to--;
   } elseif($makedupe){
    if($DEBUG) echo "makedupe mode<br>";
    $fr = mysql_query("select seq from $ftable where seq > '".addslashes($from)."' and $xcmd group by seq order by seq;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s-1;
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
    $to--;
   }
  } elseif($from_multi){
   if($DEBUG) echo "from_multi, no hole to fill<br>";
   // no hole
   if($normal){
    if($DEBUG) echo "normal mode<br>";
    if($to>$eos){ if($DEBUG) echo "to>eos"; $to = $eos; }
   } elseif($insert){
    if($DEBUG) echo "insert mode<br>";
    $fr = mysql_query("select seq from $ftable where seq >= '".addslashes($to)."' and $xcmd group by seq order by seq desc;");
    if(@mysql_num_rows($fr)>0){
     while(list($s)=mysql_fetch_row($fr)){
      $nseq = $s+1;
      mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
      if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
     }
    }
   } elseif($makedupe){
    if($DEBUG) echo "makedupe - do nothing...<br>";
   }
  }
 }
 if($DEBUG) echo "to=$to eos=$eos<br>";
/*  if($from==$eos-1){
   $eos = $from+1;
 } */
 // if($insert){ echo "insert. to=$to "; $to = $to-1; echo "to-1 = $to<br>"; }
 mysql_query("update $ftable set seq = '".addslashes($to)."' where id = '".addslashes($fupid)."';");
 if($makedupe || $normal) mysql_query("update $ftable set del = '".addslashes($txtdel)."', cron = '".addslashes($txtcron)."' where $xcmd and seq = '".addslashes($to)."';");
 $txtseq = $to;

}

// insert
function addfup($tl,$ts,$tc,$th,$tf,$to,$td,$tr){
 global $list; global $utable; global $ftable; global $txtseq; global $sets; global $xcmd; global $sid;
 global $makedupe; global $insert; global $normal;
 if($sets) $tl = '0';

 // insert
 if($insert){
  $fr = mysql_query("select seq from $ftable where seq >= '".$to."' and $xcmd group by seq order by seq desc;");
  if(@mysql_num_rows($fr)>0){
   while(list($s)=mysql_fetch_row($fr)){
    $nseq = $s+1;
    mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
    if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
   }
  }
 }

 $eos = getlisteos();
 if($to>=$eos) $nseq = $eos; else $nseq = $to;
 $txtseq = $nseq;
 mysql_query("insert into $ftable values('null','','".addslashes($tl)."','".addslashes($ts)."','".addslashes($tc)."','".addslashes($th)."','".addslashes($tf)."','".addslashes($nseq)."','".addslashes($td)."','".addslashes($tr)."','$sid')") or die(mysql_error());
 $fid = mysql_insert_id();

 if($makedupe){
   // update all with new cron and delay
   mysql_query("update $ftable set del = '".addslashes($td)."', cron = '".addslashes($tr)."' where $xcmd and seq = '".addslashes($nseq)."';");
 }
 return $fid;
}

// delete
function delfup($seq){
 global $list; global $utable; global $ftable; global $logtxt; global $sets; global $xcmd;
 list($ns,$nd) = nextfup($seq);
 if(!$sets) mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where cseq = '".addslashes($seq)."' and list = '".addslashes($list)."';");
 $fr = mysql_query("select seq from $ftable where seq > '".addslashes($seq)."' and $xcmd order by seq;");
 if(@mysql_num_rows($fr)>0){
  while(list($s)=mysql_fetch_row($fr)){
   $nseq = $s-1;
   mysql_query("update $ftable set seq = '$nseq' where seq = '$s' and $xcmd;");
   if(!$sets) mysql_query("update $utable set cseq = '$nseq' where cseq = '$s' and list = '".addslashes($list)."';");
  }
 }
 fixeos();
}

function getlisteos($exclude_fupid=''){
 global $list; global $ftable; global $xcmd;
 if($exclude_fupid) $escmd = " and id <> '$exclude_fupid'";
 $frows = mysql_query("select seq from $ftable where $xcmd$escmd order by seq desc limit 1;");
 if(@mysql_num_rows($frows)>0){
  list($eos)=mysql_fetch_row($frows);
  $eos++;
 } else {
  $eos = '1';
 }
 return $eos;
}

function fixeos(){
 global $utable; global $list; global $sets;
 if(!$sets) return false;
 $eos = getlisteos();
 mysql_query("update $utable set cseq = '$eos' where cseq >= '$eos' and list = '".addslashes($list)."';");
}

function fixnonexist(){
 global $ftable; global $utable; global $list; global $txtseq; global $sets;
 if(!$sets) return false;
 $eos = getlisteos();
 $fups = array();
 $frows = mysql_query("select id,seq from $ftable where $xcmd order by seq");
 if(@mysql_num_rows($frows)>0){
  $first = 1;
  $fstr = '';
  while(list($fup)=mysql_fetch_row($frows)){
   $fstr .= " and cseq <> '$fup'";
  }
  $urows = mysql_query("select id,cseq from $utable where list = '".addslashes($list)."'$fstr;");
  if(@mysql_num_rows($urows)>0){
   while(list($id,$cs)=mysql_fetch_row($urows)){
    $frow = mysql_query("select id from $ftable where seq = '$cs' and $xcmd;");
    if(@mysql_num_rows($frow)==0 && $cs <> $eos){
     $frow = mysql_query("select id,seq,del from $ftable where seq > '$cs' and $xcmd order by seq limit 1;");
     if(@mysql_num_rows($frow)){
      list($i,$s,$d)=mysql_fetch_row($frow);
      $cmd = "update $utable set cseq = '$s', cdel = '$d' where id = '$id';";
      mysql_query($cmd);
      } else {
      $cmd = "update $utable set cseq = '$eos', cdel = '0' where id = '$id';";
      mysql_query($cmd);
     }
    }
   }
  }
 }
}

function nextfup($t){
 global $list; global $ftable; global $xcmd;
 $frows = mysql_query("select seq,del from $ftable where seq > '$t' and $xcmd order by seq limit 1;");
 if(@mysql_num_rows($frows)>0){
  list($ns,$nd)=mysql_fetch_row($frows);
 } else {
  $ns = getlisteos();
  $nd = '0';
 }
 return array($ns,$nd);
}

function hasmultiseq($sq){
 global $list; global $ftable; global $xcmd;
 $frows = mysql_query("select id,seq from $ftable where $xcmd and seq = '".addslashes($sq)."';");
 if(@mysql_num_rows($frows)>1) return true;
 else return false;
}
function islastseq($sq){
 global $list; global $ftable; global $xcmd;
 $frows = mysql_query("select seq from $ftable where $xcmd and seq = '".addslashes($sq)."' order by seq desc limit 1;");
 list($lastseq)=mysql_fetch_row($frows);
 if($lastseq==$sq) return true;
 return false;
}
// EOF
?>
