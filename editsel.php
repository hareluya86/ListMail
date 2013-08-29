<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");
include('./selfunc.php');

ini_set("max_execution_time","0");
$islogged = islogged();
// echo "<html><body><b>LIST=$list,UEDIT=$uedit,UADD=$uadd,ADDED=$added<br></b>";

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

 // echo "edit=$edit rsid=$rsid add=$add save=$save add_rule=$add_rule add_subrule=$add_subrule add_subrule_type=$add_subrule_type add_subrule_num=$add_subrule_num rem_rule=$rem_rule show_query=$show_query<br>";
 // edit sub-pages
 if($edit || $add){
 
  if($add_rule) $xnr = $nr - 1; else $xnr = $nr;
  for($h=0;$h<$xnr;$h++){
   $month[$h] = ${'month'.$h};
   $day[$h] = ${'day'.$h};
   $year[$h] = ${'year'.$h};
  }
  
  // add/edit save
  if(($add && $save) || ($edit && $save) && !$add_rule && !$rem_rule){
   // if add, add the select
   if($x_uniq_em<>'1') $x_uniq_em = '';
   if($add){
    // echo "ADD save<br>";
    $cmd = "insert into $setable values('','".addslashes($txtname)."','".addslashes($txtdescr)."', '$x_uniq_em')";
    // echo "CMD=$cmd<br>";
    mysql_query($cmd) or die(mysql_error());
    $rsid = mysql_insert_id();
   }
   elseif($edit){
    // echo "EDIT save<br>";
    mysql_query("update $setable set name = '".addslashes($txtname)."', descr = '".addslashes($txtdescr)."', fdupe = '$x_uniq_em' where id = '".addslashes($rsid)."'") or die(mysql_error());
   }
   // delete and re-insert all rules
   $rsid = addslashes($rsid);  
   $cmd = "delete from $sedtable where rsid = '$rsid'";
   // echo "CMD=$cmd<br>";
   mysql_query($cmd) or die(mysql_error());
   if(!$custom){
    for($l=0;$l<$nr;$l++){
     if($target[$l]=='all') $target[$l]='0';
     if($sub[$l]<>'1' && $sub[$l]<>'2') $sub[$l] = '0';
/*    if($qtype=='a' || $qtype=='e') $qordate[$k] = 'search';
    elseif($qtype=='d') $qordate[$k] = 'date';
    elseif($qtype=='A') $qordate[$k] = 'all';
  */  
     if($qordate[$l]=='more'){
      $q_type[$l] = 'm';
     }
     if($qordate[$l]=='search'){
      // echo "qtype=".$q_type[$l]."<br>";
     }

     if($qordate[$l]=='date'){
      // echo "setting date.. date=$year[$l]-$month[$l]-$day[$l]";
      $q_date[$l] = "$x_d_type[$l]$year[$l]-$month[$l]-$day[$l]";
      $q_for[$l] = '';
      $q_type[$l] = 'd';
     }
	
     // echo "inserting<br>";
     $cmd = "insert into $sedtable values('',
    '".addslashes($sub[$l])."',
    '".addslashes($rsid)."',
    '".addslashes($cond[$l])."',
    '".addslashes($stat[$l])."',
    '".addslashes($target[$l])."',
    '".addslashes($q_type[$l])."',	
    '".addslashes($field[$l])."',
    '".addslashes($q_for[$l])."',
    '".addslashes($q_date[$l])."')";
     // echo "INSERTCMD=$cmd<br>";
     mysql_query($cmd) or die(mysql_error());
    }
   } else {
    $cmd = "insert into $sedtable values('','!','".addslashes($rsid)."','','','','','','".addslashes(clean_sql($custom_q))."','');";
    mysql_query($cmd) or die(mysql_error());
   }
   $logtxt = 'Selection saved.';
   $add=''; $edit=1;
  } // end edit or add save !add_rule !rem_rule
  

  // build date arrays..
  // echo "building date arrays?!?<br>";
  // $month=array(); $day=array(); $year=array();

  // show query
  if($show_query){
   $qry = make_rquery();
   $error = '';
   $qrows = @mysql_query($qry) or $error = mysql_error();

   $logtxt .= $qry . '<br>';
   if($error) $logtxt .= '<img src=1.gif height=4><br><font color=black>MySQL Error: </font><font color=red>' . $error . '</font><br>'; else $logtxt .= '<img src=1.gif height=4><br><b>'.@mysql_num_rows($qrows).'</b> users matched';
  }
  // edit & add main page

  if($x_uniq_em) $xuniqemchk = ' checked'; else $xuniqemchk = '';  
  
  if(!$add && !$add_rule && !$rem_rule && !$show_query){
   // assume edit of existing ruleset
   // echo "FETCHing data rsid=$rsid<br>";
   $rows = mysql_query("select name,descr,fdupe from $setable where id = '$rsid'");
   list($txtname,$txtdescr,$x_uniq_em)=mysql_fetch_row($rows);
   if($x_uniq_em) $xuniqemchk = ' checked'; else $xuniqemchk = '';  
   // echo "XUE=$x_uniq_em<br>";
   //   if($x_uniq_em<>'1') $x_uniq_em='';

   // echo "txtname=$txtname<br>";
   // echo "txtdescr=$txtdescr<br>";
   
   // check if a custom query
   $rows = mysql_query("select rsub,q from $sedtable where rsid = '$rsid' limit 1");
   list($rsub,$q)=mysql_fetch_row($rows);
   if($rsub=='!'){ $custom = 1; $custom_q = $q; }// else $custom = '';
   
   if(!$custom){
    $rows = mysql_query("select id,rsub,rsid,cond,stat,list,qtype,field,q,qdate from $sedtable where rsid = '$rsid' order by id");
    $nr = @mysql_num_rows($rows);
    $k=0;
    if($nr>0){
     $cond=''; $stat=''; $sub=''; $target=''; $q_type=''; $field='';
     $q_for=''; $qordate=''; $year=''; $month=''; $dat=''; $x_d_type='';
     while(list($id,$rsub,$rid,$rcond,$rstat,$rlist,$qtype,$rfield,$q,$qdate)=mysql_fetch_row($rows)){
      // echo "Loaded data: ($rsub,$rid,$rcond,$rstat,$rlist,qtype=$qtype,$rfield,$q,$qdate)<br>";
      $cond[$k] = $rcond;
      $stat[$k] = $rstat;
      $sub[$k] = $rsub;
      if($rlist=='0') $rlist = 'all';
      $target[$k] = $rlist;

      $q_type[$k] = $qtype;
      $field[$k] = $rfield;
      $q_for[$k] = $q;
      if($qtype=='d') $qordate[$k] = 'date';
      elseif($qtype=='m') $qordate[$k] = 'more';
      else $qordate[$k] = 'search';

      // echo "qtype=$qtype<br>";
      list($y,$m,$d)=dateparse(substr($qdate,1,10),'break');
      $year[$k] = $y;
      $month[$k] = $m;
      $day[$k] = $d;
      $x_d_type[$k]=substr($qdate,0,1);
      $k++;
     }
    } else {
     // no rules ?? offer message and delete...
     echo "Error, no rules.<br>";
    }
    // end !custom
   } else {
    // custom
    // echo "customx";
   }
  } elseif(!$custom) {
   // add, set default data, create $rsid
   if($add && !$add_rule && !$rem_rule && !$show_query){
    // echo "add_rule=$add_rule<br>rem_rule=$rem_rule<br>";
    // echo "setting defaults<br>";
    
    $target[0] = firstlist();
    $sub[0] = '0';
    $q_type[0] = 'a';
    $field[0] = 'f';
    $q_for[0] = '';
    $stat[0] = 'a';
    $cond[0] = 'a';
    $day[0] = date("d");
    $month[0] = date("m");
    $year[0] = date("Y");
    $qordate[0] = 'more';
    $x_d_type[0] = 'a'; 
    // echo "add..<br>";print_r($month);
    // create unique rsid
   }
   elseif($add_rule){
    if($add_subrule_type == '1' || $add_subrule_type == '2') $logtxt = 'Sub-rule'; else $logtxt = 'Rule';
    $logtxt .= " added.  Note: Changes not saved.";
    // echo "nr=$nr<br>";
    // echo "adding new rule, setting defaults<br>";
    if(!$add_subrule_num && $add_subrule_num <> '0'){
     $rn = $nr-1;
    } else {
     $rn = $add_subrule_num+1;
    }
    if(!$add_subrule_type || $add_subrule_type == '0') $xsub = '0';
    elseif($add_subrule_type == '1') $xsub = '1';
    elseif($add_subrule_type == '2') $xsub = '2';
    // echo "xsub=$xsub<br>";
    
    array_splice($sub, $rn, count($sub), array_merge(array($xsub), array_slice($sub, $rn)));
    array_splice($target, $rn, count($target), array_merge(array(firstlist()), array_slice($target, $rn)));
    array_splice($q_type, $rn, count($q_type), array_merge(array('a'), array_slice($q_type, $rn)));
    array_splice($field, $rn, count($field), array_merge(array('f'), array_slice($field, $rn)));
    array_splice($q_for, $rn, count($q_for), array_merge(array(''), array_slice($q_for, $rn)));
    array_splice($stat, $rn, count($stat), array_merge(array('a'), array_slice($stat, $rn)));
    array_splice($cond, $rn, count($cond), array_merge(array('a'), array_slice($cond, $rn)));
    array_splice($day, $rn, count($day), array_merge(array(date("d")), array_slice($day, $rn)));
    array_splice($month, $rn, count($month), array_merge(array(date("m")), array_slice($month, $rn)));
    array_splice($year, $rn, count($year), array_merge(array(date("Y")), array_slice($year, $rn)));
    array_splice($qordate, $rn, count($qordate), array_merge(array('more'), array_slice($qordate, $rn)));
    array_splice($x_d_type, $rn, count($x_d_type), array_merge(array('a'), array_slice($x_d_type, $rn)));
   }
   elseif($rem_rule){
    // error message set above
   }
  }
  if($custom) $ci = ' SQL'; else $ci = '';
  adminheader('LM: User Select','Edit Selection'.$ci,'selects');

  echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1><script language=javascript><!--
 function checkyear(year) {
  return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
 }
--></script>\n";

  listmenu($list,'selects');

  echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg_nopad>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Edit Selection$ci</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('selection')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
   </table>
   <img src=1.gif height=4><br>
   <table border=0 cellspacing=0 cellpadding=0><tr><td valign=top>
   <form name=edit_frm method=post>
   <span class=table_inside_small>Short Name:<br><input name=txtname type=text class=xbox size=20 maxlength=255 value=\"".htmlspecialchars($txtname)."\"></span></td><td width=10><img src=1.gif width=10><br></td><td valign=top><span class=table_inside_small>Description:<br><input name=txtdescr type=text class=xbox size=60 maxlength=255 value=\"".htmlspecialchars($txtdescr)."\"><br></span>
   </td></tr></table>
  </td>
 </tr>
</table>\n";
if(!$custom){
 echo "<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td><input type=checkbox class=chkbox value=1 name=x_uniq_em$xuniqemchk style=\"width: 13px\"><span class=table_inside_small>&nbsp;Filter Duplicate Email Addresses &nbsp;(Not recommended if only querying a single list)</span><br></td>
 </tr>
</table>\n";
}
echo "
<img src=1.gif height=7><br>\n";

selection();

if($add){
 $saveadd = 'Save / Add';
 $saveadd_size = '90';
 $back = 'Back to Main / Cancel';
 $back_size = '140';
} else {
 $saveadd = 'Save Selection';
 $saveadd_size = '110';
 $back = 'Back to Main';
 $back_size = '90';
}
if($custom){
 $showq = 'Test';
} else {
 $showq = 'Show';
}

echo "<img src=1.gif height=2><br><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><input class=button type=button value=\"$saveadd\" onclick=\"edit_frm.save.value='1'; edit_frm.submit(); return true;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$saveadd_size."px\"><img src=1.gif width=3><input class=button type=button value=\"$showq Query\" onclick=\"edit_frm.show_query.value='1'; edit_frm.submit(); return true;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\"><img src=1.gif width=3><input class=button type=button value=\"$back\" onclick=\"hopto('./editsel.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$back_size."px\"><br></td><td align=right><img src=1.gif width=3>"; if(!$custom) echo "<input class=button type=button value=\"Custom Query\" onclick=\"if(confirm('Setting up a custom query discards existing rules. Continue?')){ edit_frm.custom.value='1'; edit_frm.submit(); return true; } else { return false; }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\">"; echo "<br></td></tr></table>\n";
if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text style=\"color: navy\">$logtxt</span><br>";
echo "</td></tr></table>
<script language=javascript>window.status='".addslashes(str_replace('<br>','',str_replace('<b>','',str_replace('</b>','',$logtxt))))."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->";
// </td></tr></table> </body></html>";
  exit;
 } // edit
 
 

// default main page

// quick save
if($quicksave){
 // $rsid $logtxt
 $txtname = addslashes($txtname);
 $txtdescr = addslashes($txtdescr);
 $cmd = "update $setable set name = '$txtname', descr = '$txtdescr' where id = '$rsid'";

 // check for blank subject or seq #
 if(!$txtname || $dupe){
  $logtxt .= "No name or description, not saved. ";
 }  else {
//  if(!$demo){
   mysql_query($cmd);
   $logtxt .= "Selection \"".stripslashes($txtname)."\" saved. ";
/*  } else {
   $logtxt .= "Cannot save in demo. ";
  } */
 }
 $txtname = stripslashes($txtname);
 $txtdescr = stripslashes($txtdescr);
}
// quick del
if($quickdel){
  // $rsid $logtxt
  mysql_query("delete from $setable where id = '".addslashes($delid)."'");
  mysql_query("delete from $sedtable where rsid = '".addslashes($delid)."'");
  $logtxt = 'Selection deleted.';
}

adminheader('LM: User Selection','User Selection','');

$nrules = @mysql_num_rows(@mysql_query("select id from $setable where 1"));

echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

listmenu($list,'selects');

echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>\n";

if ($nrules == '0'){
 echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
 <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>User Selection</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('selection')."');\">Help</a><br></td></tr></table>
</td></tr></table>
<img src=1.gif height=5><br><div align=center><span class=status_text>There are no selections.</span></div>";
} else {
echo "   <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>User Selection</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('selection')."');\">Help</a><br></td></tr></table>
     </td>
    </tr>
    <tr>
     <td>
     <table width=100% border=0 cellspacing=0 cellpadding=3>
      <tr class=bg2>
       <td width=1><span class=table_head_col>Short&nbsp;Name</span><br></td>
       <td width=1><span class=table_head_col>Description</span><br></td>
       <td width=1><span class=table_head_col>#&nbsp;Rules</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
       <td><span class=table_head_col>&nbsp;</span><br></td>
      </tr>";

$cmd="select id,name,descr from $setable where 1 order by id";
$result = mysql_query($cmd);
while(list($rsid,$name,$descr) = @mysql_fetch_row($result)){
 $rrow = mysql_query("select id,rsub from $sedtable where rsid = '$rsid'");
 $rnum = @mysql_num_rows($rrow);
 if($rnum>0){
  list($xid,$rsub)=mysql_fetch_row($rrow);
  if($rsub=='!') $rnum = 'Custom';
 }
 echo "
     <tr>
      <td align=left width=1><form name=rs_frm$rsid method=post action=$PHP_SELF>
       <input type=hidden name=quicksave value=1>
       <input type=hidden name=rsid value=$rsid>
       <input type=text class=xbox value=\"".htmlspecialchars($name)."\" name=txtname size=25 maxlength=255>
      </td>
      <td align=left width=1>
       <input class=xbox type=text name=txtdescr size=48 maxlength=255 value=\"".htmlspecialchars($descr)."\">
       <input type=hidden name=list value=$list>
      </td>
      <td align=center><span class=table_inside_small>$rnum</span><br></td>
      <td width=1>
       <input class=button type=submit name=qe value=Save onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\"><br>
	   </form>
      </td>
      <td width=1>
       <input class=button type=button value=Edit onclick=\"hopto('./editsel.php?list=$list&edit=1&rsid=$rsid');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\">
      </td>
	  
      <td width=1>
       <input class=button type=button value=Delete onclick=\"if(confirm('Are you sure you want to delete this selection?')){ hopto('./editsel.php?list=$list&quickdel=1&delid=$rsid'); }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\">
      </td>
     </tr>\n";
 }
 echo "</table>\n";
} // end nfups != 0

echo "  </td>
 </tr>
</table>
<img src=1.gif height=4><br>
<input style=\"width: 120px;\" class=button type=button name=gosub value=\"Add Selection\" onclick=\"hopto('./editsel.php?list=$list&add=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 110px\"><br>";

if($logtxt) echo "<img src=./1.gif height=5><br><span class=status_text style=\"color: navy\">$logtxt</span><br>";

echo "</td></tr></table>\n";
echo "<script language=javascript>window.status='".addslashes(str_replace('<br>','',str_replace('<b>','',str_replace('</b>','',$logtxt))))."';</script>
</body>
</html>
<!-- ListMail (c) 2001 Dean Wiebe -->";
 
} // isadmin
?>
