<?php /* ListMailPRO Auto Backup Script
Backup and ZIP ListMailPRO data and upload to LMP server via HTTP POST or FTP
Security with a special 16 character password.
Max file size 100MB */

// automatically send installation ID and ListMail URL


// Zip the backup file (highly recommended)
$zipit = true;
// -- You do not need to modify anything below this line.

// Create backup and send to LM
include("./config.php");
include("./admin.php");
ini_set("max_execution_time","0");
list($lmp_id,$lmp_url,$daypw)=mysql_fetch_row(mysql_query("select instid,listmailpath,daypw from $ctable where 1"));

if($pw<>$daypw) exit("Access denied.");
// echo "instid=$lmp_id lmpath=$lmp_url daypw=$daypw<br>";

// make backup file
// read backup file into var for posting
// try line by line...
 
  // optimize tables..
  optimizeall();
  
  $fn = './attach/' . date("Y-m-d_h-i-s");
  list($nu) = @mysql_fetch_row(mysql_query("select count(*) from $utable where 1;"));
  if(!$nu) $nu = 0;
  //  echo "<center><div align=left style=\"font: 9pt arial; width: 760px;\">Optimizing tables... "; flush();
  // echo "DONE "; flush();

  // echo "Writing backup file... "; flush();
  // exec("echo > $fn.LM");
  $fp = fopen("$fn.LM",'w') or die('Could not open file for writing - is your ListMail attach folder writable?');
  reset($atables);
  while(list($k,$v)=each($atables)){
  
   /* NEW BACKUP PROCESS */
   $start = time();
   $out = "-- ListMailPRO Backup v1.00 (c) 2006 ListMail / Dean Wiebe\n-- This file can be restored from within ListMail or by uploading into PhpMyAdmin\n-- Backup Start: ".date("Y-m-d H:i(+s\s) T ",$start)."\n";

   foreach($atables as $table){
    // CREATE
    $out .= "\nCREATE TABLE $table (\n";
    $rows = mysql_query("describe $table") or die("db error accessing table $table");;
    $nfs = @mysql_num_rows($rows);
    $x=1;
    $foot = '';
    while(list($field,$type,$null,$key,$default,$extra)=mysql_fetch_row($rows)){
     //  echo "field=$field, type=$type, null=$null, key=$key, default=$default, extra=$extra<br>";
     $out .= " $field $type";
     if($default) $out .= " DEFAULT '$default'";
     if(!$null) $out .= " NOT NULL";
     if($extra=='auto_increment') $out .= " AUTO_INCREMENT";
     if($key=='PRI'){ if($foot) $foot .= ", \n"; $foot .= " PRIMARY KEY($field)"; }
     elseif($key=='MUL'){ if($foot) $foot .= ", \n"; $foot .= " INDEX($field)"; }
     if($x<>$nfs) $out .= ", \n"; elseif($x==$nfs&&$foot) $out .= ", \n"; else $out .= "\n";
     if($x==$nfs && $foot) $out .= $foot."\n";
     if($x==$nfs) $out .= ");\n\n";
     $x++;
    }
    fputs($fp,$out);
    $out = '';
    // INSERT
    $rows = mysql_query("select * from $table where 1") or die("db error accessing table $table");
    $nrows = @mysql_num_rows($rows);
    if($nrows>0){
     while($row = mysql_fetch_row($rows)){
      $out .= "INSERT INTO $table VALUES(";
      $x = 1;
      $cnt = count($row);
      while(list($k,$v)=each($row)){
       $out .= "'".addslashes($v)."'";
       if($x<>$cnt) $out.= ",";
       $x++;
      }
      $out .= ");\n";
      fputs($fp,$out);
      $out = '';
     }
    } // else echo " no rows in table..";
   }
   $end = time();
   $total = $end - $start;
   $out .= "\n-- Backup Finish: ".date("Y-m-d H:i(+s\s) T ",$end)."\n";
   $out .= "-- Total MySQL Time: $total seconds";
   fputs($fp,$out);
   /* END NEW BACKUP PROCESS */
  }
  
  fclose($fp);
  // echo "DONE "; flush();
  reset($atables);
  // echo "Verifying backup file... "; flush();
  // VERIFY FILE FOR ALL TABLES
  $found = array();
  if(@file_exists($fn.'.LM')){
   if($fp=fopen($fn.'.LM','r')){
    $uc = 0;
    while(!feof($fp)){
     $line = fgets($fp,2048);
     // echo "line=$line<br>";
     // check # of users
     if(strpos($line,'INTO '.$utable)>0) $uc++;
     while(list($k,$v)=each($atables)){
      if(strpos(' '.$line,'CREATE TABLE '.$v)>0 && !in_array($v,$found)){
       // echo "FOUND $v<br>";
       $found[] = $v;
      }
     }
     reset($atables);
    }
   } else {
    // echo "<font color=red>ERROR - COULD NOT OPEN FILE TO VERIFY</font><br>";
   }
   // echo "substr(line,0,20)=".substr($line,0,20);
   if(count($atables)<>count($found)) echo "<font color=red>ERROR - MISSING DATA TABLES</font><br>";
   elseif(substr($line,0,20)<>'-- Total MySQL Time:') echo "<font color=red>ERROR - MISSING DATA</font><br>";
   else {
    // echo "OK "; flush();
    // check # of users
    if($nu <> $uc){ } // echo "<font color=red>ERROR - MISSING USERS</font><br>";
   }
   if($zipit||true){
    // echo 'gZipping... '; flush();
    // compress file with buffering
    // echo "memory_usage=".memory_get_usage()."<br>";
    // 2 MB default memory limit
    $memory_limit = 2 * 1024 * 1024;
    $buffer = '';

    $if = $fn.'.LM';
    $of = $fn.'.LM.gz';
    $ifp = fopen($if,'r');
    $ofp = fopen($of,'w');
    if(!$ifp || !$ofp) exit('gz: could not open input and/or output file');
    while(!feof($ifp)){
     $buffer .= fgets($ifp,2048);
     if(strlen($buffer) > $memory_limit){
      // echo "strlen(buffer) > memory_limit ... usage=".memory_get_usage()."<br>";
      // write to output file
      fputs($ofp,gzencode($buffer));
      $buffer = '';
     }
    }
    if(strlen($buffer)>0) fputs($ofp,gzencode($buffer));
    fclose($ifp);
    fclose($ofp);
    if(!@unlink($fn.'.LM')){
     @exec('rm '.$fn.'.LM');
     @exec('del '.$fn.'.LM');
    }
    // echo "OK<br>"; flush();
   } // else echo "<br></span>";
  }  
   
$fn = $of;

// done backup, start building request
$pfn = str_replace('./attach/','',$fn);
$fn = './attach/'.$pfn;

// echo "pfn=$pfn fn=$fn<br>";
// POST to LM

$bound = md5(time());
$body = "--$bound\r\n";
$body .= "content-disposition: form-data; name=\"lmp_url\"\r\n\r\n";
$body .= "$lmp_url\r\n";
$body .= "--$bound\r\n";
$body .= "content-disposition: form-data; name=\"lmp_id\"\r\n\r\n";
$body .= "$lmp_id\r\n";
$body .= "--$bound\r\n";
$body .= "content-disposition: form-data; name=\"userfile\"; filename=\"$pfn\"\r\n";
$body .= "Content-Type: application/octet-stream\r\n";
$body .= "Content-Transfer-Encoding: binary\r\n\r\n";
$foot = "--$bound--\r\n";
$len = strlen($body)+filesize($fn)+strlen($foot);

$head = "POST /members/backup-inbound.php HTTP/1.1\r\n";
$head .= "Host: lmp2.lmhost.com\r\n";
$head .= "Content-Type: multipart/form-data; boundary=$bound\r\n";
$head .= "Content-Length: $len\r\n\r\n";

$fp = @fsockopen('lmp2.lmhost.com',80);
if($fp){
 $str1 = $head.$body;
 fputs($fp,$str1);
 unset($str1);
 $fp2 = fopen($fn,'r') or die('error opening file');
 while(!feof($fp2)){
  $line = fgets($fp2,2048);
  fputs($fp,$line);
 }
 fclose($fp2);
 fputs($fp,"\r\n");
 fputs($fp,$foot);
 // get result back
 while(!feof($fp)){
  $line = fgets($fp,1024);
  flush();
 }
 fclose($fp);
 echo "LMP Server: $line";
} else {
 // notify user that server could not be contacted
 echo "Could not contact LMP server!  This should be temporary - please try again.<br>";
}
// @exec('rm '.$fn.'.LM');
// @exec('del '.$fn.'.LM');
if(!@unlink($fn)){
 @exec('rm '.$fn);
 @exec('del '.$fn);
}
    
?>