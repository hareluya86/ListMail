#!/usr/bin/perl
#
# ListMail bounce.cgi
#
# Modify this file to contain your ListMail URL, place it in your cgi-bin directory and CHMOD it to 755.

# Full URL to ListMail, no trailing slash
$listmail_url = "http://ec2-54-213-182-145.us-west-2.compute.amazonaws.com/Listmail";

# Program and paramaters to pass URL to
# Usually works as default (may be /usr/local/bin/wget or other)
$http_program = "/usr/bin/wget -O /dev/null " . $listmail_url . "/bounce.php";

# dont need to change anything below here

sub encode {
  my $str = shift || '';
  $str =~ s/([^\w.-])/sprintf("%%%02X",ord($1))/eg;
  $str;
}

# main

# get piped message
$email = '';
while($line=<>){
 $email = $email . $line;
}

if($email){
 if($email ne ''){
  $sys_cmd = $http_program . "\\?em=" . encode(substr($email,0,4096)) . " 1> /dev/null 2> /dev/null";
 }
 # forward to bounce.php
 system($sys_cmd);
}
# done!
