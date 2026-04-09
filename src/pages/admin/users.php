<?php
// Admin: quản lý user, có phân trang
$me = require_role('admin');

// Đổi role nhanh
if (is_post() && ($_POST['action'] ?? '') === 'change_role') {
    $uid  = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if (in_array($role, ['admin','employer','user'], true) && $uid !== (int)$me['id']) {
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
        flash_set('success', 'Đã cập nhật role.');
    }
    redirect('admin/users');
}

$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 15;

$total = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$users = db()->query("
    SELECT * FROM users ORDER BY id
    LIMIT $perPage OFFSET " . (($page - 1) * $perPage)
)->fetchAll();

$baseUrl   = BASE_URL . '?page=admin/users';
$pageTitle = 'Quản lý User';
require __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-700 mb-0">
        <i class="bi bi-people me-2 text-primary"></i>Quản lý người dùng
        <span class="badge bg-primary ms-2" style="font-size:0.75rem"><?= $total ?></span>
    </h4>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Họ tên</th>
                <th>Điện thoại</th>
                <th>Role</th>
                <th>Ngày tạo</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                $roleBadge = ['admin'=>'bg-danger','employer'=>'bg-warning text-dark','user'=>'bg-success'][$u['role']] ?? 'bg-secondary';
                ?>
                <tr>
                    <td class="text-muted small"><?= $u['id'] ?></td>
                    <td class="small"><?= e($u['email']) ?></td>
                    <td class="fw-500 small"><?= e($u['full_name']) ?></td>
                    <td class="small text-muted"><?= e($u['phone'] ?? '-') ?></td>
                    <td>
                        <form method="post" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <?php if ((int)$u['id'] === (int)$me['id']): ?>
                                <span class="badge <?= $roleBadge ?>"><?= $u['role'] ?></span>
                                <small class="text-muted">(bạn)</small>
                            <?php else: ?>
                                <select name="role" class="form-select form-select-sm" style="width:auto">
                                    <?php foreach (['user','employer','admin'] as $r): ?>
                                        <option value="<?= $r ?>" <?= $r == $u['role'] ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary flex-shrink-0">OK</button>
                            <?php endif; ?>
                        </form>
                    </td>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                            <a href="<?= e(url('admin/user_delete', ['id' => $u['id']])) ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Xoá user này?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    <?= render_pagination($total, $perPage, $page, $baseUrl) ?>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
