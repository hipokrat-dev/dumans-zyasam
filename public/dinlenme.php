<?php
require dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/content.php';
require_once dirname(__DIR__).'/app/listening.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
function reply(int $status,array $data): never { http_response_code($status); echo json_encode($data); exit; }
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Allow: POST');reply(405,['ok'=>false]);}
// JSON and same-origin checks keep third-party pages from posting listening events.
$origin=$_SERVER['HTTP_ORIGIN']??'';
$scheme=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http';
if($origin!==$scheme.'://'.($_SERVER['HTTP_HOST']??'') || !str_starts_with(strtolower($_SERVER['CONTENT_TYPE']??''),'application/json')) reply(403,['ok'=>false]);
if((int)($_SERVER['CONTENT_LENGTH']??0)>1024)reply(413,['ok'=>false]);
$raw=file_get_contents('php://input',false,null,0,1025);
if(strlen($raw)>1024)reply(413,['ok'=>false]);
$data=json_decode($raw,true);
if(!is_array($data)||!is_string($data['id']??null)||!is_string($data['audio']??null)||!in_array($data['action']??null,['start','listen'],true))reply(400,['ok'=>false]);
$item=null;
foreach(content_items() as $candidate) if($candidate['id']===$data['id']&&$candidate['audio']!==''&&$candidate['audio']===$data['audio']&&in_array($candidate['category'],['kancalar','motivasyon'],true)){$item=$candidate;break;}
if(!$item)reply(404,['ok'=>false]);
try {
    $pdo=db();if(!$pdo)reply(503,['ok'=>false]);
    ini_set('session.use_strict_mode','1');
    session_name('dumansiz_listen');
    session_set_cookie_params(['lifetime'=>0,'httponly'=>true,'secure'=>$scheme==='https','samesite'=>'Strict','path'=>'/']);
    session_start();
    $_SESSION['listens']??=[];
    $key=listening_key($item);$now=time();
    if($data['action']==='start'){
        $entry=listening_begin($_SESSION['listens'],$key,$now);
        reply(200,['ok'=>true,'ticket'=>$entry['ticket'],'counted'=>$entry['counted']]);
    }
    $entry=$_SESSION['listens'][$key]??[];
    $ticket=is_string($data['ticket']??null)?$data['ticket']:'';
    if(!isset($entry['ticket'])||!hash_equals($entry['ticket'],$ticket))reply(403,['ok'=>false]);
    if(!empty($entry['counted']))reply(200,['ok'=>true,'counted'=>true]);
    if(!listening_eligible($entry,$ticket,$now))reply(409,['ok'=>false]);
    listening_schema($pdo);
    listening_increment($pdo,$key,$now);
    $_SESSION['listens'][$key]['counted']=true;
    reply(200,['ok'=>true,'counted'=>true]);
} catch(Throwable $err){error_log('Dumansiz: listening statistics unavailable');reply(503,['ok'=>false]);}
