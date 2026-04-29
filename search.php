<?php 
// 1. nhúng kết nối và header
require_once 'includes/db_config.php'; 
require_once 'includes/header.php'; 

// 2. lấy dữ liệu từ form tìm kiếm
$district = isset($_GET['district']) ? mysqli_real_escape_string($conn, $_GET['district']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$price = isset($_GET['price']) ? mysqli_real_escape_string($conn, $_GET['price']) : '';
$utilities = isset($_GET['utilities']) ? mysqli_real_escape_string($conn, $_GET['utilities']) : '';

// 3. xây dựng câu query động
$sql = "SELECT motel.*, categories.Name as category_name 
        FROM motel 
        JOIN categories ON motel.category_id = categories.ID 
        WHERE motel.approve = 1";

if (!empty($district)) {
    $sql .= " AND motel.district_id = '$district'";
}
if (!empty($category)) {
    $sql .= " AND motel.category_id = '$category'";
}
if (!empty($utilities)) {
    $sql .= " AND motel.utilities LIKE '%$utilities%'";
}

// xử lý lọc theo khoảng giá
if ($price == '1') {
    $sql .= " AND motel.price < 1500000";
} elseif ($price == '2') {
    $sql .= " AND motel.price BETWEEN 1500000 AND 3000000";
} elseif ($price == '3') {
    $sql .= " AND motel.price > 3000000";
}

$sql .= " ORDER BY motel.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h3 class="fw-bold border-start border-5 border-primary ps-3">Kết quả tìm kiếm</h3>
        <p class="text-muted m-0">Tìm thấy <strong><?php echo mysqli_num_rows($result); ?></strong> kết quả phù hợp</p>
    </div>

    <div class="row g-4">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($room = mysqli_fetch_assoc($result)) {
        ?>
        <div class="col-md-4">
            <div class="card room-card shadow-sm h-100">
                <div class="room-img-container position-relative" style="height: 200px; overflow: hidden;">
                    <img src="uploads/rooms/<?php echo $room['images'] ? $room['images'] : 'default-room.jpg'; ?>" class="w-100 h-100 object-fit-cover" alt="Phòng trọ">
                    <span class="badge-custom shadow-sm position-absolute top-0 start-0 m-3 bg-white px-2 py-1 rounded small">
                        <i class="fa-solid fa-tag text-primary me-1"></i> <?php echo $room['category_name']; ?>
                    </span>
                </div>
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-2 text-truncate"><?php echo $room['title']; ?></h5>
                    <p class="text-muted small mb-3">
                        <i class="fa-solid fa-location-dot me-1"></i> <?php echo $room['address']; ?>
                    </p>
                    <div class="d-flex gap-2 mb-4">
                        <span class="badge bg-blue-subtle text-primary border border-primary-subtle rounded-pill"><?php echo $room['area']; ?>m²</span>
                        <span class="badge bg-green-subtle text-success border border-success-subtle rounded-pill">Sạch sẽ</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="price-tag fw-bold text-danger fs-5"><?php echo number_format($room['price'], 0, ',', '.'); ?>đ</span>
                        <a href="detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-dark rounded-pill px-4 shadow-sm">Xem ngay</a>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
            
            echo '
            <div class="col-12 text-center my-5 py-5">
                <i class="fa-solid fa-magnifying-glass-minus fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">không tìm thấy phòng nào phù hợp với yêu cầu của m rồi...</h4>
                <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">Quay lại trang chủ</a>
            </div>';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>