# Dumansız Yaşam

Türkçe, mobil uyumlu sigara bırakma farkındalık sitesi. PHP 8.2+ ve Hostinger MySQL/MariaDB. Derleme veya Node.js sunucusu gerektirmez.

## Çalıştırma

```sh
php -S 127.0.0.1:8085 -t public
php tests/check.php
node tests/calculator.cjs
```

Veritabanı yapılandırılmadan ana sayfa varsayılan metinlerle çalışır, yönetim kapalıdır. Gerçek video/ses depoda bulunmaz; yönetimden yüklenir. Dosya yokken tasarlanmış hareketli arka plan gösterilir. Hesap makinesi verileri cihazda işlenir, saklanmaz.

## Hostinger Business kurulumu

Hedef: https://lavenderblush-llama-450679.hostingersite.com/

1. hPanel → Gelişmiş → GIT → GitHub → `hipokrat-dev/dumans-zyasam` → `main`. Depo kökünü `public_html` içine dağıtın. Kökteki `.htaccess` özel dizinleri engeller ve medya/yönetim isteklerini `public/` altına yönlendirir. PHP 8.2+ ve PDO MySQL/fileinfo/mbstring etkin olmalı.
2. hPanel → Veritabanları → Yönetici: bu siteye özel veritabanı ve kullanıcı oluşturun. phpMyAdmin SQL bölümünde `database/schema.sql` içeriğini çalıştırın. Şema mevcut verileri silmez; dağıtımlar otomatik şema sıfırlamaz.
3. Bu alan adı için `/setup.php` ekranı kullanılabilir: veritabanı şifresini ve yeni yönetici şifresini site sahibi girer. Ekran sabit `u149068033_dumansiz` kullanıcısını doğrular, web kökü dışındaki `../dumansiz-config.php` dosyasını oluşturur ve kendini kapatır. Otomatik dağıtım bu özel dosyayı değiştirmez. Başka bir hostta elle kurulum için `config.example.php` dosyasını `config.local.php` olarak site köküne kopyalayın, gerçek MySQL bilgilerini ve `password_hash` ile üretilmiş yönetici parola özetini yazın. Bu dosya Git tarafından yok sayılır, HTTP erişimi `.htaccess` ile engellenir. Gerçek şifreleri GitHub'a yüklemeyin. Daha güçlü ayrım için web kökünü `public/` olarak ayarlayın; diğer dosyalar web kökü dışında kalır.
4. `public/uploads` PHP tarafından yazılabilir olmalı (normalde 755; 777 kullanmayın). `.user.ini` yükleme limitlerini sağlar; hPanel limitleri daha düşükse 50 MB upload / 105 MB post olarak ayarlayın.
5. HTTPS ile `/admin.php` adresine girin, ana başlık/açıklama ve medya dosyalarını kaydedin. Video MP4/WebM; ses MP3/OGG/WAV. Dosyalar 50 MB ile sınırlandırılmış, MIME türleri sunucuda doğrulanır. Videonun ilk 15 saniyesi döner. Ses 2,5 saniye sonra başlamayı dener, otomatik oynatma engellenirse kullanıcı düğmeye basar.
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

İlçe filtresi ve Türkçe / ASCII karakterlerle arama tarayıcıda çalışır. JavaScript kapalıyken bütün kartlar görünür. Google Haritalar ve yol tarifi bağlantıları kurum + adresle açılır; ziyaretçinin konumu istenmez.
