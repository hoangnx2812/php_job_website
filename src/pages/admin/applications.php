<?php
// Admin: xem toàn bộ CV / đơn ứng tuyển trong hệ thống
require_role('admin');

if (is_post() && ($_POST['action'] ?? '') === 'delete') {
    $stmt = db()->prepare('DELETE FROM applications WHERE id = ?');
    $stmt->execute([(int)$_POST['id']]);
    flash_set('success', 'Đã xoá đơn.');
    redirect('admin/applications');
}

$rows = db()->query("
    SELECT a.*, j.title AS job_title, u.full_name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
")->fetchAll();

$pageTitle = 'Quản lý CV';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Quản lý CV / đơn ứng tuyển</h3>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>ID</th><th>Ứng viên</th><th>Vị trí</th><th>CV</th><th>Trạng thái</th><th>Ngày</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= e($a['applicant_name']) ?><br><small class="text-muted"><?= e($a['applicant_email']) ?></small></td>
            <td><?= e($a['job_title']) ?></td>
            <td><code><?= e($a['cv_file']) ?></code></td>
            <td><?= e($a['status']) ?></td>
            <td><?= e($a['created_at']) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Xoá?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button class="btn btn-sm btn-danger">Xoá</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../layout/footer.php';
