<?php
session_start();
include 'includes/db_config.php';

if(!isset($_SESSION['failed'])){
    $_SESSION['failed'] = 0;
}

$username = mysqli_real_escape_string($conn, trim($_POST['username']));
$password = $_POST['password'];

// ================= CAPTCHA =================

if($_SESSION['failed'] >= 3){

    $secretKey = "6Ldg6N8sAAAAAE0drapmfrIQuWwYDazm1V5hxxxZ";

    $captcha = $_POST['g-recaptcha-response'];

    if(!$captcha){

        header("Location: login.php?error=captcha");
        exit();
    }

    $ip = $_SERVER['REMOTE_ADDR'];

    $response = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha&remoteip=$ip"
    );

    $responseKeys = json_decode($response, true);

    if(intval($responseKeys["success"]) !== 1){

        header("Location: login.php?error=captcha");
        exit();
    }
}

$sql = "SELECT * FROM user WHERE Username = '$username'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {

    $user = mysqli_fetch_assoc($result);

    $storedPassword = $user['Password'];

    $loginOk = false;

    // ===== PASSWORD VERIFY =====

    if (password_verify($password, $storedPassword)) {

        $loginOk = true;

    } elseif ($password === $storedPassword) {

        $loginOk = true;

        // tự động nâng cấp password cũ sang hash
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query(
            $conn,
            "UPDATE user SET Password='$newHash' WHERE ID={$user['ID']}"
        );
    }

    // ===== LOGIN SUCCESS =====

    if ($loginOk) {

        session_regenerate_id(true);

        unset($user['Password']);

        // avatar mặc định
        if (
            empty($user['Avatar']) ||
            $user['Avatar'] === NULL ||
            $user['Avatar'] === 'NULL'
        ) {
            $user['Avatar'] = 'default.png';
        }

        // chuẩn hóa role
        if (isset($user['Role']) && !isset($user['role'])) {
            $user['role'] = $user['Role'];
        }

        $_SESSION['user'] = $user;

        // reset số lần sai
        $_SESSION['failed'] = 0;

        header("Location: index.php");
        exit();
    }

    // ===== SAI PASSWORD =====

    $_SESSION['failed']++;

    header("Location: login.php?error=1");
    exit();

} else {

    // ===== USER KHÔNG TỒN TẠI =====

    $_SESSION['failed']++;

    header("Location: login.php?error=2");
    exit();
}
?>