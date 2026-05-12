<?php
// Bật báo lỗi để check
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db_config.php';

global $conn;

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header('Location: ../index.php'); exit();
}

include '../includes/header.php';

// Hàm lấy dữ liệu an toàn (để không bị trắng trang)
function get_count($conn, $query) {
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return "Lỗi SQL: " . mysqli_error($conn); // Hiện lỗi nếu query sai
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Lấy số liệu (Sửa tên bảng cho đúng với DB của m - viết thường hay viết hoa)
$count_user    = get_count($conn, "SELECT COUNT(*) as total FROM user"); 
$count_motel   = get_count($conn, "SELECT COUNT(*) as total FROM motel");
$count_pending = get_count($conn, "SELECT COUNT(*) as total FROM motel WHERE approve = 0");
$total_views   = get_count($conn, "SELECT SUM(count_view) as total FROM motel");
?>

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold text-dark"><i class="fa-solid fa-shield-halved text-primary me-3"></i>Quản trị hệ thống</h2>
        <p class="text-muted">Hệ thống đang hoạt động. Mọi điều hướng nằm ở menu góc phải.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-primary border-5">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 me-3"><i class="fa-solid fa-users fs-4"></i></div>
                    <div><div class="text-muted small fw-bold">NGƯỜI DÙNG</div><h3 class="fw-bold mb-0"><?php echo $count_user; ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-success border-5">
                <div class="d-flex align-items-center text-success">
                    <div class="p-3 bg-success bg-opacity-10 rounded-3 me-3"><i class="fa-solid fa-house-chimney fs-4"></i></div>
                    <div><div class="text-muted small fw-bold">BÀI ĐĂNG</div><h3 class="fw-bold mb-0 text-dark"><?php echo $count_motel; ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-warning border-5">
                <div class="d-flex align-items-center text-warning">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 me-3"><i class="fa-solid fa-clock-rotate-left fs-4"></i></div>
                    <div><div class="text-muted small fw-bold">CHỜ DUYỆT</div><h3 class="fw-bold mb-0 text-dark"><?php echo $count_pending; ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 border-start border-info border-5">
                <div class="d-flex align-items-center text-info">
                    <div class="p-3 bg-info bg-opacity-10 rounded-3 me-3"><i class="fa-solid fa-eye fs-4"></i></div>
                    <div><div class="text-muted small fw-bold">LƯỢT XEM</div><h3 class="fw-bold mb-0 text-dark"><?php echo number_format((float)$total_views); ?></h3></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4">Lối tắt quản lý nhanh</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="manage_rooms.php" class="btn btn-light w-100 p-4 rounded-4 text-start border-0 shadow-sm hover-up h-100">
                            <i class="fa-solid fa-folder-open text-primary mb-3 fs-3"></i>
                            <div class="fw-bold">Quản lý tin đăng</div>
                            <small class="text-muted">Kiểm soát tất cả bài viết</small>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="approve_rooms.php" class="btn btn-light w-100 p-4 rounded-4 text-start border-0 shadow-sm hover-up h-100 position-relative">
                            <i class="fa-solid fa-check-to-slot text-success mb-3 fs-3"></i>
                            <div class="fw-bold">Duyệt bài đăng</div>
                            <span class="badge bg-danger rounded-pill mt-2"><?php echo $count_pending; ?> tin mới</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="manage_users.php" class="btn btn-light w-100 p-4 rounded-4 text-start border-0 shadow-sm hover-up h-100">
                            <i class="fa-solid fa-users-gear text-info mb-3 fs-3"></i>
                            <div class="fw-bold">Quản lý Thành viên</div>
                            <small class="text-muted">Cấp quyền, khóa tài khoản</small>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="stats.php" class="btn btn-light w-100 p-4 rounded-4 text-start border-0 shadow-sm hover-up h-100">
                            <i class="fa-solid fa-chart-line text-warning mb-3 fs-3"></i>
                            <div class="fw-bold">Thống kê</div>
                            <small class="text-muted">Báo cáo tin đăng theo tháng</small>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $feedback_count = get_count(
                        $conn,
                            "SELECT COUNT(*) as total
                            FROM feedbacks
                            WHERE Status='pending'"
                        );
                        ?>
                        <a href="admin_feedback.php" class="btn btn-light w-100 p-4 rounded-4 text-start border-0 shadow-sm hover-up h-100 position-relative">
                            <i class="fa-solid fa-headset text-danger mb-3 fs-3"></i>
                            <div class="fw-bold"> Phản hồi hệ thống</div>
                            <small class="text-muted"> Trả lời phản hồi người dùng</small>
                            <?php if($feedback_count > 0): ?>
                            <span class="badge bg-danger rounded-pill mt-2">
                                <?= $feedback_count ?> phản hồi mới
                            </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-up:hover { transform: translateY(-8px); background: #f8faff !important; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; color: #4e73df; }
    .bg-opacity-10 { background-color: rgba(0, 0, 0, 0.1); }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>