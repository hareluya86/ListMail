<?php
/* ListMail (c) 2005 Dean Wiebe <dean@listmailpro.com>
   You may not modify or distribute the program without express permission from the author.
   Files are overwritten when updates are released, so it's not a good idea anyway. */

include("./config.php");
include("./admin.php");

$islogged = islogged();
if($islogged){$isadmin='true';} else {$isadmin='';header("Location: ./login.php?r=".urlencode(substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1))); exit;}

if ($isadmin == 'true'){

 if($domail == 'yes'){
  if(!$message){ $helptxt .= "<br>Not sent because there was no message."; }
  if($message){
  $xmsg = nl2br($message);
  $helptxt .= "<br><font size=2 face=helvetica color=navy>Your message has been sent!  Expect a reply at <u>$fmail</u> shortly!</font><br><br><font size=2><u>Message</u>:</font><br><font size=2 color=navy>$xmsg</font>";
  $to = "dean@listmailpro.com";
  $subj = "Your ListMail Question";
  $msg = $message;
  $header = "From: $fmail\n";
  mail($to,$subj,$msg,$header);
  }
 }


// CONFIG - ABOUT THE DATABASE

if($page == 'setupdb'){
 $helptxt = "<b>About the ListMail Database</b>

All of your ListMail settings are stored in a MySQL database on your web host.  Your information is stored apart from the ListMail files in a special directory on the server.

This gives us some interesting options.  Not only are MySQL databases very efficient for handling data, but when upgrades are released you can simply overwrite the files and ListMail will automatically update the database.  Even though the files are overwritten, your data is safe and sound!";

 $helptxt = nl2br($helptxt);
}

// CONFIG - DAILYMAIL
if($page == 'daymail'){
 $helptxt = "<b>What Dailymail Does</b>

When the dailymail script is called, each list is called in turn, it's settings read, and each user in that list examined.  If the user has a delay of 0, we process and send the currently queued followup.  If the user has a delay of more than 0, we simply decrease it so that it can be tested again the next day.


<b>Bounced Messages</b>

Dailymail is responsible for checking for and re-sending bounced messages.  If a user is bouncing they are resent the bounced message, repeatedly, by dailymail until that message doesn't bounce back.  If the message bounces back more than the set maximum allowed bounces, the user is deleted.

If messages are sent to the user while they are bouncing they are not sent but queued.  These queued messages are sent to the user if it is detected they have stopped bouncing, or deleted if the user is deleted.


<b>General Maintenance</b>

Dailymail perform various maintenance tasks such as removing unconfirmed emails, logs, and sent messages older than certain dates.";

 $helptxt=nl2br($helptxt);
} // dailymail


// CONFIG - GENERAL SETUP / CONFIG MENU

if($page=='setupgen'){
 $helptxt = "<b>Configuring ListMail with the Config Menu</b>
<br><br>
The <i>configuration menu</i> is a very important menu.  It's the first menu you need to work with when you install ListMail, and it plays an essential role in the overall operation and effectiveness of the program.  You must know how to properly enter your information.
<br><br>
At first glance there seem to be a lot of options, I will explain each section and the options within.
<a name=gen>
The <b>General Config</b> section is fairly straightforward, it contains some important fields.
<ul><li><u>Admin Password</u> - The password you use to login to ListMail.
<li><u>Dailymail Password</u> - The password you enter into the scheduled cron command on your host.
<li><u>URL to ListMail</u> - Needs to be correct, ie. http://yoursite.com/mail/
<li><u>Admin Email Address</u> - This should be an address @yoursite.com for best testing results.
<li><u>Remote Test Address</u> - Email address used for testing, should not be on your server, but rather @yourisp, @hotmail.com, @yahoo.com, etc.
</ul><a name=smtp>
Next is the <b>Mail Sending Method</b> section.  This section determines how large batches of email are sent out.
<br><br>
<ul><li><u>SMTP sending</u> - This option is highly recommended.  You are required to enter your authorization information, exactly as you would in an email client.  If necessary, set up your email checking mailbox.  Allow some room for error, if your server calls for you to check email every 20 minutes, for example, enter a setting of 19.
<li><u>Without SMTP</u> -  There are severe delays for sending email this way.  Each opens up a new process on the server.  If too many are opened at once, the server will cancel the script.  Therefore you need to make sure your delay is very high.  I recommend sending 100 messages and then pausing for 2 minutes.  Your server should allow between 100 and 500 processes.  The delays are necessary to ensure the server has time to perform a DNS lookup, send the email, and close.</ul><a name=errmbox>
The <b>Bounced Message Handling</b> section determines how to handle messages that aren't accepted for delivery.
<br><br>
It is recommended you use the <i>bounce.cgi</i> CGI script which performs on-the-fly removes instantly, as bounces come in.  Although this method is a bit trickier to set up, it doesn't have problems with large numbers of bounces like the email account option does.
<br><br>
Step 1: Edit & upload bounce.cgi
<br><br>
<u>Edit</u> the file <i>bounce.cgi</i>, it came with ListMail.  Set the \$listmail_url variable correctly for your site.  Upload the file into your <i>cgi-bin</i> directory with FTP.  Right click on the remote file and CHMOD the permissions of the bounce.cgi file to be 755 (full access for the owner, and read/execute access for others)
<br><br>
Step 2: Set up forwarding
<br><br>
ListMail supports two common unix mailers, Sendmail and Qmail.  To see what your server uses, send yourself an email and look at the properties of the message, the full source.  At the top of it you should see some lines that say \"received by sendmail\" or \"received by qmail\".
<br><br>
<ul><li><u>Sendmail</u> - Your server should support <i>procmail</i>.  We need to put a file called <i>.procmailrc</i> in your HOME directory.
<br><br>
Start a new file with Notepad.  Copy & paste the following 3 lines of text:
<br><br>
<span style=\"background: #eeeeee\">:0<br>
* ^TO_bounce@yourdomain.com<br>
| \$HOME/cgi-bin/bounce.cgi</span>
<br><br>
Replace <i>bounce@yourdomain.com</i> with your ListMail bounce address and make sure the path to the CGI script is correct.  Leave everything else exactly as shown.
<br><br>
Save the file as procmailrc.txt, or similar, in a temporary folder that will be easy to access with your FTP program.
<br><br>
Upload the file into your home directory (not your public html directory, the one up from that) and rename the new remote file to <i>.procmailrc</i>
<br><br>
<li><u>Qmail</u> - Create a <i>.qmail-bounce</i> (bounce being the mailbox name ie. bounce@site.com) file in your HOME directory containing the following single line.
<br><br>
<span style=\"background: #eeeeee\">|\$HOME/cgi-bin/bounce.cgi</span><br>
<br>
Change the path if necessary, with the full path to your home dir and file. (ie. |/usr/account/www/domain.com/cgi-bin/bounce.cgi)<br>
<br>
<li><u>valiases (cPanel, etc)</u> - Login via SSH or FTP and modify the file <i>/etc/valiases/yourdomain.com</i>, in it add the following line:
<br><br>
<span style=\"background: #eeeeee\">bounce@yoursite.com: \"|/home/username/public_html/cgi-bin/bounce.cgi\"</span>
<br>
</ul>
Once you discover how it's done on your server you can also setup signup and removal scripts with this method.
<br><br>
To test if bounce.cgi is working, make sure your admin and bounce email addresss are set in the config menu.  Send an email to your bounce address with the subject, in capitals, \"<b>TESTBOUNCE</b>\".  Moments later, you should receive a success message at the administrator address.
<br><br>
If the above procedure doesn't work, ask your host to create an alias for you.  They should be more than willing to help you keep your list clean of bouncing users.  For qmail they may need to create the <i>.qmail-bounce</i> file in their qmail/mailnames/yoursite.com directory and set it to the right user/permissions.  For sendmail they may need to edit the /etc/mail/aliases file.  Make sure to inform them about the correct path to bounce.cgi and ask that all emails to your bounce address are forwarded through the cgi script.
<br><br>
<u>Note:</u> On a Windows platform you can't have a file with a period as the first letter of the filename,  ie: <i>.procmailrc .qmail-bounce</i>, or otherwise.  It is recommended that you rename the file once you have uploaded it with FTP.  (This will probably also make the file invisible.)
<br><br>
Using an <b>email account</b> to process bounced messages:<br>
<ul><li>Simply create an account such as bounce@yoursite.com and enter the login information.  The mailbox will be checked for bounced messages by the dailymail script.</ul>
The <i>mail sending method</i> and <i>bounced message handling</i> bounce to email address information needs to be correct for mail to be sent correctly.
<br><br>
If you dont use bounce.cgi or a mailbox, messages will be returned to the admin email address which MUST be @yoursite.com.<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=lists>List Config</a><br>
";
// $helptxt=nl2br($helptxt);
} // dailymail

// LISTS - LIST CONFIG PAGE
if($page=='lists'){
 $helptxt = "<b>List Configuration Menu</b>
<br><br>
The <i>list settings menu</i> is where you add and set up your lists.  You need to be familiar with all of the options.
<br><br>
The <b>Mail List Settings</b> section of this page contains several important fields that determine how your messages appear when they are sent.
<br><br>
<ul><li><u>List Ref. #</u> - Used to refer to your list within the program.  It is recommended that you leave this at the default when creating lists.
<li><u>List Title</u> - Only really used within the program.  Just enter a short description so you recognize the list in the dropdown menus.
<li><u>Send as Name</u> - The name you want all emails to this list be sent from.  For example, \"My Newsletter\".  This will show up in the From: field in the recipient's inbox.
<li><u>Send as Email</u> - This is sent with the <i>Send as Name</i> and is where the message appears to be from.  Replies are returned to this address.
</ul>
<a name=notifs>
Enabling <b>Email Notification</b> will send an email to the specified email addresses when a user subscribes, unsubscribes, is added, or is deleted for bouncing.  The email contains the user's full information, including any active custom fields and their values.<br><br>
<a name=welc>
The <b>Welcome Email</b> and <b>Confirmation Email</b>.
<br><br>
<ul><li>The <i>welcome message</i> is always sent when a user subscribes to your list.
<li>The <i>confirmation message</i> is optional.  Select the <i>Use confirmation process?</i> checkbox to enable it.  This function will send a confirmation email to each subscriber.  If the user doesn't reply by clicking the link within the set number of days, they are deleted.  </ul>
The <b>!confirm</b> message code must be used in the confirmation message to output the confirmation link.  If the code is missing, the link will be automatically added at the bottom of the message.
<br><br>
Enter each message's <u>subject</u>, <u>text</u> message, and optionally, the <u>HTML</u> message and <u>attachments.</u><br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=editfups>Followups</a>, <a href=./help.php?list=$list&popup=$popup&page=customhtml>Custom HTML</a>, <a href=./help.php?list=$list&popup=$popup&page=customfields>Custom Fields</a>, <a href=./help.php?list=$list&popup=$popup&page=supforms>Signup Forms</a>
</small>
";
}

// LISTS - FOLLOWUPS

if($page=='editfups'){
 $helptxt = "<b>Setting up Followup Messages</b><br>
<br>
With ListMail, any list can have any number of timed followup messages.  Users start receiving these messages in the days following their subscription.
<br><Br>To configure the followups for the current list, simply click the \"Followups\" button on the main menu.
<br><br>
Two important followup-related variables you need to be familiar with are the <u>Seq #</u> and the <u>Delay #</u>.  These variables determine the order in which the followups are sent as well as how many days to delay before sending each followup.<br>
<br>
Your followups should be entered in the sequence you wish them sent, ie.  1, 2, 3, 5, 12, with no duplicates.  If you delete a message in the middle of the sequence, you do NOT have to renumber your messages.  The program will automatically skip to the next one as if it were the very next number.
<br><br>
<i>Be careful</i> when changing sequence numbers on live lists. If users are already halfway through the sequence you can end up sending them the wrong emails.<br>
<br>
Note: If you add a followup at the end of the sequence and there are users who have already completed the sequence, they are sent the new message(s) the next time(s) dailymail is executed.<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=lists>List Config</a>, <a href=./help.php?list=$list&popup=$popup&page=customhtml>Custom HTML</a>, <a href=./help.php?list=$list&popup=$popup&page=customfields>Custom Fields</a>, <a href=./help.php?list=$list&popup=$popup&page=supforms>Signup Forms</a>
</small>
";
}

// LISTS - CUSTOM HTML
if($page=='customhtml'){
 $helptxt = "<b>Custom HTML Options</b><br>
<br>
ListMail lets you customize the web pages displayed to the user when subscribing and unsubscribing, even the error pages!  You can set it up to look like your site, or have the page automatically forward the user to another page after signup.  The choices are endless because you can enter a full HTML page that has dynamic data from ListMail inserted directly into it!
<br><br>
Each list has it's own customizable pages.  Just click the <u>Custom HTML</u> button, found in the <i>list settings</i> menu, to edit the current list.<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=lists>List Config</a>, <a href=./help.php?list=$list&popup=$popup&page=editfups>Followups</a>, <a href=./help.php?list=$list&popup=$popup&page=customfields>Custom Fields</a>, <a href=./help.php?list=$list&popup=$popup&page=supforms>Signup Forms</a>
</small>";
}

// LISTS - CUSTOM FIELDS
if($page =='customfields'){
 $helptxt = "<b>Custom List Fields</b><br><br>
ListMail lets you create up to 10 custom fields for each list.  This allows you collect any type of information you like about your users.  You can then create custom message codes that will display the user's entered information in any email.
<br><br>
The <u>Custom Fields</u> button can be found in the <i>list settings</i> page.  Simply enter a short description and the field will be considered \"active\".  When active, the field will be shown in the user adding & editing pages as well as the signup code generator for the current list.<br>
<br>
Note that even if a field is not named, or active, it is still usable in your signup form and message codes.  However, the field won't show up when editing a user, or with the signup code generator, unless you give it a name.<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=lists>List Config</a>, <a href=./help.php?list=$list&popup=$popup&page=editfups>Followups</a>, <a href=./help.php?list=$list&popup=$popup&page=customhtml>Custom HTML</a>, <a href=./help.php?list=$list&popup=$popup&page=supforms>Signup Forms</a>
</small>";
}

// LISTS - SIGNUP FORMS
if($page == 'supforms'){
 $helptxt = "<b>Creating Signup Forms</b><br><br>
ListMail comes with an easy to use generator to get you started with the design of your subscription forms.  Look for the <u>Signup Code</u> button in the <i>list settings</i> menu.
<br><br>
Several variables can be used in the signup form.  Always remember to have your form point to the ListMail signup.php, wherever it is.
<br><br>
Here's a list of variables that can be used in the form.
<ul><li><u>list</u> - The List Ref # of the list to subscribe to.
<li><u>lists[]</u> - Array used for multi-list subscribe (see below)
<li><u>fname</u> - The user's first name.
<li><u>lname</u> - The user's last name.
<li><u>email</u> - The user's email address.
<li><u>user1</u> thru <u>user10</u> - Custom fields.
<li><u>submode</u> - Can have a value of <i>sub</i> or <i>unsub</i>.
<li><u>htmail</u> - Can have a value of <i>html</i> or <i>text</i>.
</ul>
These variables are essential to the operation of the signup script.  The list and email fields are required, the rest are optional.<br><br>
To provide the user a <b>choice between lists</b>, use the following code to declare the <i>list</i> variable.<br><br>
&lt;input type=radio name=list value=1&gt;Subscribe to list 1<br>
&lt;input type=radio name=list value=2&gt;Subscribe to list 2<br>
&lt;input type=radio name=list value=3&gt;etc..<br>
<br>
To provide a <b>multi-list subscribe</b> form, use the <i>lists[]</i> array.  Set up your form as follows:<br><br>
&lt;input type=hidden name=lists[] value=1&gt;<br>
&lt;input type=checkbox name=lists[] value=2&gt;Subscribe to list 2<br>
&lt;input type=checkbox name=lists[] value=3&gt;Subscribe to list 3<br>
<br>
Each list is checked for duplicates and required information.  Any errors will return the first error-producing list's Custom HTML error page and halt the signup.  A successful signup with multiple lists will result in the first successful list's Custom HTML page being shown.  Each successfully subscribed to list's settings for welcome and/or confirmation emails are checked, and the messages sent, if enabled.<br>
</font><br>

<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=lists>List Config</a>, <a href=./help.php?list=$list&popup=$popup&page=editfups>Followups</a>, <a href=./help.php?list=$list&popup=$popup&page=customhtml>Custom HTML</a>, <a href=./help.php?list=$list&popup=$popup&page=customfields>Custom Fields</a>
</small>
";


}
if($page == 'editusers'){
$helptxt = "<b>Using the User Database</b><br>
<br>
The <i>user database</i> menu is where you manage the users subscribed to your lists.
<br><br>
You can manually add and import users, edit user details, and search the database.
<br><br>
By default, display starts on the first page of users for the current list.  You can edit the list #, first and last names, the user's email address, and followup sequencing values from this view.  Just dont forget to save!  Press the <u>Edit</u> button next to the user to edit the rest of the user's information.<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=adduser>Manually Adding a User</a>, <a href=./help.php?list=$list&popup=$popup&page=import>Importing Users</a>";
}

if($page == 'adduser'){
 $helptxt = "<b>Manually Adding a User</b><br><br>
Manually adding a user to a list from within ListMail is easy.  Simply click the <u>Add User</u> button in the <i>user database</i> menu and you will be brought to the editing page for the new user.  This page looks almost the same as the normal user details page, but with an added function.  You have the ability to send the welcome email to the user when you save their info.<br><br>
Enter the user's info and hit the <u>Save</u> button.  If you decide not to add the user, be sure to hit <u>Cancel</u>, as it will delete the newly inserted row in the database.
<br><br>
When adding a user, their email address is checked to ensure it's not in an invalid format and is not a duplicate entry in the list.
<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=import>Importing Users</a>";
}

if($page == 'import'){
 $helptxt = "<b>Importing Users</b><br><br>
You can import your existing users from a CSV (comma separated values) file.
<br><br>
First, make sure the current list is the one you want to add to.  Next, simply click the <u>Import Users</u> button at the top of the <i>user database</i> page.
<br><br>
<b>From a text file</b>: Click the <u>Browse</u> button and find the file you want attached on your local hard drive and click <u>OK</u>.  Now press <u>Send</u> and wait for the file to upload.  If you cannot upload the file through the script, upload it with FTP to the ListMail directory and enter the filename in the box just below.
<br><br>
<b>From MySQL</b>:  Enter your connection information including <i>hostname</i>, <i>username</i>, <i>password</i>, <i>database</i>, and <i>table</i> name.
<br><br>
The program will read the file, or database, and discover how many fields it contains.  You will then be presented with a field match page where you can assign which fields you want inserted where.
<br><br>
When done selecting the field types, click <u>Import Users</u> and the users will be inserted into the list, with the data being distributed into the specified fields.
<br><br>
<small><i>See also:</i> <a href=./help.php?list=$list&popup=$popup&page=adduser>Manually Adding a User</a>";
}

if($page=='msgcodes'){
$helptxt = "<b>Message Codes</b><br><br>
Message codes allow you to enter dynamic information into your messages.  Simply place a code in the body of a message and it will be replaced with the user's first name, last name, full name, email, the current list title, the date, and so on.  These codes allow you to better personalize contact with your customers and make your services look all that more professional.
<br><br>
The <i>message codes</i> menu is where you can configure the codes for the current list, as well as global codes.
<br><br>
Some of these codes are essential.  For example, you should always include a message code of the <i>remove</i> type.  Codes of this type will output an automatically generated link where your subscriber can quickly leave your list if he or she desires.
<br><br>
<small><i>See also: </i> <a href=./help.php?list=$list&popup=$popup&page=linktrack>Link Tracking</a>, <a href=./help.php?list=$list&popup=$popup&page=fattach>File Attachments</a></small>";
} // codes


if($page == 'linktrack'){
 $helptxt = "<b>Link Tracking</b><br>
<br>
ListMail allows you to quickly and easily enter hard to remember links into your messages through message codes.  Simply enter the code for a link and it will be replaced with a URL such as http://yoursite.com/mail/link.php?id=code.  When a user clicks on this link, ListMail will count the hit and redirect them to the URL configured in the <i>link tracking</i> menu.
<br><br>
In an HTML message matching codes will be replaced with the link's title appearing as the clickable link.  Take care when setting your link titles if you're using HTML email.
<br><br>
You can track individual user info from clicked links.  This will record the user's name, email address, and list number when the link is clicked.  Simply turn on the <i>track user info</i> option in the <i>tracked links</i> menu.  Then click the <b>tracked hits</b> button to view tracked information.<br><br>
<small><i>See also: </i> <a href=./help.php?list=$list&popup=$popup&page=msgcodes>Message Codes</a>, <a href=./help.php?list=$list&popup=$popup&page=fattach>File Attachments</a></small>";
} // links

if($page=='sendmail'){
 $helptxt = "<b>Sending Email</b><br><br>
To send an email to the current list, click the <u>Send Email</u> button on the main menu.  You will be presented with a message composition page where you can input, save, and load messages.  Enter your text-only or HTML+text message, click send, and away it goes!
<br><br>
To send a message to all lists change the current list to 'All'.  Messages sent to all lists are sorted for duplicate email addresses.<br><br>
Take care that you have the proper list selected when sending your emails.  Generally, it's preferable to choose your list first, load your message second, and lastly send.
<br><br>

<small><i>See also: </i> <a href=./help.php?list=$list&popup=$popup&page=msgcodes>Message Codes</a>, <a href=./help.php?list=$list&popup=$popup&page=linktrack>Link Tracking</a>, <a href=./help.php?list=$list&popup=$popup&page=fattach>File Attachments</a></small>
 ";
}

if($page=='fattach'){

$helptxt = "<b>File Attachments</b><br><br>
To attach files to your messages, click the <u>Add</u> button next to the file attachment field in any message editing page.  This will pop up a convenient upload and attachment utility.  To use this feature, make sure your /attach/ directory is CHMOD'd to 777 with your FTP program to give PHP full read and write permissions.
<br><br>
Once your files are uploaded, simply check off the ones you want attached to your message and click <u>Attach</u> in the popup and the attachment field will be set for you.
<br><br>
<small><i>See also: </i> <a href=./help.php?list=$list&popup=$popup&page=msgcodes>Message Codes</a>, <a href=./help.php?list=$list&popup=$popup&page=linktrack>Link Tracking</a></small>

";
}

/* *******************************************************************************************
***************************************************************************************** */
// OLD STUFF
if($page == 'inst'){
$helptxt = "<b>Checking Requirements</b>
<br><br>
Your web host must support PHP scripting and MySQL databases in order to run ListMail.
<br><br>
Your web hosting plan should specifically indicate whether or not your site supports PHP/MySQL.
<br><br>
If you're not sure, you can perform a manual test by uploading a PHP file into your web directory and attempting to run it.  The file should be named \"something.php\" and contain a few lines, such as follows:
<br><br>
<font style=\"font-family: courier; background:#eeeeee\">
&lt;?php<br>
phpinfo();<br>
?&gt;
</font>
<br><br>
Upload this file to your web host and access it via \"http://yoursite.com/something.php\", it should be interpreted and the phpinfo() function should execute and display a whole bunch of information to you.  If your server supports PHP, it will show you this information.  If it doesn't, it won't.
<br><br>
Testing MySQL is a bit harder, what you need is your username, password, and database name.  I suggest looking around your web provider's site and checking the details of the hosting plan you purchased.  If you can't find anything on the topic, send a message to your technical support staff.
<br><br>
To test MySQL create the following file \"testsql.php\" and run it on your host with your MySQL username, password, and database name.
<br><br>
<font style=\"font-family: courier; background:#eeeeee\">
&lt;?php<br>
mysql_connect('localhost','yourusername','yourpassword') or die(\"Can't connect.\");<br>
echo \"Connected Ok.&lt;br&gt;\";<br>
mysql_selectdb('yourdbname') or die (\"Could not access database.\");<br>
echo \"Database Ok.&lt;br&gt;\";<br>
?&gt;<br><br>
</font>
<b>Unzipping the ListMail Distribution File</b>
<br><br>
If you don't already have the common utility WinZip, you can download it from <a href=http://winzip.com/getsite.cgi?winzip80.exe>WinZip.com</a>.  It will allow you to get at the files inside the distribution package so that you can start setting up ListMail.
<br><br>
When running the WinZip setup program, you can uncheck the options to install icons anywhere.  This is because you can simply right-click on a .zip file in Windows Explorer to access a quick extracting link.
<br><br>
Once WinZip is installed, right click on the file ListMail.zip in Windows Explorer and choose \"Extract to folder ListMail/\".  This will create a sub folder named \"ListMail\" within the current folder.  Double click the folder to open it.
<br><br>
<b>Editing the Config File</b><br><br>
Once you have navigated to the ListMail directory where you extracted the .zip file, double click on the file \"config.php\".  You will be asked which program to open the file with, scroll down to Notepad.  Select it and press \"ok\", or simply double-click it.
<br><br>
You will need to change a few things in order to get ListMail up and running on your server.
<br><br>
You'll see the following entries:
<br><br>
// Your ISP Database Information (host will always be localhost)<br>
\$sqlhost = 'localhost';<br>
\$sqluser = 'dean';<br>
\$sqlpass = 'yourpassword';<br>
\$sqldb = 'listmail';
<br><br>
These are the settings for your ISP-specific database access Username, Password, and Database names.  If your web host supports MySQL you should be provided with this information.  Take a look at the tech support and member areas of their site.  If this fails and you are sure you have purchased a hosting plan that includes PHP and MySQL, contact the tech staff directly by email.  Ask them what your MySQL username, password, and database values should be.
<br><br>
Make sure you only edit the text between the quotes and nothing else.  This file must maintain conformity with the PHP code syntax.
<br><br>
Follow the example provided, replace the example username, password, and database name with your own information.
<br><br>
Save the config.php file and proceed to upload the files to your web server.<br><br>
<b>Uploading the ListMail Files</b>
<br><br>
Hopefully, you're familiar with FTP, the File Transfer Protocol.  If you have a web site and are managing the files for it, you'll use an FTP client to upload and download files.  A popular FTP program is <a href=ftp://ftp1.ipswitch.com/ipswitch/product_downloads/ws_ftple.exe>WS_FTP</a> which is free for home users.
<br><br>
Connect to ftp.yoursite.com with the FTP client and make a sub-directory for ListMail in your main web directory (usually \"public_html\").  You can name this directory anything you want, like \"mail\".  It's up to you, the directory ListMail is installed to doesn't affect it's operation, you specify what directory you installed it to later in the config menu.
<br><br>
Once you've created the sub-directory, upload all of the ListMail scripts into it.
<br><br>
<b>Setting Permissions on Attachments Directory</b><br><Br>
You must set the CHMOD file permissions for the /attach/ directory to 777 or read, write, & execute access for all users.  This is to allow easy upload and attaching via the attachment popup.  If you don't do this, you will need to upload your attachments manually.
<br><br>
<b>Installing the Database Tables</b><br><br>
Now that the files are uploaded to your web server, you need to set up the database tables used by ListMail.  If your MySQL username, password, and database name are entered correctly into the config.php file, you're all set to install them.
<br><br>
Load ListMail by browsing to the directory you installed it to by typing \"http://yoursite.com/mail\" into your IE address bar.  You will be presented with the ListMail login box.  Enter \"install\" as the password.
<br><br>
Go to the \"Config\" menu and click on \"Install Databases\".
<br><br>
If all goes well, you will see a series of install progress messages and there will be no error messages.  If not, then you probably didn't enter the correct username/password/database information into config.php.  Check with your ISP to make sure that your site is configured to access MySQL and that your username, password, and database name (in config.php) are the same as provided to you by your ISP.  Be sure that you re-upload the config.php file to the web server before you try to install the databases again!
<br><br>
If you're unsure about your MySQL username, password, and database name, check the help section of your host's web site or contact their technical staff.
<br><br>
<b>Configuring ListMail</b>
<br><br>
Now that the databases are installed, you need to set up your ListMail setup options.
<br><br>
Again, click on \"Config\" in the ListMail admin menu.  If the databases were installed correctly, you'll now be shown a list of options you can change.  These are the ListMail configuration options.
<br><br>
Enter your desired ListMail password, your email address, and the full URL to ListMail with a trailing slash.  The other entries can remain at their default settings until you decide to change them.
<br><br>
Press \"Save\".
<br><br>
Don't forget your password because you will need it to log in to ListMail in the future!
<br><br>
<b>Scheduling the Daily Script on Your Web Host</b>
<br><br>
The last thing that needs to be done is for the daily mail script to run every day on your web host.  This is done with what's called a \"cron\" or \"crontab\" entry.  Cron is a program that schedules tasks on most web hosts.
<br><br>
If you cannot enter cron commands for your account yourself through a control panel, contact the technical support staff at your ISP.  They will more than likely help you by entering the required command for you into your account's cron settings.
<br><br>
The single-line command that needs to be entered into your cron file is as follows:
<br><br>
<font style=\"font-family: courier; background:#eeeeee\">0 0 * * * /usr/bin/wget -O - http://yoursite.com/mail/dailymail.php?pw=YourDailyMailPass</font>
<br><br>
Here's an explanation of the command: the <font style=\"font-family: courier; background:#eeeeee\">0 0 * * *</font> part determines the time and frequency of the execution of the command.  This command will execute every day at midnight.  If you wanted to run the command at say 3:15 am, you would enter <font style=\"font-family: courier; background:#eeeeee\">15 3 * * *</font>
<br><br>
The next part, <font style=\"font-family: courier; background:#eeeeee\">/usr/bin/wget</font> specifies the command to be executed.  \"wget\" is a command-line tool to grab a web page.  The paramater <font style=\"font-family: courier; background:#eeeeee\">-O -</font> tells wget not to output the webpage to a file as it normally does.
<br><br>
And finally, the URL to your dailymail.php file with your password is required for wget to grab each day.
<br><br>
That's it!  ListMail should now be completely operative.  You can now start editing your first list's settings and followups!";
}

if($page == 'conf'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Using the \"Config\" Menu.</font></u><br>
<br>
The Config menu is where you set your general, script-wide administrative options.
<br><br>
<b>Admin Password</b>:  Your ListMail administrator password, stored in a database to prevent its display.
<br><br>
<b>Admin Email</b>:  Contact email for errors.
<br><br>
<b>Full ListMail Path</b>:  The full path to your installation of ListMail.  You can put ListMail in any directory you want as long as you set it here and in your scheduled dailymail URL.
<br><br>
<b>Message Code Character</b>:  This is the character that signals ListMail to interpret a message code.  See the Message Code help file for more informatoin.
<br><br>
<b>Link Code Addition</b>:  This is the string that follows the message code character to signify that the code should be replaced with a url pointing to a link in the link database.
<br><br>
<b>Users Per Page</b>:  The number of users to show per page in the user editing menu.  Defaults to 8 for best visibility of the bottom toolbar on 800x600 resolution displays.
<br>";
} // conf menu

if($page == 'list'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Using the \"Lists\" Menu.</font></u><br>
<br>
<b>Add List</b>:  Adds a new list to the database.  The list receives the next list ref # and has some default values inserted into it.
<br><br>
<b>Send Email</b>:  Opens a message editing page to send a one-time mailing to the entire current list.  Messages codes are active and displayed.
<br><br>
<b>Save List</b>:  Saves the current list.  If you make any changes, don't forget to press this button to apply it to the database!
<br><br>
<b>Delete List</b>:  Deletes the current list.
<br><br>
<b>Get Form Code</b>:  Displays code that can be cut and pasted into your web site source to subscribe users to the current list.
<br><br>
<b>List Ref #</b>: The list's reference number, used to access the list in all scripts.
<br><br>
<b>List Title</b>:  A short title for your list shown when users unsubscribe.  Also used for the list title message code.
<br><br>
<b>Send As Name</b>:  The name emails for the list appear to be coming from.
<br><br>
<b>Send As Email</b>:  The email address from which messages to the list appear to be coming from.  This must be something@yourdomain.com or your server most likely won't send any emails.
<br><br>
<b>Errors To Email</b>:  The email address at which bounced emails for the list are received.
<br><br>
<b>Welcome Subject</b>:  The subject of the welcome email, sent when someone subscribes to the list.  Message codes can be used.
<br><br>
<b>Welcome Message</b>:  The body of the welcome email.  Message codes can be used.
<br><br>
<b>Remove Text</b>:  The text that automatically appears above the remove URL when you use the !remove code in messages to the list.
";
} // lists menu

if($page == 'fups'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Using the \"Followups\" Menu.</font></u><br>
<br>
Followups are shown one list at a time, use the dropdown menu (top left) to change the current list.
<br><br>
Followups MUST be numbered sequentially, starting at 1.
<br><br>
If a followup is missing either the subject or the message body, no email will be sent by dailymail.
<br><br>
If you set a followup to a delay of 0, it will be sent the very next day.  Whereas if you assign it a delay of 1, it will be delayed 1 day and sent the day after that.
<br><br>
<b>Add Followup</b>:  Adds a followup to the end of the sequence for the current list.  You must navigate to the empty followup and press the Edit button.
<br><br>
<b>Save</b>:  Performs a quick save of the editable subject, sequence and delay options.
<br><br>
<b>Edit</b>:  Brings up the followup editing menu.  Here you can edit the followup's message body.  Message codes are active and displayed.
<br><br>
<b>Delete</b>:  Deletes the followup.<br>";
} // fups menu

if($page == 'users'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Using the \"Users\" Menu.</font></u><br>
<br>
<b>Add User</b>:  Manually add a new user.  You will be required to know the desired list # to add the user to.
<br><br>
<b>Wildcard Email Search</b>:  Searches for a substring in all emails for all lists.
<br><br>
<b>Reset Sequence List #</b>:  This will reset the followup sequence back to #1 for all users of the specified list.
<br><br>
<b>Mail All</b>:  Sends an email to all users in all lists.
<br><br>
<b>Import Users</b>:  Limited import commands, currently only supports simple 1 email per line text files and local databases.  If you have something to import that's not supported, let me know and I'll program a custom conversion method just for you.
<br><br>
<b>Member of List #</b>:  The list the user is subscribed to.
<br><br>
<b>User's Seq #</b>:  The followup sequence that the current user is on.
<br><br>
<b>User's Delay #</b>:  Days before the next message is sent to this user.<br>";
} // user menu

if($page == 'login'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">The Admin Login Process.</font></u><br>
<br>
Logins are validated with cookies and what are called \"sessions\".  You can only get access logging in with the correct administrator password.  When you log in, a temporary access cookie is stored on your hard drive.  When you log out, this cookie is deleted.  Always be sure to log out or someone could very easily use the computer to access your admin options.
<br>";
} // login

if($page == 'codes'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Using Message Codes.</font></u><br>
<br>
Message codes allow you to enter dynamic information into all of your emails.  You simply place a code in the body of a message and it will be replaced with the user's first name, last name, full name, email, the current list title, the date, and so on.
<br><br>
These codes allow you to better personalize your contact with your customers and make your services look all that more professional.  For a complete list of message codes, they are displayed on any message body editing page.
<br><br>
Message codes can be used in both TEXT and HTML emails.  In HTML emails, obviously the links can't be shown the same way because we want them to highlight.  Therefore, the following rules apply to sending HTML mail with message codes:
<br><br>
Links from the link database will be replaced with <span style=background:#eeeeee>&lt;a href=(hit count & redirect)&gt;LINK TITLE&lt;/a&gt;</span>.  The link's title will automatically appear as the clickable link, so take care when setting your titles in the link database if you're using HTML email.  No &lt;br&gt; is automatically put at the end of the link, so if you want a new line, don't forget!
<br><br>
The <span style=background:#eeeeee>!remove</span> message code works in a similar fashion with HTML mail.  The remove text is shown and then a link is displayed below with the name \"Unsubscribe\"
<br><br>
Unfortunately, style sheets cannot be used to easily change how these links look.  Mail programs don't include this functionality.
<br>
";
} // codes


// SCHEDULER

if($page == 'scheduler'){
$helptxt = "<u><font style=\"font: 11pt helvetica\">Scheduling Messages.</font></u><br>
<br>
The scheduler allows you to set a message to be sent at a later date.  This allows you to write your newsletter in advance and send messages while you are away.  It's also good for people with dial-up connections and large lists.  Since it runs in dailymail, you don't have to have an open connection, with your browser, to the script.<br><br>
The message is saved and will be sent by dailymail when it executes on the set date.
<br><br>
Keep in mind that times can vary.  The actual day/time your message is sent is based on your server's current time and your dailymail execution time.<br><br>
<small><i>See also: </i> <a href=./help.php?num=0&popup=$popup&page=sendmail>Sending Email</a></small>

";


}

// BEGIN MAIN PAGE

if($popup) $logo = 'nologo'; else $logo = '';
adminheader('LM: Help','Help',$logo);
if(!$popup){
  echo "
<table width=760 border=0 cellspacing=0 cellpadding=0>
 <tr>
  <td valign=top width=1>\n";

  listmenu($list,'help');

  echo "  </td>
  <td width=10><img src=1.gif width=10></td>
  <td valign=top>";
}

 // test if index or help page
 // show help page
 echo "  <table width=100% class=thinbord border=0 cellspacing=0 cellpadding=0><tr><td class=bg1><span class=confhead>ListMail Help</span></td></tr></table><img src=1.gif height=10><br>

<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td valign=top width=1>
 <tr>
  <td valign=top>
   <font class=hmt>";
 if($page) echo $helptxt; else {
  echo "Here you will find help on the various functions and features of ListMail.  You will learn how to configure and use the program to it's fullest capabilities.<br><br>Simply choose a help topic from the menu at right.<br><br>
If you have any questions, just can't get it installed, want to request a feature, or have an appealing business proposition, please type it in below, or email me at <a href=mailto:dean@listmailpro.com?subject=Ask%20me style=\"color: black\">dean@listmailpro.com</a> and you will be heard.
<br><form name=form1 method=post><input type=hidden name=domail value=yes><span class=conflist>Your email:<br><input class=xbox type=text size=25 name=fmail><br><img src=1.gif height=5><br>Message:<br><textarea name=message class=xbox cols=80 rows=5></textarea><br><img src=1.gif height=8><br><input type=submit class=button value=\"Send Request\"></span><br>";
 }
 echo "</font>
  </td>
<td width=10><img src=1.gif width=10></td>
<td width=1 bgcolor=black><img src=1.gif width=1></td>
<td width=10><img src=1.gif width=10></td>
  <td width=140 valign=top>\n";

echo "<u><font style=\"font: 11pt helvetica\">Installation:</font></u><br>
 <img src=1.gif height=2><br>
 <a href=./help.php?list=$list&popup=$popup&page=inst class=hml>Installation Howto</a><br>
 <img src=1.gif height=10><br>

<u><font style=\"font: 11pt helvetica\">Configuration:</font></u><br>
 <img src=1.gif height=2><br>
 <!-- config / faq -->
 <a href=./help.php?list=$list&popup=$popup&page=setupdb class=hml>About the Database</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=daymail class=hml>About Dailymail</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=setupgen class=hml>Config Menu</a><br>
 <img src=1.gif height=10><br>
<u><font style=\"font: 11pt helvetica\">List Setup:</font></u><br>
 <img src=1.gif height=2><br>
 <!-- lists -->
 <a href=./help.php?list=$list&popup=$popup&page=lists class=hml>List Configuration</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=editfups class=hml>Followups</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=customhtml class=hml>Custom HTML</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=customfields class=hml>Custom Fields</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=supforms class=hml>Signup Forms</a><br>

 <img src=1.gif height=10><br>
<u><font style=\"font: 11pt helvetica\">Managing Users:</font></u><br>
 <img src=1.gif height=2><br>
 <!-- users -->
 <a href=./help.php?list=$list&popup=$popup&page=editusers class=hml>User Database</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=adduser class=hml>Manually Adding Users</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=import class=hml>Importing Users</a><br>

 <img src=1.gif height=10><br>
<u><font style=\"font: 11pt helvetica\">Sending Email:</font></u><br>
 <img src=1.gif height=2><br>
 <!-- emailing -->
 <a href=./help.php?list=$list&popup=$popup&page=sendmail class=hml>Sending Email</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=scheduler class=hml>Scheduling Email</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=msgcodes class=hml>Message Codes</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=linktrack class=hml>Link Tracking</a><br>
 <a href=./help.php?list=$list&popup=$popup&page=fattach class=hml>File Attachments</a><br>
 <img src=1.gif height=10><br>";

if(!$popup){ echo "  </td>
 </tr>
</table>
<img src=1.gif height=5><br>";
}
echo "
</body>
</html>";

} // pw
// EOF
?>
