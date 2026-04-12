<?php
// Form tạo / sửa bài đăng. Nếu có ?id thì là sửa, ngược lại là tạo mới.
$u  = require_role('employer');
$id = (int)($_GET['id'] ?? 0);

// Lấy công ty của employer này (quan hệ 1-1)
$companyStmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ?');
$companyStmt->execute([$u['id']]);
$company = $companyStmt->fetch();

$job = [
    'title'        => '',
    'description'  => '',
    'requirements' => '',
    'location'     => '',
    'salary_min'   => '',
    'salary_max'   => '',
    'job_type'     => 'full-time',
    'company_id'   => $company ? $company['id'] : 0,
    'expired_at'   => '',
    'is_hot'       => 0,
];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->execute([$id, $u['id']]);
    $existing = $stmt->fetch();
    if (!$existing) { http_response_code(404); die('Không tìm thấy bài đăng.'); }
    $job = $existing;
}

$error = null;
if (is_post()) {
    if (!$company) {
        $error = 'Bạn chưa có công ty. Vui lòng tạo công ty trước.';
    } else {
        $data = [
            'title'        => trim($_POST['title'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'requirements' => trim($_POST['requirements'] ?? ''),
            'location'     => trim($_POST['location'] ?? ''),
            'salary_min'   => $_POST['salary_min'] !== '' ? (int)$_POST['salary_min'] : null,
            'salary_max'   => $_POST['salary_max'] !== '' ? (int)$_POST['salary_max'] : null,
            'job_type'     => $_POST['job_type'] ?? 'full-time',
            'company_id'   => $company['id'],
            'is_hot'       => isset($_POST['is_hot']) ? 1 : 0,
            'expired_at'   => trim($_POST['expired_at'] ?? '') ?: null,
        ];

        if (!$data['title'] || !$data['description']) {
            $error = 'Vui lòng điền đủ tiêu đề và mô tả.';
        } else {
            if ($id) {
                $stmt = db()->prepare("UPDATE jobs SET title=?, description=?, requirements=?, location=?, salary_min=?, salary_max=?, job_type=?, company_id=?, is_hot=?, expired_at=? WHERE id=? AND employer_id=?");
                $stmt->execute([$data['title'], $data['description'], $data['requirements'], $data['location'], $data['salary_min'], $data['salary_max'], $data['job_type'], $data['company_id'], $data['is_hot'], $data['expired_at'], $id, $u['id']]);
                flash_set('success', 'Đã cập nhật bài đăng.');
            } else {
                $stmt = db()->prepare("INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary_min, salary_max, job_type, is_hot, expired_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$data['company_id'], $u['id'], $data['title'], $data['description'], $data['requirements'], $data['location'], $data['salary_min'], $data['salary_max'], $data['job_type'], $data['is_hot'], $data['expired_at']]);
                flash_set('success', 'Đã tạo bài đăng mới.');
            }
            redirect('employer/jobs');
        }
        // Giữ lại dữ liệu đã nhập khi có lỗi
        $job = array_merge($job, $data, ['salary_min' => $data['salary_min'] ?? '', 'salary_max' => $data['salary_max'] ?? '']);
    }
}

$pageTitle = $id ? 'Sửa bài đăng' : 'Đăng bài mới';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-<?= $id ? 'pencil' : 'plus-circle' ?> me-2 text-primary"></i>
        <?= $id ? 'Sửa bài đăng' : 'Đăng bài mới' ?>
    </h4>
    <a href="<?= e(url('employer/jobs')) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<?php if (!$company): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Bạn chưa có công ty. Vui lòng
        <a href="<?= e(url('employer/company')) ?>">tạo thông tin công ty</a> trước khi đăng bài.
    </div>
<?php else: ?>
    <!-- Hiển thị công ty đang đăng -->
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-building"></i>
        Đăng bài cho công ty: <strong><?= e($company['name']) ?></strong>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-500">Vị trí tuyển dụng <span class="text-danger">*</span></label>
                    <input name="title" value="<?= e($job['title']) ?>" class="form-control"
                           placeholder="Vd: PHP Backend Developer" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Mô tả công việc <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="6" required
                              placeholder="Mô tả chi tiết về công việc, trách nhiệm..."><?= e($job['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Yêu cầu ứng viên</label>
                    <textarea name="requirements" class="form-control" rows="4"
                              placeholder="Kỹ năng, kinh nghiệm yêu cầu..."><?= e($job['requirements']) ?></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-500">Địa điểm</label>
                        <input name="location" value="<?= e($job['location']) ?>" class="form-control"
                               placeholder="Hà Nội, TP.HCM...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-500">Lương Min (triệu)</label>
                        <input type="number" name="salary_min" value="<?= e($job['salary_min']) ?>"
                               class="form-control" placeholder="15" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-500">Lương Max (triệu)</label>
                        <input type="number" name="salary_max" value="<?= e($job['salary_max']) ?>"
                               class="form-control" placeholder="25" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-500">Loại hình</label>
                        <select name="job_type" class="form-select">
                            <?php foreach (['full-time','part-time','intern','contract'] as $t): ?>
                                <option value="<?= $t ?>" <?= $t == $job['job_type'] ? 'selected' : '' ?>>
                                    <?= $t ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-500">Hạn nộp hồ sơ</label>
                        <input type="date" name="expired_at"
                               value="<?= e($job['expired_at'] ? date('Y-m-d', strtotime($job['expired_at'])) : '') ?>"
                               class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_hot" id="isHot"
                               <?= $job['is_hot'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-500" for="isHot">
                            <span class="badge-hot ms-1">HOT</span> Đánh dấu là việc làm nổi bật
                        </label>
                    </div>
                </div>
                <button class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i>
                    <?= $id ? 'Cập nhật' : 'Đăng bài' ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
