<?php

function sendbySMTP(){

	if($smtpsend){
	  // initialize display
	  if($mtpchk=='1'){
	   $mtimer = '0';

	   // date ex. 20020207164722
	   $starttime = date("YmdHis");

	   if($solo||$dayo||$welcnf){  echo "<span class=table_inside_normal>Checking mail... </span>"; flush(); }

	   $asock = fsockopen($mtphost, $mtpport);
	   if(!$asock) $error = 1; else $error = '';
	   if(!$error){
		// connected!
		$srvmsg = fgets($asock, 1024);
		$lastmsg = substr($srvmsg, 0, 1);
		if ($lastmsg <> "+") $error = 1; else $error = '';
		if($error){
		 // server not found
		 if($solo||$dayo||$welcnf) echo " Cannot connect to <b>$mtphost</b><br>";
		} else {
		 if($solo||$dayo||$welcnf) echo " Connected!<br>Verifying username and password...";

		 // send user
		 fputs($asock, "USER $mtpuser\r\n");
		 $srvmsg = fgets($asock, 1024);
		 $lastmsg = substr($srvmsg, 0, 1);
		 if ($lastmsg <> "+") $error = 1; else $error = '';

		 // send pass
		 fputs($asock, "PASS $mtppass\r\n");
		 $srvmsg = fgets($asock, 1024);
		 $lastmsg = substr($srvmsg, 0, 1);
		 if ($lastmsg <> "+") $error = 1; else $error = '';
		 if($error) echo " Failed!<br>"; else echo " Verified!<br>";

		 if(!$error){
		  // check # of messages
		  fputs($asock, "STAT\r\n");
		  $srvmsg = fgets($asock, 1024);
		  $lastmsg = substr($srvmsg, 0, 1);
		  if ($lastmsg <> "+") $error = 1;
		 }
		 // QUIT
		 fputs($asock, "QUIT\r\n");
		 $srvmsg = fgets($asock, 1024);
		 $lastmsg = substr($srvmsg, 0, 1);
		 if ($lastmsg <> "+") $error = 1;
		 sleep(4);
		}
	   } else echo "Failed!<br>";
	  }

	  if(!$mtauth=='1') $mtauth = '';

	  // test connect
	  if($dayo || $solo || $welcnf) echo '<center><span class=table_inside_normal>Connecting to SMTP server... ';
	  flush();

	  // connect to SMTP

	  $ssock = fsockopen($mthost, $mtport);
	  if(!$ssock) $error = 1; else $error = '';
	  if(!$error){
	   // connected!
	   if($solo||$dayo||$welcnf) echo "Connected!<br>";
	   $srvmsg ='';
	   $srvmsg = getsmtpmsg($ssock);
	   if(!$error){

		if(!$error){
		 $smtpcmd = "EHLO localhost\r\n";
		 if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		 fputs($ssock, $smtpcmd);
		 $srvmsg = getsmtpmsg($ssock);
		 if($smtp_debug) logwrite($bugf,$srvmsg);
		 $lastmsg = substr($srvmsg, 0, 3);
		 if ($lastmsg <> "250"){
		  if($lastmsg=="500"){
		   if(!$helohost) $helohost = 'localhost';
		   $smtpcmd = "HELO $helohost\r\n";
		   if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		   fputs($ssock, $smtpcmd);
		   $srvmsg = getsmtpmsg($ssock);
		   if($smtp_debug) logwrite($bugf,$srvmsg);
		   $lastmsg = substr($srvmsg, 0, 3);
		   if($lastmsg <> "250") $error = 1; else $error = '';
		  } else $error = 1;
		 } else {
		  $error = '';
		  if(strpos($srvmsg,'PIPELINING')>0){ if($smtp_debug) logwrite($bugf,"LM: SMTP Pipelining Detected\r\n"); $pipeline = 1; } else $pipeline = '';

	//      $pipeline = ''; echo "PIPELINING OFF<br>";
		 }
		}

		// auth login
		if($mtauth && strpos($srvmsg,'LOGIN')>0){
		 if($smtp_debug) logwrite($bugf,"LM: AUTH LOGIN Detected\r\n");
		 // EHLO says auth is good
		 
		 //Start TLS/SSL here...
		 /*logwrite($bugf,"Start SSL\r\n");
		 fputs($ssock, "STARTTLS\r\n");
		 $srvmsg = getsmtpmsg($ssock);
		 logwrite($bugf,$srvmsg."");
		 
		 stream_socket_enable_crypto($ssock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
		 fputs($ssock, "Hello\r\n");
		 $srvmsg = getsmtpmsg($ssock);
		 logwrite($bugf,$srvmsg."");
		 
		 $cnf = getcwd()."/certs/openssl.cnf";
		 $configArgs = array(
			'config' =>$cnf
		 );
		 
		 $res = openssl_pkey_new($configArgs); //Resource number
		 logwrite($bugf,$res."\r\n");
		 openssl_pkey_export($res, $privatekey); //Private key
		 logwrite($bugf,$privatekey."\r\n");
		 $publickey=openssl_pkey_get_details($res); //Public key
		 $publickey=$publickey["key"];
		 logwrite($bugf,$publickey."\r\n");
		 
		 openssl_public_encrypt("ClientHello", $crypttext, $publickey); //encrypt any message
		 logwrite($bugf,openssl_error_string()."\r\n");
		 logwrite($bugf,$crypttext."\r\n");
		 fputs($ssock,$crypttext."\r\n");
		 $srvmsg = getsmtpmsg($ssock);
		 logwrite($bugf,$srvmsg."\r\n");
		 
		 openssl_private_decrypt($srvmsg, $decrypted, $privatekey); // decrypt what server returns
		 logwrite($bugf,$decrypted."\r\n");
		 */
		 //End TLS/SSL here...
		 
		 $smtpcmd = "AUTH LOGIN\r\n";
		 if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		 fputs($ssock, $smtpcmd);

		 $srvmsg = getsmtpmsg($ssock);
		 if($smtp_debug) logwrite($bugf,$srvmsg);
		 $lastmsg = substr($srvmsg, 0, 3);
		 if ($lastmsg <> "334") $error = 1; else $error = '';

		 if(!$error){
		  // send username
		  if($solo||$dayo||$welcnf) echo "Authenticating User/Pass... ";

		  $smtpcmd = base64_encode($mtuser)."\r\n";
		  if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		  fputs($ssock, $smtpcmd);

		  $srvmsg = getsmtpmsg($ssock);
		  if($smtp_debug) logwrite($bugf,$srvmsg);
		  $lastmsg = substr($srvmsg, 0, 3);
		  
		  if ($lastmsg <> "334") $error = 1; else $error = '';
		  if(!$error){
		   // send password

		   $smtpcmd = base64_encode($mtpass)."\r\n";
		   if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		   fputs($ssock, $smtpcmd);

		   $srvmsg = getsmtpmsg($ssock);
		   if($smtp_debug) logwrite($bugf,$srvmsg);
		   $lastmsg = substr($srvmsg, 0, 3);
		   if ($lastmsg <> "235") $error = 1; else $error = '';
		  }
		  if($error) { if($solo||$dayo||$welcnf) echo "Bad password<br>"; } else { if($solo||$dayo||$welcnf) echo "Authenticated!<br>"; }
		 } else {
		  if($solo||$dayo||$welcnf) echo "Username not accepted.<br>";
		 }
		 $noplain = 1;
		}

		// auth plain
		if($mtauth && strpos($srvmsg,'PLAIN')>0 && !$noplain){
		 // EHLO says auth is good
		 $smtpcmd = "AUTH PLAIN ".base64_encode("\0".$mtuser."\0".$mtpass)."\r\n";
		 if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
		 fputs($ssock, $smtpcmd);
		 $srvmsg = getsmtpmsg($ssock);
		 if($smtp_debug) logwrite($bugf,$srvmsg);
		 $lastmsg = substr($srvmsg, 0, 3);
		 if ($lastmsg <> "235") $error = 1; else $error = '';
		 if($error){  if($solo||$dayo||$welcnf) echo "User/Pass not accepted.<br>"; }
		}
	   } else {
		if($solo||$dayo||$welcnf) echo "EHLO failed! ($srvmsg)<br>";
	   }
	  } else {
	   if($solo||$dayo||$welcnf) echo "Server not found!<br>";
	  }
	  if(!$error){
	   // NOOP check server.
	   $smtpcmd = "NOOP\r\n";
	   logwrite($bugf,'> '.$smtpcmd);
	   if($smtp_debug) logwrite($bugf,'> '.$smtpcmd);
	   fputs($ssock, $smtpcmd);
	   $srvmsg = getsmtpmsg($ssock);
	   if($smtp_debug) logwrite($bugf,$srvmsg);
	   $lastmsg = substr($srvmsg, 0, 3);
	   if ($lastmsg <> "250") $error = 1; else $error = '';
	  }
	  if($error){
	   if($dayo || $solo || $welcnf) echo '<font color=red>Failed.</font><br><br>Check your SMTP settings!<br>';
	   // if a solo mailing, save msg to saved and delete from sent.
	   if($solo) {
		echo " Your message has been saved for later.</small><br>";
		$cmd = "select subject,content,htcontent,fattach from $ttable where id = '$xid'";
		$res = mysql_query($cmd) or die('admin-11-'.mysql_error());
		if($sqldebug) echo "CMD=<b>$cmd</b><br>";

		list($subj,$cont,$htcont,$fatt)=mysql_fetch_row($res);
		// slashes for sql command
		$subj = addslashes($subj);
		$cont = addslashes($cont);
		$htcont = addslashes($htcont);
		$subj = "SAV: " . $subj;
		$cmd = "insert into $stable values('','s','','$subj','$cont','$htcont','$fatt')";
		mysql_query($cmd) or die('admin-12-'.mysql_error());
		if($sqldebug) echo "CMD=<b>$cmd</b><br>";
		echo "<form name=back method=post action=./editconf.php>
		<input class=button type=submit name=bs value=\"To Config\"></form>";
		exit;
	   }

	   // removed - dailymail, switch to non-smtp if smtp fails..
	   if($daym) {
		// $smtpsend=0;
		exit;
	   }
	  }
	 }
 }
?>