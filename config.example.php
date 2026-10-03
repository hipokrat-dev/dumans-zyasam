<?php
// Copy to config.local.php outside public_html; never commit the real file.
return [
 'database'=>['host'=>'localhost','name'=>'HOSTINGER_DB_NAME','user'=>'HOSTINGER_DB_USER','password'=>'HOSTINGER_DB_PASSWORD'],
 // Generate locally: php -r 'echo password_hash(readline("Admin password: "), PASSWORD_DEFAULT), PHP_EOL;'
 'admin_password_hash'=>'',
];
