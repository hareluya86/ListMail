<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include('./config.php');
include('./admin.php');

$link = mysql_connect($sqlhost,$sqluser,$sqlpass);
mysql_selectdb($sqldb);

$endbutton = "<br><input type=button class=button value=\"Close Window\"
onclick=\"window.close()\"><br>";

if($p == 'smtp_helo'){
 pophead('SMTP');
echo "<font style=\"font: 10pt helvetica\"><b>
HELO Setting</b><br><br>
On most servers a setting of auto will work.  However, on some servers you are required to enter either 'yourdomain' or 'yourdomain.com'.<br>$endbutton";
}

if($p == 'bounce_settings'){
 pophead('Error Mailbox Settings');
echo "<font style=\"font: 10pt helvetica\"><b>
Error Mailbox Settings</b><br><br>
<u>Error Mailbox</u>: To handle bounced emails automatically and elegantly, you need to configure this mailbox.  Simply create a normal mail account on your web host and enter the login information here.  Make sure to enter the email address correctly, as this is where error messages are directed.<br><br>
When a bounced (returned) message is detected, it is re-sent to the user in case the problem with their mailbox is temporary.  While a user is not responding, emails are queued.  When the user is detected to have stopped bouncing, they are sent the queue.  If they recover within the set # of bounces, they don't miss a single message!  Users returning messages from confirmation or welcome emails are automatically deleted.<br><br>
Bounced messages are automatically processed each day by dailymail.
<br>$endbutton";
}

if($p == 'smtp_settings'){
 pophead('Mail Server Settings');
echo "<font style=\"font: 10pt helvetica\"><b>
SMTP Server Settings</b><br><br>
It is very important that the servers for bounced messages and sending messages are configured correctly.
<br><br>
<u>SMTP Sending Server</u>: For improved efficiency and speed, enter your host's outgoing mail server here.  This will be the same server you enter into your email program to send email with your domain name.  You must know how your server authenticates mail sending and set the login settings accordingly, if necessary.  When you first install ListMail do a few test emailings to make sure your servers are responding from within the program.  If you've upgraded, create a test list, add yourself, and send a test email to that list.<br>$endbutton";
}

if($p == 'k_msg_code'){
 pophead("Message Codes");
 echo "
<font style=\"font: 10pt helvetica\"><b>
Using The Link Database</b><br><br>
<u>Message Code</u>: Insertable into your email messages, both text and HTML.  It is replaced by a link to ListMail's link redirection script which tracks the hit and forwards the user when the link is clicked in email.<br><br>
<u>Ref Code</u>: Used to create the message codes and identify the link to the script.<br><br>
<u>Link Title</u>: In HTML email, when you use link message codes, they are replaced by an html link with the title of the link.<br><br>
<small><i><u>Example Email Output</u></i></small><br>
An example of a link without tracking on is:<br>
<font color=blue>http://mfware.com/mail/link.php?id=ex1</font><br><br>
With tracking on it will look like this:<br>
<font color=blue>http://mfware.com/mail/link.php?id=x78129ex1</font><br><br>
In HTML email, the URL is hidden and the <i>title of the link in the link config menu is displayed</i> as clickable.  ListMail will replace the link code with <font color=blue>&lt;a href=targeturl&gt;linktitle&lt;/a&gt;</font>.<br>$endbutton
</body>
</html>";
} // k_msg_code


if($p == 'track_users'){
pophead('Track Users');
echo "<font style=\"font: 10pt helvetica\"><b>
Track your active users</b><br><br>
This option will change how your links look but will add extra tracking information.  With it you can tell who clicked your links, and when.<br><br>
An example of a link without tracking on is:<br>
<font color=blue>http://mfware.com/mail/link.php?id=ex1</font><br><br>
With tracking it will look like this:<br>
<font color=blue>http://mfware.com/mail/link.php?id=x78129ex1</font><br><br>
Each user is assigned a unique 6 character string of random characters, which precedes the link id.  This allows us to track the user and list information.<br><br>
In HTML email, the URL is hidden and the <i>title of the link in the link config menu is displayed</i> as clickable.<br>$endbutton
</body>
</html>";
} // track users

if($p == 'date_format'){
// scrollbars=no,height=450,width=440,top=0,left=195
pophead("Date Code Format");
echo "<font style=\"font: 10pt helvetica\">
The following characters are recognized in the format string:<br><br>
</font>
<font style=\"font: 8pt system\">";
?>
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
z - day of the year; i.e. "0" to "365"<br><br>
<?php echo "
</font>
<font style=\"font: 10pt helvetica\">
Unrecognized characters in the format string will be printed as-is.<br><br>
Examples:<br><br>
<b style=\"background:#eeeeee\">l, F jS, Y</b> would output: Monday, January 1st, 2001<br><br>
<b style=\"background:#eeeeee\">d/m/y</b> would output: 01/01/01<br>$endbutton
</font>
</body>
</html>";
exit;
} // date_format

if($p == 'message_codes'){

adminheader('LM: Help','Message Codes','nologo');
// scrollbars=no,height=450,width=440,top=0,left=195
echo "<div align=left><font style=\"font: 10pt helvetica\">
<b>Message Codes - Replaced by ListMail when email is sent</b><br>
<br>
<u>The following codes are available:</u><br><br>
</font>
<font style=\"font: 10pt helvetica\">";

// get the codes for global and current list
// $list = getlistnum($num);

if($num=='all') $onlyglbl = 1;

echo "<u>Global Codes</u><br>
<img src=1.gif height=2><br>";

  // get fields
  $cmd = "select field1,field2,field3,field4,field5,field6,field7,field8,field9,field10 from $ltable where listnum = '$list' limit 1";
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


list($keych,$linkch)=mysql_fetch_row(mysql_query("select keycode,linkcode from $ctable where 1"));

$cmd = "select ref,typ from $dtable where list = '*' order by id";
$drows = mysql_query($cmd);
while(list($ref,$typ)=mysql_fetch_row($drows)){
 $typdesc = getctype($typ);
 echo "<b>".$keych.$ref."</b> - $typdesc<br>";
}

if($list<>'all'){
 echo "<img src=1.gif height=10><br>
<u>Current List Only</u><br>
<img src=1.gif height=2><br>";
 $cmd = "select ref,typ from $dtable where list = '$list' order by id";
 $drows = mysql_query($cmd);
 if(mysql_num_rows($drows)==0) {
  echo "<i>No list-specific codes configured.</i><br>";
 } else {
  while(list($ref,$typ)=mysql_fetch_row($drows)){
   $typdesc = getctype($typ);
   echo "<b>".$keych.$ref."</b> - $typdesc<br>";
  }
 }
}

// all links
echo "<img src=1.gif height=10><br>
<u>Tracked Links</u><br>
<img src=1.gif height=2><br>";

$krows = mysql_query("select refcode,title from $ktable where 1 order by refcode");
if(@mysql_num_rows($krows)>0){
 while(list($ref,$title)=mysql_fetch_row($krows)){
  echo "<b>".$keych.$linkch.$ref."</b> - $title<br>";
 }
} else {
 echo "<i>No link codes exist.</i><br>";
}
echo "</font><img src=1.gif height=10><br><input type=button class=button value=\"Edit Message Codes\" onclick=\"popitup('./editconf.php?num=$num&codes=1&PHPSESSID=$PHPSESSID',
'scrollbars=yes,height=450,width=800,top=0,left=0');\"><br>
<img src=1.gif height=10><br>
<font style=\"font: 10pt helvetica\">
These codes, when placed in an email subject or message body, will be replaced with dynamic information at the time of delivery.<br>$endbutton<br>
</font>
</body>
</html>";
} // message_codes

if($p == 'confirm_process'){
// scrollbars=no,height=450,width=440,top=0,left=195
pophead('ListMail Help - Confirmation Email');
echo "<font style=\"font: 10pt helvetica\">
<b>Confirmation Email Process</b><br>
<br>
With this option enabled, subscribers must first answer an email confirmation message.  When a signup occurs, users are stored in the database and flagged as unconfirmed.  They must click the special confirmation link to validate themselves before they will receive any mailings or followups.<br><br>
To insert this link, the code <b>!confirm</b> <u>must</u> be used in the confirmation email message body.  When clicked, this link will add the user to the mailing list user database.<br><br>
If the confirmation process is active, when a user signs up they will be shown the customizable <b>confirmation page</b> and be sent the confirmation email.  When the user clicks the link in the email, they will be shown the list's customizable <b>welcome page</b> and be sent the welcome email.  So make sure to configure the output of the welcome page to thank them for verifying their subscription if you're using confirmation emails.<br><br>
If a user does not validate their subscription within the set number of days their information will be removed from the user database.<br><br>
If the confirmation process is <i>not</i> active, the user will be shown the welcome page and will receive the welcome email immediately after entering their information.<br>$endbutton
</font>
</body>
</html>";
}

if($p == 'remove_text'){
// scrollbars=no,height=450,width=440,top=0,left=195
pophead('ListMail Help - Remove Text');
echo "<font style=\"font: 10pt helvetica\">
<b>Remove Text - How it works</b><br>
<br>
The remove text works with the <b>!remove</b> message code.  The code is replaced by a string containing the remove text, followed by a line feed, and then the automatically configured remove URL.  An example of the !remove command's output is as follows:<br><br>
To unsubscribe from our list please click the link below:
http://yoursite.com/remove.php?list=1&email=users@email.com<br><br>
This would be the output of the !remove command if the remove text is set to \"To unsubscribe from our list please click the link below:\".<br><br>
If the message being sent is HTML, the link will be shown with the title \"Unsubscribe\".  If this bothers you, <a href=mailto:dean@mfware.com?subject=Customize%20the%20HTML%20remove%20link>ask me</a> to put in an option to customize it. :-)<br>$endbutton
</font>
</body>
</html>";
}

if($p == 'html_mail'){
// scrollbars=no,height=200,width=440,top=0,left=195
pophead('Sending HTML Email');
echo "<font style=\"font: 10pt helvetica\">
<b>HTML Email</b><br><br>
When you send HTML mail with ListMail, a text version is always included.  The recipient's email client will decide which version to show, depending on it's capabilities.  If the client only supports text, then the text version will be shown.  Otherwise, they will see your HTML page.  For the time being, all HTML images must point to a web server, we cannot do them inline as attachments.<br>
$endbutton</font>
</body>
</html>";
}

if($p == 'fatt_mail'){
list($lmpath)=mysql_fetch_row(mysql_query("select listmailpath from $ctable where 1"));

// scrollbars=no,height=200,width=440,top=0,left=195
pophead('ListMail Help - File Attachments');
echo "<font style=\"font: 10pt helvetica\">
<b>File attachments</b><br><br>
You can attach any number of files to any type of message with ListMail.  To attach files, click \"Add\".  Here you can upload and attach files quickly and easily.<br><br>
<u>Note:</u> All file attachments are stored in the ".$lmpath."<b>attach</b> directory.  You must CHMOD this directory to 777 with your FTP program to allow for uploads via the attach script.<br><br>
<u>Example attachments:</u><br>
<table border=0 cellspacing=0 cellpadding=0><tr><td><font style=\"font: 10pt helvetica\">
Single file:<br></td><td><input type=text size=25 value=test1.zip><br></td></tr><tr><td><font style=\"font: 10pt helvetica\">Multiple files:&nbsp;<br></td><td><input type=text size=25 value=test1.jpg,test2.zip,test3.exe><br></td></tr></table>
$endbutton</font>
</body>
</html>";
}

if($p == 'days_delay'){
// scrollbars=no,height=200,width=440,top=0,left=195
pophead('ListMail Help - Followup Days');

echo "<font style=\"font: 10pt helvetica\">
<b>Followup Days Delay</b><br><br>
A setting of <b>0</b> will result in a 24 hour delay in the sending of the message.  If the first followup has a delay of <b>0</b>, subscribers will receive the first followup the NEXT time dailymail is run, which can be anywhere between 23:59 and 0:01 hours from then.  If you want a mandatory delay of 1 full day before sending, set the first one to 1 to ensure a 24->48 hour delay.  All followups other than the first one will be delayed 24 hours with a setting of '0'.<br><br>
<small><u>Example:</u></small><br>
For a week, enter <b>6</b><br>
For 31 days, enter <b>30</b><br>
<br>$endbutton
</font>
</body>
</html>";
}

?>
