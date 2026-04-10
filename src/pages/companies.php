<?php
// Danh sách công ty với logo, phân trang
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 9;
$q       = trim($_GET['q'] ?? '');

$sql    = "SELECT c.*, (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.is_active = 1) AS job_count
           FROM companies c WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= " AND (c.name LIKE ? OR c.location LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Đếm tổng để phân trang
$countStmt = db()->prepare(str_replace('SELECT c.*, (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.is_active = 1) AS job_count', 'SELECT COUNT(*)', $sql));
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$sql .= " ORDER BY c.name LIMIT $perPage OFFSET " . (($page - 1) * $perPage);
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$baseUrl = BASE_URL . '?' . http_build_query(array_filter(['page' => 'companies', 'q' => $q]));

$pageTitle = 'Công ty';
require __DIR__ . '/../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0"><i class="bi bi-building me-2 text-primary"></i>Danh sách công ty</h4>
    <span class="text-muted small"><?= $total ?> công ty</span>
</div>

<!-- Tìm kiếm công ty -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="get" class="row g-2">
            <input type="hidden" name="page" value="companies">
            <div class="col-md-9">
                <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Tên công ty, địa điểm...">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Tìm</button>
                <?php if ($q): ?>
                    <a href="<?= e(url('companies')) ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Không tìm thấy công ty.</div>
<?php endif; ?>

<div class="row g-3 mb-4">
<?php foreach ($rows as $c): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card company-card h-100 border-0">
            <div class="card-body p-3">
                <div class="d-flex gap-3 align-items-start">
                    <?php if ($c['logo']): ?>
                        <img src="/uploads/logos/<?= e($c['logo']) ?>"
                             alt="<?= e($c['name']) ?>" class="company-logo flex-shrink-0">
                    <?php else: ?>
                        <div class="company-logo-placeholder flex-shrink-0">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <h6 class="fw-600 mb-1">
                            <a href="<?= e(url('company_detail', ['id' => $c['id']])) ?>"
                               class="text-decoration-none text-dark stretched-link">
                                <?= e($c['name']) ?>
                            </a>
                        </h6>
                        <?php if ($c['location']): ?>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-geo-alt me-1"></i><?= e($c['location']) ?>
                            </div>
                        <?php endif; ?>
                        <p class="small text-secondary mb-2" style="line-height:1.4">
                            <?= e(mb_strimwidth($c['description'] ?? '', 0, 100, '...')) ?>
                        </p>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.78rem">
                                <i class="bi bi-briefcase me-1"></i><?= (int)$c['job_count'] ?> việc làm
                            </span>
                            <?php if ($c['website']): ?>
                                <a href="<?= e($c['website']) ?>" target="_blank"
                                   class="small text-muted text-decoration-none">
                                    <i class="bi bi-globe"></i> Website
                                </a>
                            <?php endif; ?>
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

<?php require __DIR__ . '/../layout/footer.php';
