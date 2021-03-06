<?php

/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
  You may not modify or distribute the program without express permission from the author.
  Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

ini_set("max_execution_time", "0");

// show debug info
// $DEBUG = true;
$debug_conn = 0;

if ($argv[1])
    $pw = $argv[1];

$isadmin = '';
if (!isset($pw))
    $pw = '';
$islogged = islogged();

if (!$pw) {
    if ($islogged) {
        $isadmin = 'true';
    } else {
        $isadmin = '';
        header("Location: ./login.php?r=" . urlencode(substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1)));
        exit;
    }
}

if ($pw)
    $isadmin = checkpw($pw);

// if(!$warn&&$pw) echo ".";

if ($isadmin == 'true' || $islogged == 'true') {
    if ($warn == 'yes') {
        adminheader("Dailymail", "Dailymail", '');

        if ($setopt) {
            if ($optval == 'true')
                $optval = '1'; else
                $optval = '';
            mysql_query("update $ctable set dayopt = '$optval' where 1;");
        }
        echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

        listmenu($list, 'daymail');

        echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";

        echo "<table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0>
 <tr class=table_head_bg_nopad>
  <td>
   <span class=table_head_text>Manually Execute Dailymail</span><br>
  </td>
  <td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('" . helplink('dailymail') . "');\">Help</a><br></td>
 </tr>
</table>
<img src=1.gif height=10><br>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <span class=table_inside_normal style=\"color: darkred;\"><big><b>> > Warning! < <</b></big><br></span><span class=table_inside_normal><img src=1.gif height=10><br>The \"Dailymail\" script should normally be executed automatically via a daily scheduled task on your web host (see <u>Help</u> at top-right).   Running dailymail manually will perform maintenance, send due scheduled messages, advance the timing of all of your users' currently scheduled followups by one day (including the sending of due messages), and process bounces by mailbox if you are using that feature.<br>
<img src=1.gif height=10><br>\n";
        // get dailymail last run time and errchk for bounce checking
        $crow = mysql_query("select daylast,errchk,dayopt from $ctable where 1");
        list($dl, $errchk, $dayopt) = mysql_fetch_row($crow);
        if ($dayopt <> '1')
            $dayopt = '';
        if ($dayopt)
            $dayoptchk = ' checked=checked'; else
            $dayoptchk = '';

        $dl = dt2epoch($dl);
        $lrd = date("l, F jS, Y", $dl);
        $lrt = date("g:ia (+s\s) T", $dl);
        echo "Last Dailymail execution:<br> <b>$lrd</b> at <b>$lrt</b><br>
<img src=1.gif height=10><br>
<input type=checkbox class=chkbox name=dayopt value=1$dayoptchk onclick=\"hopto('./dailymail.php?list=$list&warn=yes&setopt=1&optval='+this.checked);\"> Do not allow dailymail to be run automatically until 23 hours 55 minutes has elapsed. (Recommended)<br>
<img src=1.gif height=10><br>
<u>Manually Execute Dailymail</u><br>
<img src=1.gif height=10><br>
Whether the time has elapsed or not you may run dailymail manually and choose whether or not to update the last execution time.<br>
<img src=1.gif height=10><br>
<form name=dfrm method=post action=" . $_SERVER['PHP_SELF'] . ">
<input type=hidden name=nolastran value=\"\">
<input type=checkbox class=chkbox name=updatelr value=1> Update last execution time.  I understand that dailymail may not run at the next scheduled execution.<br>
<img src=1.gif height=10><br>
If you wish, you may select certain parts of dailymail to be skipped.<br>
<img src=1.gif height=10><br>
<input type=hidden name=list value=$list>
<input type=hidden name=warn value=no>
<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_inside_normal align=left nowrap>
<input type=checkbox class=chkbox name=nomaint value=1> Skip maintenance<br>
<input type=checkbox class=chkbox name=noscfup value=1> Skip scheduled followup set application<br>
<input type=checkbox class=chkbox name=nosched value=1> Skip scheduled messages<br>
<input type=checkbox class=chkbox name=nofups value=1> Skip followup message/user processing and sending<br>
<input type=checkbox class=chkbox name=noremind value=1> Skip confirmation reminders<br>\n";

        // check if checking bounced mail
        if ($errchk)
            echo "<input type=checkbox class=chkbox name=nobounce value=1> Skip bounce via mailbox<br>";

        echo "
  </td>
 </tr>
</table>
<img src=1.gif height=10><br>
A dailymail report, if enabled, is always sent to the ListMail administrator.<br>
<img src=1.gif height=10><br>
<input class=button type=submit onclick=\"if(!confirm('Running dailymail will perform maintenance, send due scheduled messages, advance the timing of all of your users\\' currently scheduled followups by one day (including the sending of due messages), and process bounces by mailbox if you are using that feature.\\n\\nAre you sure you want to run Dailymail?')){ return false; } if(dfrm.updatelr.checked) dfrm.nolastran.value=''; else dfrm.nolastran.value='1';\" value=\"Run Dailymail Now\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 130px\"><br>
</form>
</span><br>
  </td>
 </tr>
</table>

</td></tr></table>
</body>
</html>";
    } else {
        // BEGIN DAILYMAIL
        if ($demo)
            exit('Not available in demo');

        if ($warn == 'no')
            $manual = 1; else
            $manual = '';

        $crow = mysql_query("select daylast,dayopt,errchk from $ctable where 1");
        list($dl, $do, $errchk) = mysql_fetch_row($crow);

        // check last ran time
        if (!$manual) {
            $dl = dt2epoch($dl);
            // check if time now is more than 23:55 from last ran
            if (time() - $dl > 86100) {
                // good
            } else {
                if ($do == '1') {
                    exit('Dailymail run too soon - it should be scheduled for <u>once</u> per day, every 24 hours.  If you wish to run it again please do so manually from within the ListMail administation panel.');
                }
            }
        }

        // update dailymail 'last ran' time
        if (!$nolastran) {
            mysql_query("update $ctable set daylast = '" . date("Y-m-d H:i:s") . "';");
        }

        // check and repair all tables
        // optimize tables
        optimizeall();
        $sendq = 0; // now a counter..
        // DAILYMAIL SCRIPT
        $report_time = '';
        $batid = qstart(2);
        if ($warn == 'no')
            $outp = 1;
        // initialize style for button if output
        if ($outp) {
            adminheader('Dailymail', 'Dailymail', 'daymail');
            echo "<img src=1.gif height=10><br><span class=table_inside_normal>";
            flush();
        }

        if ($nomaint && $nosched && $nofups && ($errchk <> '1' || $nobounce) && $noscfup && $noremind)
            echo "Nothing to do!<br>";
        if (!$nomaint) {
            // begin maintenance
            if ($outp) {
                echo "Performing maintenance...";
                flush();
            }
            $m = date("m");
            $y = date("Y");
            $d = date("d");
            $ymd = date("Y-m-d");

            // delete queues older than 3 days that are no longer queued
            /* $prows = mysql_query("select id,batid from $ptable where lastact < '".date("Y-m-d H:i:s",time()-259200)."';");
              if(@mysql_num_rows($prows)>0){
              while(list($id,$bid)=mysql_fetch_row($prows)){
              $orows = mysql_query("select id from $otable where bat = '$bid' limit 1;");
              if(@mysql_num_rows($orows)==0) mysiql_query("delete from $ptable where id = '$id';");
              }
              } */
            // delete old completed queues inactive more than 3 days
            //mysql_query("delete from $ptable where lastact < '".date("Y-m-d H:i:s",time()-259200)."' and completed = '1';");
            // get maintenance from config
            list($maint) = mysql_fetch_row(mysql_query("select maint from $ctable where 1"));
            $maint = explode(';', $maint);
            $maint_sent = $maint[0];
            if (!$maint_sent)
                $maint_sent = '0'; //Max num of sent message in DB
            $maint_hits = $maint[1];
            if (!$maint_hits)
                $maint_hits = '0'; //Max tracked hits in database
            $maint_unc = $maint[2];
            if (!$maint_unc)
                $maint_unc = '0'; //Delete unconfirmed users after __ days

            if ($maint_sent > '0') {
                // delete excess sent messages
                $cmd = "select id from $ttable where 1 order by id desc limit " . ($maint_sent - 1) . ",18446744073709551615";
                $trows = mysql_query($cmd) or die(mysql_error());
                if (@mysql_num_rows($trows) > 0) {
                    while (list($fromid) = mysql_fetch_row($trows)) {
                        mysql_query("delete from $ttable where id < '$fromid'");
                    }
                    @mysql_free_result($trows);
                }
            }

            if ($maint_hits > '0') {
                // delete excess hits
                $cmd = "select id from $htable where 1 order by id desc limit $maint_hits,1";
                $drows = mysql_query($cmd);
                if (@mysql_num_rows($drows) > 0) {
                    list($fromid) = mysql_fetch_row($drows);
                    $fromid = $fromid - 1;
                    mysql_query("delete from $htable where id < '$fromid'");
                    @mysql_free_result($drows);
                }
            }

            if ($maint_unc > '0') {
                // delete unconfirmed users
                $dday = date("Y-m-d", mktime(0, 0, 0, $m, $d - $maint_unc, $y));
                $cmd = "delete from $utable where cnf = '0' and dateadd < '$dday'";
                mysql_query($cmd);
            }
            // done maint
            echo " Done!<br>";
            flush();
        } // !nomaint
        // initialize stats
        $stats = array();
        $lrows = @mysql_query("select listnum from $ltable where 1 order by listnum");
        while (list($listnum) = @mysql_fetch_row($lrows)) {
            $stats[$listnum]['users'] = '0';
            $stats[$listnum]['newusers'] = '0';
            $stats[$listnum]['bouncing'] = '0';
            $stats[$listnum]['deleted'] = '0';
            $stats[$listnum]['stopped'] = '0';
            $stats[$listnum]['unconf'] = '0';
            $stats[$listnum]['scheduled'] = '';
            $stats[$listnum]['schedfups'] = '';
            $stats[$listnum]['sentfups'] = '0';
        }
        @mysql_free_result($lrows);
        $stats[0]['deleted'] = '0';
        $stats[0]['bouncing'] = '0';
        $stats[0]['scheduled'] = '';
        $stats[0]['schedfups'] = '';
        $stats[0]['sentfups'] = '0';
        $report = '';


        if (!$noscfup) {
            // scheduled followup application
            $qrows = mysql_query("select id,list,subject,message,htmessage,fattach from $qtable where date <= '" . date("Y-m-d") . "' and type = 'f' order by id");
            if (@mysql_num_rows($qrows) > 0) {
                while (list($qid, $qlist, $fsid, $qmsg, $qhtmsg, $qfatt) = @mysql_fetch_row($qrows)) {
                    // echo "processing schedule: qid=$qid,qlist=$qlist,fsid=$fsid,qmsg=$qmsg,qhtmsg=$qhtmsg,qfatt=$qfatt<br>";
                    @mysql_query("delete from $qtable where id = '$qid'");
                    $error = '';
                    if (!is_numeric($qlist))
                        $error .= 'list is not numeric. ';
                    if (!is_numeric($fsid))
                        $error .= 'saved followup set id not numeric. ';
                    $lrow = mysql_query("select id from $ltable where listnum = '$qlist';");
                    if (@mysql_num_rows($lrow) == 0)
                        $error .= 'list ' . $qlist . ' not found! ';
                    if (!$error) {
                        $frows = mysql_query("select name from $fstable where id = '$fsid';");
                        if (@mysql_num_rows($frows) == 0)
                            $error .= 'followup set id ' . $fsid . ' not found. ';
                        if (!$error)
                            list($setname) = mysql_fetch_row($frows);
                    }
                    if (!$error) {
                        // echo "deleting followups on list $qlist..<br>";
                        if (!mysql_query("delete from $ftable where list = '$qlist';"))
                            $error .= 'delete command failed. ';
                    }
                    if (!$error) {
                        $frows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron from $ftable where sset = '$fsid' order by seq;");
                        if (@mysql_num_rows($frows) > 0) {
                            while (list($sub, $con, $htc, $fat, $seq, $del, $cro) = mysql_fetch_row($frows)) {
                                // echo "inserting followup $seq..<br>";
                                // insert new followups
                                mysql_query("insert into $ftable values('','','" . addslashes($qlist) . "','" . addslashes($sub) . "','" . addslashes($con) . "','" . addslashes($htc) . "','" . addslashes($fat) . "','" . addslashes($seq) . "','" . addslashes($del) . "','" . addslashes($cro) . "','')");
                            }
                        } else
                            $error .= 'no followups in set! ';
                    }

                    if (!$error) {
                        // reset all users to first seq and del
                        $frows = mysql_query("select seq,del from $ftable where seq > '0' and list = '$qlist' order by seq limit 1;");
                        if (@mysql_num_rows($frows) > 0) {
                            list($ns, $nd) = mysql_fetch_row($frows);
                        } else {
                            $ns = '1';
                            $nd = '0';
                        }
                        mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where list = '$qlist';");
                        $setnu = mysql_affected_rows();
                        $stats[0]['schedfups'] .= "Followup Set \"$setname\" Applied to List $qlist. Reset $setnu Users to Seq $ns Delay $nd.\n";
                        $stats[$qlist]['schedfups'] .= "Followup Set \"$setname\" Applied. Reset $setnu Users to Seq $ns Delay $nd.\n";
                    }
                }
                echo mysql_num_rows($qrows) . " scheduled followup sets applied.<br>";
            } else {
                if ($outp) {
                    echo "No scheduled followup sets to apply.<br>";
                    flush();
                }
            }

            @mysql_free_result($qrows);
            @mysql_free_result($frows);
            @mysql_free_result($lrow);
            // end of scheduled followup sets
        } // !noscfup
        // bounced processing
        // check if checking bounced mail
        if ($errchk == '1' && !$nobounce) {
            // get mail server settings and connect
            $result = mysql_query("select errhost,errport,erruser,errpass,errfrom from $ctable where 1");
            list($ehost, $eport, $euser, $epass, $errfrom) = mysql_fetch_row($result);
            @mysql_free_result($result);

            if ($outp) {
                echo "Connecting to bounced message server...";
                flush();
            }
            $asock = fsockopen($ehost, $eport);
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
                    if ($outp) {
                        echo " Cannot connect to <b>$ehost</b>:$eport<br>";
                        flush();
                    }
                } else {
                    if ($outp) {
                        echo " Connected!<br>Verifying username and password...";
                        flush();
                    }

                    // send user
                    fputs($asock, "USER $euser\r\n");
                    $srvmsg = fgets($asock, 1024);
                    $lastmsg = substr($srvmsg, 0, 1);
                    if ($lastmsg <> "+")
                        $error = 1; else
                        $error = '';

                    // send pass
                    fputs($asock, "PASS $epass\r\n");
                    $srvmsg = fgets($asock, 1024);
                    // echo " PASSmsg=$srvmsg.. ";
                    $lastmsg = substr($srvmsg, 0, 1);
                    if ($lastmsg <> "+")
                        $error = 1; else
                        $error = '';
                    if ($error) {
                        if ($outp) {
                            echo " Failed!<br>";
                            flush();
                        }
                    } else {
                        if ($outp) {
                            echo " Verified!<br>";
                            flush();
                        }
                        // process bounced
                        // check # of messages
                        fputs($asock, "STAT\r\n");
                        $srvmsg = fgets($asock, 1024);
                        //echo " STATmsg=$srvmsg.. "; //flush();debug
                        $lastmsg = substr($srvmsg, 0, 1);
                        if ($lastmsg <> "+")
                            $error = 1;
                        $numsgs = explode(' ', $srvmsg); //echo 'srvmsg='.$srvmsg.'<br>';
                        $numsgs = $numsgs[1]; //echo 'num of msg'.$numsgs.'<br>';//debug
                        // $mailsize = $numsgs[2];
                        if ($numsgs <> '0')
                            $bounced = $numsgs; 
                        else
                            $bounced = '';
                        $numsgs = '';
                        // process bounced

                        if ($bounced) {
                            $deleted = 0;
                            if ($outp) {
                                echo " Processing $bounced bounced messages, please wait...<br>";
                                flush();
                            }
                            
                            // loop through each email to check if should update the bounce counter
                            for ($i = 1; $i <= $bounced; $i++) {
                                fputs($asock, "RETR $i\r\n");
                                $srvmsg = fgets($asock, 1024);
                                $lastmsg = substr($srvmsg, 0, 1);
                                if ($lastmsg <> "+") $error = 1;

                                $data = '';
                                $msgline = array();
                                while (substr($srvmsg = fgets($asock, 1024), 0, 3) <> ".\r\n") {
                                    array_push($msgline, $srvmsg);
                                    //echo $srvmsg.'<br>';//debug
                                }
                                //if($i==3) die();//debug
                                $themesg = '';
                                while (list($key, $val) = each($msgline))
                                    $themesg .= "> " . $val;

                                $flagpos = strpos($themesg, $searchstr);
                                $em = $themesg;

                                $error = 1;
                                $schar = 0;
                                $cchar = 0;
                                $cline = 0;
                                $done = '';
                                while (!$done) {
                                    if ($cchar == strlen($em))
                                        $done = 1;
                                    if ($em[$cchar] <> "\n" && !$done) {
                                        $cchar++;
                                    } else {
                                        $line = substr($em, $schar, $cchar - $schar);
                                        if ($line)
                                            $lines[$cline] = $line;
                                        $schar = $cchar + 1;
                                        $cline++;
                                        $cchar++;
                                    }
                                }

                                // find lines that start with To: and From:
                                @reset($lines);
                                $from = '';
                                while (list($key, $val) = each($lines)) {
                                    $val2 = $val;
                                    if (strpos(strtoupper(substr($val, 0, 5)), strtoupper('To:')) !== false) {
                                        //echo 'val='.$val.'<br>';
                                        $val = str_replace('<', '', $val);
                                        $val = str_replace('>', '', $val);
                                        $tpos = strpos(strtoupper(substr($val, 0, 5)), strtoupper('To:'));
                                        $qpos = strpos($val, '" ');
                                        if ($qpos !== false) {
                                            $line = substr($val, $qpos + 2, strlen($val) - $qpos - 2);
                                        } else
                                            $line = substr($val, $tpos + 4, strlen($val) - $tpos - 4);
                                        $line = str_replace("\r", '', $line);
                                        $line = str_replace("\n", '', $line);
                                        $line = str_replace(' ', '', $line);
                                        $target = $line;
                                    }
                                    //echo 'val2='.$val2.'<br>';
                                    if (strpos(strtoupper(substr($val2, 0, 7)), strtoupper('From:')) !== false
                                            && !$from) {
                                        $val2 = str_replace('<', '', $val2);//echo 'val2='.$val2.'<br>';
                                        $val2 = str_replace('>', '', $val2);//echo 'val2='.$val2.'<br>';
                                        $tpos = strpos(strtoupper(substr($val2, 0, 7)), strtoupper('From:'));//echo 'tpos='.$tpos.'<br>';
                                        $qpos = strpos($val2, '" ');//echo 'qpos='.$qpos.'<br>';
                                        if ($qpos !== false) {
                                            $line2 = substr($val2, $qpos + 4, strlen($val2) - $qpos - 4);//echo 'line2='.$line2.'<br>';
                                        } else
                                            $line2 = substr($val2, $tpos + 6, strlen($val2) - $tpos - 6);//echo 'line2='.$line2.'<br>';
                                        $line2 = str_replace("\r", '', $line2);//echo 'line2='.$line2.'<br>';
                                        $line2 = str_replace("\n", '', $line2);//echo 'line2='.$line2.'<br>';
                                        $line2 = str_replace(' ', '', $line2);//echo 'line2='.$line2.'<br>';
                                        $from = $line2;
                                    }
                                }//echo 'Target='.$target.'<br>';//debug
                                //echo 'From='.$from.'<br>';//debug
                                if ($target && $from == $errfrom)
                                    //bounce($target, $em); //loops through ALL lists to update email bounce debug
                                    bounce_remote($target, $em);// should not use bounce2 because it should be detected that email is not a bounce email before calling bounce()
                                else continue;
                                    
                                $stats[0]['bouncing']++; //total number of bounces
                                $stats[$ulist]['bouncing']++; //total number of bounces for list #$ulist
                                
                                //fputs($asock, "RSET \r\n");
                                //$srvmsg = fgets($asock, 1024);echo 'RSET='.$srvmsg.'<br>';
                                //fputs($asock, "DELE $i\r\n");//debug
                                //$srvmsg = fgets($asock, 1024);
                                //$lastmsg = substr($srvmsg, 0, 1);
                                // if ($lastmsg <> "+") $error = 1; else $error = '';
                            } // for each bounced msg

                            fputs($asock, "QUIT\r\n");
                            $srvmsg = fgets($asock, 1024);
                            $lastmsg = substr($srvmsg, 0, 1);
                            if ($lastmsg <> "+")
                                $error = 1; else
                                $error = '';
                            if ($outp) {
                                echo " Done!<br>$bounced bounced messages processed.<br>";
                                if ($deleted > 0)
                                    echo $deleted . " users deleted for too many bounces.<br>";
                                flush();
                            }
                        } else {
                            // no bounced msgs
                            if ($outp) {
                                echo "No bounced messages.<br>";
                                flush();
                            }
                        } // end bounced
                    }
                }
            } else {
                // if mbox
                echo "Failed connecting to <b>$ehost</b>!<br>";
            }
        } else {
            // echo "Bounced processing not enabled. (returned to the admin)<br>";
        }
        // reset counter
        // $sendq = 0;


        if (!$nosched) {
            // scheduled mailings.
            $qrows = mysql_query("select id,list,subject,message,htmessage,fattach from $qtable where date <= '" . date("Y-m-d") . "' and type = 'm' order by id");
            if (@mysql_num_rows($qrows) > 0) {
                include('./selfunc.php');
                while (list($qid, $qlist, $qsubj, $qmsg, $qhtmsg, $qfatt) = @mysql_fetch_row($qrows)) {
                    @mysql_query("delete from $qtable where id = '$qid'");
                    // echo "qid=$qid,qlist=$qlist,qsubj=$qsubj,qmsg=$qmsg..<br><br>";
                    $lcmd = 'select id,listnum, remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum='.$qlist;
                    $listremote = mysql_query($lcmd);
                    list($lid,$listnum,$remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = @mysql_fetch_row($listremote);
                    
                    if (is_numeric($qlist)) {
                        //check if list is remote

                        $cmd = "select email,id from $utable where list = '$qlist' and cnf = '1'";
                        $stats[0]['scheduled'] .= "Scheduled Email to List $qlist: $qsubj\n";
                        $stats[$qlist]['scheduled'] .= "Scheduled Email: $qsubj\n";
                    } else {
                        $tors = substr($qlist, 2, strlen($qlist) - 2);
                        $cmd = make_rquery('send', $tors);
                        $stats[0]['scheduled'] .= "Scheduled Email to Selection \"" . rname($tors) . "\": $qsubj\n";
                    }
                    if($remote){
                        if(!$dbh){
                            try {
                                $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                                //echo ++$debug_conn.'<br>';//debug
                            } catch (PDOException $e) {
                                die('dailymail-4-' . $e->getMessage());
                            }
                        }
                        $dbh_query = $dbh->query($cmd);
                        $numsent = $dbh_query->rowCount();
                        $urows = $dbh_query->fetchAll();
                        //$dbh = null; //close the connection
                    }else{
                        $uresult = mysql_query($cmd);
                        $urows = array();
                        $numsent = 0;
                        while($user = mysql_fetch_row($uresult)){
                            $urows[$numsent++] = $user;
                        }
                    }
                    
                    $qsubj = addslashes($qsubj);
                    $qmsg = addslashes($qmsg);
                    $qhtmsg = addslashes($qhtmsg);
                    $qfatt = addslashes($qfatt);
                    $cmd = "insert into $ttable values('','$qsubj','$qmsg','$qhtmsg','$qfatt','" . date("Y-m-d H:i:s") . "','1','$numsent','" . addslashes($qlist) . "')";
                    @mysql_query($cmd);
                    $qsubj = stripslashes($qsubj);
                    $msgid = mysql_insert_id();

                    //while (list($xem, $uid) = @mysql_fetch_row($urows)) {
                    while(list(,$user) = each($urows)){
                        list($xem, $uid) = $user;
                        $xid = calc32();
                        mysql_query("insert into $otable values('$xid','$batid','2','1','$qlist','$uid','$msgid','');") or die(mysql_error()); 
                        $sendq++;
                    }
                }
                // print_r($stats[0]['scheduled']);
                echo mysql_num_rows($qrows) . " scheduled messages to be sent.<br>";
            } else {
                if ($outp) {
                    echo "No scheduled messages to send.<br>";
                    flush();
                }
            }
            mysql_free_result($qrows);
            // end of scheduled messages
        } // !nosched
        // get all lists
        $cmd = "select listnum,title,addopts,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where 1 order by listnum";
        $lrows = mysql_query($cmd);
        $numlists = @mysql_num_rows($lrows);
        $stats[0]['lists'] = $numlists;
        $fupz = 0;

        if (!$nofups) {

            // begin queueing followups
            include('./cron.php');
            $cr = new cron;
            $now = date("Ymd", time());

            while (list($list, $title, $addopts, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row($lrows)) {
                if ($DEBUG)
                    echo "START addopts=$addopts<br>";
                $addopts = explode(';', $addopts);
//   if($DEBUG) echo "ar=$ar,aa=$aa,rr=$rr,ra=$ra,fpsc=$fpsc,fpsid=$fpsid<br>";
                if ($DEBUG)
                    echo "[List $list] ";

                // get last followup
                $cmdl = "select id as fid,list as flist,seq,del from $ftable where list = '$list' order by seq desc limit 1";
                $resultl = mysql_query($cmdl);
                if (@mysql_num_rows($resultl) > 0) {
                    list($fid, $flist, $seq) = @mysql_fetch_row($resultl);
                    if (is_numeric($seq))
                        $lastf = $seq + 1; else
                        $lastf = '1';
                    if ($DEBUG)
                        echo "EOS=$lastf<br>";
                    $fupsexist = 1;
                } else {
                    if ($DEBUG)
                        echo "No followups<br>";
                    $fupsexist = '';
                }
                if ($fupsexist) {
                    // get 1st followup
                    list($fs, $fd) = mysql_fetch_row(mysql_query("select seq,del from $ftable where list = '$list' order by seq limit 1"));
                    $listopts = getlistopts($list);
                    if ($DEBUG)
                        echo "START listopts=<br>" . print_r($listopts);
                    // check if looping
                    if (substr($listopts[6], 0, 1) == '1') {
                        list($null, $xs, $xd) = explode(':', $listopts[6]);
                        if (isset($xs) && isset($xd) && is_numeric($xs) && is_numeric($xd)) {
                            $fs = $xs;
                            $fd = $xd;
                        }
                        if ($DEBUG)
                            echo "loop is enabled. resetting to seq: $fs del: $fd<br>";

                        // reset users
                        if ($remote) {
                            if(!$dbh){
                                try {
                                    $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                                    $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                                    //echo ++$debug_conn.'<br>';//debug
                                } catch (PDOException $e) {
                                    die('dailymail-1-' . $e->getMessage());
                                }
                            }
                            $dbh_query = $dbh->exec("update $utable set cseq = '$fs', cdel = '$fd' where list = '$list' and cseq >= '$lastf';");
                            //$dbh = null; //close the connection
                        } else {
                            mysql_query("update $utable set cseq = '$fs', cdel = '$fd' where list = '$list' and cseq >= '$lastf';");
                            if ($DEBUG)
                                echo mysql_affected_rows() . " users looped!<br>";
                        }
                    }

                    // check if loading a new set
                    if ($listopts[7] == '1') {
                        if ($DEBUG)
                            echo "set loading on % complete is enabled<br>";
                        // get total active users on list
                        if($remote){
                            if(!$dbh){
                                try {
                                    $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                                    $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                                    //echo ++$debug_conn.'<br>';//debug
                                } catch (PDOException $e) {
                                    die('dailymail-2-' . $e->getMessage());
                                }
                            }
                            $dbh_query = $dbh->query("select id,cseq from $utable where list = '$list' and cnf = '1';");
                            $nu = $dbh_query->rowCount();
                            $nue = 0;
                            while(list($id,$cseq)=$dbh_query->fetch()){
                                if($cseq >= $lastf) $nue++;
                            }
                            //$dbh = null; //close the connection
                        }else{
                            $rows = mysql_query("select id from $utable where list = '$list' and cnf = '1';");
                            $nu = @mysql_num_rows($rows);
                            // get number of active users at eos
                            $rows = mysql_query("select id from $utable where list = '$list' and cnf = '1' and cseq >= '$lastf';");
                            $nue = @mysql_num_rows($rows);
                        }
                        
                        // calc percentage
                        $perc = round($nue / $nu, 9) * 100;
                        if ($DEBUG)
                            echo " $nu active users / $nue >= eos = $perc ";
                        // if percentage >= set, load set

                        if ($perc >= $addopts[4]) {
                            if ($DEBUG)
                                echo " LOAD SET id=$fpsid<br>";
                            // turn off set loading option and reset data !
                            if ($DEBUG)
                                echo "Turn off set loading option and reset data<br>";
                            // delete existing fups
                            mysql_query("delete from $ftable where list = '$list';");
                            $frows = mysql_query("select subject,content,htcontent,fattach,seq,del,cron from $ftable where sset = '" . $addopts[5] . "' order by seq;");
                            if (@mysql_num_rows($frows) > 0) {
                                while (list($sub, $con, $htc, $fat, $seq, $del, $cro) = mysql_fetch_row($frows)) {
                                    // echo "inserting followup $seq..<br>";
                                    // insert new followups
                                    mysql_query("insert into $ftable values('','','" . addslashes($list) . "','" . addslashes($sub) . "','" . addslashes($con) . "','" . addslashes($htc) . "','" . addslashes($fat) . "','" . addslashes($seq) . "','" . addslashes($del) . "','" . addslashes($cro) . "','')");
                                }
                                // reset all users to first seq and del
                                list($ns, $nd) = mysql_fetch_row(mysql_query("select seq,del from $ftable where list = '$list' order by seq limit 1;"));
                                if ($DEBUG)
                                    echo "new saved set loaded!  new seq=$ns new del=$nd<br>";

                                mysql_query("update $utable set cseq = '$ns', cdel = '$nd' where list = '$list';");
                                $setnu = mysql_affected_rows();
                                $stats[0]['schedfups'] .= "Followup Set \"$setname\" Applied to List $list. Reset $setnu Users to Seq $ns Delay $nd.\n";
                                $stats[$list]['schedfups'] .= "Followup Set \"$setname\" Applied. Reset $setnu Users to Seq $ns Delay $nd.\n";
                            }
                            // turn off set loading option
                            $listopts[7] = '0';
                            $listopts = implode(';', $listopts);
                            if ($DEBUG)
                                echo "FINAL listopts=$listopts<br>";
                            $addopts[4] = '0';
                            $addopts[5] = '0';
                            $addopts = implode(';', $addopts);
                            if ($DEBUG)
                                echo "FINAL addopts=$addopts<br>";
                            mysql_query("update $ltable set listopts = '" . addslashes($listopts) . "', addopts = '" . addslashes($addopts) . "' where listnum = '$list';") or die(mysql_error());
                        }
                    }

                    // check if moving to a new list/seq/del
                    if ($listopts[8] == '1') {
                        $xl = $addopts[6];
                        $xs = $addopts[7];
                        $xd = $addopts[8];
                        if (isset($xs) && isset($xd) && is_numeric($xs) && is_numeric($xd)) {
                            $fs = $xs;
                            $fd = $xd;
                        }
                        if ($DEBUG)
                            echo "eos move users enabled. resetting to list $xl seq $xs del $xd<br>";
                        // loop eos users
                        $rows = mysql_query("select id,email from $utable where list = '$list' and cseq >= '$lastf';");
                        if (@mysql_num_rows($rows) > 0) {
                            while (list($fuid, $fem) = mysql_fetch_row($rows)) {
                                $moved++;
                                // check dupe
                                $row = mysql_query("select id from $utable where list = '$xl' and email like '$fem';");
                                if (@mysql_num_rows($row) > 0) {
                                    while (list($fxid) = mysql_fetch_row($row)) {
                                        mysql_query("delete from $utable where id = '$fxid';");
                                    }
                                }
                                // update user
                                mysql_query("update $utable set list = '$xl', cseq = '$xs', cdel = '$xd' where id = '$fuid';");
                                // TODO: list add. ops
                            }
                            if ($DEBUG)
                                echo "$moved users moved!<br>";
                        }
                    }

                    // check if removing users at eos
                    if ($listopts[9] == '1') {
                        if ($DEBUG)
                            echo "eos remove users enabled.<br>";
                        $rows2 = mysql_query("select id,email from $utable where list = '$list' and cseq >= '$lastf';");
                        if (@mysql_num_rows($rows2) > 0) {
                            while (list($uid, $uem) = mysql_fetch_row($rows2)) {
                                if ($listopts[1] == 1)
                                    mysql_query("update $utable set cnf = '2' where id = '$uid';");
                                else
                                    mysql_query("delete from $utable where id = '$uid';");
                                if ($listopts[4] == '1')
                                    remlists($uem, $list, 4);
                                if ($listopts[5] == '1')
                                    addlists($uem, $list, 5);
                            }
                        }
                        if ($DEBUG)
                            echo mysql_num_rows($rows2) . " users removed<br>";
                    }

                    // send followups
                    // get all users for the current list
                    $cmd2 = "select id,cseq,cdel,email from $utable where list = '$list' and cnf = '1'";
                    $result2 = mysql_query($cmd2);

                    // check all users - need to send a message?
                    while (list($uid, $cseq, $cdel, $email) = @mysql_fetch_row($result2)) {
                        $noadvance = false;
                        $sendids = array();
                        // if past end of sequence, set to end of sequence and skip user
                        if ($cseq >= $lastf) {
                            if ($DEBUG)
                                echo "User at or above EOS, skipping<br>";
                            if ($cseq > $lastf)
                                mysql_query("update $utable set cseq = '$lastf', cdel = '0' where id = '$uid'");
                        } else {
                            if ($DEBUG)
                                echo "[Processing User] uid=$uid cseq=$cseq cdel=$cdel email=$email<br>";
                            $result3 = mysql_query("select id,cron from $ftable where list = '$list' and seq = '$cseq' order by id");
                            if (@mysql_num_rows($result3) == 0) {
                                if ($DEBUG)
                                    echo " Warning: User set to followup $cseq, which does not exist!  Detecting next followup (and sending if delay=0)<br>";
                                // msg not found, check if higher ones exist.  if they do, get new delay and skip if over 0.
                                $result4 = mysql_query("select seq,del,cron from $ftable where list = '$list' and seq > '$cseq' order by seq limit 1");
                                if (@mysql_num_rows($result4) > 0) {
                                    list($newseq, $newdel, $newcron) = mysql_fetch_row($result4);
                                } else {
                                    $newseq = $lastf;
                                    $newdel = '0';
                                    $noadvance = 1;
                                }

                                if ($DEBUG)
                                    echo " Next Followup = Seq: $newseq Delay: $newdel Cron: $newcron - Updating user<br>";
                                mysql_query("update $utable set cseq = '$newseq', cdel = '$newdel' where id = '$uid'");
                                // use the new followup data and continue processing in case it should be sent
                                $cseq = $newseq;
                                $cdel = $newdel;
                                $cron = $newcron;
                                if ($cdel == 0) {
                                    $result5 = mysql_query("select id,cron from $ftable where list = '$list' and seq = '$cseq' order by id;");
                                    if (@mysql_num_rows($result5) > 0) {
                                        while (list($mid, $cron) = mysql_fetch_row($result5)) {
                                            if ($cron) {
                                                $cr->calcLastRan("* * " . $cron);
                                                $due = date("Ymd", $cr->getLastRanUnix() - 1);
                                                if ($now == $due) {
                                                    $sendids[] = $mid;
                                                    if ($DEBUG)
                                                        echo " Delay is 0 and cron is due on the new message - sending!<br>";
                                                }
                                            } else {
                                                $sendids[] = $mid;
                                                if ($DEBUG)
                                                    echo "Delay is 0 on the new message - sending!<br>";
                                            }
                                        }
                                    }
                                }
                            } else {
                                // found users' followup
                                while (list($mid, $cron) = @mysql_fetch_row($result3)) {
                                    // check cron if delay = 0
                                    if ($cdel == 0) {
                                        if ($cron) {
                                            $cr->calcLastRan("* * " . $cron);
                                            $due = date("Ymd", $cr->getLastRanUnix() - 1);
                                            if ($now == $due) {
                                                $sendids[] = $mid;
                                                if ($DEBUG)
                                                    echo " Delay is 0 and cron is due - sending!<br>";
                                            }
                                        } else {
                                            $sendids[] = $mid;
                                            if ($DEBUG)
                                                echo " Delay is 0 - sending!<br>";
                                        }
                                    }
                                }
                            }
                            if (count($sendids) > 0) {
                                if ($DEBUG)
                                    echo " *** There are " . count($sendids) . " to send! ***<br>";
                                // queue messages
                                while (list($k, $mid) = each($sendids)) {
                                    if ($DEBUG)
                                        echo " *** uid=$uid mid=$mid";
                                    $stats[$list]['sentfups']++;
                                    $fupz++;
                                    $sendq++;
                                    if ($DEBUG)
                                        echo "insert into $otable values('" . calc32() . "','$batid','2','2','$uid','$mid','fup');<br>";
                                    mysql_query("insert into $otable values('" . calc32() . "','$batid','2','2','$uid','$mid','fup');");
                                    // get next followup & delay & update user
                                    list($newseq, $newdel, $newcron) = @mysql_fetch_row(@mysql_query("select seq,del,cron from $ftable where list = '$list' and seq > '$cseq' order by seq limit 1"));
                                    if (!$newseq) {
                                        $newseq = $cseq + 1;
                                        $newdel = '0';
                                    }
                                    if ($DEBUG)
                                        echo " *** Next Followup = uid $uid Seq $newseq Del $newdel Cron $newcron<br>";
                                    mysql_query("update $utable set cseq = '$newseq', cdel = '$newdel' where id = '$uid'");
                                }
                            } else {
                                if ($DEBUG)
                                    echo " *** NO message to send, ";
                                // dont send msg, simply decrease cdel
                                if ($cseq < $lastf && $cdel > 0) {
                                    $newdel = $cdel - 1;
                                    if ($DEBUG)
                                        echo "new delay = $newdel<br>";
                                    mysql_query("update $utable set cdel = '$newdel' where id = '$uid'");
                                } else {
                                    // do nothing
                                    if ($DEBUG)
                                        echo " do nothing...<br>";
                                }
                            }
                        }
                    } // user while
                } else {
                    // else no followups
                    if ($DEBUG)
                        echo "No followups for this list [$list]<br>";
                }
                // fups exist?
                // end
            } // list while
            if ($fupz >= 1) {
                if ($outp) {
                    echo "$fupz followups queued.<br>";
                    flush();
                }
            } else {
                if ($outp) {
                    echo "No followups to send.<br>";
                    flush();
                }
            }
        } // !nofups (noindent)
        // confirmation reminder emails
        if (!$noremind) {
            $reminders = 0;
            // select all lists with reminder enabled
            $rows = mysql_query("select listnum,remopts,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where remact = '1';");
            if (@mysql_num_rows($rows) > 0) {
                while (list($list, $remopts) = mysql_fetch_row($rows)) {
                    // get settings
                    list($every, $max) = explode(';', $remopts);
                    $dates = array();
                    $m = date("m");
                    $d = date("d");
                    $y = date("Y");
                    $xcmd = '';
                    for ($i = 1; $i <= $max; $i++) {
                        if ($i > 1)
                            $xcmd .= ' or ';
                        $xcmd .= "dateadd = '" . date("Y-m-d", mktime(0, 0, 0, $m, $d - $i * $every, $y)) . "'";
                    }
                    $cmd = "select id from $utable where list = '$list' and cnf = '0' and ($xcmd);";
                    $urows = mysql_query($cmd);
                    if (@mysql_num_rows($urows) > 0) {
                        while (list($uid) = mysql_fetch_row($urows)) {
                            $xid = calc32();
                            $cmd = "insert into $otable(id,bat,battype,mtype,uid,mid) values('$xid','$batid','6','6','$uid','');";
                            mysql_query($cmd) or die(mysql_error());
                            $sendq++;
                            $reminders++;
                        }
                    }
                }
            }
            if ($reminders) {
                if ($reminders == 1)
                    $s = ''; else
                    $s = 's'; echo "$reminders confirmation reminder$s to send.<br>";
            }
            else
                echo "No confirmation reminders to send.<br>";
        }
        echo "<br>";
        // dailymail report
        $total_num_users = 0;
        $total_num_new_users = 0;
        mysql_data_seek($lrows, 0);
        while (list($list, $title,$addopts, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = mysql_fetch_row($lrows)) {
            $ucmd = "select id,dateadd from $utable where cnf = '1' and list = '$list'";
            //echo 'checking list'.$list.'<br>';debug
            //check if list is remote
            if ($remote) {
                if(!$dbh){
                    try {
                        $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                        $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                    } catch (PDOException $e) {
                        die('dailymail-3-' . $e->getMessage());
                    }
                }
                $dbh_query = $dbh->query($ucmd);
                $nusers = $dbh_query->rowCount();
                $users = $dbh_query->fetchAll();
            } else {
                $ucount = mysql_query($ucmd);
                $nusers = @mysql_num_rows($ucount);
                //export to array for use later
                $users = array();
                $index = 0;
                while ($user_row = mysql_fetch_row($ucount)) {
                    $users[] = $user_row;
                }
            }
            if (!$nusers)
                $nusers = '0';
            $stats[$list]['users'] = $nusers;
            $total_num_users += $nusers;
            //count with the results obtained, no need to requery from db
            $num_new_users = 0;
            while (list(, $user) = each($users)) {
                //if($remote) echo 'This is a remote list<br>';
                list($id,$dateadd) = $user;
                if ($dateadd> date('Y-m-d H:i:s',mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) &&
                        $dateadd <= date('Y-m-d H:i:s',mktime(0, 0, 0, date("m"), date("d"), date("Y")))){
                    $num_new_users++;
                    //echo 'user '.$id.' was added on '.$dateadd.'<br>';//debug
                }
            }
            //echo 'num_new_users = '.$num_new_users;//debug
            $allnew = $num_new_users;
            $total_num_new_users += $allnew;
            
            // count new users
            /* $cmd = "select id from $utable where list = '$list' and cnf = '1' and dateadd = '" . date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y"))) . "'";
              $anrows = mysql_query($cmd);
              $allnew = @mysql_num_rows($anrows); */
            $stats[$list]['newusers'] = $allnew;
            $allnew = '';

            // set up report.
            $report .= "List $list: $title\r\n" . $stats[$list]['users'] . " total users (" . $stats[$list]['newusers'] . " new), \r\n" . $stats[$list]['sentfups'] . " followups sent\r\n";
            if ($stats[$list]['scheduled'])
                $report .= $stats[$list]['scheduled'];
            if ($stats[$list]['schedfups'])
                $report .= $stats[$list]['schedfups'];
            $report .= "\n";
            // echo "LIST $list USERS = $stats[$list]['users'][0]<br>";
        } // list while
        @mysql_free_result($lrows);

        // count new users
        /* $cmd = "select id from $utable where cnf = '1' and dateadd = '" . date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y"))) . "'";
          $anrows = mysql_query($cmd);
          $allnew = @mysql_num_rows($anrows); */
        $allnew = $total_num_new_users;
        $stats[0]['newusers'] = $allnew;
        $allnew = '';
        //@mysql_free_result($anrows);
        // count all users
        /* $urows = mysql_query("select id from $utable where cnf = '1'");
          $numusers = @mysql_num_rows($urows); */
        $numusers = $total_num_users;
        $stats[0]['users'] = $numusers;
        $numusers = '';
        //@mysql_free_result($urows);
        // put beginning of report on..
        if ($DEBUG)
            echo "REPORT=$report<br><br>";
        $report = "Dailymail Report for " . $report_time . "\r\nTotals: $numlists lists, \r\n" . $stats[0]['users'] . " users (" . $stats[0]['newusers'] . " new), \r\n$fupz followups sent\n" . $stats[0]['schedfups'] . $stats[0]['scheduled'] . "\r\n" . $report;

        // send mail!
        if ($outp)
            $disp = 1; else
            $disp = '';
        if ($sendq > 0) {
            //  echo "sending mail<br>";
            qcheckin($batid, 1, $report);
            $sentok = domail('', 'daym', $disp, $batid); 
            if ($sentok) {
                qfinish($batid);
            }
        }

        // remove old bouncing / admin messages.  - old
        @mysql_query("delete from $stable where kind = 'b'");
        @mysql_query("delete from $stable where kind = 'a'");

        // optimize tables
        optimizeall();

        // if($outp) echo "MySQL tables optimized.<br>";
        // dailymail done, check for daily-xtra.php
        if (file_exists('./daily-xtra.php')) {
            if ($outp)
                echo "Running daily-xtra.php..<br>";
            include('./daily-xtra.php');
            mysql_select_db($sqldb);
        }

        // notify admin if no email is sent.
        if ($sendq == 0) {
            // delete from qp
            mysql_query("delete from $ptable where batid = '$batid';");
            if ($DEBUG)
                echo "sending report from dailymail<br>";
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
                $subj = "Dailymail Report for " . date("l, F jS");
                $crlf = "\r\n";
                $header = "From: \"$sendn\" <$sende>$crlf";
                $header .= "Return-Path: $errmail$crlf";
                $header .= "MIME-Version: 1.0$crlf";
                $header .="X-Mailer: ListMail v$ver$crlf";
                $header .="X-LM-Flags: 5.x.x";
                $report = $lmpath . "\r\n\n" . $report;
                /*if ($phpmailf) mail($admmail, $subj, $report, $header, "-f$errmail"); 
                else mail($admmail, $subj, $report, $header);*/
                //Don't send mail, put it on the webserver
                $file = './attach/dailymail_'.date('Ymd').'.txt';
                $content = $admmail."\r\n".$subj."\r\n".$report."\r\n".$header."\r\n";
                file_put_contents($file, $content."\r\n",FILE_APPEND);
                //$cmd = "insert into $ttable values('','".addslashes($txtsubj)."','".addslashes($txtcont)."','".addslashes($txthtcont)."','".addslashes($txtfatt)."','".date("Y-m-d H:i:s")."','1','$numsent','".addslashes($tolist)."')";
                //$xid = calc32();
                //$cmd = "insert into $otable(id,bat,battype,mtype,uid,lid,mid) values('$xid','$batid','1','1','$uid','$list','$msgid');";    
            }
        }
        if (!$outp)
            echo "Dailymail executed " . date("Y-m-d h:ma");

        // show button to go back
        if ($outp)
            echo "<form method=post action=./dailymail.php><input type=hidden name=list value=$list><input type=hidden name=warn value=yes><input class=button type=submit value=\"Back to ListMail\"></form>";
    } // !warn
} // pw check
// EOF
?>
