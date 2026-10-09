# Dumansız Yaşam

Türkçe, mobil uyumlu sigara bırakma farkındalık sitesi. PHP 8.2+ ve Hostinger MySQL/MariaDB. Derleme veya Node.js sunucusu gerektirmez.

## Çalıştırma

```sh
php -S 127.0.0.1:8085 -t public
php tests/check.php
node tests/calculator.cjs
php tests/content.php
node tests/impact.cjs
```

Veritabanı yapılandırılmadan ana sayfa varsayılan metinlerle çalışır, yönetim kapalıdır. Açılış videosu yönetimden yüklenir; varsayılan ses depodadır. Dosya yokken tasarlanmış hareketli arka plan gösterilir. Hesap makinesi verileri cihazda işlenir, saklanmaz.

## Hostinger Business kurulumu

Hedef: https://lavenderblush-llama-450679.hostingersite.com/

1. hPanel → Gelişmiş → GIT → GitHub → `hipokrat-dev/dumans-zyasam` → `main`. Depo kökünü `public_html` içine dağıtın. Kökteki `.htaccess` özel dizinleri engeller ve medya/yönetim isteklerini `public/` altına yönlendirir. PHP 8.2+ ve PDO MySQL/fileinfo/mbstring etkin olmalı.
2. hPanel → Veritabanları → Yönetici: bu siteye özel veritabanı ve kullanıcı oluşturun. phpMyAdmin SQL bölümünde `database/schema.sql` içeriğini çalıştırın. Şema mevcut verileri silmez; dağıtımlar otomatik şema sıfırlamaz.
3. Bu alan adı için `/setup.php` ekranı kullanılabilir: veritabanı şifresini ve yeni yönetici şifresini site sahibi girer. Ekran sabit `u149068033_dumansiz` kullanıcısını doğrular, web kökü dışındaki `../dumansiz-config.php` dosyasını oluşturur ve kendini kapatır. Otomatik dağıtım bu özel dosyayı değiştirmez. Başka bir hostta elle kurulum için `config.example.php` dosyasını `config.local.php` olarak site köküne kopyalayın, gerçek MySQL bilgilerini ve `password_hash` ile üretilmiş yönetici parola özetini yazın. Bu dosya Git tarafından yok sayılır, HTTP erişimi `.htaccess` ile engellenir. Gerçek şifreleri GitHub'a yüklemeyin. Daha güçlü ayrım için web kökünü `public/` olarak ayarlayın; diğer dosyalar web kökü dışında kalır.
4. `public/uploads` PHP tarafından yazılabilir olmalı (normalde 755; 777 kullanmayın). `.user.ini` yükleme limitlerini sağlar; hPanel limitleri daha düşükse 50 MB upload / 105 MB post olarak ayarlayın.
5. HTTPS ile `/admin.php` adresine girin, ana başlık/açıklama ve medya dosyalarını kaydedin. Video MP4/WebM; ses MP3/OGG/WAV. Dosyalar 50 MB ile sınırlandırılmış, MIME türleri sunucuda doğrulanır. Videonun ilk 15 saniyesi döner. Ses sayfa açılır açılmaz başlamayı dener, otomatik oynatma engellenirse kullanıcı düğmeye basar.
6. hPanel'de Git otomatik dağıtımı etkin bırakın. `main` dalına gönderilen kod değişiklikleri otomatik yayınlanır. Bu işleyiş Hostinger'in Git entegrasyonudur; GitHub Actions yalnızca kontrolleri çalıştırır. Başarısız kontrollerin yayını engellemesi için main dalına uygun koruma kuralı konmalıdır.
7. Her yayında `config.local.php` ve `public/uploads` korunmalıdır. Hostinger dağıtımı temiz klasörle değiştiriyorsa bunları web kökü dışındaki kalıcı konuma taşıyıp yapılandırmayı uyarlamadan otomatik dağıtımı açmayın. İlk kurulumda bunu bir test yüklemesi ve yeniden dağıtımla doğrulayın.

Resmi rehber: https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/

## Güvenlik ve içerik

- Yönetimde parola özeti, sunucu tarafı oturum, 30 dakika boşta kalma süresi, CSRF koruması ve IP başına 5 hatalı girişten sonra 15 dakika bekleme bulunur. CSP ve HTML kaçışları uygulanır.
- Kullanıcı sağlık verisi toplanmaz. Yönetici oturumu için zorunlu oturum çerezi kullanılır. Sunucu erişim günlüklerini Hostinger yönetir.
- Klinik tedavi veya başarı garantisi verilmez. İçerikte WHO, CDC ve Sağlık Bakanlığı kaynakları bulunur; profesyonel bırakma desteği teşvik edilir.
- Sayfanın kavramsal çerçevesi özgün metinlerle bağımlılık döngüsünü ve öğrenilmiş çağrışımları açıklamaktır. Kitap metni veya markalı tedavi programı kullanılmaz.

## Yayın doğrulaması

HTTPS ana sayfa ve `/admin.php`; kaydet-yenile kalıcılığı; gerçek video/ses; mobil görünüm; maliyet hesabı; hatalı giriş/CSRF; `/config.local.php`, `/.git/config` ve `/database/schema.sql` için 403 kontrol edin. Yedeklemelerde MySQL ile yüklenen medyayı birlikte saklayın.

## Bursa poliklinik rehberi

`public/poliklinikler.php` 16 merkezi listeler; kaynaklı kurum verileri `app/clinics.php` içindedir. ALO 171 Bursa listesinin 15 kaydı ve Bursa Şehir Hastanesi bulunur. Kaynak kontrol tarihi 3 Ekim 2026; telefonla teyit yapılmamıştır. Kaynaklarda çelişen adresler ilgili kartların notlarında açıklanır. Yeni bir kayıt eklerken sigara bırakma hizmeti kaynağı, kurum telefonu, adres ve ilçe birlikte doğrulanmalıdır. Tüm Bursa birimlerinin eksiksiz ve sürekli güncel olduğu iddia edilmez.

İlçe ve merkez seçimi tarayıcıda çalışır. JavaScript kapalıyken GET formu seçilen merkezi gösterir. Google Haritalar ve yol tarifi bağlantıları kurum + adresle açılır; ziyaretçinin konumu istenmez.

## Ses ve medya kontrolleri

Kullanıcının 4 Ekim 2026 tarihli MP3 dosyası `public/assets/media/ambience.mp3` içinde varsayılan sestir. Panelden yüklenen ses varsa önceliklidir. Ses sayfa açılır açılmaz başlamayı dener; tarayıcı engellerse erişilebilir hoparlör simgesiyle açılır. Video duraklatma düğmesi kaldırılmıştır. Hareket azaltma tercihi olan ziyaretçilerde video durdurulur.

Bursa sayfasında ilçe ve merkez seçimi tek kartı günceller. Kurum iletişim verileri tasarım güncellemesinde değiştirilmemiştir.

## Seçmeli Bursa arayüzü

Poliklinikler artık tek kart üzerinde gösterilir. İlçe seçimi merkez menüsünü günceller; merkez menüsü ve önceki/sonraki düğmeleri adres, telefon, not ve harita bağlantılarını birlikte değiştirir. Kaynak açıklaması erişilebilir bir dialog içindedir. JavaScript olmadan GET formu seçilen merkezi sunucuda gösterir.

3D katmanlar ve geçiş efektleri CSS ile çizilir. Hassas fare işaretçisinde hafif eğim uygulanır; dokunmatik cihazlarda eğim devre dışıdır, hareket azaltma tercihinde animasyonlar kapatılır.

## Seçmeli keşif ve etki ekranları

`rehber.php` tek ekranda Nikotin Yalanları ve Hayatın Gerçekleri seçim kartlarını sunar. Her sekmede bir başlık seçilir; başlığın hemen altında ses, kısa açıklama ve isteğe bağlı video bulunur. Başlık veya sekme değişince önceki medya durur. Dokuz başlangıç başlığı sunulur; konu sesleri yönetimden eklenene kadar yüklenmediği belirtilir.

`etkiler.php` Para / Sağlık / Zaman seçimlerini ve günlük paket, yıl, fiyat kontrollerini içerir. Para bugünkü sabit fiyatla hesaplanır; her çuval 25.000 TL, çizim sınırı 18 çuvaldır ve fazlası sayıyla gösterilir. Zaman sigara içmeye ayrılan dakikadır, yaşam süresi kaybı değildir. Paket-yıla göre koyulaşan ve nefes hareketi yapan akciğer tıbbi tahmin değil, açıkça işaretlenmiş temsili animasyondur. Formüller ve CDC kaynakları isteğe bağlı pencerededir. Ziyaretçi girdileri saklanmaz.

Admin içerik stüdyosunda ana sayfa medya/metinleri ve üç kategori düzenlenir. En fazla 24 konu, 70 karakter başlık ve isteğe bağlı 220 karakter açıklama desteklenir. Her konuya ses ve video yüklenebilir veya medya bağlantısı kaldırılabilir. İçerikler mevcut `settings` tablosunda `content_items` JSON kaydına, işlem kilidi ve doğrulama ile yazılır; şema değişikliği gerekmez. Dosya yükleme başarısız olursa işlem geri alınır ve yeni taşınmış dosyalar temizlenir. Başlık kaldırıldığında eski medya dosyaları yedekleme amacıyla tutulur.

## Yönetici şifresi kurtarma

`admin.php` giriş ekranındaki “Şifremi unuttum” bağlantısı `sifre-yenile.php` formunu açar. Site sahibi mevcut Hostinger veritabanı şifresini doğrular ve yeni yönetici şifresini kendisi girer. Form HTTPS, CSRF ve sunucu genelinde beş denemeden sonra 15 dakika bekleme ile korunur. Veritabanı bağlantısı korunarak yalnızca yönetici şifresinin özeti, özel yapılandırma dosyasına atomik olarak yazılır. Dosya izinleri 0600 kalır. Şifre değişikliğinde eski yönetici oturumları geçersizleşir. Giriş denemesi kilidi IP ve şifre özeti sürümüne bağlıdır; doğrulanmış şifre yenileme sonrası eski kilit yeni şifreyi engellemez. Veritabanı şifresi de unutulduysa bu form doğrulama yapamaz; Hostinger hesabı üzerinden sahiplik doğrulanarak ayrıca kurtarma gerekir.

`php tests/recovery.php` yanlış doğrulama, şifre kuralları, atomik yapılandırma koruması ve yeni şifre özetini test eder.

Keşfet ekranında yalnızca `kancalar` ve `motivasyon` kategorileri gösterilir. Eski Tetikleyiciler kayıtları ve medya bağlantıları yönetimde korunur; ziyaretçi ekranında gösterilmez. Kategori kimlikleri değişmediği için mevcut başlıklar ve yüklemeler korunur.

## Görselli açılış ekranı

Ana sayfa video veya ses oynatmaz; kullanıcı görseli `assets/media/freedom-key.png` orijinal 1448×1086 çözünürlükte gösterilir. Slogan “Özgürlüğünün anahtarı senin elinde.” şeklindedir. Yönetimde Ana sayfa bölümünden iki slogan satırı ve PNG/JPG/WebP görsel güncellenebilir. Önceden yüklenen video ve sesler silinmez, ancak açılış ekranında kullanılmaz. Bölüm sesleri çalışmaya devam eder.

## Dinlenme istatistikleri

Yönetici panelindeki `admin.php?tab=listens` yalnızca giriş yapmış yöneticiye mevcut seslerin sayısını ve sıralamasını gösterir. `audio_listens` tablosu ilk rapor veya dinlenme kaydında otomatik oluşturulur; mevcut içerik ve dosyalar değiştirilmez.

En az 10 saniye aktif, oynatıcıda sesi açık dinleme sayılır. Aynı geçici tarayıcı oturumunda aynı kayıt için 30 dakikalık tekrar engeli vardır. Kısa kayıtlar, çevrimdışı olaylar ve yönetici önizlemeleri sayılmaz. Bu ölçüm tekil kişi ya da sahteciliğe dayanıklı reklam metriği değildir. Geçmiş dinlemeler geri getirilemez. Ses dosyası değişince yeni sayaç kullanılır; başlık değişince korunur. İsim/IP bilgisi tabloya kaydedilmez. `dumansiz_listen` HttpOnly, SameSite=Strict oturum çerezi yalnızca oynatma başlayınca açılır.

Kontrol: `php tests/listening.php` ve `node tests/listening.cjs`.
