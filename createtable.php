<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once './airnav_migration.php';

$utable = 'newsletter_subscribers';

// test connection
$res = mysql_connect($sqlhost,$sqluser,$sqlpass);
if(!$res){
    echo "Connection fail!";
    exit;
}
echo "Connected!";
mysql_select_db($sqldb,$res);

$sqlcmd = "CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(7) NOT NULL,
  `list` smallint(5) unsigned NOT NULL,
  `fname` tinytext NOT NULL,
  `lname` tinytext NOT NULL,
  `email` varchar(255) NOT NULL,
  `user1` text NOT NULL,
  `user2` text NOT NULL,
  `user3` text NOT NULL,
  `user4` text NOT NULL,
  `user5` text NOT NULL,
  `user6` text NOT NULL,
  `user7` text NOT NULL,
  `user8` text NOT NULL,
  `user9` text NOT NULL,
  `user10` text NOT NULL,
  `cseq` smallint(6) NOT NULL,
  `cdel` smallint(6) NOT NULL,
  `cnf` char(1) NOT NULL,
  `dateadd` datetime NOT NULL,
  `ipaddr` varchar(15) NOT NULL,
  `refurl` varchar(75) NOT NULL,
  `htmail` char(1) NOT NULL,
  `bounces` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `list` (`list`),
  KEY `uid` (`uid`),
  KEY `cnf` (`cnf`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=883 ;";
mysql_query($sqlcmd) or die('admin-12-' . mysql_error());
echo 'Table '.$utable.' deleted';

?>
