<?php
// ======================================================
// Các hàm xử lý đăng nhập / phân quyền
// ======================================================
require_once __DIR__ . '/db.php';

// Lấy user hiện tại từ session, trả về null nếu chưa đăng nhập
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

// Kiểm tra role, nếu không đúng thì redirect về home
function require_role(string ...$roles): array
{
    $u = current_user();
    if (!$u) {
        header('Location: ' . BASE_URL . '?page=login');
        exit;
    }
    if ($roles && !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
    return $u;
}

// Kiểm tra mật khẩu. Hỗ trợ cả password_hash thật lẫn seed "PLAIN:xxx".
// Khi user seed login lần đầu, password sẽ được tự động hash lại trong DB.
function verify_and_upgrade_password(array $user, string $plain): bool
{
    $stored = $user['password'];
    if (strpos($stored, 'PLAIN:') === 0) {
        // Seed dạng plain text
        if (substr($stored, 6) === $plain) {
            $newHash = password_hash($plain, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$newHash, $user['id']]);
            return true;
        }
        return false;
    }
    return password_verify($plain, $stored);
}

// Sau khi login thành công, redirect theo role
function redirect_by_role(string $role): void
{
    switch ($role) {
        case 'admin':
            header('Location: ' . BASE_URL . '?page=admin/dashboard');
            break;
        case 'employer':
            header('Location: ' . BASE_URL . '?page=employer/dashboard');
            break;
        default:
            header('Location: ' . BASE_URL . '?page=jobs');
    }
    exit;
}
