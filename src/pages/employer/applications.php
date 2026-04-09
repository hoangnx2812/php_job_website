<?php
// Employer xem và duyệt đơn ứng tuyển vào các bài của mình, có phân trang
$u      = require_role('employer');
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;

// Xử lý cập nhật trạng thái đơn
if (is_post()) {
    $appId    = (int)($_POST['app_id'] ?? 0);
    $action   = $_POST['action'] ?? '';
    $newStatus = in_array($action, ['accepted', 'rejected', 'pending'], true) ? $action : null;
    if ($appId && $newStatus) {
        // Chỉ cập nhật đơn thuộc bài của chính employer này
        $stmt = db()->prepare("
            UPDATE applications a
            JOIN jobs j ON j.id = a.job_id
            SET a.status = ?
            WHERE a.id = ? AND j.employer_id = ?
        ");
        $stmt->execute([$newStatus, $appId, $u['id']]);
        flash_set('success', 'Đã cập nhật trạng thái đơn.');
        redirect('employer/applications');
    }
}

// Đếm tổng
$countStmt = db()->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN jobs j ON j.id = a.job_id
    WHERE j.employer_id = ?
");
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

$stmt = db()->prepare("
    SELECT a.*, j.title AS job_title, u.full_name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.employer_id = ?
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute([$u['id']]);
$apps = $stmt->fetchAll();

$baseUrl  = BASE_URL . '?page=employer/applications';
$pageTitle = 'Đơn ứng tuyển';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-file-earmark-person me-2 text-primary"></i>Đơn ứng tuyển
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<?php if (!$apps): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Chưa có đơn ứng tuyển nào.</div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>Ứng viên</th>
                <th>Vị trí ứng tuyển</th>
                <th>CV</th>
                <th>Thư xin việc</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th class="text-center">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($apps as $a): ?>
                <?php
                $badgeCls = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$a['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$a['status']] ?? $a['status'];
                ?>
                <tr>
                    <td>
                        <div class="fw-600 small"><?= e($a['applicant_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem"><?= e($a['applicant_email']) ?></div>
                    </td>
                    <td class="small fw-500"><?= e($a['job_title']) ?></td>
                    <td>
                        <a href="<?= e(url('download_cv', ['id' => $a['id']])) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Tải CV
                        </a>
                    </td>
                    <td class="small text-secondary">
                        <?= e(mb_strimwidth($a['cover_letter'] ?? '', 0, 80, '...')) ?>
                    </td>
                    <td><span class="<?= $badgeCls ?>"><?= $statusLabel ?></span></td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <form method="post" class="d-flex gap-1 justify-content-center">
                            <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                            <button name="action" value="accepted" class="btn btn-sm btn-success"
                                    title="Nhận đơn">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button name="action" value="rejected" class="btn btn-sm btn-danger"
                                    title="Từ chối">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button name="action" value="pending" class="btn btn-sm btn-secondary"
                                    title="Đặt lại chờ duyệt">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    </td>
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
