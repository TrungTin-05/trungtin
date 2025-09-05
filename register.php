<?php
// Thông tin kết nối CSDL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "websitetoanhoc";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Kiểm tra xem dữ liệu POST có tồn tại không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username_post = $_POST['username'];
    $password_post = $_POST['password'];
    $full_name_post = $_POST['full_name'];
    $role_post = $_POST['user_role']; // Sửa từ 'user_role_select' thành 'user_role'

    // Kiểm tra tên người dùng đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username_post);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Tên người dùng đã tồn tại, trả về lỗi
        echo json_encode(["success" => false, "message" => "Tên đăng nhập đã tồn tại."]);
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password_post, PASSWORD_DEFAULT);

        // Chuẩn bị câu lệnh SQL để chèn dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username_post, $hashed_password, $full_name_post, $role_post);

        // Thực thi câu lệnh
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Đăng ký thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi: " . $stmt->error]);
        }
    }
    $check_stmt->close();
    $stmt->close();
}
$conn->close();
?>