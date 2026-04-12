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

// Hiển thị mức lương từ salary_min và salary_max (đơn vị: triệu VND)
// Trả về chuỗi như "15-25 triệu" hoặc "Thỏa thuận"
function format_salary(?int $min, ?int $max): string
{
    if (!$min && !$max) return 'Thỏa thuận';
    if ($min && $max) return $min . '-' . $max . ' triệu';
    if ($min) return 'Từ ' . $min . ' triệu';
    return 'Đến ' . $max . ' triệu';
}

// Render phân trang Bootstrap
// $total: tổng số bản ghi
// $perPage: số bản ghi mỗi trang
// $currentPage: trang hiện tại (bắt đầu từ 1)
// $baseUrl: URL gốc (đã có sẵn page= và các param khác, chưa có p=)
// Trả về HTML string chứa <nav> pagination
function render_pagination(int $total, int $perPage, int $currentPage, string $baseUrl): string
{
    // Không cần phân trang nếu chỉ có 1 trang
    if ($total <= $perPage) return '';

    $totalPages = (int)ceil($total / $perPage);
    // Đảm bảo currentPage trong khoảng hợp lệ
    $currentPage = max(1, min($currentPage, $totalPages));

    // Hàm nội bộ build URL cho trang cụ thể
    $pageUrl = function(int $p) use ($baseUrl): string {
        // Xóa p= cũ nếu có rồi thêm lại
        $url = preg_replace('/([&?])p=\d+/', '$1', $baseUrl);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $sep . 'p=' . $p;
    };

    $html = '<nav aria-label="Phân trang"><ul class="pagination justify-content-center flex-wrap">';

    // Nút Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($pageUrl($currentPage - 1)) . '">&laquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
    }

    // Hiển thị tối đa 7 trang (3 trước + hiện tại + 3 sau), dùng ellipsis cho phần còn lại
    $range = 2; // số trang hiện trước/sau trang hiện tại
    for ($i = 1; $i <= $totalPages; $i++) {
        $showPage = ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= $range);
        if (!$showPage) {
            // Thêm ellipsis khi nhảy qua
            if ($i === $currentPage - $range - 1 || $i === $currentPage + $range + 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            continue;
        }
        if ($i === $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . e($pageUrl($i)) . '">' . $i . '</a></li>';
        }
    }

    // Nút Next
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . e($pageUrl($currentPage + 1)) . '">&raquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

// Chuyển datetime sang dạng "X ngày trước" / "X giờ trước" / "vừa xong"
function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)        return 'vừa xong';
    if ($diff < 3600)      return (int)($diff / 60) . ' phút trước';
    if ($diff < 86400)     return (int)($diff / 3600) . ' giờ trước';
    if ($diff < 2592000)   return (int)($diff / 86400) . ' ngày trước';
    if ($diff < 31104000)  return (int)($diff / 2592000) . ' tháng trước';
    return date('d/m/Y', strtotime($datetime));
}

// Render badge hạn nộp hồ sơ
// Trả về HTML badge: "Còn X ngày" (xanh/cam/đỏ) hoặc "Hết hạn" (xám) hoặc "" nếu không có deadline
function deadline_badge(?string $expiredAt): string
{
    if (!$expiredAt) return '';
    $diff = (int)ceil((strtotime($expiredAt) - time()) / 86400);
    if ($diff < 0)  return '<span class="badge-deadline expired"><i class="bi bi-clock me-1"></i>Hết hạn</span>';
    if ($diff === 0) return '<span class="badge-deadline urgent"><i class="bi bi-clock me-1"></i>Hết hạn hôm nay</span>';
    if ($diff <= 3) return '<span class="badge-deadline urgent"><i class="bi bi-clock me-1"></i>Còn ' . $diff . ' ngày</span>';
    if ($diff <= 7) return '<span class="badge-deadline warning"><i class="bi bi-clock me-1"></i>Còn ' . $diff . ' ngày</span>';
    return '<span class="badge-deadline normal"><i class="bi bi-clock me-1"></i>Còn ' . $diff . ' ngày</span>';
}

// Format số lượt xem: 1234 → "1.2k", 500 → "500"
function format_views(int $views): string
{
    if ($views >= 1000) return round($views / 1000, 1) . 'k';
    return (string)$views;
}
