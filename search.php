<?php 
session_start();
require_once 'includes/db_config.php'; 
require_once 'includes/header.php'; 

// 1. Lấy dữ liệu lọc từ URL 
$address_search = $_GET['address'] ?? '';
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? intval($_GET['min_price']) : 0;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? intval($_GET['max_price']) : 999999999;
$near_vinh = $_GET['near_vinh'] ?? '';

// Tọa độ ĐH Vinh để tính khoảng cách
$vinhLat = 18.667238; 
$vinhLng = 105.693334; 
$radiusKm = 3;
$distanceSql = "(6371 * acos(cos(radians($vinhLat)) * cos(radians(motel.latitude)) * cos(radians(motel.longitude) - radians($vinhLng)) + sin(radians($vinhLat)) * sin(radians(motel.latitude))))";

// 2. Xây dựng SQL truy vấn (Lọc theo giá, địa chỉ và duyệt tin)
$sql = "SELECT motel.*, categories.Name as category_name, $distanceSql AS distance_km
        FROM motel 
        JOIN categories ON motel.category_id = categories.ID 
        WHERE motel.approve = 1";

// Lọc theo khoảng giá chính xác
$sql .= " AND motel.price BETWEEN $min_price AND $max_price";

if (!empty($address_search)) {
    $address_search = mysqli_real_escape_string($conn, $address_search);
    $sql .= " AND motel.address LIKE '%$address_search%'";
}

if ($near_vinh == '1') {
    $sql .= " HAVING distance_km <= $radiusKm ORDER BY distance_km ASC";
} else {
    $sql .= " ORDER BY motel.created_at DESC";
}

$result = mysqli_query($conn, $sql);
?>

<div class="container my-5">
    <div class="card shadow-sm mb-4 border-0 bg-light">
        <div class="card-body p-4">
            <form action="search.php" method="GET" class="row g-3">
                <div class="col-md-5 position-relative">
                    <label class="fw-bold mb-1">Địa điểm</label>
                    <input type="text" name="address" id="search-input" class="form-control" placeholder="Nhập tên đường..." autocomplete="off" onkeyup="fetchSuggestions(this.value)" value="<?php echo htmlspecialchars($address_search); ?>">
                    <div id="suggestion-box" class="position-absolute w-100 shadow-sm" style="z-index: 1000;"></div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold mb-1">Khoảng giá (VNĐ)</label>
                    <div class="input-group">
                        <input type="number" name="min_price" class="form-control" placeholder="Từ" value="<?php echo $_GET['min_price'] ?? ''; ?>">
                        <span class="input-group-text">-</span>
                        <input type="number" name="max_price" class="form-control" placeholder="Đến" value="<?php echo $_GET['max_price'] ?? ''; ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($room = mysqli_fetch_assoc($result)): 
                // Xử lý ảnh (nếu là link web hoặc file cục bộ)
                $img_list = explode(',', $room['images']);
                $first_img = !empty($img_list[0]) ? trim($img_list[0]) : 'default.jpg';
                $img_src = (strpos($first_img, 'http') !== false) ? $first_img : 'uploads/rooms/'.$first_img;

                // Kiểm tra trạng thái yêu thích
                $is_fav = false;
                if (isset($_SESSION['user_id'])) {
                    $uid = $_SESSION['user_id'];
                    $mid = $room['ID'];
                    $check_fav = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = $uid AND motel_id = $mid");
                    if (mysqli_num_rows($check_fav) > 0) $is_fav = true;
                }
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm position-relative">
                    <button class="btn btn-white position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" 
                            onclick="toggleWishlist(<?php echo $room['ID']; ?>, this)" 
                            style="z-index: 5; width: 35px; height: 35px;">
                        <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart text-danger"></i>
                    </button>
                    
                    <img src="<?php echo $img_src; ?>" class="card-img-top object-fit-cover" style="height: 200px;">
                    <div class="card-body">
                        <h6 class="text-primary fw-bold"><?php echo number_format($room['price'], 0, ',', '.'); ?> đ</h6>
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($room['title']); ?></h5>
                        <p class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($room['address']); ?></p>
                        <a href="detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-outline-dark w-100">Xem ngay</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><h4>Không có phòng nào khớp với yêu cầu của bạn.</h4></div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/search.js"></script>

<?php include 'includes/footer.php'; ?>