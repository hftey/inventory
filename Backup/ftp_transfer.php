<?php

$dbFileName = 'inventory'.date('Ymd').'.sql.gz';

$dbFile = '/var/www/html/raymond/venzon/demo/inventory/Backup/'.$dbFileName;


$dbHost = 'localhost'; // Database Host
$dbUser = 'root'; // Database Username
$dbPass = 'tes@7.30'; // Database Password

$command = 'ls -al';
//$ftp_server = "23.229.149.40";
$ftp_server = "110.4.45.97";
//$ftp_user_name = "inventory2@raymondtey-goddy.com";
$ftp_user_name = "ftp_backup@exactanalytical.com.my";
$ftp_user_pass = "QWE0609poi!";
$conn_id = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

$upload = ftp_put($conn_id, "inventory/".$dbFileName, $dbFile, FTP_BINARY);

// close the connection
ftp_close($conn_id);



?>