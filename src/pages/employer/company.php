<?php
// Employer xem và sửa thông tin công ty của mình + upload logo
$u = require_role('employer');

// Lấy công ty của employer
$stmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ?');
$stmt->execute([$u['id']]);
$company = $stmt->fetch();

$error = null;

if (is_post()) {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'create') {
        // Tạo công ty mới (employer chưa có company)
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $error = 'Vui lòng nhập tên công ty.';
        } else {
            db()->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)')
               ->execute([
                   $u['id'],
                   $name,
                   trim($_POST['description'] ?? '') ?: null,
                   trim($_POST['location'] ?? '') ?: null,
                   trim($_POST['website'] ?? '') ?: null,
               ]);
            flash_set('success', 'Đã tạo công ty thành công.');
            redirect('employer/company');
        }
    } elseif ($action === 'update' && $company) {
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $error = 'Tên công ty không được để trống.';
        } else {
            $newLogo = $company['logo']; // giữ logo cũ mặc định

            // Xử lý upload logo mới
            if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $f   = $_FILES['logo'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
                    $error = 'Logo chỉ chấp nhận JPG, PNG, GIF, WEBP.';
                } elseif ($f['size'] > 2 * 1024 * 1024) {
                    $error = 'Logo tối đa 2MB.';
                } else {
                    if (!is_dir(LOGO_UPLOAD_DIR)) @mkdir(LOGO_UPLOAD_DIR, 0777, true);
                    $filename = 'logo_' . $company['id'] . '_' . time() . '.' . $ext;
                    $dest     = LOGO_UPLOAD_DIR . '/' . $filename;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        // Xóa logo cũ nếu có
                        if ($company['logo']) {
                            $oldPath = LOGO_UPLOAD_DIR . '/' . $company['logo'];
                            if (file_exists($oldPath)) @unlink($oldPath);
                        }
                        $newLogo = $filename;
                    } else {
                        $error = 'Không lưu được file logo.';
                    }
                }
            }

            if (!$error) {
                db()->prepare('UPDATE companies SET name=?, description=?, location=?, website=?, logo=? WHERE owner_id=?')
                   ->execute([
                       $name,
                       trim($_POST['description'] ?? '') ?: null,
                       trim($_POST['location'] ?? '') ?: null,
                       trim($_POST['website'] ?? '') ?: null,
                       $newLogo,
                       $u['id'],
                   ]);
                flash_set('success', 'Đã cập nhật thông tin công ty.');
                redirect('employer/company');
            }
        }
    }
}

// Reload company sau khi tạo
if (!$company) {
    $stmt = db()->prepare('SELECT * FROM companies WHERE owner_id = ?');
    $stmt->execute([$u['id']]);
    $company = $stmt->fetch();
}

$pageTitle = 'Công ty của tôi';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-building-gear me-2 text-primary"></i>Công ty của tôi
    </h4>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$company): ?>
    <!-- Form tạo công ty mới -->
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Bạn chưa có công ty. Điền thông tin bên dưới để tạo công ty.
    </div>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                    <input name="name" class="form-control" required placeholder="Tên công ty của bạn">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Địa điểm</label>
                        <input name="location" class="form-control" placeholder="Hà Nội, TP.HCM...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Website</label>
                        <input name="website" class="form-control" placeholder="https://...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-500">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Giới thiệu về công ty..."></textarea>
                </div>
                <button class="btn btn-success px-4">
                    <i class="bi bi-plus-circle me-1"></i> Tạo công ty
                </button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <!-- Preview logo + thống kê -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center">
                <div class="card-body p-3">
                    <?php if ($company['logo']): ?>
                        <img src="/uploads/logos/<?= e($company['logo']) ?>"
                             alt="<?= e($company['name']) ?>"
                             class="img-fluid rounded-3 mb-2"
                             style="max-width:120px;max-height:120px;object-fit:contain;border:1px solid #e2e8f0;padding:8px;background:#fff">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-2"
                             style="width:100px;height:100px;border-radius:14px;background:#f1f5f9;border:1px solid #e2e8f0;color:#94a3b8;font-size:2.5rem">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="small text-muted">Chưa có logo</div>
                    <?php endif; ?>
                    <div class="fw-600 mt-2"><?= e($company['name']) ?></div>
                    <?php if ($company['location']): ?>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-geo-alt me-1"></i><?= e($company['location']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Form sửa thông tin -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-600 mb-3">Chỉnh sửa thông tin công ty</h6>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">
                        <div class="mb-3">
                            <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                            <input name="name" value="<?= e($company['name']) ?>"
                                   class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Địa điểm</label>
                                <input name="location" value="<?= e($company['location']) ?>"
                                       class="form-control" placeholder="Hà Nội, TP.HCM...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Website</label>
                                <input name="website" value="<?= e($company['website']) ?>"
                                       class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-500">Mô tả</label>
                            <textarea name="description" class="form-control" rows="4"><?= e($company['description']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-500">Logo công ty</label>
                            <input type="file" name="logo" class="form-control"
                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                            <div class="form-text">Chấp nhận: JPG, PNG, GIF, WEBP. Tối đa 2MB.</div>
                        </div>
                        <button class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../../layout/footer.php';
