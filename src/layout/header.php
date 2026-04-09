<?php
// Header chung cho toàn bộ trang
$u = current_user();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Job Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .navbar-brand { font-weight: 700; }
        .job-card { transition: transform .1s; }
        .job-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= e(url('home')) ?>">JobVN</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('jobs')) ?>">Việc làm</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('companies')) ?>">Công ty</a></li>
                <?php if ($u && $u['role'] === 'user'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('user/my_applications')) ?>">Đơn của tôi</a></li>
                <?php endif; ?>
                <?php if ($u && $u['role'] === 'employer'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('employer/dashboard')) ?>">Bảng điều khiển</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('employer/jobs')) ?>">Bài đăng của tôi</a></li>
                <?php endif; ?>
                <?php if ($u && $u['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/dashboard')) ?>">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($u): ?>
                    <li class="nav-item"><span class="nav-link">Xin chào, <b><?= e($u['full_name']) ?></b> (<?= e($u['role']) ?>)</span></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('logout')) ?>">Đăng xuất</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('login')) ?>">Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('register')) ?>">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php foreach (flash_get() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
