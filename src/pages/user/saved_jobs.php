<?php
// Danh sách job đã lưu của user, có nút bỏ lưu
$u      = require_role('user');
$page   = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;

// Toggle save/unsave qua POST
if (is_post() && isset($_POST['job_id'])) {
    $jobId = (int)$_POST['job_id'];
    $stmt  = db()->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->execute([$u['id'], $jobId]);
    if ($stmt->fetch()) {
        db()->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$u['id'], $jobId]);
        flash_set('success', 'Đã bỏ lưu job.');
    } else {
        db()->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?,?)')->execute([$u['id'], $jobId]);
        flash_set('success', 'Đã lưu job.');
    }
    redirect('user/saved_jobs');
}

// Đếm tổng
$countStmt = db()->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?');
$countStmt->execute([$u['id']]);
$total = (int)$countStmt->fetchColumn();

// Lấy danh sách job đã lưu
$stmt = db()->prepare("
    SELECT j.*, c.name AS company_name, c.logo AS company_logo, s.created_at AS saved_at
    FROM saved_jobs s
    JOIN jobs j ON j.id = s.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll();

$baseUrl   = BASE_URL . '?page=user/saved_jobs';
$pageTitle = 'Job đã lưu';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-heart-fill text-danger me-2"></i>Job đã lưu
        <span class="badge bg-danger ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
    <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-search me-1"></i>Tìm thêm job
    </a>
</div>

<?php if (!$rows): ?>
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-heart text-muted" style="font-size:3rem;opacity:0.3"></i>
        <p class="text-muted mt-3 mb-3">Bạn chưa lưu job nào.</p>
        <a href="<?= e(url('jobs')) ?>" class="btn btn-primary">
            <i class="bi bi-search me-1"></i>Khám phá việc làm
        </a>
    </div>
<?php else: ?>
<div class="row g-3 mb-4">
<?php foreach ($rows as $j): ?>
    <div class="col-md-6">
        <div class="card job-card h-100 border-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <!-- Logo -->
                    <?php if ($j['company_logo']): ?>
                        <img src="/uploads/logos/<?= e($j['company_logo']) ?>"
                             alt="<?= e($j['company_name']) ?>"
                             class="company-logo flex-shrink-0">
                    <?php else: ?>
                        <div class="company-logo-placeholder flex-shrink-0">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-600 mb-1 me-2">
                                <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                                   class="text-decoration-none text-dark">
                                    <?= e($j['title']) ?>
                                </a>
                            </h6>
                            <!-- Nút bỏ lưu -->
                            <form method="post" class="flex-shrink-0">
                                <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                <button class="btn-save-job saved" title="Bỏ lưu">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </form>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-building me-1"></i><?= e($j['company_name']) ?>
                            <span class="mx-1">•</span>
                            <i class="bi bi-geo-alt me-1"></i><?= e($j['location']) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                            <span class="badge-type"><?= e(job_type_label($j['job_type'])) ?></span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem">
                            <i class="bi bi-bookmark me-1"></i>Đã lưu <?= date('d/m/Y', strtotime($j['saved_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- Phân trang -->
<?= render_pagination($total, $perPage, $page, $baseUrl) ?>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
