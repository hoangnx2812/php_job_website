<?php
// Admin: xem toàn bộ bài đăng, có phân trang
require_role('admin');

$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;

$countStmt = db()->query('SELECT COUNT(*) FROM jobs');
$total     = (int)$countStmt->fetchColumn();

$rows = db()->query("
    SELECT j.*, c.name AS company_name, u.full_name AS employer_name
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    JOIN users u ON u.id = j.employer_id
    ORDER BY j.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
)->fetchAll();

$baseUrl   = BASE_URL . '?page=admin/jobs';
$pageTitle = 'Quản lý bài đăng';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-megaphone me-2 text-primary"></i>Quản lý bài đăng
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Vị trí</th>
                <th>Công ty</th>
                <th>Employer</th>
                <th>Địa điểm</th>
                <th>Lương</th>
                <th>Loại</th>
                <th>Ngày đăng</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $j): ?>
                <tr>
                    <td class="text-muted small"><?= $j['id'] ?></td>
                    <td>
                        <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                           class="fw-500 text-decoration-none small"><?= e($j['title']) ?></a>
                    </td>
                    <td class="small"><?= e($j['company_name']) ?></td>
                    <td class="small text-muted"><?= e($j['employer_name']) ?></td>
                    <td class="small text-muted"><?= e($j['location']) ?></td>
                    <td>
                        <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                    </td>
                    <td><span class="badge-type"><?= e($j['job_type']) ?></span></td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($j['created_at'])) ?></td>
                    <td>
                        <a href="<?= e(url('admin/job_delete', ['id' => $j['id']])) ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Xoá bài đăng này?')">
                            <i class="bi bi-trash"></i>
                        </a>
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
