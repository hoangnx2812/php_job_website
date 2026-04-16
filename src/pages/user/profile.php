<?php
// Trang profile nâng cao: sửa thông tin, upload avatar, đổi mật khẩu
// Dùng được cho cả 3 role: user, employer, admin
$u = require_role('user', 'employer', 'admin');

$errorInfo = null;
$errorPass = null;

if (is_post()) {
    $action = $_POST['action'] ?? 'update_info';

    if ($action === 'update_info') {
        $fullName        = trim($_POST['full_name'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $bio             = trim($_POST['bio'] ?? '');
        $skills          = trim($_POST['skills'] ?? '');
        $experienceYears = $_POST['experience_years'] !== '' ? (int)$_POST['experience_years'] : null;

        if (!$fullName) {
            $errorInfo = 'Vui lòng điền họ và tên.';
        } else {
            // Xử lý upload avatar nếu có file được chọn
            $avatarName = $u['avatar'] ?? null;
            if (!empty($_FILES['avatar']['name'])) {
                $file     = $_FILES['avatar'];
                $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize  = 2 * 1024 * 1024; // 2MB

                if (!in_array($file['type'], $allowed)) {
                    $errorInfo = 'Ảnh đại diện chỉ chấp nhận định dạng JPEG, PNG, GIF, WebP.';
                } elseif ($file['size'] > $maxSize) {
                    $errorInfo = 'Ảnh đại diện tối đa 2MB.';
                } else {
                    // Đặt tên file duy nhất để tránh trùng
                    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $avatarName = 'avatar_' . $u['id'] . '_' . time() . '.' . $ext;
                    $dest       = __DIR__ . '/../../../uploads/avatars/' . $avatarName;
                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        $errorInfo   = 'Lỗi khi lưu ảnh đại diện. Vui lòng thử lại.';
                        $avatarName  = $u['avatar'] ?? null;
                    }
                }
            }

            if (!$errorInfo) {
                db()->prepare(
                    'UPDATE users SET full_name=?, phone=?, bio=?, skills=?, experience_years=?, avatar=? WHERE id=?'
                )->execute([$fullName, $phone ?: null, $bio ?: null, $skills ?: null, $experienceYears, $avatarName, $u['id']]);
                flash_set('success', 'Đã cập nhật thông tin cá nhân.');
                redirect('user/profile');
            }
        }

    } elseif ($action === 'change_password') {
        $oldPass = $_POST['old_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

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

// Reload user để lấy dữ liệu mới nhất (sau khi cập nhật)
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$u['id']]);
$u = $stmt->fetch();

// Đếm số đơn đã nộp (chỉ có ý nghĩa với role=user)
$appCount = 0;
if ($u['role'] === 'user') {
    $cntStmt = db()->prepare('SELECT COUNT(*) FROM applications WHERE user_id = ?');
    $cntStmt->execute([$u['id']]);
    $appCount = (int)$cntStmt->fetchColumn();
}

// Đếm số job đã lưu
$savedCount = 0;
if ($u['role'] === 'user') {
    $cntStmt2 = db()->prepare('SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?');
    $cntStmt2->execute([$u['id']]);
    $savedCount = (int)$cntStmt2->fetchColumn();
}

$pageTitle = 'Hồ sơ cá nhân';
require __DIR__ . '/../../layout/header.php';
?>
<h4 class="fw-700 mb-4">
    <i class="bi bi-person-circle me-2 text-primary"></i>Hồ sơ cá nhân
</h4>

<div class="row g-4">
    <!-- Cột trái: avatar + thống kê -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 text-center p-4">
            <!-- Ảnh đại diện hoặc placeholder icon -->
            <?php if (!empty($u['avatar'])): ?>
                <img src="/uploads/avatars/<?= e($u['avatar']) ?>"
                     alt="Avatar"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0;margin:0 auto 1rem">
            <?php else: ?>
                <div style="width:100px;height:100px;border-radius:50%;background:#eff6ff;border:3px solid #bfdbfe;
                            display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#1a56db;font-size:2.5rem">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>

            <h5 class="fw-700 mb-1"><?= e($u['full_name']) ?></h5>
            <span class="badge bg-<?= ['admin'=>'danger','employer'=>'warning text-dark','user'=>'success'][$u['role']] ?? 'secondary' ?> mb-3">
                <?= e($u['role']) ?>
            </span>

            <!-- Thống kê nhanh chỉ hiện với ứng viên -->
            <?php if ($u['role'] === 'user'): ?>
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="rounded-3 p-2" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <div class="fw-700 fs-5 text-primary"><?= $appCount ?></div>
                        <div class="text-muted" style="font-size:0.75rem">Đơn đã nộp</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="rounded-3 p-2" style="background:#f8fafc;border:1px solid #e2e8f0">
                        <div class="fw-700 fs-5 text-danger"><?= $savedCount ?></div>
                        <div class="text-muted" style="font-size:0.75rem">Job đã lưu</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-3 text-muted small">
                <i class="bi bi-envelope me-1"></i><?= e($u['email']) ?>
            </div>
            <?php if ($u['phone']): ?>
            <div class="text-muted small mt-1">
                <i class="bi bi-telephone me-1"></i><?= e($u['phone']) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($u['bio'])): ?>
            <p class="mt-3 text-secondary small" style="line-height:1.6"><?= nl2br(e($u['bio'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột phải: form sửa thông tin + đổi mật khẩu -->
    <div class="col-md-8">
        <!-- Card thông tin cơ bản + profile nâng cao -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-600 mb-3">
                    <i class="bi bi-person text-primary me-2"></i>Thông tin cá nhân
                </h6>
                <?php if ($errorInfo): ?>
                    <div class="alert alert-danger"><?= e($errorInfo) ?></div>
                <?php endif; ?>
                <!-- enctype cần thiết để upload file ảnh đại diện -->
                <form method="post" enctype="multipart/form-data">
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
                            <label class="form-label fw-500">Email</label>
                            <!-- Email readonly: không cho sửa để tránh conflict account -->
                            <input type="email" value="<?= e($u['email']) ?>"
                                   class="form-control" readonly style="background:#f8fafc">
                            <div class="form-text text-muted">Email không thể thay đổi.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-500">Giới thiệu bản thân</label>
                            <textarea name="bio" class="form-control" rows="3"
                                      placeholder="Mô tả ngắn về bản thân, mục tiêu nghề nghiệp..."><?= e($u['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-500">Kỹ năng <span class="text-muted small fw-400">(phân cách bằng dấu phẩy)</span></label>
                            <input name="skills" value="<?= e($u['skills'] ?? '') ?>"
                                   class="form-control" placeholder="PHP, MySQL, React...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Kinh nghiệm</label>
                            <select name="experience_years" class="form-select">
                                <option value="">-- Chọn --</option>
                                <?php for ($y = 0; $y <= 10; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($u['experience_years'] ?? null) == $y ? 'selected' : '' ?>>
                                        <?= $y === 0 ? 'Chưa có kinh nghiệm' : ($y < 10 ? $y . ' năm' : '10+ năm') ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-500">Ảnh đại diện</label>
                            <input type="file" name="avatar" accept="image/*" class="form-control">
                            <div class="form-text text-muted">Tối đa 2MB. Định dạng JPEG, PNG, GIF, WebP.</div>
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
