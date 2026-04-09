<?php
// Admin: xem toàn bộ CV / đơn ứng tuyển trong hệ thống, có phân trang
require_role('admin');

if (is_post() && ($_POST['action'] ?? '') === 'delete') {
    db()->prepare('DELETE FROM applications WHERE id = ?')->execute([(int)$_POST['id']]);
    flash_set('success', 'Đã xoá đơn.');
    redirect('admin/applications');
}

$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;

$countStmt = db()->query('SELECT COUNT(*) FROM applications');
$total     = (int)$countStmt->fetchColumn();

$rows = db()->query("
    SELECT a.*, j.title AS job_title, u.full_name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
)->fetchAll();

$baseUrl   = BASE_URL . '?page=admin/applications';
$pageTitle = 'Quản lý CV';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-file-earmark-person me-2 text-primary"></i>Quản lý CV / Đơn ứng tuyển
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Ứng viên</th>
                <th>Vị trí ứng tuyển</th>
                <th>CV</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $a): ?>
                <?php
                $badgeCls    = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$a['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$a['status']] ?? $a['status'];
                ?>
                <tr>
                    <td class="text-muted small"><?= $a['id'] ?></td>
                    <td>
                        <div class="fw-500 small"><?= e($a['applicant_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem"><?= e($a['applicant_email']) ?></div>
                    </td>
                    <td class="small fw-500"><?= e($a['job_title']) ?></td>
                    <td>
                        <a href="<?= e(url('download_cv', ['id' => $a['id']])) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Tải CV
                        </a>
                    </td>
                    <td><span class="<?= $badgeCls ?>"><?= $statusLabel ?></span></td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Xoá đơn này?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
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
<?php require __DIR__ . '/../../layout/footer.php';
