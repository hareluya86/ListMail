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
 $cmd = "list = '$list' and email like '$email' and (cnf = '1' OR cnf = '0')";
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
 $cmd = "select id,list,email from $utable where $cmd";
 $rows = mysql_query($cmd) or die(mysql_error());
 if(@mysql_num_rows($rows)>0){
  list($cid,$list,$email)=@mysql_fetch_row($rows);
 } else { $error = 'rem_nf'; }
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
 $rows = mysql_query($cmd);
 if(@mysql_num_rows($rows)>0){
  $delcmd = array();
  $x = 0;
  if($r) $r .= ';';
  while(list($id,$uniqid,$ln,$fname)=mysql_fetch_row($rows)){
   // loop results, deleting IF $delete, if not, simply display confirm page
   if($delete){
    // do it
    $narr = getnotifs($ln);
    if($narr[1]=='1') sendnotif('remove',$id);
    $lrow = mysql_query("select listopts from $ltable where listnum = '$ln'");
    list($listopts)=mysql_fetch_row($lrow);
    $listopts = explode(';',$listopts);
    if($listopts[4]=='1') remlists($email,$ln,4);
    if($listopts[5]=='1') addlists($email,$ln,5);
    // echo "User ID $id deleted. (Uncomment)<br>";
    if($listopts[1]==1){
     // echo "updating $id to Removed..<br>";
     mysql_query("update $utable set cnf = '2' where id = '$id';");
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
  list($data) = processmsg($cid,$data,'','','0','',$c,$r);
 }
 if($delcmd) while(list($k,$v)=each($delcmd)) mysql_query($v);
 if(strpos(' '.$data,'URL:')=='1') header('Location: '.str_replace('URL:','',$data)); else echo $data;  exit;
}
?>
