<?php
session_start();
include 'includes/db_config.php';
include 'includes/header.php';
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liên hệ hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4">
            <h2 class="text-primary mb-4">
                Liên hệ 
            </h2>

            <form action="feedback_process.php"  method="POST">
                <div class="mb-3">
                    <label>Tiêu đề</label>
                    <input type="text"  name="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Nội dung</label>
                    <textarea name="content" rows="5"  class="form-control"  required></textarea>
                </div>

                <button class="btn btn-primary">
                    Gửi phản hồi
                </button>
            </form>
        </div>
    </div>

    <?php
    $userID = $user['ID'];
    $result = mysqli_query( $conn,
        "SELECT * FROM feedbacks
         WHERE UserID='$userID'
         ORDER BY ID DESC"
    );
    while($row = mysqli_fetch_assoc($result)):
    ?>

    <div class="card mt-4 shadow-sm border-0">
        <div class="card-body">
            <h5 class="text-primary"> <?= $row['Title'] ?> </h5>
            <p> <?= $row['Content'] ?></p>
            <div class="alert alert-info"> <?= $row['AutoReply'] ?></div>

            <?php if(!empty($row['AdminReply'])): ?>
                <div class="alert alert-success">
                    <b>Admin phản hồi:</b>
                    <br>
                    <?= $row['AdminReply'] ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Đang chờ admin phản hồi...
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php
mysqli_query( $conn,
    "UPDATE feedbacks
     SET IsRead = 1
     WHERE UserID='$userID'"
);
?>
</body>
</html>
<?php include 'includes/footer.php';?>