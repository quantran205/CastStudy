<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

// ✔ check quyền
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 1 && $_SESSION['user']['role'] != 2)) {
    header('Location: index.php');
    exit();
}

// ✔ check query lỗi
$categories = mysqli_query($conn, "SELECT * FROM categories");
if (!$categories) die("Lỗi categories: " . mysqli_error($conn));

$districts  = mysqli_query($conn, "SELECT * FROM districts");
if (!$districts) die("Lỗi districts: " . mysqli_error($conn));

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Đăng phòng trọ</h3>

        <!-- THÔNG BÁO -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                🎉 Đăng phòng thành công!
            </div>
        <?php endif; ?>

        <form action="post_room_process.php" method="POST" enctype="multipart/form-data">

            <div class="row">
                <div class="col-md-6">
                    <label>Tiêu đề</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>Giá</label>
                    <input type="number" name="price" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>Diện tích</label>
                    <input type="number" name="area" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <label>Mô tả</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>SĐT</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>Tiện ích</label>
                    <input type="text" name="utilities" class="form-control">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Loại phòng</label>
                    <select name="category_id" class="form-select" required>
                        <?php while($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['ID'] ?>"><?= $c['Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Khu vực</label>
                    <select name="district_id" class="form-select" required>
                        <?php while($d = mysqli_fetch_assoc($districts)): ?>
                            <option value="<?= $d['ID'] ?>"><?= $d['Name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- MAP -->
            <div class="mt-4">
                <label>Chọn vị trí (bắt buộc)</label>
                <div id="map" style="height:400px;"></div>
                <input type="hidden" name="lat" id="lat" required>
                <input type="hidden" name="lng" id="lng" required>
            </div>

            <!-- ẢNH -->
            <div class="mt-4">
                <label>Ảnh phòng</label>
                <input type="file" name="images[]" class="form-control" multiple onchange="previewImages(event)">
                <div id="preview" class="d-flex flex-wrap mt-2"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-4">Đăng phòng</button>
        </form>
    </div>
</div>

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
            img.style.margin = "5px";
            img.style.borderRadius = "8px";
            preview.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
}

// MAP
let map, marker;

function initMap() {
    const vinh = { lat: 18.6733, lng: 105.6923 };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: vinh
    });

    map.addListener("click", function (e) {

        if (marker) {
            marker.setPosition(e.latLng);
        } else {
            marker = new google.maps.Marker({
                position: e.latLng,
                map: map
            });
        }

        document.getElementById("lat").value = e.latLng.lat();
        document.getElementById("lng").value = e.latLng.lng();
    });
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>