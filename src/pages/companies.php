<?php
// Danh sách công ty
$rows = db()->query("
    SELECT c.*, (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) AS job_count
    FROM companies c ORDER BY c.name
")->fetchAll();

$pageTitle = 'Công ty';
require __DIR__ . '/../layout/header.php';
?>
<h3 class="mb-3">Danh sách công ty</h3>
<div class="row g-3">
<?php foreach ($rows as $c): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5><?= e($c['name']) ?></h5>
                <div class="text-muted"><?= e($c['location']) ?></div>
                <p class="small mt-2"><?= e($c['description']) ?></p>
                <span class="badge bg-info"><?= (int)$c['job_count'] ?> việc làm</span>
                <?php if ($c['website']): ?>
                    • <a href="<?= e($c['website']) ?>" target="_blank">Website</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../layout/footer.php';
