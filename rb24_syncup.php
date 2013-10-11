<?php


$csvfile = './list/Subscribers_special.csv';
$list = 19;

$csv = fopen('./list/Subscribers_special.csv','r');
$file_index = 1;
$record_count = 0;
$batch_size = 1000;
$sqlcmd = '';
/*while(list($id,$uid,$list,$fname,$lname,$email,$user1,$user2,
        $user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,
        $cseq,$cdel,$cnf,$dateadd,$ipaddr,$refurl,$htmail,$bounces) = fgetcsv($csv)){*/
while(list($rbmail)=fgetcsv($csv)){
    
}

?>
