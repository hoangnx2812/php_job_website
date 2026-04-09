<?php
// Trang đăng nhập
if (current_user()) {
    redirect_by_role(current_user()['role']);
}

$error = null;
if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt  = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && verify_and_upgrade_password($user, $pass)) {
        $_SESSION['user_id'] = $user['id'];
        flash_set('success', 'Đăng nhập thành công!');
        redirect_by_role($user['role']);
    }
    $error = 'Email hoặc mật khẩu không đúng.';
}

$pageTitle = 'Đăng nhập';
require __DIR__ . '/../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-2"
                         style="width:52px;height:52px;background:linear-gradient(135deg,#1a56db,#0d3b8e)">
                        <i class="bi bi-briefcase-fill text-white fs-4"></i>
                    </div>
                    <h4 class="fw-700 mb-1">Chào mừng trở lại</h4>
                    <p class="text-muted small mb-0">Đăng nhập vào tài khoản JobVN của bạn</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-500">Email</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="email@example.com" required
                               value="<?= e(is_post() ? ($_POST['email'] ?? '') : '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Mật khẩu</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                    </div>
                    <button class="btn btn-primary w-100" style="padding:0.6rem">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                    </button>
                </form>

                <hr>
                <div class="small text-muted">
                    Chưa có tài khoản? <a href="<?= e(url('register')) ?>">Đăng ký ngay</a>
                </div>
                <details class="mt-2">
                    <summary class="small text-muted cursor-pointer">Tài khoản demo</summary>
                    <div class="mt-2 p-2 rounded" style="background:#f8fafc;font-size:0.8rem">
                        Mật khẩu tất cả: <code>123456</code><br>
                        <strong>Admin:</strong> admin@example.com<br>
                        <strong>Employer:</strong> employer1@example.com<br>
                        <strong>Ứng viên:</strong> user1@example.com
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php';
