<?php
// Admin: duyệt yêu cầu trở thành nhà tuyển dụng
require_role('admin');

if (is_post()) {
    $reqId  = (int)($_POST['req_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note   = trim($_POST['admin_note'] ?? '');

    if ($reqId && in_array($action, ['approve', 'reject'], true)) {
        $stmt = db()->prepare('SELECT * FROM employer_requests WHERE id = ? AND status = \'pending\'');
        $stmt->execute([$reqId]);
        $req = $stmt->fetch();

        if ($req) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                if ($action === 'approve') {
                    // Đổi role user thành employer
                    $pdo->prepare('UPDATE users SET role = \'employer\' WHERE id = ?')
                        ->execute([$req['user_id']]);

                    // Tạo công ty từ thông tin trong request
                    $pdo->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)')
                        ->execute([
                            $req['user_id'],
                            $req['company_name'],
                            $req['company_description'],
                            $req['company_location'],
                            $req['company_website'],
                        ]);

                    // Cập nhật trạng thái request
                    $pdo->prepare('UPDATE employer_requests SET status=\'approved\', reviewed_at=NOW() WHERE id=?')
                        ->execute([$reqId]);

                    $pdo->commit();
                    flash_set('success', 'Đã duyệt yêu cầu và tạo tài khoản nhà tuyển dụng.');
                } else {
                    // Từ chối: chỉ cập nhật status + ghi lý do
                    $pdo->prepare('UPDATE employer_requests SET status=\'rejected\', admin_note=?, reviewed_at=NOW() WHERE id=?')
                        ->execute([$note ?: null, $reqId]);
                    $pdo->commit();
                    flash_set('warning', 'Đã từ chối yêu cầu.');
                }
            } catch (Exception $ex) {
                $pdo->rollBack();
                flash_set('danger', 'Có lỗi xảy ra: ' . $ex->getMessage());
            }
        }
    }
    redirect('admin/employer_requests');
}

// Lấy tất cả request kèm thông tin user
$statusFilter = $_GET['status'] ?? 'pending';
$allowedStatus = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($statusFilter, $allowedStatus, true)) $statusFilter = 'pending';

$sql = "
    SELECT r.*, u.full_name, u.email, u.phone
    FROM employer_requests r
    JOIN users u ON u.id = r.user_id
";
if ($statusFilter !== 'all') {
    $sql .= " WHERE r.status = " . db()->quote($statusFilter);
}
$sql .= " ORDER BY r.created_at DESC";
$rows = db()->query($sql)->fetchAll();

// Đếm pending để badge
$pendingCount = (int)db()->query("SELECT COUNT(*) FROM employer_requests WHERE status='pending'")->fetchColumn();

$pageTitle = 'Duyệt nhà tuyển dụng';
require __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Duyệt yêu cầu nhà tuyển dụng</h3>
        <?php if ($pendingCount > 0): ?>
            <span class="badge bg-danger"><?= $pendingCount ?> đang chờ duyệt</span>
        <?php endif; ?>
    </div>
    <!-- Filter tab -->
    <div class="btn-group btn-group-sm">
        <?php foreach (['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'all' => 'Tất cả'] as $s => $label): ?>
            <a href="<?= e(url('admin/employer_requests', ['status' => $s])) ?>"
               class="btn <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$rows): ?>
    <div class="alert alert-info">Không có yêu cầu nào.</div>
<?php else: ?>
<div class="row g-3">
<?php foreach ($rows as $r): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Thông tin user -->
                    <div class="col-md-3">
                        <div class="fw-600"><?= e($r['full_name']) ?></div>
                        <div class="small text-muted"><?= e($r['email']) ?></div>
                        <?php if ($r['phone']): ?>
                            <div class="small text-muted"><i class="bi bi-phone me-1"></i><?= e($r['phone']) ?></div>
                        <?php endif; ?>
                        <div class="small text-muted mt-1">Gửi: <?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></div>
                    </div>

                    <!-- Thông tin công ty -->
                    <div class="col-md-4">
                        <div class="fw-600 text-primary"><i class="bi bi-building me-1"></i><?= e($r['company_name']) ?></div>
                        <?php if ($r['company_location']): ?>
                            <div class="small"><i class="bi bi-geo-alt me-1"></i><?= e($r['company_location']) ?></div>
                        <?php endif; ?>
                        <?php if ($r['company_website']): ?>
                            <div class="small"><a href="<?= e($r['company_website']) ?>" target="_blank"><?= e($r['company_website']) ?></a></div>
                        <?php endif; ?>
                        <?php if ($r['company_description']): ?>
                            <div class="small text-muted mt-1"><?= e(mb_strimwidth($r['company_description'], 0, 100, '...')) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Trạng thái -->
                    <div class="col-md-2 text-center">
                        <?php
                        $badges = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                        $labels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
                        ?>
                        <span class="badge bg-<?= $badges[$r['status']] ?> fs-6">
                            <?= $labels[$r['status']] ?>
                        </span>
                        <?php if ($r['reviewed_at']): ?>
                            <div class="small text-muted mt-1"><?= e(date('d/m/Y', strtotime($r['reviewed_at']))) ?></div>
                        <?php endif; ?>
                        <?php if ($r['status'] === 'rejected' && $r['admin_note']): ?>
                            <div class="small text-danger mt-1">Lý do: <?= e($r['admin_note']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Hành động (chỉ hiện khi pending) -->
                    <div class="col-md-3">
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="post">
                                <input type="hidden" name="req_id" value="<?= $r['id'] ?>">

                                <!-- Nút Duyệt -->
                                <button name="action" value="approve"
                                        class="btn btn-success btn-sm w-100 mb-2"
                                        onclick="return confirm('Duyệt yêu cầu này? User sẽ trở thành employer và công ty sẽ được tạo.')">
                                    <i class="bi bi-check-circle me-1"></i> Duyệt
                                </button>

                                <!-- Form từ chối có nhập lý do -->
                                <div class="input-group input-group-sm">
                                    <input type="text" name="admin_note" class="form-control"
                                           placeholder="Lý do từ chối (tuỳ chọn)">
                                    <button name="action" value="reject" class="btn btn-danger"
                                            onclick="return confirm('Từ chối yêu cầu này?')">
                                        <i class="bi bi-x-circle"></i> Từ chối
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <span class="text-muted small">Đã xử lý</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../layout/footer.php';
