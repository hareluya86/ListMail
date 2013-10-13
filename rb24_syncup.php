<?php
include("./config.php");
include("./admin.php");
$csvfile = './list/RB24 list 20131010.csv';
$list = 1000;

$csv = fopen($csvfile, 'r');
$file_index = 1;
$record_count = 0;
$batch_size = 1000;
$sqlcmd = '';

while (list($email) = fgetcsv($csv)) {
        // echo "list=$list<br>lists=$lists<br>cgi=$cgi<br>";
    list($remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row(mysql_query("select remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list'"));
    $dbh = '';
    if ($remote) {
        if (!$dbh) {
            try {
                $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                exit();
            }
        }
    }
    if ($list && !$lists && !$cgi) {
        $lists = array();
        $lists[0] = $list;
    }
    if ($cgi)
        $lists = explode(',', $list);

    sort($lists);

    $liststr = '';
    while (list($k, $v) = each($lists)) {
        if ($k > 0)
            $liststr .= ',';
        $liststr .= $v;
    }
    reset($lists);

    if ($submode == 'unsub') {
        /* if($lists){
        } elseif($list){ */
        // get uid
        Header("Location: rem.php?r=" . $liststr . "&email=$email");
        exit;
        /* } else {
        exit('no list defined');
        } */
    }

    // initial setup
    // get return-path from config

    $today = date("Y-m-d H:i:s");

    $ipaddr = getip();
    if ($ipaddr <> 'Unknown')
        $iphost = @gethostbyaddr($ipaddr);

    $refurl = $_SERVER["HTTP_REFERER"];
    if (!$refurl)
        $refurl = $HTTP_REFERER;
    if (!$refurl)
        $refurl = "Unknown";

    if ($cgi)
        $ipaddr = "Unknown";
    if ($cgi)
        $refurl = 'Email';

    if (!$htmail || strtoupper($htmail) == 'HTML')
        $html = '1';
    if (strtoupper($htmail) == 'TEXT')
        $html = '0';

    $dupes = array();
    $oklists = array();
    $badlists = array();
    $bademails = array();
    $uids = array();
    $required = array();
    $closed = array();
    $rem_lists = array();
    $add_lists = array();

    $email = trim($email);

    // check closed lists & sanitize
    while (list($key, $list) = each($lists)) {
        if (!is_numeric($list))
            exit('list not numeric');
        list($list) = @mysql_fetch_row(mysql_query("select listnum from $ltable where listnum = '$list';"));
        if (!$list)
            exit('list not found');
        $listopts = getlistopts($list);
        if ($listopts[0] == 1) {
            $closed[$list] = 1;
            if (!in_array($list, $badlists))
                $badlists[] = $list;
        }
    }
    reset($lists);

    // check required fields
    while (list($key, $list) = each($lists)) {
        if (!$cgi) {
            $reqerr = '';
            $requx = '';
            $cmd = "select req1,req2,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10 from $ltable where listnum = '$list' limit 1";
            $listrow = mysql_query($cmd);
            list($req1, $req2, $u1, $u2, $u3, $u4, $u5, $u6, $u7, $u8, $u9, $u10) = mysql_fetch_row($listrow);
            for ($i = 1; $i <= 10; $i++) {
                list($null, $reqd, $errm) = explode('/*LMP*/', ${"u" . $i});
                if ($reqd == '1' && !${"user" . $i}) {
                    $requx = 1;
                    $reqerr .= $errm . '<br>';
                }
            }
            if ($req1 == 1 && !$fname) {
                $reqfn = 1;
            } else
                $reqfn = false;
            if ($req2 == 1 && !$lname) {
                $reqln = 1;
            } else
                $reqln = false;
            if (!$email)
                $reqem = 1; else
                $reqem = false;
            if ($reqem || $reqfn || $reqln || $requx) {
                $required[$list] = array($reqfn, $reqln, $reqem, $requx, $reqerr);
                if (!in_array($list, $badlists))
                    $badlists[] = $list;
            }
        }
    }
    reset($lists);

    // check email format
    while (list($key, $list) = each($lists)) {
        // check email structure
        if (!valid_email($email)) {
            $bademails[$list] = 1;
            if (!in_array($list, $badlists))
                $badlists[] = $list;
        }
    }
    reset($lists);

    while (list($key, $list) = each($lists)) {
        if (!$allowdupes && !$allow_dupes) {//default is don't allow duplicates
            $cmd = "select id,uid,cnf from $utable where list = '$list' and email like '" . addslashes($email) . "';";
            //echo 'check if allow duplicates'.'<br>';//debug
            if ($remote) {
                $pdo_query = $dbh->query($cmd);
                $pdo_num_results = $pdo_query->rowCount();
                if ($pdo_num_results > 0) {
                    //header("Location: http://www.airnavsystems.com/downhere.html"); //hard-coded stuff...
                    //die();
                    list($xid, $xuid, $xcnf) = $pdo_query->fetch();
                    if ($xcnf <> '1') {
                        // overwrite
                        $uids[$list] = $xuid;
                    } else {
                        if ($overwrite_dupes || $overwritedupes) {
                            // overwrite
                            $uids[$list] = $xuid;
                        } else {
                            $dupes[$list] = 1;
                            if (!in_array($list, $badlists))
                                $badlists[] = $list;
                        }
                    }
                }
            }else {
                $result = mysql_query($cmd);
                if (mysql_num_rows($result) > 0) {
                    list($xid, $xuid, $xcnf) = mysql_fetch_row($result);
                    if ($xcnf <> '1') {
                        // overwrite
                        $uids[$list] = $xuid;
                    } else {
                        if ($overwrite_dupes || $overwritedupes) {
                            // overwrite
                            $uids[$list] = $xuid;
                        } else {
                            $dupes[$list] = 1;
                            if (!in_array($list, $badlists))
                                $badlists[] = $list;
                        }
                    }
                }
            }//echo 'exiting duplicate check..'.'<br>';//debug
        }else {
            $dupes[$list] = false;
        }
    }
    reset($lists);
    // check dupes
    /* Original code
    * 
    *
    while(list($key,$list)=each($lists)){
    if(!$allowdupes && !$allow_dupes){
    // check for duplicates
    echo 'Allow dupes = '.$allowdupes;
    $cmd = "select id,uid,cnf from $utable where list = '$list' and email like '".addslashes($email)."';";
    if($remote){
    try {
    $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
    $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
    echo 'Connected with PDO!';
    } catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    }
    $dbh->query("use $remotedb") or die($dbh->errorInfo());
    $result = $dbh->exec($cmd);
    }
    else{
    $result = mysql_query($cmd);
    }

    if(mysql_num_rows($result)>0){
    list($xid,$xuid,$xcnf)=mysql_fetch_row($result);
    if($xcnf<>'1'){
    // overwrite
    $uids[$list] = $xuid;
    } else {
    if($overwrite_dupes || $overwritedupes){
    // overwrite
    $uids[$list] = $xuid;
    } else {
    $dupes[$list] = 1;
    if(!in_array($list,$badlists)) $badlists[] = $list;
    }
    }
    }
    } else {
    $dupes[$list] = false;
    }
    }
    reset($lists);
    * 
    */

    // do oklists
    while (list($key, $list) = each($lists)) {
        //echo 'entering oklists..'.'<br>';//debug
        if ($closed[$list])
            $errorout = 1;
        if ($required[$list])
            $errorout = 1;
        if (!$bademails[$list] && !$required[$list] && !$dupes[$list] && !$closed[$list]) {
            $oklists[] = $list;
        }
    }
    reset($lists);

    sort($badlists);
    // error out
    if (count($oklists) == 0 || $errorout) {
        if (!$cgi) {
            // get custom subscribe error page options
            if ($chtmlid && is_numeric($chtmlid)) {
                $chid = $chtmlid; //echo 'chtmlid: '.$chtmlid.'<br>';//debug
            } else {
                // get first badlist HTML
                $chid = listopts('chtmlid', $badlists[0]);
                $eid = listopts('errorid', $badlists[0]);
            }
            $data = chtml('suberror', $chid);
            // get errors for badlist
            list($rfn, $rln, $rem, $ux, $uerr) = $required[$badlists[0]];
            if ($closed[$badlists[0]])
                $msg .= 'You may not subscribe to a closed list!<br>';
            elseif ($dupes[$badlists[0]])
                $msg .= emsg('email_dupe', $eid) . '<br>';
            else {
                if ($rem)
                    $msg .= emsg('email_req', $eid) . '<br>';
                elseif ($bademails[$badlists[0]])
                    $msg .= emsg('email_bad', $eid) . '<br>';
                if ($rfn)
                    $msg .= emsg('fname_req', $eid) . '<br>';
                if ($rln)
                    $msg .= emsg('lname_req', $eid) . '<br>';
                if ($ux)
                    $msg .= $uerr;
            }
            $data = str_replace('!data', $msg, $data); //echo 'msg: '.$msg.'<br>';//debug
            //if(strpos(' '.$data,'URL:')=='1') header('Location: '.str_replace('URL:','',$data)); else echo $data;
            if (strpos(' ' . $data, 'URL:') == '1')
                header('Location: ' . str_replace('URL:', '', $data));
            if (strpos(' ' . $msg, 'URL:') == '1') {
                $msg = str_replace('<br>', '', $msg);
                header('Location: ' . str_replace('URL:', '', $msg));
            }
            else
                echo $data;
            exit;
        } else {
            exit();
        }
    }

    while (list($key, $list) = each($oklists)) {
        list($welcact, $cnfact) = @mysql_fetch_row(mysql_query("select welcact,cnfact from $ltable where listnum = '$list'"));
        //echo 'check list settings..'.'<br>';//debug
    //$u
        // allow customizing seq and delay on signup form
        if ($seq && is_numeric($seq) && $seq > 0 && $del && is_numeric($del) && $del >= 0) {
            $userseq = $seq;
            $userdel = $del;
        } else {
            // find seq & delay
            $cmd = "select del,seq from $ftable where list = '$list' order by seq limit 1";
            $result = mysql_query($cmd);
            if (mysql_num_rows($result) == '0') {
                if (!$seq)
                    $userseq = 1; else
                    $userseq = $seq;
                if (!$del)
                    $userdel = 0; else
                    $userdel = $del;
                $userdel = 0;
            } else {
                list($del2, $seq2) = @mysql_fetch_row($result);
                $thelst = $list;
                if (!$seq)
                    $userseq = $seq2; else
                    $userseq = $seq;
                if (!$del)
                    $userdel = $del2; else
                    $userdel = $del;
            }
            $seq = '';
            $del = '';
        }

        if ($cnfact == '1')
            $confirmed = '0'; else
            $confirmed = '1';
        // removed unneeded $cnf = $cquery;
    //echo 'confirm:'.$confirmed.'<br>';//debug
        if (!$uids[$list]) {
            $uniq = '';
            while (!$uniq) {
                $uid = unique_id(7);
                if ($remote) {
                    $dbh->query("use $remotedb") or die($dbh->errorInfo());
                    $dbhresult = $dbh->exec("select id from $utable where uid = '$uid'");
                    if ($dbhresult == 0)
                        $uniq = 1;
                    //echo 'Is list unique? '.$uniq.'<br>';//debug
                }
                else {
                    if (@mysql_num_rows(mysql_query("select id from $utable where uid = '$uid'", $link)) == 0)
                        $uniq = 1;
                }
            }
        } else
            $uid = $uids[$list];

        if (!$added) {
            $list = addslashes($list);
            $email = addslashes($email);
            $fname = addslashes($fname);
            $lname = addslashes($lname);
            $user1 = addslashes($user1);
            $user2 = addslashes($user2);
            $user3 = addslashes($user3);
            $user4 = addslashes($user4);
            $user5 = addslashes($user5);
            $user6 = addslashes($user6);
            $user7 = addslashes($user7);
            $user8 = addslashes($user8);
            $user9 = addslashes($user9);
            $user10 = addslashes($user10);
            $added = 1;
        }

        if ($uids[$list]) { //why?
            mysql_query("delete from $utable where uid = '" . $uids[$list] . "'") or die(mysql_error());
            // echo "deleted rows.. ".mysql_affected_rows()."..<br>";
        }

        $cmd = "INSERT INTO $utable VALUES (NULL,'$uid','$list','$fname','$lname','$email','$user1','$user2','$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10','$userseq','$userdel','$confirmed','$today','$ipaddr','$refurl','$html','0');";
        if ($remote) { //open connection to remote host
            if (!$dbh) {
                try {
                    $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                    $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                } catch (PDOException $e) {
                    echo 'Connection failed: ' . $e->getMessage();
                    exit();
                }
            }
            $dbh->query("use $remotedb") or die($dbh->errorInfo());
            $dbh->exec($cmd) or die($dbh->errorInfo());
            $userid = $dbh->lastInsertId();
            //$dbh = null; //Close the connection
        } else {
            mysql_query($cmd) or die("Database error while inserting.." . mysql_error());
            $userid = mysql_insert_id();
        }


        if ($key == 0)
            $chuserid = $userid;
        $listopts = getlistopts($list);
        if ($listopts[2] == '1')
            $rem_lists[] = $list;
        if ($listopts[3] == '1')
            $add_lists[] = $list;

        if ($cnfact == '1' || ($cnfact <> '1' && $welcact == '1')) {
            sendwelcome($userid, $list);
        }

        // admin notification (welcome only)
        if ($cnfact == '0') {
            $narr = getnotifs($list);
            if ($narr[0] == '1') {
                sendnotif('subscriber', $userid, $cgi);
            }
            // signup-xtra
            if (file_exists('./signup-xtra.php')) {
                include('./signup-xtra.php');
                mysql_select_db($sqldb, $link);
            }
        } else {
            // signup-confirm-xtra
            if (file_exists('./signup-confirm-xtra.php')) {
                include('./signup-confirm-xtra.php');
                mysql_select_db($sqldb, $link);
            }
        }
    } // end loop
    reset($oklists);

    // add/rem users from other lists
    // SOMEHOW STOP THIS WHEN USER IS SUBSCRIBING HERE TO TARGET LISTS
    if ($rem_lists) {
        while (list($k, $v) = each($rem_lists))
            remlists($email, $v, 2, $lists);
    }
    if ($add_lists) {
        while (list($k, $v) = each($add_lists))
            addlists($email, $v, 3, $lists);
    }

    // display custom success page (or confirm page)

    if (!$cgi) {//echo "Display custom success page.. <br>";
        if ($cnfact == '1')
            $confirmed = 'preconfirm'; else
            $confirmed = 'subscribe'; //echo $confirmed.'<br>';//debug
        if ($chtmlid)
            $chid = $chtmlid;
        else
            $chid = listopts('chtmlid', $oklists[0]);
        if ($chtmlid && is_numeric($chtmlid))
            $chid = $chtmlid;
        $eid = listopts('errorid', $oklists[0]);
        $data = chtml($confirmed, $chid);
        //echo "userid: ".$chuserid."<br>";//debug
        list($data) = processmsg($chuserid, $data, '', '', '0');
        /* list($data) = processmsg2($chuserid, $uid,$list, $fname, $lname, $email,$refurl,$ipaddr,
        $user1, $user2, $user3, $user4, $user5, $user6, $user7, $user8, $user9, $user10,
        $data, '', '', '0') ; */
        //$cmd = "INSERT INTO $utable VALUES (NULL,'$uid','$list','$fname','$lname','$email','$user1','$user2','$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10','$userseq','$userdel','$confirmed','$today','$ipaddr','$refurl','$html','0');";
        //if(!$data) echo 'data is empty';
        if ($key == 0)
            if (strpos(' ' . $data, 'URL:') == 1)
                header('Location: ' . str_replace('URL:', '', $data)); else
                echo $data;
        echo 'key ' . $key;
    }
}

// EOF
?>
