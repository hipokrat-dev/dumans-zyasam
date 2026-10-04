<?php
require dirname(__DIR__).'/app/bootstrap.php';require dirname(__DIR__).'/app/content.php';
function expect(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
$items=default_content();expect(count(validate_content($items))===9,'Default topics');
expect(validate_content([])===[],'Empty categories supported');
$items[0]['audio']='uploads/'.str_repeat('a',32).'.mp3';expect(validate_content($items)[0]['audio']!== '', 'Uploaded audio accepted');
foreach(['https://bad.example/a.mp3','uploads/../../config.local.php','uploads/'.str_repeat('a',32).'.php'] as $path){$invalid=$items;$invalid[0]['audio']=$path;try{validate_content($invalid);throw new LogicException('Invalid path accepted');}catch(RuntimeException $e){}}
$invalid=$items;$invalid[1]['id']=$invalid[0]['id'];try{validate_content($invalid);throw new LogicException('Duplicate id accepted');}catch(RuntimeException $e){}
$invalid=$items;$invalid[0]['title']=str_repeat('a',71);try{validate_content($invalid);throw new LogicException('Oversized title accepted');}catch(RuntimeException $e){}
echo "Content validation passed\n";
