<?php
// Trang dashboard của nhà tuyển dụng: thống kê + đơn ứng tuyển gần đây
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

// Tổng lượt xem của tất cả job thuộc employer này
$totalViews = db()->prepare('SELECT COALESCE(SUM(views), 0) FROM jobs WHERE employer_id = ?');
$totalViews->execute([$u['id']]);
$totalViews = (int)$totalViews->fetchColumn();

// Lấy thông tin công ty
$companyStmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ?');
$companyStmt->execute([$u['id']]);
$company = $companyStmt->fetch();

// Top 5 jobs theo lượt xem (cho bar chart ngang)
$topViewsStmt = db()->prepare("
    SELECT title, views FROM jobs
    WHERE employer_id = ? AND is_active = 1
    ORDER BY views DESC LIMIT 5
");
$topViewsStmt->execute([$u['id']]);
$topViewsJobs = $topViewsStmt->fetchAll();

// Số đơn ứng tuyển phân loại theo trạng thái (cho doughnut chart)
$appStatsStmt = db()->prepare("
    SELECT a.status, COUNT(*) AS cnt
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    WHERE j.employer_id = ?
    GROUP BY a.status
");
$appStatsStmt->execute([$u['id']]);
$appStats = [];
foreach ($appStatsStmt->fetchAll() as $row) {
    $appStats[$row['status']] = (int)$row['cnt'];
}

// Lấy 5 đơn ứng tuyển mới nhất vào các bài đăng của employer này
$recentApps = db()->prepare("
    SELECT a.id, a.status, a.created_at,
           u.full_name AS applicant_name,
           u.email     AS applicant_email,
           j.title     AS job_title
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN users u ON u.id = a.user_id
    WHERE j.employer_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recentApps->execute([$u['id']]);
$recentApps = $recentApps->fetchAll();

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

<!-- Stats: 5 ô thống kê -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
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
    <div class="col-md-3">
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
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted fw-500">Tổng lượt xem</div>
                        <div class="fs-2 fw-700 text-info"><?= number_format($totalViews) ?></div>
                    </div>
                    <div class="fs-1 text-info opacity-25"><i class="bi bi-eye"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Analytics Charts ===== -->
<?php if ($topViewsJobs || array_sum($appStats) > 0): ?>
<div class="row g-4 mb-4">
    <!-- Chart 1: Top jobs theo lượt xem (bar chart ngang) -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Top việc làm theo lượt xem
                </h6>
                <?php if ($topViewsJobs): ?>
                    <canvas id="chartViews" height="200"></canvas>
                <?php else: ?>
                    <div class="text-muted small text-center py-4">Chưa có dữ liệu lượt xem</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Chart 2: Đơn ứng tuyển theo trạng thái (doughnut chart) -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Trạng thái đơn ứng tuyển
                </h6>
                <?php if (array_sum($appStats) > 0): ?>
                    <canvas id="chartApps" height="200"></canvas>
                <?php else: ?>
                    <div class="text-muted small text-center py-4">Chưa có đơn ứng tuyển nào</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN + khởi tạo charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
<?php if ($topViewsJobs): ?>
// Chart 1: bar chart ngang hiển thị top jobs theo lượt xem
new Chart(document.getElementById('chartViews'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($topViewsJobs, 'title'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Lượt xem',
            data: <?= json_encode(array_column($topViewsJobs, 'views')) ?>,
            backgroundColor: 'rgba(26,86,219,0.7)',
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
<?php endif; ?>

<?php if (array_sum($appStats) > 0): ?>
// Chart 2: doughnut chart hiển thị tỉ lệ trạng thái đơn ứng tuyển
new Chart(document.getElementById('chartApps'), {
    type: 'doughnut',
    data: {
        labels: ['Chờ xét', 'Chấp nhận', 'Từ chối'],
        datasets: [{
            data: [
                <?= (int)($appStats['pending'] ?? 0) ?>,
                <?= (int)($appStats['accepted'] ?? 0) ?>,
                <?= (int)($appStats['rejected'] ?? 0) ?>
            ],
            backgroundColor: ['#fbbf24', '#34d399', '#f87171'],
            borderWidth: 0
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } },
        cutout: '65%'
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<!-- Quick links -->
<div class="d-flex gap-2 flex-wrap mb-4">
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

<!-- Bảng đơn ứng tuyển gần đây -->
<?php if ($recentApps): ?>
<div class="card border-0 shadow-sm rounded-3 mt-2">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="fw-700 mb-0">
            <i class="bi bi-clock-history me-2 text-primary"></i>Đơn ứng tuyển gần đây
        </h6>
        <a href="<?= e(url('employer/applications')) ?>" class="btn btn-sm btn-outline-primary">
            Xem tất cả <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
                    <th>Ứng viên</th>
                    <th>Vị trí ứng tuyển</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentApps as $a): ?>
                <?php
                // Map status sang badge class và label hiển thị
                $badgeCls    = ['pending'=>'badge-status-pending','accepted'=>'badge-status-accepted','rejected'=>'badge-status-rejected'][$a['status']] ?? 'badge-status-pending';
                $statusLabel = ['pending'=>'Chờ duyệt','accepted'=>'Đã nhận','rejected'=>'Từ chối'][$a['status']] ?? $a['status'];
                ?>
                <tr>
                    <td>
                        <div class="fw-600 small"><?= e($a['applicant_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem"><?= e($a['applicant_email']) ?></div>
                    </td>
                    <td class="small fw-500"><?= e($a['job_title']) ?></td>
                    <td><span class="<?= $badgeCls ?>"><?= $statusLabel ?></span></td>
                    <td class="small text-muted"><?= time_ago($a['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../layout/footer.php';
