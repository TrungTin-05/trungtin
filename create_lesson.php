<?php
// Bao gồm tệp kết nối cơ sở dữ liệu và các hàm tạo dữ liệu
require_once 'database/config.php';
require_once 'database/create_data.php';

// Kiểm tra xem yêu cầu có phải là POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $tieu_de = $_POST['title'];
    $noi_dung = $_POST['content'];
    $lop = $_POST['grade'];

    // Gọi hàm để thêm bài học vào cơ sở dữ liệu
    $result = themBaiHoc($tieu_de, $noi_dung, $lop);

    if ($result) {
        echo "Thêm bài học thành công!";
    } else {
        echo "Lỗi khi thêm bài học.";
    }
} else {
    // Nếu không phải phương thức POST, chuyển hướng về trang giáo viên
    header("Location: teacher.html");
    exit();
}
?>
