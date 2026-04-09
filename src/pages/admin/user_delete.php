<?php
$me = require_role('admin');
$id = (int)($_GET['id'] ?? 0);
if ($id && $id !== (int)$me['id']) {
    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Đã xoá user.');
}
redirect('admin/users');
