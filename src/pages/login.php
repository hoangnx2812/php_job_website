<?php
// Trang đăng nhập
if (current_user()) {
    redirect_by_role(current_user()['role']);
}

$error = null;
if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
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
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Đăng nhập</h4>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Đăng nhập</button>
                </form>
                <hr>
                <div class="small text-muted">
                    Chưa có tài khoản? <a href="<?= e(url('register')) ?>">Đăng ký</a><br>
                    <b>Tài khoản demo (mật khẩu: <code>123456</code>):</b><br>
                    admin@example.com / employer1@example.com / user1@example.com
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php';
