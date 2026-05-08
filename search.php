<?php 
require_once 'includes/db_config.php'; 
require_once 'includes/header.php'; 

$district = $_GET['district'] ?? '';
$category = $_GET['category'] ?? '';
$price = $_GET['price'] ?? '';
$utilities = $_GET['utilities'] ?? '';
$near_vinh = $_GET['near_vinh'] ?? '';

$district = mysqli_real_escape_string($conn, $district);
$category = mysqli_real_escape_string($conn, $category);
$price = mysqli_real_escape_string($conn, $price);
$utilities = mysqli_real_escape_string($conn, $utilities);
$near_vinh = mysqli_real_escape_string($conn, $near_vinh);

// TỌA ĐỘ ĐẠI HỌC VINH MỚI
$vinhLat = 18.667238;
$vinhLng = 105.693334;

$radiusKm = 3;

$distanceSql = "
(
    6371 * acos(
        cos(radians($vinhLat)) 
        * cos(radians(motel.latitude)) 
        * cos(radians(motel.longitude) - radians($vinhLng)) 
        + sin(radians($vinhLat)) 
        * sin(radians(motel.latitude))
    )
)
";

$sql = "SELECT motel.*, categories.Name as category_name, $distanceSql AS distance_km
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

if ($price == '1') {
    $sql .= " AND motel.price < 1500000";
} elseif ($price == '2') {
    $sql .= " AND motel.price BETWEEN 1500000 AND 3000000";
} elseif ($price == '3') {
    $sql .= " AND motel.price > 3000000";
}

if ($near_vinh == '1') {

    $sql .= " AND motel.latitude IS NOT NULL 
              AND motel.longitude IS NOT NULL
              AND motel.latitude != 0
              AND motel.longitude != 0
              HAVING distance_km <= $radiusKm
              ORDER BY distance_km ASC";

} else {

    $sql .= " ORDER BY motel.created_at DESC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Lỗi tìm kiếm: " . mysqli_error($conn));
}
?>

<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h3 class="fw-bold border-start border-5 border-primary ps-3 mb-2">
                Kết quả tìm kiếm
            </h3>

            <?php if ($near_vinh == '1'): ?>
                <div class="text-muted small">
                    Đang lọc phòng trong bán kính 
                    <strong><?php echo $radiusKm; ?>km</strong> quanh Đại Học Vinh
                </div>
            <?php endif; ?>
        </div>

        <p class="text-muted m-0">
            Tìm thấy 
            <strong><?php echo mysqli_num_rows($result); ?></strong> kết quả
        </p>

    </div>

    <div class="mb-4 d-flex flex-wrap gap-2">

        <a href="search.php?near_vinh=1" class="btn btn-success rounded-pill px-4">
            <i class="fa-solid fa-school me-2"></i>
            Gần Đại học Vinh
        </a>

        <a href="search.php" class="btn btn-outline-dark rounded-pill px-4">
            <i class="fa-solid fa-rotate-left me-2"></i>
            Xóa lọc
        </a>

        <a href="index.php" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fa-solid fa-house me-2"></i>
            Trang chủ
        </a>

    </div>

    <div class="row g-4">

        <?php
        if (mysqli_num_rows($result) > 0) {

            while ($room = mysqli_fetch_assoc($result)) {

                $raw_imgs = explode(',', $room['images']); 
                $room_imgs = array_filter(array_map('trim', $raw_imgs));

                $first_img = !empty($room_imgs)
                    ? reset($room_imgs)
                    : 'default-room.jpg';
        ?>

        <div class="col-md-4">

            <div class="card room-card shadow-sm h-100">

                <div class="room-img-container position-relative" style="height: 200px; overflow: hidden;">

                    <img 
                        src="uploads/rooms/<?php echo htmlspecialchars($first_img); ?>" 
                        class="w-100 h-100 object-fit-cover"
                        alt="Phòng trọ"
                    >

                    <span class="badge-custom shadow-sm position-absolute top-0 start-0 m-3 bg-white px-2 py-1 rounded small">
                        <i class="fa-solid fa-tag text-primary me-1"></i>
                        <?php echo htmlspecialchars($room['category_name']); ?>
                    </span>

                    <?php if (!empty($room['distance_km'])): ?>

                    <span class="position-absolute top-0 end-0 m-3 bg-success text-white px-2 py-1 rounded small shadow-sm">
                        <i class="fa-solid fa-location-arrow me-1"></i>
                        <?php echo number_format($room['distance_km'], 2); ?> km
                    </span>

                    <?php endif; ?>

                </div>

                <div class="card-body p-4">

                    <h5 class="card-title fw-bold mb-2 text-truncate">
                        <?php echo htmlspecialchars($room['title']); ?>
                    </h5>

                    <p class="text-muted small mb-3 text-truncate">
                        <i class="fa-solid fa-location-dot me-1"></i>
                        <?php echo htmlspecialchars($room['address']); ?>
                    </p>

                    <div class="d-flex gap-2 mb-4 flex-wrap">

                        <span class="badge bg-blue-subtle text-primary border border-primary-subtle rounded-pill">
                            <?php echo htmlspecialchars($room['area']); ?>m²
                        </span>

                        <span class="badge bg-green-subtle text-success border border-success-subtle rounded-pill">
                            Sạch sẽ
                        </span>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto">

                        <span class="price-tag fw-bold text-danger fs-5">
                            <?php echo number_format($room['price'], 0, ',', '.'); ?>đ
                        </span>

                        <a href="detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-dark rounded-pill px-4 shadow-sm">
                            Xem ngay
                        </a>

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
                <h4 class="text-muted">
                    Không tìm thấy phòng phù hợp
                </h4>

                <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">
                    Quay lại trang chủ
                </a>
            </div>';
        }
        ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>