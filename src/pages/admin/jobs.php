<?php
// Admin: xem toàn bộ bài đăng
require_role('admin');
$rows = db()->query("
    SELECT j.*, c.name AS company_name, u.full_name AS employer_name
    FROM jobs j
    JOIN companies c ON c.id = j.company_id
    JOIN users u ON u.id = j.employer_id
    ORDER BY j.created_at DESC
")->fetchAll();

$pageTitle = 'Quản lý bài đăng';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Quản lý bài đăng</h3>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>ID</th><th>Vị trí</th><th>Công ty</th><th>Employer</th><th>Địa điểm</th><th>Ngày</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $j): ?>
        <tr>
            <td><?= $j['id'] ?></td>
            <td><a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"><?= e($j['title']) ?></a></td>
            <td><?= e($j['company_name']) ?></td>
            <td><?= e($j['employer_name']) ?></td>
            <td><?= e($j['location']) ?></td>
            <td><?= e($j['created_at']) ?></td>
            <td><a href="<?= e(url('admin/job_delete', ['id' => $j['id']])) ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Xoá bài đăng?')">Xoá</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../layout/footer.php';
