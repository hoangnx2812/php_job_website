<?php
// Trang để user thường nâng cấp lên employer
// Chỉ user role=user mới vào được trang này
$u = require_role('user');

$error = null;

if (is_post()) {
    $companyName = trim($_POST['company_name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $website     = trim($_POST['website'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$companyName) {
        $error = 'Vui lòng nhập tên công ty.';
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Tạo công ty với owner là user hiện tại
            $pdo->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)')
               ->execute([$u['id'], $companyName, $description ?: null, $location ?: null, $website ?: null]);

            // Nâng cấp role lên employer
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute(['employer', $u['id']]);

            $pdo->commit();

            // Cập nhật session
            $_SESSION['user_id'] = $u['id'];
            flash_set('success', 'Chúc mừng! Tài khoản của bạn đã được nâng cấp thành Nhà tuyển dụng.');
            // Redirect về dashboard employer
            header('Location: ' . BASE_URL . '?page=employer/dashboard');
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $error = 'Đã xảy ra lỗi. Vui lòng thử lại.';
        }
    }
}

$pageTitle = 'Trở thành nhà tuyển dụng';
require __DIR__ . '/../../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <!-- Banner giới thiệu -->
        <div class="card border-0 rounded-3 mb-4 text-white"
             style="background: linear-gradient(135deg, #1a56db 0%, #0d3b8e 100%)">
            <div class="card-body p-4 text-center">
                <i class="bi bi-building-check" style="font-size:2.5rem;opacity:0.8"></i>
                <h4 class="fw-700 mt-2 mb-1">Trở thành Nhà tuyển dụng</h4>
                <p class="mb-0 opacity-85 small">
                    Đăng tin tuyển dụng, tiếp cận hàng nghìn ứng viên tiềm năng
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">Thông tin công ty của bạn</h6>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                        <input name="company_name" class="form-control" required
                               placeholder="Tên công ty của bạn"
                               value="<?= e(is_post() ? ($_POST['company_name'] ?? '') : '') ?>">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Địa điểm</label>
                            <input name="location" class="form-control" placeholder="Hà Nội, TP.HCM..."
                                   value="<?= e(is_post() ? ($_POST['location'] ?? '') : '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Website</label>
                            <input name="website" class="form-control" placeholder="https://..."
                                   value="<?= e(is_post() ? ($_POST['website'] ?? '') : '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Mô tả công ty</label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Giới thiệu ngắn về công ty..."><?= e(is_post() ? ($_POST['description'] ?? '') : '') ?></textarea>
                    </div>

                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Sau khi xác nhận, tài khoản của bạn sẽ được nâng cấp lên <strong>Nhà tuyển dụng</strong>.
                        Tính năng ứng tuyển sẽ không còn khả dụng.
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary px-4">
                            <i class="bi bi-building-check me-1"></i> Xác nhận nâng cấp
                        </button>
                        <a href="<?= e(url('home')) ?>" class="btn btn-outline-secondary">Huỷ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
