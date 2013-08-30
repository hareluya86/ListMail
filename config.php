<?php
// CONFIG.PHP - ListMail Configuration File
//
// This is a .PHP file that must maintain proper syntax.  Be careful to only
// change the values between the single quotes and nothing else, or the script
// may not work correctly.
//
// Required is your database information, the host should normally be left at the
// setting, 'localhost', but not always.  Check with your host if it doesn't work.

// Enter your MySQL Information!
$sqlhost = 'localhost';
$sqluser = 'listmail';
$sqlpass = 'listmail';
$sqldb = 'listmail';

// SAVE this file as config.php, -not- config.php.txt
// UPLOAD all of the files to a new folder on your web site, such as public_html/mail/
// BROWSE to the new subdirectory

// OPTIONS are available for different server types
// These options are rarely needed but are available if problems occur
// To change options remove and add the comment characters "//" from the beginning of the lines.
// Examples are provided with their defaults
// Setting an option to '' means no value, usually off.  Be careful to keep proper PHP syntax.

// Detect line endings for Mac browsers, default 0
// ini_set('auto_detect_line_endings',1);

// Use -f setting to set return-path correctly with PHP mail()
// This is used for welcome and confirmation messages.  If you are receiving "raw" bounce
// messages from welcome emails even if you have bounce configured this may need to be set.
$phpmailf = '';
// $phpmailf = 1;

// International character set, default iso-8859-1 if not set)
$charset = '';
// $charset = 'iso-8859-1';

// Manually set path to MySQL binaries - for problems with backup / restore.
// The linux commandline utilities "mysql" and "mysqldump" need to be available for the
// backup process.  If ListMail cannot detect the path you can set it manually.

// $sqlpath = "/usr/bin/mysql/";
// $sqlpath = "/usr/local/bin/mysql/";
// $sqlpath = "/usr/local/mysql/bin/";
// $sqlpath = "/usr/local/psa/mysql/bin/";

// max random value for problematic servers (deprecated)
// $randmaxx = '50000';

// mail counter increment, default 50
// $mailcount = 50;

// path for mimeclass/mimepart (sendwelcome)
// blank is normal - use current directory always '.' comment to allow setting $mp from your script
$mp = '';

// add Maximizer option to exports
/* $maximizer = 1;
$max_header = '"MTI: Web Inquiry"
"BUSINESS_PEOPLE","1","401","E-mail Address"
"P_E-mail Address","FIRST NAME","LAST NAME","ADDRESS LINE 1","ADDRESS LINE 2","CITY","COUNTY","POSTCODE","COUNTRY","PHONE 1"
"ALPHA-39","ALPHA-39","ALPHA-39","ALPHA-39","ALPHA-39","ALPHA-59","ALPHA-21","ALPHA-21","ALPHA-21","ALPHA-119"';
$max_fields = 'em,fn,ln,u1,u2,u3,u4,u5,u6,u7'; */

// !! Do not change anything below here !!
if (get_magic_quotes_gpc()==1) { $gpc = 'yes'; }
?>
