<?php
session_start();
require_once 'config.php';

// Kiểm tra vai trò người dùng (chỉ cho phép giáo viên)
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Giáo viên') {
    die("Bạn không có quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ POST
    $lesson_id = $_POST['lesson_id'];
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];

    // Kết nối CSDL
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    // Chèn câu hỏi vào bảng 'questions'
    $stmt = $conn->prepare("INSERT INTO questions (lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $lesson_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);

    if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thêm câu hỏi thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm câu hỏi: ' . $stmt->error]);
}

    $stmt->close();
    $conn->close();
}
?>