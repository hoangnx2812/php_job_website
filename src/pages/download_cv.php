<?php
// Download CV - kiểm tra quyền trước khi cho tải file
// admin: tải mọi CV; employer: tải CV ứng tuyển vào job của mình; user: tải CV của mình
$u = current_user();
if (!$u) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

$appId = (int)($_GET['id'] ?? 0);
if (!$appId) {
    http_response_code(400);
    die('Yêu cầu không hợp lệ.');
}

// Lấy thông tin đơn ứng tuyển
$stmt = db()->prepare("
    SELECT a.*, j.employer_id
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    WHERE a.id = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    http_response_code(404);
    die('Không tìm thấy đơn ứng tuyển.');
}

// Kiểm tra quyền download
$canDownload = false;
if ($u['role'] === 'admin') {
    // Admin tải được tất cả
    $canDownload = true;
} elseif ($u['role'] === 'employer' && (int)$app['employer_id'] === (int)$u['id']) {
    // Employer chỉ tải CV ứng tuyển vào job của mình
    $canDownload = true;
} elseif ($u['role'] === 'user' && (int)$app['user_id'] === (int)$u['id']) {
    // User chỉ tải CV của chính mình
    $canDownload = true;
}

if (!$canDownload) {
    http_response_code(403);
    die('Bạn không có quyền tải file này.');
}

// Build đường dẫn tuyệt đối đến file CV
$cvFile   = $app['cv_file'];
$filePath = UPLOAD_DIR . '/' . $cvFile;

// Kiểm tra file tồn tại
if (!file_exists($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    die('File CV không tồn tại hoặc không thể đọc: ' . htmlspecialchars($cvFile));
}

// Xác định MIME type
$ext = strtolower(pathinfo($cvFile, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

// Gửi header và stream file về client
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $cvFile . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Tắt output buffering để stream file được ngay
if (ob_get_level()) ob_end_clean();
readfile($filePath);
exit;
