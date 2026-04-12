<?php
// Trang chủ: hero + khám phá theo lĩnh vực + job mới nhất + stats
$pdo = db();

// Lấy 6 job mới nhất kèm logo công ty
$latest = $pdo->query("
    SELECT j.*, c.name AS company_name, c.logo AS company_logo, c.location AS company_location
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.is_active = 1
    ORDER BY j.created_at DESC
    LIMIT 6
")->fetchAll();

// Thống kê tổng quan để hiện ở hero section
$statsJobs = (int)$pdo->query('SELECT COUNT(*) FROM jobs WHERE is_active = 1')->fetchColumn();
$statsCompanies = (int)$pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn();
$statsUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Đếm số jobs theo từng lĩnh vực để hiển thị section "Khám phá"
$categoryStats = $pdo->query("
    SELECT category, COUNT(*) AS job_count
    FROM jobs
    WHERE is_active = 1
    GROUP BY category
    ORDER BY job_count DESC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Cấu hình icon và màu cho từng lĩnh vực
$categoryConfig = [
    'Công nghệ thông tin' => ['icon' => 'bi-code-slash',   'color' => '#1a56db', 'bg' => '#eff6ff'],
    'Marketing'           => ['icon' => 'bi-megaphone',    'color' => '#7c3aed', 'bg' => '#f5f3ff'],
    'Thiết kế'            => ['icon' => 'bi-palette',      'color' => '#db2777', 'bg' => '#fdf2f8'],
    'Tài chính'           => ['icon' => 'bi-graph-up',     'color' => '#059669', 'bg' => '#ecfdf5'],
    'HR'                  => ['icon' => 'bi-people',       'color' => '#d97706', 'bg' => '#fffbeb'],
    'Bán hàng'            => ['icon' => 'bi-cart',         'color' => '#dc2626', 'bg' => '#fef2f2'],
    'Vận hành'            => ['icon' => 'bi-gear',         'color' => '#0891b2', 'bg' => '#ecfeff'],
    'Khác'                => ['icon' => 'bi-briefcase',    'color' => '#64748b', 'bg' => '#f8fafc'],
];

$u = current_user();
$pageTitle = 'Trang chủ';
require __DIR__ . '/../layout/header.php';
?>

<!-- Hero Section -->
<div class="hero-section rounded-4 mb-5 p-5 text-white text-center position-relative overflow-hidden"
     style="background: linear-gradient(135deg, #1a56db 0%, #0d3b8e 60%, #1e1b4b 100%); min-height: 340px;">
    <!-- Decorative circles -->
    <div style="position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
    <div style="position:absolute;bottom:-40px;left:-40px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>

    <div class="position-relative">
        <h1 class="display-5 fw-700 mb-2">Tìm công việc mơ ước</h1>
        <p class="lead mb-4 opacity-85">Hàng ngàn việc làm IT, marketing, kinh doanh đang chờ bạn</p>

        <!-- Search box nổi bật -->
        <form action="<?= e(BASE_URL) ?>" method="get" class="row g-2 justify-content-center">
            <input type="hidden" name="page" value="jobs">
            <div class="col-md-5">
                <input name="q" class="form-control form-control-lg"
                       style="border-radius:10px;border:none;font-size:1rem;"
                       placeholder="Tên công việc, kỹ năng...">
            </div>
            <div class="col-md-3">
                <input name="location" class="form-control form-control-lg"
                       style="border-radius:10px;border:none;font-size:1rem;"
                       placeholder="Địa điểm">
            </div>
            <div class="col-auto">
                <button class="btn btn-warning fw-600 btn-lg" style="border-radius:10px;padding:0.6rem 1.5rem;">
                    <i class="bi bi-search me-1"></i> Tìm kiếm
                </button>
            </div>
        </form>

        <!-- Stats counter với animation đếm từ 0 -->
        <div class="row justify-content-center mt-4 g-3">
            <div class="col-auto">
                <div class="px-3 py-2 rounded-3" style="background:rgba(255,255,255,0.12)">
                    <span class="fw-700 fs-5 counter" data-target="<?= $statsJobs ?>">0</span><span class="fw-700 fs-5">+</span>
                    <span class="ms-1 opacity-85 small">Việc làm</span>
                </div>
            </div>
            <div class="col-auto">
                <div class="px-3 py-2 rounded-3" style="background:rgba(255,255,255,0.12)">
                    <span class="fw-700 fs-5 counter" data-target="<?= $statsCompanies ?>">0</span><span class="fw-700 fs-5">+</span>
                    <span class="ms-1 opacity-85 small">Công ty</span>
                </div>
            </div>
            <div class="col-auto">
                <div class="px-3 py-2 rounded-3" style="background:rgba(255,255,255,0.12)">
                    <span class="fw-700 fs-5 counter" data-target="<?= $statsUsers ?>">0</span><span class="fw-700 fs-5">+</span>
                    <span class="ms-1 opacity-85 small">Ứng viên</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section: Khám phá theo lĩnh vực -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-700 mb-0">
            <i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i> Khám phá theo lĩnh vực
        </h4>
    </div>
    <div class="row g-3">
        <?php foreach ($categoryConfig as $catName => $cfg): ?>
            <?php $count = $categoryStats[$catName] ?? 0; ?>
            <div class="col-6 col-md-3">
                <a href="<?= e(url('jobs', ['category' => $catName])) ?>"
                   class="category-card text-decoration-none d-block"
                   style="--cat-color: <?= $cfg['color'] ?>; --cat-bg: <?= $cfg['bg'] ?>;">
                    <div class="category-card-icon">
                        <i class="bi <?= $cfg['icon'] ?>"></i>
                    </div>
                    <div class="category-card-name"><?= e($catName) ?></div>
                    <div class="category-card-count"><?= $count ?> việc làm</div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Job mới nhất -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-lightning-fill text-warning me-1"></i> Việc làm mới nhất
    </h4>
    <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">
        Xem tất cả <i class="bi bi-arrow-right"></i>
    </a>
</div>

<div class="row g-3 mb-5">
<?php foreach ($latest as $j): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card job-card h-100 <?= $j['is_hot'] ? 'hot' : '' ?>">
            <div class="card-body p-3">
                <!-- Logo + tên công ty -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($j['company_logo']): ?>
                        <img src="/uploads/logos/<?= e($j['company_logo']) ?>"
                             alt="<?= e($j['company_name']) ?>"
                             class="company-logo">
                    <?php else: ?>
                        <div class="company-logo-placeholder">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="fw-600 small"><?= e($j['company_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem">
                            <i class="bi bi-geo-alt-fill me-1"></i><?= e($j['location']) ?>
                        </div>
                    </div>
                </div>
                <!-- Tiêu đề job -->
                <h6 class="fw-600 mb-2">
                    <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                       class="text-decoration-none text-dark stretched-link">
                        <?= e($j['title']) ?>
                    </a>
                    <?php if ($j['is_hot']): ?>
                        <span class="badge-hot ms-1">HOT</span>
                    <?php endif; ?>
                </h6>
                <!-- Badges -->
                <div class="d-flex flex-wrap gap-1 align-items-center">
                    <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                    <span class="badge-type"><?= e($j['job_type']) ?></span>
                    <?php if (!empty($j['category'])): ?>
                        <span class="badge-category"><?= e($j['category']) ?></span>
                    <?php endif; ?>
                    <?= deadline_badge($j['expired_at'] ?? null) ?>
                </div>
                <div class="text-muted mt-2" style="font-size:0.74rem">
                    <i class="bi bi-clock me-1"></i><?= time_ago($j['created_at']) ?>
                    <span class="ms-2"><i class="bi bi-eye me-1"></i><?= format_views($j['views']) ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- CTA section -->
<?php if (!$u): ?>
<div class="card border-0 shadow-sm rounded-4 text-center p-5"
     style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
    <h4 class="fw-700 mb-2">Bắt đầu hành trình sự nghiệp của bạn</h4>
    <p class="text-muted mb-4">Đăng ký miễn phí để ứng tuyển, lưu việc làm yêu thích và nhận thông báo job mới</p>
    <div class="d-flex gap-2 justify-content-center">
        <a href="<?= e(url('register')) ?>" class="btn btn-primary btn-lg px-4">
            <i class="bi bi-person-plus me-1"></i> Đăng ký ngay
        </a>
        <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-lg px-4">
            <i class="bi bi-search me-1"></i> Duyệt việc làm
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// Animated counter: đếm từ 0 đến target khi trang load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.counter').forEach(function (el) {
        var target = parseInt(el.dataset.target, 10);
        if (!target) return;
        var duration = 1200; // ms
        var step = Math.ceil(target / (duration / 30));
        var current = 0;
        var timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current;
        }, 30);
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php';
