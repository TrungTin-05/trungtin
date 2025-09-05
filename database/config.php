<?php
// Database connection details. Use constants for easy management.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'websitetoanhoc');

// Tạo kết nối
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Kiểm tra kết nối và hiển thị lỗi cụ thể
if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}

// Thiết lập bộ ký tự (quan trọng để hiển thị tiếng Việt)
$conn->set_charset("utf8mb4");
?>
