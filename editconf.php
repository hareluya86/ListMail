<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$islogged = islogged();

if($backup && $restored && $_COOKIE['LMadm1'] == md5($xid)){
 // fix cookie
 list($instid)=mysql_fetch_row(mysql_query("select instid from $ctable where 1"));
 setcookie('LMadm1',md5($instid),0,'/','.'.getdomain(),0);
 header('Location: ./editconf.php?backup=1');
 exit();
}

if($islogged || (!$installed && !$codes)){$isadmin='true';} else { $isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit; }

if ($isadmin == 'true'){

if($sysinfo){
  // get data
  list($lmp)=mysql_fetch_row(mysql_query("select listmailpath from $ctable where 1"));
  list($mv)=@mysql_fetch_row(mysql_query("select version()"));
  $st = @shell_exec('uname -a');
  $mp = detectpath('mysql');
  if($mp) $mp .= '*';
  $pp = detectpath('perl',1);
  $wp = detectpath('wget',1);
  $cp = detectpath('curl',1);
  $fp = detectpath('fetch',1);
  $lp = detectpath('lynx',1);

  if(@file_exists('./attach')){
   $at = 'Yes';
   $ap = substr(sprintf('%o', fileperms('./attach')), -3);
   if($ap=='777') $ap .= ' - Correct'; else $ap .= ' - <font color=green>Incorrect</font> -  <a class=sysinfo_link target=a0 href=http://listmailpro.com/help/attach.html>Help</a>';
   if(@file_exists('./attach/.htaccess')){
    $as = file_get_contents('./attach/.htaccess');
   } else $as = 'No - <a class=sysinfo_link target=a0 href=http://listmailpro.com/help/attach.html>Help</a>';
  } else $at = 'No';

  adminheader('LM: Server Info','Server Info','');
  echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
  listmenu($list,'config');
  echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

   <table border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <input type=button class=button value=\"Back to Config\" onclick=\"hopto('./editconf.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 110px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button_on value=\"SysInfo\" onclick=\"hopto('./editconf.php?sysinfo=1&list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\" style=\"width: 79px\">
     </td>
   </tr>
  </table>
  <img src=1.gif height=3><br>

<table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Server Information</span></td><td align=right><span class=table_head_text>v$file_version<img src=1.gif width=2>:</span><img src=1.gif width=2><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('sysinfo')."');\">Help</a><br></td></tr></table></td></tr><tr><td height=1 bgcolor=black><img src=1.gif height=1><br></td></tr><tr><td bgcolor=#eeeeee>
<table width=92% border=0 cellspacing=0 cellpadding=0><tr><td width=5><img src=1.gif width=5></td><td>
<img src=1.gif height=5><br>
<span class=sysinfo_head>General Information</span><br>

<span class=sysinfo_option_head>ListMail URL:</span>
<span class=sysinfo_option>$lmp<br></span>
<span class=sysinfo_option_head>Server Path:</span>
<span class=sysinfo_option>$DOCUMENT_ROOT</span><br>
<span class=sysinfo_option_head>Server ID:</span>
<span class=sysinfo_option>$st</span><br>
<span class=sysinfo_option_head>Client Type:</span>
<span class=sysinfo_option>$HTTP_USER_AGENT</span><br>
<!-- <span class=sysinfo_option_head>Dailymail Last Run:</span>
<span class=sysinfo_option>date here</span><br> -->
<img src=1.gif height=10><br>
<table width=100% border=0 cellspacing=0><tr><td width=50% valign=top><span class=sysinfo_head>PHP</span><br>
<span class=sysinfo_option_head>PHP Version:</span>
<span class=sysinfo_option>".phpversion()."</span><br>
<span class=sysinfo_option_head>php.ini \"file_uploads\":</span>
<span class=sysinfo_option>"; if(ini_get('file_uploads')=='1') echo 'Yes'; else echo 'No'; echo "</span><br>
<span class=sysinfo_option_head>php.ini \"upload_max_filesize\":</span>
<span class=sysinfo_option>".ini_get('upload_max_filesize')."</span><br>
<span class=sysinfo_option_head>php.ini \"memory_limit\":</span>
<span class=sysinfo_option>".ini_get('memory_limit')."</span><br>
<span class=sysinfo_option_head>php.ini \"post_max_size\":</span>
<span class=sysinfo_option>".ini_get('post_max_size')."</span><br>
</td><td width=50% valign=top>
<span class=sysinfo_head>Linux Programs</span><br>
<span class=sysinfo_option_head>Path to \"perl\":</span>
<span class=sysinfo_option>$pp</span><br>
<span class=sysinfo_option_head>Path to \"wget\":</span>
<span class=sysinfo_option>$wp</span><br>
<span class=sysinfo_option_head>Path to \"curl\":</span>
<span class=sysinfo_option>$cp</span><br>
<span class=sysinfo_option_head>Path to \"fetch\":</span>
<span class=sysinfo_option>$fp</span><br>
<span class=sysinfo_option_head>Path to \"lynx\":</span>
<span class=sysinfo_option>$lp</span><br>
</td></tr></table>
<img src=1.gif height=10><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=50% valign=top>
<span class=sysinfo_head>MySQL</span><br>
<span class=sysinfo_option_head>MySQL Version:</span>
<span class=sysinfo_option>$mv</span><br>
<span class=sysinfo_option_head>Path to MySQL utilities:</span>
<span class=sysinfo_option>$mp</span><br>
</td><td width=50% valign=top nowrap>
<span class=sysinfo_head>./attach Folder</span><br>
<span class=sysinfo_option_head>./attach Exists?</span>
<span class=sysinfo_option>$at</span><br>
<span class=sysinfo_option_head>./attach Permissions:</span>
<span class=sysinfo_option>$ap</span><br>
<span class=sysinfo_option_head>./attach Security:</span>
<span class=sysinfo_option>$as</span><br>
</td></tr></table>
<img src=1.gif height=10><br>
</td></tr></table>
</center>
</td></tr></table>
</td></tr></table>";
exit;
}
if($backup){
 if($dl=='1'){
  header("Content-type: application/octet-stream");
  header("Content-Disposition: attachment; filename=$fn");
  readfile("./attach/$fn");
  exit;
 }

 if($delete&&$file&&!$restore){
  @exec("rm $file");
  @exec("del $file");
 }

 if($upload&&$file){
	$rpos = strrpos($file,'/');
	$pfn = substr($file,$rpos+1);
	$fn = './attach/'.$pfn;

     echo "<center><div align=left style=\"font: 9pt arial; width: 760px;\">Sending $pfn to LMP server..."; flush();
	
     list($lmp_id,$lmp_url)=mysql_fetch_row(mysql_query("select instid,listmailpath from $ctable where 1;"));
	$targ = 'https://www.lmp2.lmhost.com/members/backup-inbound.php';
	// echo "lmpid=".$lmp_id." lmpurl=".$lmp_url."<br>";
	$lmp_url = urlencode($lmp_url);
	$lmp_id =  urlencode($lmp_id);
	

	
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
	  // echo "line=$line<br>"; flush();
	 }
	 fclose($fp);
	 echo "$line";
	} else {
	 // notify user that server could not be contacted
	 echo "Could not contact LMP server!  Please try again.<br>";
	}
   
     echo "</div></center>"; 
 }

 if($restore&&$file){
  // if(!$sqlpath) $sqlpath = detectpath('mysql');
  // if(!$sqlpath) echo "<span class=status_text>Error: Path to MySQL could not be detected.  Manually set the path to the MySQL binaries in config.php</span><br>";

  // get old installid and admpw
  list($instid,$admpw)=mysql_fetch_row(mysql_query("select instid,admpw from $ctable where 1"));
  // drop tables (for backward compat.)
  echo "<span style=\"font: 9pt arial\">Dropping old tables... "; flush();
  while(list($key,$val)=each($atables)){
   @mysql_query("DROP TABLE if exists $val");
  }
  echo "OK "; flush();
  $fileext = substr($file,strlen($file)-3,3);
  if(strtoupper($fileext) == '.GZ') $unzip = 1; else $unzip = '';
  if($unzip){
   echo "Unzipping... "; flush();
   // unzip it
   $memory_limit = 2 * 1024 * 1024;
   $buffer = '';
   // echo "unzipping $file.. $file<br>";
   $if = $file;
   $of = './attach/'.substr($file,2,strlen($file)-5);
   // echo "of=$of<br>";
   $ifp = gzopen($if,'r');
   $ofp = fopen($of,'w');
   if(!$ifp || !$ofp) exit('gz: could not open input and/or output file');
   while(!feof($ifp)){
    $buffer .= gzread($ifp,2048);
    if(strlen($buffer) > $memory_limit){
     // echo "strlen(buffer) > memory_limit ... usage=".memory_get_usage()."<br>";
     // write to output file
     fputs($ofp,$buffer);
     $buffer = '';
    }
   }
   if(strlen($buffer)>0) fputs($ofp,$buffer);
   // echo "done?<br>";
   $file = $of;
   fclose($ifp);
   fclose($ofp);
   echo "OK "; flush();
  }
  // echo "file=$file";
  echo "Importing data - Please wait... "; flush();
  $fp = fopen($file,'r') or die("Could not open file: $file");
  $cmd = '';
  while(!feof($fp)){
   $line = fgets($fp,10000);
   // $line = substr($line,0,strlen($line)-1);
   if(substr($line,0,2)<>'--'){
    $cmd .= substr($line,0,strlen($line)-1);
    if(substr($line,strlen($line)-2,1)==';'){
     // echo "<textarea rows=10 cols=100>$cmd</textarea><br>";
     mysql_query($cmd) or die(mysql_error());
     $cmd = '';
    }
   }
  }
  fclose($fp);
  // exec($sqlpath."mysql -u$sqluser -p$sqlpass -h$xsqlhost".$sqlsock." $sqldb <$file");
  echo "OK "; flush();
  if($unzip){
   @exec("rm $file");
   @exec("del $file");
  }
  $special = "refresh-cookie;$instid";
  echo "Optimizing tables... "; flush();
  optimizeall();
  echo "OK<br></span>"; flush();
  $logtxt = 'Restore complete!';
  list($nadmpw)=mysql_fetch_row(mysql_query("select admpw from $ctable where 1"));
  if($nadmpw<>$admpw){
   if(!$nadmpw) $nadmpw = 'blank/<b>install</b>';
   else $nadmpw = "<b>$nadmpw</b>";
   echo "<span style=\"font: 9pt arial\">Your admin password appears to have changed (to $nadmpw).  Please <a style=\"color: navy; font: 9pt arial;\" href=./login.php>re-login</a> to reset your cookies.</span><br>";
   exit();
   // setcookie('LMadm2',md5($nadmpw),0,'/','.'.getdomain(),0);
  }
 }

 if($write){
  // optimize tables..
  $fn = './attach/' . date("Y-m-d_h-i-s");
  list($nu) = @mysql_fetch_row(mysql_query("select count(*) from $utable where 1;"));
  if(!$nu) $nu = 0;
  echo "<center><div align=left style=\"font: 9pt arial; width: 760px;\">Optimizing tables... "; flush();
  optimizeall();
  echo "DONE "; flush();
  echo "Writing backup file - Please wait... "; flush();
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
  echo "DONE "; flush();
  reset($atables);
  echo "Verifying backup file... "; flush();
  // VERIFY FILE
  $found = array();
  if(@file_exists($fn.'.LM')){
   if($fp=fopen($fn.'.LM','r')){
    while(!feof($fp)){
     $line = fgets($fp,2048);
    }
   } else {
    echo "<font color=red>ERROR - COULD NOT OPEN FILE TO VERIFY</font><br>";
   }
   // echo "Done verifying<br>";
   // echo "substr(line,0,20)=".substr($line,0,20);
   if(substr($line,0,20)<>'-- Total MySQL Time:') echo "<font color=red>ERROR - MISSING DATA</font><br>";
   else {
    echo "OK "; flush();
   }
   if($zipit){
    echo 'gZipping... '; flush();
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
    echo "OK<br>"; flush();
   } else echo "<br></span>";
   // echo "DONE<br>"; flush();
   $logtxt = "Backup file written!";
   echo "</div></center>";
  } else {
   echo "<font color=red>NOT FOUND</font><br></small>";
  }
 }

 if(!$dorestore){
  // main page
  adminheader('LM: Backup & Restore','Backup & Restore','');
  echo "<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";
  listmenu($list,'config');
  echo "   <!-- end left menu -->
  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>

   <table border=0 bordercolor=#dddddd cellspacing=0 cellpadding=0>
    <tr>
     <td width=1>
      <input type=button class=button value=\"Back to Config\" onclick=\"hopto('./editconf.php?list=$list');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 110px\">
     </td>
     <td width=3><img src=1.gif width=3></td>
     <td width=1>
      <input type=button class=button_on value=\"Backup & Restore\" onclick=\"hopto('./editconf.php?list=$list&backup=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button_on'\" style=\"width: 135px\">
     </td>
   </tr>
  </table>
  <img src=1.gif height=3><br>

<table width=100% class=outer_table border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Backup & Restore Database</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('backup_restore')."');\">Help</a><br></td></tr></table>
</td></tr></table>

<img src=1.gif height=5><br>
<span class=table_inside_normal>This page allows you to save and restore your entire database of ListMail data.  That includes everything, all of your settings, lists, and users.  With this function you can change hosts and migrate your ListMail installation as well as protect again unforeseen accidents.<br><img src=1.gif height=10><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td valign=top width=48%><form method=post action=\"./editconf.php\"><input type=hidden name=backup value=1><input type=hidden name=write value=1><input type=hidden name=list value=$list>
<span class=table_inside_normal><b><u>Backup Database</u></b><br><img src=1.gif height=2><br>Your ListMailPRO data will be written to a file which you can download.  This file is a basic MySQL dump file that can be restored either through ListMailPRO (at right) or with a program like PhpMyAdmin.<br><img src=1.gif height=10><br>Files are temporarily stored in the attach folder.  It is <i>very important</i> that you delete these files immediately as they are fully accessible.<br>\n";
if(function_exists('gzencode')) echo "<img src=1.gif height=10><br>
<input name=zipit value=1 type=checkbox style=\"vertical-align: bottom\" class=chkbox checked=checked> gZip the file (Recommended)</span><br>\n";
echo "<img src=1.gif height=10><br><input class=button type=submit value=\"Write Backup File\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\"><br>
<img src=1.gif height=10><br>\n";

$handle=opendir('./attach');
$x = 0;
$xarray = array();
while($file_name = readdir($handle)) {
 $fileext = substr($file_name,strlen($filename)-3,3);
 if ($file_name != "." && $file_name != ".." && (strtoupper($fileext) == '.LM' || strtoupper($fileext) == '.GZ')) {
  $xarray[] = $file_name;
  $relpath = "./attach/".$file_name;
  $file_size = filesize($relpath);
  if($file_size >= 1048576) {
   $file_size = ceil(round($file_size / 1048576 * 100) / 100) . "M";
  }
  elseif ($file_size >= 1024) {
   $file_size = ceil(round($file_size / 1024 * 100) / 100) ."k";
  } else {
   $file_size = $file_size . "b";
  }

  //  if($demo<>'yes') $dlhref = "./attach/$file_name"; else $dlhref = "Not%20Available%20In%20Demo";
  if($demo<>'yes') $dlhref = "./editconf.php?list=$list&backup=1&dl=1&fn=$file_name"; else $dlhref = "Not%20Available%20In%20Demo";

  echo "<div style=\"border: 1px solid #eeeeee; padding: 3px 3px 3px 3px\"><span class=table_inside_normal><b>$file_name</a><img src=1.gif width=20 height=1>$file_size</b></span><br><img src=1.gif height=3><br><a href=$dlhref class=table_inside_normal style=\"color: blue\">Download</a><span class=table_inside_normal> Choose \"Save to Disk\"</span><br>
<img src=1.gif height=3><br><a href=\"./editconf.php?list=$list&backup=1&delete=1&file=./attach/$file_name\" class=table_inside_normal style=\"color: red\">Delete</a><span class=table_inside_normal> the file once you have saved it.</span><br>
<img src=1.gif height=3><br>";
  if(!$no_auto_backup) echo "<a href=\"./editconf.php?list=$list&backup=1&upload=1&file=./attach/$file_name\" class=table_inside_normal style=\"color: green\">Upload</a><span class=table_inside_normal> the file to LMP servers! * <a style=\"color: green\" href=https://www.lmp2.lmhost.com/members/?r=".urlencode('/members/backup.php?backup=1')." target=newlmp>Learn More</a></span>";
   echo "</div><img src=1.gif height=5><br>";
 }
}
closedir($handle);

echo "</td></form>
<td width=10><img src=1.gif width=10></td>
<td valign=top><form method=post><input type=hidden name=backup value=1><input type=hidden name=restore value=1><span class=table_inside_normal><b><u>Restore Database</u></b><br><img src=1.gif height=2><br>Your settings will be read from a backup file uploaded into the ListMailPRO directory.<br><img src=1.gif height=10><br><font color=red>This will completely overwrite any existing lists, users, and settings with the data from the backup file!</font><br><img src=1.gif height=10><br>
Click your uploaded file below.  If you dont see any files, upload your *.LM or *.LM.gz file to your ListMailPRO directory via FTP and refresh the page.<br>
<img src=1.gif height=10><br>\n";

echo "
<span class=table_inside_normal>
<img src=1.gif height=2><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td>";
$handle=opendir('./');
$x = 0;
$xarray = array();
while($file_name = readdir($handle)) {
 $fileext = substr($file_name,strlen($filename)-3,3);
 $fileext2 = substr($file_name,strlen($filename)-6,6);
 if ($file_name != "." && $file_name != ".." && (strtoupper($fileext) == ".LM" || strtoupper($fileext2) == 'LM.TXT' || strtoupper($fileext2) == '.LM.GZ')) {
  $xarray[] = $file_name;
  $relpath = "./".$file_name;
  $file_size = filesize($relpath);
  if ($file_size >= 1048576) {
   $file_size = ceil(round($file_size / 1048576 * 100) / 100) . "M";
  }
  elseif ($file_size >= 1024) {
   $file_size = ceil(round($file_size / 1024 * 100) / 100) ."k";
  } else {
   $file_size = $file_size . "b";
  }
  echo "<tr>
   <td><a onclick=\"if(!confirm('Are you sure you want to COMPLETELY overwrite all existing ListMail data??')) return false; else return true;\" href=\"./editconf.php?list=$list&backup=1&restore=1&file=./$file_name\"  class=table_inside_small style=\"color: blue\">$file_name</a></td><td width=20><img src=1.gif width=20 height=1></td><td width=1 align=right><span class=table_inside_small style=\"color: navy\">$file_size</span></td></tr>";
 }
}
echo "</table>";
if(!empty($xarray)){
 echo "<img src=1.gif height=10><br>You should manually delete the(se) file(s) as soon as you have restored your database.<br>";
}
if(empty($xarray)){
 echo "<span class=table_inside_normal>No uploaded backup files found.</span>";
}

closedir($handle);

echo "</td></form></tr></table>";
  if($logtxt) echo "<span class=status_text>$logtxt</span><br>";
  echo "</td></tr></table>";
 }
 exit;
} // end backup & restore

if($customcmd){
 if(!$ntimes) $ntimes = '1';
 if($doit){
  $ccmd = addslashes($ccmd);
  for($i=0;$i<$ntimes;$i++){
   // echo "CMD=".$ccmd."<br>";
   mysql_query($ccmd) or die("failed executing command");
  }
  echo "<br>Successfully executed command(s). ".mysql_affected_rows()." rows affected.<br>";
 }
 echo "<form method=post><input type=hidden name=customcmd value=1><input type=hidden name=doit value=1>Enter custom command: <input type=text name=ccmd value=\"".htmlspecialchars($ccmd)."\" size=50> Repeat <input type=text size=1 name=ntimes value=$ntimes><br>
 <input type=submit value=\"Do it!\"></form>";
  exit;
}

/* *************************************************************************************************** */

if($codes){
 list($keych)=mysql_fetch_row(mysql_query("select keycode from $ctable where 1"));

 if(!$list) $list = 'all';
 if($list=='all') $clist = '0'; else $clist = $list;
 if($save){
  $errtxt = '';
  if($typ=='remove'){
   // check multi list rem / options
   if($dummy) $data4 = ''; else $data4 = str_replace(' ','',$data4);
   if($data4){
    if(strpos(' '.$data4,'*')>0) $data4 = '*';
    else
    if(!ereg("^[0-9]+(,[0-9]+)*$",$data4)){
     $error = 1; $rem_multi = 1;
    } else {
     // check if lists exist
     $lists=explode(',',$data4);
     while(list($k,$v)=each($lists)){
      if(@mysql_num_rows(mysql_query("select listnum from $ltable where listnum = '$v'"))==0) $errtxt .= "Warning: List $v used in the code does not exist.";
     }
    }
   }
   if(($data3 || $data4) && $data5=='1') $data5=='2';
  }
  if($typ=='date' || $typ=='dateadd'){
   // check offsets data2-4 +/- 999
   if(!is_numeric($data2)) $data2 = '0';
   if(!is_numeric($data3)) $data3 = '0';
   if(!is_numeric($data4)) $data4 = '0';
   if($data2 > 999) $data2 = '999'; if($data2 < -999) $data2 = '-999';
   if($data3 > 999) $data3 = '999'; if($data3 < -999) $data3 = '-999';
   if($data4 > 999) $data4 = '999'; if($data4 < -999) $data4 = '-999';
  }

  $noreload = '';
  if(($cname <> $origname) || $add || $noreload) $checkdupe = 1; else $checkdupe = '';

  $dupe = '';
  $cname = addslashes($cname);
  $typ = addslashes($typ);
  $data1 = addslashes($data1);
  $data2 = addslashes($data2);
  $data3 = addslashes($data3);
  $data4 = addslashes($data4);
  $data5 = addslashes($data5);
  $id = addslashes($id);

  if($checkdupe){
   if(@mysql_num_rows(@mysql_query("select id from $dtable where ref like '$cname' and list = '$clist'")) > 0){ $dupe = 1; $error = 1; } else $dupe = '';
  }
  if($cname=='') $error = 1;
  if(!$error){
   if($edit){
    mysql_query("update $dtable set list='$clist', ref='$cname',typ='$typ',data1='$data1',data2='$data2',data3='$data3',data4='$data4',data5='$data5' where id = '$id'");
    $logtxt = 'Saved message code.';
    $origname = $cname;
   }
   if($add){
    mysql_query("insert into $dtable (id,list,ref,typ,data1,data2,data3,data4,data5) values('','$clist','$cname','$typ','$data1','$data2','$data3','$data4','$data5')") or die(mysql_error());
    $logtxt = 'Added message code.';
    $id = mysql_insert_id();
    $add='';
    $edit = 1;
   }
  } else {
   $noreload = 1;
   if($cname=='') $logtxt = 'Blank code name. Not saved!';
   if($dupe) $logtxt = 'Duplicate code name. Not saved!';
   if($rem_multi) $logtxt = 'Multi-list invalid format.  Format is "1,2,3". Not saved!';
  }
  $xtxt = $logtxt;
  if($noreload){
   $cname = stripslashes($cname);
   $typ = stripslashes($typ);
   $data1 = stripslashes($data1);
   $data2 = stripslashes($data2);
   $data3 = stripslashes($data3);
   $data4 = stripslashes($data4);
   $data5 = stripslashes($data5);
   $id = stripslashes($id);
  }
  $save='';
 }

 if($edit || $add){
  if($edit) $atxt = 'Edit Message Code';
  if($add){
   $ref = $cname;
   $atxt = 'Add Message Code';
  }
  adminheader('LM: '.$atxt,$atxt,'');

  echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

  listmenu($list,'confcode');

  echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";

  echo " <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
  <tr>
   <td class=table_head_bg_nopad>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>$atxt</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('message_codes')."');\">Help</a><br></td></tr></table>
   </td>
  </tr>
  </table>
  <table width=100% border=0 cellspacing=0 cellpadding=0>
  <tr>
   <td>
    <img src=1.gif height=4><br>\n";

  if($edit && !$noreload){
   $cmd = "select id,ref,typ,data1,data2,data3,data4,data5 from $dtable where id = '$id'";
   $drow = mysql_query($cmd);
   list($id,$ref,$typ,$data1,$data2,$data3,$data4,$data5)=@mysql_fetch_row($drow);
  } elseif ($noreload){
   $ref = $cname;
   $typ = $typ;
   // echo "id=$id ref=$ref typ=$typ data1=$data1 data2=$data2 data3=$data3 data4=$data4 cid=$cid cname=$cname type=$typ<br>";
  }

  if(!$typ) $typ = 'remove';
  switch($typ){
   case 'fname': $fn=' selected'; break;
   case 'lname': $ln=' selected'; break;
   case 'fullname': $fu=' selected'; break;
   case 'email': $em=' selected'; break;
   case 'remove': $re=' selected'; break;
   case 'confirm': $co=' selected'; break;
   case 'text': $te=' selected'; break;
   case 'date': $da=' selected'; break;
   case 'title': $ti=' selected'; break;
   case 'uid': $ui=' selected'; break;
   case 'field1' : $f1 = ' selected'; break;
   case 'field2' : $f2 = ' selected'; break;
   case 'field3' : $f3 = ' selected'; break;
   case 'field4' : $f4 = ' selected'; break;
   case 'field5' : $f5 = ' selected'; break;
   case 'field6' : $f6 = ' selected'; break;
   case 'field7' : $f7 = ' selected'; break;
   case 'field8' : $f8 = ' selected'; break;
   case 'field9' : $f9 = ' selected'; break;
   case 'field10' : $f10 = ' selected'; break;
   case 'ip' : $ip = ' selected'; break;
   case 'refurl' : $ru = ' selected'; break;
   case 'dateadd' : $dt = ' selected'; break;
   case 'link2html' : $ht = ' selected'; break;
   case 'remcnfht' : $rch = ' selected'; break;
   case 'numsubs' : $nsu = ' selected'; break;
  }

  // get custom fields for this list
  $fields = getcfields($list);

  echo "<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <form name=code_frm method=post action=./editconf.php><span class=table_inside_small>Message Code Type:</span><br>
   <select name=typ class=xbox onchange=\"code_frm.noreload.value=1; code_frm.submit();\">
   <option value=#>-- Choose Type --
   <option value=remove$re>Remove Link
   <option value=remcnfht$rch>Remove Confirm
   <option value=confirm$co>Signup Confirm
   <option value=fname$fn>First Name
   <option value=lname$ln>Last Name
   <option value=fullname$fu>Full Name
   <option value=email$em>Email Address
   <option value=text$te>Custom Text
   <option value=date$da>Formatted Date
   <option value=dateadd$dt>Date Added
   <option value=title$ti>List Title
   <option value=refurl$ru>Referring URL
   <option value=uid$ui>Unique UID
   <option value=ip$ip>IP Address
   <option value=link2html$ht>Link to HTML
   <option value=field1$f1>".$fields[1]."
   <option value=field2$f2>".$fields[2]."
   <option value=field3$f3>".$fields[3]."
   <option value=field4$f4>".$fields[4]."
   <option value=field5$f5>".$fields[5]."
   <option value=field6$f6>".$fields[6]."
   <option value=field7$f7>".$fields[7]."
   <option value=field8$f8>".$fields[8]."
   <option value=field9$f9>".$fields[9]."
   <option value=field10$f10>".$fields[10]."
   <option value=numsubs$nsu>Subscriber Count
   </select><br>
   <img src=1.gif height=5><br>";

  if($ref) $thecode = $keych.$ref;

  echo "<span class=table_inside_normal>";

  // show text in edit form
  switch($typ){
   // name
   case 'fname': echo "The code <b>$thecode</b> will be replaced by the user's first name or the default text when email is sent.<br><img src=1.gif height=10><br>"; break;
   case 'lname': echo "The code <b>$thecode</b> will be replaced by the user's last name when email is sent.<br><img src=1.gif height=10><br>"; break;
   case 'fullname': echo "The code <b>$thecode</b> will be replaced by the user's full name, a combination of the first and last name or just the first name if the last name doesn't exist.  You may also assign a default value if no first name is found.<br><img src=1.gif height=10><br>"; break;

   // email
   case 'email': echo "The code <b>$thecode</b> is replaced by the user's email address when email is sent.<br><img src=1.gif height=10><br>"; break;

   // remove
   case 'remove':
    $c = $id; if(!$c) $c = '1';
    echo "The code <b>$thecode</b> is replaced by an automatically generated removal link.  You can set text that appears above the link and also set the title of the HTML link that is displayed by this code.  The remove text is optional.  If you leave it blank, just the link will be output by this code.  When clicked, this link will bring the user to your Custom HTML \"Remove Confirm\" page for the list they are removing themselves from.<br><img src=1.gif height=10><br>
<span class=table_inside_small><u>Example of Output</u><br><img src=1.gif height=5><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=50% valign=top><span class=table_inside_small><b>Text Version:</b><br>";
   // create link
   $rlink = $lmpath . "rem.php?";
   if($data5=='1' || !$data5) $rlink .= "u=1a2b3c4";
   if($data5=='2') $rlink .= "c=$c&u=1a2b3c4";
   if($data5=='3') $rlink .= "u=$c,1a2b3c4";
   if($data5=='4') $rlink .= "x=$c&l=2&e=email@addr.com";
   if($data5=='5') $rlink .= "x=$c,2,email@addr.com";

   if($data1) echo nl2br($data1)."<br>";
   echo "$rlink</span></td><td width=50% valign=top><span class=table_inside_small><b>HTML Version:<br></b>";
   if($data1) echo nl2br($data1)."<br>";
   echo "<a href=$rlink>$data2</a></td></tr></table><img src=1.gif height=10><br>";  break;

   // confirm
   case 'confirm':
    echo "The code <b>$thecode</b> is replaced by an automatically generated confirmation link.  You can set text that appears above the link and also set the title of the HTML link that is displayed by this code.  The remove text is optional.  If you leave it blank, just the link will be output by this code.  When clicked, this link will bring the user to your Custom HTML \"Subscribe Success\" page for the list they are subscribing to.<br><img src=1.gif height=10><br>
<span class=table_inside_small><u>Example of Output</u><br><img src=1.gif height=5><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=50% valign=top><span class=table_inside_small><b>Text Version:</b><br>";
   if($data1) echo nl2br($data1)."<br>";
   echo "http://".getdomain().getdir()."/confirm.php?u=1a2b3c4</span></td><td width=50% valign=top><span class=table_inside_small><b>HTML Version:<br></b>";
   if($data1) echo nl2br($data1)."<br>";
   echo "<a href=http://".getdomain().getdir()."/confirm.php?u=1a2b3c4>$data2</a></td></tr></table><img src=1.gif height=10><br>";  break;

   // HTML remove confirm
   case 'remcnfht':
    echo "The code <b>$thecode</b> is replaced by a button that enables the user to confirm removal of their address on the \"Remove Confirm\" Custom HTML page.  The button can be customized by setting up a style sheet in your Custom HTML modifying the \".confirm_button\" element.  When clicked, the button will bring the user to your Custom HTML \"Remove Success\" page for the list(s) they are unsubscribing from.<br><img src=1.gif height=10><br>";  break;

   // custom text
   case 'text': echo "The code <b>$thecode</b> will be replaced by user-definable text when email is sent.  In HTML email, line breaks will be replaced with <b>&lt;br&gt;</b>'s.<br><img src=1.gif height=10><br>"; break;

   // date
   case 'date': echo "The code <b>$thecode</b> will be replaced by a formatted date that is calculated at sending time.<br><img src=1.gif height=10><br>";
echo "<span class=table_inside_small><u>Recognized characters in string:</u><br></span>";
?>
<div style="font: 9pt courier new">
d - day of the month, 2 digits with leading zeros; i.e. "01" to "31"<br>
D - day of the week, textual, 3 letters; i.e. "Fri"<br>
F - month, textual, long; i.e. "January"<br>
j - day of the month without leading zeros; i.e. "1" to "31"<br>
l (lowercase 'L') - day of the week, textual, long; i.e. "Friday"<br>
m - month; i.e. "01" to "12"<br>
M - month, textual, 3 letters; i.e. "Jan"<br>
n - month without leading zeros; i.e. "1" to "12"<br>
S - English ordinal suffix, textual, 2 characters; i.e. "th", "nd"<br>
t - number of days in the given month; i.e. "28" to "31"<br>
w - day of the week, numeric, i.e. "0" (Sunday) to "6" (Saturday)<br>
Y - year, 4 digits; i.e. "1999"<br>
y - year, 2 digits; i.e. "99"<br>
z - day of the year; i.e. "0" to "365"<br>
</div>
<?php
echo "<img src=1.gif height=10><br>"; break;

   // date added
   case 'dateadd': echo "The code <b>$thecode</b> will be replaced by the date the user was added to ListMail.<br><img src=1.gif height=10><br>";
echo "<span class=table_inside_small><u>Recognized characters in string:</u><br></span>";
?>
<div style="font: 9pt courier new">
d - day of the month, 2 digits with leading zeros; i.e. "01" to "31"<br>
D - day of the week, textual, 3 letters; i.e. "Fri"<br>
F - month, textual, long; i.e. "January"<br>
j - day of the month without leading zeros; i.e. "1" to "31"<br>
l (lowercase 'L') - day of the week, textual, long; i.e. "Friday"<br>
m - month; i.e. "01" to "12"<br>
M - month, textual, 3 letters; i.e. "Jan"<br>
n - month without leading zeros; i.e. "1" to "12"<br>
S - English ordinal suffix, textual, 2 characters; i.e. "th", "nd"<br>
t - number of days in the given month; i.e. "28" to "31"<br>
w - day of the week, numeric, i.e. "0" (Sunday) to "6" (Saturday)<br>
Y - year, 4 digits; i.e. "1999"<br>
y - year, 2 digits; i.e. "99"<br>
z - day of the year; i.e. "0" to "365"<br>
</div>
<?php
echo "<img src=1.gif height=10><br>"; break;

   // list title
   case 'title': echo "The code <b>$thecode</b> will be replaced by the user's list title when email is sent.<br><img src=1.gif height=10><br>"; break;

   // user's unique id
   case 'uid': echo "The code <b>$thecode</b> will be replaced by the user's randomly generated user id code.  (Used in removal link, tracking, etc.)  You can use this with external scripts to interact with the ListMail database.  This is intended mainly for experienced programmers to use.<br><img src=1.gif height=10><br>"; break;

   // custom fields
   case (substr($typ,0,5) == 'field'): echo "The code <b>$thecode</b> will be replaced with the user's data from the chosen custom field.  You can optionally enter a default value for if the user data is not found.<br><img src=1.gif height=10><br>"; break;

   // ip address
   case 'ip': echo "The code <b>$thecode</b> will be replaced by the user's IP address, if it exists in the database.  ListMail will attempt to collect the user's IP address when they subscribe from a form.  If ListMail is unable to identify the IP, or users are entered manually, with email subscription, or the import process, \"Unknown\" will be shown.<br><img src=1.gif height=10><br>"; break;

   // refurl
   case 'refurl': echo "The code <b>$thecode</b> will be replaced with the user's referring URL, if it exists in the database.  ListMail will attempt to collect the referring URL, or, the exact page the user used to subscribe, when a subscription is made from a form.  If ListMail is unable to identify the referring URL, \"Unknown\" will be shown.  If user's are entered via email subscription, \"Email\" will be shown.   If imported, \"Imported\" will be shown.  And finally, if you manually add a user from within ListMail, \"Manually Added\" will be shown.<br><img src=1.gif height=10><br>"; break;

   // link2html
   case 'link2html': echo "The code <b>$thecode</b> is replaced by an automatically generated link to a page showing the current message's HTML.  Message codes are processed on this page.  You can set text that appears above the link and also set the title of the HTML link that is displayed by this code.  The text is optional.  If you leave it blank, just the link will be output by this code.<br><img src=1.gif height=10><br>
<span class=table_inside_small><u>Example of Output</u><br><img src=1.gif height=5><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=50% valign=top><span class=table_inside_small><b>Text Version:</b><br>";
  if($data1) echo nl2br($data1)."<br>";
   echo "http://".getdomain().getdir()."/showpage.php?u=1a2b3c4&m=123&nbsp;</span></td><td width=50% valign=top><span class=table_inside_small><b>HTML Version:<br></b>";
   if($data1) echo nl2br($data1)."<br>";
   echo "<a href=http://".getdomain().getdir()."/showpage.php?u=1a2b3c4&m=123>$data2</a></td></tr></table><img src=1.gif height=10><br>"; break;
   
     // numsubs
   case 'numsubs':    echo "The code <b>$thecode</b> is replaced with the current number of subscribers on the user's list.  You can optionally specify an offset to fudge the results, a little :-)<br>
<img src=1.gif height=10><br>";
    break;

  }  // end top text begin bottom boxes

  echo "</span>\n";

  // all types except rem
  if($typ=='remove'){
   if($data5=='1' || (!$data3 && !$data4 && !$data5)) $d51 = ' selected'; else $d51 = '';
   if($data5=='2' || (!$data5 && ($data3 || $data4))) $d52 = ' selected'; else $d52 = '';
   if($data5=='3') $d53 = ' selected'; else $d53 = '';
   if($data5=='4') $d54 = ' selected'; else $d54 = '';
   if($data5=='5') $d55 = ' selected'; else $d55 = '';
   echo "<table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Code !name</span><br>
   <input type=text class=xbox name=cname value=\"".htmlspecialchars($ref)."\"><br></td><td width=10><img src=1.gif width=10><br></td><td><span class=table_inside_small>Link Style</span><br><select class=xbox name=data5>";
   if(!$data3 && !$data4) echo "<option value=1$d51>rem.php?u=1a2b3c4";
   echo "<option value=2$d52>rem.php?c=$c&u=1a2b3c4<option value=3$d53>rem.php?u=$c,1a2b3c4<option value=4$d54>rem.php?x=$c&l=2&e=email@addr.com<option value=5$d55>rem.php?x=$c,2,email@addr.com</select><br></td></tr></table>
   <img src=1.gif height=10><br>";
  } elseif($typ<>'date'&&$typ<>'dateadd'&&$typ<>'numsubs') {
   echo "   <span class=table_inside_small>Code !name</span><br>
   <input type=text class=xbox name=cname value=\"".htmlspecialchars($ref)."\"><br>
   <img src=1.gif height=10><br>";
  }

  // extra stuff for certain types
  switch($typ){
   case (substr($typ,0,5) == 'field'): echo "<span class=table_inside_small>Default output (optional)</span><br>
    <input type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\"><br>
    <imt src=1.gif height=10><br>\n";
    break;

   case 'fname':
    if($data2) $data2chk = ' checked'; else $data2chk = '';
    echo "<span class=table_inside_small>No first name output (optional)</span><br>
    <input type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\"><br>
    <img src=1.gif height=10><br>
    <input type=checkbox class=chkbox_left name=data2 value=1$data2chk><span class=table_inside_small> Capitalize, ie \"John\"</span><br>
    <img src=1.gif height=10><br>\n";
    break;

   case 'lname':
    if($data2) $data2chk = ' checked'; else $data2chk = '';
    echo "<span class=table_inside_small>No last name output (optional)</span><br>
    <input type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\"><br>
    <img src=1.gif height=10><br>
    <input type=checkbox class=chkbox_left name=data2 value=1$data2chk><span class=table_inside_small> Capitalize, ie \"Doe\"</span><br>
    <img src=1.gif height=10><br>\n";
    break;

   case 'fullname':
    if($data2) $data2chk = ' checked'; else $data2chk = '';
    echo "<span class=table_inside_small>No name output (optional)</span><br>
    <input type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\"><br>
    <img src=1.gif height=10><br>
    <input type=checkbox class=chkbox_left name=data2 value=1$data2chk><span class=table_inside_small> Capitalize, ie \"John Doe\"</span><br>
    <img src=1.gif height=10><br>\n";
    break;

   case 'remove':
       if($data3) $data3chk = ' checked'; else $data3chk = '';
       if($data4) { $dummy2chk = ' checked'; $dummychk = ''; } else { $dummy2chk = ''; $dummychk = ' checked'; }

    echo "<span class=table_inside_small>Remove Text (optional, a line break or <b>&lt;br&gt;</b> and the remove link is added to this when code is used)</span><br>
<textarea class=xbox name=data1 cols=90 rows=4>$data1</textarea><br>
<img src=1.gif height=10><br>
<span class=table_inside_small>Remove HTML Link Title</span><br>
<input type=text class=xbox size=40 name=data2 value=\"".htmlspecialchars($data2)."\"><br>
<img src=1.gif height=10><br>
<input type=checkbox class=chkbox_left name=data3 value=1$data3chk><span class=table_inside_small> One-click Remove (No \"Remove Confirm\" Custom HTML, just \"Remove Success\")</span><br>

<input type=checkbox class=chkbox_left name=dummy value=1$dummychk onclick=\"if(this.checked){ code_frm.dummy2.checked=0; } else { code_frm.dummy2.checked=1; code_frm.data4.focus(); } return true;\"><span class=table_inside_small> Remove user from list mailed-to only</span><br>

<input type=checkbox class=chkbox_left name=dummy2 value=1$dummy2chk onclick=\"if(this.checked){ code_frm.dummy.checked=0; code_frm.data4.focus(); } else { code_frm.dummy.checked=1; } return true;\"><span class=table_inside_small> Remove user from multiple lists.  Users are removed from the mailed-to list, utilizing it's Custom HTML, as well as these lists (separate by commas, * = all) </span><input type=text class=xbox size=6 name=data4 value=\"".htmlspecialchars($data4)."\"><br><img src=1.gif height=10><br>
   ";
    break;

   case 'confirm':
    echo "<span class=table_inside_small>Confirm Text (optional, a line break <b>&lt;br&gt;</b> and the confirmation link is added to this when code is used)</span><br>
<textarea class=xbox name=data1 cols=90 rows=4>$data1</textarea><br>
<img src=1.gif height=10><br>
<span class=table_inside_small>Confirmation HTML Link Title</span><br>
<input type=text class=xbox size=40 name=data2 value=\"".htmlspecialchars($data2)."\"><br>
   <img src=1.gif height=10><br>";
    break;

   case 'remcnfht':
    echo "<span class=table_inside_small>Remove Button Text</span><br>
<input type=text class=xbox name=data1 cols=90 value=\"".htmlspecialchars($data1)."\"><br>
<img src=1.gif height=10><br>";
    break;

   case 'text':
    echo "<span class=table_inside_small>Custom Text (Max 65000+ chars)</span><br>
   <textarea class=xbox name=data1 style=\"width: 510px\" rows=8>$data1</textarea><br>
   <img src=1.gif height=10><br>";
   if($data2=='1') $d2chk = ' checked'; else $d2chk = '';
   echo "<input class=chkbox name=data2 type=checkbox value=1$d2chk> <span class=table_inside_normal>Do not translate line breaks to &lt;br&gt; in HTML messages.<br>
   <img src=1.gif height=10><br>";
    break;

   case 'dateadd':
   case 'date':
    if(!$data2 || !is_numeric($data2)) $data2 = '0'; if(!$data3 || !is_numeric($data3)) $data3 = '0'; if(!$data4 || !is_numeric($data4)) $data4 = '0';
    $datex = date($data1,mktime($dar[0],$dar[1],$dar[2],$dar[3]+$data3,$dar[4]+$data2,$dar[5]+$data4));
    echo "<table border=0 cellspacing=0 cellpadding=0><tr><td valign=top class=table_inside_small>Code !name<br>
   <input type=text class=xbox name=cname value=\"".htmlspecialchars($ref)."\"><br></td><td width=10><img src=1.gif height=10><br></td>
    <td valign=top class=table_inside_small>Date Format String (ie. <b>m/d/y</b>)<br>
    <input type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\" size=30><br></td><td width=10><img src=1.gif height=10><br></td><td valign=top class=table_inside_small>Offset (ie. <b>0</b>,<b>5</b>,<b>-5</b>)<br>Days <input size=2 type=text class=xbox name=data2 value=\"".htmlspecialchars($data2)."\" style=\"width: 24px\"> Months <input size=2 type=text class=xbox name=data3 value=\"".htmlspecialchars($data3)."\" style=\"width: 24px\"> Years <input size=2 type=text class=xbox name=data4 value=\"".htmlspecialchars($data4)."\" style=\"width: 24px\"></td></tr></table>
    <img src=1.gif height=10><br>";
    if($data1) echo "    <span class=table_inside_small>Example</span><br>
    <font size=2 face=helvetica><b>$datex</b></font><br>
    <img src=1.gif height=10><br>";
    break;

   case 'link2html':
    echo "<span class=table_inside_small>Text above link (optional, a line break <b>&lt;br&gt;</b> and the link is added to this when code is used)</span><br>
<textarea class=xbox name=data1 cols=90 rows=4>$data1</textarea><br>
<img src=1.gif height=10><br>
<span class=table_inside_small>HTML Link Title</span><br>
<input type=text class=xbox size=40 name=data2 value=\"".htmlspecialchars($data2)."\"><br>
   <img src=1.gif height=10><br>";
    break;
    
   case 'numsubs':
    if(!$data1||!is_numeric($data1)) $data1 = 0;
    echo "<table border=0 cellspacing=0 cellpadding=0><tr><td valign=top class=table_inside_small>Code !name<br>
   <input type=text class=xbox name=cname value=\"".htmlspecialchars($ref)."\"><br></td><td width=10><img src=1.gif height=10><br></td>
   <td valign=top class=table_inside_small>Offset (ie. <b>500</b>,<b>-500</b>)<br><input size=2 type=text class=xbox name=data1 value=\"".htmlspecialchars($data1)."\" style=\"width: 48px\"><br></td></tr></table>
    <img src=1.gif height=10><br>";
   
  }
  if($add){ $btxt = 'Cancel'; $bsiz = '60'; }
  if($edit){ $btxt = 'Back to Codes'; $bsiz = '100'; }

  echo "
   <input type=hidden name=list value=$list>
   <!-- <input type=hidden name=typ value=$typ> -->
   <input type=hidden name=save value=>
   <input type=hidden name=codes value=1>
   <input type=hidden name=id value=$id>
   <input type=hidden name=add value=$add>
   <input type=hidden name=edit value=$edit>
   <input type=hidden name=noreload value=$noreload>";
if(!$origname) $origval = htmlspecialchars($ref); else $origval = htmlspecialchars($origname);
 echo "   <input type=hidden name=origname value=\"".htmlspecialchars($origval)."\">
   <table border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td width=1><input type=submit class=button value=\"Save Code\" onclick=\"code_frm.save.value=1; code_frm.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 100px\"></td>
     <td width=3><img src=1.gif width=3></td>
     <td><input type=button class=button value=\"$btxt\" onclick=\"hopto('./editconf.php?list=$list&codes=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: ".$bsiz."px\"></td></form>
    </tr>
   </table>
  </td>
 </tr>
</table>
<img src=./1.gif height=10><br>\n";
  if($xtxt) echo "<span class=status_text>$xtxt</span><br>";
  if($errtxt) echo "<span class=status_text>$errtxt</span><br>";
  echo "<script language=javascript>window.status='$logtxt';</script>";
  exit;
 }



/* ************************************************************************************************ */

 if($qsave){
  $ref = addslashes($ref);
  // check
  if($list=='all') $xlist = '0'; else $xlist = $list;
  if($origlist <> $tlist) $xlist = $tlist;
  if(($ref <> $origref) || ($origlist <> $tlist)) $checkdupe = 1; else $checkdupe = '';
  $dupe = '';
  if($checkdupe){
   if(@mysql_num_rows(@mysql_query("select id from $dtable where ref like '$ref' and list = '$xlist'")) > 0) $dupe = 1; else $dupe = '';
  }
  if($ref <> '' && !$dupe){
   $cmd = "update $dtable set list = '$tlist', ref = '$ref' where id = '$id';";
   mysql_query($cmd);
   $logtxt = 'Message code saved.';
   if($origlist <> $tlist) $logtxt = 'Message code moved to list $tlist';
  } else {
   if($ref=='') $logtxt = 'Blank code name, not saved!';
   if($dupe){
    $logtxt = 'Duplicate code name, not saved!';
    if($origlist <> $tlist) $logtxt = 'Duplicate code name found on target list, not saved!';
   }
  }
 }
 if($del){
  mysql_query("delete from $dtable where id = '$id'");
  $logtxt = "Message code deleted.";
 }

 // main page
 if(!$edit && !$add){
  // get fields
  $cmd = "select field1,field2,field3,field4,field5,field6,field7,field8,field9,field10 from $ltable where listnum = '$list'";
  $lrow = mysql_query($cmd);
  list($field1,$field2,$field3,$field4,$field5,$field6,$field7,$field8,$field9,$field10) = @mysql_fetch_row($lrow);
  for($i=1;$i<=10;$i++){ list(${"field".$i},$null,$null0)=explode('/*LMP*/',${"field".$i}); }
  $fields = array('',$field1,$field2,$field3,$field4,$field5,$field6,$field7,$field8,$field9,$field10);
  while(list($key,$val)=each($fields)){
   if($val) {
    $fields[$key] = "Custom Field #$key (" . $val . ")";
   } else {
    $fields[$key] = "Custom Field #$key";
   }
  }

  if($list=='all') $title = 'Global Message Codes'; else $title = "List $list Message Codes";
  adminheader('LM: Edit Message Codes',$title,'');
  echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

  listmenu($list,'confcode');

  $cmd = "select id,list,ref,typ from $dtable where list = '$clist' order by id";
  $drows = @mysql_query($cmd);

  if(@mysql_num_rows($drows)==0){ $pad = '_nopad'; }

  echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";
  echo " <table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
  <tr>
   <td class=table_head_bg$pad>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>
    <span class=table_head_text>";
  if($list=='all') echo "Global Message Codes"; else echo "List $list Message Codes";
  echo "</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('message_codes')."');\">Help</a><br></td></tr></table>
   </td>
  </tr>\n";

  if(mysql_num_rows($drows)<>0){
   echo "  <tr>
   <td>
    <!-- pad -->
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
    <table width=100% border=0 cellspacing=0 cellpadding=3>
     <tr class=bg2>
      <td><img src=1.gif width=2><span class=table_head_col>List</span></td>
      <td><span class=table_head_col>Message Code</span></td>
      <td><span class=table_head_col>Ref<br></span></td>
      <td><span class=table_head_col>Type</span></td>
      <td><img src=1.gif height=1></td>
      <td><img src=1.gif height=1></td>
      <td><img src=1.gif height=1></td>
     </tr>\n";
   while(list($id,$clist,$ref,$typ)=@mysql_fetch_row($drows)){
    echo "\n     <tr bgcolor=white>
      <td><form name=qform$id method=post><input type=hidden name=qsave value=1><input type=hidden name=codes value=1><input type=hidden name=origlist value=\"$clist\"><input type=hidden name=origref value=\"".htmlspecialchars($ref)."\"><input type=hidden name=id value=\"$id\"><img src=1.gif width=2><select class=xbox name=tlist onchange=\"if(qform$id.tlist.value=='0') xtra = ' (Global)'; else xtra = ''; if(confirm('Are you sure you want to move this message code to List '+qform$id.tlist.value+xtra+'?')) qform$id.submit(); else { qform$id.tlist.value='$clist'; return false; }\"><option value=\"0\">All";

    $lrows = @mysql_query("select listnum from $ltable where 1 order by listnum");
    while(list($listnum) = @mysql_fetch_row($lrows)){
     if($clist==$listnum && $clist <> '0') $sel = " selected"; else $sel = "";
     echo "<option value=".$listnum.$sel.">$listnum\n";
    }

    echo "</select></td>
      <td><span class=code_example>".$keych.$ref."</span><br></td>
      <td><input type=text class=xbox name=ref value=\"".htmlspecialchars($ref)."\" style=\"width: 150px\" size=20></td>
      <td><span class=code_type>".getctype($typ)."</span></td>
      <td width=1><input type=submit class=button value=\"Save\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 38px\"></td></form>
      <td width=1><input class=button type=button onclick=\"hopto('./editconf.php?list=$list&codes=1&edit=1&id=$id&type=$typ')\" value=Edit onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px\"></td>
      <td width=1><input class=button type=button onclick=\"if(confirm('Are you sure you want to delete this message code?')) hopto('./editconf.php?list=$list&codes=1&del=1&id=$id')\" value=\"Delete\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px\"></td>
     </tr>\n";
   }
   echo "    </table>
    </td></tr></table><!-- end pad -->
   </td>
  </tr>
 </table>\n";
  } else {
   echo "
   </td>
  </tr>
 </table>\n";

   $crows = mysql_query("select id from $dtable where list = '0'");
   $crows = @mysql_num_rows($crows);
   if($crows>0){ $glob = " &nbsp;There are $crows <a href=./editconf.php?list=all&codes=1 class=status_link>Global Codes</a>."; } else $glob = '';

   echo "<img src=1.gif height=5><br><div align=center><span class=status_text>There are no message codes for this list.$glob</span></div>";
  }
  echo "<img src=1.gif height=3><br>
 <table border=0 cellspacing=0 cellpadding=0><tr><td><input class=button type=button onclick=\"hopto('./editconf.php?list=$list&codes=1&add=1');\" value=\"Add Code\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 105px\"></td>\n";
if($list<>'all') echo "<td width=3><img src=1.gif width=3></td><td><input class=button type=button onclick=\"hopto('./editconf.php?list=all&codes=1');\" value=\"Global Codes\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 105px\"></td>";
  echo "</tr></table>";
  echo "<img src=1.gif height=10><br><center><span class=table_inside_small>Note: List specific codes with the same name as a global code will take precedence.</span><br>";
 }
  echo "
   </td>
  </tr>
 </table>
   </td>
 </tr>
</table>
<script language=javascript>window.status='$logtxt';</script>";
 exit;
}

if (!$installed || $doinst){
 ob_start();
 // Begin newly pasted install sequence
 adminheader('LM: Install DB','Edit Database','');

 echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,'config');

 echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";

echo "<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=table_head_bg_nopad><table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>ListMail Install Script</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('install_db')."');\">Help</a><br></td></tr></table>
</td></tr></table>
    <img src=1.gif height=3><br>";

 // page buttons
 echo "<table border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
<!-- main -->
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td>
   <form name=iform method=post value=$PHP_SELF>
   <input type=hidden name=doinstall value=yes><input type=hidden name=doinst value=1>
   <input class=button type=submit name=isub value=\"Install Database\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\">
 </td></form>
 <td width=3><img src=1.gif width=3></td>
 <td>
  <form name=rform method=post onSubmit=\"if (!confirm('This will delete and then recreate all ListMail database tables.  All of your lists, users, and followup message settings will be lost!  Are you sure you want to do this?')) return false\">
  <input type=hidden name=doreset value=yes><input type=hidden name=doinst value=1>
  <input class=button type=submit name=rsub value=\"Reset Database\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\">
 </td></form>
 <td width=3><img src=1.gif width=3></td>
 <td>
  <form name=dform method=post value=$PHP_SELF onSubmit=\"if (!confirm('This will completely delete all ListMail database information and will not create new tables.  ListMail will not work after using this!  Are you sure you want to do this?')) return false\">
  <input type=hidden name=douninst value=yes><input type=hidden name=doinst value=1>
  <input class=button type=submit name=usub value=\"Uninstall Database\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 120px\">
 </td></form>
</tr>
</table>
  </td>
 </tr>
</table>\n";
if(!$doinstall&&!$doreset&&!$douninst) echo "<div class=table_inside_small style=\"margin-top: 4px\" align=left>Installation ID: $instid</div>\n";
echo "<img src=1.gif height=10><br>\n";

 // status messages and processing
 echo "<table border=0 cellspacing=0 cellpadding=2><tr><td><span class=table_inside_normal>\n";

 if (($demo <> 'yes') && (($douninst == 'yes') || ($doreset == 'yes'))){
 // remove tables
 $cmd="DROP TABLE IF EXISTS $ctable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ctable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $ktable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ktable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $ltable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ltable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $ftable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ftable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $fstable;";
 mysql_query($cmd) or die ("**Error removing table <b>$fstable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $utable;";
 mysql_query($cmd) or die ("**Error removing table <b>$utable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $itable;";
 mysql_query($cmd) or die ("**Error removing table <b>$itable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $vtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$vtable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $stable;";
 mysql_query($cmd) or die ("**Error removing table <b>$stable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $htable;";
 mysql_query($cmd) or die ("**Error removing table <b>$htable</b>! ".mysql_error());
 $cmd="DROP TABLE IF EXISTS $ttable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ttable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $dtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$dtable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $qtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$qtable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $otable;";
 mysql_query($cmd) or die ("**Error removing table <b>$otable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $ptable;";
 mysql_query($cmd) or die ("**Error removing table <b>$ptable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $setable;";
 mysql_query($cmd) or die ("**Error removing table <b>$setable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $sedtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$sedtable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $chtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$chtable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $chdtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$chdtable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $etable;";
 mysql_query($cmd) or die ("**Error removing table <b>$etable</b>! ".mysql_error());
 $cmd = "DROP TABLE IF EXISTS $edtable;";
 mysql_query($cmd) or die ("**Error removing table <b>$edtable</b>! ".mysql_error());

 echo "All tables removed.<br>";
}

// create tables
if (($demo <> 'yes') && (($douninst <> 'yes') && (($doreset  == 'yes') || ($doinstall == 'yes')))){

// config table
$cmd="CREATE TABLE $ctable (
   instid varchar(32) not null,
   admpw tinytext NOT NULL,
   daypw tinytext NOT NULL,
   daylast datetime NOT NULL,
   admmail tinytext NOT NULL,
   listmailpath tinytext NOT NULL,
   keycode tinytext NOT NULL,
   linkcode tinytext NOT NULL,
   sortopts tinytext NOT NULL,
   wrapon char(1) not null,
   wrapcols char(3) not null,
   ver varchar(5) not null,
   ktrack char(1) not null,
   errhost VARCHAR (40) not null,
   errport smallint NOT NULL,
   erraddr VARCHAR (40) not null,
   erruser VARCHAR (60) not null,
   errpass VARCHAR (20) not null,
   errchk CHAR (1) not null,
   errfrom VARCHAR (40),
   delay1 SMALLINT UNSIGNED not null,
   delay2 TINYINT (2) UNSIGNED not null,
   delay3 SMALLINT UNSIGNED not null,
   nbounce VARCHAR (6) not null,
   mthost varchar (40) NOT NULL,
   mtport smallint NOT NULL,
   mtauth char(1) NOT NULL,
   mtuser varchar (60) NOT NULL,
   mtpass varchar (99) NOT NULL,
   mtdelay smallint (5) not null,
   mtchk char(3) NOT NULL,
   mtphost varchar (40) not null,
   mtpport smallint not null,
   mtpuser varchar (60) not null,
   mtppass varchar (25) not null,
   mtpdelay tinyint not null,
   mtpchk char(1) not null,
   keepsent char(3) not null,
   keephits char(3) not null,
   keepucnf char(3) not null,
   keeplogs char(3) not null,
   notifs varchar(9) not null,
   maint tinytext not null,
   textonly text not null,
   dayopt char(1) not null
);";
echo "Creating <i>config database</i> table <b>$ctable</b> - ";
mysql_query($cmd) or die ("**Error creating $ctable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?");

$xid = calc32();

$dom = getdomain();
$d = getdir();

$cmd="INSERT INTO $ctable VALUES ('$xid','','','".date("Y-m-d H:i:s",time()-31536000)."','admin@$dom','http://www.$dom$d/','!','link_','10;desc;dadd','1','65','$current_version','','mail.$dom','110','bounce@$dom','bounce@$dom','','0','500','4','500','3:30','localhost','25','TRUE','you@$dom','','1000','1;0','mail.$dom','110','you','','19','0','30','60','10','10','1;0;0;0;0','50;5000;10','','1');";
echo "Inserting example config data.<br>";
//debug
echo $cmd;
//debug
mysql_query($cmd) or die("**Error inserting example config data.");

setcookie('LMadm1',md5($xid),0);


// link table
$cmd="CREATE TABLE $ktable (
   id mediumint(8) unsigned NOT NULL auto_increment,
   refcode varchar(255) NOT NULL,
   title tinytext NOT NULL,
   http tinytext NOT NULL,
   hits mediumint(4) unsigned NOT NULL,
   PRIMARY KEY (id),
   KEY refcode (refcode)
);";

echo "Creating <i>link database</i> table <b>$ktable</b> - ";
mysql_query($cmd) or die ("**Error creating $ktable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?");

$cmd="INSERT INTO $ktable VALUES ('','ex1','Example 1','ftp://ftp.ex.com/pub/download.zip','1');";
echo "Inserting example link data.<br>";
mysql_query($cmd) or die("**Error inserting example link data.");

// followup table
$cmd="CREATE TABLE $ftable (
   id mediumint(8) unsigned NOT NULL auto_increment,
   active tinyint(1) NOT NULL,
   list smallint unsigned NOT NULL,
   subject tinytext NOT NULL,
   content mediumtext NOT NULL,
   htcontent mediumtext NOT NULL,
   fattach tinytext NOT NULL,
   seq smallint NOT NULL,
   del smallint NOT NULL,
   cron tinytext NOT NULL,
   sset smallint unsigned NOT NULL,
   PRIMARY KEY (id),
   KEY list (list)
);";

echo "Creating <i>followup messages</i> table <b>$ftable</b><br>";
mysql_query($cmd) or die ("**Error creating $ftable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?");

// followup set table
$cmd="CREATE TABLE $fstable (
   id smallint unsigned NOT NULL auto_increment,
   name tinytext NOT NULL,
   PRIMARY KEY (id)
);";

echo "Creating <i>followup saved set</i> table <b>$fstable</b><br>";
mysql_query($cmd) or die ("**Error creating $fstable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?");

$cmd ="CREATE TABLE $ltable (
   id smallint(5) unsigned NOT NULL auto_increment,
   listnum smallint unsigned NOT NULL,
   title tinytext NOT NULL,
   sende tinytext NOT NULL,
   sendn tinytext NOT NULL,
   welsubj tinytext NOT NULL,
   welcom mediumtext NOT NULL,
   welcht mediumtext NOT NULL,
   welcf tinytext NOT NULL,
   welcact char(1) NOT NULL,
   cnfsubj tinytext NOT NULL,
   cnfmesg mediumtext NOT NULL,
   cnfht mediumtext NOT NULL,
   cnff tinytext NOT NULL,
   cnfact char(1) NOT NULL,
   remsubj tinytext NOT NULL,
   remmsg mediumtext NOT NULL,
   remht mediumtext NOT NULL,
   remf tinytext NOT NULL,
   remact char(1) NOT NULL,
   remopts varchar(10) NOT NULL,
   req1 char(1) NOT NULL,
   req2 char(1) NOT NULL,
   field1 text NOT NULL,
   field2 text NOT NULL,
   field3 text NOT NULL,
   field4 text NOT NULL,
   field5 text NOT NULL,
   field6 text NOT NULL,
   field7 text NOT NULL,
   field8 text NOT NULL,
   field9 text NOT NULL,
   field10 text NOT NULL,
   notifs varchar(9) NOT NULL,
   nmails tinytext NOT NULL,
   listopts varchar(20) NOT NULL,
   chtmlid smallint unsigned NOT NULL,
   errorid smallint unsigned NOT NULL,
   addopts varchar(100) NOT NULL,
   remote tinyint(1) NOT NULL DEFAULT '0',
   remotedb text NOT NULL,
   remoteuser text NOT NULL,
   remotepwd text NOT NULL,
   remotehost text NOT NULL,
   PRIMARY KEY (id),
   KEY listnum (listnum)
);";

echo "Creating <i>mailing list</i> table <b>$ltable</b> - ";
mysql_query($cmd) or die ("**Error creating $ltable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?");

//$cmd="INSERT INTO $ltable VALUES ('0', '1', 'Example Mailing List', 'news@$dom', 'Your Site Newsletter', 'Welcome!', 'Welcome to the $dom newsletter.','','','1','Confirmation','Please confirm your subscription to our mailing list by clicking the link below:\n\n!confirm\n\nThank you!','','','0','Confirmation Reminder','You subscribed to our list but did not respond to our confirmation email.  We hope you are still interested in joining us!\n\nPlease confirm your subscription to our mailing list by clicking the link below:\n\n!confirm\n\nThank you!','','','0','1;7','0','0','Example Field/*LMP*//*LMP*/Please enter your Example!','','','','','','','','','','0;0;0;0;0','','0;1;0','1','1',';;;;100;;1;1;0');";
$cmd="INSERT INTO $ltable VALUES ('0', '1', 'Example Mailing List', 'news@$dom', 'Your Site Newsletter', 'Welcome!', 'Welcome to the $dom newsletter.','','','1','Confirmation','Please confirm your subscription to our mailing list by clicking the link below:\n\n!confirm\n\nThank you!','','','0','Confirmation Reminder','You subscribed to our list but did not respond to our confirmation email.  We hope you are still interested in joining us!\n\nPlease confirm your subscription to our mailing list by clicking the link below:\n\n!confirm\n\nThank you!','','','0','1;7','0','0','Example Field/*LMP*//*LMP*/Please enter your Example!','','','','','','','','','','0;0;0;0;0','','0;1;0','1','1',';;;;100;;1;1;0',0,'','','','');";
echo "Creating example list.<br>";
mysql_query($cmd) or die("**Error inserting example list data.");

/* echo "Creating <i>log</i> table <b>$itable</b> - ";
mysql_query($cmd) or die ("**Error creating $itable! ".mysql_error(). "<br>Is the database login information in CONFIG.PHP  correct?  Did you already run install?"); */

$cmd="CREATE TABLE $utable (
   id mediumint(9) unsigned NOT NULL auto_increment,
   uid varchar(7) NOT NULL,
   list smallint unsigned NOT NULL,
   fname tinytext NOT NULL,
   lname tinytext NOT NULL,
   email varchar (255) NOT NULL,
   user1 text NOT NULL,
   user2 text NOT NULL,
   user3 text NOT NULL,
   user4 text NOT NULL,
   user5 text NOT NULL,
   user6 text NOT NULL,
   user7 text NOT NULL,
   user8 text NOT NULL,
   user9 text NOT NULL,
   user10 text NOT NULL,
   cseq smallint NOT NULL,
   cdel smallint NOT NULL,
   cnf char(1) NOT NULL,
   dateadd datetime NOT NULL,
   ipaddr varchar (15) not null,
   refurl varchar (75) not null,
   htmail CHAR (1) NOT NULL,
   bounces TINYTEXT not null,
   PRIMARY KEY (id),
   KEY list (list),
   KEY uid (uid),
   KEY email (email),
   KEY cnf (cnf)
);";
echo "Creating <i>user database</i> table <b>$utable</b> - ";
mysql_query($cmd) or die ("**Error creating $utable: ".mysql_error());

$today = date('Y-m-d');
$uniq_str = unique_id(7);
$cmd="INSERT INTO $utable VALUES ('0','$uniq_str','1','User','Example','you@$dom','','','','','','','','','','','1','0','1','$today','unknown','example user','1','0');";
echo "Adding example user to list.<br>";
mysql_query($cmd) or die ("**Error inserting example user.");

// saved messages table
$cmd = "CREATE TABLE $stable (
   id smallint(5) unsigned NOT NULL auto_increment,
   kind CHAR (1) not null,
   uid mediumint(9) not null,
   subject tinytext NOT NULL,
   content mediumtext NOT NULL,
   htcontent mediumtext NOT NULL,
   fattach tinytext NOT NULL,
   PRIMARY KEY (id),
   KEY kind (kind)
);";

echo "Creating <i>saved messages</i> table <b>$stable</b> - ";
mysql_query($cmd) or die ("**Error creating $stable");

echo "Inserting example saved message.<br>";
$cmd = "insert into $stable values ('','s','','Example Saved Message','Example Saved Message','','');";
mysql_query($cmd);

// lm_hits
$cmd = "CREATE TABLE $htable (
   id mediumint(9) unsigned NOT NULL auto_increment,
   uid mediumint(9) unsigned NOT NULL,
   kid smallint(5) unsigned NOT NULL,
   date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   PRIMARY KEY (id)
);";

echo "Creating <i>hit tracking</i> table <b>$htable</b><br>";
mysql_query($cmd) or die ("**Error creating $htable");

// lm_sent
$cmd = "CREATE TABLE $ttable (
   id smallint(5) unsigned NOT NULL auto_increment,
   subject tinytext NOT NULL,
   content mediumtext NOT NULL,
   htcontent mediumtext NOT NULL,
   fattach tinytext NOT NULL,
   datesent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   msgtype char(1) NOT NULL,
   numsent mediumint unsigned DEFAULT '0' NOT NULL,
   sentto tinytext NOT NULL,
   PRIMARY KEY (id)
);";
echo "Creating <i>sent messages</i> table <b>$ttable</b><br>";
mysql_query($cmd) or die ("**Error creating $ttable");

$cmd = "CREATE TABLE $dtable (
  id smallint(5) unsigned NOT NULL auto_increment,
  list smallint(5) unsigned NOT NULL default '0',
  ref varchar(40) NOT NULL default '',
  typ varchar(10) NOT NULL default '',
  data1 text NOT NULL,
  data2 text NOT NULL,
  data3 text NOT NULL,
  data4 text NOT NULL,
  data5 text NOT NULL,
  PRIMARY KEY  (id),
  KEY list (list),
  KEY ref (ref),
  KEY typ (typ)
);";
echo "Creating <i>message codes</i> table <b>$dtable</b> - ";
mysql_query($cmd) or die ("**Error creating $dtable");

echo "Inserting default global codes.<br>";
// insert default message codes
$cmd = "insert into $dtable (list,ref,typ) values('0','fname','fname');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ) values('0','lname','lname');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ) values('0','fullname','fullname');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ) values('0','email','email');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1,data2,data5) values('0','remove','remove','Not interested in receiving more?  Click here:','Unsubscribe','1');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1) values('0','button','remcnfht','Remove Me');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1,data2) values('0','confirm','confirm','','Confirm');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1,data2) values('0','mytext','text','Example custom text code.','<span style=\"font: 10pt helvetica\">Example <a href=#>custom</a> text!</span>');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1) values('0','date','date','m/d/y');";
mysql_query($cmd);
$cmd = "insert into $dtable (list,ref,typ,data1) values('0','date2','date','l, F jS, Y');";
mysql_query($cmd);

$cmd = "CREATE TABLE $qtable (
  id smallint(5) unsigned NOT NULL auto_increment,
  type char(1) NOT NULL,
  date date NOT NULL default '0000-00-00',
  subject tinytext NOT NULL,
  message mediumtext NOT NULL,
  htmessage mediumtext NOT NULL,
  fattach tinytext NOT NULL,
  list tinytext NOT NULL ,
  PRIMARY KEY (id),
  KEY date (date)
);";
echo "Creating <i>scheduled messages</i> table <b>$qtable</b><br>";
mysql_query($cmd) or die ("**Error creating $qtable");

// no example scheduled..

$cmd = "CREATE TABLE $otable (
  id varchar(32) NOT NULL default '',
  bat varchar(6) NOT NULL default '',
  battype char(1) NOT NULL default '',
  mtype char(1) NOT NULL default '',
  lid smallint unsigned NOT NULL default '',
  uid mediumint(9) unsigned NOT NULL default '0',
  mid varchar(10) NOT NULL default '',
  xtra varchar(10) NOT NULL default '',
  PRIMARY KEY  (id)
)";

echo "Creating <i>outbox</i> queue table <b>$otable</b><br>";
mysql_query($cmd) or die ("**Error creating $otable");

$cmd = "CREATE TABLE $ptable (
  id mediumint unsigned NOT NULL auto_increment,
  batid varchar(6) NOT NULL,
  qtype char(1) NOT NULL,
  formid varchar(14) NOT NULL,
  started datetime NOT NULL,
  lastact datetime NOT NULL,
  report TEXT NOT NULL,
  completed char(1) NOT NULL,
  PRIMARY KEY (id),
  KEY batid (batid)
)";
echo "Creating <i>queue protect</i> table <b>$ptable</b><br>";
mysql_query($cmd) or die ("**Error creating $ptable");

// no example queued..


// selection table

$cmd = "CREATE TABLE $setable (
  id smallint(5) unsigned NOT NULL auto_increment,
  name tinytext NOT NULL,
  descr tinytext NOT NULL,
  fdupe char(1) NOT NULL default '',
  PRIMARY KEY  (id))";

echo "Creating <i>selection</i> table <b>$stable</b><br>";
mysql_query($cmd) or die ("**Error creating $stable");

// rule table

$cmd = "CREATE TABLE $sedtable (
  id mediumint(8) unsigned NOT NULL auto_increment,
  rsub char(1) NOT NULL default '',
  rsid smallint(5) unsigned NOT NULL default '0',
  cond char(1) NOT NULL default '',
  stat char(1) NOT NULL default '',
  list smallint(6) unsigned NOT NULL default '0',
  qtype char(1) NOT NULL default '',
  field char(1) NOT NULL default '',
  q tinytext NOT NULL,
  qdate tinytext NOT NULL,
  PRIMARY KEY (id),
  KEY rsid (rsid)
)";

echo "Creating <i>selection rules</i> table <b>$sedtable</b><br>";
mysql_query($cmd) or die ("**Error creating $sedtable");

// custom html table
$cmd = "CREATE TABLE $chtable (
 id smallint unsigned NOT NULL auto_increment,
 name tinytext NOT NULL,
 PRIMARY KEY (id)
)";
echo "Creating <i>custom HTML</i> table <b>$chtable</b><br>";
mysql_query($cmd) or die ("**Error creating $chtable");

// custom html data table
$cmd = "CREATE TABLE $chdtable (
 id smallint unsigned NOT NULL,
 typ varchar(12) NOT NULL,
 html mediumtext NOT NULL,
 url text NOT NULL,
INDEX (id),
INDEX (typ)
)";
echo "Creating <i>custom HTML data</i> table <b>$chdtable</b><br>";
mysql_query($cmd) or die ("**Error creating $chdtable");

// custom html default data
mysql_query("insert into $chtable values('','Default HTML')");
$iid = mysql_insert_id();
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'subscribe', '<html>\r\n<head>\r\n<title>Welcome to the list!</title>\r\n</head>\r\n<body bgcolor=white>\r\nThanks for signing up, !fname.<br>\r\nYour email address <u>!email</u> will be kept safe!<br><br>You will receive an introductory email in a moment.<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die("Error: ".mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'suberror', '<html>\r\n<head>\r\n<title>Signup Error</title>\r\n</head>\r\n<body bgcolor=white>\r\nThere were errors with your subscription to our mailing list.<br><br>\r\nThe error messages are as follows:<br>\r\n!data<br>\r\nPlease press the back button on your browser and re-enter your information.<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die("Error: ".mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'remove', '<html>\r\n<head>\r\n<title>Remove Confirmation Page</title>\r\n<style type=text/css rel=stylesheet> <!--\r\n.confirm_button: { border: 1px solid #000000; font: 10pt arial; }\r\n--> </style>\r\n\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour name was successfully retrieved from our database.  We apologize if our mailings were an inconvenience to you and hope you are able to find what you were looking for in the near future.  Please confirm that you would like your email address (!email) removed from our mailing list by clicking the button below.<br>\r\n!button','');";
mysql_query($cmd) or die("Error: ".mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'remerror', '<html>\r\n<head>\r\n<title>Remove Error</title>\r\n</head>\r\n<body bgcolor=white>\r\nThere were errors removing you from our mailing list(s):<br><br>!data\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die("Error: ".mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid','remsuccess', '<html>\r\n<head>\r\n<title>Removal Success Page</title>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nYour email address (!email) was successfully removed from our database.  Be assured you will never receive any more email from this list server.  We\'re sorry you decided to leave and wish you the best in the future!<br><br>\r\nManagement<br>\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die("Error: ".mysql_error());
$cmd = "INSERT INTO $chdtable VALUES ( '$iid', 'preconfirm', '<html>\r\n<head>\r\n<title>Confirmation Page</title>\r\n</head>\r\n<body bgcolor=white>\r\n!fname !lname,<br><br>\r\nBefore we add you to our database, we must verify your email address.  Please check your email for the confirmation message and click the link provided to finalize your subscription.  <br>\r\n\r\n</body>\r\n</html>','');";
mysql_query($cmd) or die("Error: ".mysql_error());

// error messages table
$cmd = "CREATE TABLE $etable (
 id smallint unsigned NOT NULL auto_increment,
 name tinytext NOT NULL,
 PRIMARY KEY (id)
);";
echo "Creating <i>error message</i> table <b>$etable</b><br>";
mysql_query($cmd) or die ("**Error creating $etable");

// error message data table
$cmd = "CREATE TABLE $edtable (
 id smallint unsigned NOT NULL,
 typ varchar(12) NOT NULL,
 data text NOT NULL,
INDEX (id),
INDEX (typ)
);";
echo "Creating <i>error message data</i> table <b>$edtable</b><br>";
mysql_query($cmd) or die ("**Error creating $edtable");

// error msg default data
mysql_query("insert into $etable values('','Default Errors')");
$iid = mysql_insert_id();
mysql_query("insert into $edtable values('$iid','fname_req','You didn\'t enter your first name.');");
mysql_query("insert into $edtable values('$iid','lname_req','You didn\'t enter your last name.');");
mysql_query("insert into $edtable values('$iid','email_req','You didn\'t enter an email address.');");
mysql_query("insert into $edtable values('$iid','email_bad','The email address you entered is in an incorrect format.');");
mysql_query("insert into $edtable values('$iid','email_dupe','That email address is already subscribed to this list!');");
mysql_query("insert into $edtable values('$iid','rem_nf','User not found in database. You should not receive any more email.');");
mysql_query("insert into $edtable values('$iid','l_closed','You may not subscribe to or unsubscribe from a closed list.  Please contact your administrator.');");

// end create tables

echo "<br>Done!  Everything worked!  Now you just need to make sure to schedule the daily mail script for execution. Details are in the Help menu.<br><br>First, <a class=table_inside_normal href=./editconf.php>go to the Config Menu and set up your admin options.</a><br></span>";

} // endif create

 echo "</td></tr></table>
 <script language=javascript>window.status='$logtxt';</script>
</body>
</html>";
} // !$installed

// test mail settings
if($testmail){

 // get config settings
 $cmd = "select admmail,errhost,errport,erraddr,erruser,errpass,errchk,errfrom,mthost,mtport,mtauth,mtuser,mtpass,mtdelay,mtchk,mtphost,mtpport,mtpuser,mtppass,mtpdelay,mtpchk from $ctable where 1";
 $crow = mysql_query($cmd);
list($admmail,$errhost,$errport,$erraddr,$erruser,$errpass,$errchk,$errfrom,$mthost,$mtport,$mtauth,$mtuser,$mtpass,$mtdelay,$mtchk,$mtphost,$mtpport,$mtpuser,$mtppass,$mtpdelay,$mtpchk) = mysql_fetch_row($crow);
 list($mtchk,$mtlog)=explode(';',$mtchk);
 $emails = explode(';',$admmail);
 $admmail = $emails[0];
 $testmail = $emails[1];
 $emails = '';

/* echo "VARS<br>admin email = $admmail<br>
remote test email = $testmail<br><br>
bounce mailbox host = $errhost<br>
bounce mailbox port = $errport<br>
bounce mailbox addr = $erraddr<br>
bounce mailbox pass = $errpass<br>
bounce mailbox chk = $errchk<br>
bounce mailbox from = $errfrom<br><br>
SMTP host = $mthost<br>
SMTP port = $mtport<br>
SMTP AUTH = $mtauth<br>
SMTP user = $mtuser<br>
SMTP pass = $mtpass<br>
SMTP delay = $mtdelay<br>
SMTP enabled = $mtchk<br><br>
SMTP POPcheck host = $mtphost<br>
SMTP POPcheck port = $mtpport<br>
SMTP POPcheck user = $mtpuser<br>
SMTP POPcheck pass = $mtppass<br>
SMTP POPcheck delay = $mtpdelay<br>
SMTP POPcheck chk = $mtpchk<br><br>";
*/

 pophead('Testing Mail Settings');

 echo "<span style=\"font-family: verdana; font-size: 9pt; color: black;\"><br>Testing mail servers..<br>";
 echo "<br><b>[Bounce Handling]</b><br>";

 echo "Is enabled? &nbsp;";
 if($errchk=='1') {
  echo "<b>Yes</b>, Using a mailbox<br>";
  echo "Connecting.. "; flush();
   // if($errport=='110') $errport .= '/pop3';
   // if($errport=='993') $errport .= '/imap/ssl';

   // output server responses
   // $debug = 1;

   $asock = fsockopen($errhost, $errport);
   if(!$asock){
    if($debug) echo "failed opening socket to $errhost $errport<br>";
    echo "failed opening socket to $errhost $errport<br>";
    $error = 1;
   } else $error = '';
   if(!$error){
    // connected!
    echo 'error='.$error.'<br>';
    $srvmsg = fgets($asock, 1024);echo 'msg='.$srvmsg.'<br>';
    if($debug) echo "connected! server: $srvmsg<br>";
    $lastmsg = substr($srvmsg, 0, 1);
    if ($lastmsg <> "+") $error = 1; else $error = '';echo 'error='.$error.'<br>';
    if(!$error){
     echo " <b>Connected!</b><br>Verifying username and password...";
     // send user
     fputs($asock, "USER $erruser\r\n");
     $srvmsg = fgets($asock, 1024);echo $srvmsg.'<br>';//debug
     if($debug) echo "sent user. server: $srvmsg<br>";
     $lastmsg = substr($srvmsg, 0, 1);
     if ($lastmsg <> "+") $error = 1; else $error = '';

     // send pass
     fputs($asock, "PASS $errpass\r\n");
     $srvmsg = fgets($asock, 1024);echo $srvmsg.'<br>';//debug
     if($debug) echo "sent pass. server: $srvmsg<br>";
     $lastmsg = substr($srvmsg, 0, 1);
     if ($lastmsg <> "+")$error = 1; else $error = '';
     if($error) echo " <b>Failed!</b><br>"; else {
      echo " <b>Verified!</b><br>";
      // check # of messages
      fputs($asock, "STAT\r\n");
      $srvmsg = fgets($asock, 1024);
      if($debug) echo "sent STAT. server: $srvmsg<br>";
      $lastmsg = substr($srvmsg, 0, 1);
      if ($lastmsg <> "+") $error = 1;
     }
    }
   } else { echo " can't connect to <i>$errhost</i> "; }

  if(!$error){
   echo "The bounced message mailbox is configured correctly.<br><br>";
  } else {
   echo "The bounced message mailbox is <b>not</b> configured correctly.<br><br>";
  }

 } else {
  if($errchk=='0') { echo "<b>No</b> (you should probably configure it)<br><br>"; }
  if($errchk=='2') {
   echo "<b>Yes</b>, Using bounce.cgi<br>";
   if($admmail){
    echo "Sending TESTBOUNCE email to bounce address.. ";
    if(mail($erraddr,'TESTBOUNCE','test',"Return-path: $erraddr\nFrom: \"ListMail Mail Test\" <$erraddr>")){
     echo "<b>Success!</b><br><br>";
    } else {
     echo "<b>Failed.  Try manually sending an email to your bounce address with TESTBOUNCE in the subject line.</b><br><br>";
    }
    echo "<small>If you receive an email at your \"Administrator Email Address\" bounce.cgi is configured correctly.</small><br><br>\n";
   } else {
    echo "<br><span style=\"color: red\">Error</span>: You must set your <u>Administrator Email Address</u> in the ListMail Config menu!<br><br>"; flush();
   }
  }
 }
 flush();

 // SMTP TEST

 echo "<b>[SMTP Server]</b><br>";
 echo "Is enabled? &nbsp;";
 if($mtchk=='1') {
  echo "<b>Yes</b><br>";
  flush();
  if($mtpchk=='1'){
   echo "Checking mail.."; flush();

   $asock = fsockopen($mtphost, $mtpport);
   if(!$asock) $error = 1; else $error = '';
   if(!$error){
    // connected!
    $srvmsg = fgets($asock, 1024);
    $lastmsg = substr($srvmsg, 0, 1);
    if ($lastmsg <> "+") $error = 1; else $error = '';
    if(!$error){
     echo " <b>Connected!</b><br>Verifying username and password...";
     // send user
     fputs($asock, "USER $mtpuser\r\n");
     $srvmsg = fgets($asock, 1024);
     $lastmsg = substr($srvmsg, 0, 1);
     if ($lastmsg <> "+") $error = 1; else $error = '';

     // send pass
     fputs($asock, "PASS $mtppass\r\n");
     $srvmsg = fgets($asock, 1024);
     $lastmsg = substr($srvmsg, 0, 1);
     if ($lastmsg <> "+")$error = 1; else $error = '';
     if($error) echo " <b>Failed!</b><br>"; else {
      echo " <b>Verified!</b><br>";
      // check # of messages
      fputs($asock, "STAT\r\n");
      $srvmsg = fgets($asock, 1024);
      $lastmsg = substr($srvmsg, 0, 1);
      if ($lastmsg <> "+") $error = 1;
     }
    }
   } else { echo " can't connect to <i>$mtphost</i> "; }

  }
  if(!$testmail) {
   echo "<br><span style=\"color: red\">Error</span>: You must set your <u>Remote Test Address</u> in the ListMail Config menu!<br>"; echo "<br><input type=button class=button value=\"Close Window\"
onclick=\"window.close()\"><br>"; exit;
  }

  $error = '';
  echo "Connecting to SMTP server.. ";

  require_once('./mimeclass.php');
  $ssock = '';

  $ssock = fsockopen($mthost, $mtport);

  if($ssock){
   echo "<b>Connected!</b><br>";
   $srvmsg = getsmtpmsg($ssock);
   fputs($ssock, "EHLO localhost\r\n");
   $srvmsg = getsmtpmsg($ssock);
   $lastmsg = substr($srvmsg, 0, 3);
   if($lastmsg=='250'){
    // AUTH via LOGIN or PLAIN..

    // auth login
    if($mtauth && strpos($srvmsg,'LOGIN')>0){
     echo "Verifying username and password...";
     fputs($ssock, "AUTH LOGIN\r\n");
     $srvmsg = getsmtpmsg($ssock);
     $lastmsg = substr($srvmsg, 0, 3);
     if ($lastmsg <> "334") $error = 1; else $error = '';

     if(!$error){
      echo "Authenticating User/Pass... ";
      fputs($ssock, base64_encode($mtuser)."\r\n");
      $srvmsg = getsmtpmsg($ssock);
      $lastmsg = substr($srvmsg, 0, 3);
      if ($lastmsg <> "334") $error = 1; else $error = '';
      if(!$error){
       fputs($ssock, base64_encode($mtpass)."\r\n");
       $srvmsg = getsmtpmsg($ssock);
       $lastmsg = substr($srvmsg, 0, 3);
       if ($lastmsg <> "235") $error = 1; else $error = '';
      }
     }
     if(!$error) echo " <b>Verified!</b><br>"; else echo " <b>Failed!</b><br>";
     $noplain = 1;
    }

    // auth plain
    if($mtauth && strpos($srvmsg,'PLAIN')>0 && !$noplain){
     echo "Verifying username and password...";
     fputs($ssock, "AUTH PLAIN ".base64_encode("\0".$mtuser."\0".$mtpass)."\r\n");
     $srvmsg = getsmtpmsg($ssock);
     $lastmsg = substr($srvmsg, 0, 3);
     if ($lastmsg <> "235") $error = 1; else $error = '';
     if(!$error) echo " <b>Verified!</b><br>"; else echo " <b>Failed!</b><br>";
   }

    if(!$error){
     // authenticated, set up test email
     echo "Sending test email to remote address..<br> "; flush();

     fputs($ssock, "MAIL FROM: <$erraddr>\r\n");
     $srvmsg = fgets($ssock, 1024);
     $lastmsg = substr($srvmsg, 0, 3);
     if ($lastmsg <> "250") $error = 1; else $error = ''; 

     if(!$error){
      fputs($ssock, "RCPT TO: <$testmail>\r\n");
      $srvmsg = getsmtpmsg($ssock, 1024);
      $lastmsg = substr($srvmsg, 0, 3);
      if ($lastmsg <> "250"){
       echo "Problem setting receiver to Remote Test Address<br>";
       $error = 1;
      } else $error = '';
      if(!$error){
       fputs($ssock, "DATA\r\n");
       $srvmsg = fgets($ssock, 1024);
       $lastmsg = substr($srvmsg, 0, 3);
       if ($lastmsg <> "354") $error = 1; else {
        $error = '';
        // build message and send
        $mail = new html_mime_mail();
        $xmsg = "The server successfully received and relayed the test message to a remote address.\n\nThis most likely means that SMTP is configured correctly.\n";
        $mail->add_text($xmsg);
        $build_params = array();
        $build_params['html_charset'] = "iso-8859-1";
        $build_params['text_charset'] = "iso-8859-1";
        $mail->build_message($build_params);
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz/3600)*100 + ($tz%3600)/60;
        $date = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);
        $body = $mail->output;
        $headers = $mail->headers;
        $crlf = "\n";
        $header .="Date: $date$crlf";
        $header .="From: \"ListMail Notification\" <$erraddr>$crlf";
        $header .= "To: \"ListMail Test\" <$testmail>$crlf";
        $header .="Subject: ListMail SMTP Test: Success!$crlf";
        $header .= "MIME-Version: 1.0$crlf";
        while(list($key2,$data) = each($headers)){
         $header .= $data.$crlf;
        }
        $header .="X-Mailer: ListMail v$ver";
        $header .= "$crlf$crlf";

        $themesg = $header.$body;
        $themesg = str_replace("\r\n","\n",$themesg);
        $themesg = str_replace("\n","\r\n",$themesg);
        $themesg = str_replace("\r\n.","\r\n..",$themesg);

        fputs($ssock, $themesg."\r\n.\r\n");
        $srvmsg = fgets($ssock, 1024);
        if($bugs){ echo "SENDmsg=$srvmsg.<br><br>"; flush(); }
        $lastmsg = substr($srvmsg, 0, 3);
        if ($lastmsg <> "250") $error = 1; else $error = '';
        if(!$error){
         echo " <b>Success!</b><br><br>";
         echo "<small>If you receive an email at your \"Remote Test Address\" the SMTP server is configured correctly.</small><br><br>";
        }
       }
      }
     }

    }
   } else {
    echo "Could not say EHLO.<br><br>";
    $error = 1;
   }
  } else {
   echo "Could not find server!<br><br>";
   $error = 1;
  }

  if($error){
   echo "The SMTP server is <b>not</b> configured correctly.  Check your settings.<br><br>";
  }
 } else {
  echo "<b>No</b><br><br>";
 }
 echo "<input type=button class=button value=\"Close Window\"
onclick=\"window.close()\"><br>";

 exit;
}
// end testmail


// EDITCONF MAIN
if ($installed && !$doinst){

 // this should be dosave
 if ($doedit == 'yes'){
  $logtxt = '';
//  ob_start();
  $tadmpw = addslashes($tadmpw);
  $tdaypw = addslashes($tdaypw);

  if($twrapon <> '1'){ $txthtml = 0; }
  if($tmtchk <> '1'){ $tmtchk = 0; }
  if($tmtlogchk <> '1'){ $tmtlogchk = 0; }
  $tmtchk = implode(';',array($tmtchk,$tmtlogchk));
  if($tmtauth <> '1'){ $tmtauth = 0; }
  if($terrchk <> '1'){ $terrchk = 0; }
  if($terrchk2 == '1' && $terrchk == '0') $terrchk = '2';

  // if($terrchk=='2') $tbounces = '1';
  if($tbounces > '20') $tbounces = '20';
  if($tbounces < '1') $tbounces = '1';
  if($tbounces2 > '999') $tbounces2 = '999';
  if($tbounces2 < '1') $tbounces2 = '1';

  $tbounces = $tbounces . ':' . $tbounces2;

  if($tmtpchk <> '1'){ $tmtpchk = 0; }
  if($n1chk <> '1') $n1chk = '0';
  if($n2chk <> '1') $n2chk = '0';
  if($n3chk <> '1') $n3chk = '0';
  if($n4chk <> '1') $n4chk = '0';
  if($n5chk <> '1') $n5chk = '0';
  $tnotifs = $n1chk.";".$n2chk.";".$n3chk.";".$n4chk.";".$n5chk;

  $tadmmail = $tadmmail . ';' . $ttestmail;

  $stest = substr($tlmpath, -1);
  if ($stest <> '/'){ $tlmpath .= "/"; $demotext = 'Trailing slash added to path.'; }

  if(!is_numeric($tmaintsent)) $tmaintsent = '0';
  if(!is_numeric($tmainthits)) $tmainthits = '0';
  if(!is_numeric($tmaintunc)) $tmaintunc = '0';
  $tmaint = $tmaintsent.';'.$tmainthits.';'.$tmaintunc.';';

  if($ttextonly || $textonlydel){
   if($ttextonly && !$textonlydel){
    // check format
    if(ereg("^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$",strtolower($ttextonly))){
     // check if not a dupe
     list($otextonly)=mysql_fetch_row(mysql_query("select textonly from $ctable where 1"));
     if($otextonly){
      $otextonly = explode(';',$otextonly);
      if(!in_array($ttextonly,$otextonly)){
       // not a dupe, insert, sort and send
       array_push($otextonly,strtolower($ttextonly));
       sort($otextonly);
      } else {
       $logtxt .= "Duplicate domain <b>$ttextonly</b> provided for addition to text-only.<br>";
      }
     } else $otextonly = array(strtolower($ttextonly));
     $logtxt = "Added domain <b>$ttextonly</b> to text-only.<br>";
     $ttextonly = implode(';',$otextonly);
     // echo "inserting: $ttextonly";
     $toquery = ", textonly = '$ttextonly'";
    } else {
     $logtxt .= "Invalid domain <b>$ttextonly</b> provided for addition to text-only.  Format is: domain.com<br>";
    }
   }
   if($textonlydel){
    if($toid){
     // get current fields and delete this one
     list($otextonly)=mysql_fetch_row(mysql_query("select textonly from $ctable where 1"));
     $otextonly = explode(';',$otextonly);
     $k = array_search($toid,$otextonly);
     if($k!==false){
      unset($otextonly[$k]);
      $ttextonly = implode(';',$otextonly);
     }
     $toquery = ", textonly = '$ttextonly'";
     $logtxt = "Removed domain <b>$toid</b> from text-only.<br>";
    } else {
     $logtxt .= "Tried to remove a non-existent domain from text-only.<br>";
    }
   }
  } else $toquery = false;
  // echo "saving...";
  $cmd = "update $ctable set admpw = '$tadmpw', daypw = '$tdaypw', admmail = '$tadmmail', listmailpath = '$tlmpath', keycode = '$tkeycode', linkcode = '$tlinkcode', wrapon = '$twrapon', wrapcols = '$twrapcols', errhost = '$terrhost', errport = '$terrport', erraddr = '$tpopaddr', erruser = '$tpopuser', errpass = '$tpoppass', errchk = '$terrchk', errfrom = '$terrfrom', delay1 = '$tdelay', delay2 = '$tdelay2', delay3 = '$tdelay3', nbounce = '$tbounces', mthost = '$tmthost', mtport = '$tmtport', mtauth = '$tmtauth', mtuser = '$tmtuser', mtpass = '$tmtpass', mtdelay = '$tmtdelay', mtchk = '$tmtchk', mtphost = '$tmtphost', mtpport = '$tmtpport', mtpuser = '$tmtpuser', mtppass = '$tmtppass', mtpdelay = $tmtpdelay, mtpchk = '$tmtpchk', notifs = '$tnotifs', maint = '$tmaint'$toquery where 1";
  if ($demo <> 'yes'){
   list($oa)=mysql_fetch_row(mysql_query("select admpw from $ctable where 1"));
   if($oa<>stripslashes($tadmpw)){
     // update cookie
     setcookie('LMadm2',md5(stripslashes($tadmpw)));
   }
   mysql_query($cmd);
   $logtxt .= "Configuration saved.";
  } else {
   $logtxt .= "Config cannot be saved in the demo.";
  }
 }
 adminheader('LM: Edit Configuration','Configuration','');

 echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

 listmenu($list,'config');

 echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";

 // read values
 $cmd = "select admpw,daypw,admmail,listmailpath,keycode,linkcode,wrapon,wrapcols,errhost,errport,erraddr,erruser,errpass,errchk,errfrom,delay1,delay2,delay3,nbounce,mthost,mtport,mtauth,mtuser,mtpass,mtdelay,mtchk,mtphost,mtpport,mtpuser,mtppass,mtpdelay,mtpchk,notifs,maint,textonly from $ctable where 1 limit 1";
$result = mysql_query($cmd);
while(list($admpw,$daypw,$admmail,$listmailpath,$keycode,$linkcode,$wrapon,$wrapcols,$errhost,$errport,$popaddr,$popuser,$poppass,$errchk,$errfrom,$delay,$delay2,$delay3,$bounces,$mthost,$mtport,$mtauth,$mtuser,$mtpass,$mtdelay,$mtchk,$mtphost,$mtpport,$mtpuser,$mtppass,$mtpdelay,$mtpchk,$notifs,$maint,$textonly) = mysql_fetch_row($result)){
 $emails = explode(';',$admmail);
 $admmail = $emails[0];
 $testmail = $emails[1];
 $emails = '';
 $maint = explode(';',$maint);
 $maint_sent = $maint[0];
 if(!$maint_sent) $maint_sent = '0';
 $maint_hits = $maint[1];
 if(!$maint_hits) $maint_hits = '0';
 $maint_unc = $maint[2];
 if(!$maint_unc) $maint_unc = '0';
 $textonly = explode(';',$textonly);
 list($mtchk,$mtlogchk)=explode(';',$mtchk);
 list($bounces,$bounces2)=explode(':',$bounces);

 if($wrapon==1){ $wcheck = ' checked'; } else { $wcheck = ''; }
 if($mtchk==1){ $mtcheck = ' checked'; $dumcheck = '';} else { $mtcheck = ''; $dumcheck = ' checked'; }
 if($mtlogchk==1){ $mtlogcheck = ' checked'; } else { $mtlogcheck = ''; }
 if($mtpchk==1){ $mtpcheck = ' checked'; } else { $mtpcheck = ''; }
 if($mtauth==1){ $mtacheck = ' checked'; } else { $mtacheck = ''; }
 if($errchk==1){ $errcheck = ' checked'; } else { $errcheck = ''; }
 if($errchk==2){ $errcheck2 = ' checked'; } else { $errcheck2 = ''; }
 if($errchk==0){ $errcheck3 = ' checked'; } else { $errcheck3 = ''; }

 $xnotifs = explode(";",$notifs);
 if($xnotifs[0]==1) $n1check = ' checked'; else $n1check = '';
 if($xnotifs[1]==1) $n2check = ' checked'; else $n2check = '';
 if($xnotifs[2]==1) $n3check = ' checked'; else $n3check = '';
 if($xnotifs[3]==1) $n4check = ' checked'; else $n4check = '';
 if($xnotifs[4]==1) $n5check = ' checked'; else $n5check = '';

 $linecolor = 'black';

 if($errport=='143') $epimap = ' selected';
 if($errport=='993') $epssl = ' selected';
 if($mtpport=='143') $mtpimap = ' selected';
 if($mtpport=='993') $mtpssl = ' selected';
 if($errport=='995') $gmailpop = ' selected';


 // main page
 echo "
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td width=1>
   <input class=button type=submit value=\"Save Configuration\" onclick=\"editf.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=submit class=button value=\"Backup &amp; Restore\" onclick=\"hopto('./editconf.php?list=$list&backup=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
  <td><img src=1.gif width=3></td>
  <td width=1>
   <input type=submit class=button value=\"Test Mail Settings\" onclick=\"popitup('./editconf.php?testmail=1',
'','300','440');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=submit class=button value=\"Edit Database\" onclick=\"hopto('./editconf.php?doinst=1');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
<!--  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=submit class=button value=\"SysInfo\" onclick=\"hopto('./editconf.php?list=$list&sysinfo=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 79px\">
  </td> -->
 </tr>
</table>

<!-- start general config -->

<img src=1.gif height=3><br>
<table width=100% class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>General Config</span></td><td align=right><span class=table_head_text>v$file_version<img src=1.gif width=2>:</span><img src=1.gif width=2><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('setup_general')."');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad --><table width=100% border=0 cellspacing=0 cellpadding=4><tr><td>
   <!-- margin --><div style=\"margin-left: 5px\">
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <table border=0 cellspacing=0 cellpadding=0><tr><td>
       <form name=editf method=post action=$PHP_SELF>
       <input type=hidden name=doedit value=yes>
       <span class=table_inside_small>Administrator Password</span><br>
       <input name=tadmpw type=password class=xbox style=\"width: 130px\" value=\"".htmlspecialchars($admpw)."\"><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Dailymail Password</span><br>
       <input type=password class=xbox name=tdaypw style=\"width: 130px\" value=\"".htmlspecialchars($daypw)."\"><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Full path (URL) to ListMail Files</span><br>
       <input name=tlmpath type=text class=xbox style=\"width: 300px\" value=\"".htmlspecialchars($listmailpath)."\"><br>
      </td></tr></table>
      <img src=1.gif height=5><br>
      <table border=0 cellspacing=0 cellpadding=0>
       <tr>
        <td>
         <span class=table_inside_small>Administrator Email Address</span><br>
         <input name=tadmmail type=text class=xbox style=\"width: 250px\" value=\"".htmlspecialchars($admmail)."\"><br>
        </td>
        <td width=10><img src=1.gif width=10 height=1></td>
        <td>
         <span class=table_inside_small>Remote Test Address</span><br>
         <input name=ttestmail type=text class=xbox style=\"width: 250px\" value=\"".htmlspecialchars($testmail)."\"><br>
        </td>
       </tr>
      </table>
      <img src=1.gif height=2><br>
    <table border=0 cellspacing=0 cellpadding=0>
     <tr>
      <td valign=top>
      <input name=n1chk type=checkbox style=\"width: 13px\" value=1$n1check><img src=1.gif width=7><span class=table_inside_small>Send admin a report after Dailymail runs</span><br>
      </td><td width=10><img src=1.gif width=10></td>
      <td valign=top>
       <input name=n2chk type=checkbox style=\"width: 13px\" value=1$n2check><img src=1.gif width=7><span class=table_inside_small>Notify admin when a bad password is entered</span><br>
      </td>
     </tr>
    </table>

     </td>
    </tr>
   </table>
   </div>
   </td></tr></table><!-- pad -->
  </td>
 </tr>
</table>

<!-- end general config -->

<!-- start maintenance -->

<img src=1.gif height=3><br>
<table width=100% class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Maintenance Options</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('setup_general')."');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad --><table width=100% border=0 cellspacing=0 cellpadding=4><tr><td>
   <!-- margin --><div style=\"margin-left: 5px\">
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
       <table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Keep <input name=tmaintsent type=text class=xbox style=\"width: 50px\" value=\"".htmlspecialchars($maint_sent)."\"> sent messages in database<br></span></td><td width=20><img src=1.gif width=20 height=1><br></td><td><span class=table_inside_small>Keep <input name=tmainthits type=text class=xbox style=\"width: 50px\" value=\"".htmlspecialchars($maint_hits)."\"> tracked hits in database<br></span></td></tr></table>
       <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>Delete unconfirmed users after <input name=tmaintunc type=text class=xbox style=\"width: 50px\" value=\"".htmlspecialchars($maint_unc)."\"> days<br></span></td><td align=right valign=bottom><span class=table_inside_small><small>Note: A value of 0 means unlimited.</small><img src=1.gif width=3><br></span></td></tr></table>
      </td></tr></table>
     </td>
    </tr>
   </table>
   </div>
   </td></tr></table><!-- pad -->
  </td>
 </tr>
</table>

<!-- end maintenance -->

<!-- start mail sending method -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Mail Sending Method</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('setup_general')."');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td width=5><img src=1.gif width=5></td><td>
   <!-- pad --><table width=100% border=0 cellspacing=0 cellpadding=4><tr><td><span class=table_inside_small>
      <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td valign=top class=table_inside_small><input name=tmtchk type=checkbox style=\"width: 13px\" value=1 onclick=\"if(this.checked==1) editf.intdummy.checked=0; else editf.intdummy.checked=1;\"$mtcheck><img src=1.gif width=7>SMTP Server (Recommended)<br></td><td align=right valign=top class=table_inside_small><img src=1.gif width=20 height=1><input name=tmtlogchk type=checkbox style=\"width: 13px\" value=1$mtlogcheck> Always write SMTP logfile<img src=1.gif width=10 height=1><br></td></tr></table>
      <table border=0 cellspacing=0 cellpadding=0><tr>
       <td width=20><img src=1.gif width=20 height=1></td><td>
       <span class=table_inside_small>Host<br><input name=tmthost type=text class=xbox style=\"width: 150px\" value=\"".htmlspecialchars($mthost)."\"></span><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Port<br><input name=tmtport type=text class=xbox style=\"width: 25px\" value=\"".htmlspecialchars($mtport)."\"></span><br>
      </td><td width=10><img src=1.gif height=10></td><td><span class=table_inside_small>Reconnect&nbsp;every<br><input class=xbox size=5 type=text name=tmtdelay value=\"$mtdelay\"> emails</span><br>
      </td><td width=10><img src=1.gif height=10></td><td align=center>
       <span class=table_inside_small>AUTH?<br></span>
       <input name=tmtauth type=checkbox value=1$mtacheck style=\"width: 13px\"><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Login ID<br><input name=tmtuser type=text class=xbox style=\"width: 140px\" value=\"".htmlspecialchars($mtuser)."\"$noauthtxt></span><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Password<br><input name=tmtpass type=password class=xbox style=\"width: 94px\" value=\"".htmlspecialchars($mtpass)."\"$noauthtxt></span><br>
      </td></tr></table>
      <img src=1.gif width=20 height=1><input name=tmtpchk type=checkbox value=1$mtpcheck style=\"width: 13px\"><img src=1.gif width=7><span class=table_inside_small>Authorize by Checking Email</span><br>
      <table border=0 cellspacing=0 cellpadding=0><tr>
       <td width=20><img src=1.gif width=20 height=1></td><td>
       <span class=table_inside_small>Host<br></span>
       <input name=tmtphost type=text class=xbox style=\"width: 150px\" value=\"".htmlspecialchars($mtphost)."\">
      </td><td width=10><img src=1.gif width=10></td><td>
       <span class=table_inside_small>Type</span><br>
       <select class=xarea name=tmtpport><option value=\"110\">POP3</option><option value=\"143\"$mtpimap>IMAP</option><option value=\"993\"$mtpssl>SSL</option></select>
      </td><td width=10><img src=1.gif width=10></td><td>
       <span class=table_inside_small>Login ID<br><input name=tmtpuser type=text class=xbox style=\"width: 140px\" value=\"".htmlspecialchars($mtpuser)."\"></span><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Password<br><input name=tmtppass type=password class=xbox style=\"width: 90px\" value=\"".htmlspecialchars($mtppass)."\"></span><br>
      </td><td width=10><img src=1.gif width=10></td><td>
       <span class=table_inside_small>Every<br>
       <input name=tmtpdelay type=text class=xbox style=\"width: 25px\" value=\"$mtpdelay\"> mins</span><br>
      </td><td width=10><img src=1.gif width=10></td>
      </tr></table>
      <img src=1.gif height=5><br>
      <input name=intdummy type=checkbox style=\"width: 13px\" value=1 onclick=\"if(this.checked==1) editf.tmtchk.checked=0; else editf.tmtchk.checked=1;\"$dumcheck><img src=1.gif width=7>Internal PHP mail() (delayed to prevent timeouts) <input name=tdelay type=text class=xbox style=\"width: 26px\" value=\"$delay\"> (ms) per email and <input name=tdelay2 type=text class=xbox style=\"width: 12px\" value=\"$delay2\"> (mins) every <input name=tdelay3 type=text class=xbox style=\"width: 26px\" value=\"$delay3\"> emails.<br>
      <img src=1.gif width=20 height=1><small>Note: Some messages such as welcome, confirmation, and some notifications are always sent with PHP mail()<img src=1.gif width=20 height=1></small><br>
     </td></tr></table>
   </td></tr></table><!-- pad -->
  </td>
 </tr>
</table>

<!-- end mail sending method -->
<!-- start bounce handling -->

<img src=1.gif height=4><br>
<table class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
    <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Bounced Message Handling</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('setup_general')."');\">Help</a><br></td></tr></table>

  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad --><table width=100% border=0 cellspacing=0 cellpadding=4><tr><td>
      <!-- margin --><div style=\"margin-left: 5px\">
         <input name=terrchk3 type=checkbox onclick=\"if(this.checked==1){ editf.terrchk.checked=0; editf.terrchk2.checked=0; }\" style=\"width: 13px\" value=1$errcheck3><img src=1.gif width=7><span class=table_inside_small>Disabled, all bounces are returned to the administrator email address.</span><br>

      <input name=terrchk2 type=checkbox onclick=\"if(this.checked==1){ editf.terrchk.checked=0; editf.terrchk3.checked=0; }\" style=\"width: 13px\" value=1$errcheck2><img src=1.gif width=7><span class=table_inside_small>Use <i>bounce.cgi</i>, which must be configured. (Recommended)</span><br>


      <input name=terrchk type=checkbox onclick=\"if(this.checked==1){ editf.terrchk2.checked=0; editf.terrchk3.checked=0; }\" style=\"width: 13px\" value=1$errcheck><img src=1.gif width=7><span class=table_inside_small>Use an email account, bounced messages are checked and processed each day by dailymail.</span><br>

      <table border=0 cellspacing=0 cellpadding=0 style=\"margin-left: 20px\"><tr><td>
       <span class=table_inside_small>Host<br></span>
       <input name=terrhost type=text class=xbox style=\"width: 150px\" value=\"".htmlspecialchars($errhost)."\">
      </td><td width=10><img src=1.gif width=10></td><td>
       <span class=table_inside_small>Type</span><br>
       <select name=terrport class=xarea>
         <option value=\"110\">POP3</option>
         <option value=\"143\"$epimap>IMAP</option>
         <option value=\"993\"$epssl>SSL</option>
         <option value=\"995\"$gmailpop>GMAILPOP</option>
       </select>
      </td><td width=10><img src=1.gif width=10></td><td>
       <span class=table_inside_small>Login ID<br><input name=tpopuser type=text class=xbox style=\"width: 140px\" value=\"".htmlspecialchars($popuser)."\"></span><br>
      </td><td width=10><img src=1.gif height=10></td><td>
       <span class=table_inside_small>Password<br><input name=tpoppass type=password class=xbox style=\"width: 90px\" value=\"".htmlspecialchars($poppass)."\"></span><br>
      </td><td width=10><img src=1.gif width=10></td><td>
      </tr></table>

      <img src=1.gif height=5><br>
      <table border=0 cellspacing=0 cellpadding=0>
       <tr>
        <td valign=top><span class=table_inside_small>Bounce to email address</span><br>
      <input name=tpopaddr type=text class=xbox size=40 style=\"width: 200px\" value=\"".htmlspecialchars($popaddr)."\">
        </td><td width=15><img src=1.gif width=15></td><td valign=bottom>
         <span class=table_inside_small>Delete users if they bounce<br> <input name=tbounces type=text class=xbox size=2 maxlength=2 value=\"$bounces\" style=\"width: 20px\"> times in <input name=tbounces2 type=text class=xbox size=3 maxlength=3 value=\"$bounces2\" style=\"width: 30px\"> days.</span><br>
        </td>
        <td>
            <span class=table_inside_small>Only read FROM:</span><br>
            <input name=terrfrom type=text class=xbox size=40 style=\"width: 200px\" value=\"".htmlspecialchars($errfrom)."\" >
        </td>
       </tr>
      </table>
      </div>
   </td></tr></table><!-- pad -->

  </td>
 </tr>
</table>

<!-- end bounce handling -->

<!-- start text only -->

<img src=1.gif height=3><br>
<table width=100% class=outer_table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td class=table_head_bg>
  <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_head_text>Text-Only Domains</span></td><td align=right><a class=table_head_help href=\"javascript:void(0)\" onmouseover=\"window.status='Help'; return true;\" onmouseout=\"window.status=''; return true;\" onclick=\"popitup('".helplink('setup_general')."');\">Help</a><br></td></tr></table>
  </td>
 </tr>
 <tr>
  <td bgcolor=#eeeeee>
   <!-- pad --><table width=100% border=0 cellspacing=0 cellpadding=4><tr><td>
   <!-- margin --><div style=\"margin-left: 5px\">
   <table width=100% border=0 cellspacing=0 cellpadding=0>
    <tr>
     <td>
      <table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>
       <table border=0 cellspacing=0 cellpadding=0><tr><td><span class=table_inside_small>\n";
echo "<select class=xarea name=toid style=\"width: 140px\">\n";
if($textonly[0]<>''){
 for($i=0;$i<count($textonly);$i++){
  echo "<option value=\"$textonly[$i]\">$textonly[$i]\n";
 }
} else {
 echo "<option value=\"\">None\n";
}

echo "</select> <input type=button class=button value=Delete onclick=\"if(!editf.toid.value){ alert('No text-only domain to remove.'); return false; } else { editf.textonlydel.value='1'; editf.submit(); }\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 48px; margin-bottom: 1px;\"><img src=1.gif width=7>Domain <input type=text class=xbox name=ttextonly size=15 style=\"width: 120px;\"> <input type=button class=button value=Add onclick=\"editf.submit();\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 30px; margin-bottom: 1px;\"><br></span></td></tr></table>
      </td></tr></table>
     </td>
    </tr>
   </table>
   </div>
   </td></tr></table><!-- pad -->
  </td>
 </tr>
</table>

<!-- end text only -->

<img src=1.gif height=3><br>
<table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td width=1>
   <input type=hidden name=doedit value=yes>
   <input class=button id=save2 type=submit value=\"Save Configuration\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
   <input type=hidden name=tkeycode value=\"".htmlspecialchars($keycode)."\">
   <input type=hidden name=tlinkcode value=\"".htmlspecialchars($linkcode)."\">
   <input type=hidden name=twrapon value=\"$wrapon\">
   <input type=hidden name=twrapcols value=\"$wrapcols\">
   <input type=hidden name=textonlydel value=\"\">
   <input type=hidden name=list value=$list>
  </td></form>
  <script language=javascript>editf.save2.focus();</script>
  <td width=3><img src=1.gif width=3></td>
  <td width=1>
   <input type=submit class=button value=\"Backup &amp; Restore\" onclick=\"hopto('./editconf.php?list=$list&backup=1')\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
  <td><img src=1.gif width=1></td>
  <td width=1>
   <input type=submit class=button value=\"Test Mail Settings\" onclick=\"popitup('./editconf.php?testmail=1',
'scrollbars=no,height=450,width=440,top=0,left=195');\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td>
  <td width=3><img src=1.gif width=3></td>
  <td  width=1>
   <form method=post><input type=hidden name=doinst value=1>
   <input type=submit class=button value=\"Edit Database\" onmouseover=\"this.className='button_over'\" onmouseout=\"this.className='button'\" style=\"width: 135px\">
  </td></form>
 </tr>
</table>\n";

if($logtxt) echo "<img src=1.gif height=5><br><span class=status_text>$logtxt</span><br>";
echo "</td></tr></table>
<script language=javascript>window.status='$logtxt';</script>
</body>
</html>";
 }
} // installed

mysql_close($link);
}

// EOF
?>
