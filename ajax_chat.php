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
$currentRole = (int)$_SESSION['user']['Role'];
$action = $_POST['action'] ?? '';

function checkCanChat($conn, $currentUserID, $currentRole, $receiver_id) {
    $stmt = $conn->prepare("SELECT ID, Role FROM user WHERE ID = ? LIMIT 1");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return false;
    }

    $receiver = $result->fetch_assoc();
    $receiverRole = (int)$receiver['Role'];

    // Admin được chat với tất cả
    if ($currentRole == 2) {
        return true;
    }

    // Khách thuê chỉ chat với chủ trọ
    if ($currentRole == 0 && $receiverRole == 1) {
        return true;
    }

    // Chủ trọ chỉ chat với khách thuê
    if ($currentRole == 1 && $receiverRole == 0) {
        return true;
    }

    return false;
}

if ($action === "users") {

    if ($currentRole == 0) {
        // Khách thuê chỉ thấy chủ trọ
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ? AND Role = 1
            ORDER BY Name ASC
        ");
    } elseif ($currentRole == 1) {
        // Chủ trọ chỉ thấy khách thuê
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ? AND Role = 0
            ORDER BY Name ASC
        ");
    } else {
        // Admin thấy tất cả
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ?
            ORDER BY Role DESC, Name ASC
        ");
    }

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

    if (!checkCanChat($conn, $currentUserID, $currentRole, $receiver_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Bạn chỉ được chat với chủ trọ/khách thuê phù hợp"
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO messages(sender_id, receiver_id, message)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $currentUserID, $receiver_id, $message);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Không gửi được tin nhắn"
        ]);
    }

    exit();
}

if ($action === "load") {

    $receiver_id = (int)($_POST['receiver_id'] ?? 0);

    if ($receiver_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Người nhận không hợp lệ"
        ]);
        exit();
    }

    if (!checkCanChat($conn, $currentUserID, $currentRole, $receiver_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền xem cuộc trò chuyện này"
        ]);
        exit();
    }

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