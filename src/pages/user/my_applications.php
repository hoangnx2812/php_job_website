<?php
// Ứng viên xem danh sách đơn ứng tuyển của mình
$u = require_role('user');
$stmt = db()->prepare("
    SELECT a.*, j.title, j.location, c.name AS company_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN companies c ON c.id = j.company_id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll();

$pageTitle = 'Đơn ứng tuyển của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Đơn ứng tuyển của tôi</h3>
<?php if (!$rows): ?>
    <div class="alert alert-info">Bạn chưa có đơn ứng tuyển nào.</div>
<?php else: ?>
<table class="table table-bordered bg-white">
    <thead class="table-light">
        <tr><th>Vị trí</th><th>Công ty</th><th>Địa điểm</th><th>CV</th><th>Trạng thái</th><th>Ngày gửi</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><a href="<?= e(url('job_detail', ['id' => $r['job_id']])) ?>"><?= e($r['title']) ?></a></td>
            <td><?= e($r['company_name']) ?></td>
            <td><?= e($r['location']) ?></td>
            <td><code><?= e($r['cv_file']) ?></code></td>
            <td>
                <?php
                $badge = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'][$r['status']];
                ?>
                <span class="badge bg-<?= $badge ?>"><?= e($r['status']) ?></span>
            </td>
            <td><?= e($r['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
