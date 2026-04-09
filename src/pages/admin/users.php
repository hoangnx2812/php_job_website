<?php
// Admin: quản lý user
$me = require_role('admin');

// Đổi role nhanh
if (is_post() && ($_POST['action'] ?? '') === 'change_role') {
    $uid = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if (in_array($role, ['admin','employer','user'], true) && $uid !== (int)$me['id']) {
        $stmt = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $uid]);
        flash_set('success', 'Đã cập nhật role.');
    }
    redirect('admin/users');
}

$users = db()->query('SELECT * FROM users ORDER BY id')->fetchAll();
$pageTitle = 'Quản lý User';
require __DIR__ . '/../../layout/header.php';
?>
<h3 class="mb-3">Quản lý người dùng</h3>
<table class="table table-bordered bg-white">
    <thead class="table-light"><tr><th>ID</th><th>Email</th><th>Tên</th><th>Role</th><th>Ngày tạo</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['full_name']) ?></td>
            <td>
                <form method="post" class="d-flex gap-1">
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" class="form-select form-select-sm">
                        <?php foreach (['user','employer','admin'] as $r): ?>
                            <option value="<?= $r ?>" <?= $r == $u['role'] ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-primary">OK</button>
                </form>
            </td>
            <td><?= e($u['created_at']) ?></td>
            <td>
                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                    <a href="<?= e(url('admin/user_delete', ['id' => $u['id']])) ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Xoá user này?')">Xoá</a>
                <?php else: ?><small class="text-muted">(bạn)</small><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../layout/footer.php';
