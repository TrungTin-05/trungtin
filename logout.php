<?php
session_start(); // Bắt đầu session
session_unset(); // Xóa tất cả các biến session
session_destroy(); // Hủy session
header('Content-Type: application/json'); // Thiết lập header để trả về JSON
echo json_encode(["success" => true, "message" => "Đăng xuất thành công!"]);
exit();
?>
