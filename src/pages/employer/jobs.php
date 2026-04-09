<?php
// Danh sách bài đăng thuộc về employer hiện tại
$u = require_role('employer');
$stmt = db()->prepare("
    SELECT j.*, c.name AS company_name,
      (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.employer_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$u['id']]);
$jobs = $stmt->fetchAll();

$pageTitle = 'Bài đăng của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Bài đăng của tôi</h3>
    <a href="<?= e(url('employer/job_form')) ?>" class="btn btn-success">+ Đăng bài mới</a>
</div>
<?php if (!$jobs): ?>
    <div class="alert alert-info">Bạn chưa có bài đăng nào.</div>
<?php else: ?>
<table class="table table-bordered bg-white">
    <thead class="table-light">
    <tr><th>Vị trí</th><th>Công ty</th><th>Địa điểm</th><th>Mức lương</th><th>Đơn ứng tuyển</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($jobs as $j): ?>
        <tr>
            <td><a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"><?= e($j['title']) ?></a></td>
            <td><?= e($j['company_name']) ?></td>
            <td><?= e($j['location']) ?></td>
            <td><?= e($j['salary']) ?></td>
            <td><?= (int)$j['app_count'] ?></td>
            <td>
                <a href="<?= e(url('employer/job_form', ['id' => $j['id']])) ?>" class="btn btn-sm btn-warning">Sửa</a>
                <a href="<?= e(url('employer/job_delete', ['id' => $j['id']])) ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Xoá bài đăng?')">Xoá</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
