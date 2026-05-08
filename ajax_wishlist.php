<?php
session_start();
require_once 'includes/db_config.php';

// Kiểm tra xem người dùng đã đăng nhập chưa thông qua user_id trong session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để sử dụng tính năng này!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$motel_id = isset($_POST['motel_id']) ? intval($_POST['motel_id']) : 0;

if ($motel_id > 0) {
    
    $check = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = $user_id AND motel_id = $motel_id");
    
    if (mysqli_num_rows($check) > 0) {
        // Nếu đã tồn tại thì thực hiện Xóa (Bỏ thích)
        $sql = "DELETE FROM favorites WHERE user_id = $user_id AND motel_id = $motel_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'removed']);
        }
    } else {
        // Nếu chưa tồn tại thì thực hiện Thêm mới (Yêu thích)
        $sql = "INSERT INTO favorites (user_id, motel_id) VALUES ($user_id, $motel_id)";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'added']);
        }
    }
}
?>