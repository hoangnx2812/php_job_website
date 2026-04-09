<?php
// Xoá bài đăng (chỉ xoá được bài của chính employer)
$u = require_role('employer');
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('DELETE FROM jobs WHERE id = ? AND employer_id = ?');
$stmt->execute([$id, $u['id']]);
flash_set('success', 'Đã xoá bài đăng.');
redirect('employer/jobs');
