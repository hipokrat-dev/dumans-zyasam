<?php require_once dirname(__DIR__).'/app/bootstrap.php'; $s=settings(); ?>
<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>Dumansız Yaşam — Özgürlüğün bir nefes uzağında</title><meta name="description" content="Dumansız bir hayata açılan kapı. Özgürlüğünü yeniden keşfet."><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/cinema.css"><script src="assets/app.js" defer></script></head>
<body class="cinema"><main class="screen" aria-label="Dumansız Yaşam">
<?php if($s['video']): ?><video class="film" id="heroVideo" autoplay muted loop playsinline preload="auto" aria-label="Dumansız yaşam açılış videosu"><source src="<?=e($s['video'])?>"></video><?php endif; ?>
<div class="vignette" aria-hidden="true"></div>
<header class="cinema-header"><a class="cinema-brand" href="/" aria-label="Dumansız Yaşam ana sayfa"><span aria-hidden="true">≈</span> dumansız<span class="light">yaşam</span></a><a class="discover" href="rehber.php">Özgürlüğünü keşfet <span aria-hidden="true">↗</span></a></header>
<div class="cinema-bottom"><p>HER NEFESTE, YENİDEN SEN.</p><div class="controls" aria-label="Medya kontrolleri">
<?php if($s['video']): ?><button id="videoToggle" type="button">Videoyu duraklat</button><?php endif; ?>
<?php if($s['audio']): ?><audio id="ambience" src="<?=e($s['audio'])?>" loop preload="auto"></audio><button id="audioToggle" type="button" aria-pressed="false">♫ Sesi aç</button><span id="audioStatus" role="status"></span><?php endif; ?>
</div></div></main></body></html>
