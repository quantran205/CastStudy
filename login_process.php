<?php
session_start();
include 'includes/db_config.php'; // kết nối conn m đã tạo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // dùng mysqli_real_escape_string để thầy không bảo m hổng bảo mật sql injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // query tìm user theo username
    $sql = "SELECT * FROM USER WHERE Username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // kiểm tra mật khẩu (t đang để so sánh trực tiếp, nếu m dùng md5 hay hash thì sửa lại nhé)
        if ($password == $user['Password']) {
            // lưu thông tin vào session để xài ở header.php
            $_SESSION['user'] = [
                'id' => $user['ID'],
                'name' => $user['Name'],
                'username' => $user['Username'],
                'role' => $user['Role'],
                'avatar' => $user['Avatar']
            ];

            // phân quyền điều hướng
            if ($user['Role'] == 2) {
                header('Location: admin/dashboard.php'); // admin thì vào dashboard
            } else {
                header('Location: index.php'); // còn lại về trang chủ
            }
            exit();
        }
    }
    
    // nếu sai thì quay lại trang login kèm thông báo lỗi
    header('Location: login.php?error=1');
    exit();
}
?>