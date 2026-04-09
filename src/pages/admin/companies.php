<?php
// Admin: quản lý công ty (xem, tạo, xoá) + upload logo
require_role('admin');

if (is_post()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $ownerId = (int)$_POST['owner_id'];
        $name    = trim($_POST['name']);

        // Kiểm tra employer này đã có công ty chưa (ràng buộc UNIQUE owner_id)
        $existing = db()->prepare('SELECT id FROM companies WHERE owner_id = ?');
        $existing->execute([$ownerId]);
        if ($existing->fetch()) {
            flash_set('danger', 'Employer này đã có công ty rồi.');
        } elseif (!$ownerId || !$name) {
            flash_set('danger', 'Vui lòng chọn employer và nhập tên công ty.');
        } else {
            db()->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)')
               ->execute([
                   $ownerId, $name,
                   trim($_POST['description']) ?: null,
                   trim($_POST['location']) ?: null,
                   trim($_POST['website']) ?: null,
               ]);
            flash_set('success', 'Đã tạo công ty.');
        }

    } elseif ($action === 'upload_logo') {
        $companyId = (int)$_POST['company_id'];
        $stmt = db()->prepare('SELECT * FROM companies WHERE id = ?');
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();

        if ($company && !empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $f   = $_FILES['logo'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true) && $f['size'] <= 2 * 1024 * 1024) {
                if (!is_dir(LOGO_UPLOAD_DIR)) @mkdir(LOGO_UPLOAD_DIR, 0777, true);
                $filename = 'logo_' . $companyId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], LOGO_UPLOAD_DIR . '/' . $filename)) {
                    // Xóa logo cũ
                    if ($company['logo']) {
                        $old = LOGO_UPLOAD_DIR . '/' . $company['logo'];
                        if (file_exists($old)) @unlink($old);
                    }
                    db()->prepare('UPDATE companies SET logo = ? WHERE id = ?')->execute([$filename, $companyId]);
                    flash_set('success', 'Đã cập nhật logo.');
                } else {
                    flash_set('danger', 'Không lưu được file logo.');
                }
            } else {
                flash_set('danger', 'Logo không hợp lệ (chỉ JPG/PNG/GIF/WEBP, tối đa 2MB).');
            }
        }

    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM companies WHERE id = ?')->execute([(int)$_POST['id']]);
        flash_set('success', 'Đã xoá công ty.');
    }

    redirect('admin/companies');
}

$rows = db()->query("
    SELECT c.*, u.full_name AS owner_name,
           (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) AS job_count
    FROM companies c JOIN users u ON u.id = c.owner_id
    ORDER BY c.id
")->fetchAll();

// Chỉ lấy employer CHƯA có công ty (cho dropdown tạo mới)
$freeEmployers = db()->query("
    SELECT u.id, u.full_name, u.email
    FROM users u
    WHERE u.role = 'employer'
      AND NOT EXISTS (SELECT 1 FROM companies c WHERE c.owner_id = u.id)
")->fetchAll();

$pageTitle = 'Quản lý công ty';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-building me-2 text-primary"></i>Quản lý công ty
    </h4>
    <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#createForm">
        <i class="bi bi-plus-circle me-1"></i> Tạo công ty
    </button>
</div>

<!-- Form tạo công ty mới -->
<div class="collapse mb-4" id="createForm">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h6 class="fw-600 mb-3">Tạo công ty mới</h6>
            <?php if (!$freeEmployers): ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Tất cả employer đều đã có công ty.
                </div>
            <?php else: ?>
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="create">
                <div class="col-md-6">
                    <label class="form-label fw-500">Chủ sở hữu (employer) <span class="text-danger">*</span></label>
                    <select name="owner_id" class="form-select" required>
                        <option value="">-- Chọn employer --</option>
                        <?php foreach ($freeEmployers as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= e($e['full_name']) ?> (<?= e($e['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-500">Tên công ty <span class="text-danger">*</span></label>
                    <input name="name" class="form-control" required placeholder="Tên công ty">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-500">Địa điểm</label>
                    <input name="location" class="form-control" placeholder="Hà Nội">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-500">Website</label>
                    <input name="website" class="form-control" placeholder="https://...">
                </div>
                <div class="col-12">
                    <label class="form-label fw-500">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary px-4">
                        <i class="bi bi-plus-circle me-1"></i> Tạo công ty
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bảng danh sách công ty -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Tên công ty</th>
                <th>Chủ sở hữu</th>
                <th>Địa điểm</th>
                <th class="text-center">Bài đăng</th>
                <th>Upload Logo</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $c): ?>
                <tr>
                    <td class="text-muted small"><?= $c['id'] ?></td>
                    <td>
                        <?php if ($c['logo']): ?>
                            <img src="/uploads/logos/<?= e($c['logo']) ?>"
                                 style="width:40px;height:40px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;padding:3px;background:#fff"
                                 alt="logo">
                        <?php else: ?>
                            <div style="width:40px;height:40px;border-radius:8px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8">
                                <i class="bi bi-building"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-500"><?= e($c['name']) ?></td>
                    <td class="small"><?= e($c['owner_name']) ?></td>
                    <td class="small text-muted"><?= e($c['location']) ?></td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= (int)$c['job_count'] ?></span>
                    </td>
                    <td>
                        <!-- Form upload logo riêng -->
                        <form method="post" enctype="multipart/form-data" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="action" value="upload_logo">
                            <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                            <input type="file" name="logo" class="form-control form-control-sm"
                                   accept=".jpg,.jpeg,.png,.gif,.webp"
                                   style="max-width:180px">
                            <button class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-upload"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('Xoá công ty này? Tất cả job liên quan cũng bị xoá.')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
