<?php
session_start();

// Xóa sạch phiên làm việc
session_unset();
session_destroy();

// Đẩy về trang chủ (index.php nằm cùng cấp với file này)
header("Location: index.php");
exit();
?>