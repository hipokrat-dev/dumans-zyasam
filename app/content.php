<?php
declare(strict_types=1);
function content_categories(): array { return ['tetikleyiciler'=>'Tetikleyiciler','kancalar'=>'Nikotinin Yalanları','motivasyon'=>'Motivasyon']; }
function default_content(): array {
 return [
 ['id'=>'kahve','category'=>'tetikleyiciler','title'=>'Kahve aynı. Seçimin yeni.','text'=>'Bir fincan kahvenin keyfi sigaraya ait değil. Bu molayı kendin için yeniden tanımla.','audio'=>'','video'=>''],
 ['id'=>'stres','category'=>'tetikleyiciler','title'=>'Bir nefeslik ara.','text'=>'Stres geldiğinde dur. Bir bardak su iç, kısa bir yürüyüş yap. Kendine başka bir mola seç.','audio'=>'','video'=>''],
 ['id'=>'yemek','category'=>'tetikleyiciler','title'=>'Yemek bitti. Döngü bitebilir.','text'=>'Masadan kalk, dişlerini fırçala ya da kısa bir yürüyüşe çık. Tanıdık bir anı yeni bir hareketle eşleştir.','audio'=>'','video'=>''],
 ['id'=>'rahatlama','category'=>'kancalar','title'=>'Rahatlama mı, kısa bir mola mı?','text'=>'Nikotin yoksunluğu yeniden sigara içince kısa süre hafifleyebilir. Bu döngüyü fark etmek, onu değiştirmek için bir başlangıç.','audio'=>'','video'=>''],
 ['id'=>'arkadas','category'=>'kancalar','title'=>'Keyif sigaraya ait değil.','text'=>'Sohbetin, kahvenin ve molanın güzel tarafı senin hayatında. Onları dumansız da yaşayabilirsin.','audio'=>'','video'=>''],
 ['id'=>'yapamam','category'=>'kancalar','title'=>'“Yapamam” son sözün değil.','text'=>'Geçmiş denemelerin geleceğini belirlemez. Nelerin zorlayıcı olduğunu fark edip destekle yeniden başlayabilirsin.','audio'=>'','video'=>''],
 ['id'=>'bugun','category'=>'motivasyon','title'=>'Bugün kendini seç.','text'=>'Her şeyi bir anda çözmek zorunda değilsin. Bugün bir kişiden destek istemek bile gerçek bir adım.','audio'=>'','video'=>''],
 ['id'=>'ozgur','category'=>'motivasyon','title'=>'Hayatında sana yer aç.','text'=>'Dumansız bir mola, kendine kalan zaman, yeni bir seçim. Küçük adımların birikmesine izin ver.','audio'=>'','video'=>''],
 ['id'=>'birlikte','category'=>'motivasyon','title'=>'Bunu yalnız yapmak zorunda değilsin.','text'=>'Sana uygun bir plan için bir sağlık profesyoneliyle görüşebilir, ALO 171’den destek alabilirsin.','audio'=>'','video'=>'']
 ];
}
function valid_upload_path(string $path, string $kind): bool {
 return (bool)preg_match($kind==='audio'?'~^uploads/[a-f0-9]{32}\.(mp3|ogg|wav)$~':'~^uploads/[a-f0-9]{32}\.(mp4|webm)$~',$path);
}
function validate_content(array $items): array {
 if(count($items)>24) throw new RuntimeException('En fazla 24 başlık ekleyebilirsiniz.');
 $ids=[];
 foreach($items as $item){
  if(!is_array($item)) throw new RuntimeException('İçerik biçimi geçersiz.');
  foreach(['id','category','title','text','audio','video'] as $key) if(!isset($item[$key])||!is_string($item[$key])) throw new RuntimeException('İçerik alanı geçersiz.');
  if(!preg_match('/^[a-z0-9-]{1,40}$/',$item['id'])||isset($ids[$item['id']]))throw new RuntimeException('Başlık kimliği geçersiz.');
  $ids[$item['id']]=true;
  if(!isset(content_categories()[$item['category']])||trim($item['title'])===''||mb_strlen($item['title'])>70||mb_strlen($item['text'])>220)throw new RuntimeException('Başlık en fazla 70, kısa metin en fazla 220 karakter olmalı.');
  foreach(['audio','video'] as $kind) if($item[$kind]!==''&&!valid_upload_path($item[$kind],$kind))throw new RuntimeException('Medya yolu geçersiz.');
 }
 return array_values($items);
}
function content_items(): array {
 try { if($pdo=db()){ $q=$pdo->prepare('SELECT value FROM settings WHERE name=?');$q->execute(['content_items']);$raw=$q->fetchColumn();if($raw!==false){$decoded=json_decode($raw,true,32,JSON_THROW_ON_ERROR);if(!is_array($decoded))throw new RuntimeException('Invalid content');return validate_content($decoded);}} }
 catch(Throwable $err){error_log('Dumansiz: content unavailable');}
 return default_content();
}
function receive_media(string $field,string $kind,array &$moved): ?string {
 if(!isset($_FILES[$field])||$_FILES[$field]['error']===UPLOAD_ERR_NO_FILE)return null;
 $f=$_FILES[$field];
 if(!is_int($f['error'])||$f['error']!==UPLOAD_ERR_OK||$f['size']>50*1024*1024)throw new RuntimeException('Dosya yüklenemedi. En fazla 50 MB kullanın.');
 $types=$kind==='audio'?['audio/mpeg'=>'mp3','audio/ogg'=>'ogg','audio/wav'=>'wav','audio/x-wav'=>'wav']:['video/mp4'=>'mp4','video/webm'=>'webm'];
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
 if(!isset($types[$mime]))throw new RuntimeException('Desteklenmeyen medya biçimi.');
 $path='uploads/'.bin2hex(random_bytes(16)).'.'.$types[$mime];
 if(!move_uploaded_file($f['tmp_name'],dirname(__DIR__).'/public/'.$path))throw new RuntimeException('Dosya kaydedilemedi.');
 $moved[]=$path;return $path;
}
