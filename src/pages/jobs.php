<?php
// Danh sách việc làm + tìm kiếm + lọc nâng cao + phân trang + nút lưu
$q        = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$jobType  = $_GET['job_type'] ?? '';
$category = $_GET['category'] ?? '';
$salMin   = (int)($_GET['salary_min'] ?? 0);
$salMax   = (int)($_GET['salary_max'] ?? 0);
$sort     = $_GET['sort'] ?? 'newest';
$page     = max(1, (int)($_GET['p'] ?? 1));
$perPage  = 10;

// Danh sách lĩnh vực hợp lệ
$validCategories = ['Công nghệ thông tin', 'Marketing', 'Thiết kế', 'Tài chính', 'HR', 'Bán hàng', 'Vận hành', 'Khác'];

// Xử lý lưu/bỏ lưu job (toggle save)
$u = current_user();
if ($u && $u['role'] === 'user' && is_post() && isset($_POST['job_id'])) {
    $saveJobId = (int)$_POST['job_id'];
    // Kiểm tra đã lưu chưa
    $stmt = db()->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->execute([$u['id'], $saveJobId]);
    if ($stmt->fetch()) {
        // Đã lưu → bỏ lưu
        db()->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$u['id'], $saveJobId]);
    } else {
        // Chưa lưu → lưu
        db()->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?,?)')->execute([$u['id'], $saveJobId]);
    }
    // Redirect lại trang hiện tại để tránh resubmit form
    $params = array_filter(compact('q', 'location', 'jobType', 'category', 'salMin', 'salMax', 'sort'));
    if ($page > 1) $params['p'] = $page;
    redirect('jobs', $params);
}

// Build query đếm tổng (để phân trang)
$sql    = "SELECT j.*, c.name AS company_name, c.logo AS company_logo
           FROM jobs j JOIN companies c ON c.id = j.company_id
           WHERE j.is_active = 1";
$params = [];
if ($q !== '') {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($location !== '') {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}
if ($jobType && in_array($jobType, ['full-time','part-time','intern','contract'], true)) {
    $sql .= " AND j.job_type = ?";
    $params[] = $jobType;
}
// Lọc theo lĩnh vực
if ($category && in_array($category, $validCategories, true)) {
    $sql .= " AND j.category = ?";
    $params[] = $category;
}
// Filter salary: job.salary_min >= filter.salary_min VÀ salary_max <= filter.salary_max
if ($salMin > 0) {
    $sql .= " AND (j.salary_min IS NULL OR j.salary_min >= ?)";
    $params[] = $salMin;
}
if ($salMax > 0) {
    $sql .= " AND (j.salary_max IS NULL OR j.salary_max <= ?)";
    $params[] = $salMax;
}

// Đếm tổng bản ghi để tính số trang
$countSql  = str_replace('SELECT j.*, c.name AS company_name, c.logo AS company_logo', 'SELECT COUNT(*)', $sql);
$countStmt = db()->prepare($countSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Ánh xạ sort param sang ORDER BY
$orderBy = match($sort) {
    'salary_desc' => 'j.salary_max DESC, j.salary_min DESC',
    'views_desc'  => 'j.views DESC',
    default       => 'j.is_hot DESC, j.created_at DESC',  // newest: hot jobs nổi lên đầu
};

// Query lấy dữ liệu trang hiện tại
$sql .= " ORDER BY $orderBy LIMIT $perPage OFFSET " . (($page - 1) * $perPage);
$stmt = db()->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Lấy danh sách job đã lưu của user để hiển thị trạng thái nút tim
$savedJobIds = [];
if ($u && $u['role'] === 'user') {
    $sStmt = db()->prepare('SELECT job_id FROM saved_jobs WHERE user_id = ?');
    $sStmt->execute([$u['id']]);
    $savedJobIds = array_column($sStmt->fetchAll(), 'job_id');
}

// Build base URL cho pagination (không có p=)
$baseParams = array_filter(
    compact('q', 'location', 'jobType', 'category', 'salMin', 'salMax', 'sort'),
    fn($v) => $v !== '' && $v !== 0 && $v !== 'newest'
);
$baseUrl    = BASE_URL . '?' . http_build_query(array_merge(['page' => 'jobs'], $baseParams));

$pageTitle = 'Việc làm';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h4 class="fw-700 mb-0"><i class="bi bi-search me-2 text-primary"></i>Danh sach viec lam</h4>
    <!-- Dòng kết quả + nút toggle grid/list view -->
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small"><?= $total ?> ket qua</span>
        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" id="btn-grid" title="Dang luoi" onclick="setView('grid')">
                <i class="bi bi-grid"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-list" title="Dang danh sach" onclick="setView('list')">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
    </div>
</div>

<!-- Filter bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="get">
            <input type="hidden" name="page" value="jobs">
            <div class="row g-2">
                <div class="col-md-3">
                    <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Từ khoá...">
                </div>
                <div class="col-md-2">
                    <input name="location" value="<?= e($location) ?>" class="form-control" placeholder="Địa điểm">
                </div>
                <!-- Dropdown lĩnh vực -->
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">-- Lĩnh vực --</option>
                        <?php foreach ($validCategories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="job_type" class="form-select">
                        <option value="">-- Loại --</option>
                        <?php foreach (['full-time','part-time','intern','contract'] as $t): ?>
                            <option value="<?= $t ?>" <?= $jobType === $t ? 'selected' : '' ?>>
                                <?= $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="salary_min" value="<?= $salMin ?: '' ?>"
                           class="form-control" placeholder="Lmin" min="0" title="Lương tối thiểu (tr/tháng)">
                </div>
                <div class="col-md-1">
                    <input type="number" name="salary_max" value="<?= $salMax ?: '' ?>"
                           class="form-control" placeholder="Lmax" min="0" title="Lương tối đa (tr/tháng)">
                </div>
                <div class="col-md-1">
                    <select name="sort" class="form-select">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="salary_desc" <?= $sort === 'salary_desc' ? 'selected' : '' ?>>Lương cao</option>
                        <option value="views_desc" <?= $sort === 'views_desc' ? 'selected' : '' ?>>Nhiều view</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-1">
                    <button class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($q || $location || $jobType || $category || $salMin || $salMax): ?>
                        <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-secondary" title="Xóa filter">
                            <i class="bi bi-x"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!$jobs): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Không tìm thấy việc làm phù hợp.
    </div>
<?php endif; ?>

<!-- Skeleton cards hiện khi đang load (ẩn ngay sau DOMContentLoaded) -->
<div id="skeleton-grid" class="row g-3 mb-4">
    <?php for($i=0;$i<4;$i++): ?>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex gap-3">
                <div class="skeleton flex-shrink-0" style="width:56px;height:56px;border-radius:10px"></div>
                <div class="flex-grow-1">
                    <div class="skeleton mb-2" style="height:18px;width:70%"></div>
                    <div class="skeleton mb-2" style="height:14px;width:50%"></div>
                    <div class="skeleton" style="height:24px;width:40%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Real content (ẩn ban đầu, hiện sau DOMContentLoaded) -->
<div id="jobs-grid" class="row g-3 mb-4" style="display:none">
<?php foreach ($jobs as $j): ?>
    <?php $isSaved = in_array($j['id'], $savedJobIds); ?>
    <div class="col-md-6">
        <div class="card job-card h-100 position-relative <?= $j['is_hot'] ? 'hot' : '' ?>">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <!-- Logo -->
                    <?php if ($j['company_logo']): ?>
                        <img src="/uploads/logos/<?= e($j['company_logo']) ?>"
                             alt="<?= e($j['company_name']) ?>"
                             class="company-logo flex-shrink-0">
                    <?php else: ?>
                        <div class="company-logo-placeholder flex-shrink-0">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-600 mb-1 me-2">
                                <a href="<?= e(url('job_detail', ['id' => $j['id']])) ?>"
                                   class="text-decoration-none text-dark stretched-link">
                                    <?= e($j['title']) ?>
                                </a>
                                <?php if ($j['is_hot']): ?>
                                    <span class="badge-hot ms-1">HOT</span>
                                <?php endif; ?>
                            </h6>
                            <!-- Nút tim (chỉ hiện khi đã login + role=user) -->
                            <?php if ($u && $u['role'] === 'user'): ?>
                                <form method="post" class="position-relative" style="z-index:2">
                                    <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                                    <?php foreach ($baseParams as $k => $v): ?>
                                        <?php if ($k !== 'p'): ?>
                                            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($page > 1): ?>
                                        <input type="hidden" name="p" value="<?= $page ?>">
                                    <?php endif; ?>
                                    <button class="btn-save-job <?= $isSaved ? 'saved' : '' ?>"
                                            title="<?= $isSaved ? 'Bỏ lưu' : 'Lưu job' ?>">
                                        <i class="bi bi-heart<?= $isSaved ? '-fill' : '' ?>"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-building me-1"></i><?= e($j['company_name']) ?>
                            <span class="mx-1">•</span>
                            <i class="bi bi-geo-alt me-1"></i><?= e($j['location']) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <span class="badge-salary"><?= e(format_salary($j['salary_min'], $j['salary_max'])) ?></span>
                            <span class="badge-type"><?= e($j['job_type']) ?></span>
                            <?php if (!empty($j['category'])): ?>
                                <span class="badge-category"><?= e($j['category']) ?></span>
                            <?php endif; ?>
                            <?= deadline_badge($j['expired_at'] ?? null) ?>
                        </div>
                        <!-- Hiển thị tối đa 3 tags kỹ năng -->
                        <?php if (!empty($j['tags'])): ?>
                          <div class="d-flex flex-wrap gap-1 mt-1">
                            <?php foreach(array_slice(explode(',', $j['tags']), 0, 3) as $tag): ?>
                              <span class="badge-tag"><?= e(trim($tag)) ?></span>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
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
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- Phân trang -->
<?= render_pagination($total, $perPage, $page, $baseUrl) ?>

<script>
// Chuyển đổi giữa dạng lưới và danh sách, lưu preference vào localStorage
function setView(v) {
    localStorage.setItem('jobsView', v);
    var grid = document.getElementById('jobs-grid');
    if (grid) {
        grid.className = v === 'list' ? 'row g-2 mb-4 list-view' : 'row g-3 mb-4';
    }
    // Cập nhật trạng thái active cho 2 nút toggle
    var btnGrid = document.getElementById('btn-grid');
    var btnList = document.getElementById('btn-list');
    if (btnGrid) btnGrid.classList.toggle('active', v === 'grid');
    if (btnList) btnList.classList.toggle('active', v === 'list');
}
document.addEventListener('DOMContentLoaded', function() {
    // Ẩn skeleton, hiện content thật
    var skeleton = document.getElementById('skeleton-grid');
    var grid = document.getElementById('jobs-grid');
    if (skeleton) skeleton.style.display = 'none';
    if (grid) grid.style.display = '';
    // Áp dụng view đã lưu
    setView(localStorage.getItem('jobsView') || 'grid');
});
</script>

<?php require __DIR__ . '/../layout/footer.php';
