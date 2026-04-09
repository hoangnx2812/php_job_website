<?php
// ======================================================
// Kết nối database bằng PDO
// ======================================================
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        // Retry vài lần vì MySQL có thể chưa sẵn sàng khi container web vừa bật
        $attempts = 0;
        while (true) {
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES     => false,
                    // Ép session MySQL dùng utf8mb4 (tránh lỗi dấu tiếng Việt)
                    PDO::MYSQL_ATTR_INIT_COMMAND   => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
                break;
            } catch (PDOException $e) {
                $attempts++;
                if ($attempts >= 10) {
                    die('Không kết nối được MySQL: ' . $e->getMessage());
                }
                sleep(1);
            }
        }
    }
    return $pdo;
}
