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
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Kết nối CSDL thất bại: " . $conn->connect_error]));
}

header('Content-Type: application/json');

// Kiểm tra quyền truy cập của người dùng
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Giáo viên') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Bạn không có quyền truy cập."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy giá trị hành động từ form để phân biệt
    $action = $_POST['action'] ?? '';

    if ($action === 'create_lesson') {
        // Xử lý tạo bài học
        $title = $_POST['title'];
        $content = $_POST['content'];
        $class = $_POST['class'];
        $teacher_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO lessons (title, content, class, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $content, $class, $teacher_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Bài học đã được lưu thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi khi lưu bài học: " . $stmt->error]);
        }
        $stmt->close();

    } elseif ($action === 'create_question') {
        // Xử lý tạo câu hỏi
        $lesson_id = $_POST['lesson_id'];
        $question_text = $_POST['question_text'];
        $option_a = $_POST['option_a'];
        $option_b = $_POST['option_b'];
        $option_c = $_POST['option_c'];
        $option_d = $_POST['option_d'];
        $correct_answer = $_POST['correct_answer'];

        $stmt = $conn->prepare("INSERT INTO questions (lesson_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $lesson_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm câu hỏi thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi thêm câu hỏi: ' . $stmt->error]);
        }
        $stmt->close();

    } else {
        // Yêu cầu không xác định
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Hành động không xác định."]);
    }

} else {
    // Nếu không phải phương thức POST
    http_response_code(405); // Mã lỗi 405 Method Not Allowed
    echo json_encode(["success" => false, "message" => "Phương thức không hợp lệ."]);
}

$conn->close();
?>