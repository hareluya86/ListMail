<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include("./airnav_migration.php");


// test connection


$csv = fopen('./list/special characters.csv','r');
$file_index = 1;
$record_count = 0;
$batch_size = 1000;
$sqlcmd = '';
while(list($id,$uid,$list,$fname,$lname,$email,$user1,$user2,
        $user3,$user4,$user5,$user6,$user7,$user8,$user9,$user10,
        $cseq,$cdel,$cnf,$dateadd,$ipaddr,$refurl,$htmail,$bounces) = fgetcsv($csv)){
    
    if($file_index++==1) continue;
    
    if($record_count%$batch_size == 0){
        if($sqlcmd){
            echo $sqlcmd.'<br>';
            $res = mysql_connect($sqlhost,$sqluser,$sqlpass);
            if(!$res){
                echo "Connection fail!";
                exit;
            }
            echo "Connected!";
            mysql_select_db($sqldb,$res);
            //mysql_query($sqlcmd) or die('admin-12-' . mysql_error());
            
        }
        $sqlcmd = "INSERT INTO ".$utable." VALUES ($id,'$uid',$list,'$fname','$lname','$email','$user1','$user2',
        '$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10',$cseq,$cdel,'$cnf','$dateadd',
        '$ipaddr','$refurl','$htmail','$bounces')";
        //echo 'cmd='.$sqlcmd.'<br>';
        //echo 'mod='.$record_count%$batch_size;
        //echo 'write mode';
    }
    else{
        $sqlcmd = $sqlcmd.",
            ($id,'$uid',$list,'$fname','$lname','$email','$user1','$user2',
        '$user3','$user4','$user5','$user6','$user7','$user8','$user9','$user10',$cseq,$cdel,'$cnf','$dateadd',
        '$ipaddr','$refurl','$htmail','$bounces')";
        //echo 'cmd='.$sqlcmd.'<br>';
        //echo 'mod='.$record_count%$batch_size;
        //echo 'wait mode';
    }
    //echo 'count='.$record_count++.'<br>';
    $record_count++;
}
// purge the remaining records 
if($sqlcmd){
    echo $sqlcmd.'<br>';
    $res = mysql_connect($sqlhost,$sqluser,$sqlpass);
    if(!$res){
        echo "Connection fail!";
        exit;
    }
    echo "Connected!";
    mysql_select_db($sqldb,$res);
    echo $sqlcmd.'<br>';
    //mysql_query($sqlcmd) or die('admin-12-' . mysql_error());
    
}
?>
