<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để làm bài.']);
    exit();
}

$username = $_SESSION['username'];
$answers = json_decode($_POST['answers'], true); 

if (empty($answers)) {
    echo json_encode(['success' => false, 'message' => 'Không có câu trả lời nào được gửi.']);
    exit();
}

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL.']);
    exit();
}

$score = 0;
$total = count($answers);

// Vòng lặp để kiểm tra từng câu trả lời
foreach ($answers as $question_id => $user_answer) {
    $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    
    $is_correct = ($question['correct_answer'] === $user_answer);
    if ($is_correct) {
        $score++;
    }

    // Lưu từng câu trả lời của người dùng vào CSDL
    $stmt_save = $conn->prepare("INSERT INTO user_answers (username, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
    $stmt_save->bind_param("sisi", $username, $question_id, $user_answer, $is_correct);
    $stmt_save->execute();
    $stmt_save->close();
    $stmt->close();
}

$conn->close();

echo json_encode(['success' => true, 'score' => $score, 'total' => $total, 'message' => "Bạn đã hoàn thành bài tập. Kết quả: $score/$total."]);
?>