<?php
// ======================================================
// Front controller - toàn bộ request đều chạy qua đây
// Ví dụ: /index.php?page=jobs  ->  src/pages/jobs.php
// ======================================================
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';

// Danh sách các trang hợp lệ. Key là page name, value là file thực sự.
$routes = [
    'home'                    => 'home.php',
    'login'                   => 'login.php',
    'register'                => 'register.php',
    'logout'                  => 'logout.php',
    'jobs'                    => 'jobs.php',
    'job_detail'              => 'job_detail.php',
    'companies'               => 'companies.php',

    // User
    'user/apply'              => 'user/apply.php',
    'user/my_applications'    => 'user/my_applications.php',

    // Employer
    'employer/dashboard'      => 'employer/dashboard.php',
    'employer/jobs'           => 'employer/jobs.php',
    'employer/job_form'       => 'employer/job_form.php',
    'employer/job_delete'     => 'employer/job_delete.php',
    'employer/applications'   => 'employer/applications.php',

    // Admin
    'admin/dashboard'         => 'admin/dashboard.php',
    'admin/users'             => 'admin/users.php',
    'admin/user_delete'       => 'admin/user_delete.php',
    'admin/jobs'              => 'admin/jobs.php',
    'admin/job_delete'        => 'admin/job_delete.php',
    'admin/companies'         => 'admin/companies.php',
    'admin/applications'      => 'admin/applications.php',
];

$page = $_GET['page'] ?? 'home';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'home';
}

$file = __DIR__ . '/../src/pages/' . $routes[$page];
if (!file_exists($file)) {
    http_response_code(404);
    die('Trang không tồn tại: ' . e($page));
}

require $file;
