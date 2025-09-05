<?php
session_start();

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

    // Tìm người dùng trong CSDL bằng username
    // Lấy thêm trường role
    $stmt = $conn->prepare("SELECT full_name, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username_post);
    $stmt->execute();
    $stmt->store_result();

    // Kiểm tra xem có người dùng nào được tìm thấy không
    if ($stmt->num_rows > 0) {
        // Lấy mật khẩu đã hash, tên đầy đủ và vai trò từ CSDL
        $stmt->bind_result($full_name, $hashed_password, $role);
        $stmt->fetch();

        // So sánh mật khẩu đã nhập với mật khẩu đã hash
        if (password_verify($password_post, $hashed_password)) {
            // Đăng nhập thành công, lưu thông tin vào session
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username_post;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = $role; // Lưu vai trò vào session
            echo json_encode(["success" => true, "message" => "Đăng nhập thành công!", "full_name" => $full_name, "role" => $role]);
        } else {
            // Sai mật khẩu
            echo json_encode(["success" => false, "message" => "Sai tên đăng nhập hoặc mật khẩu."]);
        }
    } else {
        // Không tìm thấy tên đăng nhập
        echo json_encode(["success" => false, "message" => "Sai tên đăng nhập hoặc mật khẩu."]);
    }

    $stmt->close();
}
$conn->close();
?>
