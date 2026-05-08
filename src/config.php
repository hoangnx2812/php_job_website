<?php
// ======================================================
// Cấu hình chung của dự án — Branch UAT (deploy lên InfinityFree)
// ======================================================

// -------------------------------------------------------
// Cấu hình kết nối MySQL trên InfinityFree
// LƯU Ý: tên database (DB_NAME) phải khớp với DB bạn tạo
// trong "MySQL Databases" của InfinityFree control panel.
// InfinityFree luôn ép prefix dạng if0_<userId>_ trước tên DB.
// -------------------------------------------------------
define('DB_HOST', 'sql204.infinityfree.com');
define('DB_PORT', 3306);
define('DB_NAME', 'if0_41829346_job_vietnam');   // ⚠️ TẠO DB này trên control panel trước khi chạy
define('DB_USER', 'if0_41829346');
define('DB_PASS', 'NSkFtyKO5xMU');

// -------------------------------------------------------
// Thư mục lưu file upload (tính từ src/config.php)
// __DIR__ = htdocs/src  →  ../uploads/...  =  htdocs/uploads/...
// -------------------------------------------------------
define('UPLOAD_DIR',      realpath(__DIR__ . '/../uploads/cv')      ?: __DIR__ . '/../uploads/cv');
define('LOGO_UPLOAD_DIR', realpath(__DIR__ . '/../uploads/logos')   ?: __DIR__ . '/../uploads/logos');

// -------------------------------------------------------
// URL gốc của app.
// Trên InfinityFree, root index.php (ở htdocs/) đã bootstrap
// front-controller, nên BASE_URL chỉ cần là "/" — mọi link
// dạng "/?page=jobs" đều route về front-controller hợp lệ.
// -------------------------------------------------------
define('BASE_URL', '/');

// -------------------------------------------------------
// PRODUCTION: KHÔNG hiển thị lỗi ra browser (tránh lộ thông tin).
// Lỗi vẫn được ghi vào error log của hosting.
// -------------------------------------------------------
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Timezone Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bắt buộc PHP output UTF-8 để không lỗi tiếng Việt
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Khởi động session cho toàn bộ app
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
