<?php
require dirname(__DIR__).'/app/listening.php';
function expect(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
$entries=[];$item=['id'=>'one','audio'=>'uploads/aaa.mp3'];$key=listening_key($item);
$entry=listening_begin($entries,$key,1000);
expect(!listening_eligible($entry,$entry['ticket'],1009),'A quick click must not count');
expect(listening_eligible($entry,$entry['ticket'],1010),'Ten seconds qualifies');
expect(!listening_eligible($entry,'forged',1010),'Forged ticket rejected');
expect(!listening_eligible([],'',1010),'Missing start rejected');
$entries[$key]['counted']=true;
expect(!listening_eligible($entries[$key],$entry['ticket'],1011),'Replay not counted');
expect(listening_begin($entries,$key,2799)['counted'],'Reload shares deduplication window');
$new=listening_begin($entries,$key,2800);
expect(!$new['counted']&&$new['ticket']!==$entry['ticket'],'New window permits a new listen');
expect(!listening_eligible($entry,$entry['ticket'],2800),'Expired ticket rejected');
expect(listening_key($item)!==listening_key(['id'=>'one','audio'=>'uploads/bbb.mp3']),'Replacement audio starts a new counter');
expect(listening_key($item)===listening_key($item+['title'=>'Renamed']),'Renaming preserves counter');
for($i=0;$i<100;$i++)listening_begin($entries,(string)$i,2801);
expect(count($entries)<=64,'Session storage bounded');
echo "Listening validation passed\n";
$pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);listening_schema($pdo);
$a=['id'=>'a','category'=>'kancalar','audio'=>'uploads/a.mp3','title'=>'Birinci','text'=>'','video'=>''];
$b=['id'=>'b','category'=>'motivasyon','audio'=>'uploads/b.mp3','title'=>'İkinci','text'=>'','video'=>''];
$pdo->prepare('INSERT INTO audio_listens (audio_key,plays,last_listened) VALUES (?,7,1000)')->execute([listening_key($b)]);
$rows=listening_report($pdo,[$a,$b,array_replace($a,['id'=>'empty','audio'=>''])]);
expect(count($rows)===2&&$rows[0]['id']==='b'&&$rows[0]['plays']===7&&$rows[1]['plays']===0,'Report sorts by plays and includes zero counts');
expect(listening_report($pdo,[array_replace($b,['audio'=>'uploads/new.mp3'])])[0]['plays']===0,'New media does not inherit old plays');
echo "Listening report checks passed\n";
