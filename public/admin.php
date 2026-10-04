<?php
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/content.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off', 'samesite'=>'Strict']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$message=''; $ready=false;
try { $pdo=db(); $ready=$pdo && !empty($config['admin_password_hash']); if($ready) $pdo->query('SELECT 1 FROM login_attempts LIMIT 1'); }
catch(Throwable $err){ $ready=false; }
if(!empty($_SESSION['admin']) && (($_SESSION['last_seen']??0)<time()-1800 || !hash_equals(hash('sha256',(string)($config['admin_password_hash']??'')),(string)($_SESSION['auth_version']??'')))) unset($_SESSION['admin']);
if(!empty($_SESSION['admin'])) $_SESSION['last_seen']=time();
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(!hash_equals($_SESSION['csrf'], (string)($_POST['csrf']??''))) { http_response_code(403); exit('Oturum doğrulanamadı. Sayfayı yenileyin.'); }
 if(!$ready){http_response_code(503);$message='Önce veritabanı ve yönetici yapılandırması tamamlanmalı.';}
 else try {
  $action=$_POST['action']??'';
  if($action==='login') {
   // Attempts belong to this password version; a verified reset clears the old lock.
   $key=hash('sha256',($_SERVER['REMOTE_ADDR']??'unknown').'|'.hash('sha256',$config['admin_password_hash']));
   $pdo->prepare('INSERT IGNORE INTO login_attempts (key_hash) VALUES (?)')->execute([$key]);
   $pdo->beginTransaction();
   $q=$pdo->prepare('SELECT attempts, locked_until FROM login_attempts WHERE key_hash=? FOR UPDATE');$q->execute([$key]);$attempt=$q->fetch(PDO::FETCH_ASSOC);
   if((int)$attempt['locked_until']>time()){$pdo->commit();$minutes=(int)ceil(((int)$attempt['locked_until']-time())/60);$message='Çok fazla deneme. '.$minutes.' dakika sonra tekrar deneyin.';http_response_code(429);}
   elseif(password_verify((string)($_POST['password']??''),$config['admin_password_hash'])) {
    $pdo->prepare('DELETE FROM login_attempts WHERE key_hash=?')->execute([$key]);$pdo->commit();
    session_regenerate_id(true);$_SESSION['admin']=true;$_SESSION['auth_version']=hash('sha256',$config['admin_password_hash']);$_SESSION['last_seen']=time();$_SESSION['csrf']=bin2hex(random_bytes(32));header('Location: admin.php');exit;
   } else {
    $count=(int)$attempt['locked_until']>0 ? 1 : (int)$attempt['attempts']+1;
    $pdo->prepare('UPDATE login_attempts SET attempts=?, locked_until=? WHERE key_hash=?')->execute([$count,$count>=5?time()+900:0,$key]);$pdo->commit();$message='Şifre doğru değil.';
   }
  } elseif(!empty($_SESSION['admin'])) {
   if($action==='logout'){$_SESSION=[];session_destroy();header('Location: admin.php');exit;}
   $updates=[];$moved=[];
   try {
    if($action==='save') {
     foreach(['headline'=>120,'intro'=>500] as $field=>$limit){$v=trim((string)($_POST[$field]??''));if($v===''||mb_strlen($v)>$limit)throw new RuntimeException('Metin boş olamaz veya izin verilen uzunluğu aşamaz.');$updates[$field]=$v;}
     foreach(['audio','video'] as $kind){$uploaded=receive_media($kind,$kind,$moved);if($uploaded!==null)$updates[$kind]=$uploaded;}
     $pdo->beginTransaction();
    } elseif(in_array($action,['save_item','delete_item'],true)) {
     $pdo->beginTransaction();
     $pdo->prepare('INSERT IGNORE INTO settings (name,value) VALUES (?,?)')->execute(['content_items',json_encode(default_content(),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
     $q=$pdo->prepare('SELECT value FROM settings WHERE name=? FOR UPDATE');$q->execute(['content_items']);
     $items=validate_content(json_decode($q->fetchColumn(),true,32,JSON_THROW_ON_ERROR));
     $id=(string)($_POST['item_id']??'');$index=null;foreach($items as $i=>$item)if($item['id']===$id)$index=$i;
     if($id!==''&&$index===null)throw new RuntimeException('Başlık bulunamadı. Sayfayı yenileyin.');
     if($action==='delete_item'){
      if($index===null)throw new RuntimeException('Silinecek başlığı seçin.');
      array_splice($items,$index,1);
     }else{
      $item=$index===null?['id'=>bin2hex(random_bytes(8)),'audio'=>'','video'=>'']:$items[$index];
      $item['category']=(string)($_POST['category']??'');$item['title']=trim((string)($_POST['title']??''));$item['text']=trim((string)($_POST['text']??''));
      foreach(['audio','video'] as $kind){if(isset($_POST['remove_'.$kind]))$item[$kind]='';$uploaded=receive_media('item_'.$kind,$kind,$moved);if($uploaded!==null)$item[$kind]=$uploaded;}
      if($index===null)$items[]=$item;else $items[$index]=$item;
     }
     $updates['content_items']=json_encode(validate_content($items),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    } else throw new RuntimeException('Geçersiz işlem.');
    $q=$pdo->prepare('INSERT INTO settings (name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');foreach($updates as $k=>$v)$q->execute([$k,$v]);$pdo->commit();
    $_SESSION['notice']='Değişiklikler kaydedildi ve siteye yansıdı.';
    $destination=$action==='save'?'home':(string)($_POST['category']??'tetikleyiciler');
    $itemQuery=$action==='save_item'?'&item='.rawurlencode($item['id']):'';
    header('Location: admin.php?tab='.rawurlencode($destination).$itemQuery);exit;
   }catch(Throwable $err){if($pdo->inTransaction())$pdo->rollBack();foreach($moved as $path)if(is_file(__DIR__.'/'.$path))unlink(__DIR__.'/'.$path);throw $err;}
  } else {http_response_code(403);$message='Önce giriş yapın.';}
 } catch(Throwable $err){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$message=$err instanceof RuntimeException && !($err instanceof PDOException)?$err->getMessage():'İşlem tamamlanamadı. Veritabanı yapılandırmasını kontrol edin.';}
}
$s=settings();$categories=content_categories();$items=content_items();
if($message===''&&isset($_SESSION['notice'])){$message=$_SESSION['notice'];unset($_SESSION['notice']);}
$tab=is_string($_GET['tab']??null)?$_GET['tab']:'home';if($tab!=='home'&&!isset($categories[$tab]))$tab='home';
$requestedItem=is_string($_GET['item']??null)?$_GET['item']:'';
$editing=null;foreach($items as $candidate)if($candidate['category']===$tab&&($requestedItem===$candidate['id']||($requestedItem===''&&$editing===null)))$editing=$candidate;
if($requestedItem==='new')$editing=null;
$csrf='<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';
?>
<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>İçerik stüdyosu — Dumansız Yaşam</title><meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="assets/admin.css?v=2"><script src="assets/admin.js" defer></script></head><body>
<header class="admin-header"><a class="brand" href="/">≈ dumansız<span>yaşam</span></a><a href="/" target="_blank" rel="noopener">Siteyi görüntüle ↗</a></header>
<?php if(!$ready||empty($_SESSION['admin'])): ?><main class="login-card"><p class="eyebrow">İÇERİK STÜDYOSU</p><h1>Yeniden hoş geldin.</h1><?php if($message): ?><p class="notice" role="status"><?=e($message)?></p><?php endif; ?><?php if(!$ready): ?><p>Önce veritabanı ve yönetici yapılandırmasını tamamlayın.</p><?php else: ?><form method="post"><?=$csrf?><input type="hidden" name="action" value="login"><label>Yönetici şifresi<input type="password" name="password" required autocomplete="current-password"></label><button class="primary">Giriş yap ↗</button></form><p class="hint"><a href="sifre-yenile.php">Şifremi unuttum ↗</a></p><?php endif; ?></main>
<?php else: ?>
<div class="admin-shell"><aside class="admin-sidebar"><p class="eyebrow">İÇERİK STÜDYOSU</p><h1>Kontrol sende.</h1><nav aria-label="Düzenlenecek bölüm"><a href="admin.php?tab=home" <?=$tab==='home'?'aria-current="page"':''?>><span>01</span>Ana sayfa</a><?php $n=2;foreach($categories as $key=>$label): ?><a href="admin.php?tab=<?=e($key)?>" <?=$tab===$key?'aria-current="page"':''?>><span>0<?=$n++?></span><?=e($label)?></a><?php endforeach; ?></nav><div class="sidebar-note"><span>Medyanı yükle.<br>Hikâyeni şekillendir.</span><p>Başlık ve dosyalar kaydedildiğinde site güncellenir.</p></div><form method="post"><?=$csrf?><input type="hidden" name="action" value="logout"><button class="logout">Güvenli çıkış ↗</button></form></aside>
<main class="editor"><div class="editor-heading"><div><p class="eyebrow">DÜZENLE & YAYINLA</p><h2><?=$tab==='home'?'Ana sayfa medyası':e($categories[$tab])?></h2></div><a href="<?=$tab==='home'?'/':'rehber.php'?>" target="_blank" rel="noopener">Önizle ↗</a></div><?php if($message): ?><p class="notice" role="status"><?=e($message)?></p><?php endif; ?>
<?php if($tab==='home'): ?>
<form method="post" enctype="multipart/form-data" class="editor-form"><?=$csrf?><input type="hidden" name="action" value="save"><div class="field-row"><label>Ana başlık<input name="headline" maxlength="120" required value="<?=e($s['headline'])?>"></label><label>Giriş metni<textarea name="intro" maxlength="500" required rows="3"><?=e($s['intro'])?></textarea></label></div><p class="hint">Bu metinler açılış videosunun erişilebilir açıklamasında kullanılır.</p><div class="upload-grid"><div class="upload-box"><label>Açılış videosu<small>MP4 / WebM · En fazla 50 MB</small><input type="file" name="video" accept="video/mp4,video/webm"></label><?php if($s['video']): ?><video controls preload="metadata" src="<?=e($s['video'])?>"></video><?php endif; ?></div><div class="upload-box"><label>Arka plan sesi<small>MP3 / OGG / WAV · En fazla 50 MB</small><input type="file" name="audio" accept="audio/mpeg,audio/ogg,audio/wav,audio/x-wav"></label><?php if($s['audio']): ?><audio controls preload="metadata" src="<?=e($s['audio'])?>"></audio><?php endif; ?><p class="hint">Ses 2,5 saniye sonra başlamayı dener. Tarayıcı engellerse ziyaretçi ses simgesine dokunur.</p></div></div><div class="save-bar"><span>Yeni dosya seçmezsen mevcut medya korunur.</span><button class="primary">Kaydet ve yayınla ↗</button></div></form>
<?php else: ?>
<form method="get" class="item-picker"><input type="hidden" name="tab" value="<?=e($tab)?>"><label for="edit-item">Düzenlenecek başlık</label><select id="edit-item" name="item"><?php foreach($items as $entry):if($entry['category']!==$tab)continue; ?><option value="<?=e($entry['id'])?>" <?=$editing&&$editing['id']===$entry['id']?'selected':''?>><?=e($entry['title'])?></option><?php endforeach; ?><option value="new" <?=$editing===null?'selected':''?>>＋ Yeni başlık ekle</option></select><button class="secondary">Aç</button></form>
<form method="post" enctype="multipart/form-data" class="editor-form"><?=$csrf?><input type="hidden" name="action" value="save_item"><input type="hidden" name="item_id" value="<?=e($editing['id']??'')?>"><input type="hidden" name="category" value="<?=e($tab)?>"><label>Başlık<input name="title" maxlength="70" required value="<?=e($editing['title']??'')?>" placeholder="Dinleyicinin göreceği başlık"></label><label>Kısa metin <small>İsteğe bağlı · En fazla 220 karakter</small><textarea name="text" maxlength="220" rows="3" placeholder="Kısa, sakin ve tek bir düşünce…"><?=e($editing['text']??'')?></textarea></label><div class="upload-grid"><?php foreach(['audio'=>'Başlığın altındaki ses','video'=>'Başlığa ait video'] as $kind=>$label): ?><div class="upload-box"><label><?=e($label)?><small><?=$kind==='audio'?'MP3 / OGG / WAV':'MP4 / WebM'?> · En fazla 50 MB</small><input type="file" name="item_<?=$kind?>" accept="<?=$kind==='audio'?'audio/mpeg,audio/ogg,audio/wav,audio/x-wav':'video/mp4,video/webm'?>"></label><?php if(!empty($editing[$kind])): ?><?php if($kind==='audio'): ?><audio controls preload="metadata" src="<?=e($editing[$kind])?>"></audio><?php else: ?><video controls preload="metadata" src="<?=e($editing[$kind])?>"></video><?php endif; ?><label class="checkbox"><input type="checkbox" name="remove_<?=$kind?>" value="1">Bu medyayı başlıktan kaldır</label><?php else: ?><p class="hint">Henüz dosya eklenmedi.</p><?php endif; ?></div><?php endforeach; ?></div><div class="save-bar"><span>Dosyalar yalnızca bu başlığa bağlanır.</span><button class="primary">Kaydet ve yayınla ↗</button></div></form>
<?php if($editing): ?><details class="remove-item"><summary>Başlığı kaldır</summary><p>Bu başlık ve medya bağlantıları ziyaretçilere gösterilmez. Yüklenen dosyalar silinmez.</p><form method="post"><?=$csrf?><input type="hidden" name="action" value="delete_item"><input type="hidden" name="item_id" value="<?=e($editing['id'])?>"><input type="hidden" name="category" value="<?=e($tab)?>"><button class="danger">Bu başlığı kaldır</button></form></details><?php endif; ?>
<?php endif; ?></main></div><?php endif; ?></body></html>
