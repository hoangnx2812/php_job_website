<?php
// Dashboard admin - tổng quan hệ thống
require_role('admin');
$pdo = db();
$stats = [
    'users'        => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'jobs'         => (int)$pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
    'companies'    => (int)$pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
    'applications' => (int)$pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
];

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../../layout/header.php';
?>
<h3>Admin Dashboard</h3>
<div class="row g-3 mt-2">
    <div class="col-md-3"><div class="card text-bg-primary"><div class="card-body">Users<h3><?= $stats['users'] ?></h3></div></div></div>
    <div class="col-md-3"><div class="card text-bg-success"><div class="card-body">Jobs<h3><?= $stats['jobs'] ?></h3></div></div></div>
    <div class="col-md-3"><div class="card text-bg-warning"><div class="card-body">Companies<h3><?= $stats['companies'] ?></h3></div></div></div>
    <div class="col-md-3"><div class="card text-bg-info"><div class="card-body">Applications<h3><?= $stats['applications'] ?></h3></div></div></div>
</div>
<div class="mt-4">
    <a href="<?= e(url('admin/users')) ?>" class="btn btn-outline-primary">Quản lý user</a>
    <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-outline-primary">Quản lý bài đăng</a>
    <a href="<?= e(url('admin/companies')) ?>" class="btn btn-outline-primary">Quản lý công ty</a>
    <a href="<?= e(url('admin/applications')) ?>" class="btn btn-outline-primary">Quản lý CV</a>
</div>
<?php require __DIR__ . '/../../layout/footer.php';
