<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$cmd = "select welsubj,welcom,welcht,welcf from $ltable where listnum = '$xid'";
$msgrow = @mysql_query($cmd) or die('admin-14-' . mysql_error());
if ($sqldebug)
    echo "CMD=<b>$cmd</b><br>";
list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);

   // read from lm_sendq and process..
    $cmd = "select id,mtype,uid,mid,xtra from $otable where bat = '$batch'";
    $mrows = @mysql_query($cmd) or die('admin-16-' . mysql_error());

    // display send box
    $nmails = @mysql_num_rows($mrows);
    $skipped = 0;
    $txsize = strlen($nmails);
    $timeleft = 0;
    
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
        while (list($oid, $mtype, $uid, $msgid, $xtra) = mysql_fetch_row($mrows)) {
            if ($first) {
                $lastmid = $msgid;
                $lastmt = $mtype;
            }
            $sqlid++;
            $skiptonext = '';
            $nouser = '';
            $error = '';

            if ($mtype <> '5') {
                // get user send vars
                $urow = mysql_query("select uid,list,fname,lname,email,htmail,bounces from $utable where id = '$uid'") or die('admin-17-' . mysql_error());
                if (@mysql_num_rows($urow) == 0) {
                    $nouser = 1;
                } else {
                    $nouser = '';
                    list($unid, $mlist, $fname, $lname, $email, $htmail, $bounces) = mysql_fetch_row($urow);
                    if (in_array(strtolower(substr($email, strpos($email, '@') + 1)), $otextonly))
                        $textonly = 1; else
                        $textonly = '';
                }
            } else
                $nouser = '';

            if (!$nouser) {
                // get message
                if ($mtype == '1' && (!$solo || $xid == 'resume') && ($lastmt <> $mtype || $lastmid <> $msgid || $first)) {
                    $cmd = "select subject,content,htcontent,fattach from $ttable where id = '$msgid'";
                    $msgrow = @mysql_query($cmd) or die('admin-18-' . mysql_error());
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }
                if ($mtype == '2') {
                    // followup
                    $cmd = "select subject,content,htcontent,fattach from $ftable where id = '$msgid'";
                    $msgrow = @mysql_query($cmd) or die('admin-19-' . mysql_error());
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '3' && (!$welc || $xid == 'resume')) {
                    // welcome message
                    $cmd = "select welsubj,welcom,welcht,welcf from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-20-' . mysql_error());
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '4' && (!$conf || $xid == 'resume')) {
                    // confirm message
                    $cmd = "select cnfsubj,cnfmesg,cnfht,cnff from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-21-' . mysql_error());
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }
                if ($mtype == '6' && (!$remi || $xid == 'resume')) {
                    // remind message
                    $cmd = "select remsubj,remmsg,remht,remf from $ltable where listnum = '$mlist'";
                    $msgrow = @mysql_query($cmd) or die('admin-21-' . mysql_error());
                    list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                }

                if ($mtype == '5') {
                    $cmd = "select list from $utable where id = '$uid'";
                    $urow = @mysql_query($cmd) or die('admin-22-' . mysql_error());
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
                        list($subj, $msg, $htmsg, $fatt) = @mysql_fetch_row($msgrow);
                        $cmd = "delete from $stable where id = '$msgid'";
                        @mysql_query($cmd) or die('admin-24-' . mysql_error());
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
                    $lrow = @mysql_query($cmd) or die('admin-25-' . mysql_error());
                    list($sendn, $sende) = @mysql_fetch_row($lrow);
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
                        list($xsubj, $xmsg, $xhtmsg) = processmsg($uid, $subj, $msg, $htmsg, '0', $mtype, $msgid);
                        if ($timedebug)
                            $time = mtime() - $stime;
                    } else {
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
                    $dcharset = "iso-8859-1";
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

                    if (($htmsg && $htmail && !$textonly) || $fatt) {
                        $header .= $crlf . "This is a multipart message in MIME format.";
                        if ($smtpsend)
                            $header .= $crlf . $crlf;
                    } else
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
?>
