<?php

$dbFile = '/var/www/html/raymond/venzon/demo/inventory/Backup/inventory'.date('Ymd').'.sql.gz';
$dbHost = 'localhost'; // Database Host
$dbUser = 'root'; // Database Username
$dbPass = 'tes@7.30'; // Database Password
exec( 'mysqldump --host="'.$dbHost.'" --user="'.$dbUser.'" --password="'.$dbPass.'" --add-drop-table "inventory" | gzip > "'.$dbFile.'"' );

?>