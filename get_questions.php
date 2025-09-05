<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['lesson_id'])) {
    $lesson_id = $_GET['lesson_id'];
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die(json_encode(['error' => 'Kết nối CSDL thất bại.']));
    }

    $stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE lesson_id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    echo json_encode($questions);

    $stmt->close();
    $conn->close();
}
?>