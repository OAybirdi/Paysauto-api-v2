<?php

date_default_timezone_set('Europe/Istanbul');
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");

/********** DB Connect ************/

 $host              = "localhost";
 $dbname            = "db";
 $dbusername        = "root";
 $dbpassword        = "";

/********** DB Connect ************/

try {
$db = new PDO("mysql:host=".$host.";dbname=".$dbname.";charset=utf8", $dbusername, $dbpassword);
} catch ( PDOException $e ){
     print $e->getMessage();
}

?> 



