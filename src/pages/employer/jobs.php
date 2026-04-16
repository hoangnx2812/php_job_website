<?php
// Danh sách bài đăng của employer, có phân trang
$u      = require_role('employer');
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;

// Xử lý toggle is_active
if (is_post() && ($_POST['action'] ?? '') === 'toggle_active') {
    $jid = (int)($_POST['job_id'] ?? 0);
    // Chỉ cho toggle bài của chính mình
    $stmt = db()->prepare('UPDATE jobs SET is_active = 1 - is_active WHERE id = ? AND employer_id = ?');
    $stmt->execute([$jid, $u['id']]);
    flash_set('success', 'Đã cập nhật trạng thái bài đăng.');
    redirect('employer/jobs');
}

// Đếm tổng
$countStmt = db()->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ?');
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

$stmt = db()->prepare("
    SELECT j.*, c.name AS company_name,
      (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute([$u['id']]);
$jobs = $stmt->fetchAll();

$baseUrl = BASE_URL . '?page=employer/jobs';
$pageTitle = 'Bài đăng của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-megaphone me-2 text-primary"></i>Bài đăng của tôi
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
    <a href="<?= e(url('employer/job_form')) ?>" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Đăng bài mới
    </a>
</div>

<?php if (!$jobs): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Bạn chưa có bài đăng nào.
        <a href="<?= e(url('employer/job_form')) ?>" class="alert-link">Đăng bài ngay</a>
    </div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>Vị trí</th>
                <th>Địa điểm</th>
                <th>Lương</th>
                <th>Loại</th>
                <th class="text-center">Đơn</th>
                <th class="text-center">Trạng thái</th>
                <th>Ngày đăng</th>
                <th class="text-center">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $j): ?>
                <tr>
                    <td>
                        <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                           class="fw-600 text-decoration-none"><?= e($j['title']) ?></a>
                    </td>
                    <td class="small text-muted"><?= e($j['location']) ?></td>
                    <td class="small">
                        <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                    </td>
                    <td><span class="badge-type"><?= e(job_type_label($j['job_type'])) ?></span></td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-600">
                            <?= (int)$j['app_count'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($j['is_active']): ?>
                            <span class="badge bg-success">Đang hiện</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Đã ẩn</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($j['created_at'])) ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="<?= e(url('employer/job_form', ['id' => $j['id']])) ?>"
                               class="btn btn-sm btn-warning" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Toggle hiện/ẩn -->
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                <button class="btn btn-sm <?= $j['is_active'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                                        title="<?= $j['is_active'] ? 'Ẩn bài' : 'Hiện bài' ?>">
                                    <i class="bi bi-<?= $j['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                            </form>
                            <a href="<?= e(url('employer/job_delete', ['id' => $j['id']])) ?>"
                               class="btn btn-sm btn-danger" title="Xoá"
                               onclick="return confirm('Xoá bài đăng này?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
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
