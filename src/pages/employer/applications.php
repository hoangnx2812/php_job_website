<?php
// Employer xem và duyệt đơn ứng tuyển vào các bài của mình
$u = require_role('employer');

// Xử lý cập nhật trạng thái
if (is_post()) {
    $appId = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $newStatus = in_array($action, ['accepted', 'rejected', 'pending'], true) ? $action : null;
    if ($appId && $newStatus) {
        // Chỉ cho cập nhật đơn thuộc bài của chính mình
        $stmt = db()->prepare("
            UPDATE applications a
            JOIN jobs j ON j.id = a.job_id
            SET a.status = ?
            WHERE a.id = ? AND j.employer_id = ?
        ");
        $stmt->execute([$newStatus, $appId, $u['id']]);
        flash_set('success', 'Đã cập nhật trạng thái đơn ứng tuyển.');
        redirect('employer/applications');
    }
}

$stmt = db()->prepare("
    SELECT a.*, j.title AS job_title, u.full_name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.employer_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$u['id']]);
$apps = $stmt->fetchAll();

$pageTitle = 'Đơn ứng tuyển';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Đơn ứng tuyển vào bài của tôi</h3>
<?php if (!$apps): ?>
    <div class="alert alert-info">Chưa có đơn ứng tuyển nào.</div>
<?php else: ?>
<table class="table table-bordered bg-white">
    <thead class="table-light">
    <tr><th>Ứng viên</th><th>Email</th><th>Vị trí</th><th>CV</th><th>Thư</th><th>Trạng thái</th><th>Ngày gửi</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($apps as $a): ?>
        <tr>
            <td><?= e($a['applicant_name']) ?></td>
            <td><?= e($a['applicant_email']) ?></td>
            <td><?= e($a['job_title']) ?></td>
            <td><code><?= e($a['cv_file']) ?></code></td>
            <td class="small"><?= e(mb_strimwidth($a['cover_letter'] ?? '', 0, 60, '...')) ?></td>
            <td>
                <?php $badge = ['pending'=>'warning','accepted'=>'success','rejected'=>'danger'][$a['status']]; ?>
                <span class="badge bg-<?= $badge ?>"><?= e($a['status']) ?></span>
            </td>
            <td><?= e($a['created_at']) ?></td>
            <td>
                <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                    <button name="action" value="accepted" class="btn btn-sm btn-success">Accept</button>
                    <button name="action" value="rejected" class="btn btn-sm btn-danger">Reject</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
