<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
function check(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
check(e('<script>"&')==='&lt;script&gt;&quot;&amp;', 'HTML escaping');
$s=settings();check($s['video']===''&&$s['audio']==='', 'Unconfigured media must not create broken requests');
ob_start();include dirname(__DIR__).'/public/index.php';$html=ob_get_clean();
check(str_contains($html,'lang="tr"'), 'Turkish document');
check(!str_contains($html,'<video'), 'Missing video has intentional visual fallback');
check(str_contains($html,'href="rehber.php"'), 'Guide available without scrolling the homepage');
check(!preg_match('/allen|carr|care model/i',$html),'No named method attribution in site');
check(!str_contains($html,'id="yearly"'),'Homepage stays focused on video');
check(str_contains(file_get_contents(dirname(__DIR__).'/public/rehber.php'),'tel:171'),'Guide retains support CTA');
echo "PHP checks passed\n";
