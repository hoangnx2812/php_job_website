<?php
// Chi tiết 1 job
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("
    SELECT j.*, c.name AS company_name, c.description AS company_desc,
           c.website AS company_site, c.logo AS company_logo,
           c.location AS company_location, c.id AS company_id
    FROM jobs j JOIN companies c ON c.id = j.company_id
    WHERE j.id = ?
");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { http_response_code(404); die('Không tìm thấy công việc.'); }

$u = current_user();

// Xử lý toggle lưu/bỏ lưu job
if ($u && $u['role'] === 'user' && is_post() && ($_POST['action'] ?? '') === 'toggle_save') {
    $stmt = db()->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->execute([$u['id'], $id]);
    if ($stmt->fetch()) {
        db()->prepare('DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?')->execute([$u['id'], $id]);
    } else {
        db()->prepare('INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (?,?)')->execute([$u['id'], $id]);
    }
    redirect('job_detail', ['id' => $id]);
}

// Kiểm tra đã lưu chưa
$isSaved = false;
if ($u && $u['role'] === 'user') {
    $stmt = db()->prepare('SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?');
    $stmt->execute([$u['id'], $id]);
    $isSaved = (bool)$stmt->fetch();
}

// Kiểm tra đã ứng tuyển chưa
$hasApplied = false;
if ($u && $u['role'] === 'user') {
    $stmt = db()->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$id, $u['id']]);
    $hasApplied = (bool)$stmt->fetch();
}

// Lấy tối đa 3 job khác từ cùng công ty (không tính job hiện tại)
$relatedStmt = db()->prepare("
    SELECT * FROM jobs
    WHERE company_id = ? AND id != ? AND is_active = 1
    ORDER BY created_at DESC LIMIT 3
");
$relatedStmt->execute([$j['company_id'], $id]);
$relatedJobs = $relatedStmt->fetchAll();

// Lấy số lượng đơn ứng tuyển cho job này
$appCountStmt = db()->prepare('SELECT COUNT(*) FROM applications WHERE job_id = ?');
$appCountStmt->execute([$id]);
$appCount = (int)$appCountStmt->fetchColumn();

// Tăng lượt xem mỗi lần vào trang chi tiết
db()->prepare("UPDATE jobs SET views = views + 1 WHERE id = ?")->execute([$id]);
$j['views'] = ($j['views'] ?? 0) + 1;

$pageTitle = $j['title'];
require __DIR__ . '/../layout/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= e(url('jobs')) ?>">Việc làm</a></li>
        <li class="breadcrumb-item active"><?= e($j['title']) ?></li>
    </ol>
</nav>

<div class="row g-4">
    <!-- Cột trái: chi tiết job -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <!-- Header: logo + tên + công ty -->
                <div class="d-flex gap-3 align-items-start mb-3">
                    <?php if ($j['company_logo']): ?>
                        <img src="/uploads/logos/<?= e($j['company_logo']) ?>"
                             alt="<?= e($j['company_name']) ?>"
                             style="width:72px;height:72px;object-fit:contain;border-radius:12px;border:1px solid #e2e8f0;padding:6px;background:#fff;flex-shrink:0">
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:12px;border:1px solid #e2e8f0;
                                    background:#f1f5f9;display:flex;align-items:center;justify-content:center;
                                    color:#94a3b8;font-size:2rem;flex-shrink:0">
                            <i class="bi bi-building"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="fw-700 mb-1"><?= e($j['title']) ?></h3>
                        <div class="text-muted">
                            <i class="bi bi-building me-1"></i><strong><?= e($j['company_name']) ?></strong>
                            <span class="mx-2">•</span>
                            <i class="bi bi-geo-alt me-1"></i><?= e($j['location']) ?>
                        </div>
                    </div>
                </div>

                <!-- Badges + HOT -->
                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <?php if ($j['is_hot']): ?>
                        <span class="badge-hot">HOT</span>
                    <?php endif; ?>
                    <span class="badge-salary fs-6 px-3 py-2">
                        <i class="bi bi-cash-stack me-1"></i>
                        <?= e(format_salary($j['salary_min'], $j['salary_max'])) ?>
                    </span>
                    <span class="badge-type px-3 py-2">
                        <i class="bi bi-briefcase me-1"></i><?= e($j['job_type']) ?>
                    </span>
                    <?= deadline_badge($j['expired_at'] ?? null) ?>
                </div>

                <!-- Info grid: thông tin nhanh -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>Địa điểm</div>
                            <div class="fw-600 small"><?= e($j['location'] ?: 'Không rõ') ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i>Đăng ngày</div>
                            <div class="fw-600 small"><?= time_ago($j['created_at']) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-people me-1"></i>Ứng tuyển</div>
                            <div class="fw-600 small"><?= $appCount ?> người</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-eye me-1"></i>Lượt xem</div>
                            <div class="fw-600 small"><?= format_views($j['views']) ?></div>
                        </div>
                    </div>
                    <?php if ($j['expired_at']): ?>
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-hourglass-split me-1"></i>Hạn nộp</div>
                            <div class="fw-600 small"><?= date('d/m/Y', strtotime($j['expired_at'])) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-6 col-md-4">
                        <div class="rounded-3 p-2 text-center info-box" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-muted small mb-1"><i class="bi bi-building me-1"></i>Công ty</div>
                            <div class="fw-600 small">
                                <a href="<?= e(url('company_detail', ['id' => $j['company_id']])) ?>"
                                   class="text-decoration-none text-primary">
                                    <?= e(mb_strimwidth($j['company_name'], 0, 20, '...')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kỹ năng yêu cầu (tags): click vào tag → tìm kiếm theo keyword đó -->
                <?php if (!empty($j['tags'])): ?>
                  <div class="mb-4">
                    <h6 class="fw-600 mb-2">Kỹ năng yêu cầu</h6>
                    <div class="d-flex flex-wrap gap-2">
                      <?php foreach(explode(',', $j['tags']) as $tag): ?>
                        <a href="<?= e(url('jobs', ['q' => trim($tag)])) ?>"
                           class="badge-tag text-decoration-none"><?= e(trim($tag)) ?></a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <h5 class="fw-600 mb-2">Mô tả công việc</h5>
                <div class="text-secondary mb-4" style="line-height:1.7">
                    <?= nl2br(e($j['description'])) ?>
                </div>

                <?php if ($j['requirements']): ?>
                    <h5 class="fw-600 mb-2">Yêu cầu</h5>
                    <div class="text-secondary mb-4" style="line-height:1.7">
                        <?= nl2br(e($j['requirements'])) ?>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <?php if ($u && $u['role'] === 'user'): ?>
                        <?php if ($hasApplied): ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-check-circle me-1"></i> Đã ứng tuyển
                            </button>
                        <?php else: ?>
                            <a href="<?= e(url('user/apply', ['job_id' => $j['id']])) ?>"
                               class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-send me-1"></i> Ứng tuyển ngay
                            </a>
                        <?php endif; ?>
                        <!-- Nút lưu / bỏ lưu -->
                        <form method="post">
                            <input type="hidden" name="action" value="toggle_save">
                            <button class="btn btn-lg <?= $isSaved ? 'btn-danger' : 'btn-outline-danger' ?>">
                                <i class="bi bi-heart<?= $isSaved ? '-fill' : '' ?> me-1"></i>
                                <?= $isSaved ? 'Bỏ lưu' : 'Lưu job' ?>
                            </button>
                        </form>
                    <?php elseif (!$u): ?>
                        <a href="<?= e(url('login')) ?>" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập để ứng tuyển
                        </a>
                    <?php endif; ?>

                    <!-- Nút chia sẻ job: dropdown với các lựa chọn -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-lg" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-share me-1"></i> Chia sẻ
                        </button>
                        <ul class="dropdown-menu shadow-sm">
                            <li>
                                <a class="dropdown-item" href="#" onclick="copyJobLink(); return false;">
                                    <i class="bi bi-clipboard me-2 text-secondary"></i>Copy link
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" target="_blank" rel="noopener"
                                   href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '?page=job_detail&id=' . $j['id']) ?>">
                                    <i class="bi bi-facebook me-2" style="color:#1877f2"></i>Facebook
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" target="_blank" rel="noopener"
                                   href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(BASE_URL . '?page=job_detail&id=' . $j['id']) ?>&title=<?= urlencode($j['title']) ?>">
                                    <i class="bi bi-linkedin me-2" style="color:#0a66c2"></i>LinkedIn
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <script>
                // Copy link trang hiện tại vào clipboard, hiển thị toast nếu có
                function copyJobLink() {
                    navigator.clipboard.writeText(window.location.href).then(function() {
                        if (typeof showToast === 'function') showToast('Đã copy link!', 'success');
                        else alert('Đã copy link!');
                    }).catch(function() {
                        // Fallback cho trình duyệt không hỗ trợ clipboard API
                        if (typeof showToast === 'function') showToast('Không thể copy tự động. Hãy copy URL trên thanh địa chỉ.', 'warning');
                    });
                }
                </script>
            </div>
        </div>
    </div>

    <!-- Cột phải: thông tin công ty + đã xem gần đây -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top:20px">
            <div class="card-body p-3">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-building me-2 text-primary"></i>Về công ty
                </h6>
                <div class="d-flex gap-2 align-items-center mb-2">
                    <?php if ($j['company_logo']): ?>
                        <img src="/uploads/logos/<?= e($j['company_logo']) ?>"
                             alt="<?= e($j['company_name']) ?>"
                             style="width:44px;height:44px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;padding:4px;background:#fff">
                    <?php endif; ?>
                    <h6 class="fw-600 mb-0">
                    <a href="<?= e(url('company_detail', ['id' => $j['company_id']])) ?>"
                       class="text-decoration-none text-dark">
                        <?= e($j['company_name']) ?>
                    </a>
                </h6>
                </div>
                <?php if ($j['company_location']): ?>
                    <div class="small text-muted mb-2">
                        <i class="bi bi-geo-alt me-1"></i><?= e($j['company_location']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($j['company_desc']): ?>
                    <p class="small text-secondary mb-3"><?= e(mb_strimwidth($j['company_desc'], 0, 200, '...')) ?></p>
                <?php endif; ?>
                <?php if ($j['company_site']): ?>
                    <a href="<?= e($j['company_site']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-globe me-1"></i> Xem website
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Widget jobs đã xem gần đây (render từ localStorage bằng JS) -->
        <div id="recent-jobs-widget" class="card border-0 shadow-sm rounded-3 mt-3" style="display:none">
            <div class="card-body p-3">
                <h6 class="fw-600 mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Đã xem gần đây</h6>
                <div id="recent-jobs-list"></div>
            </div>
        </div>
    </div>
</div>

<!-- JS: lưu job hiện tại vào localStorage (danh sách đã xem) -->
<script>
(function() {
    var KEY = 'recentJobs';
    // Thông tin job hiện tại để lưu vào lịch sử
    var job = { id: <?= $j['id'] ?>, title: <?= json_encode($j['title']) ?>, company: <?= json_encode($j['company_name']) ?> };
    var list = JSON.parse(localStorage.getItem(KEY) || '[]');
    // Loại bỏ job này nếu đã tồn tại để tránh trùng
    list = list.filter(function(x) { return x.id !== job.id; });
    list.unshift(job);
    // Giữ tối đa 5 jobs gần nhất
    if (list.length > 5) list = list.slice(0, 5);
    localStorage.setItem(KEY, JSON.stringify(list));
})();
</script>

<!-- JS: render widget "đã xem gần đây" sau khi trang load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var list = JSON.parse(localStorage.getItem('recentJobs') || '[]')
                 .filter(function(x) { return x.id !== <?= $j['id'] ?>; });
    if (!list.length) return;
    var widget = document.getElementById('recent-jobs-widget');
    var container = document.getElementById('recent-jobs-list');
    list.forEach(function(job) {
        var a = document.createElement('a');
        a.href = '<?= BASE_URL ?>?page=job_detail&id=' + job.id;
        a.className = 'd-block text-decoration-none mb-2 small';
        a.innerHTML = '<div class="fw-500">' + job.title + '</div>'
                    + '<div class="text-muted" style="font-size:0.75rem">' + job.company + '</div>';
        container.appendChild(a);
    });
    widget.style.display = '';
});
</script>

<?php if ($relatedJobs): ?>
<!-- Jobs khác từ cùng công ty -->
<div class="mt-5">
    <h5 class="fw-700 mb-3">
        <i class="bi bi-building me-2 text-primary"></i>Vị trí khác tại <?= e($j['company_name']) ?>
    </h5>
    <div class="row g-3">
        <?php foreach ($relatedJobs as $r): ?>
            <div class="col-md-4">
                <div class="card job-card border-0 h-100">
                    <div class="card-body p-3">
                        <h6 class="fw-600 mb-1">
                            <a href="<?= e(url('job_detail', ['id' => $r['id']])) ?>"
                               class="text-decoration-none text-dark">
                                <?= e($r['title']) ?>
                            </a>
                        </h6>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i><?= e($r['location'] ?: 'Không xác định') ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge-salary"><?= e(format_salary($r['salary_min'], $r['salary_max'])) ?></span>
                            <span class="badge-type"><?= e($r['job_type']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Sticky apply bar: xuất hiện cố định dưới màn hình khi scroll qua nút ứng tuyển chính -->
<?php if ($u && $u['role'] === 'user' && !$hasApplied): ?>
<div id="sticky-apply" style="position:fixed;bottom:0;left:0;right:0;z-index:990;
     background:#fff;border-top:1px solid #e2e8f0;padding:0.75rem 1rem;
     display:none;box-shadow:0 -4px 12px rgba(0,0,0,0.08)">
  <div class="container d-flex align-items-center justify-content-between gap-3">
    <div class="fw-600 text-truncate"><?= e($j['title']) ?> — <span class="text-muted fw-400"><?= e($j['company_name']) ?></span></div>
    <a href="<?= e(url('user/apply', ['job_id' => $j['id']])) ?>" class="btn btn-primary btn-sm px-4 flex-shrink-0">
      <i class="bi bi-send me-1"></i> Ứng tuyển ngay
    </a>
  </div>
</div>
<script>
// Hiện sticky bar khi nút ứng tuyển chính đã scroll ra khỏi viewport
(function() {
    var mainApply = document.querySelector('.btn-primary.btn-lg');
    var stickyBar = document.getElementById('sticky-apply');
    if (mainApply && stickyBar) {
        window.addEventListener('scroll', function() {
            var rect = mainApply.getBoundingClientRect();
            stickyBar.style.display = rect.bottom < 0 ? 'block' : 'none';
        });
    }
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php';
