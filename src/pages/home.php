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

<!-- Hero Section: gradient mesh + floating blobs -->
<div class="hero-section position-relative overflow-hidden" style="
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 40%, #1a56db 100%);
    padding: 5rem 0 4rem;
    margin: -1.5rem -12px 2rem;
">
    <!-- Decorative floating blobs -->
    <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;
                background:rgba(96,165,250,0.12);border-radius:50%;filter:blur(60px);pointer-events:none"></div>
    <div style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;
                background:rgba(167,139,250,0.10);border-radius:50%;filter:blur(50px);pointer-events:none"></div>

    <div class="container position-relative">
        <!-- Badge trên cùng -->
        <div class="text-center mb-3">
            <span style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.9);
                         border:1px solid rgba(255,255,255,0.2);border-radius:999px;
                         font-size:0.82rem;font-weight:600;padding:0.35em 1em;letter-spacing:0.03em">
                Nen tang tuyen dung #1 Viet Nam
            </span>
        </div>

        <!-- Heading -->
        <h1 class="text-white text-center fw-700 mb-2" style="font-size:clamp(1.8rem,4vw,3rem);line-height:1.2">
            Tim Viec Lam <span style="background:linear-gradient(90deg,#60a5fa,#a78bfa);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
            Mo Uoc</span> Cua Ban
        </h1>
        <p class="text-center mb-4" style="color:rgba(255,255,255,0.75);font-size:1.1rem;max-width:520px;margin:0 auto 1.75rem">
            Ket noi voi hang nghin co hoi viec lam tu cac cong ty hang dau
        </p>

        <!-- Search bar nổi bật -->
        <div class="mx-auto" style="max-width:640px">
            <form method="get" action="<?= e(BASE_URL) ?>">
                <input type="hidden" name="page" value="jobs">
                <div style="background:#fff;border-radius:16px;padding:6px;
                            box-shadow:0 20px 60px rgba(0,0,0,0.3);display:flex;gap:6px">
                    <div style="flex:1;position:relative">
                        <i class="bi bi-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);
                                                        color:#94a3b8;font-size:1rem"></i>
                        <input name="q" class="form-control border-0 shadow-none"
                               style="padding-left:2.5rem;border-radius:12px;font-size:0.95rem;background:transparent"
                               placeholder="Vi tri, ky nang, cong ty...">
                    </div>
                    <button class="btn btn-primary px-4 fw-600" style="border-radius:12px;white-space:nowrap">
                        <i class="bi bi-search me-1"></i>Tim kiem
                    </button>
                </div>
                <!-- Quick tags gợi ý tìm kiếm -->
                <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                    <?php foreach(['PHP Developer','Frontend React','Marketing','UI/UX Designer','Data Analyst'] as $kw): ?>
                        <a href="<?= e(url('jobs',['q'=>$kw])) ?>"
                           style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.85);
                                  border:1px solid rgba(255,255,255,0.2);border-radius:999px;
                                  font-size:0.78rem;padding:0.25em 0.85em;text-decoration:none;
                                  transition:background 0.15s"
                           onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                            <?= e($kw) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>

        <!-- Stats counters dạng card đẹp hơn -->
        <div class="row justify-content-center mt-4 g-3">
            <?php foreach([
                ['icon'=>'bi-briefcase-fill','value'=>$statsJobs,'label'=>'Viec lam'],
                ['icon'=>'bi-building-fill','value'=>$statsCompanies,'label'=>'Cong ty'],
                ['icon'=>'bi-people-fill','value'=>$statsUsers,'label'=>'Ung vien'],
            ] as $s): ?>
            <div class="col-auto">
                <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);
                            border:1px solid rgba(255,255,255,0.18);border-radius:16px;
                            padding:0.9rem 1.5rem;text-align:center;min-width:110px">
                    <i class="bi <?= $s['icon'] ?>" style="color:rgba(255,255,255,0.7);font-size:1.3rem"></i>
                    <div class="fw-700 text-white" style="font-size:1.4rem;line-height:1.2">
                        <span class="counter" data-target="<?= $s['value'] ?>">0</span>+
                    </div>
                    <div style="color:rgba(255,255,255,0.65);font-size:0.8rem"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Section: Khám phá theo lĩnh vực -->
<div class="mb-5">
    <h2 class="section-title">
        <i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i> Kham pha theo linh vuc
    </h2>
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
<div class="d-flex justify-content-between align-items-start mb-3">
    <h2 class="section-title">
        <i class="bi bi-lightning-fill text-warning me-1"></i> Viec lam moi nhat
    </h2>
    <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm mt-1">
        Xem tat ca <i class="bi bi-arrow-right"></i>
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
                <!-- Footer card: thời gian + views -->
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2"
                     style="border-top: 1px solid var(--border-color);">
                    <span class="text-muted" style="font-size:0.73rem">
                        <i class="bi bi-clock me-1"></i><?= time_ago($j['created_at']) ?>
                    </span>
                    <div class="d-flex gap-2 text-muted" style="font-size:0.73rem">
                        <span><i class="bi bi-eye me-1"></i><?= format_views($j['views']) ?></span>
                    </div>
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
