<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// ✔ lấy dữ liệu an toàn
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$price = (int)($_POST['price'] ?? 0);
$area = (int)($_POST['area'] ?? 0);
$address = $_POST['address'] ?? '';
$phone = $_POST['phone'] ?? '';
$utilities = $_POST['utilities'] ?? '';
$category_id = (int)($_POST['category_id'] ?? 0);
$district_id = (int)($_POST['district_id'] ?? 0);

$lat = $_POST['lat'] ?? '';
$lng = $_POST['lng'] ?? '';
$lating = ($lat && $lng) ? "$lat,$lng" : null;

// ✔ upload ảnh
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$imageNames = [];

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] == 0) {

            $fileName = time() . "_" . basename($_FILES['images']['name'][$key]);
            move_uploaded_file($tmp_name, $uploadDir . $fileName);

            $imageNames[] = $fileName;
        }
    }
}

$images = implode(",", $imageNames);

$sql = "INSERT INTO motel 
(title, description, price, area, address, lating, images, user_id, category_id, district_id, utilities, phone, approve)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare lỗi: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ssiiissiiiss",
    $title, $description, $price, $area,
    $address, $lating, $images,
    $user_id, $category_id, $district_id,
    $utilities, $phone
);

// ✔ execute + check lỗi
if (!mysqli_stmt_execute($stmt)) {
    die("Execute lỗi: " . mysqli_stmt_error($stmt));
}

// ✔ redirect đúng
header("Location: post_room.php?success=1");
exit();