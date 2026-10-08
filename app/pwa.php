<?php
function pwa_head(): void { ?>
<link rel="icon" type="image/png" sizes="192x192" href="/assets/icons/app-192.png"><link rel="manifest" href="/manifest.webmanifest"><meta name="theme-color" content="#17120e"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="Dumansız Hayat"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><link rel="apple-touch-icon" href="/assets/icons/app-180.png"><link rel="stylesheet" href="/assets/pwa.css?v=1"><script src="/assets/pwa.js?v=1" defer></script>
<?php }
function pwa_controls(string $active): void {
$links = [
 'home'=>['/','Ana sayfa','M3 11 12 3l9 8M5 10v11h5v-7h4v7h5V10'],
 'listen'=>['/rehber.php','Dinle','M4 14v-3a8 8 0 0 1 16 0v3M4 13H2v7h5v-7ZM20 13h2v7h-5v-7Z'],
 'cost'=>['/etkiler.php','Bedel','M4 20V10h4v10M10 20V4h4v16M16 20v-7h4v7'],
 'clinics'=>['/poliklinikler.php','Poliklinikler','M4 21V5h16v16M9 21v-5h6v5M12 7v6M9 10h6'],
]; ?>
<nav class="pwa-bottom-nav" aria-label="Uygulama menüsü"><?php foreach($links as $key=>[$url,$label,$path]): ?><a href="<?=e($url)?>" <?=$key===$active?'aria-current="page"':''?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?=e($path)?>"/></svg><span><?=e($label)?></span></a><?php endforeach; ?></nav>
<?php if(in_array($active,['home','listen'],true)): ?><button type="button" id="pwa-install" class="pwa-install" hidden>Ana ekrana ekle ↗</button><?php endif; ?>
<dialog id="pwa-install-dialog" aria-labelledby="pwa-install-title"><img class="pwa-app-icon" src="/assets/icons/app-192.png" width="80" height="80" alt="Dumansız Hayat: duman halkasını açan altın anahtar"><h2 id="pwa-install-title">Dumansız Hayat, telefonunda.</h2><p id="pwa-install-help">Tarayıcının menüsünden “Uygulamayı yükle” veya “Ana ekrana ekle” seçeneğini kullanabilirsin.</p><p>Ses ve videoları dinlemek ve izlemek için internet bağlantısı gerekir.</p><div class="pwa-dialog-actions"><button id="pwa-install-confirm" type="button" hidden>Uygulamayı ekle</button><button id="pwa-install-close" type="button">Kapat</button></div></dialog>
<?php }
