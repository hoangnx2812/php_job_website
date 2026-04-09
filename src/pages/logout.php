<?php
// Đăng xuất: xoá session rồi về trang chủ
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '?page=home');
exit;
