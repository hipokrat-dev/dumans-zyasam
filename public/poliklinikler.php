<?php
require_once dirname(__DIR__).'/app/bootstrap.php';
$clinics = require dirname(__DIR__).'/app/clinics.php';
$districts = ['Büyükorhan','Gemlik','Gürsu','İnegöl','Mudanya','Mustafakemalpaşa','Nilüfer','Orhangazi','Osmangazi','Yenişehir','Yıldırım'];
$districtInput = is_string($_GET['district'] ?? null) ? $_GET['district'] : '';
$selectedDistrict = in_array($districtInput, $districts, true) ? $districtInput : 'Nilüfer';
$available = array_filter($clinics, fn($c) => $c['district'] === $selectedDistrict);
$requested = filter_var($_GET['clinic'] ?? null, FILTER_VALIDATE_INT);
$selected = $requested !== false && $requested !== null && isset($available[$requested]) ? $requested : array_key_first($available);
$renderClinic = function(array $clinic): void {
    $query=$clinic['name'].' '.$clinic['address'];
    $map='https://www.google.com/maps/search/?api=1&query='.rawurlencode($query);
    $directions='https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($query);
?>
<article class="clinic-card" aria-label="Seçilen destek merkezi" data-type="<?=str_contains($clinic['type'],'Hastane') || str_contains($clinic['type'],'hastane') ? 'hospital' : 'shm'?>" data-district="<?=e($clinic['district'])?>" data-search="<?=e($query.' '.$clinic['type'])?>"><div class="card-top"><span class="district-tag"><?=e($clinic['district'])?></span><span class="clinic-symbol" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 21V4h8v17M4 21V9h4m8 0h4v12M2 21h20M12 7v6m-3-3h6M10 21v-4h4v4"/></svg></span></div><p class="clinic-type"><?=e($clinic['type'])?></p><h3><?=e($clinic['name'])?></h3><div class="address"><span aria-hidden="true">⌖</span><address><?=e($clinic['address'])?></address></div><div class="phones"><span class="field-label">TELEFON<?=isset($clinic['extension'])?' · DAHİLİ '.e($clinic['extension']):''?></span><?php foreach($clinic['phones'] as $phone): ?><a href="tel:+9<?=preg_replace('/\D/','',$phone)?>"><?=e($phone)?> <span aria-hidden="true">↗</span></a><?php endforeach; ?></div>
<?php if(isset($clinic['note'])): ?><details class="clinic-note"><summary>Başvuru ve adres notu</summary><p><?=e($clinic['note'])?></p><?php if(isset($clinic['alternate_phone'])): ?><a href="tel:+9<?=preg_replace('/\D/','',$clinic['alternate_phone'])?>">Alternatif numarayı ara ↗</a><?php endif; ?></details><?php endif; ?>
<div class="card-actions"><a class="map-link" href="<?=e($map)?>" target="_blank" rel="noopener" aria-label="<?=e($clinic['name'])?> Google Haritalar’da aç">Google Haritalar <span aria-hidden="true">↗</span></a><a class="directions" href="<?=e($directions)?>" target="_blank" rel="noopener" aria-label="<?=e($clinic['name'])?> için yol tarifi al">Yol tarifi ↗</a></div><div class="card-sources"><a href="<?=e($clinic['source'])?>" target="_blank" rel="noopener">Poliklinik kaynağı ↗</a><?php if(isset($clinic['contact_source'])): ?><a href="<?=e($clinic['contact_source'])?>" target="_blank" rel="noopener">Kurum bilgileri ↗</a><?php endif; ?></div></article>
<?php }; ?>
<!doctype html>
<html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Bursa’da Destek Bul — Dumansız Yaşam</title><meta name="description" content="İlçeni ve polikliniğini seç. Bursa sigara bırakma merkezlerinin adres, telefon ve Google Haritalar yol tariflerine ulaş."><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/clinics.css?v=4"><script src="assets/clinics.js?v=4" defer></script></head>
<body>
<a class="skip" href="#district">Merkez seçimine geç</a>
<div class="ambient ambient-one" aria-hidden="true"></div><div class="ambient ambient-two" aria-hidden="true"></div>
<header class="site-header"><a class="brand" href="/" aria-label="Dumansız Yaşam ana sayfa">≈ dumansız<b>yaşam</b></a><nav aria-label="Ana menü"><a href="rehber.php">Keşfet</a><a href="etkiler.php">Gerçek bedeli</a><a class="active" href="poliklinikler.php" aria-current="page">Bursa’da destek</a></nav></header>
<main class="experience">
<section class="selection" aria-labelledby="page-title"><p class="eyebrow"><span></span> BURSA · BİRLİKTE DAHA KOLAY</p><h1 id="page-title">Bir seçim. <br><em>Yeni bir başlangıç.</em></h1><p class="intro">Sana yakın desteği keşfet.<br>İlçeni ve merkezini seç, ilk adımını at.</p>
<form class="selector-form" method="get" aria-label="Destek merkezi seç">
<label for="district"><span class="step">01</span><span>İlçeni seç<small><?=count($districts)?> ilçede destek</small></span></label>
<select id="district" name="district"><?php foreach($districts as $district): ?><option value="<?=e($district)?>" <?=$district===$selectedDistrict?'selected':''?>><?=e($district)?></option><?php endforeach; ?></select>
<label for="clinic"><span class="step">02</span><span>Merkezini seç<small id="choice-count"><?=count($available)?> seçenek</small></span></label>
<select id="clinic" name="clinic"><?php foreach($clinics as $id=>$clinic): ?><option value="<?=$id?>" data-district="<?=e($clinic['district'])?>" <?=$id===$selected?'selected':''?>><?=e($clinic['name'])?></option><?php endforeach; ?></select>
<noscript><button class="fallback-submit" type="submit">Seçimi göster ↗</button></noscript>
</form>
<div class="help-line"><span class="help-icon" aria-hidden="true">↗</span><div><p>İlk konuşma, ilk adım.</p><a href="tel:171">ALO 171 <span>Sigara bırakma danışma hattı</span></a></div></div>
<div class="selection-footer"><span><?=count($clinics)?> destek noktası</span><span>Kaynak kontrolü · 03.10.2026</span></div>
</section>
<section class="viewer" aria-label="Seçilen merkezin bilgileri"><div class="viewer-top"><span class="viewer-label"><i></i> SENİN DESTEK NOKTAN</span><span id="result-count" role="status" aria-live="polite"><?=e($selectedDistrict)?> · <?=array_search($selected,array_keys($available),true)+1?> / <?=count($available)?></span></div>
<div class="perspective-stage"><div class="orbit" aria-hidden="true"></div><div class="card-stack" id="card-stack"><div id="clinic-stage"><?php $renderClinic($clinics[$selected]); ?></div></div></div>
<div class="viewer-bottom"><p>Bir merkez seç.<br><span>Adresine ve iletişim bilgilerine ulaş.</span></p><div class="pager" aria-label="Merkezler arasında gezin" hidden><button type="button" id="previous-clinic" aria-label="Önceki merkez">←</button><button type="button" id="next-clinic" aria-label="Sonraki merkez">→</button></div></div>
</section>
</main>
<footer><a href="/">← Ana sayfa</a><p>Gitmeden önce kurumu arayarak hizmet adresini ve randevuyu teyit et.</p><button id="sources-open" type="button" hidden>Kaynaklar ve güncellik ↗</button><noscript><a href="https://alo171.saglik.gov.tr/TR-68192/bursa.html" target="_blank" rel="noopener">ALO 171 kaynak listesi ↗</a></noscript></footer>
<dialog id="sources-dialog" aria-labelledby="sources-title"><div class="dialog-top"><h2 id="sources-title">Kaynaklar ve güncellik</h2><button type="button" id="sources-close" aria-label="Kaynaklar penceresini kapat">×</button></div>
<section class="source-note"><div><p class="eyebrow">YOLA ÇIKMADAN ÖNCE</p><h2>Bir aramayla<br>teyit et.</h2></div><div><p>Çalışma günleri, hekim ve hizmet binası değişebilir. Gitmeden önce kurumu arayıp sigara bırakma polikliniğinin adresini ve randevu koşullarını doğrula. Telefonlar poliklinik, merkez veya hastane santral numarası olabilir.</p><p>Bu rehber, <a href="https://alo171.saglik.gov.tr/TR-68192/bursa.html" target="_blank" rel="noopener">ALO 171’in Bursa listesindeki 15 kaydın tamamını</a> ve <a href="https://bursasehir.saglik.gov.tr/TR-661722/poliklinik-hakkinda.html" target="_blank" rel="noopener">Bursa Şehir Hastanesinin polikliniğini</a> içerir. İletişim bilgileri Bakanlık ve kurum sayfalarıyla karşılaştırılmıştır; kaynaklar her kartta yer alır. Şehirdeki yeni açılan veya henüz bu kaynaklarda yayımlanmayan tüm birimleri kapsadığı garanti edilemez.</p><p class="checked">Son kaynak kontrolü: <time datetime="2026-10-03">3 Ekim 2026</time> · Telefonla doğrulama yapılmamıştır.</p><p>İlçeni bulamadıysan güncel yönlendirme için <a href="tel:171">ALO 171’i ara ↗</a></p></div></section>

</dialog>
<?php foreach($clinics as $id=>$clinic): ?><template id="clinic-template-<?=$id?>"><?php $renderClinic($clinic); ?></template><?php endforeach; ?>
</body></html>
