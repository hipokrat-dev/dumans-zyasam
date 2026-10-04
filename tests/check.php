<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
function check(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
check(e('<script>"&')==='&lt;script&gt;&quot;&amp;', 'HTML escaping');
$s=settings();check($s['video']===''&&$s['audio']==='assets/media/ambience.mp3', 'Bundled audio available without database setup');
ob_start();include dirname(__DIR__).'/public/index.php';$html=ob_get_clean();
check(str_contains($html,'lang="tr"'), 'Turkish document');
check(!str_contains($html,'<video')&&!str_contains($html,'<audio'), 'Without a saved video the homepage falls back to the image without audio');
check(str_contains($html,'assets/media/freedom-key.png'), 'Homepage retains supplied fallback image');
$homeSource=file_get_contents(dirname(__DIR__).'/public/index.php');
check(str_contains($homeSource,'autoplay muted loop playsinline')&&!str_contains($homeSource,'<audio'), 'Saved homepage video autoplays muted and loops without background audio');
check(str_contains($html,'Özgürlüğünün anahtarı')&&str_contains($html,'senin elinde.'), 'Homepage slogan emphasizes personal agency');
check(str_contains($html,'href="rehber.php"'), 'Guide available without scrolling the homepage');
check(!preg_match('/allen|carr|care model/i',$html),'No named method attribution in site');
check(!str_contains($html,'id="yearly"'),'Homepage stays focused on image');
check(str_contains(file_get_contents(dirname(__DIR__).'/public/rehber.php'),'tel:171'),'Guide retains support CTA');
check(!str_contains($html,'id="videoToggle"'), 'No video pause button');
check(!str_contains($html,'audioToggle')&&!str_contains($html,'assets/app.js'), 'No sound control or autoplay script on homepage');
echo "PHP checks passed\n";
