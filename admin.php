<?php

/* ListMail (c) 2006 Dean Wiebe <dean@listmailpro.com>
  You may not modify or distribute the program without express permission from the author.
  Files are overwritten when updates are released, so it's not a good idea anyway. */

$current_version = '1.87'; // db
$file_version = '1.88';

ini_set('error_reporting', '2039');
session_cache_limiter('private, must-revalidate');

if (strpos(' ' . $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') > 0)
    $windows = 1; else
    $windows = '';

$sqldebug = '';
$timedebug = '';

if (get_magic_quotes_gpc() == 1)
    $gpc = 1;
if (!isset($gpc))
    $gpc = '';

if (isset($_GET)) {
    while (list($key, $val) = each($_GET)) {
        if (!is_array($val)) {
            if ($gpc)
                $val = stripslashes($val);
            @eval("\$$key = \$val;");
        } else {
            eval("\$$key = array();");
            foreach ($val as $key2 => $val2) {
                if ($gpc)
                    $val2 = stripslashes($val2);
                @eval("array_push(\$$key,\$val2);");
            }
        }
    }
}

// POST overrides GET

if (isset($_POST)) {
    while (list($key, $val) = each($_POST)) {
        if (!is_array($val)) {
            if ($gpc)
                $val = stripslashes($val);
            @eval("\$$key = \$val;");
        } else {
            eval("\$$key = array();");
            foreach ($val as $key2 => $val2) {
                if ($gpc)
                    $val2 = stripslashes($val2);
                @eval("array_push(\$$key,\$val2);");
            }
        }
    }
}

if (isset($_SERVER["PHP_SELF"]))
    $PHP_SELF = $_SERVER["PHP_SELF"];

// todo, add international charsets

$ctable = 'lm_config';
$ltable = 'lm_lists';
$ftable = 'lm_follow';
$fstable = 'lm_follows';
$utable = 'newsletter_subscribers'; // $utable = 'lm_users';
$ktable = 'lm_links';
$vtable = "lm_custom";
$stable = "lm_saved";
$htable = "lm_hits";
$ttable = "lm_sent";
$dtable = "lm_codes";
$qtable = "lm_schedule";
$otable = "lm_sendq";
$ptable = "lm_sendp";
$setable = "lm_select";
$sedtable = "lm_selectd";
// being removed or new use
$itable = "lm_logs";
// v1.8
$chtable = 'lm_chtml';
$chdtable = 'lm_chtmld';
$etable = 'lm_error';
$edtable = 'lm_errord';

// all tables (for backup & optimize)
$atables = array($ctable, $utable, $ftable, $ltable, $ktable, $stable, $htable, $ttable, $dtable, $qtable, $otable, $ptable, $setable, $sedtable, $chtable, $chdtable, $etable, $edtable);

// IIS fix
if ($windows) {
    error_reporting(E_ALL ^ E_NOTICE);
}
ini_set("max_execution_time", "0");

// mimeclass path
if (!$mp)
    $mp = '.';

// global date array
$dar = explode('/', date("H/i/s/m/d/Y"));

function adminheader($title, $headtxt, $xtra, $xlist = '') {
    global $demo;
    global $charset;
    global $otable;
    global $ttable;
    global $ptable;
    global $list;
    global $special;
    $jumpdoc = '';
    if ($xlist)
        $listx = '?list=' . $xlist;
    if ($xtra == 'jumplists')
        $jumpdoc = '<meta http-equiv="refresh" content="1; URL=./editlists.php' . $listx . '">';
    if ($xtra == 'autoinst')
        $jumpdoc = '<meta http-equiv="refresh" content="1; URL=./editconf.php?doinst=1&doinstall=yes">';
    if ($special) {
        if (substr($special, 0, 14) == 'refresh-cookie')
            $jumpdoc = '<meta http-equiv="refresh" content="1; URL=./editconf.php?backup=1&restored=1&xid=' . substr($special, 15) . '">';
    }

    $dadate = date("l, F jS  h:ia");
    echo "<html>
<head>
<meta name=\"robots\" content=\"noindex,nofollow\">
<title>$title</title>\n";

    if ($charset)
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\n"; else
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";

    echo "<style type=text/css rel=stylesheet> <!--

form { margin: 0px 0px 0px 0px; }

.outer_table {
	border: 1px solid #000000;
}

.cur_list_bg {
	background: #394273;
}

.table_head_bg {
	background: #394273;
	border-bottom: 1px solid #000000;
}

.table_head_bg_nopad {
	background: #394273;
}

.table_head_text {
	font-family: verdana,arial,helvetica;
	font-size: 10pt;
	color: white;
	margin-left: 1px;
}

.table_head_help, .table_head_help:visited, .table_head_help:hover {
	font-family: verdana,arial,helvetica;
	font-size: 10pt;
	color: white;
	margin-right: 1px;
	text-decoration: none;
}

.table_head_col {
	font-family: verdana,arial,helvetica;
	font-size: 8pt;
	color: black;
}
.table_head_col2 {
	font: 8pt verdana;
	color: black;
	text-decoration: none;
}

.table_inside_bg {
	background: #eeeeee;
}

.table_inside_toolbar {
	font-family: arial,helvetica;
	font-size: 10pt;
	color: black;
}

.table_inside_small {
	font-family: arial;
	font-size: 9pt;
	color: black;
}
.tis, .tis:visited {
	font-family: arial;
	font-size: 9pt;
	color: blue;
	text-decoration: none;
}
.tis:hover { color: navy; }


.table_inside_normal {
	font-family: arial;
	font-size: 10pt;
	color: black;

}
.sysinfo_head {
	font: bold 10pt arial;
	color: black;
	text-decoration: underline;
}
.sysinfo_option_head {
	font: 9pt arial;
	color: black;
}
.sysinfo_option {
	font: 9pt arial;
	color: navy;
}
a.sysinfo_link,a.sysinfo_link:visited {
	font: 9pt arial;
	color: red;
	text-decoration: none;
}
a.sysinfo_link:hover {
	text-decoration: underline;
}


a.inside,a.inside:visited { font-family: arial; font-size: 9pt; color: navy; text-decoration: none }
a.inside:hover { text-decoration: underline; color: black }


.status_text {
	font-family: arial;
	font-size: 9pt;
	color: navy;
}
.status_link, .status_link:visited {
        font: bold 9pt arial;
	color: navy;
} .status_link:hover { color: blue }
.status_link2, .status_link2:visited {
        font: 9pt arial;
	color: navy;
} .status_link2:hover { color: blue }

.input_dropdown {
	font-family: arial;
	font-size: 9pt;
	color: black;
}

.button { font-family: helvetica;
	font-size: 8pt;
	border: 1px solid #000000;
	background: #eeeeee;
	cursor: pointer;
}
.button_on { font: bold 8pt helvetica; color: black; border: 1px solid #000000; background: #dddddd;}
.button_over { font: bold 8pt helvetica; color: navy; border: 1px solid #000000; background: #eeeeee; cursor: pointer; }
.button_click { font: 8pt helvetica; color: navy; border: 2px solid; background: #eeeeee; }


.bbutton {   font: 8pt helvetica;
	border: 1px solid #000000;
	background: #dddddd;
	cursor: pointer;
}

.rbutton { font-family: helvetica;
	font-size: 8pt;
	border: 1px solid #000000;
	background: #eeeeee;
	cursor: pointer;
	margin-bottom: 1px;
}


.no_border_textbox {
 font: 9pt helvetica;
 color: black;
 border: 1px solid white;
}

.user_button { font-family: helvetica;
	font-size: 8pt;
	border: 1px solid #000000;
	background: #eeeeee;
	cursor: pointer;
	width: 80px;
}



.code_example {
	font-family: times new roman;
	font-size: 12pt;
	font-weight: bold;
	color: black;
}
.code_type {
	font-family: times new roman;
	font-size: 10pt;
	color: black;
}
.sent_msg {
	font-family: helvetica;
	font-size: 9pt;
}

.link_tracked_text {
	font-family: times new roman;
	font-size: 12pt;
	color: black;
}

.rev_dropdown {
  font-family: helvetica;
  font-size: 8pt; border:
  1px solid black;
  background: #eeeeee;
  color: black;
}

a.menulink,a.menulink:visited { font-family: verdana; font-size: 9pt; color: black; text-decoration: none }
a.menulink:hover { text-decoration: underline; }

.xarea { font-family: helvetica; font-size: 8pt; border: 1px solid black; }
.xselect {
 font-family: helvetica;
 font-size: 9pt;
 color: black;
 border: 1px solid black;
}

.xbox {
 font-family: helvetica;
 font-size: 9pt;
 color: black;
 border: 1px solid black;
}

.xlink,.xlink:visited { font-family: verdana; font-size: 9pt; color: black; text-decoration: none }
.xlink:hover { text-decoration: underline }

.smxarea { font-family: verdana; font-size: 8pt; border: 1px solid #000000; }

.hmh { font-family: verdana; font-size: 11pt }
.hmt { font-family: verdana; font-size: 10pt }
a.hml,a.hml:visited { 	font-family: arial;
	font-size: 8pt;
	color: blue;
	text-decoration: none; }
a.hml:hover { color: navy; }

.conflist { font: 9pt verdana; color: black }

.wbutton { font-family: helvetica;
 font-size: 8pt;
 border: 1px solid #000000;
 background: #ffffff;
 cursor: pointer;
}

.bg2 { background: #eeeeee }



.smbold { font: 17pt helvetica }

body {

}

.bigtext { font: 16pt verdana; color: black; }

.toplink,.toplink:visited { font-family: helvetica; font-size: 9pt; color: black; text-decoration: underline }
.toplink:hover { color: black; }

.tbhead { font: bold 10pt verdana; }
.tbint { font: 18pt helvetica; }
.atbint,.atbint:visited { font: 18pt helvetica; color: black; }
.atbint:hover { color: blue; }

.resume0 { font: 8pt arial; }
.resume1 { font: 9pt arial }
.resume2 { font: 8pt arial; text-decoration: underline }
.resume_k1,.resume_k1:visited { font: 9pt arial; text-decoration: none; color: navy }
.resume_k1:hover { text-decoration: underline }

.chkbox { width: 13px; padding:0; margin:0; }
.chkbox_left { width: 13px; margin-left: 0px }
.chkbox_nom { margin: 0px 0px 0px 0px; padding: 0px 0px 0px 0px; width: 13px; height: 13px; }

.helv_8pt_bold { font: bold 8pt helvetica }
.verd_8pt { font: 8pt verdana; }
.helv_8pt { font: 8pt helvetica; }

--> </style>
$jumpdoc
</head>
<body bgcolor=white topmargin=10 leftmargin=10 rightmargin=10>
<center>
<script language=javascript> <!--

function popitup(url,opts,h,w){

 if(!opts || (h && w)){
  var x = 0, y = 0; // default values
  if (document.all) {
    x = window.screenTop + 46;
    y = window.screenLeft;
  }
  else if (document.layers) {
    x = window.screenX + 46;
    y = window.screenY;
  }

  var cw = 0, ch = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    cw = window.innerWidth;
    ch = window.innerHeight;
  } else if( document.documentElement &&
      ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    cw = document.documentElement.clientWidth;
    ch = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    cw = document.body.clientWidth;
    ch = document.body.clientHeight;
  }

  y = y + (cw / 2) - 380 + 132;
  if(!w && !h){
   h = ch - 75;
   w = 618
  }
 }
 if (newwindow.location && !newwindow.closed){
  newwindow.location.href = url;
 } else {
  if(!opts){
newwindow=window.open(url,'popup','location=no,resizable=yes,scrollbars=yes,status=no,titlebar=no,toolbar=no,width='+w+',height='+h+',top='+x+',screenY='+y+',left='+y+',screenX='+x);
  } else {
   newwindow=window.open(url,'name',opts);
  }
  if (!newwindow.opener) newwindow.opener = self;
 }
 if (window.focus) {newwindow.focus()}
}

--> </script>
<script language=javascript> <!--

var newwindow='';
function jumpto(url_list){
   var URL = url_list.options[url_list.selectedIndex].value;
   window.location.href = URL;
}
--> </script>
<script language=javascript> <!--
function hopto(URL){
   window.location.href = URL;
}
--> </script>\n";
// $xtra='nologo';
    if ($xtra <> 'nologo') {
        echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
";
        //<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=420 valign=bottom><a href=http://listmailpro.com><img src=logo.gif width=420 height=28 border=0></a><br></td><td valign=bottom align=right><span class=bigtext>$headtxt</span></td></tr></table>
   if(substr($_SERVER['REQUEST_URI'],-strlen('login.php')) != 'login.php')
       echo "<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=420 valign=bottom><a href=http://listmailpro.com><img src=logo.gif width=420 height=28 border=0></a><br></td><td valign=bottom align=right><span class=bigtext>$headtxt</span></td></tr></table>";
echo " </td>
 </tr>
 </table>\n";

        if ($xtra <> 'resume' && $xtra <> 'login' && $xtra <> 'daymail' && $xtra <> 'logout' && $xtra <> 'jumplists') {
            $cmd = "select id,bat,battype,mid from $otable where 1 group by bat order by battype, mid";
            $orows = mysql_query($cmd);
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            if (@mysql_num_rows($orows) > 0) {

                echo "<table width=760 border=0 cellspacing=0 cellpadding=0><tr><td colspan=8><span class=resume0><font color=red>Attention</font>: You have items in the outgoing queue.  Some mailings may be in progress.</span><br></td></tr>
  <tr>
   <td width=100><span class=resume2>Started</span><br></td>
   <td width=5><img src=./1.gif width=5></td>
   <td width=50><span class=resume2>Type</span><br></td>
   <td width=5><img src=./1.gif width=5></td>
   <td width=100><span class=resume2>Subject</span><br></td>
   <td width=5><img src=./1.gif width=5></td>
   <td width=75><span class=resume2>Messages&nbsp;Left</span><br></td>
   <td><img src=./1.gif width=1><br></td>
  </tr>";
                while (list($rid, $rbat, $rbattype, $mid) = mysql_fetch_row($orows)) {
                    $prow = mysql_query("select started from $ptable where batid = '$rbat';");
                    if (@mysql_num_rows($prow) > 0) {
                        list($rstart) = mysql_fetch_row($prow);
                        $rstart = date("Y-m-d H:i", dt2epoch($rstart));
                    } else
                        $rstart = 'Unknown';
                    if ($rbattype == '1') {
                        $rtypetxt = 'Solo&nbsp;Email';
                        // get email subject
                        $cmd = "select subject from $ttable where id = '$mid'";
                        $srow = mysql_query($cmd) or die('admin-1-' . mysql_error());
                        if ($sqldebug)
                            echo "CMD=<b>$cmd</b><br>";
                        list($subj) = mysql_fetch_row($srow);
                    }
                    if ($rbattype == '2') {
                        $rtypetxt = 'Dailymail';
                        $subj = 'N/A';
                    }
                    if ($rbattype == '3') {
                        $rtypetxt = 'Welcome&nbsp;Email';
                        $subj = 'N/A';
                    }
                    if ($rbattype == '4') {
                        $rtypetxt = 'Confirmation&nbsp;Email';
                        $subj = 'N/A';
                    }



                    $cmd = "select id from $otable where bat = '$rbat'";
                    $cntrows = mysql_query($cmd) or die('admin-2-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    $msgsleft = mysql_num_rows($cntrows);
                    $subj = str_replace(' ', '&nbsp;', $subj);
                    echo "<tr><td width=100 nowrap><span class=resume1>$rstart</span><br></td>
    <td width=5><img src=./1.gif width=5></td>
    <td width=50 nowrap><span class=resume1>$rtypetxt</span><br></td>
    <td width=5><img src=./1.gif width=5></td>
    <td width=100 nowrap><table border=0 cellspacing=0 cellpadding=0><tr><td nowrap><span class=resume1>$subj</span><br></td></tr></table></td>
    <td width=5><img src=./1.gif width=5></td>
    <td width=75 nowrap><span class=resume1>$msgsleft</span><br></td>
    <td nowrap><a class=resume_k1 href=./domail.php?list=$list&resume=1&batid=" . $rbat . ">Resume</a><span class=resume1> | </span><a class=resume_k1 href=./domail.php?list=$list&delete_queue=1&batid=" . $rbat . " onclick=\"if(!confirm('Warning! If you cancel this mailing some messages may not be sent!\\n\\nAre you sure you want to Cancel?')) return false;\">Cancel</a><span class=resume1><br>
   </td></tr>";
                }
                echo "</table>";
            }
        }
        echo "
<!-- space from header -->
<table border=0 cellspacing=0 cellpadding=0><tr><td height=6><img src=1.gif height=6></td></tr></table>\n";
    }
}

// end adminheader

function checkpw($inpw) {
    global $ctable;
    $cmd = "select daypw from $ctable where 1";
    $prow = mysql_query($cmd) or die('admin-3-' . mysql_error());
    if ($sqldebug)
        echo "CMD=<b>$cmd</b><br>";
    list($daypw) = mysql_fetch_row($prow);
    if ($inpw <> $daypw) {
        if ($pw == '') {
            adminheader("Admin Login", "Admin Login", '');
        } else {
            adminheader("Access Denied", "Access Denied", '');
        }
        echo "Access Denied.<br>";
        return 'false';
    } else
        return 'true';
}

// end checkpw

function processmsg($uid, $subj, $msg = '', $htmsg = '', $mhtml = '0', $mtyp = '', $mid = '', $r = '') {
    global $dtable;
    global $ktable;
    global $utable;
    global $ltable;
    global $ctable;
    global $link;
    global $dar;
    if ($uid == 'preview')
        $pre = 1; else
        $pre = '';
    $prow = mysql_query("select linkcode,keycode,listmailpath,ktrack from $ctable where 1", $link) or die('admin-4-' . mysql_error());
    list($linkch, $keych, $lmpath, $ktr) = mysql_fetch_row($prow);
    $inarr = array($subj, $msg, $htmsg);
    //echo 'subject: '.$subj;//debug
    //echo 'msg: '.$msg;//debug
    //echo '$htmsg: '.$htmsg;//debug
    if (!$uid && !$pre)
        return $inarr;

    if (!$pre) {
        // get list title before getting user info
        $cmd = "select title,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$lnum'";
        $lrow = @mysql_query($cmd, $link) or die('admin-6-' . mysql_error());
        if ($sqldebug)
            echo "CMD=<b>$cmd</b><br>";
        list($ltitle,$remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = @mysql_fetch_row($lrow);
    } else
        $ltitle = 'Preview List';

    //echo 'subject: '.$subj;//debug
    //echo 'msg: '.$msg;//debug
    //echo '$htmsg: '.$htmsg;//debug
    if (!$pre) {
        // get user info
        $cmd = "select id,uid,list,fname,lname,email,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,dateadd,ipaddr,refurl from $utable where id = '$uid'";
        
        if($remote){
            try {
                $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                $dbh_query = $dbh->query($cmd);
            } catch (PDOException $e) {
                die('admin-5-' . $e->getMessage());
            }
            list($id, $usid, $lnum, $fname, $lname, $email, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $dadd, $uip, $refu) = $dbh_query->fetch();
            $dbh = null; //close the connection
            //echo 'lnum:'.$lnum.'<br>';
        }else{
            $urow = mysql_query($cmd, $link) or die('admin-5-' . mysql_error());
            list($id, $usid, $lnum, $fname, $lname, $email, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $dadd, $uip, $refu) = mysql_fetch_row($urow);
        }
    } else {
        $usid = 'preview';
        $lnum = 'X';
        $fname = 'FIRSTNAME';
        $lname = 'LASTNAME';
        $email = 'preview@' . getdomain();
        $user1 = 'CUSTOM1';
        $user2 = 'CUSTOM2';
        $user3 = 'CUSTOM3';
        $user4 = 'CUSTOM4';
        $user5 = 'CUSTOM5';
        $user6 = 'CUSTOM6';
        $user7 = 'CUSTOM7';
        $user8 = 'CUSTOM8';
        $user9 = 'CUSTOM9';
        $user10 = 'CUSTOM10';
        $dadd = date("Y-m-d");
        $uip = '0.0.0.0';
        $refu = 'http://' . getdomain() . '/preview_signup.html';
    }
    //echo 'subject: '.$subj;//debug
    //echo 'msg: '.$msg;//debug
    //echo '$htmsg: '.$htmsg;//debug//debug
    while (list($k, $v) = each($inarr)) {
        if ($v) {
            $xmsg = $v;
            if ($k == 2)
                $mhtml = 1; else
                $mhtml = 0;

            if (!$pre) {
                // custom text
                $drows = @mysql_query("select ref,typ,data1,data2 from $dtable where (list = '$lnum' or list = '0') and typ = 'text' order by list desc,ref") or die('admin-7-' . mysql_error());
                if (@mysql_num_rows($drows) > 0) {
                    while (list($ref, $typ, $data1, $data2) = @mysql_fetch_row($drows)) {
                        if ($mhtml == '1') {
                            $data1 = str_replace("\r\n", "\n", $data1);
                            if ($data2 <> '1')
                                $data1 = str_replace("\n", "<br>", $data1);
                            $xmsg = eregi_replace($keych . $ref, $data1, $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $ref, $data1, $xmsg);
                        }
                    }
                }
            }

            // links
            $cmd = "select refcode,title from $ktable where 1 order by refcode desc";
            $result = mysql_query($cmd, $link) or die('admin-8-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            while (list($refcode, $ktitle) = @mysql_fetch_row($result)) {
                if ($refcode <> '') {
                    if ($mhtml == '1') {
                        // html msg
                        if ($ktr == 1) {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, "<a class=codelink href=\"" . $lmpath . "link.php?id=" . $usid . $refcode . "\">" . $ktitle . "</a>", $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, "<a class=codelink href=\"" . $lmpath . "link.php?id=" . $refcode . "\">" . $ktitle . "</a>", $xmsg);
                        }
                    } else {
                        // not an html msg
                        if ($ktr == 1) {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, $lmpath . "link.php?id=" . $usid . $refcode, $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, $lmpath . "link.php?id=" . $refcode, $xmsg);
                        }
                    }
                }
            }
            // done links
            // message codes
            $cmd = "select id,ref,typ,data1,data2,data3,data4,data5 from $dtable where (list = '$lnum' or list = '0') and typ <> 'text' order by list desc,ref";
            $drows = @mysql_query($cmd, $link) or die('admin-9-' . mysql_error());
            //echo $cmd.'<br>';
            //echo 'num of rows: '.mysql_num_rows($drows);
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            while (list($cid, $ref, $typ, $data1, $data2, $data3, $data4, $data5) = @mysql_fetch_row($drows)) {
                // process each code
                if ($ref)
                    switch ($typ) {
                        // fname,lname,fullname,remove,text,date,user1-10

                        case 'fname' :
                            if (!$fname && $data1)
                                $fname = $data1;
                            if ($data2)
                                $fname = capitalize($fname);
                            $xmsg = eregi_replace($keych . $ref, $fname, $xmsg);
                            break;

                        case 'lname' :
                            if (!$lname && $data1)
                                $lname = $data1;
                            if ($data2)
                                $lname = capitalize($lname);
                            $xmsg = eregi_replace($keych . $ref, $lname, $xmsg);
                            break;

                        case 'fullname' :
                            $fullname = $fname;
                            if ($lname)
                                $fullname .= " " . $lname;
                            if ($data2)
                                $fullname = capitalize($fullname);
                            if (!$fullname && $data1)
                                $fullname = $data1;
                            $xmsg = eregi_replace($keych . $ref, $fullname, $xmsg);
                            break;

                        case 'email' :
                            $xmsg = eregi_replace($keych . $ref, $email, $xmsg);
                            break;

                        case 'remove' :
                            if ($data4)
                                $c = '&c=' . $cid;
                            $rlink = $lmpath . "rem.php?";
                            if ($data5 == '1' || !$data5)
                                $rlink .= "u=$usid";
                            if ($data5 == '2')
                                $rlink .= "c=$cid&u=$usid";
                            if ($data5 == '3')
                                $rlink .= "u=$cid,$usid";
                            if ($data5 == '4')
                                $rlink .= "x=$cid&l=$lnum&e=$email";//echo 'unsubscribe: '.$rlink.'<br>';//debug
                            if ($data5 == '5')
                                $rlink .= "x=$cid,$lnum,$email";

                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=coderem href=\"$rlink\">$data2</a>";
                            } else {

                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $rlink;
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'remcnfht' :
                            $bcode = "<form name=rconf method=post>
<input type=hidden name=list value=$list>
<input type=hidden name=dodel value=1>
<input type=hidden name=u value=$usid>
<input type=hidden name=r value=$r>
<input type=hidden name=c value=" . htmlspecialchars($mid) . ">
<input class=confirm_button type=submit value=\"$data1\">
</form>\n";
                            $xmsg = eregi_replace($keych . $ref, $bcode, $xmsg);
                            break;

                        case 'confirm' :
                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=coderem href=\"" . $lmpath . "confirm.php?u=$usid\">$data2</a>";
                            } else {

                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $lmpath . "confirm.php?u=$usid";
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'date' :
                            $today = date($data1, mktime($dar[0], $dar[1], $dar[2], $dar[3] + $data3, $dar[4] + $data2, $dar[5] + $data4));
                            $xmsg = eregi_replace($keych . $ref, $today, $xmsg);
                            break;

                        case 'dateadd' :
                            list($y, $m, $d) = dateparse($dadd, 'break');
                            $xdadd = date($data1, mktime(0, 0, 0, $m + $data3, $d + $data2, $y + $data4));
                            $xmsg = eregi_replace($keych . $ref, $xdadd, $xmsg);
                            break;

                        case 'title' :
                            $xmsg = eregi_replace($keych . $ref, $ltitle, $xmsg);
                            break;

                        case 'uid' :
                            $xmsg = eregi_replace($keych . $ref, $usid, $xmsg);
                            break;

                        /* case 'field2' :
                          $xmsg = eregi_replace($keych . $ref,$user2,$xmsg);
                          break; */

                        case (substr($typ, 0, 5) == 'field'):
                            $fnum = substr($typ, 5, 2);
                            if (!${"user" . $fnum} && $data1)
                                ${"user" . $fnum} = $data1;
                            if ($mhtml == '1') {
                                ${"user" . $fnum} = str_replace("\r\n", "\n", ${"user" . $fnum});
                                ${"user" . $fnum} = str_replace("\n", "<br>", ${"user" . $fnum});
                                $xmsg = eregi_replace($keych . $ref, ${"user" . $fnum}, $xmsg);
                            } else {
                                $xmsg = eregi_replace($keych . $ref, ${"user" . $fnum}, $xmsg);
                            }
                            break;

                        case 'ip' :
                            $xmsg = eregi_replace($keych . $ref, $uip, $xmsg);
                            break;

                        case 'refurl' :
                            $xmsg = eregi_replace($keych . $ref, $refu, $xmsg);
                            break;

                        case 'link2html':
                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=link2html href=\"" . $lmpath . "showpage.php?u=$usid&m=$mtyp$mid\">$data2</a><br>";
                            } else {
                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $lmpath . "showpage.php?u=$usid&m=$mtyp$mid";
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'numsubs' :
                            list($nsu) = mysql_fetch_row(mysql_query("select count(*) from $utable where list = '$lnum';"));
                            if ($data1 && is_numeric($data1))
                                $nsu = $nsu + $data1;
                            $xmsg = eregi_replace($keych . $ref, "$nsu", $xmsg);
                            break;
                        // end switch
                    }
                    
                // no if ref closing bracket
                // end message code database
                // word wrap
                /* if($mhtml==0){
                  $cmd="select wrapon,wrapcols from $ctable where 1";
                  $wraprow=mysql_query($cmd,$link);
                  if($sqldebug) echo "CMD=<b>$cmd</b><br>";
                  while(list($wrapon,$wrapcols) = @mysql_fetch_row($wraprow)){
                  if($wrapon==1){ $xmsg = wordwrap($xmsg,$wrapcols);  }
                  }
                  } */

                // ^^ removed
                // $xmsg = str_replace("\r","\\r",str_replace("\n","\\n\n",$xmsg));
                // echo "<textarea rows=20 cols=50>$xmsg</textarea>";
                $outarr[$k] = $xmsg;
            } // end each msg while
        } // if v
    } // msgwhile

    return $outarr;
}

function processmsg2($id, $usid,$lnum, $fname, $lname, $email,$refu,$uip,$user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $subj, $msg = '', $htmsg = '', $mhtml = '0', $mtyp = '', $mid = '', $r = '') {
    global $dtable;
    global $ktable;
    global $utable;
    global $ltable;
    global $ctable;
    global $link;
    global $dar;
    if ($id == 'preview')
        $pre = 1; else
        $pre = '';
    $prow = mysql_query("select linkcode,keycode,listmailpath,ktrack from $ctable where 1", $link) or die('admin-4-' . mysql_error());
    list($linkch, $keych, $lmpath, $ktr) = mysql_fetch_row($prow);
    $inarr = array($subj, $msg, $htmsg);

    if (!$id && !$pre)
        return $inarr;
    
    if (!$pre) {
        // get list title before getting user info
        $cmd = "select title,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$lnum'";
        $lrow = @mysql_query($cmd, $link) or die('admin-6-' . mysql_error());
        if ($sqldebug)
            echo "CMD=<b>$cmd</b><br>";
        list($ltitle,$remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = @mysql_fetch_row($lrow);
    } else
        $ltitle = 'Preview List';
    
    if (!$pre) {
        // get user info
        /*$cmd = "select id,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,dateadd,ipaddr,refurl from $utable where id = '$id'";
        
        if($remote){
            try {
                $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                echo 'Connected with PDO!';
                $dbh_query = $dbh->query($cmd);
            } catch (PDOException $e) {
                die('admin-5-' . $e->getMessage());
            }
            list($id, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $dadd, $uip, $refu) = $dbh_query->fetch();
            echo 'listnum='.$lnum;
        }else{
            $urow = mysql_query($cmd, $link) or die('admin-5-' . mysql_error());
            list($id, $usid, $lnum, $fname, $lname, $email, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $dadd, $uip, $refu) = mysql_fetch_row($urow);
        }*/
    
    } else {
        $usid = 'preview';
        $lnum = 'X';
        $fname = 'FIRSTNAME';
        $lname = 'LASTNAME';
        $email = 'preview@' . getdomain();
        $user1 = 'CUSTOM1';
        $user2 = 'CUSTOM2';
        $user3 = 'CUSTOM3';
        $user4 = 'CUSTOM4';
        $user5 = 'CUSTOM5';
        $user6 = 'CUSTOM6';
        $user7 = 'CUSTOM7';
        $user8 = 'CUSTOM8';
        $user9 = 'CUSTOM9';
        $user10 = 'CUSTOM10';
        $dadd = date("Y-m-d");
        $uip = '0.0.0.0';
        $refu = 'http://' . getdomain() . '/preview_signup.html';
    }
    
    while (list($k, $v) = each($inarr)) {
        if ($v) {
            $xmsg = $v;
            if ($k == 2)
                $mhtml = 1; else
                $mhtml = 0;

            if (!$pre) {
                // custom text
                $drows = @mysql_query("select ref,typ,data1,data2 from $dtable where (list = '$lnum' or list = '0') and typ = 'text' order by list desc,ref") or die('admin-7-' . mysql_error());
                if (@mysql_num_rows($drows) > 0) {
                    while (list($ref, $typ, $data1, $data2) = @mysql_fetch_row($drows)) {
                        if ($mhtml == '1') {
                            $data1 = str_replace("\r\n", "\n", $data1);
                            if ($data2 <> '1')
                                $data1 = str_replace("\n", "<br>", $data1);
                            $xmsg = eregi_replace($keych . $ref, $data1, $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $ref, $data1, $xmsg);
                        }
                    }
                }
            }

            // links
            $cmd = "select refcode,title from $ktable where 1 order by refcode desc";
            $result = mysql_query($cmd, $link) or die('admin-8-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            while (list($refcode, $ktitle) = @mysql_fetch_row($result)) {
                if ($refcode <> '') {
                    if ($mhtml == '1') {
                        // html msg
                        if ($ktr == 1) {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, "<a class=codelink href=\"" . $lmpath . "link.php?id=" . $usid . $refcode . "\">" . $ktitle . "</a>", $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, "<a class=codelink href=\"" . $lmpath . "link.php?id=" . $refcode . "\">" . $ktitle . "</a>", $xmsg);
                        }
                    } else {
                        // not an html msg
                        if ($ktr == 1) {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, $lmpath . "link.php?id=" . $usid . $refcode, $xmsg);
                        } else {
                            $xmsg = eregi_replace($keych . $linkch . $refcode, $lmpath . "link.php?id=" . $refcode, $xmsg);
                        }
                    }
                }
            }
            // done links
            // message codes
            $cmd = "select id,ref,typ,data1,data2,data3,data4,data5 from $dtable where (list = '$lnum' or list = '0') and typ <> 'text' order by list desc,ref";
            $drows = @mysql_query($cmd, $link) or die('admin-9-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            while (list($cid, $ref, $typ, $data1, $data2, $data3, $data4, $data5) = @mysql_fetch_row($drows)) {
                // process each code
                if ($ref)
                    switch ($typ) {
                        // fname,lname,fullname,remove,text,date,user1-10

                        case 'fname' :
                            if (!$fname && $data1)
                                $fname = $data1;
                            if ($data2)
                                $fname = capitalize($fname);
                            $xmsg = eregi_replace($keych . $ref, $fname, $xmsg);
                            break;

                        case 'lname' :
                            if (!$lname && $data1)
                                $lname = $data1;
                            if ($data2)
                                $lname = capitalize($lname);
                            $xmsg = eregi_replace($keych . $ref, $lname, $xmsg);
                            break;

                        case 'fullname' :
                            $fullname = $fname;
                            if ($lname)
                                $fullname .= " " . $lname;
                            if ($data2)
                                $fullname = capitalize($fullname);
                            if (!$fullname && $data1)
                                $fullname = $data1;
                            $xmsg = eregi_replace($keych . $ref, $fullname, $xmsg);
                            break;

                        case 'email' :
                            $xmsg = eregi_replace($keych . $ref, $email, $xmsg);
                            break;

                        case 'remove' :
                            if ($data4)
                                $c = '&c=' . $cid;
                            $rlink = $lmpath . "rem.php?";
                            if ($data5 == '1' || !$data5)
                                $rlink .= "u=$usid";
                            if ($data5 == '2')
                                $rlink .= "c=$cid&u=$usid";
                            if ($data5 == '3')
                                $rlink .= "u=$cid,$usid";
                            if ($data5 == '4')
                                $rlink .= "x=$cid&l=$lnum&e=$email";
                            if ($data5 == '5')
                                $rlink .= "x=$cid,$lnum,$email";

                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=coderem href=\"$rlink\">$data2</a>";
                            } else {

                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $rlink;
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'remcnfht' :
                            $bcode = "<form name=rconf method=post>
<input type=hidden name=list value=$lnum>
<input type=hidden name=dodel value=1>
<input type=hidden name=u value=$usid>
<input type=hidden name=r value=$r>
<input type=hidden name=c value=" . htmlspecialchars($mid) . ">
<input class=confirm_button type=submit value=\"$data1\">
</form>\n";
                            $xmsg = eregi_replace($keych . $ref, $bcode, $xmsg);
                            break;

                        case 'confirm' :
                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=coderem href=\"" . $lmpath . "confirm.php?u=$usid&l=$lnum\">$data2</a>";
                            } else {

                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $lmpath . "confirm.php?u=$usid&l=$lnum";
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'date' :
                            $today = date($data1, mktime($dar[0], $dar[1], $dar[2], $dar[3] + $data3, $dar[4] + $data2, $dar[5] + $data4));
                            $xmsg = eregi_replace($keych . $ref, $today, $xmsg);
                            break;

                        case 'dateadd' :
                            list($y, $m, $d) = dateparse($dadd, 'break');
                            $xdadd = date($data1, mktime(0, 0, 0, $m + $data3, $d + $data2, $y + $data4));
                            $xmsg = eregi_replace($keych . $ref, $xdadd, $xmsg);
                            break;

                        case 'title' :
                            $xmsg = eregi_replace($keych . $ref, $ltitle, $xmsg);
                            break;

                        case 'uid' :
                            $xmsg = eregi_replace($keych . $ref, $usid, $xmsg);
                            break;

                        /* case 'field2' :
                          $xmsg = eregi_replace($keych . $ref,$user2,$xmsg);
                          break; */

                        case (substr($typ, 0, 5) == 'field'):
                            $fnum = substr($typ, 5, 2);
                            if (!${"user" . $fnum} && $data1)
                                ${"user" . $fnum} = $data1;
                            if ($mhtml == '1') {
                                ${"user" . $fnum} = str_replace("\r\n", "\n", ${"user" . $fnum});
                                ${"user" . $fnum} = str_replace("\n", "<br>", ${"user" . $fnum});
                                $xmsg = eregi_replace($keych . $ref, ${"user" . $fnum}, $xmsg);
                            } else {
                                $xmsg = eregi_replace($keych . $ref, ${"user" . $fnum}, $xmsg);
                            }
                            break;

                        case 'ip' :
                            $xmsg = eregi_replace($keych . $ref, $uip, $xmsg);
                            break;

                        case 'refurl' :
                            $xmsg = eregi_replace($keych . $ref, $refu, $xmsg);
                            break;

                        case 'link2html':
                            if ($mhtml == '1') {
                                if ($data1)
                                    $rtext = $data1 . "<br>"; else
                                    $rtext = '';
                                $rtext .= "<a class=link2html href=\"" . $lmpath . "showpage.php?u=$usid&m=$mtyp$mid\">$data2</a><br>";
                            } else {
                                if ($data1)
                                    $rtext = $data1 . "\n"; else
                                    $rtext = '';
                                $rtext .= $lmpath . "showpage.php?u=$usid&m=$mtyp$mid";
                            }
                            $xmsg = eregi_replace($keych . $ref, $rtext, $xmsg);
                            break;

                        case 'numsubs' :
                            list($nsu) = mysql_fetch_row(mysql_query("select count(*) from $utable where list = '$lnum';"));
                            if ($data1 && is_numeric($data1))
                                $nsu = $nsu + $data1;
                            $xmsg = eregi_replace($keych . $ref, "$nsu", $xmsg);
                            break;
                        // end switch
                    }
                // no if ref closing bracket
                // end message code database
                // word wrap
                /* if($mhtml==0){
                  $cmd="select wrapon,wrapcols from $ctable where 1";
                  $wraprow=mysql_query($cmd,$link);
                  if($sqldebug) echo "CMD=<b>$cmd</b><br>";
                  while(list($wrapon,$wrapcols) = @mysql_fetch_row($wraprow)){
                  if($wrapon==1){ $xmsg = wordwrap($xmsg,$wrapcols);  }
                  }
                  } */

                // ^^ removed
                // $xmsg = str_replace("\r","\\r",str_replace("\n","\\n\n",$xmsg));
                // echo "<textarea rows=20 cols=50>$xmsg</textarea>";
                $outarr[$k] = $xmsg;
            } // end each msg while
        } // if v
    } // msgwhile

    return $outarr;
}

function setfiletype($filen) {
    global $atype;
    $atype = '';
    global $etype;
    $etype = '';
    if (strtoupper(substr($filen, '-3', '3')) == 'HTM') {
        $atype = 'text/html';
        $etype = 'quoted-printable';
    }
    if (strtoupper(substr($filen, '-4', '4')) == 'HTML') {
        $atype = 'text/html';
        $etype = 'quoted-printable';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'TXT') {
        $atype = 'text/plain';
        $etype = 'quoted-printable';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'PHP') {
        $atype = 'application/octet-stream';
        $etype = 'quoted-printable';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'ZIP') {
        $atype = 'application/zip';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'BMP') {
        $atype = 'image/bmp';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'JPG') {
        $atype = 'image/jpeg';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-4', '4')) == 'JPEG') {
        $atype = 'image/jpeg';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'GIF') {
        $atype = 'image/gif';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'WAV') {
        $atype = 'audio/wave';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == '.AU') {
        $atype = 'audio/au';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'MID') {
        $atype = 'audio/midi';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-4', '4')) == 'MIDI') {
        $atype = 'audio/midi';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'MOV') {
        $atype = 'video/quicktime';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'AVI') {
        $atype = 'video/avi';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'MPG') {
        $atype = 'video/mpeg';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-4', '4')) == 'MPEG') {
        $atype = 'video/mpeg';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'WMV') {
        $atype = 'application/octet-stream';
        $etype = 'base64';
    } if (strtoupper(substr($filen, '-3', '3')) == 'MDB') {
        $atype = 'application/vnd.ms-access
';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'PDF') {
        $atype = 'application/pdf';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'CSV') {
        $atype = 'text/plain';
        $etype = 'quoted-printable';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'XLW') {
        $atype = 'application/vnd.ms-excel';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'XLS') {
        $atype = 'application/vnd.ms-excel';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'DOC') {
        $atype = 'application/msword';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'SWF') {
        $atype = 'application/x-shockwave-flash';
        $etype = 'base64';
    }
    if (strtoupper(substr($filen, '-3', '3')) == 'PPT') {
        $atype = 'application/octet-scream';
        $etype = 'base64';
    }
    if (!$atype) {
        $atype = 'application/octet-stream';
        $etype = 'base64';
    }
}

if (!$smtp_timeout || !is_numeric($smtp_timeout))
    $smtp_timeout = 9;

// domail!!!
function domail($sendq = '', $sendt = '', $xid = '', $batch) {
    global $ftable;
    global $ttable;
    global $ctable;
    global $ltable;
    global $utable;
    global $stable;
    global $demo;
    global $otable;
    global $ptable;
    global $list;
    global $mailcount;
    global $phpmailf;
    global $charset;
    global $html_to_aol;
    global $windows;
    global $smtp_debug;
    global $link;

    // include('./config.php');
    require_once('./mimeclass.php');
    require_once('./smtp.php');

    // determine paramaters
    if ($sendt == 'welc') {
        $welc = 1;
        $welcnf = 1;
    } else
        $welc = '';
    if ($sendt == 'conf') {
        $conf = 1;
        $welcnf = 1;
    } else
        $conf = '';
    if ($sendt == 'remi') {
        $remi = 1;
        $welcnf = 1;
    } else
        $remi = '';
    if ($sendt == 'solo')
        $solo = 1; else
        $solo = 0;
    if ($sendt == 'daym') {
        $daym = 1;
        if ($xid)
            $dayo = 1; else
            $dayo = '';
    } else
        $daym = '';

    if ($solo)
        echo "<center>";

    // config vars
    $qptime = time();
    $qpint = 20;
    $lmpdomain = getdomain();

    $cmd = "select ver,admmail,erraddr,errchk,delay1,delay2,delay3,mthost,mtport,mtauth,mtuser,mtpass,mtdelay,mtchk,mtphost,mtpport,mtpuser,mtppass,mtpdelay,mtpchk,listmailpath,textonly from $ctable where 1";
    $crow = mysql_query($cmd) or die('admin-10-' . mysql_error());
    list($ver, $admmail, $erraddr, $errchk, $del1, $del2, $del3, $mthost, $mtport, $mtauth, $mtuser, $mtpass, $mtdelay, $mtchk, $mtphost, $mtpport, $mtpuser, $mtppass, $mtpdelay, $mtpchk, $lmp, $otextonly) = mysql_fetch_row($crow);
    $otextonly = explode(';', $otextonly);
    list($mtchk, $mtlog) = explode(';', $mtchk);
    if ($mtlog == '1')
        $smtp_debug = 1;
    if ($sqldebug)
        echo "CMD=<b>$cmd</b><br>";
    $xmails = explode(';', $admmail);
    $admmail = $xmails[0];

    // turn into "ms"
    $mtpdelay = $mtpdelay * 100;
    if ($errchk == '0')
        $errmail = $admmail;
    if ($errchk == '2' || $errchk == '1')
        $errmail = $erraddr;

    // check if using smtp or mail()
    if ($mtchk == '1')
        $smtpsend = 1; else
        $smtpsend = '';

    if ($sendt == 'nosmtp')
        $smtpsend = '';

    // debug, shows SMTP messages
    // $smtp_debug = '1';

    if ($smtp_debug) {
        $bugfn = './attach/LM_SMTP_' . date("Y-m-d_h-i-s") . '.txt';
        if (!$bugf = @fopen($bugfn, 'w')) {
            echo '<font color=red>Warning</font>: Cannot write SMTP debug file (./attach/LM_SMTP_' . date("Y-m-d-h-i-s") . '.txt)<br>Is your ./attach folder CHMOD 777?  SMTP log disabled.<br><br>';
            flush();
            $smtp_debug = '';
        } else {

            function logwrite($bf, $in) {
                global $smtp_debug;
                if (fwrite($bf, $in) === false) {
                    echo "Error writing to SMTP debug file! (Out of disk space?)<br>";
                    $smtp_debug = '';
                }
            }

        }
    }

    if ($smtpsend) {
        // initialize display
        if ($mtpchk == '1') {
            $mtimer = '0';

            // date ex. 20020207164722
            $starttime = date("YmdHis");

            if ($solo || $dayo || $welcnf) {
                echo "<span class=table_inside_normal>Checking mail... </span>";
                flush();
            }

            $asock = fsockopen($mtphost, $mtpport);
            if (!$asock)
                $error = 1; else
                $error = '';
            if (!$error) {
                // connected!
                $srvmsg = fgets($asock, 1024);
                $lastmsg = substr($srvmsg, 0, 1);
                if ($lastmsg <> "+")
                    $error = 1; else
                    $error = '';
                if ($error) {
                    // server not found
                    if ($solo || $dayo || $welcnf)
                        echo " Cannot connect to <b>$mtphost</b><br>";
                } else {
                    if ($solo || $dayo || $welcnf)
                        echo " Connected!<br>Verifying username and password...";

                    // send user
                    fputs($asock, "USER $mtpuser\r\n");
                    $srvmsg = fgets($asock, 1024);
                    $lastmsg = substr($srvmsg, 0, 1);
                    if ($lastmsg <> "+")
                        $error = 1; else
                        $error = '';

                    // send pass
                    fputs($asock, "PASS $mtppass\r\n");
                    $srvmsg = fgets($asock, 1024);
                    $lastmsg = substr($srvmsg, 0, 1);
                    if ($lastmsg <> "+")
                        $error = 1; else
                        $error = '';
                    if ($error)
                        echo " Failed!<br>"; else
                        echo " Verified!<br>";

                    if (!$error) {
                        // check # of messages
                        fputs($asock, "STAT\r\n");
                        $srvmsg = fgets($asock, 1024);
                        $lastmsg = substr($srvmsg, 0, 1);
                        if ($lastmsg <> "+")
                            $error = 1;
                    }
                    // QUIT
                    fputs($asock, "QUIT\r\n");
                    $srvmsg = fgets($asock, 1024);
                    $lastmsg = substr($srvmsg, 0, 1);
                    if ($lastmsg <> "+")
                        $error = 1;
                    sleep(4);
                }
            } else
                echo "Failed!<br>";
        }

        if (!$mtauth == '1')
            $mtauth = '';

        // test connect
        if ($dayo || $solo || $welcnf)
            echo '<center><span class=table_inside_normal>Connecting to SMTP server... ';
        flush();

        // connect to SMTP

        $ssock = fsockopen($mthost, $mtport);
        if (!$ssock)
            $error = 1; else
            $error = '';
        if (!$error) {
            // connected!
            if ($solo || $dayo || $welcnf)
                echo "Connected!<br>";
            $srvmsg = '';
            $srvmsg = getsmtpmsg($ssock);
            if (!$error) {

                if (!$error) {
                    $smtpcmd = "EHLO localhost\r\n";
                    if ($smtp_debug)
                        logwrite($bugf, '> ' . $smtpcmd);
                    fputs($ssock, $smtpcmd);
                    $srvmsg = getsmtpmsg($ssock);
                    if ($smtp_debug)
                        logwrite($bugf, $srvmsg);
                    //if($smtp_debug) logwrite($bugf,'Is this where I think it is?');
                    $lastmsg = substr($srvmsg, 0, 3);
                    if ($lastmsg <> "250") {
                        if ($lastmsg == "500") {
                            if (!$helohost)
                                $helohost = 'localhost';
                            $smtpcmd = "HELO $helohost\r\n";
                            if ($smtp_debug)
                                logwrite($bugf, '> ' . $smtpcmd);
                            fputs($ssock, $smtpcmd);
                            $srvmsg = getsmtpmsg($ssock);
                            if ($smtp_debug)
                                logwrite($bugf, $srvmsg);
                            $lastmsg = substr($srvmsg, 0, 3);
                            if ($lastmsg <> "250")
                                $error = 1; else
                                $error = '';
                        } else
                            $error = 1;
                    } else {
                        $error = '';
                        if (strpos($srvmsg, 'PIPELINING') > 0) {
                            if ($smtp_debug)
                                logwrite($bugf, "LM: SMTP Pipelining Detected\r\n"); $pipeline = 1;
                        } else
                            $pipeline = '';

//      $pipeline = ''; echo "PIPELINING OFF<br>";
                    }
                }

                // auth login
                if ($mtauth && strpos($srvmsg, 'LOGIN') > 0) {
                    if ($smtp_debug)
                        logwrite($bugf, "LM: AUTH LOGIN Detected\r\n");
                    // EHLO says auth is good
                    //Start TLS/SSL here...
                    /* logwrite($bugf,"Start SSL\r\n");
                      fputs($ssock, "STARTTLS\r\n");
                      $srvmsg = getsmtpmsg($ssock);
                      logwrite($bugf,$srvmsg."");

                      stream_socket_enable_crypto($ssock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                      fputs($ssock, "Hello\r\n");
                      $srvmsg = getsmtpmsg($ssock);
                      logwrite($bugf,$srvmsg."");

                      $cnf = getcwd()."/certs/openssl.cnf";
                      $configArgs = array(
                      'config' =>$cnf
                      );

                      $res = openssl_pkey_new($configArgs); //Resource number
                      logwrite($bugf,$res."\r\n");
                      openssl_pkey_export($res, $privatekey); //Private key
                      logwrite($bugf,$privatekey."\r\n");
                      $publickey=openssl_pkey_get_details($res); //Public key
                      $publickey=$publickey["key"];
                      logwrite($bugf,$publickey."\r\n");

                      openssl_public_encrypt("ClientHello", $crypttext, $publickey); //encrypt any message
                      logwrite($bugf,openssl_error_string()."\r\n");
                      logwrite($bugf,$crypttext."\r\n");
                      fputs($ssock,$crypttext."\r\n");
                      $srvmsg = getsmtpmsg($ssock);
                      logwrite($bugf,$srvmsg."\r\n");

                      openssl_private_decrypt($srvmsg, $decrypted, $privatekey); // decrypt what server returns
                      logwrite($bugf,$decrypted."\r\n");
                     */
                    //End TLS/SSL here...

                    $smtpcmd = "AUTH LOGIN\r\n";
                    if ($smtp_debug)
                        logwrite($bugf, '> ' . $smtpcmd);
                    fputs($ssock, $smtpcmd);

                    $srvmsg = getsmtpmsg($ssock);
                    if ($smtp_debug)
                        logwrite($bugf, $srvmsg);
                    $lastmsg = substr($srvmsg, 0, 3);
                    if ($lastmsg <> "334")
                        $error = 1; else
                        $error = '';

                    if (!$error) {
                        // send username
                        if ($solo || $dayo || $welcnf)
                            echo "Authenticating User/Pass... ";

                        $smtpcmd = base64_encode($mtuser) . "\r\n";
                        if ($smtp_debug)
                            logwrite($bugf, '> ' . $smtpcmd);
                        fputs($ssock, $smtpcmd);

                        $srvmsg = getsmtpmsg($ssock);
                        if ($smtp_debug)
                            logwrite($bugf, $srvmsg);
                        $lastmsg = substr($srvmsg, 0, 3);

                        if ($lastmsg <> "334")
                            $error = 1; else
                            $error = '';
                        if (!$error) {
                            // send password

                            $smtpcmd = base64_encode($mtpass) . "\r\n";
                            if ($smtp_debug)
                                logwrite($bugf, '> ' . $smtpcmd);
                            fputs($ssock, $smtpcmd);

                            $srvmsg = getsmtpmsg($ssock);
                            if ($smtp_debug)
                                logwrite($bugf, $srvmsg);
                            $lastmsg = substr($srvmsg, 0, 3);
                            if ($lastmsg <> "235")
                                $error = 1; else
                                $error = '';
                        }
                        if ($error) {
                            if ($solo || $dayo || $welcnf)
                                echo "Bad password<br>";
                        } else {
                            if ($solo || $dayo || $welcnf)
                                echo "Authenticated!<br>";
                        }
                    } else {
                        if ($solo || $dayo || $welcnf)
                            echo "Username not accepted.<br>";
                    }
                    $noplain = 1;
                }

                // auth plain
                if ($mtauth && strpos($srvmsg, 'PLAIN') > 0 && !$noplain) {
                    // EHLO says auth is good
                    $smtpcmd = "AUTH PLAIN " . base64_encode("\0" . $mtuser . "\0" . $mtpass) . "\r\n";
                    if ($smtp_debug)
                        logwrite($bugf, '> ' . $smtpcmd);
                    fputs($ssock, $smtpcmd);
                    $srvmsg = getsmtpmsg($ssock);
                    if ($smtp_debug)
                        logwrite($bugf, $srvmsg);
                    $lastmsg = substr($srvmsg, 0, 3);
                    if ($lastmsg <> "235")
                        $error = 1; else
                        $error = '';
                    if ($error) {
                        if ($solo || $dayo || $welcnf)
                            echo "User/Pass not accepted.<br>";
                    }
                }
            } else {
                if ($solo || $dayo || $welcnf)
                    echo "EHLO failed! ($srvmsg)<br>";
            }
        } else {
            if ($solo || $dayo || $welcnf)
                echo "Server not found!<br>";
        }
        if (!$error) {
            // NOOP check server.
            $smtpcmd = "NOOP\r\n";
            logwrite($bugf, '> ' . $smtpcmd);
            if ($smtp_debug)
                logwrite($bugf, '> ' . $smtpcmd);
            fputs($ssock, $smtpcmd);
            $srvmsg = getsmtpmsg($ssock);
            if ($smtp_debug)
                logwrite($bugf, $srvmsg);
            $lastmsg = substr($srvmsg, 0, 3);
            if ($lastmsg <> "250")
                $error = 1; else
                $error = '';
        }
        if ($error) {
            if ($dayo || $solo || $welcnf)
                echo '<font color=red>Failed.</font><br><br>Check your SMTP settings!<br>';
            // if a solo mailing, save msg to saved and delete from sent.
            if ($solo) {
                echo " Your message has been saved for later.</small><br>";
                $cmd = "select subject,content,htcontent,fattach from $ttable where id = '$xid'";
                $res = mysql_query($cmd) or die('admin-11-' . mysql_error());
                if ($sqldebug)
                    echo "CMD=<b>$cmd</b><br>";

                list($subj, $cont, $htcont, $fatt) = mysql_fetch_row($res);
                // slashes for sql command
                $subj = addslashes($subj);
                $cont = addslashes($cont);
                $htcont = addslashes($htcont);
                $subj = "SAV: " . $subj;
                $cmd = "insert into $stable values('','s','','$subj','$cont','$htcont','$fatt')";
                mysql_query($cmd) or die('admin-12-' . mysql_error());
                if ($sqldebug)
                    echo "CMD=<b>$cmd</b><br>";
                echo "<form name=back method=post action=./editconf.php>
    <input class=button type=submit name=bs value=\"To Config\"></form>";
                exit;
            }

            // removed - dailymail, switch to non-smtp if smtp fails..
            if ($daym) {
                // $smtpsend=0;
                exit;
            }
        }
    }

    if ($solo && $xid <> 'resume') {
        // out of loop so we dont re-read msg if its a solo mailing, unless resuming..
        $cmd = "select subject,content,htcontent,fattach from $ttable where id = '$xid'";
        $msgrow = @mysql_query($cmd) or die('admin-13-' . mysql_error());
        if ($sqldebug)
            echo "CMD=<b>$cmd</b><br>";
        list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
    }
    if ($welc || $conf || $remi) {
        // out of loop so dont have to re-read for welcome/confirmation during import
        if ($welc) {
            // welcome message
            $cmd = "select welsubj,welcom,welcht,welcf from $ltable where listnum = '$xid'";
            $msgrow = @mysql_query($cmd) or die('admin-14-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
        }
        elseif ($conf) {
            // confirm message
            $cmd = "select cnfsubj,cnfmesg,cnfht,cnff from $ltable where listnum = '$xid'";
            $msgrow = @mysql_query($cmd) or die('admin-15-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
        }
        elseif ($remi) {
            // confirm message
            $cmd = "select remsubj,remmsg,remht,remf from $ltable where listnum = '$xid'";
            $msgrow = @mysql_query($cmd) or die('admin-15-' . mysql_error());
            if ($sqldebug)
                echo "CMD=<b>$cmd</b><br>";
            list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
        }
        // echo "subj=$subj msg=$msg<br>";
    }

    $y = 0;


    // queue loop !!!
    // read from lm_sendq and process..
    $cmd = "select id,mtype,uid,lid,mid,xtra from $otable where bat = '$batch'";
    //echo $cmd;//debug
    $mrows = @mysql_query($cmd) or die('admin-16-' . mysql_error());
    if ($sqldebug)
        echo "CMD=<b>$cmd</b><br>";

    // display send box
    $nmails = @mysql_num_rows($mrows);//echo 'number of mails: '.$nmails;//debug
    $skipped = 0;
    $txsize = strlen($nmails);
    $timeleft = 0;

    if ($dayo || $solo || $welcnf) {
        echo "<center></span><span id=delaymsg style=\"font: italic 8pt helvetica; display: none\">Delaying.. Please wait..</span><br><span class=table_inside_normal>If your mailing stops for any reason you can simply and safely <input class=button type=button onclick=\"hopto('./domail.php?resume=1&batid=$batch&list=$list')\" name=bs value=\"Resume\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 60px\"> sending.<br><br><form name=s>Sending to server: <input class=xbox type=text name=disp size=$txsize value=0>&nbsp;of&nbsp;$nmails.<br><br>Average speed: <input class=no_border_textbox type=text name=speed size=3 style=\"width: 37px\" value=0>per second. <input class=no_border_textbox type=text name=speed2 size=5 value=0 style=\"width: 50px\">per hour.<br>";
        if ($solo)
            echo "<br><input class=button type=button value=\"Back to Send Email\" onclick=\"hopto('./domail.php?list=$list&edit=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\"><br>"; echo "<!-- <br>Estimated Time Left: <input class=no_border_textbox type=text name=timeleft size=11 style=\"width: 85px; text-align: right\" value=\"$timeleft\"><br> --></span></form>";
        flush();
    }
    if (!$mailcount)
        $mailcount = 50;

    $start_date = explode(' ', date("Y m d H i s"));

    // msg date
    $tz = date("Z");
    $tzs = ($tz < 0) ? "-" : "+";
    $tz = abs($tz);
    $tz = ($tz / 3600) * 100 + ($tz % 3600) / 60;

    $sqlid = -1;
    if (@mysql_num_rows($mrows) > 0) {
        $first = 1;
        while (list($oid, $mtype, $uid, $lid, $msgid, $xtra) = mysql_fetch_row($mrows)) {
            echo 'oid='.$oid.'<br>';
            if ($first) {
                $lastmid = $msgid;
                $lastmt = $mtype;
            }
            $sqlid++;
            $skiptonext = '';
            $nouser = '';
            $error = '';
            //echo 'oid '.$oid.', uid='.$uid.', lid='.$lid.', mtype='.$mtype.'<br>';//debug 
            if ($mtype <> '5') {
                //Get list remote info
                $lcmd = "select remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = $list";
                echo $lcmd.'<br>';
                $lrows = @mysql_query($lcmd);
                list($remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = mysql_fetch_row($lrows);
                echo 'remote='.$remote;//debug
                // get user send vars
                $ucmd = "select id,uid,list,fname,lname,email,htmail,bounces from $utable where id = '$uid'";
                echo $ucmd.'<br>';//debug
                if($remote){
                    try {
                        $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
                        $dbh = new PDO($pdo_db, $remoteuser, $remotepwd); echo 'connected to dbh!<br>';//debug
                    } catch (PDOException $e) {
                        die('admin-39-' . $e->getMessage());
                    }
                    $dbh_query = $dbh->query($ucmd);
                    list($id, $unid, $mlist, $fname, $lname, $email, $htmail, $bounces) = $dbh_query->fetch(); //assuming there will only be 1 unique userid
                    $rcount = $dbh_query->rowCount();
                    echo 'email: '.$email.'<br> rcount='.$rcount.'<br>';//debug
                    $dbh = null; //close the connection
                }else{
                    $urow = mysql_query($ucmd, $link) or die('admin-17-' . mysql_error());echo mysql_error();   
                    list($id, $unid, $mlist, $fname, $lname, $email, $htmail, $bounces) = mysql_fetch_row($urow);
                    $rcount = @mysql_num_rows($urow);echo 'user='.$email;//debug
                }
                //$urow = mysql_query($ucmd) or die('admin-17-' . mysql_error());
                if ($rcount == 0) {
                    $nouser = 1;
                } else {
                    $nouser = '';
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    //list($unid, $mlist, $fname, $lname, $email, $htmail, $bounces) = mysql_fetch_row($urow);
                    if (in_array(strtolower(substr($email, strpos($email, '@') + 1)), $otextonly))
                        $textonly = 1; else
                        $textonly = '';
                    // echo "UID=$uid UNID=$unid  EMAIL=$email<br>";
                }
            } else
                $nouser = '';//echo 'nouser='.$nouser;//debug

            if (!$nouser) {
                // get message
                if ($mtype == '1' && (!$solo || $xid == 'resume') && ($lastmt <> $mtype || $lastmid <> $msgid || $first)) {
                    $cmd = "select subject,content,htcontent,fattach from $ttable where id = '$msgid'";
                    $msgrow = @mysql_query($cmd) or die('admin-18-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                    echo $subj.'<br>'.$msg.'<br>'.$htmsg.'<br>'.$fatt.'<br>';
                }
                if ($mtype == '2') {
                    // followup
                    $cmd = "select subject,content,htcontent,fattach from $ftable where id = '$msgid'";
                    $msgrow = @mysql_query($cmd) or die('admin-19-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '3' && (!$welc || $xid == 'resume')) {
                    // welcome message
                    $cmd = "select welsubj,welcom,welcht,welcf from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-20-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '4' && (!$conf || $xid == 'resume')) {
                    // confirm message
                    $cmd = "select cnfsubj,cnfmesg,cnfht,cnff from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-21-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }
                if ($mtype == '6' && (!$remi || $xid == 'resume')) {
                    // remind message
                    $cmd = "select remsubj,remmsg,remht,remf from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-21-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '5') {
                    $cmd = "select list from $utable where id = '$uid'";
                    $urow = @mysql_query($cmd) or die('admin-22-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($mlist) = @mysql_fetch_row($urow);

                    // admin email.. msgtype in sendq $xtra
                    $msgtype = $xtra;

                    if ($msgtype == 'bounce' || $msgtype == 'daily') {
                        $xmails = explode(';', $admmail);
                        $admmail = $xmails[0];
                        $email = $admmail;
                        // saved table
                        $cmd = "select subject,content,htcontent,fattach from $stable where id = '$msgid'";
                        $msgrow = @mysql_query($cmd) or die('admin-23-' . mysql_error());
                        if ($sqldebug)
                            echo "CMD=<b>$cmd</b><br>";
                        list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                        $cmd = "delete from $stable where id = '$msgid'";
                        @mysql_query($cmd) or die('admin-24-' . mysql_error());
                        if ($sqldebug)
                            echo "CMD=<b>$cmd</b><br>";
                    }
                    $today = date("D M jS H:m T");

                    if ($msgtype == 'daily') {
                        $subj = "Dailymail - $today";
                    }

                    /*    // get ip and ref url
                      global $REMOTE_ADDR; */
                    global $HTTP_REFERER;

                    getip();

                    $refurl = $_SERVER['HTTP_REFERER'];
                    if (!$refurl)
                        $refurl = $HTTP_REFERER; if (!$refurl)
                        $refurl = "Unknown";
                    $iphost = @gethostbyaddr($ipaddr);

                    if (!$sendn)
                        $sendn = 'ListMail Notification';
                    $sende = $errmail;
                } else {
                    // not admin msg, continue
                    $cmd = "select sendn,sende from $ltable where listnum = '$mlist'";
                    //echo $cmd.'<br>';//debug
                    $lrow = @mysql_query($cmd) or die('admin-25-' . mysql_error());
                    if ($sqldebug)
                        echo "CMD=<b>$cmd</b><br>";
                    list($sendn, $sende) = @mysql_fetch_row($lrow);//echo $sendn.' and '.$sende.'<br>';//debug
//    echo "got list settings<br>"; flush();
                }

                // send the message
                if ($subj && $msg && !$nouser) {
//    echo "processing message...<br>"; flush();
                    // process message codes
                    if ($timedebug)
                        $sttime = mtime();
                    if ($mtype <> '5') {
                        if ($timedebug)
                            $stime = mtime();
                        //list($xsubj, $xmsg, $xhtmsg) = processmsg($uid, $subj, $msg, $htmsg, '0', $mtype, $msgid);
                        list($xsubj, $xmsg, $xhtmsg) = processmsg2($id, $uid, $mlist, $fname, $lname, $email, '', '', '', '', '', '', '', '','','','','', $subj,'',$htmsg,'0',$mtype,$msgid);
                        //list($unid, $mlist, $fname, $lname, $email, $htmail, $bounces)
                        //processmsg2($id, $usid,$lnum, $fname, $lname, $email,$refu,$uip,$user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $subj, $msg = '', $htmsg = '', $mhtml = '0', $mtyp = '', $mid = '', $r = '') 
                        if ($timedebug)
                            $time = mtime() - $stime;
                        if ($timedebug)
                            echo "[processmsg] $time sec<br>\n";
//     $xmsg = processmsg($uid,$msg,'0',$mtype,$msgid);
                        /*      if($aol){
                          $xmsg = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\">$1</a>",$xmsg);
                          $xmsg = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\">$1</a>",$xmsg);
                          } */
                        //   $xhtmsg = processmsg($uid,$htmsg,'1',$mtype,$msgid);
                    } else {
                        // echo "SUBJ=$subj,MSG=$msg.<br>";
                        $xsubj = $subj;
                        $xmsg = $msg;
                        $xhtmsg = $htmsg;
                    }
                    if ($timedebug)
                        $stime = mtime();
                    // init MIME message
                    $mail = new html_mime_mail();
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message0] $time sec<br>\n";
                    // html message
                    if ($timedebug)
                        $stime = mtime();
                    if ($htmsg && $htmail == '1' && !$textonly)
                        $mail->add_html($xhtmsg, $xmsg, '');
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message1] $time sec<br>\n";

                    // text message
                    if ($timedebug)
                        $stime = mtime();
                    if (($msg && !$htmsg) || ($msg && $htmsg && ($htmail == '0' || $textonly)))
                        $mail->add_text($xmsg);
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message2] $time sec<br>\n";

                    // add file attachments
                    if ($fatt) {
                        $xfatt = explode(',', $fatt);
                        while (list($key2, $filen) = each($xfatt)) {
                            setfiletype($filen); // set atype and etype
                            global $atype;
                            global $etype;
                            if (!$atype)
                                $atype = 'application/binary';

                            // check file exists before adding
                            $result = file_exists("./attach/" . $filen);

                            if ($result == true) {
                                $attachment = $mail->get_file("./attach/$filen");
                                $mail->add_attachment($attachment, $filen, $atype, $etype);
                            }
                        }
                    }

                    // build final message
                    // default charset for MIME message, improve this.
                    //$dcharset = "iso-8859-1";
                    $dcharset = "UTF-8";
                    if ($charset)
                        $bchar = $charset; else
                        $bchar = $dcharset;

                    $build_params = array();
                    $build_params['html_charset'] = $bchar;
                    $build_params['text_charset'] = $bchar;

                    if ($timedebug)
                        $stime = mtime();
                    // $build_params['text_wrap'] = '30';
                    $mail->build_message($build_params);
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message3] $time sec<br>\n";

                    // testing!!
                    /* $body = $mail->output;
                      echo "<br><Br>".nl2br($body); */

                    // build full name for to: field
                    if ($mtype == '5')
                        $fulln = 'ListMail Admin';
                    else {
                        $fulln = $fname;
                        if ($lname && $lname <> '0')
                            $fulln .= ' ' . $lname;
                    }

                    // final message

                    $index = 0;
                    // $bad_rcpt = array();
                    // $smtp_from = $errmail;
                    // $smtp->Sender = $errmail;
                    // calculate date/time
                    $date = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);


                    if ($timedebug)
                        $stime = mtime();
                    $body = $mail->output;
                    $headers = $mail->headers;
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message-body+header] $time sec<br>\n";

                    if ($timedebug)
                        $stime = mtime();
                    $header = '';

                    $crlf = "\n";
                    if ($smtpsend)
                        $header .="Date: $date$crlf";
                    $header .="From: \"$sendn\" <$sende>$crlf";
                    if ($smtpsend) {
                        $header .="Message-Id: <" . date("YmdHisT") . ".$uid@$lmpdomain>$crlf";
                        if ($fulln)
                            $header .= "To: \"$fulln\" <$email>$crlf";
                        else
                            $header .= "To: $email$crlf";
                    }
                    if ($smtpsend)
                        $header .="Subject: $xsubj$crlf";
                    if (!$smtpsend)
                        $header .= "Return-Path: $errmail$crlf";
                    $header .= "MIME-Version: 1.0$crlf";
                    while (list($key2, $data) = each($headers)) {
                        // $data1 = str_replace("\r","\\r",str_replace("\n","\\n\n",$data));
                        // echo "data1=<b>$data1</b><br>";
                        $header .= $data . $crlf;
                    }
                    /* 	$header .="List-Unsubscribe: ".$lmp."rem.php?u=$unid$crlf";
                      $header .="Importance: Low$crlf"; */
//	$header .="X-Priority: High$crlf";
                    $header .="X-Mailer: ListMail v$ver$crlf";
                    $header .="X-LMP-Recipient: $email";

                    if ($smtpsend)
                        $header .= "$crlf";

                    /*
                    if (($htmsg && $htmail && !$textonly) || $fatt) {
                        $header .= $crlf . "This is a multipart message in MIME format.";
                        if ($smtpsend)
                            $header .= $crlf . $crlf;
                    } else*/
                    if ($smtpsend)
                        $header .= "$crlf";
                    if ($timedebug)
                        $time = mtime() - $stime;
                    if ($timedebug)
                        echo "[build message-extra headers] $time sec<br>\n";

                    if ($timedebug)
                        $ttime = mtime() - $sttime;
                    if ($timedebug)
                        echo "[Total build] $time sec<br><br>\n";


                    // final send
//    echo "going to final send<br>"; flush();
                    if ($smtpsend) {

//     echo "smtpsend..<br>"; flush();

                        if ($demo <> 'yes') {

                            // need to POP check?
                            if ($mtpchk) {
                                $now = date("YmdHis");
                                $diff = $now - $starttime;
                                if ($diff > $mtpdelay) {
                                    $asock = fsockopen($mtphost, $mtpport);
                                    if (!$asock)
                                        $error = 1; else
                                        $error = '';
                                    if (!$error) {
                                        // connected!
                                        $srvmsg = fgets($asock, 1024);
                                        $lastmsg = substr($srvmsg, 0, 1);
                                        if ($lastmsg <> "+")
                                            $error = 1; else
                                            $error = '';
                                        if (!$error) {
                                            // send user
                                            fputs($asock, "USER $mtpuser\r\n");
                                            $srvmsg = fgets($asock, 1024);
                                            $lastmsg = substr($srvmsg, 0, 1);
                                            if ($lastmsg <> "+")
                                                $error = 1; else
                                                $error = '';

                                            // send pass
                                            fputs($asock, "PASS $mtppass\r\n");
                                            $srvmsg = fgets($asock, 1024);
                                            $lastmsg = substr($srvmsg, 0, 1);
                                            if ($lastmsg <> "+")
                                                $error = 1; else
                                                $error = '';
                                            // if($error) echo " Failed!<br>"; else echo " Verified!<br>";

                                            if (!$error) {
                                                // check # of messages
                                                fputs($asock, "STAT\r\n");
                                                $srvmsg = fgets($asock, 1024);
                                                $lastmsg = substr($srvmsg, 0, 1);
                                                if ($lastmsg <> "+")
                                                    $error = 1;
                                            }
                                            // QUIT
                                            fputs($asock, "QUIT\r\n");
                                            $srvmsg = fgets($asock, 1024);
                                            $lastmsg = substr($srvmsg, 0, 1);
                                            if ($lastmsg <> "+")
                                                $error = 1;
                                            sleep(4);
                                        }
                                    } // else failed connect
                                    $starttime = $now;
                                }
                            }

//      echo "check smtp delay..<br>"; flush();
                            // check smtp delay
                            $mtimer++;
                            if ($mtimer > $mtdelay) {
                                $smtpcmd = "QUIT\r\n";
                                if ($smtp_debug)
                                    logwrite($bugf, '> ' . $smtpcmd);
                                fputs($ssock, $smtpcmd);
                                $srvmsg = @fgets($ssock, 1024);
                                if ($smtp_debug)
                                    logwrite($bugf, $srvmsg);
                                $reconnect = 1;
                                $skiptonext = '';
                                $mtimer = 1;
                            }
                            if (!$ssock)
                                $reconnect = 1;

                            // echo "check reconnect.. value=$reconnect<br>"; flush();

                            if ($reconnect || !is_resource($ssock)) {
                                if ($smtp_debug)
                                    logwrite($bugf, 'LM: Reconnecting... ');
                                // (re)connect to smtp
                                if ($smtp_debug)
                                    logwrite($bugf, $srvmsg);
                                $ssock = fsockopen($mthost, $mtport);
                                if (!$ssock)
                                    $error = 1; else
                                    $error = '';
                                if (!$error) {
                                    // connected!
                                    $srvmsg = '';

                                    $srvmsg = getsmtpmsg($ssock);
                                    if ($smtp_debug)
                                        logwrite($bugf, "Connected!\r\n");
                                    if (!$error) {

                                        if (!$error) {
                                            $hhost = @getenv('HTTP_HOST');
                                            if (!$hhost)
                                                $hhost = 'listmail';
                                            else {
                                                // echo substr($hhost,0,4);
                                                if (substr($hhost, 0, 4) == 'www.')
                                                    $hhost = substr($hhost, 4, strlen($hhost) - 1);
                                            }

                                            // send ehlo
                                            $smtpcmd = "EHLO localhost\r\n";
                                            if ($smtp_debug)
                                                logwrite($bugf, '> ' . $smtpcmd);
                                            fputs($ssock, $smtpcmd);
                                            $srvmsg = getsmtpmsg($ssock);
                                            if ($smtp_debug)
                                                logwrite($bugf, $srvmsg);
                                            $lastmsg = substr($srvmsg, 0, 3);
                                            if ($lastmsg <> "250") {
                                                if ($lastmsg == "500") {
                                                    if (!$helohost)
                                                        $helohost = 'localhost';
                                                    $smtpcmd = "HELO $helohost\r\n";
                                                    if ($smtp_debug)
                                                        logwrite($bugf, '> ' . $smtpcmd);
                                                    fputs($ssock, $smtpcmd);
                                                    $srvmsg = getsmtpmsg($ssock);
                                                    if ($smtp_debug)
                                                        logwrite($bugf, $srvmsg);
                                                    $lastmsg = substr($srvmsg, 0, 3);
                                                    if ($lastmsg <> "250")
                                                        $error = 1; else
                                                        $error = '';
                                                } else
                                                    $error = 1;
                                            } else
                                                $error = '';
                                        }

                                        // check pipelining, need to finish this
                                        if (strpos($srvmsg, 'PIPELINING') > 0)
                                            $pipeline = 1; else
                                            $pipeline = '';
//         $pipeline = ''; echo "PIPELINING OFF!<br>"; flush();
                                        // auth
                                        if ($mtauth && strpos($srvmsg, 'AUTH LOGIN') > 0) {
                                            // EHLO says auth is good
                                            $smtpcmd = "AUTH LOGIN\r\n";
                                            if ($smtp_debug)
                                                logwrite($bugf, '> ' . $smtpcmd);
                                            fputs($ssock, $smtpcmd);

                                            $srvmsg = getsmtpmsg($ssock);
                                            if ($smtp_debug)
                                                logwrite($bugf, $srvmsg);
                                            $lastmsg = substr($srvmsg, 0, 3);
                                            if ($lastmsg <> "334")
                                                $error = 1; else
                                                $error = '';

                                            if (!$error) {
                                                // send username
                                                $smtpcmd = base64_encode($mtuser) . "\r\n";
                                                if ($smtp_debug)
                                                    logwrite($bugf, '> ' . $smtpcmd);
                                                fputs($ssock, $smtpcmd);
                                                $srvmsg = getsmtpmsg($ssock);
                                                if ($smtp_debug)
                                                    logwrite($bugf, $srvmsg);
                                                $lastmsg = substr($srvmsg, 0, 3);
                                                if ($lastmsg <> "334")
                                                    $error = 1; else
                                                    $error = '';

                                                if (!$error) {
                                                    // send password
                                                    $smtpcmd = base64_encode($mtpass) . "\r\n";
                                                    if ($smtp_debug)
                                                        logwrite($bugf, '> ' . $smtpcmd);
                                                    fputs($ssock, $smtpcmd);
                                                    $srvmsg = getsmtpmsg($ssock);
                                                    if ($smtp_debug)
                                                        logwrite($bugf, $srvmsg);
                                                    $lastmsg = substr($srvmsg, 0, 3);
                                                    if ($lastmsg <> "235")
                                                        $error = 1; else
                                                        $error = '';
                                                }
                                                if ($error)
                                                    echo "Bad password<br>";
                                            } else {
                                                echo "Username not accepted.<br>";
                                            }
                                            $noplain = 1;
                                        } else {
                                            // echo "AUTH command not accepted.<br>";
                                        }
                                        // auth plain
                                        if ($mtauth && strpos($srvmsg, 'AUTH PLAIN') > 0 && !$noplain) {
                                            // EHLO says auth is good
                                            $smtpcmd = "AUTH PLAIN " . base64_encode("\0" . $mtuser . "\0" . $mtpass) . "\r\n";
                                            if ($smtp_debug)
                                                logwrite($bugf, '> ' . $smtpcmd);
                                            fputs($ssock, $smtpcmd);
                                            $srvmsg = getsmtpmsg($ssock);
                                            if ($smtp_debug)
                                                logwrite($bugf, $srvmsg);
                                            $lastmsg = substr($srvmsg, 0, 3);
                                            if ($lastmsg <> "235")
                                                $error = 1; else
                                                $error = '';
                                            if ($error) {
                                                if ($solo || $dayo || $welcnf)
                                                    echo "User/Pass not accepted.<br>";
                                            }
                                        }
                                    } else {
                                        echo "EHLO failed! ($srvmsg)<br>";
                                    }
                                } else {
                                    echo "Connect to server <b>$mthost</b>:<b>$mtport</b> failed.<br>";
                                }
                                $reconnect = 0;
                            } // end !connected
                            // send the mail     !
//      echo "send the mail!<br>"; flush();

                            $errmsg = '';

                            // from
                            if (!$pipeline) {
                                $smtpcmd = "MAIL FROM: <$errmail>\r\n";
                                if ($smtp_debug)
                                    logwrite($bugf, '> ' . $smtpcmd);
                                fputs($ssock, $smtpcmd);
                                $srvmsg = fgets($ssock, 1024);
                                if ($smtp_debug)
                                    logwrite($bugf, $srvmsg);
                                $lastmsg = substr($srvmsg, 0, 3);
                                if ($lastmsg <> "250")
                                    $error = 1; else
                                    $error = '';

                                if (!$error) {
                                    // check recipient
                                    // echo "EMAIL=$email..<br>";
                                    $smtpcmd = "RCPT TO: <$email>\r\n";
                                    if ($smtp_debug)
                                        logwrite($bugf, '> ' . $smtpcmd);
                                    fputs($ssock, $smtpcmd);

                                    $srvmsg = getsmtpmsg($ssock, 1024);
                                    $errmsg .= 'RCPT TO: &lt;' . $email . '&gt;' . "\n" . $srvmsg;
                                    if ($smtp_debug)
                                        logwrite($bugf, $srvmsg);

//        if($email == 'badtest@email.com'){ echo " WAS: $srvmsg<br>"; $srvmsg = '550 BADTEST'; }

                                    $lastmsg = substr($srvmsg, 0, 3);
                                    if ($lastmsg <> '250')
                                        $error = 1; else
                                        $error = '';
                                }

                                if ($error) {
                                    if ($srvmsg)
                                        $xsrvmsg = ": $srvmsg"; else
                                        $xsrvmsg = ' was BLANK, possible timeout';
                                    if ($smtp_debug)
                                        logwrite($bugf, "LM: Undeliverable. RCPT response$xsrvmsg. Skipping.\r\n");
                                    $skipped++;
                                    $smtpcmd = "RSET\r\n";
                                    if ($smtp_debug)
                                        logwrite($bugf, '> ' . $smtpcmd);
                                    fputs($ssock, $smtpcmd);
                                    $rsrvmsg = getsmtpmsg($ssock, 1024);
                                    $errmsg .= $srvmsg;
                                    if ($smtp_debug)
                                        logwrite($bugf, $rsrvmsg);
                                    if ($rsrvmsg <> '')
                                        $skiptonext = 1; else {
                                        if ($smtp_debug)
                                            logwrite($bugf, "LM: Server did not respond to RSET.\r\n"); $reconnect = 1;
                                    }
                                } else
                                    $error = '';

                                if (!$error) {
                                    // start data
                                    $smtpcmd = "DATA\r\n";
                                    if ($smtp_debug)
                                        logwrite($bugf, '> ' . $smtpcmd);
                                    fputs($ssock, $smtpcmd);
                                    $srvmsg = fgets($ssock, 1024);
                                    $errmsg .= $srvmsg;
                                    if ($smtp_debug)
                                        logwrite($bugf, $srvmsg);
                                    $lastmsg = substr($srvmsg, 0, 3);
                                    if ($lastmsg <> "354")
                                        $error = 1; else
                                        $error = '';
                                }
                            } else {

                                // SMTP pipelining
                                $smtpcmd = "MAIL FROM: <$errmail>\r\n";
                                if ($smtp_debug)
                                    logwrite($bugf, '> ' . $smtpcmd);
                                fputs($ssock, $smtpcmd);
                                $smtpcmd = "RCPT TO: <$email>\r\n";
                                // echo "RCPT TO: $email<br>"; flush();
                                if ($smtp_debug)
                                    logwrite($bugf, '> ' . $smtpcmd);
                                fputs($ssock, $smtpcmd);

                                $srvmsg = fgets($ssock, 1024);
                                if ($smtp_debug)
                                    logwrite($bugf, $srvmsg);

                                $lastmsg = substr($srvmsg, 0, 3);
                                if ($lastmsg <> "250")
                                    $error = 1; else
                                    $error = '';

                                if (!$error) {
                                    $srvmsg = getsmtpmsg($ssock, 1024);
                                    // $errmsg .= 'RCPT TO: &lt;'.$email.'&gt;'."\n".$srvmsg;
                                    if ($smtp_debug)
                                        logwrite($bugf, $srvmsg);

                                    // if($email == 'badtest@email.com'){ echo " WAS: $srvmsg<br>"; $srvmsg = '550 BADTEST'; }

                                    $lastmsg = substr($srvmsg, 0, 3);

                                    if ($lastmsg <> '250')
                                        $error = 1;
                                    if ($error) {
                                        if ($srvmsg)
                                            $xsrvmsg = ": $srvmsg"; else
                                            $xsrvmsg = ' was BLANK, possible timeout';
                                        if ($smtp_debug)
                                            logwrite($bugf, "LM: Undeliverable. RCPT response$xsrvmsg. Skipping.\r\n");
                                        $skipped++;
                                        $smtpcmd = "RSET\r\n";
                                        if ($smtp_debug)
                                            logwrite($bugf, '> ' . $smtpcmd);
                                        fputs($ssock, $smtpcmd);
                                        $rsrvmsg = getsmtpmsg($ssock, 1024);
                                        $errmsg .= $srvmsg;
                                        if ($smtp_debug)
                                            logwrite($bugf, $rsrvmsg);
                                        if ($rsrvmsg <> '')
                                            $skiptonext = 1; else {
                                            if ($smtp_debug)
                                                logwrite($bugf, "LM: Server did not respond to RSET.\r\n"); $reconnect = 1;
                                        }
                                    } else
                                        $error = '';

                                    if (!$error) {
                                        $smtpcmd = "DATA\r\n";
                                        if ($smtp_debug)
                                            logwrite($bugf, '> ' . $smtpcmd);
                                        fputs($ssock, $smtpcmd);

                                        $srvmsg = fgets($ssock, 1024);
                                        $errmsg .= $srvmsg;
                                        if ($smtp_debug)
                                            logwrite($bugf, $srvmsg);
                                        $lastmsg = substr($srvmsg, 0, 3);
                                        if ($lastmsg <> "354")
                                            $error = 1; else
                                            $error = '';
                                    }
                                }
                            }

                            // data - both send methods
                            if (!$error) {
                                // start data
                                $themesg = $header . $body;
                                $themesg = str_replace("\r\n", "\n", $themesg);
                                $themesg = str_replace("\n", "\r\n", $themesg);
                                $themesg = str_replace("\r\n.", "\r\n..", $themesg);
                                $xmsg = str_replace('<', '&lt;', $themesg);
                                $xmsg = str_replace('>', '&gt;', $xmsg);
                                if ($smtp_debug){
                                    //Truncate the message so that the log file won't get too big
                                    $log_xmsg = substr($xmsg,0,2000);
                                    logwrite($bugf,$log_xmsg.'\r\n...<truncated at 2000 char>...\r\n');
                                }

                                if ($smtp_debug)
                                    logwrite($bugf, "> SENT DATA\r\n");
                                fputs($ssock, $themesg . "\r\n.\r\n");
                                $srvmsg = fgets($ssock, 1024);
                                $errmsg .= $srvmsg;
                                if ($smtp_debug)
                                    logwrite($bugf, $srvmsg);
                                $lastmsg = substr($srvmsg, 0, 3);
                                if ($lastmsg <> "250")
                                    $error = 1; else
                                    $error = '';
                            }
                        }
                    } else {
                        // use php mail()
                        $body = str_replace("\r\n", "\n", $body);
                        // echo htmlspecialchars("WINDOWS=$windows EMAIL=<b>$email</b> SUBJ=<b>$xsubj</b> BODY=<b>$body</b> HEADER=<b>$header</b><br>");
                        if ($windows)
                            $header .= "\nTo: \"$fulln\" <$email>"; else if ($fulln)
                            $email = "\"$fulln\" <$email>";
                        if (!$demo) {
                            if ($phpmailf)
                                mail($email, $xsubj, $body, $header, "-f$errmail");
                            else
                                mail($email, $xsubj, $body, $header);
                        }
                    }
                } else {
                    // !message
                    if (!$msg || !$subj)
                        echo " Message has no body / subject!<br>";
                    if ($nouser && $smtp_debug)
                        echo "User no longer exists in database.<br>";
                }
//   echo "next??!<br>";
                if ($error && !$skiptonext && !$reconnect) {
//    echo "sending error!! ";
                    echo "<span class=status_text style=\"color: red\"><br>Sending error.  Check your mail settings.<br><br></span><span class=status_text>Server said:<br> " . nl2br($errmsg) . "<br></span><input class=button type=button onclick=\"hopto('./editconf.php?list=$list')\" name=tc value=\"Go To Config\"><br>";
                    exit;
                }
            } // !nouser
            $first = '';
            $lastmid = $msgid;
            $lastmt = $mtype;
            $y++;
            // remove from q
            $cmd = "delete from $otable where id = '$oid'";
            mysql_query($cmd) or die('admin-26-' . mysql_error());

            // php mail delay
            if (!$smtpsend) {
                sleep(($del1) * (.001));
                if (($y % $del3) == 0 && $y <> sizeof($sendq) && $mtype <> '3') {
                    echo "<script language=javascript>document.s.delaymsg.style.display=\"\"</script>";
                    flush();
                    sleep(($del2) * 60);
                    echo "<script language=javascript>document.s.delaymsg.style.display=\"none\"</script>";
                }
            }

            // report in to qprot
            $xt = time();
            if ($xt - $qptime >= $qpint) {
                qcheckin($batch);
                $qptime = $xt;
            }

            // optimize table every 1000
            if ($y % 1000 == 0 || $y == $nmails)
                mysql_query("optimize table $otable") or die('admin-27-' . mysql_error());
            if ($y % 250 == 0 || $y == $nmails || $y == 50) {
                // per second timer test
                $this_date = explode(' ', date("Y m d H i s"));
                $elapsed = mktime($this_date[3], $this_date[4], $this_date[5], $this_date[1], $this_date[2], $this_date[0]) - mktime($start_date[3], $start_date[4], $start_date[5], $start_date[1], $start_date[2], $start_date[0]);
                if (!$elapsed || $elapsed == 0)
                    $elapsed = 1;
                $avg = round($y / $elapsed, 2);
                $hr = $avg * 3600;
                echo "<script language=javascript>document.s.speed.value='$avg'</script>\n";
                echo "<script language=javascript>document.s.speed2.value='$hr'</script>\n";
                /*    // check avg1
                  $remain = round(($nmails - $y) / $avg);
                  $timeleft = $remain;
                  echo "<script language=javascript>this.timeleft.value='$timeleft'</script>\n";
                  // $timeleft = "$h$m$s"; */
            }

            // increase display count
            if ($dayo || $solo || $welcnf) {
//    echo "checking display count .. y=$y .. mailcount=$mailcount<br>";
                //     echo "y=$y mailcount=$mailcount DIV=" . ($y % $mailcount)."<br>";
                if ($sqldebug)
                    echo "CMD=<b>$cmd</b><br>";
                if ($y % $mailcount == 0 || $y == $nmails)
                    echo "<script language=javascript>document.s.disp.value='$y'</script>\n";
//    echo "end if<br>";
            }
//   echo "no sending error!  end loop! next!<br>";
            flush();
        }
    } else {
        if ($dayo || $solo || $welcnf)
            echo "Called domail with no messages in queue, aborting.<br>";
    }

    if (!$error && $smtpsend) {
        // start data
        $smtpcmd = "QUIT\r\n";
        if ($smtp_debug)
            logwrite($bugf, '> ' . $smtpcmd);
        fputs($ssock, $smtpcmd);
        $srvmsg = fgets($ssock, 1024);
        if ($smtp_debug)
            logwrite($bugf, $srvmsg);
        $lastmsg = substr($srvmsg, 0, 3);
        if ($lastmsg <> "221")
            $error = 1; else
            $error = '';
    }
    if ($smtp_debug) {
        fclose($bugf);
        echo "<br>SMTP log written: <a href=$bugfn>$bugfn</a><br>";
    }
    if ($skipped > 0) {
        $skiperc = round($skipped / $nmails * 100, 1);
        if ($skiperc > 50)
            echo "<br><font color=red>Warning: $skiperc% of messages were skipped due to timeout, authentication or unknown error!<br></font>You may want to check your settings, enable the SMTP log and send some test messages to make sure they are delivered.<br>";
    }
    // if(is_resource($ssock)) echo "<b>(Q) server is connected</b><br>"; else echo "<b>(Q) server is not connected</b>";
    // if($smtpsend) mysql_query("optimize table $qtable");
    return true;
}

// end domail
// listmenu
function listmenu($list, $cpage, $xtra = '') {
    global $ltable, $link;
    global $confirmform;
    global $subform;
    global $suberrform;
    global $remconform;
    global $remsucform;
    global $remerrform;

    $loadfile = "editlists.php"; // default

    if ($cpage == 'listsup')
        $xtra = '&getcode=1'; else
        $xtra = '';
    if ($cpage == 'lists_w')
        $xtra = '&editwc=w';
    if ($cpage == 'lists_c')
        $xtra = '&editwc=c';
    if ($cpage == 'lists_r')
        $xtra = '&editwc=r';
    if ($cpage == 'list_cf')
        $xtra = '&efields=1';
    if ($cpage == 'confcode') {
        $xtra = '&codes=1';
        $loadfile = 'editconf.php';
    }
    if ($cpage == 'custom') {
        $loadfile = "custom.php";
        if ($confirmform)
            $xtra = '&confirmform=1';
        if ($subform)
            $xtra = '&subform=1';
        if ($suberrform)
            $xtra = '&suberrform=1';
        if ($remconform)
            $xtra = '&remconform=1';
        if ($remsucform)
            $xtra = '&remsucform=1';
        if ($remerrform)
            $xtra = '&remerrform=1';
    }
    if ($cpage == 'fups')
        $loadfile = 'editfups.php';
    if ($cpage == 'users')
        $loadfile = 'edituser.php';

    if ($cpage == 'useradd') {
        $loadfile = 'edituser.php';
        $xtra = '&addu=y';
    }
    if ($cpage == 'domail') {
        $loadfile = 'domail.php';
        $xtra = '&edit=1';
    }
    if ($cpage == 'logs') {
        $loadfile = 'viewlogs.php';
        $logpage = 1;
    }
    if ($cpage == 'listsup') {
        $loadfile = 'editlists.php';
        $xtra = '&getcode=yes';
    }
    if ($cpage == 'fields') {
        $loadfile = 'editlists.php';
        $xtra = '&efields=1';
    }
    if ($cpage == 'domail')
        $onchange = "if(form1.txtsubj.value=='' && form1.txtcont.value=='') { jumpto(this); } else { if(confirm('Warning. This will reset your message!\\nYou may need to CANCEL, save your message, change lists, and reload it.\\n\\nPress OK to discard unsaved changes.')) jumpto(this); else this.value = orig; }"; else
        $onchange = "jumpto(this);";

    if ($cpage == 'scheduler')
        $loadfile = 'scheduler.php';

    echo "   <!-- list menu -->
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
     <!-- top list nav.. -->
     <table width=120 border=0 cellspacing=0 cellpadding=0 class=outer_table>
      <tr>
       <td class=cur_list_bg><span class=table_head_text>Current List:</span><br></td>
      </tr>
      <tr>
       <td width=1>
     <select id=alnum class=input_dropdown style=\"width: 118px\" onchange=\"$onchange\">";


    if ($cpage == 'config' || $cpage == 'links' || $cpage == 'scheduler' || $cpage == 'schedadd' || $cpage == 'daymail' || $cpage == 'rules' || $cpage == 'domail' || $cpage == 'chtml' || $cpage == 'emsgs' || $cpage == 'selects') {
        echo "<option value=\"\">N/A</a>";
    } else {
        if ($cpage == 'users' || $cpage == 'domail' || $cpage == 'confcode' || $cpage == 'logs')
            echo "<option value=\"./" . $loadfile . "?list=all" . $xtra . "\">All (warning: does not show remote lists)";

        $cmd = "select listnum,title from $ltable where 1 order by listnum";
        $result = @mysql_query($cmd, $link) or die('admin-28-' . mysql_error());
        while (list($lnu2, $lti2) = @mysql_fetch_row($result)) {
            $lti2 = "$lnu2: $lti2";
            if ($lnu2 == $list && $list <> 'all')
                $l_x = " selected"; else
                $l_x = "";
            echo "<option value=\"./" . $loadfile . "?list=$lnu2" . $xtra . "\"" . $l_x . ">$lti2\n";
        }
    }

    echo "     </select>
           <script language=javascript> orig=alnum.value; </script>
        </td>
       </tr>
      </table>
     </td>
    </tr>
    <tr>
     <td height=9><img src=1.gif height=9></td>
    </tr>
   </table>
   <!-- end list menu -->
   <!-- left menu -->";

    if ($cpage <> 'import2') {
        echo "   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>\n";
        if ($cpage == 'config')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./editconf.php?list=$list');\" value=\"Configuration\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";
        if ($cpage == 'confcode')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./editconf.php?list=$list&codes=y');\" value=\"Message Codes\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";
        if ($cpage == 'links')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./link.php?list=$list');\" value=\"Link Tracking\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
     </td>
    </tr>
    <tr><td height=10><img src=1.gif height=10></td></tr>
    <tr>
     <td>\n";
        if ($cpage == 'listset' || $cpage == 'listsup' || $cpage == 'chtml' || $cpage == 'emsgs' || $cpage == 'lists_w' || $cpage == 'lists_c' || $cpage == 'list_cf')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./editlists.php?list=$list');\" value=\"List Settings\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";
        /*   if($cpage=='chtml') $menuc = 'button_on'; else $menuc = 'button';
          echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./editchtml.php?list=$list');\" value=\"Custom HTML\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
          </td>
          </tr>
          <tr><td height=3><img src=1.gif height=3></td></tr>
          <tr>
          <td>\n";
          if($cpage=='emsgs') $menuc = 'button_on'; else $menuc = 'button';
          echo "      <input style=\"width: 120px;\" class=$menuc type=button onclick=\"hopto('./editemsg.php?list=$list');\" value=\"Error Messages\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\">
          </td>
          </tr>

          <tr><td height=3><img src=1.gif height=3></td></tr>
          <tr>
          <td>\n";
         */
        if ($cpage == 'fups')
            $menuc = 'button_on'; else
            $menuc = 'button';

        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Followups\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./editfups.php?list=$list')\">
     </td>
    </tr>

    <tr><td height=10><img src=1.gif height=10></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'users' || $cpage == 'useradd' || $cpage == 'import' || $cpage == 'export')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"User Database\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./edituser.php?list=$list')\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'selects')
            $menuc = 'button_on'; else
            $menuc = 'button';

        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"User Selection\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./editsel.php?list=$list')\">
     </td>
    </tr>
    <tr><td height=10><img src=1.gif height=10></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'domail')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=esubmit value=\"Send an Email\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./domail.php?list=$list&edit=1')\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'scheduler' || $cpage == 'schedadd')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Scheduler\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./scheduler.php?list=$list')\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'sentmsgs')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=esubmit value=\"Sent Messages\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./sentmsgs.php?list=$list')\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";

        if ($cpage == 'daymail')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Dailymail\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./dailymail.php?list=$list&warn=yes')\">
     </td>
    </tr>
    <tr><td height=10><img src=1.gif height=10></td></tr>
    <tr>
     <td>\n";
        if ($cpage == 'help')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Online Help\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"popitup('" . helplink('help_faq') . "');\">
     </td>
    </tr>
    <tr><td height=3><img src=1.gif height=3></td></tr>
    <tr>
     <td>\n";
//   if($cpage=='help') $menuc = 'button_on'; else $menuc = 'button';
        $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Support Forum\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('http://forum.listmailpro.com')\">
     </td>
    </tr>
    <tr><td height=10><img src=1.gif height=10></td></tr>
    <tr>
     <td>\n";

        /* echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Tech Support\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('http://tech.listmailpro.com');\">
          </td>
          </tr>
          <tr><td height=10><img src=1.gif height=10></td></tr>
          <tr>
          <td>\n";
         */
        if ($cpage == 'logout')
            $menuc = 'button_on'; else
            $menuc = 'button';
        echo "      <input style=\"width: 120px;\" class=$menuc type=button name=gosub value=\"Logout\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='$menuc'\" onclick=\"hopto('./login.php?logout=1')\">
     </td>
    </tr>

   </table>\n";
    }
}

function pophead($title) {
    echo "
<html>
<head>
<title>Help: $title&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</title>
<style type=text/css rel=stylesheet> <!--
.button { font-family: helvetica; font-size: 8pt; border: 1px solid black; background: #eeeeee }

body {
} --> </style> </head>
<body bgcolor=#ffffff>\n";
}

function firstlist() {
    global $ltable;
    $lrow = mysql_query("select listnum from $ltable where 1 order by listnum limit 1") or die('admin-29-' . mysql_error());
    list($listn) = @mysql_fetch_row($lrow);
    return $listn;
}

function getsmtpmsg($xsock) {
    global $smtp_timeout;
    $data = '';
    socket_set_timeout($xsock, $smtp_timeout);
    while ($str = fgets($xsock, 1024)) {
        $data .= $str;
        if (substr($str, 3, 1) == " ")
            break;
    }
    return $data;
}

function getcfields($list, $xtra = '') {
    global $ltable, $link;
    if ($xtra == 'users')
        $terse = 1;
    $cmd = "select field1,field2,field3,field4,field5,field6,field7,field8,field9,field10 from $ltable where listnum = '$list' limit 1";
    $lrow = mysql_query($cmd, $link) or die('admin-30-' . mysql_error());
    list($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10) = @mysql_fetch_row($lrow);
    for ($i = 1; $i <= 10; $i++) {
        list(${"field" . $i}, $null, $null0) = explode('/*LMP*/', ${"field" . $i});
    }
    $fields = array('', $field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10);

    while (list($key, $val) = each($fields)) {
        if ($key <> 0) {
            if ($val) {
                if ($terse)
                    $fields[$key] = $val;
                else
                    $fields[$key] = "Custom Field $key (" . $val . ")";
            } else {
                if ($terse)
                    $fields[$key] = '';
                else
                    $fields[$key] = "Custom Field $key";
            }
        }
    }
    return $fields;
}

function dateparse($indate, $do) {
    if ($do == 'break') {
        list($y, $m, $d) = explode('-', $indate);
        return array($y, str_pad($m, 2, "0", STR_PAD_LEFT), str_pad($d, 2, "0", STR_PAD_LEFT));
    }
    if ($do == 'form') {
        list($y, $m, $d) = $indate;
        return $y . '-' . str_pad($m, 2, "0", STR_PAD_LEFT) . '-' . str_pad($d, 2, "0", STR_PAD_LEFT);
    }
}

if (!function_exists('valid_email')) {

    function valid_email($email) {
        // http://www.linuxjournal.com/article/9585
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex)
            $isValid = false;
        else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64)
                $isValid = false;
            else if ($domainLen < 1 || $domainLen > 255)
                $isValid = false;
            else if ($local[0] == '.' || $local[$localLen - 1] == '.')
                $isValid = false;
            else if (preg_match('/\\.\\./', $local))
                $isValid = false;
            else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
                $isValid = false;
            else if (preg_match('/\\.\\./', $domain))
                $isValid = false;
            else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local)))
                    $isValid = false;
            }
        }
        return $isValid;
    }

}

// retrieve email notifs settings

function getnotifs($list, $xtr = '') {
    global $ctable;
    global $ltable;
    global $link;
    if ($xtr == 'cfg') {
        $crow = mysql_query("select notifs from $ctable where 1", $link) or die('admin-31-' . mysql_error());
        list($notifs) = mysql_fetch_row($crow);
        $marr = explode(';', $notifs);
        return $marr;
    } else {
        $crow = mysql_query("select notifs,nmails from $ltable where listnum = '$list'", $link) or die('admin-32-' . mysql_error());
        list($notifs, $nmails) = mysql_fetch_row($crow);
        if ($xtr == 'mails') {
            $marr = explode(',', $nmails);
            if (!$marr[0])
                return false;
            else
                return $marr;
        } else {
            $narr = explode(';', $notifs);
            return $narr;
        }
    }
}

function queuenotifs($list, $xmtype, $xuid, $x = '', $xtr) {
    global $otable;

    $marr = getnotifs($list, 'mails');
    // echo "LIST=$list,XMTYPE=$xmtype,XUID=$xuid,X=$x,XTR=$xtr<br>";
    // echo "MARR=";
    // print_r($marr);
    // echo "<BR>";
    while (list($key, $val) = each($marr)) {
        // echo "KEY=$key,VAL=$val<br>";

        make_seed();
        $xid = calc32();
        $cmd = "insert into $otable(id,mtype,uid,mid,xtra) values('$xid','$xmtype','$xuid','$key','$xtr');";
        // echo "CMD=$cmd<br>";
        mysql_query($cmd, $link) or die('admin-33-' . mysql_error());
    }
}

// some stuff we dont really need anymore
// for cross-server compatibility
function myaddslashes($st) {
    if (get_magic_quotes_gpc() == 1) {
        return $st;
    } else {
        return addslashes($st);
    }
}

function optimizeall() {
    global $atables;
    global $link;
    reset($atables);
    $alltabs = '';
    while (list($key, $val) = each($atables)) {
        if ($key <> 0)
            $alltabs .= ", ";
        $alltabs .= $val;
    }
    mysql_query("optimize table $alltabs", $link) or die('admin-34-' . mysql_error());
}

function calc32($size = 32) {
    $unique_str = md5(microtime());
    return substr($unique_str, 0, $size);
}

function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function make_batid() {
    // global $randmaxx;
    global $ptable, $link;
    while (!$uniq) {
        $uniq_str = unique_id(6);
        $ucmd = "select batid from $ptable where batid = '$uniq_str' limit 1";
        $urow = mysql_query($ucmd, $link) or die('admin-35-' . mysql_error());
        if (@mysql_num_rows($urow) == 0)
            $uniq = 1;
    }
    make_seed();
    return $uniq_str;
}

function csv_parse($data, $separator = ",") {
    $quote = '"';
    $values = array();
    $toggle = 0;
    $len = strlen($data);
    $count = 1;
    for ($i = 0; $i < $len; $i++) {
        $tmp = substr($data, $i, 1);
        if (strcmp($tmp, $quote) == 0) {
            $toggle = $toggle ^ 1;
        }
        $value = $value . $tmp;
        if (strcmp($tmp, $separator) == 0) {
            if (!$toggle) {
                # End of word
                $value = ereg_replace(",$", "", $value);
                $value = ereg_replace("^\"", "", $value);
                $value = ereg_replace("\"$", "", $value);
                $value = ereg_replace("\"+", "\"", $value);
                $num_of_elems = count($values);
                $values[$num_of_elems] = $value;
                $value = "";
            }
        }
    }
    $value = ereg_replace("^\"", "", $value);
    $value = ereg_replace("\"$", "", $value);
    $value = ereg_replace("\"+", "\"", $value);
    $num_of_elems = count($values);
    $values[$num_of_elems] = $value;
    return ($values);
}

// end csv_parse()

function sendnotif($notif, $userid, $cgi = '', $inmsg = '') {
    // admin notifs for subscribers & removals
    global $utable, $ltable, $ctable, $link, $phpmailf;

    if ($userid == 'admin')
        $admin = 1; else
        $admin = '';

    if (!$admin) {
        $urow = mysql_query("select uid,list,email,fname,lname,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,ipaddr,refurl from $utable where id = '$userid'", $link) or die('admin-36-' . mysql_error());
        list($uid, $ulist, $uemail, $ufname, $ulname, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $ipaddr, $refurl) = mysql_fetch_row($urow);
        $iphost = @gethostbyaddr($ipaddr);
        if ($iphost)
            $ipaddr = $ipaddr . " ($iphost)";

        $lrow = mysql_query("select title,sendn,sende,notifs,nmails from $ltable where listnum = '$ulist'", $link) or die('admin-37-' . mysql_error());
        list($ltitle, $sendn, $sende, $notifs, $nmails) = mysql_fetch_row($lrow);
        $fields = getcfields($ulist, 'users');
    }

    $crow = mysql_query("select admmail,errchk,erraddr,ver from $ctable where 1", $link) or die('admin-38-' . mysql_error());
    list($admmail, $errchk, $errmail, $ver) = mysql_fetch_row($crow);
    $xmails = explode(';', $admmail);
    $admmail = $xmails[0];

    if ($errchk <> '1' && $errchk <> '2')
        $errmail = $admmail;

    $marr = explode(',', $nmails);

    if ($admin) {
        $marr[0] = $admmail;
        $sende = $errmail;
    }

    if ($marr[0]) {
        $sendn = "ListMail Notification";
        while (list($key, $em) = each($marr)) {
            $aemail = $em;
            switch ($notif) {
                case 'subscriber':
                    $subj = "List $ulist: New Signup! - $uemail";
                    $msg = "A new user has signed up to your list: $ltitle\n\nTheir information is as follows:\n\n";
                    break;

                case 'manual_add':
                    $subj = "List $ulist: New Signup! - $uemail";
                    $msg = "You manually added a user to your list: $ltitle\n\nTheir information is as follows:\n\n";
                    break;

                case 'remove':
                    $subj = "List $ulist: Unsubscriber - $uemail";
                    $msg = "A user has removed themselves from your list: $ltitle\n\nTheir information was as follows:\n\n";
                    break;

                case 'rem_bounce':
                    $subj = "List $ulist: Bouncing User Removed - $uemail";
                    $msg = "A user has been removed from your list: $ltitle\n\nTheir information was as follows:\n\n";
                    break;

                case 'test_bounce':
                    $subj = "ListMail bounce.cgi Test: Success!";
                    $msg = "The test message was successfully received and processed by ListMail's bounce.cgi.\n\nThis most likely means that the script is configured correctly.\n";
                    break;
            }
            if (!$admin) {
                $msg .= "Email: $uemail\nFirst Name: $ufname\nLast Name: $ulname\n\n";
                if (!$cgi) {
                    for ($i = 1; $i <= 10; $i++) {
                        if ($fields[$i])
                            $msg .= $fields[$i] . ": " . ${"user" . $i} . "\n";
                        if ($i == 10)
                            $msg .= "\n";
                    }
                }
                $msg .= "IP Address: $ipaddr\nRef. URL: $refurl\n\n";
                $msg .= "Unique ID: $uid\n\n";
            }

            if ($notif == 'rem_bounce')
                $msg .= "---------- The bouncing message follows ----------\n\n$inmsg";

            $crlf = "\n";
            $header = "From: \"$sendn\" <$sende>$crlf";
            $header .= "Return-Path: $errmail$crlf";
            $header .= "MIME-Version: 1.0$crlf";
            $header .="X-Mailer: ListMail v$ver";
            $msg = str_replace("\r\n", "\n", $msg);
            if ($phpmailf)
                mail($aemail, $subj, $msg, $header, "-f$sende");
            else
                mail($aemail, $subj, $msg, $header);
        }
    }
}

function senddate() {
    // calculate date/time
    $tz = date("Z");
    $tzs = ($tz < 0) ? "-" : "+";
    $tz = abs($tz);
    $tz = ($tz / 3600) * 100 + ($tz % 3600) / 60;
    return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);
}

//At this point, the user has already been inserted
// $list is required because there are remote lists which requires connection to other database
function sendwelcome($userid,$list) {
    global $ctable;
    global $ltable;
    global $utable;
    global $link;
    global $phpmailf;
    global $charset;
    global $html_to_aol;
    global $windows;
    global $mp;
    global $otable;
    require_once($mp . '/mimeclass.php');
    
    // get list settings
    $lcmd = "select title,sendn,sende,welcact,cnfact,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list' limit 0,1";
    $lresult = mysql_query($lcmd, $link) or die('admin-41-' . mysql_error());
    list($title, $sendn, $sende, $welcact, $cnfact, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row($lresult);

    $date = senddate();

    // retreiving user info
    //$cmd = "select uid,fname,lname,cnf,list,email,htmail,user2 from $utable where id = '$userid'";
    $cmd = "select id,uid,list,fname,lname,email,user1,user2,user3,user4,user5,user6,user7,user8,user9,user10,cseq,cdel,cnf,dateadd,ipaddr,refurl,htmail,bounces from $utable where id = '$userid'";
    if($remote){
        try {
            $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
            $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
        } catch (PDOException $e) {
            die('admin-39-' . $e->getMessage());
        }
        $dbh_query = $dbh->query($cmd);
        //list($unid, $fname, $lname, $cnf, $list, $email, $htmail, $user2) = $dbh_query->fetch(); //assuming there will only be 1 unique userid
        list($unid,$uid,$list,$fname,$lname,$email,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$cseq,$cdel,$cnf,$dateadd,$ipaddr,$refurl,$htmail,$bounces)= $dbh_query->fetch();
       if($remote) $dbh = null; //close the connection
    }else{
        $urow = mysql_query($cmd, $link) or die('admin-39-' . mysql_error());
        list($unid,$uid,$list,$fname,$lname,$email,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$cseq,$cdel,$cnf,$dateadd,$ipaddr,$refurl,$htmail,$bounces) = mysql_fetch_row($urow);
    }
    

    // build full name
    $fulln = $fname;
    if ($lname)
        $fulln .= " " . $lname;

    // get return-path from config
    $crow = mysql_query("select admmail,errchk,erraddr,ver,listmailpath,mthost,mtport,mtauth,mtuser,mtpass,mtdelay,mtchk,mtphost,mtpport,mtpuser,mtppass,mtpdelay,mtpchk,textonly from $ctable where 1", $link) or die('admin-40-' . mysql_error());
    list($admmail,$errchk,$erraddr,$ver,$listmailpath,$mthost,$mtport,$mtauth,$mtuser,$mtpass,$mtdelay,$mtchk,$mtphost,$mtpport,$mtpuser,$mtppass,$mtpdelay,$mtpchk,$textonly) = mysql_fetch_row($crow);
    $xmails = explode(';', $admmail);
    $admmail = $xmails[0];
    $textonly = explode(';', $textonly);
    if (in_array(strtolower(substr($email, strpos($email, '@') + 1)), $textonly))
        $textonly = 1; else
        $textonly = '';
    if ($errchk <> '1' && $errchk <> '2')
        $errmail = $admmail;
    
    //Sending by SMTP
    $smtpoptions = explode(';', $mtchk);
    $smtpsend = $smtpoptions[0];
    $smtplog = $smtpoptions[1];

    // set up vars
    if ($cnf == '1') {
        $f1 = 'welsubj';
        $f2 = 'welcom';
        $f3 = 'welcht';
        $f4 = 'welcf';
    }
    if ($cnf == '0') {
        $f1 = 'cnfsubj';
        $f2 = 'cnfmesg';
        $f3 = 'cnfht';
        $f4 = 'cnff';
    }

    // get message
    $cmd = "select $f1,$f2,$f3,$f4 from $ltable where listnum = '$list'";
    $msgrow = @mysql_query($cmd, $link) or die('admin-42-' . mysql_error());
    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow); //This is the welcome message for this list

    // set up confirm message
    if ($cnf == '0') {
        // confirm
        $cmd = "update $utable set cnf = '0' where id = '$id'";
        if($remote){
            try {
                $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                $dbh->exec($cmd);
            } catch (PDOException $e) {
                die('admin-43-' . $e->getMessage());
            }
            $dbh = null; //close the connection
        }else{
            @mysql_query($cmd, $link) or die('admin-43-' . mysql_error());
        }
        /*
          $cstr = $lmpath . "confirm.php?u=" . $unid;
          $ctest = strpos($msg,"!confirm");
          if($ctest === false) {
          $msg .= "\r\n\r\n" . $cstr;
          } else {
          $msg = eregi_replace("!confirm",$cstr,$msg);
          }
          if($htmsg){
          $cstr = "<a href=\"" . $lmpath . "confirm.php?u=" . $unid . "\">Click Here</a>";
          $htest = strpos($htmsg,"!confirm");
          if($htest === false) {
          $htmsg .= "<br><br>" . $cstr;
          } else {
          $htmsg = eregi_replace("!confirm",$cstr,$htmsg);
          }
          } */
        $typ = '4';
    }

    // set up welcome message
    if ($cnf == '1') {
        $typ = '3';
        $logtxt .= " Welcome message sent!";
        $xtxt .= "<br>Welcome message sent.";
    }
    //list($xsubj, $xmsg, $xhtmsg) = processmsg($userid, $subj, $msg, $htmsg, '0', $typ, $list);
    list($xsubj, $xmsg, $xhtmsg) = processmsg2($userid, $uid,$list, $fname, $lname, $email,$refurl,$ipaddr,$user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $subj, $msg, $htmsg, '0', $typ, $list);
    //from above code list($unid,$uid,$list,$fname,$lname,$email,$user1,$user2,$user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,$cseq,$cdel,$cnf,$dateadd,$ipaddr,$refurl,$htmail,$bounces)= $dbh_query->fetch();$subj, $msg = '', $htmsg = '', $mhtml = '0', $mtyp = '', $mid = '', $r = ''
    //in processmsg2 list($id, $usid, $lnum, $fname, $lname, $email, $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10, $dadd, $uip, $refu) = mysql_fetch_row($urow);
    // $xsubj = processmsg($userid,$subj,'0',$typ,$list);
    // $xmsg = processmsg($userid,$msg,'0',$typ,$list);

    /* if($aol){
      //  echo "making aol clickable...<br>";
      $xmsg = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\">$1</a>",$xmsg);
      $xmsg = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\">$1</a>",$xmsg);
      } */
    // echo "<br>finalmsg=$xmsg<br><br>";
    // $xhtmsg = processmsg($userid,$htmsg,'1',$typ,$list);

    $mail = new html_mime_mail();

    // html message
    if ($htmsg && $htmail == '1' && !$textonly)
        $mail->add_html($xhtmsg, $xmsg, '');

    // text message only
    if (($msg && !$htmsg) || ($msg && $htmsg && ($htmail == '0' || $textonly)))
        $mail->add_text($xmsg);

    // add file attachments
    if ($fatt) {
        $xfatt = explode(',', $fatt);
        while (list($key2, $filen) = each($xfatt)) {
            setfiletype($filen);
            global $atype;
            global $etype;
            if (!$atype)
                $atype = 'application/binary';
            // check file exists before adding
            $result = file_exists("./attach/" . $filen);
            if ($result == true) {
                $attachment = $mail->get_file("./attach/$filen");
                $mail->add_attachment($attachment, $filen, $atype, $etype);
            }
        }
    }

    // build final message
    // default charset for MIME message, improve this.
    //$dcharset = "iso-8859-1";
    $dcharset = "UTF-8";
    if ($charset)
        $bchar = $charset; else
        $bchar = $dcharset;

    $build_params = array();
    $build_params['html_charset'] = $bchar;
    $build_params['text_charset'] = $bchar;
    // $build_params['text_wrap'] = '30';
    $mail->build_message($build_params);

    // create final message
    $body = $mail->output;
    $headers = $mail->headers;
    $header = '';
    $crlf = "\n";
    if($smtpsend) $header .="Date: $date$crlf";
    $header .="From: \"$sendn\" <$sende>$crlf";
    if($smtpsend){
      $header .="Message-Id: <".date("YmdHisT").".$uid@$lmpdomain>$crlf";
      if($fulln) $header .= "To: \"$fulln\" <$email>$crlf";
       else $header .= "To: $email$crlf";
     }
    if($smtpsend) $header .="Subject: $xsubj$crlf";
    if(!$smtpsend) $header .= "Return-Path: $errmail$crlf";
    $header .= "MIME-Version: 1.0$crlf";
    while (list($key2, $data) = each($headers)) {
        $header .= $data . $crlf;
    }
    $header .="X-Mailer: ListMail v$ver$crlf";
    $header .="X-LMP-Recipient: $email";
    if($smtpsend) $header .= "$crlf$crlf";
    

    if (($htmsg && $htmail && !$textonly) || $fatt) {
        //$header .= $crlf . "This is a multipart message in MIME format.";
    }

    // send email
    $body = str_replace("\r\n", "\n", $body);

    // echo "HEADER=>>".addslashes($header)."<<<br>";
    // echo "BODY=>>".addslashes($body)."<<<br>";

    if ($windows)
        $header .= "\nTo: \"$fulln\" <$email>"; else if ($fulln)
        $email = "\"$fulln\" <$email>";
    // echo htmlspecialchars("WINDOWS=$windows EMAIL=<b>$email</b> SUBJ=<b>$xsubj</b> BODY=<b>$body</b> HEADER=<b>$header</b><br>");
    // echo $header;
    //if($phpmailf) mail($email,$xsubj,$body,$header,"-f$errmail"); else mail($email,$xsubj,$body,$header);
    if ($phpmailf){
        mail($email, $xsubj, $body, $header, "-f$errmail");
        exit; //If send by mail(), end here
    }
    
    //if SMTP is disabled, send with php mail() and exit
    if(!$smtpsend){
        mail($email,$xsubj,$body,$header);
        exit;
    }
    // carry on with SMTP prodecure
    // Note: Ignore $mtpchk - SMTP authorized by email check, which I don't know what the hell is it
    
    // turn into "ms"
    $mtpdelay = $mtpdelay * 100;
    if ($errchk == '0')
        $errmail = $admmail;
    if ($errchk == '2' || $errchk == '1')
        $errmail = $erraddr;
    if ($smtplog == '1')
        $smtp_debug = 1;
    
    //If SMTP logging is turned on, open connection to log file
    if ($smtp_debug) {
        $bugfn = './attach/LM_SMTP_' . date("Y-m-d_h-i-s") . '.txt';
        if (!$bugf = @fopen($bugfn, 'w')) {
            echo '<font color=red>Warning</font>: Cannot write SMTP debug file (./attach/LM_SMTP_' . date("Y-m-d-h-i-s") . '.txt)<br>Is your ./attach folder CHMOD 777?  SMTP log disabled.<br><br>';
            flush();
            $smtp_debug = '';
        } else {
            function logwrite($bf, $in) {
                global $smtp_debug;
                if (fwrite($bf, $in) === false) {
                    echo "Error writing to SMTP debug file! (Out of disk space?)<br>";
                    $smtp_debug = '';
                }
            }
        }
    }
    
    //Step 1. Connect to SMTP
    //Start SMTP connection procedure
    $ssock = fsockopen($mthost, $mtport);
    //Error with connection
    if (!$ssock){
        if ($smtp_debug) logwrite($bugf, "Server not found!\r\n");
        exit;
    }
    $srvmsg = getsmtpmsg($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
    if ($smtp_debug) logwrite($bugf, "Connected to host $mthost:$mtport!\r\n"); //share the good news first
    
    //Step 2. authenticate user
    if($mtauth){
        //Say hello first
        $smtpcmd = "EHLO localhost\r\n";
        fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, ">$smtpcmd");
        $srvmsg = getsmtpmsg($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
        if(strpos($srvmsg, 'LOGIN') <= 0){
            if ($smtp_debug) logwrite($bugf, "Login not available...\r\n");
            if ($smtp_debug) logwrite($bugf,$srvmsg);
            return;
        }
        if ($smtp_debug) logwrite($bugf, "AUTH LOGIN detected...\r\n");
        //Then ask for AUTH LOGIN
        if ($smtp_debug) logwrite($bugf, "Start authentication now...\r\n");
        $smtpcmd = "AUTH LOGIN\r\n";
        fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, ">".$smtpcmd);
        $srvmsg = getsmtpmsg($ssock);
        $lastmsg = substr($srvmsg, 0, 3);
        if ($lastmsg <> '334'){ //Server returns anything OTHER THAN 'challenge' message
            if ($smtp_debug) logwrite($bugf, "Server error: $lastmsg...\r\n");
            exit;
        }
        
        // Username
        $smtpcmd = base64_encode($mtuser)."\r\n";
        fputs($ssock, $smtpcmd);
        $srvmsg = fgets($ssock);
        $lastmsg = substr($srvmsg, 0, 3); if ($smtp_debug) logwrite($bugf, '$srvmsg');
        if($lastmsg <> "334"){
            if ($smtp_debug) logwrite($bugf, "Authentication failed: $lastmsg...");
            exit;
        }
        if ($smtp_debug) logwrite($bugf, "Username accepted...\r\n");
        
        //Password
        $smtpcmd = base64_encode($mtpass)."\r\n";
        fputs($ssock, $smtpcmd);
        $srvmsg = fgets($ssock);
        $lastmsg = substr($srvmsg, 0, 3); if ($smtp_debug) logwrite($bugf, $srvmsg);
        if($lastmsg <> "235"){
            if ($smtp_debug) logwrite($bugf, "Authentication failed: $lastmsg...\r\n");
            exit;
        }
        if ($smtp_debug) logwrite($bugf, "Password accepted...\r\n");
    }
    //Step 3 send email
    if ($smtp_debug) logwrite($bugf, "Login success!\r\n");
    if ($smtp_debug) logwrite($bugf, "NOOP server now...\r\n"); //I don't know why need to NOOP at this point. To keep connection alive?
    $smtpcmd = 'NOOP' . "\r\n";
    fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, '>'.$smtpcmd);
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
    $lastmsg = substr($srvmsg, 0, 3);
    if ($lastmsg <> "250") {
        if ($smtp_debug) logwrite($bugf, "Connection lost: $lastmsg...\r\n");
        exit;
    }
    // piece message up and send
    // first provide sender - MAIL FROM
    // list($subj, $msg, $htmsg, $fatt)
    if ($smtp_debug) logwrite($bugf, "Sending mail now...\r\n");
    $smtpcmd = "MAIL FROM: <$errmail>\r\n";
    fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, '>'.$smtpcmd);
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
    $lastmsg = substr($srvmsg, 0, 3);
    if ($lastmsg <> "250") {
        if ($smtp_debug) logwrite($bugf, "Sending failed: $lastmsg...\r\n");
        exit;
    }
    if ($smtp_debug) logwrite($bugf, $svrmsg); // at this point we just log the server replies, don't get too excited...

        
//now provide recipient - RCPT TO
    $smtpcmd = "RCPT TO: <$email>\r\n";
    fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, '>'.$smtpcmd);
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
    $lastmsg = substr($srvmsg, 0, 3); if ($lastmsg <> "250") {
        if ($smtp_debug) logwrite($bugf, "Sending failed: $lastmsg...\r\n");
        exit;
    }

    //start data command - DATA
    $smtpcmd = "DATA\r\n";
    fputs($ssock, $smtpcmd); if ($smtp_debug) logwrite($bugf, '>'.$smtpcmd);
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, $srvmsg);
    $lastmsg = substr($srvmsg, 0, 3); if ($lastmsg <> "354") {
        if ($smtp_debug) logwrite($bugf, "Sending failed: $lastmsg...\r\n");
        exit;
    }

    //Filter actual data
    $themesg = $header . $body;
    $themesg = str_replace("\r\n", "\n", $themesg);
    $themesg = str_replace("\n", "\r\n", $themesg);
    $themesg = str_replace("\r\n.", "\r\n..", $themesg);
    $xmsg = str_replace('<', '&lt;', $themesg);
    $xmsg = str_replace('>', '&gt;', $xmsg);

    //debug
    if ($smtp_debug) logwrite($bugf,$xmsg);
    fputs($ssock, $themesg . "\r\n.\r\n");
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, "> SENT DATA\r\n");
    $lastmsg = substr($srvmsg, 0, 3); if ($smtp_debug) logwrite($bugf, $srvmsg);
    if ($lastmsg <> "250") {
        if ($smtp_debug)  logwrite($bugf, "Sending failed: $lastmsg...");
        exit;
    }

    //Done, close the connection
    $smtpcmd = "QUIT\r\n";
    fputs($ssock, $smtpcmd);
    $srvmsg = fgets($ssock); if ($smtp_debug) logwrite($bugf, '>'.$smptcmd);
    $lastmsg = substr($srvmsg, 0, 3); if ($smtp_debug) logwrite($bugf, $srvmsg);
    if ($lastmsg <> "221") {
        if ($smtp_debug) logwrite($bugf, "Connection error: $lastmsg...\r\n");
        exit;
    }
    //Done!   
}

// sendwelcome()

function detectpath($p, $fn = '') {
    global $windows;
    if ($windows) {
        return "not yet supported";
        $s = '\\';
        $q = $p . '.exe';
        $fs = array("C:\\$q", "C:\\$p\\$q", "C:\\$p\\bin\\$q");
    } else {
        $s = '/';
        $fs = array("/usr/bin/$p", "/usr/local/$p", "/usr/local/bin/$p", "/usr/local/bin/$p/$p", "/usr/local/$p/bin/$p");
        while (list($k, $v) = each($fs)) {
            $x = @shell_exec("ls $v");
            if ($x) {
                if ($fn)
                    return $v;
                else
                    return substr($x, 0, strrpos($x, $s) + 1);
            }
        }
        return false;
    }
}

// process and delete bouncing users based on email 2003-02-13
function bounce($email, $msg) {
    global $utable;
    global $ctable;
    global $ltable;
    global $link;
    if (!valid_email($email))
        return false;

    $brow = mysql_query("select nbounce from $ctable where 1", $link) or die('admin-44-' . mysql_error());
    list($nbounce) = mysql_fetch_row($brow);
    list($num, $days) = explode(':', $nbounce);

    $urows = mysql_query("select id,list,email,bounces from $utable where email like '" . addslashes($email) . "'") or die('admin-45-' . mysql_error());
    if (@mysql_num_rows($urows) > 0) {
        while (list($id, $list, $email, $bounces) = mysql_fetch_row($urows)) {
            $bounces = explode(';', $bounces);
            $today = date("Ymd", mktime(0, 0, 0, date("m"), date("d"), date("Y")));

            if (!$bounces[1]) {
                if ($num == '1') {
                    $narr = getnotifs($list);
                    if ($narr[3] == '1')
                        sendnotif('rem_bounce', $id, '', $msg);
                    $listopts = getlistopts($list);
                    // check whether to keep in db or not
                    if ($listopts[1] == 1)
                        mysql_query("update $utable set cnf = '3' where id = '$id'") or die('admin-47-' . mysql_error());
                    else
                        mysql_query("delete from $utable where id = '$id'") or die('admin-48-' . mysql_error());
                    // check remove from other lists
                    // if($listopts[4]=='1') remlists($email,$list,4);
                }
                mysql_query("update $utable set bounces = '0;$today' where id = '$id'") or die('admin-49-' . mysql_error());
            } else {
                // process
                // check and adjust values in case number of bounces is changed to a lower number
                if (count($bounces) > $num) {
                    $xbounces = $bounces;
                    $bounces = array();
                    $bounces[0] = $num - 2;
                    $x = 1;
                    while (list($key, $val) = each($xbounces)) {
                        if ($key >= count($xbounces) - ($num - 1)) {
                            $bounces[$x] = $val;
                            $x++;
                        }
                    }
                    reset($bounces);
                    $xbounces = '';
                }
                if (($bounces[0] + 2) >= $num) {
                    $time1 = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
                    $y = substr($bounces[1], 0, 4);
                    $m = substr($bounces[1], 4, 2);
                    $d = substr($bounces[1], 6, 2);
                    $time2 = mktime(0, 0, 0, $m, $d, $y);
                    $calc = ($time1 - $time2) / 86400;
                    if ($calc <= $days) {
                        // notify admin
                        $narr = getnotifs($list);
                        if ($narr[3] == '1')
                            sendnotif('rem_bounce', $id, '', $msg);
                        // perform operation
                        $listopts = getlistopts($list);
                        if ($listopts[1] == 1) {
                            mysql_query("update $utable set cnf = '3' where id = '$id'") or die('admin-51-' . mysql_error());
                        } else {
                            mysql_query("delete from $utable where id = '$id'") or die('admin-52-' . mysql_error());
                        }
                        // if($listopts[4]=='1') remlists($email,$list,4);
                    } else {
                        $n1 = $bounces[0] . ';' . $bounces[2];
                        while (list($key, $val) = each($bounces)) {
                            if ($key > 2) {
                                $n1 .= ';' . $bounces[$key];
                            }
                        }
                        $n1 .= ';' . $today;
                        mysql_query("update $utable set bounces = '$n1' where id = '$id'") or die('admin-53-' . mysql_error());
                    }
                } else {
                    $n1 = $bounces[0] + 1;
                    $n1 = "$n1";
                    while (list($key, $val) = each($bounces)) {
                        if ($key <> 0) {
                            $n1 .= ";$val";
                        }
                    }
                    $n1 .= ";$today";
                    reset($bounces);
                    mysql_query("update $utable set bounces = '$n1' where id = '$id'") or die('admin-54-' . mysql_error());
                }
            }
        }
    }
}

function nocacheheader() {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

function helplink($page) {
    switch ($page) {
        case 'help_faq': $id = '';
            break;
        case 'setup_general': $id = '25';
            break;
        case 'install_db': $id = '6';
            break;
        case 'custom_html': $id = '28';
            break;
        case 'scheduler': $id = 'scheduler';
            break;
        case 'message_codes': $id = '36';
            break;
        case 'installation': $id = '11';
            break;
        case 'send_email': $id = '34';
            break; // see also quick load ?
        case 'link_codes': $id = '37';
            break;
        case 'tracked_hits': $id = '37';
            break;
        case 'list_settings': $id = '26';
            break;
        case 'custom_fields': $id = '29';
            break;
        case 'signup_form': $id = '30';
            break;
        case 'followups': $id = '27';
            break;
        case 'import': $id = '33';
            break;

        case 'dailymail': $id = 'dailymail_cron';
            break;
        case 'backup_restore': $id = 'backup_restore';
            break;
        case 'export': $id = 'export';
            break;
        case 'sentmsgs': $id = 'sent_messages';
            break;
        case 'selection': $id = 'selection';
            break;
        case 'user_database': $id = 'user_database';
            break;
    }
    if (!$id && $page <> 'help_faq')
        $id = $page;
    return "http://www.listmailpro.com/help/$id.html?x=1";
}

function getdomain($www = '') {
    $dom = ($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    if (!$www && strpos(' ' . $dom, 'www.') > 0)
        $dom = str_replace('www.', '', $dom);
    return $dom;
}

function getdir() {
    $d = $_SERVER['PHP_SELF'];
    $d = substr($d, 0, strrpos($d, '/'));
    return $d;
}

function islogged() {
    global $ctable;
    if (!$_COOKIE['LMadm1'])
        return false;
    if (!$_COOKIE['LMadm2'])
        return false;
    $crow = mysql_query("select instid,admpw from $ctable where 1");
    if (@mysql_num_rows($crow) == 0)
        return false;
    list($i, $a) = @mysql_fetch_row($crow);
    $urow = mysql_query("select admpw from $ctable where md5(instid) = '" . addslashes($_COOKIE['LMadm1']) . "' and md5(admpw) = '" . addslashes($_COOKIE['LMadm2']) . "'") or die('admin-56-' . mysql_error());
    if (@mysql_num_rows($urow) > 0)
        return 1; else
        return false;
}

function getctype($in) {
    global $fields;
    if ($in == 'fname')
        return "User's First Name";
    elseif ($in == 'lname')
        return "User's Last Name";
    elseif ($in == 'fullname')
        return "User's Full Name";
    elseif ($in == 'email')
        return "User's Email Address";
    elseif ($in == 'date')
        return "Formatted Date";
    elseif ($in == 'db')
        return "DB Field";
    elseif ($in == 'text')
        return "User-defined Text";
    elseif ($in == 'uid')
        return "User's Unique ID";
    elseif ($in == 'title')
        return "User's List Title";
    elseif ($in == 'remove')
        return "Remove Link";
    elseif ($in == 'confirm')
        return "Confirmation Link";
    elseif ($in == 'ip')
        return "IP Address";
    elseif ($in == 'refurl')
        return "Referring URL";
    elseif ($in == 'dateadd')
        return "Date Added";
    elseif ($in == 'field1')
        return $fields[1];
    elseif ($in == 'field2')
        return $fields[2];
    elseif ($in == 'field3')
        return $fields[3];
    elseif ($in == 'field4')
        return $fields[4];
    elseif ($in == 'field5')
        return $fields[5];
    elseif ($in == 'field6')
        return $fields[6];
    elseif ($in == 'field7')
        return $fields[7];
    elseif ($in == 'field8')
        return $fields[8];
    elseif ($in == 'field9')
        return $fields[9];
    elseif ($in == 'field10')
        return $fields[10];
    elseif ($in == 'title')
        return "List Title";
    elseif ($in == 'uid')
        return "User ID Code";
    elseif ($in == 'link2html')
        return "Link to HTML";
    elseif ($in == 'remcnfht')
        return "Remove Confirm";
    elseif ($in == 'numsubs')
        return "Subscriber Count";
}

function getip() {
    $ipaddr = getenv("HTTP_X_FORWARDED_FOR");
    if (!$ipaddr)
        $ipaddr = getenv("HTTP_X_FORWARDED_FOR");
    if (!$ipaddr)
        $ipaddr = getenv("REMOTE_ADDR");
    if (!$ipaddr)
        $ipaddr = $REMOTE_ADDR;
    if (!$ipaddr) {
        $ipaddr = "Unknown";
    }
    return $ipaddr;
}

function getemsg($i, $t) {
    global $edtable;
    // $i=id $t=type
    // if no id, default to default emsgs id 0?
    $e = mysql_query("select data from $edtable where id = '$i' and typ = '$t'") or die('admin-57-' . mysql_error());
    if (@mysql_num_rows($e) > 0)
        list($d) = mysql_fetch_row($e); else {
        echo "Error message not found.";
        $d = false;
    }
    return $d;
}

function listopts($t, $l) {
    global $ltable;
    if ($t == 'chtmlid' || $t == 'errorid') {
        $r = mysql_query("select $t from $ltable where listnum = '$l';") or die('admin-58-' . mysql_error());
        if (@mysql_num_rows($r) > 0) {
            list($d) = mysql_fetch_row($r);
            return $d;
        } else
            return false;
    }
}

function emsg($t, $i) {
    global $edtable;
    $r = mysql_query("select data from $edtable where typ = '$t' and id = '$i';") or die('admin-59-' . mysql_error());
    if (@mysql_num_rows($r) > 0) {
        list($d) = mysql_fetch_row($r);
        return $d;
    } else
        return false;
}

function chtml($t, $i) {
    //echo "calling custom html code.. "."<br>";//debug
    global $chdtable;
    $r = mysql_query("select html,url from $chdtable where typ = '$t' and id = '$i';") or die('admin-60-' . mysql_error());
    // echo "select html from $chdtable where typ = '$t' and id = '$i';";
    //echo "select html from $chdtable where typ = '$t' and id = '$i';";//debug
    if (@mysql_num_rows($r) > 0) {
        //echo "custom html found";//debug
        list($d, $u) = mysql_fetch_row($r);
        if ($u)
            return 'URL:' . $u; else
            return $d;
    } else {
        echo "ListMail Custom HTML not found!  Please contact your administrator.<br>";
        return false;
    }
}

function unique_id($argLength = 8) {
    $id = md5(uniqid(rand(), true));
    return substr($id, rand(0, (31 - $argLength)), $argLength);
}

function capitalize($name = NULL) {
    if (empty($name))
        return false;
    $name = strtolower($name);
    $names_array = explode('-', $name);
    for ($i = 0; $i < count($names_array); $i++) {
        if (strncmp($names_array[$i], 'mc', 2) == 0 || ereg('^[oO]\'[a-zA-Z]', $names_array[$i])) {
            $names_array[$i][2] = strtoupper($names_array[$i][2]);
        }
        $names_array[$i] = ucfirst($names_array[$i]);
    }
    $name = implode('-', $names_array);
    return ucwords($name);
}

function mtime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

// queue protect functions (v1.85)
// notes: completed = 'q' should never be deleted or resumed

function qstart($qtype) {
    // qtype 1=send email 2=dailymail 3=import welc&cnf
    global $ptable;
    global $doformid;
    global $report_time;
    $now = date("Y-m-d H:i:s");
    $report_time = $now;
    $bat = make_batid();
    // echo "qstart: new bat=$bat formid=$doformid time=$now<br>";
    mysql_query("insert into $ptable values('','" . addslashes($bat) . "','" . addslashes($qtype) . "','" . addslashes($doformid) . "','$now','$now','','q');") or die(mysql_error());
    return $bat;
}

function qcheckin($bat, $start = '', $report = '') {
    global $ptable;
    // if($start) echo "start "; echo "qcheckin: bat=$bat time=".date("Y-m-d H:i:s")."<br>";
    $st = '';
    if ($start)
        $st .= ", completed = '0'";
    if ($report)
        $st .= ", report = '" . addslashes($report) . "'";
    mysql_query("update $ptable set lastact = '" . date("Y-m-d H:i:s") . "'" . $st . " where batid = '" . addslashes($bat) . "';") or die(mysql_error());
}

function qfinish($bat) {
    global $ptable, $ctable, $DEBUG, $phpmailf;
    // echo "qfinish: bat=$bat time=".date("Y-m-d H:i:s")."<br>";
    mysql_query("update $ptable set lastact = '" . date("Y-m-d H:i:s") . "', completed = '1' where batid = '" . addslashes($bat) . "';") or die(mysql_error());
    // if a dailymail queue, send report
    list($qtype, $started, $report) = mysql_fetch_row(mysql_query("select qtype,started,report from $ptable where batid = '" . addslashes($bat) . "';"));
    if ($qtype == '2') {
        if ($DEBUG)
            echo "sending report from qfinish()<br>";
        // send dailymail report
        $narr = getnotifs('', 'cfg');
        if ($narr[0] == '1') {
            $crow = mysql_query("select ver,errchk,admmail,erraddr,listmailpath from $ctable where 1");
            list($ver, $errchk, $admmail, $errmail, $lmpath) = mysql_fetch_row($crow);
            $xmails = explode(';', $admmail);
            $admmail = $xmails[0];

            if ($errchk == '2' || $errchk == '1')
                $errmail = $errmail; else
                $errmail = $admmail;
            $sendn = "ListMail Notification";
            $sende = "$errmail";
            // get date from ptable
            $subj = "Dailymail Report for " . date("l, F jS", dt2epoch($started));
            $crlf = "\n";
            $header = "From: \"$sendn\" <$sende>$crlf";
            $header .= "Return-Path: $errmail$crlf";
            $header .= "MIME-Version: 1.0$crlf";
            $header .="X-Mailer: ListMail v$ver$crlf";
            $header .="X-LM-Flags: 5.x.x";
            $report = $lmpath . "\n\n" . $report;
            if ($phpmailf)
                mail($admmail, $subj, $report, $header, "-f$errmail"); else
                mail($admmail, $subj, $report, $header);
        }
    }
}

function dt2epoch($dt) {
    return mktime(substr($dt, 11, 2), substr($dt, 14, 2), substr($dt, 17, 2), substr($dt, 5, 2), substr($dt, 8, 2), substr($dt, 0, 4));
}

// sqlsafe - usage: mysql_query(safe("select * from $utable where list = '\$list';"));
function safe($x) {
    $y = explode('\'', $x);
    while (list($k, $v) = each($y)) {
        if (substr($v, 0, 1) == '$') {
            $v = substr($v, 1);
            global ${$v};
            $x = str_replace("\$$v", addslashes(${$v}), $x);
        }
    }
    return $x;
}

function getlistopts($l) {
    global $link;
    global $ltable;
    $lrow = mysql_query("select listopts from $ltable where listnum = '" . addslashes($l) . "';", $link) or die(mysql_error());
    list($listopts) = mysql_fetch_row($lrow);
    return explode(';', $listopts);
}

function remlists($email, $list, $opt = '', $multis = '') {
    // option 2 = adding 4 = removing
    global $link;
    global $ltable;
    global $utable;
    global $logtxt;
    global $DEBUG;
    if ($DEBUG)
        echo "remlists() option=$opt";
    if ($opt == 2 || $opt == 4) {
        if (!$logtxt)
            $logtxt = '';
        $lx = $logtxt;
        $ly = '';
        list($addopts) = mysql_fetch_row(mysql_query("select addopts from $ltable where listnum = '$list';"));
        list($addrem, $addadd, $remrem, $remadd) = explode(';', $addopts);
        if ($opt == 2)
            $rl = $addrem;
        else
            $rl = $remrem;
    }
    $f = 1;
    $rl = explode(',', $rl);
    while (list($k, $v) = each($rl)) {
        if (($multis && !in_array($v, $multis)) || !$multis) {
            $listopts = getlistopts($v);
            if ($listopts[1] == '1') {
                mysql_query("update $utable set cnf = '2' where email like '$email' and list = '$v';");
            } else {
                mysql_query("delete from $utable where email like '$email' and list = '$v';");
            }
            if (@mysql_affected_rows() > 0) {
                if (!$f)
                    $ly .= ',';
                $ly .= $v;
                $f = '';
            }
        }
    }

    if (!$f) {
        if ($opt == 2)
            $lx .= 'Removed from list';
        elseif ($opt == 4)
            $lx .= 'Also removed from list';
        if (strpos($ly, ',') > 0)
            $lx .= 's '; else
            $lx .= ' ';
        $logtxt = $lx . $ly . '. ';
    }
}

function addlists($email, $list, $opt = '', $multis = '') {
    // option 3 = adding 5 = removing
    global $link;
    global $ftable;
    global $ltable;
    global $utable;
    global $logtxt;
    global $DEBUG;
    if ($DEBUG)
        echo "addlists() option=$opt";
    $dateadd = date("Y-m-d H:i:s");
    if ($opt == 3 || $opt == 5) {
        if (!$logtxt)
            $logtxt = '';
        $lx = $logtxt;
        $ly = '';
        list($addopts) = mysql_fetch_row(mysql_query("select addopts from $ltable where listnum = '$list';"));
        list($addrem, $addadd, $remrem, $remadd) = explode(';', $addopts);
        if ($opt == 3)
            $rl = $addadd;
        else
            $rl = $remadd;
    }
    $f = 1;
    $rl = explode(',', $rl);
    // get user data from current list/email
    $row = mysql_query("select * from $utable where list = '" . addslashes($list) . "' and email like '" . addslashes($email) . "';");
    list($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9, $a10, $a11, $a12, $a13, $a14, $a15, $a16, $a17, $a18, $a19, $a20, $a21, $a22, $a23) = mysql_fetch_row($row);

    while (list($k, $v) = each($rl)) {
        // add to lists
        if (($multis && !in_array($v, $multis)) || !$multis) {
            // check if on target list already, if so skip
            $row = mysql_query("select id from $utable where email like '" . addslashes($email) . "' and list = '" . addslashes($v) . "' and cnf = '1';") or die(mysql_error());
            $row2 = mysql_query("select id from $ltable where listnum = '$v';") or die(mysql_error());
            if ($DEBUG)
                echo "rows=" . @mysql_num_rows($row) . "<br>";
            if ($DEBUG)
                echo "rows2=" . @mysql_num_rows($row2) . "<br>";
            if (@mysql_num_rows($row) > 0 || @mysql_num_rows($row2) == 0) {
                // skip it
            } else {
                // check if on target list already, if so and not active delete it
                $row = mysql_query("select id from $utable where email like '" . addslashes($email) . "' and list = '" . addslashes($v) . "' and cnf <> '1';");
                if (@mysql_num_rows($row) > 0)
                    list($xid) = mysql_fetch_row($row);
                mysql_query("delete from $utable where id = '$xid';");
                // add the user
                // make uid
                $uniq = '';
                while (!$uniq) {
                    $uniq_str = unique_id(7);
                    if (@mysql_num_rows(mysql_query("select id from $utable where uid = '$uniq_str'")) == 0)
                        $uniq = 1;
                }
                // get seq and delay
                $row = mysql_query("select seq,del from $ftable where list = '" . addslashes($v) . "' order by seq limit 1");
                if (@mysql_num_rows($row) > 0) {
                    list($seq, $del) = mysql_fetch_row($row);
                } else {
                    $seq = '1';
                    $del = '0';
                }
                // insert data
                $debug_query = "insert into $utable values('','$uniq_str','$v','" . addslashes($a3) . "','" . addslashes($a4) . "','" . addslashes($a5) . "','" . addslashes($a6) . "','" . addslashes($a7) . "','" . addslashes($a8) . "','" . addslashes($a9) . "','" . addslashes($a10) . "','" . addslashes($a11) . "','" . addslashes($a12) . "','" . addslashes($a13) . "','" . addslashes($a14) . "','" . addslashes($a15) . "','$seq','$del','1','$dateadd','" . addslashes($a20) . "','" . addslashes($a21) . "','" . addslashes($a22) . "','" . addslashes($a23) . "');";
                mysql_query("insert into $utable values('','$uniq_str','$v','" . addslashes($a3) . "','" . addslashes($a4) . "','" . addslashes($a5) . "','" . addslashes($a6) . "','" . addslashes($a7) . "','" . addslashes($a8) . "','" . addslashes($a9) . "','" . addslashes($a10) . "','" . addslashes($a11) . "','" . addslashes($a12) . "','" . addslashes($a13) . "','" . addslashes($a14) . "','" . addslashes($a15) . "','$seq','$del','1','$dateadd','" . addslashes($a20) . "','" . addslashes($a21) . "','" . addslashes($a22) . "','" . addslashes($a23) . "');");
                //debug
                //echo '<span>' . $debug_query . '</span>';
                //debug
                if (!$f)
                    $ly .= ',';
                $ly .= $v;
                $f = '';
            }
            if ($DEBUG)
                echo "add email $email to list $v<br>";
        }
    }
    if (!$f) {
        if ($opt == 3)
            $lx .= 'Also added to list';
        elseif ($opt == 5)
            $lx .= 'Added to list';
        if (strpos($ly, ',') > 0)
            $lx .= 's '; else
            $lx .= ' ';
        $logtxt = $lx . $ly . '. ';
    }
}

// Connect
$link = mysql_connect($sqlhost, $sqluser, $sqlpass) or die("Could not connect to MySQL.  Please check your MySQL database, username, password, and permissions and set up ListMail's config.php<br><small>MySQL Error: " . mysql_error() . "</small>");
mysql_select_db($sqldb) or die("Could not select database<br><small>MySQL Error: " . mysql_error() . "</small>");

// get config
$crow = @mysql_query("select instid from $ctable where 1 limit 1");
@list($instid) = @mysql_fetch_row($crow);
if ($instid)
    $installed = 1; else
    $installed = '';

// EOF
?>
