<?php
// Employer xem và duyệt đơn ứng tuyển vào các bài của mình, có phân trang + lọc theo job
$u       = require_role('employer');
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;
$filterJobId = (int)($_GET['job_id'] ?? 0);

// Xử lý cập nhật trạng thái đơn
if (is_post()) {
    $appId    = (int)($_POST['app_id'] ?? 0);
    $action   = $_POST['action'] ?? '';
    $newStatus = in_array($action, ['accepted', 'rejected', 'pending'], true) ? $action : null;
    if ($appId && $newStatus) {
        // Lấy thông tin đơn để gửi thông báo cho ứng viên
        $appInfoStmt = db()->prepare("
            SELECT a.user_id, j.title AS job_title, c.name AS company_name
            FROM applications a
            JOIN jobs j ON j.id = a.job_id
            JOIN companies c ON c.id = j.company_id
            WHERE a.id = ? AND j.employer_id = ?
        ");
        $appInfoStmt->execute([$appId, $u['id']]);
        $appInfo = $appInfoStmt->fetch();

        // Chỉ cập nhật đơn thuộc bài của chính employer này
        $stmt = db()->prepare("
            UPDATE applications a
            JOIN jobs j ON j.id = a.job_id
            SET a.status = ?
            WHERE a.id = ? AND j.employer_id = ?
        ");
        $stmt->execute([$newStatus, $appId, $u['id']]);

        // Thông báo cho ứng viên: trạng thái đơn đã được cập nhật
        if ($appInfo) {
            $statusLabel = ['pending' => 'Chờ xét', 'accepted' => 'Được chấp nhận', 'rejected' => 'Từ chối'];
            notify(
                (int)$appInfo['user_id'],
                'status_changed',
                "Đơn ứng tuyển «{$appInfo['job_title']}» tại {$appInfo['company_name']} đã được cập nhật: {$statusLabel[$newStatus]}",
                url('user/my_applications')
            );
        }

        flash_set('success', 'Đã cập nhật trạng thái đơn.');
        // Giữ nguyên filter khi redirect
        $redirectParams = $filterJobId ? ['job_id' => $filterJobId] : [];
        redirect('employer/applications', $redirectParams);
    }
}

// Lấy danh sách job của employer để dùng cho dropdown filter
$jobListStmt = db()->prepare("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$jobListStmt->execute([$u['id']]);
$jobList = $jobListStmt->fetchAll();

// Build điều kiện WHERE dựa trên filter job
$whereExtra = '';
$whereParams = [$u['id']];
if ($filterJobId > 0) {
    $whereExtra = ' AND a.job_id = ?';
    $whereParams[] = $filterJobId;
}

// Đếm tổng
$countStmt = db()->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN jobs j ON j.id = a.job_id
    WHERE j.employer_id = ?$whereExtra
");
$countStmt->execute($whereParams);
$total = (int)$countStmt->fetchColumn();

// Lấy dữ liệu trang hiện tại
$stmt = db()->prepare("
    SELECT a.*, j.title AS job_title, u.full_name AS applicant_name, u.email AS applicant_email
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.employer_id = ?$whereExtra
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
);
$stmt->execute($whereParams);
$apps = $stmt->fetchAll();

// Build base URL cho phân trang (giữ filter job_id nếu có)
$baseUrl = BASE_URL . '?page=employer/applications' . ($filterJobId ? '&job_id=' . $filterJobId : '');

$pageTitle = 'Đơn ứng tuyển';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-file-earmark-person me-2 text-primary"></i>Đơn ứng tuyển
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<!-- Filter theo job -->
<?php if ($jobList): ?>
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body p-3">
        <form method="get" class="row g-2 align-items-center">
            <input type="hidden" name="page" value="employer/applications">
            <div class="col-auto">
                <label class="col-form-label small fw-600">Lọc theo bài đăng:</label>
            </div>
            <div class="col-md-5">
                <select name="job_id" class="form-select">
                    <option value="">-- Tất cả bài đăng --</option>
                    <?php foreach ($jobList as $job): ?>
                        <option value="<?= $job['id'] ?>" <?= $filterJobId === $job['id'] ? 'selected' : '' ?>>
                            <?= e($job['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i> Lọc
                </button>
                <?php if ($filterJobId): ?>
                    <a href="<?= e(url('employer/applications')) ?>" class="btn btn-outline-secondary btn-sm" title="Xóa filter">
                        <i class="bi bi-x me-1"></i> Bỏ lọc
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!$apps): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Chưa có đơn ứng tuyển nào.</div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>Ứng viên</th>
                <th>Vị trí ứng tuyển</th>
                <th>CV</th>
                <th>Thư xin việc</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th class="text-center">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($apps as $a): ?>
                <?php
                $badgeCls = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$a['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$a['status']] ?? $a['status'];
                ?>
                <tr>
                    <td>
                        <div class="fw-600 small"><?= e($a['applicant_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem"><?= e($a['applicant_email']) ?></div>
                    </td>
                    <td class="small fw-500"><?= e($a['job_title']) ?></td>
                    <td>
                        <!-- Nút xem/tải CV -->
                        <a href="<?= e(url('download_cv', ['id' => $a['id']])) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Tải CV
                        </a>
                    </td>
                    <td class="small text-secondary">
                        <?= e(mb_strimwidth($a['cover_letter'] ?? '', 0, 80, '...')) ?>
                    </td>
                    <td><span class="<?= $badgeCls ?>"><?= $statusLabel ?></span></td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <form method="post" class="d-flex gap-1 justify-content-center">
                            <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                            <!-- Giữ nguyên filter job_id khi submit action -->
                            <?php if ($filterJobId): ?>
                                <input type="hidden" name="job_id" value="<?= $filterJobId ?>">
                            <?php endif; ?>
                            <button name="action" value="accepted" class="btn btn-sm btn-success"
                                    title="Nhận đơn">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button name="action" value="rejected" class="btn btn-sm btn-danger"
                                    title="Từ chối">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button name="action" value="pending" class="btn btn-sm btn-secondary"
                                    title="Đặt lại chờ duyệt">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    <?= render_pagination($total, $perPage, $page, $baseUrl) ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
