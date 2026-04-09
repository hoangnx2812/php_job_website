<?php
// Trang đăng ký làm nhà tuyển dụng.
// Chỉ user role=user mới vào được.
// Sau khi submit → tạo employer_request với status=pending, admin duyệt sau.
$u = require_role('user');

// Kiểm tra user đã có request chưa
$stmt = db()->prepare('SELECT * FROM employer_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$u['id']]);
$existing = $stmt->fetch();

$error = null;
if (is_post() && (!$existing || $existing['status'] === 'rejected')) {
    $companyName = trim($_POST['company_name'] ?? '');
    $companyDesc = trim($_POST['company_description'] ?? '');
    $companyLoc  = trim($_POST['company_location'] ?? '');
    $companyWeb  = trim($_POST['company_website'] ?? '');

    if (!$companyName) {
        $error = 'Vui lòng nhập tên công ty.';
    } else {
        $stmt = db()->prepare('
            INSERT INTO employer_requests (user_id, company_name, company_description, company_location, company_website)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$u['id'], $companyName, $companyDesc ?: null, $companyLoc ?: null, $companyWeb ?: null]);
        flash_set('success', 'Yêu cầu của bạn đã được gửi. Vui lòng chờ admin duyệt.');
        redirect('user/become_employer');
    }
}

// Lấy lại sau redirect để hiển thị trạng thái mới nhất
$stmt = db()->prepare('SELECT * FROM employer_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$u['id']]);
$existing = $stmt->fetch();

$pageTitle = 'Đăng ký làm nhà tuyển dụng';
require __DIR__ . '/../../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">

        <?php if ($existing && $existing['status'] === 'pending'): ?>
            <!-- Đang chờ duyệt -->
            <div class="card border-0 shadow-sm text-center p-5">
                <div class="mb-3" style="font-size:3rem">⏳</div>
                <h4>Yêu cầu đang chờ duyệt</h4>
                <p class="text-muted">
                    Bạn đã gửi yêu cầu trở thành nhà tuyển dụng với công ty
                    <strong><?= e($existing['company_name']) ?></strong>.<br>
                    Admin sẽ xem xét và phản hồi sớm nhất có thể.
                </p>
                <small class="text-muted">Gửi lúc: <?= e($existing['created_at']) ?></small>
            </div>

        <?php else: ?>
            <!-- Chưa gửi hoặc bị từ chối → hiện form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-1 fw-700">
                        <i class="bi bi-building-add text-primary me-2"></i>Đăng ký làm nhà tuyển dụng
                    </h4>
                    <p class="text-muted small mb-4">
                        Điền thông tin công ty. Admin sẽ xem xét và duyệt trong thời gian sớm nhất.
                    </p>

                    <?php if ($existing && $existing['status'] === 'rejected'): ?>
                        <div class="alert alert-danger">
                            <strong>Yêu cầu trước đã bị từ chối.</strong>
                            <?php if ($existing['admin_note']): ?>
                                Lý do: <?= e($existing['admin_note']) ?>
                            <?php endif; ?>
                            <br><small>Bạn có thể gửi lại yêu cầu mới bên dưới.</small>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                            <input name="company_name" class="form-control"
                                   placeholder="Tên công ty của bạn"
                                   value="<?= e($_POST['company_name'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Địa điểm</label>
                                <input name="company_location" class="form-control"
                                       placeholder="Hà Nội / TP. HCM..."
                                       value="<?= e($_POST['company_location'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Website</label>
                                <input name="company_website" class="form-control"
                                       placeholder="https://..."
                                       value="<?= e($_POST['company_website'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-500">Mô tả công ty</label>
                            <textarea name="company_description" class="form-control" rows="4"
                                      placeholder="Giới thiệu ngắn về công ty..."><?= e($_POST['company_description'] ?? '') ?></textarea>
                        </div>
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i> Gửi yêu cầu
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
