<?php
session_start();
require_once "includes/db_config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['user'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Bạn chưa đăng nhập"
    ]);
    exit();
}

$currentUserID = (int)$_SESSION['user']['ID'];
$action = $_POST['action'] ?? '';

if ($action === "users") {
    $stmt = $conn->prepare("
        SELECT ID, Name, Role
        FROM user
        WHERE ID != ?
        ORDER BY Role DESC, Name ASC
    ");
    $stmt->bind_param("i", $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "users" => $users
    ]);
    exit();
}

if ($action === "send") {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($receiver_id <= 0 || $message === '') {
        echo json_encode([
            "status" => "error",
            "message" => "Dữ liệu không hợp lệ"
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO messages(sender_id, receiver_id, message)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $currentUserID, $receiver_id, $message);
    $stmt->execute();

    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === "load") {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT 
            ID,
            sender_id,
            receiver_id,
            message,
            DATE_FORMAT(created_at, '%H:%i') AS time_send
        FROM messages
        WHERE 
            (sender_id = ? AND receiver_id = ?)
            OR
            (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");

    $stmt->bind_param("iiii", $currentUserID, $receiver_id, $receiver_id, $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "messages" => $messages
    ]);
    exit();
}

echo json_encode([
    "status" => "error",
    "message" => "Action không hợp lệ"
]);
exit();