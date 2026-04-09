<?php
// Trang đăng ký. Chỉ cho phép role "user" hoặc "employer".
if (current_user()) {
    redirect_by_role(current_user()['role']);
}

$error = null;
if (is_post()) {
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $role     = $_POST['role'] ?? 'user';
    if (!in_array($role, ['user', 'employer'], true)) {
        $role = 'user';
    }

    // Thông tin công ty (chỉ cần khi role=employer)
    $companyName = trim($_POST['company_name'] ?? '');
    $companyLoc  = trim($_POST['company_location'] ?? '');
    $companyWeb  = trim($_POST['company_website'] ?? '');
    $companyDesc = trim($_POST['company_description'] ?? '');

    if (!$email || !$fullName || strlen($pass) < 6) {
        $error = 'Vui lòng điền đầy đủ, mật khẩu tối thiểu 6 ký tự.';
    } elseif ($role === 'employer' && !$companyName) {
        $error = 'Vui lòng nhập tên công ty.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (email, password, full_name, role, phone) VALUES (?,?,?,?,?)');
                $stmt->execute([$email, $hash, $fullName, $role, $phone ?: null]);
                $newUserId = (int)$pdo->lastInsertId();

                // Nếu là employer: tạo công ty luôn trong cùng transaction
                if ($role === 'employer') {
                    $stmt = $pdo->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)');
                    $stmt->execute([$newUserId, $companyName, $companyDesc ?: null, $companyLoc ?: null, $companyWeb ?: null]);
                }

                $pdo->commit();
                $_SESSION['user_id'] = $newUserId;
                flash_set('success', 'Đăng ký thành công! Chào mừng bạn đến với JobVN.');
                redirect_by_role($role);
            } catch (Exception $ex) {
                $pdo->rollBack();
                $error = 'Đã xảy ra lỗi, vui lòng thử lại.';
            }
        }
    }
}

$pageTitle = 'Đăng ký';
require __DIR__ . '/../layout/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius:14px">
            <div class="card-body p-4">
                <h4 class="mb-1 fw-700">Tạo tài khoản mới</h4>
                <p class="text-muted small mb-4">Tham gia JobVN để tìm việc hoặc đăng tuyển</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Họ và tên <span class="text-danger">*</span></label>
                            <input name="full_name" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-500">Số điện thoại</label>
                            <input name="phone" class="form-control" placeholder="0900000000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6"
                               placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Loại tài khoản</label>
                        <select name="role" class="form-select" id="roleSelect">
                            <option value="user">Ứng viên tìm việc</option>
                            <option value="employer">Nhà tuyển dụng</option>
                        </select>
                    </div>

                    <!-- Block thông tin công ty (chỉ hiện khi chọn employer) -->
                    <div id="companyBlock" style="display:none">
                        <hr class="my-3">
                        <h6 class="mb-3 text-primary"><i class="bi bi-building me-1"></i>Thông tin công ty</h6>
                        <div class="mb-3">
                            <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                            <input name="company_name" class="form-control" id="companyNameInput" placeholder="Tên công ty của bạn">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Địa điểm</label>
                                <input name="company_location" class="form-control" placeholder="Hà Nội">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Website</label>
                                <input name="company_website" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-500">Mô tả công ty</label>
                            <textarea name="company_description" class="form-control" rows="3"
                                      placeholder="Giới thiệu ngắn về công ty..."></textarea>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-2" style="padding: 0.65rem;">
                        <i class="bi bi-person-check me-1"></i> Đăng ký
                    </button>
                </form>
                <hr>
                <div class="small text-muted text-center">
                    Đã có tài khoản? <a href="<?= e(url('login')) ?>">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Hiện/ẩn block công ty khi chọn role employer
const roleSelect = document.getElementById('roleSelect');
const companyBlock = document.getElementById('companyBlock');
const companyNameInput = document.getElementById('companyNameInput');

function toggleCompanyBlock() {
    const isEmployer = roleSelect.value === 'employer';
    companyBlock.style.display = isEmployer ? 'block' : 'none';
    companyNameInput.required = isEmployer;
}

roleSelect.addEventListener('change', toggleCompanyBlock);

// Khôi phục trạng thái khi có lỗi (page reload)
<?php if (is_post() && ($_POST['role'] ?? '') === 'employer'): ?>
roleSelect.value = 'employer';
toggleCompanyBlock();
<?php endif; ?>
</script>
<?php require __DIR__ . '/../layout/footer.php';
