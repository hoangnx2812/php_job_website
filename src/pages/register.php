<?php
// Trang đăng ký. Chỉ cho phép role "user" hoặc "employer".
if (current_user()) {
    redirect_by_role(current_user()['role']);
}

$error = null;
if (is_post()) {
    $email     = trim($_POST['email'] ?? '');
    $fullName  = trim($_POST['full_name'] ?? '');
    $pass      = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'user';
    if (!in_array($role, ['user', 'employer'], true)) {
        $role = 'user';
    }
    if (!$email || !$fullName || strlen($pass) < 6) {
        $error = 'Vui lòng điền đầy đủ, mật khẩu tối thiểu 6 ký tự.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (email, password, full_name, role) VALUES (?,?,?,?)');
            $stmt->execute([$email, $hash, $fullName, $role]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            flash_set('success', 'Đăng ký thành công!');
            redirect_by_role($role);
        }
    }
}

$pageTitle = 'Đăng ký';
require __DIR__ . '/../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Đăng ký tài khoản</h4>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại tài khoản</label>
                        <select name="role" class="form-select">
                            <option value="user">Ứng viên tìm việc</option>
                            <option value="employer">Nhà tuyển dụng</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Đăng ký</button>
                </form>
                <hr>
                <div class="small text-muted">Đã có tài khoản? <a href="<?= e(url('login')) ?>">Đăng nhập</a></div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layout/footer.php';
