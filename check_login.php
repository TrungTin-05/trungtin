<?php
session_start();
header('Content-Type: application/json');

$response = [
    'loggedin' => false,
    'full_name' => null,
    'role' => null // Thêm trường role
];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $response['loggedin'] = true;
    $response['full_name'] = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;
    $response['role'] = isset($_SESSION['role']) ? $_SESSION['role'] : null; // Lấy role từ session
}

echo json_encode($response);
?>