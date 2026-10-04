<?php require_once dirname(__DIR__).'/app/bootstrap.php'; $s=settings(); ?>
<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>Dumansız Yaşam — Özgürlüğün bir nefes uzağında</title><meta name="description" content="Dumansız bir hayata açılan kapı. Özgürlüğünü yeniden keşfet."><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/cinema.css?v=2"><link rel="stylesheet" href="assets/media.css?v=2"><script src="assets/app.js?v=2" defer></script></head>
<body class="cinema"><main class="screen" aria-label="Dumansız Yaşam">
<?php if($s['video']): ?><video class="film" id="heroVideo" autoplay muted loop playsinline preload="auto" aria-label="Dumansız yaşam açılış videosu"><source src="<?=e($s['video'])?>"></video><?php endif; ?>
<div class="vignette" aria-hidden="true"></div>
<header class="cinema-header"><a class="cinema-brand" href="/" aria-label="Dumansız Yaşam ana sayfa"><span aria-hidden="true">≈</span> dumansız<span class="light">yaşam</span></a><nav class="cinema-nav" aria-label="Ana menü"><a class="discover" href="poliklinikler.php">Bursa’da destek <span aria-hidden="true">↗</span></a><a class="discover" href="rehber.php">Özgürlüğünü keşfet <span aria-hidden="true">↗</span></a></nav></header>
<div class="cinema-bottom"><p>HER NEFESTE, YENİDEN SEN.</p><div class="controls" aria-label="Medya kontrolleri">
<?php include dirname(__DIR__).'/app/audio-control.php'; ?>
</div></div></main></body></html>
