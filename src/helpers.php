<?php
// ======================================================
// Các hàm tiện ích chung
// ======================================================

// Escape output an toàn cho HTML
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Build url đến 1 page (front controller)
function url(string $page, array $params = []): string
{
    $params = array_merge(['page' => $page], $params);
    return BASE_URL . '?' . http_build_query($params);
}

// Lưu / lấy flash message (hiện 1 lần rồi biến mất)
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// Kiểm tra request POST
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Redirect tiện
function redirect(string $page, array $params = []): void
{
    header('Location: ' . url($page, $params));
    exit;
}
