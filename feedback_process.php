<?php
session_start();
include 'includes/db_config.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
$userID = $user['ID'];
$title = mysqli_real_escape_string( $conn, $_POST['title']);
$content = mysqli_real_escape_string( $conn, $_POST['content']);
$autoReply =
"Hệ thống đã nhận phản hồi của bạn. "
.
"Admin sẽ phản hồi sớm nhất có thể.";
$sql = "INSERT INTO feedbacks (UserID, Title, Content, AutoReply)
        VALUES ('$userID', '$title', '$content','$autoReply')";

mysqli_query($conn, $sql);
header("Location: feedback.php");
?>