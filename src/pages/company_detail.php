<?php
// Trang chi tiết công ty: thông tin + danh sách job đang tuyển
$companyId = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT c.*, u.full_name AS owner_name
    FROM companies c JOIN users u ON u.id = c.owner_id
    WHERE c.id = ?
");
$stmt->execute([$companyId]);
$c = $stmt->fetch();
if (!$c) { http_response_code(404); die('Không tìm thấy công ty.'); }

// Lấy tất cả job đang hoạt động của công ty
$stmt = db()->prepare("
    SELECT * FROM jobs WHERE company_id = ? AND is_active = 1 ORDER BY created_at DESC
");
$stmt->execute([$companyId]);
$jobs = $stmt->fetchAll();

$u = current_user();

// Xử lý toggle lưu job (nếu user role=user)
if ($u && $u['role'] === 'user' && is_post() && isset($_POST['job_id'])) {
    $saveJobId = (int)$_POST['job_id'];
    $chk = db()->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $chk->execute([$u['id'], $saveJobId]);
    if ($chk->fetch()) {
        db()->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$u['id'], $saveJobId]);
    } else {
        db()->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?,?)')->execute([$u['id'], $saveJobId]);
    }
    redirect('company_detail', ['id' => $companyId]);
}

// Lấy saved job ids để hiển thị trạng thái nút tim
$savedJobIds = [];
if ($u && $u['role'] === 'user') {
    $sStmt = db()->prepare('SELECT job_id FROM saved_jobs WHERE user_id = ?');
    $sStmt->execute([$u['id']]);
    $savedJobIds = array_column($sStmt->fetchAll(), 'job_id');
}

$pageTitle = e($c['name']);
require __DIR__ . '/../layout/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= e(url('companies')) ?>">Công ty</a></li>
        <li class="breadcrumb-item active"><?= e($c['name']) ?></li>
    </ol>
</nav>

<div class="row g-4">
    <!-- Cột trái: thông tin công ty -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top:20px">
            <div class="card-body p-4 text-center">
                <!-- Logo -->
                <?php if ($c['logo']): ?>
                    <img src="/uploads/logos/<?= e($c['logo']) ?>"
                         alt="<?= e($c['name']) ?>"
                         style="width:88px;height:88px;object-fit:contain;border-radius:16px;border:1px solid #e2e8f0;padding:8px;background:#fff;margin-bottom:1rem">
                <?php else: ?>
                    <div style="width:88px;height:88px;border-radius:16px;border:1px solid #e2e8f0;
                                background:#f1f5f9;display:flex;align-items:center;justify-content:center;
                                color:#94a3b8;font-size:2.5rem;margin:0 auto 1rem">
                        <i class="bi bi-building"></i>
                    </div>
                <?php endif; ?>

                <h4 class="fw-700 mb-1"><?= e($c['name']) ?></h4>

                <?php if ($c['location']): ?>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-geo-alt me-1"></i><?= e($c['location']) ?>
                    </div>
                <?php endif; ?>

                <span class="badge bg-primary bg-opacity-10 text-primary mb-3" style="font-size:0.82rem">
                    <i class="bi bi-briefcase me-1"></i><?= count($jobs) ?> việc làm đang tuyển
                </span>

                <?php if ($c['description']): ?>
                    <p class="text-secondary small text-start" style="line-height:1.65">
                        <?= nl2br(e($c['description'])) ?>
                    </p>
                <?php endif; ?>

                <?php if ($c['website']): ?>
                    <a href="<?= e($c['website']) ?>" target="_blank"
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-globe me-1"></i> Xem website
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cột phải: danh sách job -->
    <div class="col-md-8">
        <h5 class="fw-700 mb-3">
            <i class="bi bi-megaphone text-primary me-2"></i>Vị trí đang tuyển dụng
        </h5>

        <?php if (!$jobs): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Công ty hiện chưa có vị trí tuyển dụng nào.
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
        <?php foreach ($jobs as $j): ?>
            <?php $isSaved = in_array($j['id'], $savedJobIds); ?>
            <div class="card job-card border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-600 mb-1">
                                <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                                   class="text-decoration-none text-dark">
                                    <?= e($j['title']) ?>
                                </a>
                            </h6>
                            <div class="text-muted small mb-2">
                                <?php if ($j['location']): ?>
                                    <i class="bi bi-geo-alt me-1"></i><?= e($j['location']) ?>
                                    <span class="mx-2">•</span>
                                <?php endif; ?>
                                <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($j['created_at'])) ?>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                                <span class="badge-type"><?= e(job_type_label($j['job_type'])) ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 ms-3">
                            <!-- Nút lưu -->
                            <?php if ($u && $u['role'] === 'user'): ?>
                                <form method="post" class="flex-shrink-0">
                                    <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                    <button class="btn-save-job <?= $isSaved ? 'saved' : '' ?>"
                                            title="<?= $isSaved ? 'Bỏ lưu' : 'Lưu job' ?>">
                                        <i class="bi bi-heart<?= $isSaved ? '-fill' : '' ?>"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                               class="btn btn-primary btn-sm align-self-center">
                                Xem →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php';
