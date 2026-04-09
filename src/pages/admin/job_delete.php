<?php
require_role('admin');
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = db()->prepare('DELETE FROM jobs WHERE id = ?');
    $stmt->execute([$id]);
    flash_set('success', 'Đã xoá bài đăng.');
}
redirect('admin/jobs');
