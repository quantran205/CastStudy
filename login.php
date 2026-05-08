<?php 
session_start();
if(!isset($_SESSION['failed'])){
    $_SESSION['failed'] = 0;
}

if(isset($_SESSION['user'])) header('Location: index.php');// nếu log rồi thì đá về trang chủ
include 'includes/header.php'; 
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg p-4 rounded-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">Đăng nhập</h2>
            <p class="text-muted small">Chào mừng bạn quay lại với GTPT Vinh Uni </p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2 small border-0 mb-4 text-center">
                <i class="fa-solid fa-circle-exclamation me-2"></i> Sai tài khoản hoặc mật khẩu!
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control bg-light border-0 p-3" placeholder="Nhập username..." required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control bg-light border-0 p-3" placeholder="********" required>
                </div>
            </div>
            <?php if($_SESSION['failed'] >= 3): ?>

            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6Ldg6N8sAAAAAGx9gXT_y4WDJOzPZZkXFJ8RHM9E"></div>
            </div>

<?php endif; ?>
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow">Đăng nhập ngay</button>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted mb-0">Chưa có tài khoản? <a href="register.php" class="text-primary fw-bold text-decoration-none">Đăng ký ngay</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>