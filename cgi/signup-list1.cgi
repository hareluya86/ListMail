#!/usr/bin/perl
# ListMail signup.cgi - Subscribe by email
#
# You must configure this script for it to work.
#
# Place the script in your "public_html/cgi-bin" directory.  You will need multiple copies of
# this script with different filenames / email alias addresses if you want to add users to
# individual lists or different sets of lists.

# SET this to your ListMail URL, NO trailing slash
$listmail_url = "http://www.DOMAIN.com/mail";

# SET the variable $list to one of the following:

# single list
$list = '1';

# multiple lists
# $list = '1,3,2';

# parse subject into a custom field? (uncomment to enable)
# $parse_subj = 1;

# custom field for subject line data, 'user1' thru 'user10'
$parse_subj_field = 'user1';

# For multiple lists, use commas.  Each list's welcome / confirmation
# setting and email will be taken into account.

# usually works as default (may be /usr/local/bin/wget or other)
$http_program = "/usr/bin/wget -O /dev/null " . $listmail_url . "/signup.php";

# begin script

# encode function
sub encode {
  my $str = shift || '';
  $str =~ s/([^\w.-])/sprintf("%%%02X",ord($1))/eg;
  $str;
}

# get piped message
$email = '';
while($line=<>){
 $email = $email . $line;
}

if($email){
 # process msg, grab subject, email address and first / last name, if possible
 # find Subject: 
 if($parse_subj){
  use URI::Escape;
  $searchstr = "\nSubject: ";
  $strpos = index($email,$searchstr);
  if($strpos>0){
   $su = substr($email,$strpos+length($searchstr),length($email));
   $len = index($su,"\n");
   $su = uri_escape(substr($su,0,$len));
  }
 }
 # find To:
 $searchstr = "\nFrom: ";
 $strpos = index($email,$searchstr);

 if($strpos>=0){
  $thestr = substr($email,$strpos+length($searchstr),length($email));
  $npos = index($thestr,"\n");
  if($npos>=0) {
   # get email
   $thestr = substr($thestr,0,$npos);
   $lbpos = index($thestr,'<');
   if($lbpos>=0){
    $rbpos = index($thestr,'>');
    $em = substr($thestr,$lbpos+1,$rbpos-$lbpos-1)
   } else {
    $justmail = 1;
    $em = $thestr;
   }
   # check for name spaces

   $spos = index($thestr,' ');
   if($spos>=0) { $justmail = ''; } else { $justmail = 1; }

   if($justmail ne 1){
    # get name
    $name = substr($thestr,0,$lbpos-1);
    # strip quotes
    $name =~ s/\"//g;

    $spos = index($name,' ');
    if($spos>=0){
     $fn = substr($name,0,$spos);
     $ln = substr($name,$spos+1,length($name)-$spos-1);
    } else {
     $fn = $name; $ln = '';
    }
   }

   # sign up
   $sys_cmd = $http_program . "\\?cgi=1\\&list=$list\\&email=$em";
   if($fn ne '') { $sys_cmd = $sys_cmd . "\\&fname=$fn"; }
   if($ln ne '') {
    $ln = encode($ln);
    $sys_cmd = $sys_cmd . "\\&lname=$ln";
   }
   if($parse_subj ne ''){
    $sys_cmd = $sys_cmd . "\\&" . $parse_subj_field ."=$su";
   }
   system($sys_cmd . " > /dev/null 2> /dev/null");
   # notify in php
  }
 }

}