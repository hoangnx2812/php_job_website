<?php
// Ứng viên ứng tuyển 1 job, upload file CV từ local
$u = require_role('user');
$jobId = (int)($_GET['job_id'] ?? 0);

$stmt = db()->prepare('SELECT j.*, c.name AS company_name FROM jobs j JOIN companies c ON c.id = j.company_id WHERE j.id = ?');
$stmt->execute([$jobId]);
$job = $stmt->fetch();
if (!$job) { http_response_code(404); die('Không tìm thấy công việc.'); }

// Đã ứng tuyển rồi thì không cho ứng tuyển lại
$stmt = db()->prepare('SELECT id FROM applications WHERE job_id = ? AND user_id = ?');
$stmt->execute([$jobId, $u['id']]);
$existing = $stmt->fetch();

$error = null;
if (is_post() && !$existing) {
    $letter = trim($_POST['cover_letter'] ?? '');
    if (empty($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Vui lòng chọn file CV.';
    } else {
        $f = $_FILES['cv'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            $error = 'Chỉ chấp nhận file PDF, DOC, DOCX.';
        } elseif ($f['size'] > 5 * 1024 * 1024) {
            $error = 'File tối đa 5MB.';
        } else {
            if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0777, true);
            $newName = 'cv_' . $u['id'] . '_' . time() . '.' . $ext;
            $dest = UPLOAD_DIR . '/' . $newName;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $stmt = db()->prepare('INSERT INTO applications (job_id, user_id, cv_file, cover_letter) VALUES (?,?,?,?)');
                $stmt->execute([$jobId, $u['id'], $newName, $letter]);

                // Thông báo cho employer: có đơn ứng tuyển mới vào bài đăng của họ
                notify(
                    (int)$job['employer_id'],
                    'new_application',
                    "Có đơn ứng tuyển mới cho bài «{$job['title']}» từ {$u['full_name']}",
                    url('employer/applications', ['job_id' => $jobId])
                );

                flash_set('success', 'Đã gửi đơn ứng tuyển.');
                redirect('user/my_applications');
            } else {
                $error = 'Không lưu được file.';
            }
        }
    }
}

$pageTitle = 'Ứng tuyển';
require __DIR__ . '/../../layout/header.php';
?>
<h3>Ứng tuyển: <?= e($job['title']) ?></h3>
<div class="text-muted mb-3"><?= e($job['company_name']) ?> • <?= e($job['location']) ?></div>

<?php if ($existing): ?>
    <div class="alert alert-info">Bạn đã ứng tuyển vị trí này rồi.</div>
    <a href="<?= e(url('user/my_applications')) ?>" class="btn btn-secondary">Xem đơn của tôi</a>
<?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="card card-body shadow-sm">
        <div class="mb-3">
            <label class="form-label">File CV (PDF/DOC/DOCX, tối đa 5MB)</label>
            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Thư xin việc (tuỳ chọn)</label>
            <textarea name="cover_letter" class="form-control" rows="5"></textarea>
        </div>
        <button class="btn btn-primary">Gửi đơn ứng tuyển</button>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
