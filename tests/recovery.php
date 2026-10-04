<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/recovery.php';
function verify(bool $ok, string $message):void {if(!$ok)throw new RuntimeException($message);}
$directory=sys_get_temp_dir().'/dumansiz-recovery-test-'.bin2hex(random_bytes(8));mkdir($directory,0700);
$target=$directory.'/config.php';
$fixture=['database'=>['host'=>'localhost','name'=>'test','user'=>'test','password'=>'fixture-database-secret'],'admin_password_hash'=>password_hash('fixture-old-password',PASSWORD_DEFAULT),'extra'=>'preserved'];
file_put_contents($target,"<?php return ".var_export($fixture,true).";");
$original=file_get_contents($target);
try {
    foreach ([['wrong','fixture-new-password','fixture-new-password'],['fixture-database-secret','short','short'],['fixture-database-secret','fixture-new-password','does-not-match']] as $args) {
        $failed=false;try{replace_admin_password($target,...$args);}catch(RuntimeException $err){$failed=true;}
        verify($failed,'Invalid recovery rejected');verify(file_get_contents($target)===$original,'Invalid recovery preserves config');
    }
    replace_admin_password($target,'fixture-database-secret','fixture-new-password','fixture-new-password');
    $updated=require $target;
    verify($updated['database']===$fixture['database']&&$updated['extra']==='preserved','Database configuration preserved');
    verify(password_verify('fixture-new-password',$updated['admin_password_hash']),'New password verifies');
    verify(!password_verify('fixture-old-password',$updated['admin_password_hash']),'Old password rejected');
    verify(!str_contains(file_get_contents($target),'fixture-new-password'),'Password not stored in plaintext');
    verify((fileperms($target)&0777)===0600,'Private file permissions');
    verify(hash('sha256',$updated['admin_password_hash'])!==hash('sha256',$fixture['admin_password_hash']),'Existing session version invalidated');
    echo "Recovery checks passed\n";
} finally {foreach(glob($directory.'/*') as $file)unlink($file);rmdir($directory);}
