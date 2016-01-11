<?php

/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
  You may not modify or distribute the program without express permission from the author.
  Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

// set up variables
$delete = $dodel; // backwards compat for new varname. 03-11-2005
$error = '';
// reserve $u $c and $x for future custom custom fields

if ($u) {
    $x = '';
    $email = '';
    if (strpos($u, ',') > 0) {
        list($c, $u) = explode(',', $u);
    } else {
        $u = addslashes($u);
        $c = addslashes($c);
    }
    $cmd = "uid = '$u' and (cnf = '1' OR cnf = '0')";
}
if ($x) {
    if (strpos($x, ',') > 0) {
        list($c, $list, $email) = explode(',', $x);
        $c = addslashes($c);
        $list = addslashes($list);
        $email = addslashes($email);
    } else {
        $c = addslashes($x);
        $list = addslashes($l);
        $email = addslashes($e);
    }
    $cmd = "list = '$list' and email like '$email' and (cnf = '1' OR cnf = '0')"; //Safe?
    //$cmd = "list = '$list' and email = '$email' and (cnf = '1' OR cnf = '0')"; //exact match email
}
if ($r) {
    if (strpos($r, ';') > 0 && !$email) {
        list($r, $ru) = explode(';', $r);
        if (strpos($ru, ',') > 0)
            list($ru) = explode(',', $ru);
        list($email) = mysql_fetch_row(mysql_query("select email from $utable where uid = '" . addslashes($ru) . "';"));
    } elseif (!$email) {
        exit('error, no email');
    }
    $cmd = '(';
    $lists = explode(',', $r);
    while (list($k, $v) = each($lists)) {
        if ($k > 0)
            $cmd .= ' or ';
        $cmd .= "list = '$v'";
    }
    reset($lists);
    $email = addslashes($email);
    $cmd .= ") and email like '$email' and (cnf = '1' OR cnf = '0');";
}

if (!$u && !$x && !$r) {
    $email = addslashes($email);
    $list = addslashes($list);
    $cmd = "list = '$list' and email like '$email' and (cnf = '1' OR cnf = '0')";
}
if ($lists && !$list)
    $list = addslashes($lists[0]);
if (($c && !is_numeric($c)) || ($list && !is_numeric($list)))
    $error = 'data_bad';

// echo "email=$email list=$list<br>";
if (!$u && !$email)
    $error = 'email_req';
if ($email && !valid_email(strtolower($email)))
    $error = 'email_bad';

// error check
if (!$error) {
    $ucmd = "select id,list,email from $utable where $cmd";
    //Check if list is remote
    $lcmd = "select title,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list'";
    $lrow = @mysql_query($lcmd, $link) or die('admin-6-' . mysql_error());
    list($ltitle, $remote, $remotedb, $remoteuser, $remotepwd, $remotehost) = @mysql_fetch_row($lrow);
    if ($remote) {
        if (!$dbh) {
            try {
                $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                
            } catch (PDOException $e) {
                die('rem-1-' . $e->getMessage());
            }
        }
        $dbh_query = $dbh->query($ucmd);
        if ($dbh_query->rowCount() > 0) {
            list($cid, $list, $email) = $dbh_query->fetch();
        } else {
            $error = 'rem_nf';
        }
    } else {
        $rows = mysql_query($ucmd) or die(mysql_error());
        if (@mysql_num_rows($rows) > 0) {
            list($cid, $list, $email) = @mysql_fetch_row($rows);
        } else {
            $error = 'rem_nf';
        }
    }
}
// echo "ORIGvars: \$c = $c, \$list = $list, \$email = $email, \$u = $u<br>";
// echo "FINALvars: \$cid = $cid, \$list = $list, \$email = $email<br>";
$chid = listopts('chtmlid', $list);
$eid = listopts('errorid', $list);
if (!$eid)
    $eid = '1';
if (!$chid)
    $chid = '1';

if (!$error) {
    if ($c) {
        // echo "checking \$c: ";
        $drow = mysql_query("select data3,data4 from $dtable where id = '" . addslashes($c) . "';");
        if (@mysql_num_rows($drow) > 0) {
            list($data3, $data4) = mysql_fetch_row($drow);
            if ($data3 == '1')
                $delete = 1;
            if ($data4) {
                // multi list
                if (strpos(' ' . $data4, '*') > 0) {
                    // put all lists in array
                    $rows = mysql_query("select listnum from $ltable where 1 order by listnum");
                    while (list($ln) = mysql_fetch_row($rows)) {
                        if ($ln <> $list)
                            $lists[] = $ln;
                    }
                } else {
                    // get lists from string
                    $lists = explode(',', $data4);
                }
            }
        }
    } else {
        // !$c, single list only
        // no options: no one-click, no multi list
    }
    $lists[] = $list;
    // print_r($lists);
    // loop code, if dodel delete, if not, confirm.
    // provide ability to output listnames with !code in chtml?
    // create sql query
    $cmd = "select id,uid,list,fname from $utable where email like '$email' and (cnf = '1' OR cnf = '0') and (";
    while (list($k, $l) = each($lists)) {
        if ($k > 0)
            $cmd .= ' or ';
        $cmd .= "list = '$l'";
    }
    $cmd .= ')';
    if ($remote) {
        if(!$dbh){
            try {
                $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost; 
                $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
            } catch (PDOException $e) {
                die('rem-2-' . $e->getMessage());
            }
        }
        $dbh_query = $dbh->prepare($cmd);
        $dbh_query->execute();
        if ($dbh_query->rowCount() > 0) {
            $rows = $dbh_query->fetchAll();
        } else {
            $error = 'rem_nf';
        }
    } else {
        $temprows = mysql_query($cmd);
        $rows = array();
        while ($temprow = mysql_fetch_array($temprows)) {
            $rows[] = $temprow; //echo $temprow['id'].'<br>';
        }
    }

    $delcmd = array();
    $x = 0;
    if ($r)
        $r .= ';';
    
    foreach ($rows as $row) {   
        list($id,$uniqid,$ln,$fname) = $row;
        
        if ($delete) {
            $narr = getnotifs($ln);
            if ($narr[1] == '1')
                sendnotif('remove', $id);
            $lrow = mysql_query("select listopts from $ltable where listnum = '$ln'");
            list($listopts) = mysql_fetch_row($lrow);
            $listopts = explode(';', $listopts);
            if ($listopts[4] == '1')
                remlists($email, $ln, 4);
            if ($listopts[5] == '1')
                addlists($email, $ln, 5);
            if ($listopts[1] == 1) {
                $cmd = "update $utable set cnf = '2' where id = '$id';"; 
                if ($remote) {
                    if (!$dbh) {
                        try {
                            $pdo_db = 'mysql:dbname=' . $remotedb . ';host=' . $remotehost;
                            $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
                        } catch (PDOException $e) {
                            die('rem-2-' . $e->getMessage());
                        }
                    }
                    $dbh_query = $dbh->prepare($cmd);
                    $dbh_query->execute();
                } else {
                    mysql_query($cmd);
                }
            } else {
                $delcmd[] = "delete from $utable where id = '$id';";
            }
        }
    }
    // if multi list rem, add uid 
    if ($r) {
        if ($x > 0)
            $r .= ',';
        $r .= $uniqid;
    }
    $x++;
}

if ($error) {
    
    $errmsg = emsg($error, $eid) . '<br>';
    $data = chtml('remerror', $chid);
    $data = str_replace('!data', $errmsg, $data);
    if (strpos(' ' . $data, 'URL:') == '1')
        header('Location: ' . str_replace('URL:', '', $data));
    else
        echo $data;
    exit;
} else {
    // echo "no error, show page";
    if ($delete) {
        // remove xtra
        if (file_exists('./remove-xtra.php')) {
            include('./remove-xtra.php');
            mysql_select_db($sqldb, $link);
        }
        // success!
        $data = chtml('remsuccess', $chid);
        list($data) = processmsg($cid, $data, '', '', '1');
    } else {
        // confirm
        $data = chtml('remove', $chid);
        list($data) = processmsg2($id, $uniqid, $ln, $fname, '', $email, '', '', '', '', '', '', '', '', '', '', '', '', $data, '', '', '0', '', $c, $r);
        
    }
}
if ($delcmd)
    while (list($k, $v) = each($delcmd))
        mysql_query($v);
if (strpos(' ' . $data, 'URL:') == '1')
    header('Location: ' . str_replace('URL:', '', $data));
else
    echo $data;
exit;
?>
