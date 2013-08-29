<?php
/* ListMail (c) 2002 - 2006 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

ini_set("max_execution_time","0");

echo " ";

// set vars
$REQ_METHOD = $_SERVER['REQUEST_METHOD'];
if(!$REQ_METHOD) $REQ_METHOD=$REQUEST_METHOD;
if($REQ_METHOD=='GET'){ if(!$em) $em = $_GET['em']; } else exit;

$em = urldecode($em);

$schar = 0;
$cchar = 0;
$cline = 0;
$done = '';

while(!$done){
 if($cchar == strlen($em)) $done = 1;
 if($em[$cchar]<>"\n" && !$done){
  $cchar++;
 } else {
  $line = substr($em,$schar,$cchar-$schar);
  if($line) $lines[$cline] = $line;
  $schar=$cchar+1;
  $cline++;
  $cchar++;
 }
}

// find lines that start with To:
reset($lines);
while(list($key,$val)=each($lines)){
 // testbounce
 if(strpos(strtoupper(substr($val,0,25)),'TESTBOUNCE') !== false){
  // mail('dean@listmailpro.com','testbounce!',"test success");
  sendnotif('test_bounce','admin');
  exit();
 }
 // LMP recipient returned
 if(strpos($val,'X-LMP-Recipient:') !== false){
  $target = str_replace("\n",'',substr($val,17));
  if($target <> $errmail) bounce($target,$em);
  exit();
 }
 if(strpos(strtoupper(substr($val,0,5)),strtoupper('To:')) !== false){
  $val = str_replace('<','',$val);
  $val = str_replace('>','',$val);
  $tpos = strpos(strtoupper(substr($val,0,5)),strtoupper('To:'));
  $qpos = strpos($val,'" ');
  if($qpos !== false){
   $line = substr($val,$qpos+2,strlen($val)-$qpos-2);
  } else $line = substr($val,$tpos+4,strlen($val)-$tpos-4);
  
  $line = str_replace("\r",'',$line);
  $line = str_replace("\n",'',$line);
  $line = str_replace(' ','',$line);
  $target = $line;
 }
}
if($target && $target <> $errmail) bounce($target,$em);
// EOF
?>
