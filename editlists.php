<?php

/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
  You may not modify or distribute the program without express permission from the author.
  Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$islogged = islogged();

ini_set("max_execution_time", "0");

if ($islogged) {
    $isadmin = 'true';
} else {
    $isadmin = '';
    header("Location: ./login.php?r=" . urlencode(substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1)));
    exit;
}

if ($isadmin == 'true') {

    $link = mysql_connect($sqlhost, $sqluser, $sqlpass);
    mysql_selectdb($sqldb);
// $thetime = date("H:i:sa");
// echo "thetime=$thetime ";
// avoid list='all'
    if ($list == 'all' || !$list)
        $list = firstlist();

    if ($editwc) {
        include('./editmsg.php');
        if ($editwc == 'w') {
            $msg = 'welcome';
            $xmsg = 'List ' . $list . ' welcome email ';
            $q = array('welsubj', 'welcom', 'welcht', 'welcf');
        } elseif ($editwc == 'c') {
            $msg = 'confirm';
            $xmsg = 'List ' . $list . ' confirmation email ';
            $q = array('cnfsubj', 'cnfmesg', 'cnfht', 'cnff');
        } elseif ($editwc == 'r') {
            $msg = 'remind';
            $xmsg = 'List ' . $list . ' confirmation reminder email ';
            $q = array('remsubj', 'remmsg', 'remht', 'remf');
        }

        if ($save) {
            @mysql_query("update $ltable set $q[0] = '" . addslashes($txtsubj) . "', $q[1] = '" . addslashes($txtcont) . "', $q[2] = '" . addslashes($txthtcont) . "', $q[3] = '" . addslashes($txtfatt) . "' where listnum = '" . addslashes($list) . "';");
            $logtxt .= $xmsg . 'saved';
        }

        if (!$save && !$ql_save && !$ql_del && !$ql_load && !$preview) {
            $cmd = "select $q[0],$q[1],$q[2],$q[3] from $ltable where listnum = '" . addslashes($list) . "'";
            $result = @mysql_query($cmd);
            list($txtsubj, $txtcont, $txthtcont, $txtfatt) = @mysql_fetch_row($result);
            $logtxt .= $xmsg . 'loaded';
        }
        $edit = 1;
        editmessage($msg);
        exit;
    }
// edit field page
    if ($efields) {
        // simple page listing 10 fields with save button
        if ($save) {
            for ($i = 1; $i <= 10; $i++) {
                if (${"reqd" . $i}) {
                    ${"reqd" . $i} = '1';
                    if (!${"error" . $i})
                        ${"error" . $i} = "Please enter your ${"field" . $i}";
                }
                ${"field" . $i} = implode('/*LMP*/', array(${"field" . $i}, ${"reqd" . $i}, ${"error" . $i}));
                ${"field" . $i} = addslashes(${"field" . $i});
            }

            $cmd = "update $ltable set field1 = '$field1', field2 = '$field2', field3 = '$field3', field4 = '$field4', field5 = '$field5', field6 = '$field6', field7 = '$field7', field8 = '$field8', field9 = '$field9', field10 = '$field10' where listnum = '$list'";
            $logtxt = "List $list custom fields saved";
            mysql_query($cmd);
        }

        // main page
        $cmd = "select field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list';";
        $lrow = mysql_query($cmd);
        list($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row($lrow);
        // parse data
        for ($i = 1; $i <= 10; $i++) {
            list(${"field" . $i}, ${"reqd" . $i}, ${"error" . $i}) = explode('/*LMP*/', ${"field" . $i});
            if (${"reqd" . $i} == '1')
                ${"reqd" . $i . "chk"} = ' checked'; else
                ${"reqd" . $i . "chk"} = '';
        }
        adminheader('LM: Fields', "Custom Fields", '');
        echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top>\n";
        listmenu($list, 'list_cf');
        echo "
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>
  
   <table border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <input type=button class=button value=\"Back to List\" style=\"width: 80px\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button_on value=\"Custom Fields\" style=\"width: 96px\" onclick=\"hopto('./editlists.php?list=$list&efields=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Custom HTML\" style=\"width: 92px\" onclick=\"hopto('./editchtml.php?cid=$cid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
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
  <img src=1.gif height=2><br>
  <span class=table_inside_normal>Simply enter a \"Short Name\" for each field to activate it.  Once saved, the field(s) will show up in the program in places such as the signup code generator and user \"Edit\" pages. The name is only shown to the ListMail administrator and has no effect on how you set up your signup forms and message codes.<br></span>
  <img src=1.gif height=4><br>
  <form method=post>
  <input type=hidden name=efields value=1>
  <input type=hidden name=list value=$list>
  <input type=hidden name=save value=1>
  <!-- <div style=\"border: 1px solid black;\">
  -->
  
  <table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0>
   <tr class=table_head_bg_nopad><td>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Custom Fields - List $list</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('custom_fields') . "');\">Help</a><br></td></tr><tr><td height=1 colspan=2 bgcolor=black><img src=1.gif height=1><br></td></tr></table>
  <table width=100% border=0 cellspacing=0 cellpadding=3> 
   <tr class=bg2>
    <td class=table_head_col align=center>#</td>
    <td class=table_head_col align=center>FormID</td>
    <td class=table_head_col>Short Name</td>
    <td class=table_head_col align=center>Required?</td>
    <td class=table_head_col>Error Message</td>
   </tr>\n";
        for ($i = 1; $i <= 10; $i++) {
            echo "   <tr bgcolor=white>
    <td class=table_inside_small align=center>
     $i<br>
    </td>
    <td class=table_inside_small align=center>
     user$i
    </td>
    <td class=table_inside_small>
     <input class=xbox type=text name=field$i value=\"" . htmlspecialchars(${"field" . $i}) . "\" style=\"width: 200px\" maxlength=30><br>
    </td>
    <td align=center><input type=checkbox class=chkbox value=1 name=reqd$i" . ${"reqd" . $i . "chk"} . "><br></td>
    <td>
     <input class=xbox type=text name=error$i value=\"" . htmlspecialchars(${"error" . $i}) . "\" style=\"width: 280px\"><br>
    </td>
   </tr>\n";
        }
        echo "  </table>
 </td></tr></table>
 </div>
 <img src=1.gif height=3><br>
 <table border=0 cellspacing=0 cellpadding=0><tr><td><input type=submit class=button value=\"Save Fields\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\"></td>
  <td width=3><img src=1.gif width=3></td>
  <td><input type=button class=button value=\"Back to List\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\"></td></tr></table></form>\n";
        if ($logtxt)
            echo "<img src=./1.gif height=5><br><span class=status_text>$logtxt</span><br>";
        exit;
    }

// this should be dosave
    if ($doedit == 'yes') {
        if ($txtcact <> '1') {
            $txtcact = 0;
        }
        if ($txtwact <> '1') {
            $txtwact = 0;
        }
        if ($txtreq1 <> '1') {
            $txtreq1 = 0;
        }
        if ($txtreq2 <> '1') {
            $txtreq2 = 0;
        }
        if ($n1chk <> '1')
            $n1chk = '0';
        if ($n2chk <> '1')
            $n2chk = '0';
        if ($n3chk <> '1')
            $n3chk = '0';
        if ($n4chk <> '1')
            $n4chk = '0';
        if ($n5chk <> '1')
            $n5chk = '0';
        if ($closchk <> '1')
            $closchk = '0';
        if ($cnfchk <> '1')
            $cnfchk = '0';
        if (!is_numeric($chtml))
            $chtml = '1';
        if (!is_numeric($emsgs))
            $chtml = '1';

        $tnotifs = $n1chk . ";" . $n2chk . ";" . $n3chk . ";" . $n4chk . ";" . $n5chk;

        function process_listtext($r, $l) {
            $p = explode(',', $r);
            $o = '';
            $first = 1;
            while (list($k, $v) = each($p)) {
                $v = trim($v);
                if (is_numeric($v) && $v <> $l) {
                    if (!$first)
                        $o .= ',';
                    $o .= $v;
                    $first = '';
                }
            }
            return $o;
        }

        // process add/remove lists option
        $textadd_rem = process_listtext($textadd_rem, $textlist);
        if (!$add_remchk)
            $textadd_rem = '';
        if (!$textadd_rem)
            $add_remchk = '0';
        $textadd_add = process_listtext($textadd_add, $textlist);
        if (!$add_addchk)
            $textadd_add = '';
        if (!$textadd_add)
            $add_addchk = '0';
        $textrem_rem = process_listtext($textrem_rem, $textlist);
        if (!$rem_remchk)
            $textrem_rem = '';
        if (!$textrem_rem)
            $rem_remchk = '0';
        $textrem_add = process_listtext($textrem_add, $textlist);
        if (!$rem_addchk)
            $textrem_add = '';
        if (!$textrem_add)
            $rem_addchk = '0';

        if ($floopchk)
            $floopchk = '1'; else
            $floopchk = '';
        if (!is_numeric($flseq) || !isset($flseq) || $flseq < 1 || !$floopchk) {
            list($flseq) = mysql_fetch_row(mysql_query("select seq from $ftable where list = '$list' order by seq limit 1"));
            if (!$flseq)
                $flseq = '1';
        }
        if (!is_numeric($fldel) || !isset($fldel) || $fldel < 0 || !$floopchk) {
            list($fldel) = mysql_fetch_row(mysql_query("select del from $ftable where list = '$list' order by seq limit 1"));
            if (!$fldel)
                $fldel = '0';
        }
        $floopchk .= ":$flseq:$fldel";

        if ($floadchk && is_numeric($floadperc) && is_numeric($floadid))
            $floadchk = '1'; else {
            $floadchk = '';
            $floadperc = '100';
            $floadid = '';
        }

        if ($fdelchk)
            $fdelchk = '1'; else
            $fdelchk = '';
        if ($fmovechk && is_numeric($fmovelist) && is_numeric($fmoveseq) && is_numeric($fmovedel) && $fmovelist >= 1 && $fmoveseq >= 1 && $fmovedel >= 0)
            $fmovechk = '1'; else {
            $fmovechk = '';
            $fmovelist = '1';
            $fmoveseq = '1';
            $fmovedel = '0';
        }
        $tlistopts = $closchk . ";" . $cnfchk . ";" . $add_remchk . ";" . $add_addchk . ";" . $rem_remchk . ";" . $rem_addchk . ";" . $floopchk . ";" . $floadchk . ";" . $fmovechk . ";" . $fdelchk;
        $textaddopts = $textadd_rem . ';' . $textadd_add . ';' . $textrem_rem . ';' . $textrem_add . ';' . $floadperc . ';' . $floadid . ';' . $fmovelist . ';' . $fmoveseq . ';' . $fmovedel;

        $textsendn = addslashes($textsendn);
        $textsende = addslashes($textsende);
        $texterrm = addslashes($texterrm);
        $texttitl = addslashes($texttitl);
        $textdesc = addslashes($textdesc);
        $textrem = addslashes($textrem);
        $tnmails = addslashes($tnmails);

        // check if changing list number, if so, change custom html, followups, users.
        $chglist = '';
        list($chklist) = @mysql_fetch_row(mysql_query("select listnum from $ltable where id = '$editid'"));

        if ($chklist <> $textlist) {
            $chglist = 1;
        }

        // renumber followups, users.
        $cmd_fp = "update $ftable set list = '$textlist' where list = '$chklist'";
        $cmd_us = "update $utable set list = '$textlist' where list = '$chklist'";
        $cmd_mc = "update $dtable set list = '$textlist' where list = '$chklist'";

        if ($chglist) {
            // check dupe listnum (if this listnum exists for a DIFF list id)
            $result = mysql_query("select id,listnum from $ltable where 1");
            $duped = '';
            while (list($dupeid, $dupenum) = @mysql_fetch_row($result)) {
                if (($dupenum == $textlist) && ($dupeid <> $editid)) {
                    $duped = 1;
                }
            }
        }

        if ($textlist == '0')
            $duped = 1;
        if ($duped) {
            $logtxt = "Duplicate or invalid list number!  Not saved!";
        } else {
            if (!$demo) {
                if ($chglist) {
                    mysql_query($cmd_fp);
                    mysql_query($cmd_us);
                    mysql_query($cmd_mc);
                }
                if (!is_numeric($txtrem_every) || $txtrem_every < 1)
                    $txtrem_every = '1';
                if (!is_numeric($txtrem_max) || $txtrem_max < 1)
                    $txtrem_max = '3';
                $txtremopts = $txtrem_every . ';' . $txtrem_max;
                $cmd = "update $ltable set listnum = '$textlist', title = '$texttitl', sende = '$textsende', sendn = '$textsendn', welcact = '$txtwact', cnfact = '$txtcact', remact = '$txtract', remopts = '$txtremopts', req1 = '$txtreq1', req2 = '$txtreq2', notifs = '$tnotifs', nmails = '$tnmails', listopts = '$tlistopts', chtmlid = '$chtml', errorid = '$emsgs', addopts = '$textaddopts', remote = '$remote', remotedb = '$remotedb', remoteuser = '$remoteuser', remotepwd = '$remotepwd', remotehost = '$remotehost' where id = '$editid'";
                mysql_query($cmd) or die("could not update");
                $logtxt = "List $textlist settings saved.";
                if ($chglist)
                    $logtxt = "List saved.";
                $list = $textlist;
            } else {
                $logtxt = "Cannot save in demo.";
            }
        }
    }

    if ($dodel) {
        if (!$demo) {
            if (!is_numeric($delid))
                exit('invalid data');
            $cmd = "select id from $ltable where 1";
            $result = mysql_query($cmd);
            if (@mysql_num_rows($result) > '1') {
                list($dlist) = mysql_fetch_row(mysql_query("select listnum from $ltable where id = '$delid';"));
                $cmd = "delete from $ltable where id = '$delid'";
                mysql_query($cmd);
                // $cmd = "delete from $vtable where list = '$listid'";
                // mysql_query($cmd);
                $cmd = "delete from $dtable where list = '$listid'";
                mysql_query($cmd);
                $cmd = "delete from $utable where list = '$listid'";
                mysql_query($cmd);
                $cmd = "delete from $ftable where list = '$listid'";
                mysql_query($cmd);
                optimizeall();
                $logtxt = "List deleted.";
                $lrow = mysql_query("select listnum from $ltable where listnum < '" . addslashes($dlist) . "' order by listnum desc limit 1");
                if (@mysql_num_rows($lrow) > 0) {
                    list($list) = mysql_fetch_row($lrow);
                } else {
                    $list = firstlist();
                }
            } else {
                $logtxt = "Cannot delete the only list.";
            }
        } else {
            $logtxt = "Cannot delete in demo.";
        }
    }

    if ($doadd) {
        if (!$demo) {
            $cmd = "select listnum from $ltable where 1 order by listnum desc limit 1";
            $result = mysql_query($cmd);
            list($listnum) = @mysql_fetch_row($result);
            $listnum++;
            $xmails = explode(';', $adminmail);
            $adminmail = $xmails[0];
            $dom = getdomain();
            $cmd2 = "insert into $ltable values('null','$listnum','New List','news@$dom','New List Newsletter','Welcome!','Welcome to the mailing list!','','','1','Please verify your subscription','Click the link below to verify your email address for subscription to the yoursite.com newsletter\n\n!confirm','','','0','Confirmation Reminder','You subscribed to our list but did not respond to our confirmation email.  We hope you are still interested in joining us!\n\nPlease confirm your subscription to our mailing list by clicking the link below:\n\n!confirm\n\nThank you!','','','0','1;7','','', '','','','','','','','','','','0;0;0;0;0','$adminmail','0;1;0','1','1',';;;;100;;1;1;0',0,'','','','')";
            mysql_query($cmd2) or die(mysql_error());
            ;
            $logtxt = "New list created.";
            $list = $listnum;
            $doadd = '';
        } else {
            $logtxt = 'Cannot add list in demo.';
        }
    }

    if ($docopy) {
        if (!$demo) {
            $cmd = "select listnum from $ltable where 1 order by listnum desc limit 1";
            $result = mysql_query($cmd);
            list($listnum) = @mysql_fetch_row($result);
            $listnum++;
            // get current list data
            $lrow = mysql_query("select title,sende,sendn,welsubj,welcom,welcht,welcf,welcact,cnfsubj,cnfmesg,cnfht,cnff,cnfact,remsubj,remmsg,remht,remf,remact,remopts,req1,req2,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,notifs,nmails,listopts,chtmlid,errorid,addopts,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '" . addslashes($list) . "';");
            list($title, $sende, $sendn, $welsubj, $welcom, $welcht, $welcf, $welcact, $cnfsubj, $cnfmesg, $cnfht, $cnff, $cnfact, $remsubj, $remmsg, $remht, $remf, $remact, $remopts, $req1, $req2, $field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10, $notifs, $nmails, $listopts, $chtmlid, $errorid, $addopts, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = mysql_fetch_row($lrow);
            mysql_query("insert into $ltable values('','$listnum','" . addslashes($title) . "','" . addslashes($sende) . "','"
                    . addslashes($sendn) . "','" . addslashes($welsubj) . "','" . addslashes($welcom) . "','" . addslashes($welcht) . "','"
                    . addslashes($welcf) . "','" . addslashes($welcact) . "','" . addslashes($cnfsubj) . "','" . addslashes($cnfmesg) . "','"
                    . addslashes($cnfht) . "','" . addslashes($cnff) . "','" . addslashes($cnfact) . "','" . addslashes($remsubj) . "','"
                    . addslashes($remmsg) . "','" . addslashes($remht) . "','" . addslashes($remf) . "','" . addslashes($remact) . "','"
                    . addslashes($remopts) . "','" . addslashes($req1) . "','" . addslashes($req2) . "','" . addslashes($field1) . "','"
                    . addslashes($field2) . "','" . addslashes($field3) . "','" . addslashes($field4) . "','" . addslashes($field5) . "','"
                    . addslashes($field6) . "','" . addslashes($field7) . "','" . addslashes($field8) . "','" . addslashes($field9) . "','"
                    . addslashes($field10) . "','" . addslashes($notifs) . "','" . addslashes($nmails) . "','" . addslashes($listopts) . "','"
                    . addslashes($chtmlid) . "','" . addslashes($errorid) . "','" . addslashes($addopts) . "','" . addslashes($remote) . "','"
                    . addslashes($remotedb) . "','" . addslashes($remoteuser) . "','" . addslashes($remotepwd) . "'," . addslashes($remotehost) . "')");
            // get current list followups and copy
            $frows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron,sset from $ftable where list = '" . addslashes($list) . "';");
            if (@mysql_num_rows($frows) > 0) {
                $fupcopy = @mysql_num_rows($frows);
                while (list($subject, $content, $htcontent, $fattach, $seq, $del, $cron, $sset) = mysql_fetch_row($frows)) {
                    mysql_query("insert into $ftable values('','','" . addslashes($listnum) . "','" . addslashes($subject) . "','" . addslashes($content) . "','" . addslashes($htcontent) . "','" . addslashes($fattach) . "','" . addslashes($seq) . "','" . addslashes($del) . "','" . addslashes($cron) . "','" . addslashes($sset) . "');") or die(mysql_error());
                }
            } else {
                $fupcopy = false;
            }
            $logtxt = "New List $listnum created from copy of list $list. ";
            if ($fupcopy)
                $logtxt .= "$fupcopy followups copied. ";
            $list = $listnum;
        } else {
            $logtxt = 'Cannot copy list in demo.';
        }
    }
    if ($getcode) {

// get list from num
        $elist = $list;

        $headtxt = "Signup Form: List $elist";
        adminheader('LM: Get Code', $headtxt, '');
        list($lmpath) = mysql_fetch_row(mysql_query("select listmailpath from $ctable where 1"));

        $supfile = $lmpath . "signup.php";

        $thecode = "";

        $thecode .= "<form method=post action=$supfile>
<input type=hidden name=list value=$elist>\n";

        if ($fcheck == 1) {
            $fchk = ' checked';
            $thecode .= "First Name: <input type=text name=fname size=10><br>\n";
        }
        if ($lcheck == 1) {
            $lchk = ' checked';
            $thecode .= "Last Name: <input type=text name=lname size=10><br>\n";
        }

// do email nomatter what
        $thecode .= "Email: <input type=text name=email><br>\n";

// custom fields
        $cmd = "select field1,field2,field3,field4,field5,field6,field7,field8,field9,field10 from $ltable where listnum = '$list' limit 1";
        $lrow = mysql_query($cmd);
        list($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10) = @mysql_fetch_row($lrow);
        for ($i = 1; $i <= 10; $i++) {
            list(${"field" . $i}, $null, $null0) = explode('/*LMP*/', ${"field" . $i});
        }
        $fields = array(1 => $field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10);

        if ($f1chk == 1) {
            $fchkd[1] = ' checked';
            $thecode .= $field1 . ": <input type=text name=user1><br>\n";
        }
        if ($f2chk == 1) {
            $fchkd[2] = ' checked';
            $thecode .= $field2 . ": <input type=text name=user2><br>\n";
        }
        if ($f3chk == 1) {
            $fchkd[3] = ' checked';
            $thecode .= $field3 . ": <input type=text name=user3><br>\n";
        }
        if ($f4chk == 1) {
            $fchkd[4] = ' checked';
            $thecode .= $field4 . ": <input type=text name=user4><br>\n";
        }
        if ($f5chk == 1) {
            $fchkd[5] = ' checked';
            $thecode .= $field5 . ": <input type=text name=user5><br>\n";
        }
        if ($f6chk == 1) {
            $fchkd[6] = ' checked';
            $thecode .= $field6 . ": <input type=text name=user6><br>\n";
        }
        if ($f7chk == 1) {
            $fchkd[7] = ' checked';
            $thecode .= $field7 . ": <input type=text name=user7><br>\n";
        }
        if ($f8chk == 1) {
            $fchkd[8] = ' checked';
            $thecode .= $field8 . ": <input type=text name=user8><br>\n";
        }
        if ($f9chk == 1) {
            $fchkd[9] = ' checked';
            $thecode .= $field9 . ": <input type=text name=user9><br>\n";
        }
        if ($f10chk == 1) {
            $fchkd[10] = ' checked';
            $thecode .= $field10 . ": <input type=text name=user10><br>\n";
        }


        if ($scheck == 1) {
            $schk = ' checked';
            $buttext = "Go!";
        } else {
            $buttext = "Subscribe Me!";
        }

        if ($htcheck == 1) {
            $htchk = ' checked';
            $buttext = "Go!";
        }

        if (!$subtype || $subtype == 'menu')
            $rmenuchk = ' checked';
        if ($subtype == 'radi')
            $rradchk = ' checked';

        if ($scheck == 1) {
            if ($subtype == 'menu') {
                $thecode .= "<select name=submode>\n<option value=sub>Subscribe</option>\n<option value=unsub>Remove</option>\n</select>\n";
            }
            if ($subtype == 'radi') {
                $thecode .= "<input type=radio name=submode value=sub checked>Signup\n<input type=radio name=submode value=unsub>Remove\n";
            }
        }

        if (!$httype || $httype == 'menu')
            $htmenuchk = ' checked';
        if ($httype == 'radi')
            $htradchk = ' checked';

        if ($htcheck == 1) {
            if ($httype == 'menu') {
                $thecode .= "<select name=htmail>\n<option value=html>HTML</option>\n<option value=text>Text</option>\n</select>\n";
            }
            if ($httype == 'radi') {
                $thecode .= "<input type=radio name=htmail value=HTML checked>HTML\n<input type=radio name=htmail value=text>Text\n";
            }
        }

        $thecode .= "<input type=submit name=sup value=\"$buttext\">\n</form>";

        echo "

<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
        listmenu($list, 'listsup');
        echo "
  </td>
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
      <input class=button type=button id=ch value=\"Errors\" style=\"width: 52px\" onclick=\"hopto('./editemsg.php?eid=$eid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button_on type=button id=sc value=\"Signup Code\" style=\"width: 86px\"  onclick=\"hopto('./editlists.php?list=$list&getcode=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\">
     </td>
   </tr>
  </table>
  <img src=1.gif height=3><br>
  
  
  <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr class=table_head_bg_nopad><td><span class=table_head_text>Subscribe Form Generator</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('signup_form') . "');\">Help</a><br></td></tr></table>
<img src=1.gif height=5><br>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td align=center>
   <font style=\"font: bold 8pt helvetica\">Code Options - Press \"Re-Generate Code\" to update the code and example.</font><br>
   <!-- options etc -->
   <table border=0 cellspacing=2 cellpadding=0>
    <tr bgcolor=#eeeeee>
     <td>
      <form name=codeopts method=post>
      <input type=hidden name=elist value=$elist>
      <input type=hidden name=list value=$list>
      <input type=hidden name=getcode value=1>
      <input type=hidden name=modform value=1>
      <font size=2>&nbsp;First Name Field:<input type=checkbox name=fcheck value=1$fchk><br>&nbsp;Last Name Field:</font><input type=checkbox name=lcheck value=1$lchk><br>
     </td>
     <td>
      <font size=2>&nbsp;Sub/Unsub Option:<input type=checkbox name=scheck value=1$schk><br><input type=radio name=subtype value=menu$rmenuchk>Menu<input type=radio name=subtype value=radi$rradchk>Radio</font><br>
     </td>
     <td>
      <font size=2>&nbsp;Text/HTML Option:<input type=checkbox name=htcheck value=1$htchk><br><input type=radio name=httype value=menu$htmenuchk>Menu<input type=radio name=httype value=radi$htradchk>Radio</font><br>
    </td>
    </tr>
   </table>
   <!-- custom codes -->
   <table border=0 cellspacing=0 cellpadding=0><tr><td><font size=2>\n";

        while (list($key, $val) = each($fields)) {
            if ($val) {
                echo "$val <input type=checkbox name=f" . $key . "chk value=1" . $fchkd[$key] . ">&nbsp;&nbsp; ";
            }
        }

        echo "</font></td></tr></table><img src=1.gif height=3><br>


   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <input class=button type=submit value=\"Re-Generate Code\">
     </td></form>
     <td width=3><img src=1.gif width=3></td>
     <td><input class=button type=button value=\"Edit List Fields\" onclick=\"popitup('./editlists.php?list=$list&efields=1&PHPSESSID=$PHPSESSID',
'','450','800');\"></td>
    </tr>
   </table>
   <br>
  </td>
 </tr>
 <tr>
  <td align=center>
  <font style=\"font: bold 8pt helvetica\">Here's the code you'll probably want to edit further:</font><br>
   <!-- code table -->
   <textarea class=xbox name=code cols=100 rows=10>" . htmlspecialchars($thecode) . "</textarea><br><br>
  <font style=\"font: bold 8pt helvetica\">And here's a <i>working</i> preview!</font><br>
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td align=center>
$thecode
     </td>
    </tr>
   </table>
<div align=left>
<input class=button type=button value=\"Back to List\" onclick=\"hopto('./editlists.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 90px\"><br></div>
  </td>
 </tr>
</table>
</td></tr></table>

";
        exit;
    } // endif getcode
// main page

    if (!$getcode) {
// begin script
// main page
        adminheader('LM: Mail List Edit', "List $list Settings", '');
        $cmd = "select id,listnum,title,sende,sendn,welsubj,welcom,welcht,welcf,welcact,cnfsubj,cnfmesg,cnfht,cnff,cnfact,remsubj,remmsg,remht,remf,remact,remopts,req1,req2,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,notifs,nmails,listopts,chtmlid,errorid,addopts,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list'";
        $result = mysql_query($cmd) or die(mysql_error());
        list($id, $listnum, $title, $sende, $sendn, $welsubj, $welcom, $welcht, $welcf, $welcact, $cnfsubj, $cnfmesg, $cnfht, $cnff, $cnfact, $remsubj, $remmsg, $remht, $remf, $remact, $remopts, $req1, $req2, $f1, $f2, $f3, $f4, $f5, $f6, $f7, $f8, $f9, $f10, $notifs, $nmails, $listopts, $cid, $eid, $addopts, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row($result);
        if ($req1 == 1)
            $req1chk = ' checked'; else
            $req1chk = '';
        if ($req2 == 1)
            $req2chk = ' checked'; else
            $req2chk = '';

        $rqx = '';
        $nocf = 1;
        for ($i = 1; $i <= 10; $i++) {
            $rq = '';
            list($fn, $rq, $null) = explode('/*LMP*/', ${"f" . $i});
            if ($rq == '1')
                $rqx = '1';
            if ($fn)
                $nocf = '';
        }
        if ($rqx == '1')
            $reqxchk = ' checked'; else
            $reqxchk = '';
        if ($cnfact == 1) {
            $cactcheck = ' checked';
        } else {
            $cactcheck = '';
        }
        if ($welcact == 1) {
            $wactcheck = ' checked';
        } else {
            $wactcheck = '';
        }
        if ($remact == 1) {
            $ractcheck = ' checked';
        } else {
            $ractcheck = '';
        }
        list($rem_every, $rem_max) = explode(';', $remopts);
        $xnotifs = explode(";", $notifs);
        $xopts = explode(";", $listopts);

        list($xopts[6], $flseq, $fldel) = explode(':', $xopts[6]);
        if (!is_numeric($flseq) || !isset($flseq) || $flseq < 1) {
            list($flseq) = mysql_fetch_row(mysql_query("select seq from $ftable where list = '$list' order by seq limit 1"));
            if (!$flseq)
                $flseq = '1';
        }
        if (!is_numeric($fldel) || !isset($fldel) || $fldel < 0) {
            list($fldel) = mysql_fetch_row(mysql_query("select del from $ftable where list = '$list' order by seq limit 1"));
            if (!$fldel)
                $fldel = '0';
        }
        list($add_rem, $add_add, $rem_rem, $rem_add, $floadperc, $floadid, $fmovelist, $fmoveseq, $fmovedel) = explode(';', $addopts);
        if ($xopts[0] == 1)
            $closcheck = ' checked'; else
            $closcheck = '';
        if ($xopts[1] == 1)
            $cnfcheck = ' checked'; else
            $cnfcheck = ''; 
        if ($xopts[2] == 1)
            $add_remcheck = ' checked'; else
            $add_remcheck = '';
        if ($xopts[3] == 1)
            $add_addcheck = ' checked'; else
            $add_addcheck = '';
        if ($xopts[4] == 1)
            $rem_remcheck = ' checked'; else
            $rem_remcheck = '';
        if ($xopts[5] == 1)
            $rem_addcheck = ' checked'; else
            $rem_addcheck = '';
        if ($xopts[6] == 1)
            $floopcheck = ' checked'; else
            $floopcheck = '';
        if ($xopts[7] == 1)
            $floadcheck = ' checked'; else
            $floadcheck = '';
        if ($xopts[8] == 1)
            $fmovecheck = ' checked'; else
            $fmovecheck = '';
        if ($xopts[9] == 1)
            $fdelcheck = ' checked'; else
            $fdelcheck = '';
        if ($xnotifs[0] == 1)
            $n1check = ' checked'; else
            $n1check = '';
        if ($xnotifs[1] == 1)
            $n2check = ' checked'; else
            $n2check = '';
        if ($xnotifs[2] == 1)
            $n3check = ' checked'; else
            $n3check = '';
        if ($xnotifs[3] == 1)
            $n4check = ' checked'; else
            $n4check = '';
        if ($xnotifs[4] == 1)
            $n5check = ' checked'; else
            $n5check = '';

        if ($remote == '1')
            $remote = ' checked'; else
            $remote = '';


        echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

        listmenu($list, 'listset');
        echo "   <!-- end left menu -->
  </td>
  <td width=10 valign=top><img src=1.gif width=10></td>
  <td valign=top>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <table width=100% border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <form id=form1 method=post action=$PHP_SELF>
      <input class=button type=button id=sls value=\"Save List\" onclick=\"form1.submit();\" style=\"width: 80px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button value=\"Custom Fields\" onclick=\"hopto('./editlists.php?list=$list&efields=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 96px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Custom HTML\" onclick=\"hopto('./editchtml.php?cid=$cid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 92px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Errors\" style=\"width: 52px\" onclick=\"hopto('./editemsg.php?eid=$eid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=sc value=\"Signup Code\" onclick=\"hopto('./editlists.php?list=$list&getcode=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 86px\">
     </td>
     <td><img src=1.gif height=1></td>
     <td width=1>
      <input class=button type=button id=nl value=\"New List\" onclick=\"hopto('./editlists.php?list=$list&doadd=yes')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>\n";
// get next list #
        $lrow = mysql_query("select listnum from $ltable where 1 order by listnum desc limit 1;");
        list($ln) = mysql_fetch_row($lrow);
        $ln++;
        echo "      <input class=button type=button id=nl value=\"Copy List\" onclick=\"if(confirm('This will create a new list ($ln) with the same settings and followups as the current list ($list).\\n\\nAre you sure?')) hopto('./editlists.php?list=$list&docopy=1'); else return false;\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=dl value=\"Delete List\" onclick=\"if (!confirm('Are you sure you want to DELETE list $listnum: " . addslashes($title) . "?\\n\\nNote! This will delete all USERS and settings for this list permanently!')){ return false } else { hopto('./editlists.php?listid=$listnum&delid=$id&dodel=1') }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 68px\">
     </td>
    </tr>
   </table>\n";
    }

    echo "  </td>
 </tr>
</table>
<img src=1.gif height=3><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Mailing List Settings</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table>
  </td>
 </tr>
  <tr>
   <td>
     <!-- body padding -->
     <table class=bg2 width=100% border=0 cellspacing=0 cellpadding=3>
      <tr>
       <td>
     <!-- body inside -->
     <img src=1.gif height=2><br>
     <table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr>
       <td bgcolor=#eeeeee colspan=2>
        <table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
          <td>
        <input type=hidden name=doedit value=yes>
        <input type=hidden name=editid value=\"$id\">
        <span class=table_inside_small>List&nbsp;Ref.&nbsp;#
        <input class=xbox type=text name=textlist value=\"$listnum\" size=2 style=\"width: 35px\"></span>
          </td>
          <td align=right>
        <span class=table_inside_small>List Title</span>
        <input type=text class=xbox name=texttitl size=50 value=\"" . htmlspecialchars($title) . "\">
          </td>
         </tr>
        </table>
       </td>
      </tr>
      <tr>
       <td height=4><img src=1.gif height=4></td>
      </tr>
      <tr>
       <td colspan=2>
        <table width=100% border=0 cellspacing=0 cellpadding=0>
         <tr>
          <td width=1>
        <span class=table_inside_small>Send&nbsp;as&nbsp;Name&nbsp;</span>
          </td>
          <td>
          <input type=text class=xbox name=textsendn size=38 value=\"" . htmlspecialchars($sendn) . "\">
          </td>
          <td align=right>
        <span class=table_inside_small>Send&nbsp;as&nbsp;Email
        <input class=xbox type=text size=38 name=textsende value=\"$sende\">
          </td>
         </tr>
        </table>
       </td>
      </tr>
      <tr>
       <td height=4><img src=1.gif height=4></td>
      </tr>
      <tr>
       <td colspan=2>
        <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Required&nbsp;Fields <input type=checkbox name=txtreq3 value=1 checked>Email Address (Mandatory) &nbsp;
         <input type=checkbox name=txtreq1 value=1" . $req1chk . ">First Name &nbsp;
         <input type=checkbox name=txtreq2 value=1" . $req2chk . ">Last Name";
    if (!$nocf) {
        echo "&nbsp;
          <a class=inside href=./editlists.php?list=$list&efields=1 onclick=\"if(!confirm('You will lose unsaved changes on this page. OK?')) return false;\"> <input type=checkbox name=txtreq2 value=1 disabled=disabled$reqxchk>Custom Fields</a>";
    }
    echo "</span><br>
         </td>
        </tr></table>
      </td>
     </tr>
    </table>
    <img src=1.gif height=3><br>
    <table border=0 cellspacing=0 cellpadding=0 width=100%>
     <tr>
      <td>
       <span class=table_inside_small>Custom HTML </span><select name=chtml class=xbox style=\"width: 220px\">";

    $nfs = '- Not Found! - Choose Below -';
    list($nf) = mysql_fetch_row(mysql_query("select count(*) from $chdtable where id = '$cid';"));
    if ($nf == 0)
        echo "<option value=x selected>$nfs";

// get all custom html
    $rows = mysql_query("select id,name from $chtable where 1 order by id");
    while (list($id, $name) = mysql_fetch_row($rows)) {
        if ($id == $cid)
            $sel = ' selected'; else
            $sel = '';
        echo "<option value=$id$sel>$name";
    }
    echo "</select></span>
      </td>
      <td align=right>
       <span class=table_inside_small>Error Messages </span><select name=emsgs class=xbox style=\"width: 220px\">";

    list($nf) = mysql_fetch_row(mysql_query("select count(*) from $edtable where id = '$eid';"));
    if ($nf == 0)
        echo "<option value=x selected>$nfs";

// get all error msgs
    $rows = mysql_query("select id,name from $etable where 1 order by id");
    while (list($id, $name) = mysql_fetch_row($rows)) {
        if ($id == $eid)
            $sel = ' selected'; else
            $sel = '';
        echo "<option value=$id$sel>$name";
    }
    echo "</select></span>
      </td>
     </tr>
    </table>
    <table border=0 cellspacing=0 cellpadding=0 width=100%>
     <tr>
      <td>
       <img src=1.gif height=3><br>
       <img src=1.gif width=5><input name=closchk type=checkbox style=\"width: 13px\" value=1$closcheck><img src=1.gif width=7><span class=table_inside_small>Closed list, only allow manual additions & imports</span><br>
      </td><td width=10><img src=1.gif width=10></td><td>
        <img src=1.gif height=3><br>
        <img src=1.gif width=5><input name=cnfchk type=checkbox style=\"width: 13px\" value=1$cnfcheck><img src=1.gif width=7><span class=table_inside_small>Keep removed users in database but don't mail</span><br>
      </td>
     </tr>
    </table>
    <!-- end inside -->
     </td>
    </tr>
   </table>
    <!-- end padding 1 -->
     </td>
    </tr>
   </table>
   <!-- end outer 1 -->

     <img src=1.gif height=4><br>

     <!-- outer 2 -->
     <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
      <tr>
       <td class=table_head_bg><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Welcome & Confirmation</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table></td>
      </tr>
      <tr>
       <td>
        <!-- padding 2 -->
        <table width=100% border=0 cellspacing=0 cellpadding=3>
         <tr>
          <td class=bg2>
          
           <table width=100% border=0 cellspacing=0 cellpadding=0>
            <tr>
             <td valign=top>
              <input type=checkbox name=txtcact value=1" . $cactcheck . ">&nbsp;<span class=table_inside_small>Send confirmation email (double opt-in) on signup to verify deliverability</span><br>
             </td>
             <td align=right>
              <a class=inside href=./editlists.php?list=$list&editwc=c>[ Edit Message ]</a><br>
             </td>
            </tr>
            <tr>
             <td valign=top>
              <input type=checkbox name=txtract value=1" . $ractcheck . ">&nbsp;<span class=table_inside_small>Send confirmation reminder email every <input type=text class=xbox name=txtrem_every size=2 style=\"width: 20px\" value=\"" . $rem_every . "\"> days to a maximum of <input type=text class=xbox name=txtrem_max size=2 style=\"width: 20px\" value=\"" . $rem_max . "\"> messages</span><br>
             </td>
             <td align=right>
              <a class=inside href=./editlists.php?list=$list&editwc=r>[ Edit Message ]</a><br>
             </td>
            </tr>
            <tr>
             <td>
              <input type=checkbox name=txtwact value=1" . $wactcheck . ">&nbsp;<span class=table_inside_small>Send welcome email on signup or when confirmed</span><br>
             </td>
             <td align=right>
              <a class=inside href=./editlists.php?list=$list&editwc=w>[ Edit Message ]</a><br>
             </td>
            </tr>
           </table>
          </td>
         </tr>
        </table>
        <!-- end padding 2 -->


      </td>
     </tr>
    </table>
    <!-- end outer 2 -->


<!-- additional onremove/onadd -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Additional Operations</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee style=\"padding: 3px 3px 3px 0px\">
   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=add_remchk type=checkbox style=\"width: 13px\" value=1" . $add_remcheck . "><img src=1.gif width=7><span class=table_inside_small>When users are added to this list remove them from the following list(s): </span><br>
     </td><td align=right><span class=table_inside_small>*</span><img src=1.gif width=2><input type=text class=xbox name=textadd_rem size=10 maxlength=100 value=\"" . htmlspecialchars($add_rem) . "\" style=\"width: 90px;\"><br>
     </td>
    </tr>
   </table>

   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=add_addchk type=checkbox style=\"width: 13px\" value=1$add_addcheck><img src=1.gif width=7><span class=table_inside_small>When users are added to this list also add them to the following list(s): </span><br>
     </td><td align=right><input type=text class=xbox name=textadd_add size=10 maxlength=40 value=\"" . htmlspecialchars($add_add) . "\" style=\"width: 90px;\"><br>
     </td>
    </tr>
   </table>

   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=rem_remchk type=checkbox style=\"width: 13px\" value=1$rem_remcheck><img src=1.gif width=7><span class=table_inside_small>When users are removed from this list also remove them from the following list(s): </span><br>
     </td><td align=right><input type=text class=xbox name=textrem_rem size=10 maxlength=40 value=\"" . htmlspecialchars($rem_rem) . "\" style=\"width: 90px;\"><br>
     </td>
    </tr>
   </table>

   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=rem_addchk type=checkbox style=\"width: 13px\" value=1$rem_addcheck><img src=1.gif width=7><span class=table_inside_small>When users are removed from this list add them to the following list(s): </span><br>
     </td><td align=right><input type=text class=xbox name=textrem_add size=10 maxlength=40 value=\"" . htmlspecialchars($rem_add) . "\" style=\"width: 90px;\"><br>
     </td>
    </tr>
   </table>
   <div align=right class=table_inside_small><small>* Enter List #s. Separate multiples with commas.</small></div>
  </td>
 </tr>
</table>
   <!-- end additional onremove/onadd -->

<!-- followup options -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Followup Options</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee style=\"padding: 0px 3px 3px 0px\">
   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=floopchk type=checkbox style=\"width: 13px\" value=1$floopcheck onclick=\"if(this.checked){ if(form1.floadchk.checked==true){ form1.floadchk.checked=false; } if(form1.fmovechk.checked==true){ form1.fmovechk.checked=false; } if(form1.fdelchk.checked==true){ form1.fdelchk.checked=false; }}\"><img src=1.gif width=7><span class=table_inside_small>Loop followups. Automatically reset users at end of sequence to Seq <input type=text class=xbox size=2 name=flseq style=\"width: 20px\" value=$flseq> Del <input type=text class=xbox size=2 name=fldel style=\"width: 20px\" value=$fldel> each day before dailymail<br>
     </td>
    </tr>
   </table>

   <img src=1.gif height=3><br>
   <div class=table_inside_small><img src=1.gif width=5><input name=fmovechk type=checkbox style=\"width: 13px\" value=1$fmovecheck onclick=\"if(this.checked){ if(form1.floadchk.checked==true){ form1.floadchk.checked=false; } if(form1.floopchk.checked==true){ form1.floopchk.checked=false; } if(form1.fdelchk.checked==true){ form1.fdelchk.checked=false; }}\"><img src=1.gif width=7><span class=table_inside_small>Move users who reach end of followup sequence to list <input name=fmovelist type=text class=xbox style=\"width: 24px\" value=\"" . htmlspecialchars($fmovelist) . "\"> Seq <input name=fmoveseq type=text class=xbox style=\"width: 24px\" value=\"" . htmlspecialchars($fmoveseq) . "\"> Del <input name=fmovedel type=text class=xbox style=\"width: 24px\" value=\"" . htmlspecialchars($fmovedel) . "\"></div>
   
   <img src=1.gif height=3><br>
   <div class=table_inside_small><img src=1.gif width=5><input name=fdelchk type=checkbox style=\"width: 13px\" value=1$fdelcheck onclick=\"if(this.checked){ if(form1.floadchk.checked==true){ form1.floadchk.checked=false; } if(form1.floopchk.checked==true){ form1.floopchk.checked=false; } if(form1.fmovechk.checked==true){ form1.fmovechk.checked=false; }}\"><img src=1.gif width=7><span class=table_inside_small>Remove users who reach end of followup sequence</div>

   <table border=0 cellspacing=0 cellpadding=0 width=100%>
    <tr>
     <td>
      <img src=1.gif height=3><br>
      <img src=1.gif width=5><input name=floadchk type=checkbox style=\"width: 13px\" value=1$floadcheck onclick=\"if(this.checked){ if(form1.fmovechk.checked==true){ form1.fmovechk.checked=false; } if(form1.floopchk.checked==true){ form1.floopchk.checked=false; } if(form1.fdelchk.checked==true){ form1.fdelchk.checked=false; }}\"><img src=1.gif width=7><span class=table_inside_small>When ";

    if (is_numeric($floadperc))
        ${"fupsel" . $floadperc} = ' selected';

    echo "<select class=xbox name=floadperc style=\"width: 55px\" onchange=\"form1.floadchk.checked=true; form1.floopchk.checked=false;\">
<option value=100$fupsel100>100%<option value=95$fupsel95>95%<option value=90$fupsel90>90%<option value=85$fupsel85>85%<option value=80$fupsel80>80%<option value=75$fupsel75>75%<option value=70$fupsel70>70%<option value=65$fupsel65>65%<option value=60$fupsel60>60%<option value=55$fupsel55>55%<option value=50$fupsel50>50%<option value=45$fupsel45>45%<option value=40$fupsel40>40%<option value=35$fupsel35>35%<option value=30$fupsel30>30%<option value=25$fupsel25>25%<option value=20$fupsel20>20%<option value=15$fupsel15>15%<option value=10$fupsel10>10%<option value=5$fupsel5>5%</select> of users are at end of sequence reset users and load saved set *<br>
     </td>
<td align=right><select class=xbox name=floadid style=\"width: 140px;\" onchange=\"form1.floadchk.checked=true; form1.floopchk.checked=false;\"><option value=>-- Choose Set --\n";
    $rows = mysql_query("select id,name from $fstable where 1 order by id");
    if (@mysql_num_rows($rows) > 0) {
        while (list($fsid, $fsname) = mysql_fetch_row($rows)) {
            if ($fsid == $floadid)
                $sel = ' selected'; else
                $sel = '';
            echo "<option value=$fsid$sel>$fsname";
        }
    } else {
        echo "<option value=>None";
    }
    echo "</select><br>
     </td>      
    </tr>
   </table>
   <div class=table_inside_small align=right><small>* This option will only run once and then be disabled automatically.</small></div>
  </td>
 </tr>
</table>
   <!-- followup options -->
   
    <!-- notifs -->

<!-- admin email notifs -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Email Notification</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad -->
   <table width=100% border=0 cellspacing=0 cellpadding=3><tr><td><img src=1.gif height=1><br>
    <table width=100% border=0 cellspacing=0 cellpadding=0>
     <tr>
      <td colspan=2 valign=top>
       <img src=1.gif height=2><br>
       <!-- margin --><span class=table_inside_small>Email address(es) to notify, separated by commas</span>
      </td>
      <td align=right nowrap valign=top><img src=1.gif width=5><input type=text class=xbox name=tnmails value=\"" . htmlspecialchars($nmails) . "\" size=61 style=\"width: 341px\"><br>
      </td>
     </tr>
     <tr>
      <td>
       <img src=1.gif height=1><br>
       <img src=1.gif width=5><input name=n1chk type=checkbox style=\"width: 13px\" value=1$n1check><img src=1.gif width=7><span class=table_inside_small>Notify when a user subscribes to this list</span><br>
      </td><td width=10><img src=1.gif width=10></td><td>
        <img src=1.gif height=1><br>
        <img src=1.gif width=5><input name=n2chk type=checkbox style=\"width: 13px\" value=1$n2check><img src=1.gif width=7><span class=table_inside_small>Notify when a user unsubscribes from this list</span><br>
      </td>
     </tr>
     <tr>
      <td>
       <img src=1.gif height=3><br>
       <img src=1.gif width=5><input name=n3chk type=checkbox style=\"width: 13px\" value=1$n3check><img src=1.gif width=7><span class=table_inside_small>Notify when a user is manually added</span><br>
      </td><td width=10><img src=1.gif width=10></td><td>
        <img src=1.gif height=3><br>
        <img src=1.gif width=5><input name=n4chk type=checkbox style=\"width: 13px\" value=1$n4check><img src=1.gif width=7><span class=table_inside_small>Notify when a user is removed for bouncing</span><br>
      </td>
     </tr>
    </table>
   </td></tr></table>
  </td>
 </tr>
</table>

    <!-- end notifs -->
    <!-- remote/local list settings -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Remote Listing Options</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('list_settings') . "');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad -->
   <table width=100% border=0 cellspacing=0 cellpadding=3>
   <tr><td><img src=1.gif height=1><br>
    <table width=100% border=0 cellspacing=0 cellpadding=0>
     <tr>
      <td>
       <img src=1.gif height=1><br>
       <img src=1.gif width=5><input name=remote type=checkbox style=\"width: 13px\" value=1" . $remote . "><img src=1.gif width=7><span class=table_inside_small>Store this list on a remote host</span><br>
      </td>
        <td><img src=1.gif height=1><br>
            <img src=1.gif width=5><span class=table_inside_small>Remote host address: <input type=text class=xbox name=remotehost value=\"" . htmlspecialchars($remotehost) . "\" size=30 style=\"\">
        </td>
     </tr>
     <tr>
        <td colspan=2><img src=1.gif height=1><br>
            <img src=1.gif width=5><span class=table_inside_small>Remote host database: <input type=text class=xbox name=remotedb value=\"" . htmlspecialchars($remotedb) . "\" size=30 style=\"\">
        </td>
     </tr>
     <tr>
        <td><img src=1.gif height=1><br>
       <img src=1.gif width=5><span class=table_inside_small>Remote host username: <input type=text class=xbox name=remoteuser value=\"" . htmlspecialchars($remoteuser) . "\" size=20 style=\"\"></td>
        <td><span class=table_inside_small>Remote host password: <input type=text class=xbox type=password name=remotepwd value=\"" . htmlspecialchars($remotepwd) . "\" size=20 style=\"\"></td>
     </tr>
    </table>
   </td></tr></table>
  </td>
 </tr>
</table>

    <!-- end remote/local list settings -->

    <img src=1.gif height=3><br>
   <table width=100% border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <input class=button type=button id=sls value=\"Save List\" onclick=\"form1.submit();\" style=\"width: 80px\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td></form>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button value=\"Custom Fields\" onclick=\"hopto('./editlists.php?list=$list&efields=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 96px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Custom HTML\" onclick=\"hopto('./editchtml.php?cid=$cid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 92px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=ch value=\"Errors\" style=\"width: 52px\" onclick=\"hopto('./editemsg.php?eid=$eid&list=$list')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=sc value=\"Signup Code\" onclick=\"hopto('./editlists.php?list=$list&getcode=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 86px\">
     </td>
     <td><img src=1.gif height=1></td>
     <td width=1>
      <input class=button type=button id=nl value=\"New List\" onclick=\"hopto('./editlists.php?list=$list&doadd=yes')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=nl value=\"Copy List\" onclick=\"hopto('./editlists.php?list=$list&docopy=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input class=button type=button id=dl value=\"Delete List\" onclick=\"if (!confirm('Are you sure you want to DELETE list $listnum: " . addslashes($title) . "?\\n\\nNote! This will delete all USERS and settings for this list permanently!')){ return false } else { hopto('./editlists.php?listid=$listnum&delid=$id&dodel=1') }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 68px\">
     </td>
    </tr>
   </table>\n";
    if ($logtxt)
        echo "<img src=./1.gif height=5><br><span class=status_text>$logtxt</span><br>";
    echo "  </td>
 </tr>
</table>
<!-- <script language=javascript>form1.nl.focus();</script> -->
  </td>
 </tr>
</table>
<img src=1.gif height=10><br>
</div>
<script language=javascript>window.status='$logtxt';</script>
</body>
</html>";

    mysql_close($link);
}
// EOF
?>
