<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/recovery.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']==='off') {
    http_response_code(403); exit('Şifre yenileme yalnızca HTTPS üzerinden kullanılabilir.');
}
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['secure'=>true,'httponly'=>true,'samesite'=>'Strict']);
session_start();
$_SESSION['recovery_csrf'] ??= bin2hex(random_bytes(32));
$error=''; $success=false;
$ready=is_file($configFile) && !empty($config['admin_password_hash']);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!is_string($_POST['csrf']??null) || !hash_equals($_SESSION['recovery_csrf'],$_POST['csrf'])) {
        http_response_code(403); exit('Oturum doğrulanamadı. Sayfayı yenileyin.');
    }
    // One server-wide limit prevents attempts from bypassing the limit with new IPs or cookies.
    $lock=fopen(sys_get_temp_dir().'/dumansiz-recovery-'.hash('sha256',__DIR__).'.lock','c+');
    if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) {
        if (is_resource($lock)) fclose($lock);
        http_response_code(429); $error='Başka bir işlem sürüyor. Biraz sonra tekrar deneyin.';
    } else {
        try {
            $raw=stream_get_contents($lock);
            $state=json_decode($raw,true) ?: ['count'=>0,'until'=>0];
            if (($state['until']??0)>time()) {http_response_code(429);throw new RuntimeException('Çok fazla deneme. 15 dakika sonra tekrar deneyin.');}
            if (($state['until']??0)>0) $state=['count'=>0,'until'=>0];
            if (!$ready) throw new RuntimeException('Önce site kurulumu tamamlanmalı.');
            foreach (['db_password','new_password','confirmation'] as $field) {
                if (!is_string($_POST[$field]??null)) throw new RuntimeException('Tüm alanları doldurun.');
            }
            // Count before verification, including requests that fail later.
            $state['count']++;
            if ($state['count']>=5) $state['until']=time()+900;
            rewind($lock);ftruncate($lock,0);fwrite($lock,json_encode($state));fflush($lock);
            replace_admin_password($configFile,$_POST['db_password'],$_POST['new_password'],$_POST['confirmation']);
            rewind($lock);ftruncate($lock,0);fwrite($lock,json_encode(['count'=>0,'until'=>0]));fflush($lock);
            $_SESSION=[];session_regenerate_id(true);$_SESSION['recovery_success']=true;
            header('Location: sifre-yenile.php');exit;
        } catch (Throwable $err) {
            $error=$err instanceof RuntimeException ? $err->getMessage() : 'İşlem tamamlanamadı. Tekrar deneyin.';
        } finally {
            flock($lock,LOCK_UN);fclose($lock);
        }
    }
}
if (isset($_SESSION['recovery_success'])) {$success=true;unset($_SESSION['recovery_success']);}
?>
<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Şifreni yenile — Dumansız Yaşam</title><link rel="stylesheet" href="assets/admin.css?v=2"></head><body><header class="admin-header"><a class="brand" href="/">≈ dumansız<span>yaşam</span></a><a href="admin.php">Yönetici girişi ↗</a></header><main class="login-card"><p class="eyebrow">İÇERİK STÜDYOSU</p><h1>Şifreni yenile.</h1>
<?php if($success): ?><p class="notice" role="status">Yönetici şifren yenilendi. Yeni şifrenle giriş yapabilirsin.</p><a class="primary" href="admin.php">Giriş ekranına dön ↗</a>
<?php elseif(!$ready): ?><p>Önce site kurulumu tamamlanmalı.</p>
<?php else: ?><p class="hint">Sahipliğini doğrulamak için Hostinger’da bu siteye ait mevcut veritabanı şifreni gir. Veritabanı şifresi değişmez; yalnızca yönetici şifren yenilenir.</p><?php if($error): ?><p class="notice" role="alert"><?=e($error)?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['recovery_csrf'])?>"><label>Mevcut Hostinger veritabanı şifresi<input type="password" name="db_password" required autocomplete="off"></label><label>Yeni yönetici şifresi<input type="password" name="new_password" required maxlength="72" autocomplete="new-password"></label><label>Yeni şifreyi tekrar yaz<input type="password" name="confirmation" required maxlength="72" autocomplete="new-password"></label><p class="hint">Yeni şifre yalnızca bu sitenin sunucusuna gönderilir ve özeti saklanır.</p><button class="primary">Yönetici şifresini yenile ↗</button></form><?php endif; ?></main></body></html>
