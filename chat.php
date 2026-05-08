<?php
session_start();
require_once "includes/db_config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$currentUserID = (int)$_SESSION['user']['ID'];
$currentName = $_SESSION['user']['Name'] ?? 'Người dùng';

$stmt = $conn->prepare("
    SELECT ID, Name, Username, Role, Avatar
    FROM user
    WHERE ID != ?
    ORDER BY Name ASC
");

$stmt->bind_param("i", $currentUserID);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chat realtime</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef3f8;
        }

        .top-link {
            width: 1000px;
            margin: 20px auto 0;
            display: flex;
            justify-content: space-between;
        }

        .top-link a {
            text-decoration: none;
            background: #087cc1;
            color: white;
            padding: 9px 14px;
            border-radius: 6px;
        }

        .chat-wrapper {
            width: 1000px;
            height: 620px;
            margin: 25px auto;
            display: flex;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .chat-sidebar {
            width: 300px;
            background: #263238;
            color: white;
            overflow-y: auto;
        }

        .chat-sidebar h3 {
            padding: 18px;
            margin: 0;
            background: #1d2a30;
            border-bottom: 1px solid #3c5058;
        }

        .user-item {
            padding: 15px 18px;
            border-bottom: 1px solid #3c5058;
            cursor: pointer;
        }

        .user-item:hover,
        .user-item.active {
            background: #087cc1;
        }

        .user-name {
            font-weight: bold;
        }

        .user-role {
            font-size: 13px;
            color: #d0d0d0;
            margin-top: 4px;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            height: 65px;
            border-bottom: 1px solid #ddd;
            padding: 0 20px;
            display: flex;
            align-items: center;
            background: #fff;
        }

        .chat-header h3 {
            margin: 0;
            color: #333;
        }

        .messages {
            flex: 1;
            padding: 20px;
            background: #f7f9fc;
            overflow-y: auto;
        }

        .empty {
            text-align: center;
            color: #777;
            margin-top: 180px;
        }

        .message-row {
            display: flex;
            margin-bottom: 12px;
        }

        .message-row.me {
            justify-content: flex-end;
        }

        .message-row.other {
            justify-content: flex-start;
        }

        .bubble {
            max-width: 65%;
            padding: 11px 14px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .me .bubble {
            background: #087cc1;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .other .bubble {
            background: #e5e7eb;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .time {
            font-size: 11px;
            margin-top: 5px;
            opacity: 0.75;
        }

        .chat-input {
            height: 70px;
            border-top: 1px solid #ddd;
            display: flex;
            align-items: center;
            padding: 0 18px;
        }

        .chat-input input {
            flex: 1;
            height: 42px;
            border: 1px solid #ccc;
            border-radius: 22px;
            padding: 0 15px;
            outline: none;
        }

        .chat-input button {
            width: 100px;
            height: 42px;
            margin-left: 10px;
            border: none;
            border-radius: 22px;
            background: #087cc1;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .chat-input button:hover {
            background: #0567a2;
        }
    </style>
</head>

<body>

<div class="top-link">
    <a href="index.php">← Về trang chủ</a>
    <a href="logout.php" style="background:#dc3545;">Đăng xuất</a>
</div>

<div class="chat-wrapper">

    <div class="chat-sidebar">
        <h3>Danh sách người dùng</h3>

        <?php while ($u = $users->fetch_assoc()): ?>
            <div class="user-item"
                 onclick="selectUser(this, <?= (int)$u['ID'] ?>, '<?= htmlspecialchars($u['Name'], ENT_QUOTES) ?>')">

                <div class="user-name">
                    <?= htmlspecialchars($u['Name']) ?>
                </div>

                <div class="user-role">
                    <?php
                    if ($u['Role'] == 2) echo "Admin";
                    elseif ($u['Role'] == 1) echo "Chủ trọ";
                    else echo "Khách thuê";
                    ?>
                </div>

            </div>
        <?php endwhile; ?>
    </div>

    <div class="chat-main">

        <div class="chat-header">
            <h3 id="chatTitle">Chọn người để bắt đầu chat</h3>
        </div>

        <div class="messages" id="messages">
            <div class="empty">Vui lòng chọn người bên trái để nhắn tin</div>
        </div>

        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Nhập tin nhắn...">
            <button type="button" onclick="sendMessage()">Gửi</button>
        </div>

    </div>

</div>

<script>
    const currentUserID = <?= $currentUserID ?>;
    const chatAjaxUrl = "ajax_chat.php";
</script>

<script src="assets/js/chat.js?v=<?= time(); ?>"></script>

</body>
</html>