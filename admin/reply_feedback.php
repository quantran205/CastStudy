<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header('Location: /index.php');
    exit();
}
$id = (int)$_POST['id'];
$reply = mysqli_real_escape_string(
    $conn,
    trim($_POST['reply'])
);
$sql = "UPDATE feedbacks
        SET
        AdminReply = '$reply',
        Status = 'done',
        IsRead = 0
        WHERE ID = '$id'";

mysqli_query($conn, $sql);
header("Location: admin_feedback.php");
exit();
?>