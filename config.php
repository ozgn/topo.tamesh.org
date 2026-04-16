<?php

/**
 * .env dosyasındaki değişkenleri yükler.
 * @param string $path .env dosyasının yolu
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Tırnakları temizle
            $value = trim($value, '"\'');

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// .env dosyasını yükle
loadEnv(__DIR__ . '/.env');

// Veritabanı yapılandırması - GİZLİ ŞİFRE İÇERMEZ
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

// Kritik değerlerin kontrolü
if (!$db_host || !$db_name || !$db_user) {
    header('Content-Type: application/json');
    die(json_encode([
        "error" => "Configuration missing. Please set up your .env file using .env.example as a template."
    ]));
}

try {
    // PDO bağlantısını oluştur
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true, // Tür dönüşümü uyumluluğu için true yapıldı
    ]);
} catch (PDOException $e) {
    // Güvenli hata mesajı (Şifreyi sızdırmaz)
    header('Content-Type: application/json');
    die(json_encode(["error" => "Database connection failed. Please check your .env credentials."]));
}
