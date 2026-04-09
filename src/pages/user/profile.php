<?php
// Trang profile: sửa thông tin cá nhân + đổi mật khẩu
// Dùng được cho cả 3 role: user, employer, admin
$u = require_role('user', 'employer', 'admin');

$errorInfo = null;
$errorPass = null;

if (is_post()) {
    $action = $_POST['action'] ?? 'update_info';

    if ($action === 'update_info') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if (!$fullName || !$email) {
            $errorInfo = 'Vui lòng điền đủ họ tên và email.';
        } else {
            // Kiểm tra email đã bị dùng bởi user khác chưa
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $u['id']]);
            if ($stmt->fetch()) {
                $errorInfo = 'Email này đã được sử dụng bởi tài khoản khác.';
            } else {
                db()->prepare('UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?')
                   ->execute([$fullName, $phone ?: null, $email, $u['id']]);
                flash_set('success', 'Đã cập nhật thông tin cá nhân.');
                redirect('user/profile');
            }
        }

    } elseif ($action === 'change_password') {
        $oldPass  = $_POST['old_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$oldPass || !$newPass || !$confirm) {
            $errorPass = 'Vui lòng điền đầy đủ các trường mật khẩu.';
        } elseif (strlen($newPass) < 6) {
            $errorPass = 'Mật khẩu mới tối thiểu 6 ký tự.';
        } elseif ($newPass !== $confirm) {
            $errorPass = 'Xác nhận mật khẩu không khớp.';
        } else {
            // Xác thực mật khẩu cũ (hỗ trợ cả PLAIN: seed lẫn hash thật)
            if (!verify_and_upgrade_password($u, $oldPass)) {
                $errorPass = 'Mật khẩu cũ không đúng.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $u['id']]);
                flash_set('success', 'Đã đổi mật khẩu thành công.');
                redirect('user/profile');
            }
        }
    }
}

// Reload user sau khi cập nhật
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$u['id']]);
$u = $stmt->fetch();

$pageTitle = 'Hồ sơ cá nhân';
require __DIR__ . '/../../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-700 mb-4">
            <i class="bi bi-person-circle me-2 text-primary"></i>Hồ sơ cá nhân
        </h4>

        <!-- Card thông tin cơ bản -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person text-primary"></i> Thông tin cơ bản
                    <span class="badge bg-<?= ['admin'=>'danger','employer'=>'warning text-dark','user'=>'success'][$u['role']] ?? 'secondary' ?> ms-auto">
                        <?= e($u['role']) ?>
                    </span>
                </h6>
                <?php if ($errorInfo): ?>
                    <div class="alert alert-danger"><?= e($errorInfo) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="action" value="update_info">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Họ và tên <span class="text-danger">*</span></label>
                            <input name="full_name" value="<?= e($u['full_name']) ?>"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Số điện thoại</label>
                            <input name="phone" value="<?= e($u['phone'] ?? '') ?>"
                                   class="form-control" placeholder="0900000000">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="<?= e($u['email']) ?>"
                                   class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card đổi mật khẩu -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-lock text-primary me-2"></i>Đổi mật khẩu
                </h6>
                <?php if ($errorPass): ?>
                    <div class="alert alert-danger"><?= e($errorPass) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label fw-500">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control"
                                   minlength="6" required placeholder="Tối thiểu 6 ký tự">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control"
                                   minlength="6" required>
                        </div>
                    </div>
                    <button class="btn btn-warning px-4">
                        <i class="bi bi-shield-lock me-1"></i> Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
