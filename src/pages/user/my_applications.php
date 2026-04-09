<?php
// Ứng viên xem danh sách đơn ứng tuyển của mình, có nút tải CV
$u      = require_role('user');
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;

$countStmt = db()->prepare('SELECT COUNT(*) FROM applications WHERE user_id = ?');
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

$stmt = db()->prepare("
    SELECT a.*, j.title, j.location, c.name AS company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll();

$baseUrl   = BASE_URL . '?page=user/my_applications';
$pageTitle = 'Đơn ứng tuyển của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Đơn ứng tuyển của tôi
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
    <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-search me-1"></i>Tìm việc thêm
    </a>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Bạn chưa có đơn ứng tuyển nào.
        <a href="<?= e(url('jobs')) ?>" class="alert-link">Xem danh sách việc làm</a>
    </div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>Vị trí</th>
                <th>Công ty</th>
                <th>Địa điểm</th>
                <th>CV</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php
                $badgeCls    = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$r['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$r['status']] ?? $r['status'];
                ?>
                <tr>
                    <td>
                        <a href="<?= e(url('job_detail', ['id' => $r['job_id']])) ?>"
                           class="fw-500 text-decoration-none small"><?= e($r['title']) ?></a>
                    </td>
                    <td class="small"><?= e($r['company_name']) ?></td>
                    <td class="small text-muted"><?= e($r['location']) ?></td>
                    <td>
                        <a href="<?= e(url('download_cv', ['id' => $r['id']])) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Tải CV
                        </a>
                    </td>
                    <td><span class="<?= $badgeCls ?>"><?= $statusLabel ?></span></td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    <?= render_pagination($total, $perPage, $page, $baseUrl) ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
