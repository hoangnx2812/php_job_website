<?php
// Trang dashboard của nhà tuyển dụng: vài con số tóm tắt
$u = require_role('employer');

$jobCount = db()->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ?');
$jobCount->execute([$u['id']]);
$jobCount = (int)$jobCount->fetchColumn();

$appCount = db()->prepare('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?');
$appCount->execute([$u['id']]);
$appCount = (int)$appCount->fetchColumn();

$pageTitle = 'Bảng điều khiển';
require __DIR__ . '/../../layout/header.php';
?>
<h3>Xin chào, <?= e($u['full_name']) ?></h3>
<div class="row g-3 mt-2">
    <div class="col-md-4">
        <div class="card text-bg-primary shadow-sm"><div class="card-body">
            <div>Số bài đăng</div><h2><?= $jobCount ?></h2>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success shadow-sm"><div class="card-body">
            <div>Đơn ứng tuyển</div><h2><?= $appCount ?></h2>
        </div></div>
    </div>
</div>
<div class="mt-4">
    <a href="<?= e(url('employer/jobs')) ?>" class="btn btn-primary">Bài đăng của tôi</a>
    <a href="<?= e(url('employer/job_form')) ?>" class="btn btn-success">+ Đăng bài mới</a>
    <a href="<?= e(url('employer/applications')) ?>" class="btn btn-outline-primary">Đơn ứng tuyển</a>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
