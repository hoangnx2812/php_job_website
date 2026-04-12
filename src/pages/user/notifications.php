<?php
// Trang thông báo in-app của ứng viên
// Hiển thị danh sách notifications, mark as read khi vào trang
$u       = require_role('user');
$perPage = 15;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Mark tất cả thông báo chưa đọc của user thành đã đọc khi vào trang này
db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
    ->execute([$u['id']]);

// Đếm tổng thông báo của user
$countStmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

// Lấy danh sách thông báo, mới nhất trước
$stmt = db()->prepare("
    SELECT * FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute([$u['id']]);
$notifications = $stmt->fetchAll();

$baseUrl   = BASE_URL . '?page=user/notifications';
$pageTitle = 'Thông báo';
require __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-bell-fill me-2 text-primary"></i>Thông báo
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<?php if (!$notifications): ?>
    <!-- Empty state khi chưa có thông báo nào -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size:3rem"></i>
            <div class="mt-3 fw-600 text-muted">Bạn chưa có thông báo nào</div>
            <div class="small text-muted mt-1">Thông báo sẽ xuất hiện khi có đơn ứng tuyển mới hoặc cập nhật trạng thái</div>
        </div>
    </div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="list-group list-group-flush rounded-3">
        <?php foreach ($notifications as $n): ?>
            <?php
            // Chọn icon và màu theo type thông báo
            $iconMap = [
                'new_application' => ['icon' => 'bi-file-earmark-person-fill', 'color' => '#1a56db', 'bg' => '#eff6ff'],
                'status_changed'  => ['icon' => 'bi-arrow-repeat',             'color' => '#16a34a', 'bg' => '#f0fdf4'],
            ];
            $style = $iconMap[$n['type']] ?? ['icon' => 'bi-bell-fill', 'color' => '#64748b', 'bg' => '#f8fafc'];
            // Nền khác nhau cho unread vs read
            $rowBg = $n['is_read'] ? '' : 'background:#fafbff;';
            ?>
            <div class="list-group-item border-0 border-bottom py-3 px-4"
                 style="<?= $rowBg ?>cursor:default">
                <div class="d-flex gap-3 align-items-start">
                    <!-- Icon vòng tròn theo type -->
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:<?= $style['bg'] ?>;color:<?= $style['color'] ?>">
                        <i class="bi <?= $style['icon'] ?>" style="font-size:1.1rem"></i>
                    </div>

                    <!-- Nội dung thông báo -->
                    <div class="flex-grow-1 min-w-0">
                        <?php if ($n['link']): ?>
                            <!-- Click vào notification → chuyển đến link liên quan -->
                            <a href="<?= e($n['link']) ?>"
                               class="text-decoration-none text-dark fw-500 small d-block mb-1">
                                <?= e($n['message']) ?>
                            </a>
                        <?php else: ?>
                            <div class="fw-500 small mb-1" style="color:var(--text-main)"><?= e($n['message']) ?></div>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size:0.75rem">
                            <i class="bi bi-clock me-1"></i><?= time_ago($n['created_at']) ?>
                        </div>
                    </div>

                    <!-- Dấu chấm xanh nếu chưa đọc (nhưng đã mark read khi vào trang → luôn read) -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="mt-3">
    <?= render_pagination($total, $perPage, $page, $baseUrl) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../layout/footer.php';
