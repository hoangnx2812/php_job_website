<?php
// Form tạo / sửa bài đăng. Nếu có ?id thì là sửa, ngược lại là tạo mới.
$u = require_role('employer');
$id = (int)($_GET['id'] ?? 0);

$job = [
    'title' => '', 'description' => '', 'requirements' => '',
    'location' => '', 'salary' => '', 'job_type' => 'full-time', 'company_id' => 0,
];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM jobs WHERE id = ? AND employer_id = ?');
    $stmt->execute([$id, $u['id']]);
    $job = $stmt->fetch();
    if (!$job) { http_response_code(404); die('Không tìm thấy bài đăng.'); }
}

// Lấy danh sách công ty của employer này (để chọn)
$stmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ? ORDER BY name');
$stmt->execute([$u['id']]);
$companies = $stmt->fetchAll();

$error = null;
if (is_post()) {
    $data = [
        'title'        => trim($_POST['title'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'requirements' => trim($_POST['requirements'] ?? ''),
        'location'     => trim($_POST['location'] ?? ''),
        'salary'       => trim($_POST['salary'] ?? ''),
        'job_type'     => $_POST['job_type'] ?? 'full-time',
        'company_id'   => (int)($_POST['company_id'] ?? 0),
    ];
    if (!$data['title'] || !$data['description'] || !$data['company_id']) {
        $error = 'Vui lòng điền đủ thông tin.';
    } else {
        if ($id) {
            $stmt = db()->prepare("UPDATE jobs SET title=?, description=?, requirements=?, location=?, salary=?, job_type=?, company_id=? WHERE id=? AND employer_id=?");
            $stmt->execute([$data['title'], $data['description'], $data['requirements'], $data['location'], $data['salary'], $data['job_type'], $data['company_id'], $id, $u['id']]);
            flash_set('success', 'Đã cập nhật bài đăng.');
        } else {
            $stmt = db()->prepare("INSERT INTO jobs (company_id, employer_id, title, description, requirements, location, salary, job_type) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['company_id'], $u['id'], $data['title'], $data['description'], $data['requirements'], $data['location'], $data['salary'], $data['job_type']]);
            flash_set('success', 'Đã tạo bài đăng mới.');
        }
        redirect('employer/jobs');
    }
    $job = array_merge($job, $data);
}

$pageTitle = $id ? 'Sửa bài đăng' : 'Đăng bài mới';
require __DIR__ . '/../../layout/header.php';
?>
<h3><?= $id ? 'Sửa bài đăng' : 'Đăng bài mới' ?></h3>
<?php if (!$companies): ?>
    <div class="alert alert-warning">
        Bạn chưa có công ty nào. Vui lòng liên hệ admin để được cấp công ty.
    </div>
<?php else: ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="card card-body shadow-sm">
    <div class="mb-3">
        <label class="form-label">Công ty</label>
        <select name="company_id" class="form-select" required>
            <option value="">-- Chọn --</option>
            <?php foreach ($companies as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $job['company_id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Vị trí</label>
        <input name="title" value="<?= e($job['title']) ?>" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Mô tả</label>
        <textarea name="description" class="form-control" rows="5" required><?= e($job['description']) ?></textarea></div>
    <div class="mb-3"><label class="form-label">Yêu cầu</label>
        <textarea name="requirements" class="form-control" rows="3"><?= e($job['requirements']) ?></textarea></div>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Địa điểm</label>
            <input name="location" value="<?= e($job['location']) ?>" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Mức lương</label>
            <input name="salary" value="<?= e($job['salary']) ?>" class="form-control" placeholder="15-25 triệu"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Loại</label>
            <select name="job_type" class="form-select">
                <?php foreach (['full-time','part-time','intern','contract'] as $t): ?>
                    <option value="<?= $t ?>" <?= $t == $job['job_type'] ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary">Lưu</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
