<?php
require dirname(__DIR__).'/app/bootstrap.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off', 'samesite'=>'Strict']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$message=''; $ready=false;
try { $pdo=db(); $ready=$pdo && !empty($config['admin_password_hash']); if($ready) $pdo->query('SELECT 1 FROM login_attempts LIMIT 1'); }
catch(Throwable $err){ $ready=false; }
if(!empty($_SESSION['admin']) && ($_SESSION['last_seen']??0)<time()-1800) unset($_SESSION['admin']);
if(!empty($_SESSION['admin'])) $_SESSION['last_seen']=time();
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(!hash_equals($_SESSION['csrf'], (string)($_POST['csrf']??''))) { http_response_code(403); exit('Oturum doğrulanamadı. Sayfayı yenileyin.'); }
 if(!$ready){http_response_code(503);$message='Önce veritabanı ve yönetici yapılandırması tamamlanmalı.';}
 else try {
  $action=$_POST['action']??'';
  if($action==='login') {
   $key=hash('sha256',$_SERVER['REMOTE_ADDR']??'unknown');
   $pdo->prepare('INSERT IGNORE INTO login_attempts (key_hash) VALUES (?)')->execute([$key]);
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT attempts, locked_until FROM login_attempts WHERE key_hash=? FOR UPDATE');$q->execute([$key]);$attempt=$q->fetch(PDO::FETCH_ASSOC);
   if((int)$attempt['locked_until']>time()){$pdo->commit();$message='Çok fazla deneme. 15 dakika sonra tekrar deneyin.';http_response_code(429);}
   elseif(password_verify((string)($_POST['password']??''),$config['admin_password_hash'])) {
    $pdo->prepare('DELETE FROM login_attempts WHERE key_hash=?')->execute([$key]);$pdo->commit();
    session_regenerate_id(true);$_SESSION['admin']=true;$_SESSION['last_seen']=time();$_SESSION['csrf']=bin2hex(random_bytes(32));header('Location: admin.php');exit;
   } else {
    $count=(int)$attempt['locked_until']>0 ? 1 : (int)$attempt['attempts']+1;
    $pdo->prepare('UPDATE login_attempts SET attempts=?, locked_until=? WHERE key_hash=?')->execute([$count,$count>=5?time()+900:0,$key]);$pdo->commit();$message='Şifre doğru değil.';
   }
  } elseif(!empty($_SESSION['admin'])) {
   if($action==='logout'){$_SESSION=[];session_destroy();header('Location: admin.php');exit;}
   $updates=[];$moved=[];
   if($action==='save'){
    foreach(['headline'=>120,'intro'=>500] as $field=>$limit){$v=trim((string)($_POST[$field]??''));if($v===''||mb_strlen($v)>$limit)throw new RuntimeException('Metin boş olamaz veya izin verilen uzunluğu aşamaz.');$updates[$field]=$v;}
    foreach(['video'=>['video/mp4'=>'mp4','video/webm'=>'webm'],'audio'=>['audio/mpeg'=>'mp3','audio/ogg'=>'ogg','audio/wav'=>'wav','audio/x-wav'=>'wav']] as $field=>$types){
     if(!isset($_FILES[$field])||$_FILES[$field]['error']===UPLOAD_ERR_NO_FILE)continue;
     $f=$_FILES[$field];if($f['error']!==UPLOAD_ERR_OK||$f['size']>50*1024*1024)throw new RuntimeException('Dosya yüklenemedi. En fazla 50 MB dosya kullanın.');
     $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);if(!isset($types[$mime]))throw new RuntimeException('Desteklenmeyen medya biçimi.');
     $path='uploads/'.bin2hex(random_bytes(16)).'.'.$types[$mime];
     if(!move_uploaded_file($f['tmp_name'],__DIR__.'/'.$path))throw new RuntimeException('Dosya kaydedilemedi. uploads klasörünün yazma iznini kontrol edin.');
     $moved[]=$path;$updates[$field]=$path;
    }
    try{$pdo->beginTransaction();$q=$pdo->prepare('INSERT INTO settings (name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');foreach($updates as $k=>$v)$q->execute([$k,$v]);$pdo->commit();}
    catch(Throwable $err){if($pdo->inTransaction())$pdo->rollBack();foreach($moved as $p)unlink(__DIR__.'/'.$p);throw $err;}
    $message='Değişiklikler kaydedildi. Ana sayfa güncellendi.';
   }
  } else {http_response_code(403);$message='Önce giriş yapın.';}
 } catch(Throwable $err){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$message=$err instanceof RuntimeException && !($err instanceof PDOException)?$err->getMessage():'İşlem tamamlanamadı. Veritabanı yapılandırmasını kontrol edin.';}
}
$s=settings();
?><!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Yönetim — Dumansız Yaşam</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/admin.css"></head><body><main class="admin"><a class="brand" href="/">≈ dumansız<span>yaşam</span></a><h1>İçerik yönetimi</h1><?php if($message): ?><p class="notice" role="status"><?=e($message)?></p><?php endif; ?>
<?php if(!$ready): ?><p>Yönetim paneli henüz etkin değil. Hostinger MySQL tablolarını ve özel yapılandırma dosyasını kurulum rehberine göre hazırlayın.</p><?php elseif(empty($_SESSION['admin'])): ?><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="login"><label>Yönetici şifresi<input type="password" name="password" required autocomplete="current-password"></label><button class="button lime">Giriş yap</button></form><?php else: ?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="save"><label>Ana başlık<input name="headline" maxlength="120" required value="<?=e($s['headline'])?>"></label><label>Giriş metni<textarea name="intro" maxlength="500" required rows="4"><?=e($s['intro'])?></textarea></label><label>Açılış videosu · MP4 veya WebM<input type="file" name="video" accept="video/mp4,video/webm"></label><p>15 saniyelik video önerilir. Daha uzunsa ilk 15 saniyesi döngüye alınır. En fazla 50 MB; video sessiz oynar.</p><?php if($s['video']): ?><video controls preload="metadata" src="<?=e($s['video'])?>"></video><?php endif; ?><label>Arka plan sesi · MP3, OGG veya WAV<input type="file" name="audio" accept="audio/mpeg,audio/ogg,audio/wav,audio/x-wav"></label><p>Ses 2,5 saniye sonra başlamayı dener. Tarayıcı engellerse ziyaretçi Sesi aç düğmesini kullanır. En fazla 50 MB. Yeni dosya seçmezsen mevcut medya korunur.</p><?php if($s['audio']): ?><audio controls src="<?=e($s['audio'])?>"></audio><?php endif; ?><button class="button lime">Kaydet ve sitede göster ↗</button></form><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="logout"><button class="button">Güvenli çıkış</button></form><?php endif; ?></main></body></html>
