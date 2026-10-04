<?php
declare(strict_types=1);

function replace_admin_password(string $target, string $proof, string $password, string $confirmation): void {
    if ($password === '' || strlen($password) > 72) {
        throw new RuntimeException('Yeni şifre boş olamaz ve en fazla 72 bayt olabilir.');
    }
    if (!hash_equals($password, $confirmation)) {
        throw new RuntimeException('Yeni şifreler eşleşmiyor.');
    }
    if (!is_file($target)) throw new RuntimeException('Önce site kurulumu tamamlanmalı.');
    if (function_exists('opcache_invalidate')) opcache_invalidate($target, true);
    $current = require $target;
    $expected = $current['database']['password'] ?? null;
    if (!is_string($expected) || $expected === '' || !hash_equals($expected, $proof)) {
        throw new RuntimeException('Veritabanı şifresi doğrulanamadı.');
    }
    $current['admin_password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    $contents = "<?php\nreturn " . var_export($current, true) . ";\n";
    $temporary = $target . '.' . bin2hex(random_bytes(12)) . '.tmp';
    $mask = umask(0077);
    try {
        $file = fopen($temporary, 'x');
    } finally {
        umask($mask);
    }
    if (!$file) throw new RuntimeException('Şifre kaydedilemedi. Hostinger dosya izinlerini kontrol edin.');
    try {
        if (fwrite($file, $contents) !== strlen($contents) || !fflush($file)) {
            throw new RuntimeException('Şifre kaydedilemedi. Tekrar deneyin.');
        }
        fclose($file); $file = null;
        if (!rename($temporary, $target)) throw new RuntimeException('Şifre kaydedilemedi. Tekrar deneyin.');
        if (function_exists('opcache_invalidate')) opcache_invalidate($target, true);
    } finally {
        if (is_resource($file)) fclose($file);
        if (is_file($temporary)) unlink($temporary);
    }
}
