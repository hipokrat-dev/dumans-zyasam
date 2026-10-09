<?php
declare(strict_types=1);

function listening_key(array $item): string {
    return hash('sha256', $item['id'].'|'.$item['audio']);
}
function listening_schema(PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS audio_listens (audio_key CHAR(64) NOT NULL PRIMARY KEY, plays BIGINT NOT NULL DEFAULT 0, last_listened BIGINT NOT NULL DEFAULT 0)');
}
function listening_begin(array &$entries, string $key, int $now): array {
    foreach ($entries as $id=>$entry) if (($entry['expires']??0)<=$now) unset($entries[$id]);
    if (!isset($entries[$key])) {
        if (count($entries)>=64) array_shift($entries);
        $entries[$key]=['ticket'=>bin2hex(random_bytes(24)), 'started'=>$now, 'expires'=>$now+1800, 'counted'=>false];
    }
    return $entries[$key];
}
function listening_eligible(array $entry, string $ticket, int $now): bool {
    return isset($entry['ticket'],$entry['started'],$entry['expires'])
        && hash_equals($entry['ticket'],$ticket) && empty($entry['counted'])
        && $now-$entry['started']>=10 && $now<$entry['expires'];
}
function listening_increment(PDO $pdo, string $key, int $now): void {
    // One atomic statement prevents lost counts when different visitors listen together.
    $q=$pdo->prepare('INSERT INTO audio_listens (audio_key,plays,last_listened) VALUES (?,1,?) ON DUPLICATE KEY UPDATE plays=plays+1,last_listened=VALUES(last_listened)');
    $q->execute([$key,$now]);
}
function listening_report(PDO $pdo, array $items): array {
    listening_schema($pdo);
    $counts=$pdo->query('SELECT audio_key,plays,last_listened FROM audio_listens')->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    $rows=[];
    foreach($items as $item) {
        if($item['audio']==='' || !in_array($item['category'],['kancalar','motivasyon'],true)) continue;
        $count=$counts[listening_key($item)]??[];
        $rows[]=$item+['plays'=>(int)($count['plays']??0),'last_listened'=>(int)($count['last_listened']??0)];
    }
    usort($rows,fn($a,$b)=>($b['plays']<=>$a['plays'])?:strcmp($a['title'],$b['title']));
    return $rows;
}
