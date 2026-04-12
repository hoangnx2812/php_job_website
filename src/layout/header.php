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
    <!-- Script khởi tạo dark mode sớm nhất có thể, tránh FOUC (flash of unstyled content) -->
    <script>(function(){var t=localStorage.getItem('theme');if(t)document.documentElement.setAttribute('data-theme',t);})()</script>
    <style>
        /* ===== CSS Variables cho Dark Mode ===== */
        :root {
            --bg-main: #f0f4f8;
            --bg-card: #ffffff;
            --text-main: #1a202c;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --navbar-bg: linear-gradient(135deg, #1a56db 0%, #0d3b8e 100%);
            --table-hover-bg: #f8faff;
            --input-bg: #ffffff;
            --footer-bg: #1e293b;
        }
        /* Dark mode: ghi đè biến màu */
        [data-theme="dark"] {
            --bg-main: #0f172a;
            --bg-card: #1e293b;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --navbar-bg: linear-gradient(135deg, #1e3a8a 0%, #1e3a8a 100%);
            --table-hover-bg: #263350;
            --input-bg: #1e293b;
            --footer-bg: #0f172a;
        }
        /* ===== Global ===== */
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            transition: background 0.2s, color 0.2s;
        }
        /* Áp dụng biến màu lên card và các element chính */
        .card {
            background: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }
        .table-admin tbody tr:hover { background: var(--table-hover-bg); }
        .form-control, .form-select {
            background-color: var(--input-bg) !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }
        .text-muted { color: var(--text-muted) !important; }
        .text-secondary { color: var(--text-muted) !important; }
        /* Dark mode: card-header, table header */
        [data-theme="dark"] .card-header { background: #1e293b !important; border-color: var(--border-color) !important; }
        [data-theme="dark"] .table-admin thead th { background: #263350 !important; color: #94a3b8 !important; border-color: var(--border-color) !important; }
        [data-theme="dark"] .table { color: var(--text-main) !important; border-color: var(--border-color); }
        [data-theme="dark"] .table td, [data-theme="dark"] .table th { border-color: var(--border-color); }
        [data-theme="dark"] .modal-content { background: var(--bg-card); color: var(--text-main); }
        [data-theme="dark"] .dropdown-menu { background: var(--bg-card); border-color: var(--border-color); }
        [data-theme="dark"] .dropdown-item { color: var(--text-main); }
        [data-theme="dark"] .dropdown-item:hover { background: #334155; }

        /* ===== Dark mode: fix Bootstrap classes + hardcoded colors ===== */

        /* .text-dark của Bootstrap dùng #212529 — ghi đè lại */
        [data-theme="dark"] .text-dark { color: var(--text-main) !important; }
        /* Links trong card/content — đổi sang xanh nhạt cho dễ đọc */
        [data-theme="dark"] a.text-dark,
        [data-theme="dark"] a.text-decoration-none.text-dark { color: var(--text-main) !important; }
        [data-theme="dark"] a.stretched-link { color: var(--text-main) !important; }

        /* List group items (notifications, applications...) */
        [data-theme="dark"] .list-group-item {
            background: var(--bg-card) !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }
        [data-theme="dark"] .list-group-item:hover { background: #263350 !important; }

        /* Alert boxes */
        [data-theme="dark"] .alert-info    { background:#172554; color:#bfdbfe; border-color:#1e40af; }
        [data-theme="dark"] .alert-success { background:#052e16; color:#bbf7d0; border-color:#166534; }
        [data-theme="dark"] .alert-warning { background:#422006; color:#fde68a; border-color:#92400e; }
        [data-theme="dark"] .alert-danger  { background:#450a0a; color:#fecaca; border-color:#b91c1c; }
        [data-theme="dark"] .alert-primary { background:#172554; color:#bfdbfe; border-color:#1e40af; }
        [data-theme="dark"] .alert-secondary{ background:#1e293b; color:#94a3b8; border-color:#334155; }

        /* Breadcrumb */
        [data-theme="dark"] .breadcrumb-item a { color: #60a5fa; }
        [data-theme="dark"] .breadcrumb-item.active,
        [data-theme="dark"] .breadcrumb-item + .breadcrumb-item::before { color: var(--text-muted); }

        /* Input group text (label trong input nhóm như "Min", "Max") */
        [data-theme="dark"] .input-group-text {
            background: #334155 !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }

        /* Buttons outline */
        [data-theme="dark"] .btn-outline-secondary {
            color: #94a3b8; border-color: #475569;
        }
        [data-theme="dark"] .btn-outline-secondary:hover,
        [data-theme="dark"] .btn-outline-secondary:focus {
            background: #334155; color: #e2e8f0; border-color: #475569;
        }
        [data-theme="dark"] .btn-outline-primary {
            color: #60a5fa; border-color: #3b82f6;
        }
        [data-theme="dark"] .btn-outline-primary:hover {
            background: #1e40af; color: #fff; border-color: #1e40af;
        }
        [data-theme="dark"] .btn-outline-danger {
            color: #f87171; border-color: #ef4444;
        }
        [data-theme="dark"] .btn-outline-danger:hover {
            background: #7f1d1d; color: #fecaca; border-color: #ef4444;
        }
        [data-theme="dark"] .btn-secondary {
            background: #334155; border-color: #475569; color: #e2e8f0;
        }
        [data-theme="dark"] .btn-light {
            background: #334155; border-color: #475569; color: #e2e8f0;
        }

        /* Pagination */
        [data-theme="dark"] .page-link {
            background: var(--bg-card); color: var(--text-main);
            border-color: var(--border-color);
        }
        [data-theme="dark"] .page-link:hover { background: #334155; color: #e2e8f0; }
        [data-theme="dark"] .page-item.active .page-link {
            background: #1a56db; border-color: #1a56db; color: #fff;
        }
        [data-theme="dark"] .page-item.disabled .page-link {
            background: var(--bg-card); color: var(--text-muted);
        }

        /* Form check (checkbox/radio label) */
        [data-theme="dark"] .form-check-label { color: var(--text-main); }
        [data-theme="dark"] .form-check-input { background-color: #334155; border-color: #475569; }

        /* Select option bg (trình duyệt hỗ trợ hạn chế nhưng có tác dụng trên Firefox/Chrome) */
        [data-theme="dark"] option { background: #1e293b; color: #e2e8f0; }

        /* Badge bg-light trong dark mode */
        [data-theme="dark"] .badge.bg-light { background: #334155 !important; color: #e2e8f0 !important; }
        [data-theme="dark"] .badge.bg-secondary { background: #475569 !important; }

        /* HR divider */
        [data-theme="dark"] hr { border-color: var(--border-color); }

        /* Small info grid (ô thông tin: địa điểm, lượt xem... trong job_detail) */
        [data-theme="dark"] .info-box {
            background: #263350 !important;
            border-color: #334155 !important;
        }

        /* Company logo placeholder */
        [data-theme="dark"] .company-logo-placeholder {
            background: #1e293b; border-color: #334155;
        }
        [data-theme="dark"] .company-logo {
            background: #1e293b; border-color: #334155;
        }

        /* Sticky apply bar */
        [data-theme="dark"] #sticky-apply {
            background: var(--bg-card) !important;
            border-top-color: var(--border-color) !important;
        }

        /* Heading tags bên trong card (kế thừa từ body nhưng đôi khi bị Bootstrap override) */
        [data-theme="dark"] h1,[data-theme="dark"] h2,
        [data-theme="dark"] h3,[data-theme="dark"] h4,
        [data-theme="dark"] h5,[data-theme="dark"] h6 { color: var(--text-main); }

        /* small tags, strong tags */
        [data-theme="dark"] small { color: var(--text-muted); }

        /* Logo công ty có background trắng cứng → đổi sang xám tối */
        [data-theme="dark"] img[style*="background:#fff"],
        [data-theme="dark"] img[style*="background: #fff"] {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Inline border màu sáng */
        [data-theme="dark"] [style*="border:1px solid #e2e8f0"],
        [data-theme="dark"] [style*="border: 1px solid #e2e8f0"] {
            border-color: #334155 !important;
        }

        /* Các div inline background sáng thường gặp (hero stat, info box...) */
        [data-theme="dark"] [style*="background:rgba(255,255,255"] {
            background: rgba(255,255,255,0.08) !important;
        }

        /* Tab, pill active states */
        [data-theme="dark"] .nav-tabs .nav-link { color: var(--text-muted); border-color: transparent; }
        [data-theme="dark"] .nav-tabs .nav-link.active {
            background: var(--bg-card); color: var(--text-main); border-color: var(--border-color);
        }
        [data-theme="dark"] .nav-tabs { border-color: var(--border-color); }
        [data-theme="dark"] .nav-pills .nav-link { color: var(--text-muted); }
        [data-theme="dark"] .nav-pills .nav-link.active { background: #1a56db; color: #fff; }

        /* bg-light trong dark mode */
        [data-theme="dark"] .bg-light { background: #1e293b !important; }
        [data-theme="dark"] .bg-white { background: var(--bg-card) !important; }
        [data-theme="dark"] .border { border-color: var(--border-color) !important; }

        /* footer bg */
        [data-theme="dark"] footer, [data-theme="dark"] .site-footer { background: var(--footer-bg); }
        /* Dark mode navbar */
        .navbar-main { background: var(--navbar-bg); }
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
        /* ===== Font weight utilities ===== */
        .fw-500 { font-weight: 500 !important; }
        .fw-600 { font-weight: 600 !important; }
        .fw-700 { font-weight: 700 !important; }
        /* ===== Badge HOT ===== */
        .badge-hot {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2em 0.55em;
            border-radius: 5px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            vertical-align: middle;
        }
        /* ===== Badge hạn nộp hồ sơ ===== */
        .badge-deadline {
            display: inline-flex;
            align-items: center;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.28em 0.65em;
            border-radius: 6px;
        }
        .badge-deadline.expired { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }
        .badge-deadline.urgent  { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
        .badge-deadline.warning { background: #fef9c3; color: #b45309; border: 1px solid #fde68a; }
        .badge-deadline.normal  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        /* ===== Badge lĩnh vực (category) - màu indigo/purple ===== */
        .badge-category {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.28em 0.65em;
            border-radius: 6px;
        }
        /* ===== Job card HOT: thêm đường viền trái màu xanh ===== */
        .job-card.hot {
            border-left: 3px solid #1a56db !important;
        }
        /* ===== Badge kỹ năng / Tags: màu tím nhạt ===== */
        .badge-tag {
            background: #f3f0ff; color: #6d28d9; border: 1px solid #ddd6fe;
            font-size: 0.74rem; padding: 0.2em 0.6em; border-radius: 4px; font-weight: 500;
            display: inline-block;
        }
        .badge-tag:hover { background: #ede9fe; color: #5b21b6; }
        /* ===== Skeleton Loading animation ===== */
        @keyframes skeleton-shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f4f8 25%, #e2e8f0 50%, #f0f4f8 75%);
            background-size: 800px 100%;
            animation: skeleton-shimmer 1.4s infinite linear;
            border-radius: 6px;
        }
        [data-theme="dark"] .skeleton {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 800px 100%;
        }
        /* ===== Toggle List View: mỗi job chiếm cả hàng ngang ===== */
        .list-view .col-md-6 { flex: 0 0 100%; max-width: 100%; }
        .list-view .job-card .card-body { padding: 0.75rem 1rem !important; }
        /* ===== Category card: hiển thị lĩnh vực trên trang chủ ===== */
        .category-card {
            background: var(--cat-bg, #eff6ff);
            border-radius: 14px;
            padding: 1.25rem 1rem;
            text-align: center;
            transition: transform 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.12);
        }
        .category-card-icon {
            font-size: 2rem;
            color: var(--cat-color, #1a56db);
            margin-bottom: 0.5rem;
            line-height: 1;
        }
        .category-card-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }
        .category-card-count {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
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
            <ul class="navbar-nav align-items-lg-center">
                <?php if ($u): ?>
                    <!-- Bell icon thông báo: chỉ hiện khi đã đăng nhập -->
                    <?php $unreadCount = unread_notif_count($u['id']); ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'user/notifications' ? 'active' : '' ?>"
                           href="<?= e(url('user/notifications')) ?>"
                           title="Thông báo"
                           style="position:relative;padding-right:0.9rem !important">
                            <i class="bi bi-bell-fill"></i>
                            <?php if ($unreadCount > 0): ?>
                                <!-- Badge số thông báo chưa đọc -->
                                <span style="position:absolute;top:4px;right:2px;background:#ef4444;color:#fff;
                                             font-size:0.62rem;font-weight:700;min-width:16px;height:16px;
                                             border-radius:999px;display:flex;align-items:center;
                                             justify-content:center;padding:0 3px;line-height:1">
                                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($currentPage, ['user/profile']) ? 'active' : '' ?>"
                           href="<?= e(url('user/profile')) ?>">
                            <!-- Hiển thị avatar nếu có, fallback về icon mặc định -->
                            <?php if (!empty($u['avatar'])): ?>
                                <img src="/uploads/avatars/<?= e($u['avatar']) ?>"
                                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.4);vertical-align:middle">
                            <?php else: ?>
                                <i class="bi bi-person-circle"></i>
                            <?php endif; ?>
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
                <!-- Nút chuyển đổi Dark/Light mode -->
                <li class="nav-item">
                    <button id="theme-toggle" class="nav-link btn btn-link border-0" title="Chuyển dark/light mode"
                            style="background:none;padding:0.5rem 0.6rem !important">
                        <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
<!-- Flash messages được render thành toast ở footer.php -->
<script>
// Khởi tạo icon đúng theo theme hiện tại
(function() {
    var theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    // Cập nhật icon sau khi DOM load xong
    document.addEventListener('DOMContentLoaded', function() {
        var icon = document.getElementById('theme-icon');
        if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    });
})();

// Xử lý click toggle dark/light mode
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var curr = document.documentElement.getAttribute('data-theme') || 'light';
        var next = curr === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        var icon = document.getElementById('theme-icon');
        if (icon) icon.className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    });
});
</script>
