Explanation of the files in the /xtra folder.

These files allow users to include their own custom PHP script before and after certain processes in ListMail.

To enable the file(s), (modify and) move them to your MAIN ListMail folder. (ie: /ListMail/xtra/signup-xtra.php becomes /ListMail/signup-xtra.php)

If a supported 'xtra' file exists in the main folder it will be automatically detected and executed by ListMail.

In the distribution archive, the files will remain in the /xtra folder. Therefore, when updates are released and applied, your custom commands will not be overwritten.  You may, however, need to modify your code if the update affects a ListMail database table used in your script.

Each file will have variables available to it depending on the event ListMail is performing at the time.  For example, in signup-xtra.php, which can be run just after signup, you have access to variables such as $uid, $fname, and $email. Please look at the top few lines of each file for a list of variables available to it.

signup-xtra.php	- AFTER a successful confirmation or signup
signup-confirm-xtra.php	- AFTER a user is instructed to confirm their email
daily-xtra.php	- AFTER dailymail
