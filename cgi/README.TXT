Help with ListMail CGI scripts
 - bounced message processing
 - subscribe by email
 - unsubscribe by email

1. The scripts must be modified before they can be used.  You must edit the $listmail_url variable within the script to point to the path of your ListMail installation.

For signup and remove scripts you must create a NEW copy of the script with a different filename AND a NEW mail address / alias for EACH list AND modify the $list variable within the file.

2. On some servers the scripts may require further modification.  ListMail makes use of the Unix program "wget".  In the scripts the path is set to "/usr/bin".  Your host may require a setting of "/usr/local/bin" or other path.

3. Once modified, upload the script(s) to your "public_html/cgi-bin" directory.

4. Right-click on each remote file with FTP and set the permissions to "755", or executable for all.

5. Set up your mail aliases:

We will use bounce.cgi as an example.

ListMail supports two common unix mailers, Sendmail and Qmail. To see what your server uses, send yourself an email and look at the properties of the message, the full source. At the top of it you should see some lines that say "received by sendmail" or "received by qmail".

Sendmail:

Your server should support procmail. We need to put a file called .procmailrc in your HOME directory.

Start a new file with Notepad. Copy & paste the following 3 lines of text:

:0
* ^TO_bounce@yourdomain.com
| $HOME/cgi-bin/bounce.cgi

Replace bounce@yourdomain.com with your ListMail bounce address.  Make sure the path to the CGI script is correct, you may have to change the $HOME variable to your actual path.  Leave everything else exactly as shown.

Save the file as procmailrc.txt, or similar, in a temporary folder that will be easy to access with your FTP program.

Upload the file into your home directory (not your public html directory, the one up from that) and rename the new remote file to .procmailrc

Qmail:

Create a .qmail-bounce (bounce being the mailbox name ie. bounce@site.com) file in your HOME directory containing the following single line.

|$HOME/cgi-bin/bounce.cgi

Change the path if necessary, with the full path to your home dir and file. (ie. |/usr/account/www/domain.com/cgi-bin/bounce.cgi)
To test if it's working, send an email to your bounce to address with the subject "TESTBOUNCE". You should receive a message at your administrator email address with the subject "Test bounce success", or similar.

If the above procedure doesn't work, ask your host to create an alias for you. They should be more than willing to help you keep your list clean of bouncing users. For qmail they may need to create the .qmail-bounce file in their qmail/mailnames/yoursite.com directory and set it to the right user/permissions. For sendmail they may need to edit the /etc/mail/aliases file. Make sure to inform them about the correct path to bounce.cgi and ask that all emails to your bounce address are forwarded through the cgi script.


