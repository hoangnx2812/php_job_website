<?php
// ======================================================
// Cấu hình chung của dự án
// ======================================================

// Kết nối MySQL (trùng thông tin trong docker-compose.yml)
define('DB_HOST', 'mysql');      // tên service trong docker-compose
define('DB_PORT', 3306);
define('DB_NAME', 'job_website');
define('DB_USER', 'app');
define('DB_PASS', 'app');

// Thư mục lưu file CV (mount từ host qua docker-compose)
define('UPLOAD_DIR', '/var/www/uploads/cv');

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
