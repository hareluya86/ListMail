#!/usr/bin/perl

# ListMailPRO bounce2.cgi (Perl LWP POST instead of wget = tiny access_log)
# May be used if a web-page-grabbing tool such as wget is not available
# Seems to run a bit slower than the original method

# 1. Modify to contain your ListMail URL
# 2. Upload to your cgi-bin directory
# 3. CHMOD (set file permissions) to 755

# Full URL to ListMail, no trailing slash
$listmail_url = "http://www.DOMAIN.com/mail";

# End config - don't need to change anything below here!

sub encode {
  my $str = shift || '';
  $str =~ s/([^\w.-])/sprintf("%%%02X",ord($1))/eg;
  $str;
}

# get piped msg
$email = '';
while($line=<>){ $email .= $line; }

# skip
if(index($email,'THIS IS A WARNING MESSAGE ONLY')>-1){ exit; }
if(index(lc $email,'temporarily deferred')>-1){ exit; }
if(index(lc $email,'temporarily off-line')>-1){ exit; }
if(index(lc $email,'temporarily suspended')>-1){ exit; }
if(index(lc $email,'mailbox is full')>-1){ exit; }
if(index(lc $email,'over quota')>-1){ exit; }
if(index(lc $email,'retry timeout')>-1){ exit; }
if(index(lc $email,'timed out')>-1){ exit; }
if(index(lc $email,'try later')>-1){ exit; }
if(index(lc $email,'mailbox temporarily disabled')>-1){ exit; } 

if($email){
 # fw to bounce.php
 use HTTP::Request::Common qw(POST);
 use LWP::UserAgent;
 my $browser = LWP::UserAgent->new();
 $browser->agent('ListMailPRO');
 my $req = HTTP::Request->new(POST => $listmail_url.'/bounce.php');
 $req->content_type("application/x-www-form-urlencoded");
 $req->content("em=" . encode(substr($email,0,4096)));
 $browser->request($req)->as_string;
}
# done!