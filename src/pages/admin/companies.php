<?php
// Admin: quản lý công ty (xem, tạo nhanh, xoá)
require_role('admin');

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $stmt = db()->prepare('INSERT INTO companies (owner_id, name, description, location, website) VALUES (?,?,?,?,?)');
        $stmt->execute([
            (int)$_POST['owner_id'],
            trim($_POST['name']),
            trim($_POST['description']),
            trim($_POST['location']),
            trim($_POST['website']),
        ]);
        flash_set('success', 'Đã tạo công ty.');
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM companies WHERE id = ?');
        $stmt->execute([(int)$_POST['id']]);
        flash_set('success', 'Đã xoá công ty.');
    }
    redirect('admin/companies');
}

$rows = db()->query("
    SELECT c.*, u.full_name AS owner_name
    FROM companies c JOIN users u ON u.id = c.owner_id
    ORDER BY c.id
")->fetchAll();
$employers = db()->query("SELECT id, full_name FROM users WHERE role='employer'")->fetchAll();

$pageTitle = 'Quản lý công ty';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Quản lý công ty</h3>
<details class="mb-3"><summary class="btn btn-success">+ Tạo công ty mới</summary>
<form method="post" class="card card-body mt-2">
    <input type="hidden" name="action" value="create">
    <div class="row">
        <div class="col-md-6 mb-2"><label>Tên công ty</label><input name="name" class="form-control" required></div>
        <div class="col-md-6 mb-2"><label>Chủ sở hữu (employer)</label>
            <select name="owner_id" class="form-select" required>
                <?php foreach ($employers as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= e($e['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-2"><label>Địa điểm</label><input name="location" class="form-control"></div>
        <div class="col-md-6 mb-2"><label>Website</label><input name="website" class="form-control"></div>
        <div class="col-12 mb-2"><label>Mô tả</label><textarea name="description" class="form-control"></textarea></div>
    </div>
    <button class="btn btn-primary">Tạo</button>
</form>
</details>

<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>ID</th><th>Tên</th><th>Chủ</th><th>Địa điểm</th><th>Website</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= e($c['name']) ?></td>
            <td><?= e($c['owner_name']) ?></td>
            <td><?= e($c['location']) ?></td>
            <td><?= e($c['website']) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Xoá?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-sm btn-danger">Xoá</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../layout/footer.php';
