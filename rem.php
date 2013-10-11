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

if($u){
 $x = ''; $email = '';
 if(strpos($u,',')>0){
  list($c,$u)=explode(',',$u);
 } else {
  $u = addslashes($u);
  $c = addslashes($c);
 }
 $cmd = "uid = '$u' and (cnf = '1' OR cnf = '0')";
}
if($x){
 if(strpos($x,',')>0){
  list($c,$list,$email)=explode(',',$x);
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
if($r){
 if(strpos($r,';')>0 && !$email){
  list($r,$ru)=explode(';',$r);
  if(strpos($ru,',')>0)list($ru)=explode(',',$ru);
  list($email)=mysql_fetch_row(mysql_query("select email from $utable where uid = '".addslashes($ru)."';"));
 } elseif(!$email){
  exit('error, no email');
 }
 $cmd = '(';
 $lists = explode(',',$r);
 while(list($k,$v)=each($lists)){
  if($k>0) $cmd .= ' or ';
  $cmd .= "list = '$v'";
 }
 reset($lists);
 $email = addslashes($email);
 $cmd .= ") and email like '$email' and (cnf = '1' OR cnf = '0');";
}

if(!$u && !$x && !$r){
 $email = addslashes($email); 
 $list = addslashes($list);
 $cmd = "list = '$list' and email like '$email' and (cnf = '1' OR cnf = '0')";
}
if($lists && !$list) $list = addslashes($lists[0]);
if(($c && !is_numeric($c)) || ($list && !is_numeric($list))) $error = 'data_bad';

// echo "email=$email list=$list<br>";
if(!$u && !$email) $error = 'email_req';
if($email && !valid_email(strtolower($email))) $error = 'email_bad';

// error check
if(!$error){
 $ucmd = "select id,list,email from $utable where $cmd";
 //Check if list is remote
 $lcmd = "select title,remote,remotedb,remoteuser,remotepwd,remotehost from $ltable where listnum = '$list'";
 $lrow = @mysql_query($lcmd, $link) or die('admin-6-' . mysql_error());
 list($ltitle,$remote,$remotedb,$remoteuser,$remotepwd,$remotehost) = @mysql_fetch_row($lrow);
 if($remote){
    try {
        $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
        $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
        $dbh_query = $dbh->query($ucmd);
    } catch (PDOException $e) {
        die('rem-1-' . $e->getMessage());
    }
    if($dbh_query->rowCount()>0){
        list($cid,$list,$email) = $dbh_query->fetch();
    }else{
        $error = 'rem_nf';
    }
    $dbh = null;//close the connection
 }else{
    $rows = mysql_query($ucmd) or die(mysql_error());
    //echo $ucmd.'<br>';//debug
    if(@mysql_num_rows($rows)>0){
    list($cid,$list,$email)=@mysql_fetch_row($rows);
    } else { $error = 'rem_nf'; } 
    //echo 'email is '.$email.'<br>';//debug
    //echo 'list is '.$list.'<br>';//debug
 }
}
// echo "ORIGvars: \$c = $c, \$list = $list, \$email = $email, \$u = $u<br>";

// echo "FINALvars: \$cid = $cid, \$list = $list, \$email = $email<br>";
$chid = listopts('chtmlid',$list);
$eid = listopts('errorid',$list);
if(!$eid) $eid = '1';
if(!$chid) $chid = '1';

if(!$error){
 if($c){
  // echo "checking \$c: ";
  $drow = mysql_query("select data3,data4 from $dtable where id = '".addslashes($c)."';");
  if(@mysql_num_rows($drow)>0){
   list($data3,$data4)=mysql_fetch_row($drow);
   if($data3=='1') $delete = 1;
   if($data4){
    // multi list
    if(strpos(' '.$data4,'*')>0){
     // put all lists in array
     $rows = mysql_query("select listnum from $ltable where 1 order by listnum");
     while(list($ln)=mysql_fetch_row($rows)){
      if($ln<>$list) $lists[] = $ln;
     }
    } else {
     // get lists from string
     $lists = explode(',',$data4);
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
 while(list($k,$l)=each($lists)){
  if($k>0) $cmd .= ' or ';
  $cmd .= "list = '$l'";
 }
 $cmd .= ')'; 
 if($remote){
    try {
        $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
        $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
        $dbh_query = $dbh->query($cmd);
    } catch (PDOException $e) {
        die('rem-2-' . $e->getMessage());
    }
    if($dbh_query->rowCount()>0){
        $rows = $dbh_query->fetchAll();
    }else{
        $error = 'rem_nf';
    }
    $dbh = null;//close the connection
 }else{
     $temprows = mysql_query($cmd);
     //echo $cmd;//debug
     $rows = array();
     while($temprow = mysql_fetch_array($temprows)){
         $rows[] = $temprow;//echo $temprow['id'].'<br>';
     }
 }
 
 //if(@mysql_num_rows($rows)>0){
 //echo 'Count: '.count($rows);//debug
 if(count($rows)>0){
  $delcmd = array();
  $x = 0;
  if($r) $r .= ';';
  //while(list($id,$uniqid,$ln,$fname)=mysql_fetch_row($rows)){
  foreach($rows as $row){
   list($id,$uniqid,$ln,$fname) = $row;   //echo 'id = '.$id.'<br>';//debug
   // loop results, deleting IF $delete, if not, simply display confirm page
   if($delete){//echo 'deleting...<br>';
    // do it
    $narr = getnotifs($ln);
    if($narr[1]=='1') sendnotif('remove',$id);
    $lrow = mysql_query("select listopts from $ltable where listnum = '$ln'");
    list($listopts)=mysql_fetch_row($lrow);
    $listopts = explode(';',$listopts);
    if($listopts[4]=='1') remlists($email,$ln,4);
    if($listopts[5]=='1') addlists($email,$ln,5);
    //echo "User ID $id deleted. (Uncomment)<br>";//debug
    if($listopts[1]==1){
      //echo "updating $id to Removed..<br>";//debug
      $cmd = "update $utable set cnf = '2' where id = '$id';";
      if($remote){
        try {
            $pdo_db = 'mysql:dbname='.$remotedb.';host='.$remotehost;
            $dbh = new PDO($pdo_db, $remoteuser, $remotepwd);
            $dbh_query = $dbh->query($cmd);
        } catch (PDOException $e) {
            die('rem-2-' . $e->getMessage());
        }
        $dbh = null;//close the connection
      }else{
          mysql_query($cmd);
      }
     //mysql_query("update $utable set cnf = '2' where id = '$id';");
    } else $delcmd[] = "delete from $utable where id = '$id';";
   }
   // if multi list rem, add uid 
   if($r){
    if($x>0) $r .= ',';
    $r .= $uniqid;
   }
   $x++;
  }
 } else {
  $error = 'rem_nf';
 }
}
// echo "\$error: $error (chid=$chid eid=$eid)<br>";
if($error){
 // echo "\$error: $error (chid=$chid eid=$eid)<br>";
 $errmsg = emsg($error,$eid) . '<br>';
 $data = chtml('remerror',$chid);
 $data = str_replace('!data',$errmsg,$data);
 if(strpos(' '.$data,'URL:')=='1') header('Location: '.str_replace('URL:','',$data)); else echo $data;  exit; 
} else {
 // echo "no error, show page";
 if($delete){
  // remove xtra
  if(file_exists('./remove-xtra.php')){
   include('./remove-xtra.php');
   mysql_select_db($sqldb,$link);
  }
  // success!
  $data = chtml('remsuccess',$chid);
  list($data) = processmsg($cid,$data,'','','1');
 } else {
  // confirm
  $data = chtml('remove',$chid);
  //list($data) = processmsg($cid,$data,'','','0','',$c,$r);
  //processmsg($uid, $subj, $msg = '', $htmsg = '', $mhtml = '0', $mtyp = '', $mid = '', $r = '')
  list($data) = processmsg2($id, $uniqid,$ln, $fname, '', $email,'','','', '', '', '', '', '', '', '', '', '', $data, '', '', '0', '', $c,$r); 
      //$id,$uniqid,$ln,$fname
    }
 }
 if($delcmd) while(list($k,$v)=each($delcmd)) mysql_query($v);
 if(strpos(' '.$data,'URL:')=='1') header('Location: '.str_replace('URL:','',$data)); else echo $data; exit;

?>
