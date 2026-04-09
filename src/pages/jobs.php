<?php
// Danh sách việc làm + tìm kiếm theo tên và địa điểm
$q        = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');

$sql = "SELECT j.*, c.name AS company_name
        FROM jobs j JOIN companies c ON c.id = j.company_id
        WHERE j.is_active = 1";
$params = [];
if ($q !== '') {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($location !== '') {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}
$sql .= " ORDER BY j.created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$pageTitle = 'Việc làm';
require __DIR__ . '/../layout/header.php';
?>
<h3>Danh sách việc làm</h3>
<form method="get" class="row g-2 mb-4">
    <input type="hidden" name="page" value="jobs">
    <div class="col-md-5"><input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Từ khoá..."></div>
    <div class="col-md-4"><input name="location" value="<?= e($location) ?>" class="form-control" placeholder="Địa điểm"></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Tìm kiếm</button></div>
</form>

<?php if (!$jobs): ?>
    <div class="alert alert-info">Không tìm thấy việc làm phù hợp.</div>
<?php endif; ?>

<div class="row g-3">
<?php foreach ($jobs as $j): ?>
    <div class="col-md-6">
        <div class="card job-card shadow-sm h-100">
            <div class="card-body">
                <h5><a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"><?= e($j['title']) ?></a></h5>
                <div class="text-muted"><?= e($j['company_name']) ?> • <?= e($j['location']) ?></div>
                <p class="mt-2 mb-2 small text-truncate"><?= e($j['description']) ?></p>
                <span class="badge bg-success"><?= e($j['salary']) ?></span>
                <span class="badge bg-secondary"><?= e($j['job_type']) ?></span>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php';
