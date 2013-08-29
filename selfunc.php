<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */
   
function selection(){
 global $link; global $ltable; global $list; global $rsid; global $edit; global $add; global $save; global $show_query; global $nr; global $add_rule; global $rem_rule; global $rem_num; global $add_subrule_type; global $add_subrule_num; global $sub; global $target; global $q_type; global $field; global $q_for; global $stat; global $cond; global $day; global $month; global $year; global $qordate; global $x_d_type; global $logtxt; global $custom; global $custom_q;

 if($rem_rule){
  // echo "splicing<br>";
  // echo "removing rule #$rem_num : sub value was $sub[$rem_num]<br>";
  $rem_sub=$sub[$rem_num];
  if(!$rem_sub) $rem_sub = '0';
   
  // echo "rem sub=$rem_sub<br>";
  array_splice($sub,$rem_num,1);
  array_splice($target,$rem_num,1);
  array_splice($q_type,$rem_num,1);
  array_splice($field,$rem_num,1);
  array_splice($q_for,$rem_num,1);
  array_splice($stat,$rem_num,1);
  array_splice($cond,$rem_num,1);
   
  array_splice($qordate,$rem_num,1);
  array_splice($x_d_type,$rem_num,1);
  array_splice($month,$rem_num,1);
  array_splice($day,$rem_num,1);
  array_splice($year,$rem_num,1);
  // echo "rule $rem_num spliced (must check same #)<br>";
  // echo nl2br(print_r($sub));
  $done = '';
  while(!$done){
   // if this is a 0, check for and remove 1's and 2's below
   // if this is a 1, check for and remove 2's below
   $rsub_sub = $sub[$rem_num];
   if(!$rsub_sub) $rsub_sub = '0';
   // echo "checking rule #$rem_num : rsub_sub = $rsub_sub<br>";
   if(( $rem_sub=='1' && $rsub_sub=='2') || ($rem_sub=='0' && ($rsub_sub=='2' || $rsub_sub=='1'))){
    // echo "removing rsub!!<br>";
    array_splice($sub,$rem_num,1);
    array_splice($target,$rem_num,1);
    array_splice($q_type,$rem_num,1);
    array_splice($field,$rem_num,1);
    array_splice($q_for,$rem_num,1);
    array_splice($stat,$rem_num,1);
    array_splice($cond,$rem_num,1);
   
    array_splice($qordate,$rem_num,1);
    array_splice($x_d_type,$rem_num,1);
    array_splice($month,$rem_num,1);
    array_splice($day,$rem_num,1);
    array_splice($year,$rem_num,1);
    // echo nl2br(print_r($sub));
    $nr = $nr - 1;
   } else {
    $done = 1;
   }
  }
		 
  $nr = $nr - 1;
  $logtxt .= "Rule removed. Note: Changes not saved";
 } 
 if(!$custom){
  // SHOW RULESETS
 
  echo "<table border=0 cellspacing=0 cellpadding=0 width=100%>
 <tr>
  <td>
   <span class=table_inside_small>Enter send rules.  Searches are case sensitive, <b>%</b> is wildcard. \"Any\" search queries become %query%<br>ANDed rules must <i>both be true</i> to match, OR rules match <i>both queries</i> at the same time.</span><br>
   <img src=1.gif height=5><br>\n";
  if(!$nr) { $nr = 1; }

  // ECHO "NUMRULES=$nr<BR>";
  // loop rules
/*  echo "target = "; print_r($target); echo "<br>";
  echo "q_type = "; print_r($q_type); echo "<br>";
  echo "field_ = "; print_r($field); echo "<br>";
  echo "q_for_ = "; print_r($q_for); echo "<br>";
  echo "stat__ = "; print_r($stat); echo "<br>";
  echo "M_____ ="; print_r($month); echo "<br>";
  echo "D______="; print_r($day); echo "<br>";
  echo "Y______"; print_r($year); echo "<br>";  
  echo "qord___"; print_r($qordate); echo "<br>";  
  echo "xdt___"; print_r($x_d_type); echo "<br>";  
 */
  for($i=0;$i<$nr;$i++){
   $csub = $sub[$i];
   $ctarget = $target[$i];
   $cq_type = $q_type[$i];
   $cfield = $field[$i];
   $cq_for = $q_for[$i];
   $cstat = $stat[$i];
   $ccond = $cond[$i];
   // echo "ccond[$i]=$ccond<br>";
   $d = $day[$i];
   $m = $month[$i];
   $y = $year[$i];
   // echo "day[$i]=$day[$i] month[$i]=$month[$i] year[$i]=$year[$i] ";
   if(!$m || !$d || !$y) { $m = date("m"); $y = date("Y"); $d = date("d"); }
   // echo "MDY=$m $d $y<br>";
   if($ccond=='a') $cond_and_chk = ' selected'; else $cond_and_chk = '';
   if($ccond=='o') $cond_or_chk = ' selected'; else $cond_or_chk = '';
   if($cstat=='*') $stat_all_chk = ' selected'; else $stat_all_chk = '';
   if($cstat=='a') $stat_a_chk = ' selected'; else $stat_a_chk = '';
   if($cstat=='u') $stat_u_chk = ' selected'; else $stat_u_chk = '';
   if($cstat=='r') $stat_r_chk = ' selected'; else $stat_r_chk = '';
   if($cstat=='b') $stat_b_chk = ' selected'; else $stat_b_chk = '';
   if($cstat=='n') $stat_n_chk = ' selected'; else $stat_n_chk = '';
   if($qordate[$i]=='more') $msel = ' selected'; else $msel = '';
   if($qordate[$i]=='search') $srsel = ' selected'; else $srsel = '';
   if($qordate[$i]=='date') $dsel = ' selected'; else $dsel = '';
   // echo "qordate[$i]=".$qordate[$i]."<br>";
   if($x_d_type[$i] == 'a') $xdtachk = ' selected'; else $xdtachk = '';
   if($x_d_type[$i] == 'c') $xdtcchk = ' selected'; else $xdtcchk = '';
   if($x_d_type[$i] == 'b') $xdtbchk = ' selected'; else $xdtbchk = '';
   if($x_d_type[$i] == 'd') $xdtdchk = ' selected'; else $xdtdchk = '';
   if($x_d_type[$i] == 'e') $xdtechk = ' selected'; else $xdtechk = '';
   
   // echo "qordate[$i] = ".$qordate[$i]."<br>";
   
   // echo "rule $i: $cstat, $ctarget, $cq_type, $cfield, $cq_for, $m, $d, $y, $x_d_type ssel=$ssel dsel=$dsel<br>";


   // ECHO "<small><small>CTARGET = $ctarget CQ_TYPE = $cq_type CFIELD = $cfield CQ_FOR = $cq_for<br></small></small>";

   echo "
   <table border=0 cellspacing=0 cellpadding=0 width=100% height=24>
    <tr>
     <td nowrap>\n";
     
   // if($csub=='1') echo "<img src=1.gif width=205 height=1>";
   $addsubtyp='';
   if($csub=='1'){
    echo "<span class=table_inside_normal>+&nbsp;</span>";
    $addsubtyp='2';
   } elseif($csub=='2'){
    echo "<img src=1.gif width=12 height=1><span class=table_inside_normal>+&nbsp;</span>";
    $addsubtyp = '2';
   } elseif($csub=='0' || !$csub){
    $addsubtyp = '1';   
   }
   
   if($i==0)
    echo "<input type=hidden name=cond[] value=x><select name=xcond style=\"width: 47px;\" class=xbox disabled><option value=x></select>";
   else echo "<select name=cond[] style=\"width: 47px;\" class=xbox><option value=a$cond_and_chk>And<option value=o$cond_or_chk>Or</select>";
   echo "<input type=hidden name=sub[] value=$csub><span id=csl$i>
      <select name=stat[] class=xbox style=\"width: 77px\">
      <option value=*$stat_all_chk>All
      <option value=a$stat_a_chk>Active
      <option value=u$stat_u_chk>Unconfirmed
      <option value=r$stat_r_chk>Removed
      <option value=b$stat_b_chk>Bounced
<!--      <option value=n$stat_n_chk>Not Active -->
      </select>
      <select name=target[] class=xbox style=\"width: 120px\">
      <option value=all>All Lists\n";

   $cmd = "select listnum,title from $ltable where 1 order by listnum";
   $result = @mysql_query($cmd,$link);
   while(list($lnu2,$lti2) = @mysql_fetch_row($result)){
    $lti2 = "$lnu2: $lti2";
    if(!$ctarget){
     if($lnu2==$list && $list <> 'all') $l_x = ' selected'; else $l_x = '';
    } else {
     if($lnu2==$ctarget && $ctarget <> '0') $l_x = ' selected'; else $l_x = '';
    }
    echo "       <option value=".$lnu2.$l_x.">$lti2\n";
   }
   echo "       </select></span>";

   /* if($csub=='1') echo "<script language=javascript><!--
       document.getElementById('csl$i').style.visibility='hidden';
       csl$i.style.display='none';
       --></script>\n";
   */
   $asel = ''; $esel = ''; $gtsel = ''; $ltsel = ''; $nesel = '';
   switch($cq_type){
    case 'a': $asel = ' selected'; break;
    case 'e': $esel = ' selected'; break;
    case 'g': $gtsel = ' selected'; break;
    case 'l': $ltsel = ' selected'; break;
    case 'n': $nesel = ' selected'; break;
   }

   // reset selects
   $fsel = ''; $lsel = ''; $emsel = ''; for($n=0;$n<10;$n++) ${"u".$n."sel"} = ''; $seqsel = ''; $delsel = ''; $refusel = '';


   switch($cfield){
    case 'f': $fsel = ' selected'; break;
    case 'l': $lsel = ' selected'; break;
    case 'e': $emsel = ' selected'; break;
//    case 's': $ssel = ' selected'; break;
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
    case 's': $seqsel = ' selected'; break;
    case 'd': $delsel = ' selected'; break;
    case 'r': $refusel = ' selected'; break;
   }

   echo "
<!--     <span class=table_inside_toolbar>Search</span> -->

      <select name=qordate[] style=\"width: 64px\" class=xbox onchange=\"
      if(this.value=='search'){
       document.getElementById('r_more$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='hidden';
       document.getElementById('r_search$i').style.visibility='visible';
       r_more$i.style.display='none';
       r_date$i.style.display='none';
       r_search$i.style.display='inline';
       return true;
      } 
      if(this.value=='date'){
       document.getElementById('r_more$i').style.visibility='hidden';
       document.getElementById('r_search$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='visible';
       r_more$i.style.display='none';
       r_search$i.style.display='none';
       r_date$i.style.display='inline';
       return true;
      }
      if(this.value=='more'){
       document.getElementById('r_search$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='hidden';
       document.getElementById('r_more$i').style.visibility='visible';
       r_search$i.style.display='none';
       r_date$i.style.display='none';
       r_more$i.style.display='inline';
       return true;
      }      
      \"><option value=more$msel>- More -<option value=search$srsel>Search<option value=date$dsel>DateAdd</select>
      <script language=javascript> dx$i = '$d'; </script>
<span id=r_more$i style=\"position: absolute; display: none\">
<span class=table_inside_normal>< Change this to search or query by date</span>
</span>
	  <span id=r_date$i style=\"position: absolute; display: none\">";


   // date select
   $ty = (date("Y")-3);
   echo "<select name=x_d_type[] class=xbox style=\"width: 39px; font: helvetica\">
<option value=a$xdtachk>>
<option value=c$xdtcchk>>=
<option value=b$xdtbchk><
<option value=d$xdtdchk><=
<option value=e$xdtechk>!=
</select><img src=1.gif width=5><select class=xbox name=month$i onchange=\"mv = edit_frm.month$i.value; if(mv=='2') { leapyear = checkyear(edit_frm.year$i.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm.day$i.options.length-1; i>=0; i--) { if(i>27) edit_frm.day$i.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm.day$i.options[i] = new Option(i+1,i+1); } if(edit_frm.day$i.options[dx$i-1]) edit_frm.day$i.options[dx$i-1].selected=true; else { edit_frm.day$i.options[ndays-1].selected=true; dx$i=ndays; } \">";
   for($j=1;$j<=12;$j++){
    $itxt = date("F",mktime(0,0,0,$j,1,2002));
    if($j==$m) $sel = ' selected'; else $sel = '';
    echo "<option value=$j".$sel.">$itxt";
   }
   echo "</select><img src=1.gif width=5><select class=xbox name=day$i onchange=\"dx$i = edit_frm.day$i.selectedIndex+1;\">";
   for($j=1;$j<=date("t",mktime(0,0,0,$m,$d,$y));$j++){
    if($j==$d) $sel = ' selected'; else $sel = '';
    echo "<option value=$j".$sel.">$j";
   }
   echo "</select><img src=1.gif width=5><select class=xbox name=year$i onchange=\"mv = edit_frm.month$i.value; if(mv=='2') { leapyear = checkyear(edit_frm.year$i.value); if(leapyear) ndays = '29'; else ndays = '28'; } if(mv=='1' || mv=='3' || mv=='5' || mv=='7' || mv=='8' || mv=='10' || mv=='12') ndays = 31; if(mv=='4' || mv=='6' || mv=='9' || mv=='11') ndays = 30; for (var i=edit_frm.day$i.options.length-1; i>=0; i--) { if(i>27) edit_frm.day$i.options[i] = null; } for (var i=0; i<=ndays-1; +i++) { edit_frm.day$i.options[i] = new Option(i+1,i+1); } if(edit_frm.day$i.options[dx$i-1]) edit_frm.day$i.options[dx$i-1].selected=true; else { edit_frm.day$i.options[ndays-1].selected=true; dx$i=ndays; } \">";
   for ($j=0;$j<=6;$j++) {
    if($ty==$y) $sel = ' selected'; else $sel = '';
    echo "<option value=$ty".$sel.">$ty";
    $ty++;
   }
   echo "</select>
	  </span>\n";
	  
   if($sub[$i]=='0') $qfsize = '78';
    elseif($sub[$i]=='1') $qfsize = '66';
    elseif($sub[$i]=='2') $qfsize='54';
   
   echo "      <span id=r_search$i style=\"position: absolute; display: none\">
      <select name=q_type[] class=xbox>
      <option value=e$esel>Exact
      <option value=a$asel>Any
      <option value=g$gtsel>>=
      <option value=l$ltsel><=
      <option value=n$nesel>!=
      </select>
      <span class=table_inside_small>in</span>
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
      <option value=s$seqsel>Seq #
      <option value=d$delsel>Delay #
      <option value=r$refusel>Refer URL
      </select>
      <span class=table_inside_small>for</span>
      <input type=text class=xbox style=\"width: ".$qfsize."px\" name=q_for[] size=10 maxlength=255 value=\"".htmlspecialchars($cq_for)."\"></span>\n";
	  
   if($qordate[$i]=='search') echo "<script language=javascript>
       document.getElementById('r_more$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='hidden';
       document.getElementById('r_search$i').style.visibility='visible';
       document.getElementById('r_more$i').style.display='inline';
       document.getElementById('r_date$i').style.display='inline';
       document.getElementById('r_search$i').style.display='inline';
       r_more$i.style.display='none';
       r_date$i.style.display='none';
       r_search$i.style.display='inline';
       </script>";
   elseif($qordate[$i]=='date') echo "<script language=javascript>
       document.getElementById('r_more$i').style.visibility='hidden';
       document.getElementById('r_search$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='visible';
       document.getElementById('r_more$i').style.display='inline';
       document.getElementById('r_search$i').style.display='inline';
       document.getElementById('r_date$i').style.display='inline';
       r_more$i.style.display='none';
       r_search$i.style.display='none';
       r_date$i.style.display='inline';</script>";
   elseif($qordate[$i]=='more') echo "<script language=javascript>
       document.getElementById('r_search$i').style.visibility='hidden';
       document.getElementById('r_date$i').style.visibility='hidden';
       document.getElementById('r_more$i').style.visibility='visible';
       document.getElementById('r_search$i').style.display='inline';
       document.getElementById('r_date$i').style.display='inline';
       document.getElementById('r_more$i').style.display='inline';
       r_search$i.style.display='none';
       r_date$i.style.display='none';
       r_more$i.style.display='inline';</script>";
		
   echo "	 </td><td align=right>&nbsp;<input type=button class=button style=\"width: 18px\" value=\" + \" onclick=\"edit_frm.nr.value=$nr+1; edit_frm.add_rule.value=1; edit_frm.add_subrule_num.value='$i'; edit_frm.add_subrule_type.value='$addsubtyp'; edit_frm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\"><img src=1.gif width=3><input type=button class=button style=\"width: 21px\"  value=\"Del\" onclick=\"";
   if($nr>1) echo "edit_frm.rem_rule.value='1'; edit_frm.rem_num.value='$i'; edit_frm.submit();"; else echo "alert('You can not remove the only rule.');";
   echo "\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" ><br>
     </td>
    </tr>
   </table>
   <img src=1.gif height=3><br>";
   }
   echo "
  </td>
 </tr>
</table>
<img src=1.gif height=3><br>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <input style=\"width: 90px;\" class=button type=button value=\"Add Rule\" onclick=\"edit_frm.nr.value=$nr+1; edit_frm.add_rule.value='1'; edit_frm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
  </td>
 </tr>
</table>
<img src=1.gif height=10><br>\n";
 // end !custom
 } else {
  // custom
  echo "<span class=table_inside_small>
  � You will only want to use <a href=http://dev.mysql.com/doc/refman/5.0/en/select.html>SELECT</a> statements.<br>
  � The following string is automatically pre-pended: <span style=\"background: #eeeeee\">SELECT * FROM lm_users </span><br>
  � In most cases you will start your query off with <span style=\"background: #eeeeee\">WHERE</span><br>
  � Semi-colons, destructive commands, and access to the lm_config table are disabled.<br>
  � To filter duplicate email addresses when selecting multiple lists, add <span style=\"background: #eeeeee\"> GROUP BY email</span> to the end of your query.<br>
  � Common fields: <b>list</b>, <b>dateadd</b> (YYYY-MM-DD), <b>cnf</b> (0=Unconfirmed 1=Active 2=Removed 3=Bounced), <b>email</b>, etc.<br>
 <img src=1.gif height=7><br>
  Enter your custom MySQL query below:<br>
  <textarea name=custom_q class=xbox rows=5 cols=80 style=\"width: 629px\">".htmlspecialchars($custom_q)."</textarea><br>\n";
 }
 echo "   <input type=hidden name=nr value=\"$nr\">
   <input type=hidden name=rem_rule value=\"\">
   <input type=hidden name=rem_num value=\"\">
   <input type=hidden name=add_rule value=\"\">
   <input type=hidden name=add_subrule value=\"\">
   <input type=hidden name=add_subrule_num value=\"\">
   <input type=hidden name=add_subrule_type value=\"\">
   <input type=hidden name=show_query value=\"\">
   <input type=hidden name=custom value=\"$custom\">
   <input type=hidden name=list value=$list>
   <input type=hidden name=rsid value=$rsid>
   <input type=hidden name=save value=\"\">
   <input type=hidden name=edit value=$edit>
   <input type=hidden name=add value=$add>
   </form>\n"; 
}

function rname($rxid){
 global $setable;
 $cmd = "select name from $setable where id = '$rxid'";
 $row = mysql_query($cmd) or die(mysql_error());
 list($rxn)=mysql_fetch_row($row);
 return $rxn;
}

function make_rquery($rxmode='',$rxid=''){
 global $setable; global $sedtable; global $utable; global $nr; global $sub; global $target; global $q_type; global $field; global $q_for; global $stat; global $cond; global $qordate; global $x_d_type; global $month; global $day; global $year; global $x_uniq_em; global $custom; global $custom_q;
 if($custom_q) $custom_q = clean_sql($custom_q);
 
 if($rxmode=='send'){
  $sub=''; $target=''; $q_type=''; $field=''; $q_for=''; $stat=''; $cond=''; $qordate=''; $x_d_type=''; $month=''; $day=''; $year=''; $x_uniq_em='';
  // echo "RS: send mode! RXID=$rxid<br>";
  // get name,descr, and dupes setting and set vars
  $rsrows = mysql_query("select name,descr,fdupe from $setable where id = '$rxid'");
  if(@mysql_num_rows($rsrows)==0){ echo "<b>Selection could not be selected! exiting!"; exit; }
  list($xname,$xdesc,$xfdupe)=@mysql_fetch_row($rsrows);
  if($xfdupe=='1') $x_uniq_em = '1'; else $x_uniq_em = '';
  // get rules, nr, etc.
  $xnr = 0;
  $rsrows = mysql_query("select rsub,cond,stat,list,qtype,field,q,qdate from $sedtable where rsid = '$rxid' order by id");
  if(@mysql_num_rows($rsrows)==0){ echo "<b>rules could not be selected! exiting!"; exit; }
  while(list($xrsub,$xcond,$xstat,$xlist,$xqtype,$xfield,$xq,$xqdate)=@mysql_fetch_row($rsrows)){
   if($xrsub=='!'){ $custom =1; $custom_q = $xq; } else $custom = '';
   $xnr++;
   if(!$custom){
    if($xlist=='0') $xlist = 'all';
    $sub[]=$xrsub; $target[]=$xlist; $q_type[]=$xqtype; $field[]=$xfield; $q_for[]=$xq; $stat[]=$xstat; $cond[]=$xcond;
    if($xqtype=='m') $qordate[]='more';
    elseif($xqtype=='d') $qordate[]='date';
    else $qordate[]='search';
    // echo "xqdate=$xqdate<br>";
    $x_d_type[]=substr($xqdate,0,1);
    list($y,$m,$d)=dateparse(substr($xqdate,1,10),'break');
    $year[] = $y;
    $month[] = $m;
    $day[] = $d;
    // end !custom
   }
  }
  $nr = $xnr;
 }
 // echo "nr=$nr<br>";

 // echo "# make_rquery: $nr rules to process<br>";
 if($rxmode=='send') $selstr = 'email,id'; else $selstr = '*';
 $cmd = "SELECT $selstr FROM $utable ";

 $wher = '';
 if(!$custom){
  $cmd .= 'WHERE ';
  for($a=0;$a<$nr;$a++){

   $st = ''; $n = '';
   switch($stat[$a]){
    case '*' : $st = ' '; break;
    case 'a' : $st = " and cnf = '1'"; break;
    case 'u' : $st = " and cnf = '0'"; break;
    case 'r' : $st = " and cnf = '2'"; break;
    case 'b' : $st = " and cnf = '3'"; break;
    case 'n' : $n = 'NOT '; $st = " and cnf = '1'"; break;
   }

   $co = '';
   if($a>0) switch($cond[$a]){
    case 'a' : $wher .= ' AND '.$n; break;
    case 'o' : $wher .= ' OR '.$n; break;
   }

   $wher .= '(';

   if($sub[$a+1] == '1' && $sub[$a] == '0'){
    $wher .= '(';
   }

   if($sub[$a+1] == '2' && $sub[$a] == '1'){
    $wher .= '(';
   }
  
   // echo "# rule $a: sub=$sub[$a] target=$target[$a] q_type=$q_type[$a] field=$field[$a] q_for=$q_for[$a] stat=$stat[$a] cond=$cond[$a] qordate=$qordate[$a] x_d_type=$x_d_type[$a] month=$month[$a] day=$day[$a] year=$year[$a]<br>";

   if($target[$a]=='all'){
    $wher .= '1';
   } else {
    $wher .= "list = '".addslashes($target[$a])."'";
   }
   $wher .= $st;
   if($qordate[$a]=='more'){
    // "more", nothing else needed
   } elseif($qordate[$a]=='search') {
    $finame = '';
    switch($field[$a]){
     case 'f': $finame = 'fname'; break;
     case 'l': $finame = 'lname'; break;
     case 'e': $finame = 'email'; break;
     case '0': $finame = 'user1'; break;
     case '1': $finame = 'user2'; break;
     case '2': $finame = 'user3'; break;
     case '3': $finame = 'user4'; break;
     case '4': $finame = 'user5'; break;
     case '5': $finame = 'user6'; break;
     case '6': $finame = 'user7'; break;
     case '7': $finame = 'user8'; break;
     case '8': $finame = 'user9'; break;
     case '9': $finame = 'user10'; break;
     case 's': $finame = 'cseq'; break;
     case 'd': $finame = 'cdel'; break;
	case 'r': $finame = 'refurl'; break;
    }
    if($q_type[$a]=='a'){
     // "any" search
     $wher .= " AND $finame like '%".addslashes($q_for[$a])."%'";
    } elseif($q_type[$a]=='e'){
     // "exact" search
     $wher .= " AND $finame = '".addslashes($q_for[$a])."'";
    } elseif($q_type[$a]=='g'){
     $wher .= " AND $finame >= '".addslashes($q_for[$a])."'";
    } elseif($q_type[$a]=='l'){
     $wher .= " AND $finame <= '".addslashes($q_for[$a])."'";
    } elseif($q_type[$a]=='n'){
     $wher .= " AND $finame != '".addslashes($q_for[$a])."'";
    }

   } elseif($qordate[$a]=='date'){
    // date
    // determine before or after
   
    $ba = ''; $co = '';
    $ba = substr($x_d_type[$a].$year[$a].'-'.$month[$a].'-'.$day[$a],0,1);
    if($ba=='a') $co = '>';
     elseif($ba=='b') $co = '<';
     elseif($ba=='c') $co = '>=';
     elseif($ba=='d') $co = '<=';
     elseif($ba=='e') $co = '!=';
     $dt = substr($x_d_type[$a].$year[$a].'-'.str_pad($month[$a], 2, "0", STR_PAD_LEFT).'-'.str_pad($day[$a], 2, "0", STR_PAD_LEFT),1,10);
    $wher .= " AND dateadd $co '".addslashes($dt)."'";
   }

   if($sub[$a] == '1' && ($sub[$a+1] == '0' || !$sub[$a+1])){
    $wher .= ')';
   }

   if($sub[$a] == '2' && (($sub[$a+1] == '0' || $sub[$a+1] == '1') || !$sub[$a+1])){
    if($sub[$a+1]=='0' || !$sub[$a+1]){
     $wher .= '))';
    } elseif($sub[$a+1]=='1'){
     $wher .= ')';
    }
   }

   $wher .= ')';
  }
  // end !custom
 } else {
  $wher = $custom_q;
 }
 $cmd .= $wher;
 if($x_uniq_em=='1'){
  $cmd .= ' group by email';
 }
 return $cmd;
}

function clean_sql($in){
 global $ctable;
 $badwords=array(';',$ctable,'DROP PROCEDURE','DROP FUNCTION','ALTER TABLE','DROP USER','DROP VIEW','CREATE FUNCTION','DROP TABLE','DROP INDEX','DROP DATABASE','DROP TRIGGER','FOREIGN KEY','DELETE FROM','DELETE LOW_PRIORITY','DELETE QUICK','DELETE IGNORE','OPTIMIZE TABLE','REPAIR TABLE','ANALYZE TABLE','UPDATE','INSERT INTO','INSERT LOW_PRIORITY','INSERT DELAYED','INSERT HIGH_PRIORITY','INSERT IGNORE','CREATE DATABASE','CREATE PROCEDURE','CREATE PROCEDURE','CREATE VIEW','CREATE TABLE','CREATE SCHEMA','TRUNCATE TABLE','FLUSH TABLES','SET PASSWORD','SHOW VARIABLES','SHOW GLOBAL VARIABLES','SHOW STATUS','CHARACTER','SET ONE_SHOT','CREATE INDEX','USE mysql');
 for($a=0;$a<count($badwords);$a++){
 
  $in = preg_replace("/{$badwords[$a]}/i",'',$in);
 }
 return $in;
}
?>