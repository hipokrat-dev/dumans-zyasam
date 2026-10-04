<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
header('Cache-Control: no-store');header('X-Robots-Tag: noindex, nofollow');
$target=dirname(__DIR__,2).'/dumansiz-config.php';
if(is_file($target)||is_file(dirname(__DIR__).'/config.local.php')){http_response_code(410);exit('Kurulum tamamlanmış. Yönetim için /admin.php adresini kullanın.');}
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off'){http_response_code(403);exit('Kurulum yalnızca HTTPS üzerinden kullanılabilir.');}
ini_set('session.use_strict_mode','1');session_set_cookie_params(['secure'=>true,'httponly'=>true,'samesite'=>'Strict']);session_start();
$_SESSION['setup_csrf']??=bin2hex(random_bytes(32));$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['setup_csrf'],(string)($_POST['csrf']??''))){http_response_code(403);exit('Sayfayı yenileyin.');}
 $lockFile=sys_get_temp_dir().'/dumansiz-setup-'.hash('sha256',__DIR__).'.lock';
 $lock=fopen($lockFile,'c+');
 if(!$lock||!flock($lock,LOCK_EX|LOCK_NB)){http_response_code(429);$error='Başka bir kurulum işlemi sürüyor. Tekrar deneyin.';}
 else try {
  $state=json_decode(stream_get_contents($lock),true)??['count'=>0,'until'=>0];
  if(($state['until']??0)>time())throw new RuntimeException('Çok fazla deneme. 15 dakika sonra tekrar deneyin.');
  if(($state['until']??0)>0)$state=['count'=>0,'until'=>0];
  $admin=(string)($_POST['admin_password']??'');
  if($admin===''||strlen($admin)>72)throw new RuntimeException('Yönetici şifresi boş olamaz ve en fazla 72 bayt olabilir.');
  if(!hash_equals($admin,(string)($_POST['admin_confirm']??'')))throw new RuntimeException('Yönetici şifreleri eşleşmiyor.');
  $pass=(string)($_POST['db_password']??'');
  // The target identity is fixed: an attacker cannot substitute their own database.
  try{$connection=new PDO('mysql:host=localhost;dbname=u149068033_dumansiz;charset=utf8mb4','u149068033_dumansiz',$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);$connection->query('SELECT name FROM settings LIMIT 1');$connection->query('SELECT key_hash FROM login_attempts LIMIT 1');}
  catch(Throwable $err){$state['count']++;if($state['count']>=5)$state['until']=time()+900;rewind($lock);ftruncate($lock,0);fwrite($lock,json_encode($state));throw new RuntimeException('Veritabanı bağlantısı doğrulanamadı. Şifreyi kontrol edin.');}
  $data=['database'=>['host'=>'localhost','name'=>'u149068033_dumansiz','user'=>'u149068033_dumansiz','password'=>$pass],'admin_password_hash'=>password_hash($admin,PASSWORD_DEFAULT)];
  $oldMask=umask(0077);$file=fopen($target,'x');umask($oldMask);
  if(!$file)throw new RuntimeException('Yapılandırma kaydedilemedi veya kurulum zaten tamamlandı.');
  $contents="<?php\nreturn ".var_export($data,true).";\n";
  if(fwrite($file,$contents)!==strlen($contents)){fclose($file);unlink($target);throw new RuntimeException('Yapılandırma yazılamadı.');}
  fflush($file);fclose($file);flock($lock,LOCK_UN);fclose($lock);
  session_regenerate_id(true);$_SESSION['admin']=true;$_SESSION['auth_version']=hash('sha256',$data['admin_password_hash']);$_SESSION['last_seen']=time();$_SESSION['csrf']=bin2hex(random_bytes(32));unset($_SESSION['setup_csrf']);
  header('Location: admin.php');exit;
 } catch(Throwable $err){$error=$err instanceof RuntimeException&&!($err instanceof PDOException)?$err->getMessage():'Kurulum tamamlanamadı.';flock($lock,LOCK_UN);fclose($lock);}
}
?><!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>İlk kurulum — Dumansız Yaşam</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/admin.css"></head><body><main class="admin"><a class="brand" href="/">≈ dumansız<span>yaşam</span></a><h1>Son bir adım.</h1><p>Hostinger veritabanını bağla, içerik yönetimini aç. Şifreler bu sitenin sunucusuna HTTPS ile iletilir; GitHub’a gönderilmez. Kurulum başarılı olduğunda bu ekran kapanır.</p><p>Veritabanı ve kullanıcı: <strong>u149068033_dumansiz</strong></p><?php if($error): ?><p class="notice" role="alert"><?=e($error)?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['setup_csrf'])?>"><label>Hostinger’da belirlediğin veritabanı şifresi<input type="password" name="db_password" required autocomplete="off"></label><label>Yeni site yönetici şifren<input type="password" name="admin_password" maxlength="72" required autocomplete="new-password"></label><label>Yönetici şifreni tekrar yaz<input type="password" name="admin_confirm" maxlength="72" required autocomplete="new-password"></label><button class="button lime">Bağlantıyı doğrula ve kurulumu bitir ↗</button></form><p>Yönetici şifren özetlenerek saklanır. Veritabanı bağlantı bilgileri, dışarıdan erişimi kapalı yapılandırma dosyasında tutulur.</p></main></body></html>
