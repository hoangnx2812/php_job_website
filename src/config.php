<?php
// ======================================================
// Cấu hình chung của dự án
// ======================================================

// -------------------------------------------------------
// Cấu hình kết nối MySQL
// Docker  → DB_HOST = 'mysql', USER = 'app', PASS = 'app'
// XAMPP   → DB_HOST = 'localhost', USER = 'root', PASS = ''
// -------------------------------------------------------
define('DB_HOST', 'localhost');   // XAMPP: localhost | Docker: mysql
define('DB_PORT', 3306);
define('DB_NAME', 'job_website');
define('DB_USER', 'root');        // XAMPP: root      | Docker: app
define('DB_PASS', '');            // XAMPP: ''        | Docker: app

// Thư mục lưu file CV — dùng __DIR__ để tự động khớp cả Docker lẫn XAMPP
define('UPLOAD_DIR',      realpath(__DIR__ . '/../uploads/cv')      ?: __DIR__ . '/../uploads/cv');

// Thư mục lưu logo công ty
define('LOGO_UPLOAD_DIR', realpath(__DIR__ . '/../uploads/logos')   ?: __DIR__ . '/../uploads/logos');

// URL gốc của app (để build link)
define('BASE_URL', '/index.php');

// Bật hiển thị lỗi cho môi trường dev
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Đặt timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt buộc PHP output UTF-8 để không bị lỗi tiếng Việt
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Khởi động session cho toàn bộ app
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
