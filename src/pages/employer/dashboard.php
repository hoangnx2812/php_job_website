<?php
// Trang dashboard của nhà tuyển dụng: vài con số tóm tắt
$u = require_role('employer');

$jobCount = db()->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id = ?');
$jobCount->execute([$u['id']]);
$jobCount = (int)$jobCount->fetchColumn();

$appCount = db()->prepare('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ?');
$appCount->execute([$u['id']]);
$appCount = (int)$appCount->fetchColumn();

$pendingCount = db()->prepare('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id = a.job_id WHERE j.employer_id = ? AND a.status = ?');
$pendingCount->execute([$u['id'], 'pending']);
$pendingCount = (int)$pendingCount->fetchColumn();

// Lấy thông tin công ty
$companyStmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ?');
$companyStmt->execute([$u['id']]);
$company = $companyStmt->fetch();

$pageTitle = 'Bảng điều khiển';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-700 mb-1">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>Bảng điều khiển
        </h4>
        <div class="text-muted small">Xin chào, <strong><?= e($u['full_name']) ?></strong></div>
    </div>
    <?php if ($company): ?>
        <div class="d-flex align-items-center gap-2">
            <?php if ($company['logo']): ?>
                <img src="/uploads/logos/<?= e($company['logo']) ?>"
                     style="width:40px;height:40px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;padding:4px;background:#fff"
                     alt="logo">
            <?php endif; ?>
            <div>
                <div class="fw-600 small"><?= e($company['name']) ?></div>
                <a href="<?= e(url('employer/company')) ?>" class="small text-muted">Chỉnh sửa</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted fw-500">Bài đăng</div>
                        <div class="fs-2 fw-700 text-primary"><?= $jobCount ?></div>
                    </div>
                    <div class="fs-1 text-primary opacity-25"><i class="bi bi-megaphone"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted fw-500">Tổng đơn ứng tuyển</div>
                        <div class="fs-2 fw-700 text-success"><?= $appCount ?></div>
                    </div>
                    <div class="fs-1 text-success opacity-25"><i class="bi bi-file-earmark-person"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted fw-500">Đơn chờ duyệt</div>
                        <div class="fs-2 fw-700 text-warning"><?= $pendingCount ?></div>
                    </div>
                    <div class="fs-1 text-warning opacity-25"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick links -->
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= e(url('employer/jobs')) ?>" class="btn btn-outline-primary">
        <i class="bi bi-list-ul me-1"></i> Bài đăng của tôi
    </a>
    <a href="<?= e(url('employer/job_form')) ?>" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Đăng bài mới
    </a>
    <a href="<?= e(url('employer/applications')) ?>" class="btn btn-outline-primary">
        <i class="bi bi-file-earmark-person me-1"></i> Đơn ứng tuyển
        <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?= $pendingCount ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= e(url('employer/company')) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-building-gear me-1"></i>
        <?= $company ? 'Cài đặt công ty' : 'Tạo công ty' ?>
    </a>
</div>

<?php if (!$company): ?>
    <div class="alert alert-warning mt-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Bạn chưa có công ty. <a href="<?= e(url('employer/company')) ?>" class="alert-link">Tạo ngay</a>
        để có thể đăng bài tuyển dụng.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../layout/footer.php';
