<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

// chỉ cho chủ trọ/admin đăng phòng
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 2)) {
    header('Location: index.php');
    exit();
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
if (!$categories) die("Lỗi categories: " . mysqli_error($conn));

$districts = mysqli_query($conn, "SELECT * FROM districts");
if (!$districts) die("Lỗi districts: " . mysqli_error($conn));

include 'includes/header.php';
?>

<!-- Leaflet CSS -->
<link 
    rel="stylesheet" 
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<div class="container mt-4">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Đăng phòng trọ</h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                🎉 Đăng phòng thành công! tin của bạn đang chờ duyệt.
            </div>
        <?php endif; ?>

        <form action="post_room_process.php" method="POST" enctype="multipart/form-data">

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Giá</label>
                    <input type="number" name="price" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Diện tích</label>
                    <input type="number" name="area" class="form-control" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">SĐT</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tiện ích</label>
                    <input type="text" name="utilities" class="form-control" placeholder="Wifi, máy lạnh, gác...">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Loại phòng</label>
                    <select name="category_id" class="form-select" required>
                        <?php while($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $c['ID']; ?>">
                                <?php echo htmlspecialchars($c['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Khu vực</label>
                    <select name="district_id" class="form-select" required>
                        <?php while($d = mysqli_fetch_assoc($districts)): ?>
                            <option value="<?php echo $d['ID']; ?>">
                                <?php echo htmlspecialchars($d['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- MAP -->
            <div class="mt-4">
                <label class="form-label fw-bold">Chọn vị trí phòng trọ trên bản đồ</label>
                <p class="text-muted small mb-2">
                    Bấm vào bản đồ để ghim vị trí. Tọa độ sẽ tự động lưu vào hệ thống.
                </p>

                <div id="map" style="height: 400px; border-radius: 12px;"></div>

                <input type="hidden" name="lat" id="lat" required>
                <input type="hidden" name="lng" id="lng" required>

                <div class="mt-2 small text-muted">
                    Tọa độ đã chọn:
                    <span id="showLat">Chưa chọn</span>,
                    <span id="showLng">Chưa chọn</span>
                </div>
            </div>

            <!-- ẢNH -->
            <div class="mt-4">
                <label class="form-label">Ảnh phòng</label>
                <input type="file" name="images[]" class="form-control" multiple onchange="previewImages(event)">
                <div id="preview" class="d-flex flex-wrap mt-2"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-4">
                Đăng phòng
            </button>
        </form>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// preview ảnh
function previewImages(event) {
    let preview = document.getElementById('preview');
    preview.innerHTML = "";

    for (let file of event.target.files) {
        let reader = new FileReader();

        reader.onload = function(e) {
            let img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "100px";
            img.style.height = "100px";
            img.style.objectFit = "cover";
            img.style.margin = "5px";
            img.style.borderRadius = "8px";
            preview.appendChild(img);
        };

        reader.readAsDataURL(file);
    }
}

// tọa độ Đại học Vinh
const vinhUniversity = [18.667238, 105.693334];

// tạo map
const map = L.map('map').setView(vinhUniversity, 15);

// dùng OpenStreetMap, không cần API key
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// marker Đại học Vinh
L.marker(vinhUniversity)
    .addTo(map)
    .bindPopup("Đại học Vinh")
    .openPopup();

let roomMarker = null;

// click để chọn tọa độ phòng
map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;

    document.getElementById('showLat').innerText = lat.toFixed(6);
    document.getElementById('showLng').innerText = lng.toFixed(6);

    if (roomMarker) {
        roomMarker.setLatLng(e.latlng);
    } else {
        roomMarker = L.marker(e.latlng).addTo(map);
    }

    roomMarker.bindPopup("Vị trí phòng trọ đã chọn").openPopup();
});
</script>

<?php include 'includes/footer.php'; ?>