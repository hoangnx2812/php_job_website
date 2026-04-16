<?php
// Dashboard admin - tổng quan hệ thống + biểu đồ Chart.js
require_role('admin');
$pdo = db();

$stats = [
    'users'           => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'jobs'            => (int)$pdo->query('SELECT COUNT(*) FROM jobs WHERE is_active = 1')->fetchColumn(),
    'companies'       => (int)$pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
    'applications'    => (int)$pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'pending_employers' => (int)$pdo->query("SELECT COUNT(*) FROM employer_requests WHERE status='pending'")->fetchColumn(),
];

// Dữ liệu biểu đồ 1: Số job tạo mới theo ngày (7 ngày gần nhất)
$jobsByDay = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS cnt
    FROM jobs
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll();

// Điền đủ 7 ngày (ngày nào không có thì = 0)
$jobDayLabels = [];
$jobDayData   = [];
$dayMap       = array_column($jobsByDay, 'cnt', 'day');
for ($i = 6; $i >= 0; $i--) {
    $d               = date('Y-m-d', strtotime("-$i days"));
    $jobDayLabels[]  = date('d/m', strtotime($d));
    $jobDayData[]    = (int)($dayMap[$d] ?? 0);
}

// Dữ liệu biểu đồ 2: Tỉ lệ đơn theo trạng thái
$appStats = $pdo->query("
    SELECT status, COUNT(*) AS cnt FROM applications GROUP BY status
")->fetchAll();
$appLabels = ['pending' => 'Chờ duyệt', 'accepted' => 'Đã nhận', 'rejected' => 'Từ chối'];
$appColors = ['pending' => '#fbbf24', 'accepted' => '#34d399', 'rejected' => '#f87171'];
$appChartLabels = [];
$appChartData   = [];
$appChartColors = [];
foreach ($appStats as $row) {
    $appChartLabels[] = $appLabels[$row['status']] ?? $row['status'];
    $appChartData[]   = (int)$row['cnt'];
    $appChartColors[] = $appColors[$row['status']] ?? '#94a3b8';
}

// Dữ liệu biểu đồ 3: Số user theo role
$userStats = $pdo->query("
    SELECT role, COUNT(*) AS cnt FROM users GROUP BY role ORDER BY role
")->fetchAll();
$roleLabels = ['admin' => 'Admin', 'employer' => 'Employer', 'user' => 'Ứng viên'];
$roleColors = ['admin' => '#6366f1', 'employer' => '#f59e0b', 'user' => '#10b981'];
$userChartLabels = [];
$userChartData   = [];
$userChartColors = [];
foreach ($userStats as $row) {
    $userChartLabels[] = $roleLabels[$row['role']] ?? $row['role'];
    $userChartData[]   = (int)$row['cnt'];
    $userChartColors[] = $roleColors[$row['role']] ?? '#94a3b8';
}

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../../layout/header.php';
?>
<h4 class="fw-700 mb-4">
    <i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard
</h4>

<!-- Thống kê tổng quan -->
<div class="row g-3 mb-4">
    <?php if ($stats['pending_employers'] > 0): ?>
    <div class="col-12">
        <a href="<?= e(url('admin/employer_requests')) ?>" class="alert alert-warning d-flex align-items-center mb-0 text-decoration-none">
            <i class="bi bi-person-exclamation fs-4 me-3"></i>
            <div>
                Có <strong><?= $stats['pending_employers'] ?> yêu cầu</strong> trở thành nhà tuyển dụng đang chờ bạn duyệt.
                <span class="text-decoration-underline ms-1">Xem ngay →</span>
            </div>
        </a>
    </div>
    <?php endif; ?>
    <div class="col-md-3">
        <div class="stat-card" style="--card-from:#0891b2;--card-to:#164e63">
            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-card-value"><?= $stats['users'] ?></div>
            <div class="stat-card-label">Tổng người dùng</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--card-from:#1a56db;--card-to:#0d3b8e">
            <div class="stat-card-icon"><i class="bi bi-briefcase-fill"></i></div>
            <div class="stat-card-value"><?= $stats['jobs'] ?></div>
            <div class="stat-card-label">Việc làm đang mở</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--card-from:#be185d;--card-to:#831843">
            <div class="stat-card-icon"><i class="bi bi-building-fill"></i></div>
            <div class="stat-card-value"><?= $stats['companies'] ?></div>
            <div class="stat-card-label">Công ty</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--card-from:#059669;--card-to:#065f46">
            <div class="stat-card-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div class="stat-card-value"><?= $stats['applications'] ?></div>
            <div class="stat-card-label">Đơn ứng tuyển</div>
        </div>
    </div>
</div>

<!-- Quick links -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="<?= e(url('admin/users')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-people me-1"></i>Quản lý user
    </a>
    <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-megaphone me-1"></i>Quản lý bài đăng
    </a>
    <a href="<?= e(url('admin/companies')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-building me-1"></i>Quản lý công ty
    </a>
    <a href="<?= e(url('admin/applications')) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-file-earmark-person me-1"></i>Quản lý CV
    </a>
    <a href="<?= e(url('admin/employer_requests')) ?>" class="btn btn-outline-warning btn-sm position-relative">
        <i class="bi bi-person-check me-1"></i>Duyệt nhà tuyển dụng
        <?php if ($stats['pending_employers'] > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $stats['pending_employers'] ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<!-- Biểu đồ -->
<div class="row g-4">
    <!-- Biểu đồ cột: Job theo ngày -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                    Bài đăng mới (7 ngày gần nhất)
                </h6>
                <canvas id="chartJobsByDay" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Biểu đồ tròn: Tỉ lệ đơn theo trạng thái -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-pie-chart-fill me-2 text-warning"></i>
                    Tỉ lệ đơn ứng tuyển
                </h6>
                <canvas id="chartAppStatus" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Biểu đồ cột: User theo role -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-people-fill me-2 text-success"></i>
                    Phân bố người dùng
                </h6>
                <canvas id="chartUsersByRole" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
// Màu chung
const chartDefaults = {
    borderRadius: 6,
    borderSkipped: false,
};
Chart.defaults.font.family = "'Be Vietnam Pro', sans-serif";

// Biểu đồ 1: Job theo ngày (cột)
new Chart(document.getElementById('chartJobsByDay'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($jobDayLabels) ?>,
        datasets: [{
            label: 'Bài đăng',
            data: <?= json_encode($jobDayData) ?>,
            backgroundColor: '#1a56db',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: '#f1f5f9' }
            },
            x: { grid: { display: false } }
        }
    }
});

// Biểu đồ 2: Tỉ lệ đơn theo trạng thái (tròn)
new Chart(document.getElementById('chartAppStatus'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($appChartLabels) ?>,
        datasets: [{
            data: <?= json_encode($appChartData) ?>,
            backgroundColor: <?= json_encode($appChartColors) ?>,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 } } }
        },
        cutout: '60%',
    }
});

// Biểu đồ 3: User theo role (cột ngang)
new Chart(document.getElementById('chartUsersByRole'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($userChartLabels) ?>,
        datasets: [{
            label: 'Số người',
            data: <?= json_encode($userChartData) ?>,
            backgroundColor: <?= json_encode($userChartColors) ?>,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: '#f1f5f9' }
            },
            y: { grid: { display: false } }
        }
    }
});
</script>
<?php require __DIR__ . '/../../layout/footer.php';
