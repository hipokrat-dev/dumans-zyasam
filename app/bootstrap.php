<?php
declare(strict_types=1);
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; media-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
$privateConfig = dirname(__DIR__, 2) . '/dumansiz-config.php';
$configFile = is_file($privateConfig) ? $privateConfig : dirname(__DIR__) . '/config.local.php';
$config = is_file($configFile) ? require $configFile : [];
function db(): ?PDO {
    global $config;
    static $db = null;
    if ($db instanceof PDO) return $db;
    if (empty($config['database'])) return null;
    $c = $config['database'];
    $db = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
    return $db;
}
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function settings(): array {
    $defaults = ['hero_title'=>'Özgürlüğünün anahtarı','hero_line'=>'senin elinde.','hero_image'=>'assets/media/freedom-key.png','headline'=>'Bir sigaradan çok daha özgürsün.', 'intro'=>'Bir şeyden vazgeçmiyorsun. Nefesini, zamanını ve seçimlerini geri alıyorsun. Zihnindeki kancaları birlikte fark edelim.', 'video'=>'', 'audio'=>''];
    try { if ($pdo = db()) foreach ($pdo->query('SELECT name, value FROM settings') as $row) if (array_key_exists($row['name'], $defaults)) $defaults[$row['name']] = $row['value']; }
    catch (Throwable $err) { error_log('Dumansiz: database settings unavailable'); }
    foreach (['video','audio'] as $key) if (!preg_match('~^uploads/[a-f0-9]{32}\.(mp4|webm|mp3|ogg|wav)$~', $defaults[$key])) $defaults[$key] = '';
    if ($defaults['audio'] === '' && is_file(dirname(__DIR__).'/public/assets/media/ambience.mp3')) $defaults['audio'] = 'assets/media/ambience.mp3';
    if (!preg_match('~^uploads/[a-f0-9]{32}\.(png|jpg|webp)$~', $defaults['hero_image'])) $defaults['hero_image']='assets/media/freedom-key.png';
    return $defaults;
}
