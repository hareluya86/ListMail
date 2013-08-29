<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

ini_set("max_execution_time","0");

$islogged = islogged();

if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);
// $thetime = date("H:i:sa");
// echo "thetime=$thetime ";

$field = 'txtfatt'; $formname = 'form1';

// header
echo '<html>
 <head>
 <title>File Attachments</title>
 <style type=text/css rel=stylesheet> <!--
 .confhead { font: 9pt verdana; color: white; margin-left: 2px; }
 .conflist { font: 8pt helvetica; color: black }
 .button { font-family: helvetica;
  font-size: 8pt;
  border: 1px solid black;
  background: #eeeeee;
 }
 .button2 { font-family: helvetica;
  font-size: 8pt;
  border: 1px solid black;
  background: #ffffff;
 }
 body {
  scrollbar-base-color: white;
  scrollbar-3dlight-color:white;
  scrollbar-arrow-color:black;
  scrollbar-base-color:white;
  scrollbar-darkshadow-color:white;
  scrollbar-face-color:#cccccc;
  scrollbar-highlight-color:white;
  scrollbar-shadow-color:black;
 }

 .logt { font: 8pt helvetica; }

 --> </style>


 </head>
 <body bgcolor=#eeeeee>
 <script language=javascript> <!--
 function hopto(URL){
    window.location.href = URL;
 }
 --> </script>
<center>
<table width=90% border=0 cellspacing=0 cellpadding=0><tr><td>';
if($ul){
 // get uploaded file
 global $_FILES;
 if ($_FILES['userfile']['tmp_name']){
  $filename = $_FILES['userfile']['tmp_name'];
  $realname = $_FILES['userfile']['name'];
  $targdir = getcwd() . '/attach';
  move_uploaded_file($_FILES['userfile']['tmp_name'], $targdir.'/'.$_FILES['userfile']['name']);
  echo "<span class=logt><b>File Uploaded:</b> $realname (".$_FILES['userfile']['size']." bytes)</span><br>";
 }
}

if($d){
 // delete files
 // echo "f=$f...<br>";
 // get file list and determine files to delete
 $nameofdir='./attach';
 $handle=opendir($nameofdir);
 $x = 0;
 $f = explode(',',$f);
 $first = 1;
 echo "<span class=logt><b>Deleting File"; if(sizeof($f)>1) echo "s"; echo ": </b>";
 while($file_name = readdir($handle)) {
  if ($file_name != "." && $file_name != "..") {
   $x++;
   if($x==in_array($x,$f)){
    if(!$first) echo ", ";
    echo "$file_name";
    $first = '';
    unlink("./attach/$file_name");

   }
  }
 }
 echo "</span><br>";
}

if(!$d && !$ul) echo "<span class=logt>&nbsp;</span><br>\n";
echo "<img src=1.gif height=5><br>\n";
// main page


echo '


 <table width=100% border=0 cellspacing=0 cellpadding=0>
  <tr>
   <td>
    <form name=ful enctype="multipart/form-data" action=upload.php method="post">
    <input type=hidden name=p value='.$p.'>
    <input type=hidden name=ul value=1>
    <input type="hidden" name="MAX_FILE_SIZE" value="2000000">
    <span class=conflist><b>Upload a file:</b></span><br><input size=29 name="userfile" type="file" class=button2><br>
    <img src=1.gif height=1><br>
    <input type="submit" value="Send File" class=button>
   </td> </form>
  </tr>
 </table>
 <img src=1.gif height=15><br>
 <table width=100% border=0 cellspacing=0 cellpadding=0>
 <tr><td colspan=3><form name=f1 method=post><span class=conflist><b>Uploaded files:</b></span></td></tr>
 <tr><td height=1 bgcolor=black colspan=3><img src=1.gif height=1></td></tr>';

// file rows
$nameofdir='./attach';
$handle=opendir($nameofdir);
$x = 0;
$xarray = array();
while($file_name = readdir($handle)) {
 if ($file_name != "." && $file_name != "..") {
  $x++;
  $xarray[] = array($x,$file_name);
  $relpath = $nameofdir."/".$file_name;
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
   <td width=1><input type=checkbox name=file$x value=1><br></td>
   <td><a href=\"$nameofdir/$file_name\" class=conflist>$file_name</a></td><td width=1 align=right><span class=conflist>$file_size</span></td></tr>";
 }
}
closedir($handle);

echo "<tr><td height=1 colspan=3><img src=1.gif height=1></td></form></tr>
</table>
<img src=1.gif height=10><br>
<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td valign=top>
<table border=0 cellspacing=0 cellpadding=0><tr><td>
<form name=uf2 method=post>
<input class=button type=button value=\"Set Attachments\" onclick=\" var fatts=''; ";

reset($xarray);
while(list($key,$val) = each($xarray)){
 list($fnum,$fnam) = $val;
 echo "if(document.f1.file$fnum.checked==true){ if(fatts != '') fatts = fatts + ','; fatts = fatts + '".addslashes($fnam)."'; }\n";
}
echo " opener.".$formname.".".$field.".value=fatts; window.close();\"></td></form></tr></table>
</td><td valign=top align=right>";

echo "<table border=0 cellspacing=0 cellpadding=0><tr><td>
<input class=button type=button value=\"Delete Files\" onclick=\" var todel=''; ";
reset($xarray);
while(list($key,$val) = each($xarray)){
 list($fnum,$fnam) = $val;
 echo "if(document.f1.file$fnum.checked==true) {
 if(todel != '') todel = todel + ',';
 todel = todel + '$fnum'; }\n";
}
echo " hopto('./upload.php?p=$p&d=y&f=' + todel); \">

</td></tr></table>
</td></tr></table>
<script language=javascript> ful.userfile.focus(); </script>";


} // isadmin
?>
