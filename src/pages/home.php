<?php
// Trang chủ: hero + vài job mới nhất
$latest = db()->query("
    SELECT j.*, c.name AS company_name
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.is_active = 1
    ORDER BY j.created_at DESC
    LIMIT 6
")->fetchAll();

$pageTitle = 'Trang chủ';
require __DIR__ . '/../layout/header.php';
?>
<div class="p-5 mb-4 bg-white rounded shadow-sm text-center">
    <h1 class="display-6">Tìm công việc mơ ước của bạn</h1>
    <p class="text-muted">Hàng ngàn việc làm IT, marketing, kinh doanh... đang chờ bạn.</p>
    <form action="<?= e(BASE_URL) ?>" method="get" class="row g-2 justify-content-center mt-3">
        <input type="hidden" name="page" value="jobs">
        <div class="col-md-5"><input name="q" class="form-control" placeholder="Tên công việc, kỹ năng..."></div>
        <div class="col-md-3"><input name="location" class="form-control" placeholder="Địa điểm"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Tìm kiếm</button></div>
    </form>
</div>

<h4 class="mb-3">Việc làm mới nhất</h4>
<div class="row g-3">
<?php foreach ($latest as $j): ?>
    <div class="col-md-6">
        <div class="card job-card shadow-sm h-100">
            <div class="card-body">
                <h5><a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"><?= e($j['title']) ?></a></h5>
                <div class="text-muted"><?= e($j['company_name']) ?> • <?= e($j['location']) ?></div>
                <div class="mt-2"><span class="badge bg-success"><?= e($j['salary']) ?></span>
                    <span class="badge bg-secondary"><?= e($j['job_type']) ?></span></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php';
