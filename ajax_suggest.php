<?php
require_once 'includes/db_config.php';

// Nhận từ khóa từ trình duyệt gửi lên
$keyword = mysqli_real_escape_string($conn, $_GET['keyword'] ?? '');

if (strlen($keyword) > 1) {
    // Truy vấn các địa chỉ duy nhất (DISTINCT) có chứa từ khóa
    $query = "SELECT DISTINCT address FROM motel WHERE address LIKE '%$keyword%' LIMIT 5";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        echo '<div class="list-group shadow border-0">';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<button type="button" class="list-group-item list-group-item-action py-2" 
                          onclick="selectSuggest(\''.addslashes($row['address']).'\')">
                    <i class="fa-solid fa-location-dot me-2 text-muted"></i>'.htmlspecialchars($row['address']).'
                  </button>';
        }
        echo '</div>';
    }
}
?>