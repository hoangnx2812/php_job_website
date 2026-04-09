<?php
// Trang đăng ký. Chỉ tạo tài khoản role=user.
// Muốn trở thành employer phải vào trang become_employer.php sau khi đăng ký.
if (current_user()) {
    redirect_by_role(current_user()['role']);
}

$error = null;
if (is_post()) {
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    if (!$email || !$fullName || strlen($pass) < 6) {
        $error = 'Vui lòng điền đầy đủ, mật khẩu tối thiểu 6 ký tự.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (email, password, full_name, role, phone) VALUES (?,?,?,?,?)');
            $stmt->execute([$email, $hash, $fullName, 'user', $phone ?: null]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            flash_set('success', 'Đăng ký thành công! Chào mừng bạn đến với JobVN.');
            redirect('home');
        }
    }
}

$pageTitle = 'Đăng ký';
require __DIR__ . '/../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0" style="border-radius:14px">
            <div class="card-body p-4">
                <h4 class="mb-1 fw-700">Tạo tài khoản mới</h4>
                <p class="text-muted small mb-4">Tham gia JobVN để tìm kiếm việc làm phù hợp</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Họ và tên <span class="text-danger">*</span></label>
                            <input name="full_name" class="form-control" placeholder="Nguyễn Văn A"
                                   value="<?= e($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Số điện thoại</label>
                            <input name="phone" class="form-control" placeholder="0900000000"
                                   value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com"
                               value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6"
                               placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                    <button class="btn btn-primary w-100 mt-2" style="padding:0.65rem">
                        <i class="bi bi-person-check me-1"></i> Đăng ký
                    </button>
                </form>
                <hr>
                <div class="small text-muted text-center">
                    Đã có tài khoản? <a href="<?= e(url('login')) ?>">Đăng nhập</a>
                </div>
                <div class="small text-muted text-center mt-1">
                    Muốn đăng tuyển? <a href="<?= e(url('user/become_employer')) ?>">Đăng ký làm nhà tuyển dụng</a> sau khi đăng nhập.
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php';
