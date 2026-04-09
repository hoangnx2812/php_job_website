<?php
// Chi tiết 1 job
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("
    SELECT j.*, c.name AS company_name, c.description AS company_desc, c.website AS company_site
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.id = ?
");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { http_response_code(404); die('Không tìm thấy công việc.'); }

$u = current_user();
$pageTitle = $j['title'];
require __DIR__ . '/../layout/header.php';
?>
<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3><?= e($j['title']) ?></h3>
                <div class="text-muted"><?= e($j['company_name']) ?> • <?= e($j['location']) ?></div>
                <div class="my-3">
                    <span class="badge bg-success fs-6"><?= e($j['salary']) ?></span>
                    <span class="badge bg-secondary fs-6"><?= e($j['job_type']) ?></span>
                </div>
                <h5>Mô tả công việc</h5>
                <p><?= nl2br(e($j['description'])) ?></p>
                <h5>Yêu cầu</h5>
                <p><?= nl2br(e($j['requirements'])) ?></p>

                <?php if ($u && $u['role'] === 'user'): ?>
                    <a href="<?= e(url('user/apply', ['job_id' => $j['id']])) ?>" class="btn btn-primary">Ứng tuyển</a>
                <?php elseif (!$u): ?>
                    <a href="<?= e(url('login')) ?>" class="btn btn-outline-primary">Đăng nhập để ứng tuyển</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><?= e($j['company_name']) ?></h5>
                <p class="small text-muted"><?= e($j['company_desc']) ?></p>
                <?php if ($j['company_site']): ?>
                    <a href="<?= e($j['company_site']) ?>" target="_blank">Website</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php';
