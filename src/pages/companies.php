<?php
// Trang danh sách công ty: tìm kiếm, sắp xếp, phân trang
$pageNum = max(1, (int)($_GET['p'] ?? 1));
$perPage = 9;
$q       = trim($_GET['q'] ?? '');
$sort    = in_array($_GET['sort'] ?? '', ['name', 'jobs']) ? $_GET['sort'] : 'jobs';

// Build câu truy vấn chính
$sql = "SELECT c.*,
               (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.is_active = 1) AS job_count
        FROM companies c
        WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (c.name LIKE ? OR c.location LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Đếm tổng bản ghi để phân trang
$countSql  = "SELECT COUNT(*) FROM companies c WHERE 1=1";
$cParams   = [];
if ($q !== '') {
    $countSql .= " AND (c.name LIKE ? OR c.location LIKE ?)";
    $cParams[] = "%$q%";
    $cParams[] = "%$q%";
}
$countStmt = db()->prepare($countSql);
$countStmt->execute($cParams);
$total = (int)$countStmt->fetchColumn();

// Sắp xếp
$orderBy = $sort === 'name' ? 'c.name ASC' : 'job_count DESC, c.name ASC';
$sql .= " ORDER BY $orderBy LIMIT $perPage OFFSET " . (($pageNum - 1) * $perPage);
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// URL gốc để render pagination (không có p=)
$baseUrl = BASE_URL . '?' . http_build_query(array_filter([
    'page' => 'companies',
    'q'    => $q,
    'sort' => $sort !== 'jobs' ? $sort : '',
]));

// Bảng màu gradient cho banner — xoay vòng theo ID công ty
$palettes = [
    ['#1a56db', '#7c3aed'],   // xanh dương → tím
    ['#059669', '#0891b2'],   // xanh lá → ngọc
    ['#0891b2', '#1d4ed8'],   // ngọc → xanh navy
    ['#7c3aed', '#db2777'],   // tím → hồng
    ['#d97706', '#dc2626'],   // cam → đỏ
    ['#be185d', '#7c3aed'],   // hồng đậm → tím
    ['#065f46', '#0891b2'],   // xanh rừng → ngọc
    ['#1e3a8a', '#4f46e5'],   // navy → indigo
];

$pageTitle = 'Công ty';
require __DIR__ . '/../layout/header.php';
?>

<!-- ===== Page header ===== -->
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h4 class="fw-700 mb-1">
            <i class="bi bi-buildings me-2 text-primary"></i>Danh sách công ty
        </h4>
        <p class="text-muted small mb-0">
            Khám phá <strong><?= $total ?></strong> công ty đang tuyển dụng trên toàn quốc
        </p>
    </div>
    <!-- Tổng số + sort hiện tại -->
    <div class="d-flex align-items-center gap-2">
        <span class="badge-type">
            <i class="bi bi-sort-down me-1"></i>
            <?= $sort === 'name' ? 'Tên A → Z' : 'Nhiều việc làm nhất' ?>
        </span>
    </div>
</div>

<!-- ===== Thanh tìm kiếm + sắp xếp ===== -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="get">
            <input type="hidden" name="page" value="companies">
            <div class="row g-2 align-items-center">
                <!-- Ô tìm kiếm -->
                <div class="col-12 col-md-7 col-lg-8">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"
                           style="pointer-events:none;font-size:0.9rem"></i>
                        <input name="q" value="<?= e($q) ?>" class="form-control ps-5"
                               placeholder="Tìm theo tên công ty hoặc địa điểm...">
                    </div>
                </div>
                <!-- Sắp xếp -->
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="jobs" <?= $sort === 'jobs' ? 'selected' : '' ?>>
                            Nhiều việc làm nhất
                        </option>
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>
                            Tên A → Z
                        </option>
                    </select>
                </div>
                <!-- Nút tìm + xoá filter -->
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>
                    <?php if ($q): ?>
                        <a href="<?= e(url('companies')) ?>" class="btn btn-outline-secondary" title="Xoá bộ lọc">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ===== Kết quả ===== -->
<?php if (!$rows): ?>
    <!-- Empty state -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <div style="font-size:3.5rem;opacity:0.25;line-height:1" class="mb-3">
                <i class="bi bi-building-slash text-muted"></i>
            </div>
            <h5 class="fw-600 mb-1">Không tìm thấy công ty nào</h5>
            <p class="text-muted small mb-3">
                Thử từ khoá khác hoặc
                <a href="<?= e(url('companies')) ?>" class="text-primary">xem tất cả công ty</a>
            </p>
        </div>
    </div>
<?php else: ?>

    <div class="row g-4 g-md-5 mb-4">
    <?php foreach ($rows as $c):
        // Chọn màu gradient cho banner theo ID
        $pal = $palettes[$c['id'] % count($palettes)];
        $bg  = "linear-gradient(135deg, {$pal[0]} 0%, {$pal[1]} 100%)";
        $cnt = (int)$c['job_count'];
    ?>
        <div class="col-md-6 col-lg-4">
            <div class="company-card h-100 d-flex flex-column position-relative">

                <!-- Banner gradient (màu xoay vòng theo ID) -->
                <div class="company-banner" style="background:<?= $bg ?>">
                    <?php if ($cnt > 0): ?>
                        <!-- Badge số việc làm góc phải banner -->
                        <span style="position:absolute;top:10px;right:12px;z-index:1;
                                     background:rgba(255,255,255,0.22);
                                     backdrop-filter:blur(4px);
                                     border:1px solid rgba(255,255,255,0.35);
                                     color:#fff;font-size:0.72rem;font-weight:700;
                                     padding:0.2em 0.7em;border-radius:999px">
                            <?= $cnt ?> việc làm
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Logo nổi lên trên đường ranh giới banner/body -->
                <div class="company-logo-wrap">
                    <?php if ($c['logo']): ?>
                        <img src="/uploads/logos/<?= e($c['logo']) ?>"
                             alt="<?= e($c['name']) ?>"
                             style="width:100%;height:100%;object-fit:contain;padding:6px">
                    <?php else: ?>
                        <!-- Chữ cái đầu tên công ty làm avatar nếu không có logo -->
                        <div style="width:100%;height:100%;display:flex;align-items:center;
                                    justify-content:center;font-size:1.4rem;font-weight:700;
                                    color:<?= $pal[0] ?>;background:<?= $pal[0] ?>18">
                            <?= mb_strtoupper(mb_substr($c['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Nội dung card -->
                <div class="card-body d-flex flex-column"
                     style="padding: 2.8rem 1.4rem 1.4rem">

                    <!-- Tên công ty (link bao phủ toàn card) -->
                    <h6 class="fw-700 mb-2 lh-sm" style="font-size:1rem">
                        <a href="<?= e(url('company_detail', ['id' => $c['id']])) ?>"
                           class="text-decoration-none stretched-link"
                           style="color:var(--text-main)"><?= e($c['name']) ?></a>
                    </h6>

                    <!-- Địa điểm -->
                    <div class="text-muted small mb-3 d-flex align-items-center gap-1">
                        <i class="bi bi-geo-alt-fill" style="font-size:0.75rem;color:<?= $pal[0] ?>"></i>
                        <?= e($c['location'] ?: 'Chưa cập nhật') ?>
                    </div>

                    <!-- Mô tả ngắn (2 dòng, text-clamp) -->
                    <?php if (!empty($c['description'])): ?>
                        <p class="small flex-grow-1 mb-4"
                           style="color:var(--text-muted);line-height:1.65;
                                  display:-webkit-box;-webkit-line-clamp:2;
                                  -webkit-box-orient:vertical;overflow:hidden">
                            <?= e($c['description']) ?>
                        </p>
                    <?php else: ?>
                        <div class="flex-grow-1 mb-4"></div>
                    <?php endif; ?>

                    <!-- Footer card: việc làm + website -->
                    <div class="d-flex justify-content-between align-items-center pt-3"
                         style="border-top:1px solid var(--border-color)">
                        <?php if ($cnt > 0): ?>
                            <span class="badge-type">
                                <i class="bi bi-briefcase-fill me-1"></i><?= $cnt ?> việc làm
                            </span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.78rem">
                                <i class="bi bi-clock me-1"></i>Chưa có việc làm
                            </span>
                        <?php endif; ?>

                        <?php if ($c['website']): ?>
                            <a href="<?= e($c['website']) ?>" target="_blank" rel="noopener"
                               class="text-muted text-decoration-none position-relative"
                               style="z-index:2;font-size:0.78rem;
                                      transition:color 0.15s"
                               onmouseover="this.style.color='<?= $pal[0] ?>'"
                               onmouseout="this.style.color=''"
                               title="Truy cập website">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Website
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
    </div>

<?php endif; ?>

<!-- Phân trang -->
<?= render_pagination($total, $perPage, $pageNum, $baseUrl) ?>

<?php require __DIR__ . '/../layout/footer.php';
