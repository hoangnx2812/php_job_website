<?php
// Header chung cho toàn bộ trang
$u = current_user();
// Lấy page hiện tại để highlight active nav link
$currentPage = $_GET['page'] ?? 'home';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>JobVN</title>
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font: Be Vietnam Pro -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== Global ===== */
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: #f0f4f8;
            color: #1a202c;
        }
        /* Font-weight utilities (Bootstrap chỉ có fw-bold, thiếu các mức trung gian) */
        .fw-500 { font-weight: 500 !important; }
        .fw-600 { font-weight: 600 !important; }
        .fw-700 { font-weight: 700 !important; }
        /* ===== Navbar ===== */
        .navbar-main {
            background: linear-gradient(135deg, #1a56db 0%, #0d3b8e 100%);
            box-shadow: 0 2px 12px rgba(26, 86, 219, 0.3);
            padding: 0.6rem 0;
        }
        .navbar-brand-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff !important;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .navbar-brand-logo .logo-icon {
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .navbar-main .nav-link {
            color: rgba(255,255,255,0.88) !important;
            font-weight: 500;
            font-size: 0.92rem;
            padding: 0.5rem 0.75rem !important;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .navbar-main .nav-link:hover,
        .navbar-main .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
        }
        .navbar-main .nav-link.active {
            font-weight: 600;
        }
        .navbar-toggler { border-color: rgba(255,255,255,0.3); }
        .navbar-toggler-icon { filter: invert(1); }
        .navbar-user-info {
            color: rgba(255,255,255,0.85) !important;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.5rem 0.75rem !important;
        }
        /* ===== Main content ===== */
        main.container { padding-top: 1.5rem; padding-bottom: 2rem; }
        /* ===== Cards ===== */
        .job-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .job-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(26, 86, 219, 0.13);
        }
        .company-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .company-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(26, 86, 219, 0.13);
        }
        /* ===== Badges ===== */
        .badge-salary {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.3em 0.7em;
            border-radius: 6px;
        }
        .badge-type {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-size: 0.78rem;
            padding: 0.3em 0.65em;
            border-radius: 6px;
        }
        .badge-status-pending  { background:#fef9c3; color:#854d0e; border:1px solid #fde68a; }
        .badge-status-accepted { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .badge-status-rejected { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
        .badge-status-pending, .badge-status-accepted, .badge-status-rejected {
            font-size: 0.78rem; padding: 0.3em 0.7em; border-radius: 6px; font-weight: 600;
        }
        /* ===== Tables ===== */
        .table-admin { border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .table-admin thead th { background: #f8fafc; font-size: 0.85rem; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .table-admin tbody tr:hover { background: #f8faff; }
        /* ===== Forms ===== */
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #e2e8f0;
            font-size: 0.93rem;
            padding: 0.5rem 0.85rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a56db 0%, #1148c4 100%);
            border: none;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            transition: opacity 0.15s, transform 0.1s;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-success { border-radius: 8px; font-weight: 600; }
        .btn-warning { border-radius: 8px; font-weight: 600; }
        .btn-danger  { border-radius: 8px; font-weight: 600; }
        /* ===== Alert flash ===== */
        .alert { border-radius: 10px; font-size: 0.93rem; }
        /* ===== Logo công ty ===== */
        .company-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #fff;
        }
        .company-logo-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        /* ===== Saved job heart ===== */
        .btn-save-job {
            background: none;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            color: #94a3b8;
            padding: 0.3rem 0.65rem;
            font-size: 1rem;
            transition: color 0.15s, border-color 0.15s, background 0.15s;
            cursor: pointer;
        }
        .btn-save-job:hover, .btn-save-job.saved {
            color: #ef4444;
            border-color: #fca5a5;
            background: #fff1f2;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-main">
    <div class="container">
        <a class="navbar-brand-logo text-decoration-none" href="<?= e(url('home')) ?>">
            <div class="logo-icon"><i class="bi bi-briefcase-fill text-white"></i></div>
            JobVN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Links bên trái -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentPage, ['jobs','job_detail']) ? 'active' : '' ?>"
                       href="<?= e(url('jobs')) ?>">
                        <i class="bi bi-search"></i> Việc làm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'companies' ? 'active' : '' ?>"
                       href="<?= e(url('companies')) ?>">
                        <i class="bi bi-building"></i> Công ty
                    </a>
                </li>
                <!-- Links cho role=user -->
                <?php if ($u && $u['role'] === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'user/my_applications' ? 'active' : '' ?>"
                           href="<?= e(url('user/my_applications')) ?>">
                            <i class="bi bi-file-earmark-text"></i> Đơn của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'user/saved_jobs' ? 'active' : '' ?>"
                           href="<?= e(url('user/saved_jobs')) ?>">
                            <i class="bi bi-heart"></i> Job đã lưu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'user/become_employer' ? 'active' : '' ?>"
                           href="<?= e(url('user/become_employer')) ?>">
                            <i class="bi bi-building-add"></i> Đăng ký NTD
                        </a>
                    </li>
                <?php endif; ?>
                <!-- Links cho role=employer -->
                <?php if ($u && $u['role'] === 'employer'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'employer/dashboard' ? 'active' : '' ?>"
                           href="<?= e(url('employer/dashboard')) ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($currentPage, ['employer/jobs','employer/job_form']) ? 'active' : '' ?>"
                           href="<?= e(url('employer/jobs')) ?>">
                            <i class="bi bi-megaphone"></i> Bài đăng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'employer/company' ? 'active' : '' ?>"
                           href="<?= e(url('employer/company')) ?>">
                            <i class="bi bi-building-gear"></i> Công ty của tôi
                        </a>
                    </li>
                <?php endif; ?>
                <!-- Links cho role=admin -->
                <?php if ($u && $u['role'] === 'admin'): ?>
                    <?php
                    // Đếm request đang chờ để hiển thị badge
                    $navPendingNtd = (int)db()->query("SELECT COUNT(*) FROM employer_requests WHERE status='pending'")->fetchColumn();
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with($currentPage, 'admin/') ? 'active' : '' ?>"
                           href="<?= e(url('admin/dashboard')) ?>">
                            <i class="bi bi-shield-lock"></i> Admin
                            <?php if ($navPendingNtd > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-1"><?= $navPendingNtd ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <!-- Links bên phải: user actions -->
            <ul class="navbar-nav">
                <?php if ($u): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($currentPage, ['user/profile']) ? 'active' : '' ?>"
                           href="<?= e(url('user/profile')) ?>">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-lg-inline"><?= e($u['full_name']) ?></span>
                            <span class="d-lg-none">Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url('logout')) ?>">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'login' ? 'active' : '' ?>"
                           href="<?= e(url('login')) ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'register' ? 'active' : '' ?>"
                           href="<?= e(url('register')) ?>">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php foreach (flash_get() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($f['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; ?>
