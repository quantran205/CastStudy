let popupReceiverID = 0;
let popupInterval = null;
let popupUsersLoaded = false;

function toggleChatBox() {
    const box = document.getElementById("chatBox");

    if (box.style.display === "none" || box.style.display === "") {
        box.style.display = "block";

        if (!popupUsersLoaded) {
            loadPopupUsers();
            popupUsersLoaded = true;
        }
    } else {
        box.style.display = "none";
    }
}

function loadPopupUsers() {
    let formData = new FormData();
    formData.append("action", "users");

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById("chatUserList");
        list.innerHTML = "";

        if (data.status !== "success") {
            list.innerHTML = `<div class="chat-loading">Không tải được người dùng</div>`;
            return;
        }

        data.users.forEach(user => {
            let roleText = "Khách thuê";

            if (parseInt(user.Role) === 2) roleText = "Admin";
            if (parseInt(user.Role) === 1) roleText = "Chủ trọ";

            let div = document.createElement("div");
            div.className = "chat-user-item";
            div.onclick = function () {
                selectPopupUser(this, user.ID, user.Name);
            };

            div.innerHTML = `
                <div class="chat-user-name">${escapeHtml(user.Name)}</div>
                <div class="chat-user-role">${roleText}</div>
            `;

            list.appendChild(div);
        });
    });
}

function selectPopupUser(element, id, name) {
    popupReceiverID = id;

    document.getElementById("chatTitle").innerHTML = "Đang chat với: " + escapeHtml(name);

    document.querySelectorAll(".chat-user-item").forEach(item => {
        item.classList.remove("active");
    });

    element.classList.add("active");

    loadPopupMessages();

    if (popupInterval !== null) {
        clearInterval(popupInterval);
    }

    popupInterval = setInterval(loadPopupMessages, 2000);
}

function sendPopupMessage() {
    const input = document.getElementById("chatMessageInput");
    const message = input.value.trim();

    if (popupReceiverID === 0) {
        alert("Vui lòng chọn người để chat");
        return;
    }

    if (message === "") return;

    let formData = new FormData();
    formData.append("action", "send");
    formData.append("receiver_id", popupReceiverID);
    formData.append("message", message);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            input.value = "";
            loadPopupMessages();
        } else {
            alert(data.message);
        }
    });
}

function loadPopupMessages() {
    if (popupReceiverID === 0) return;

    let formData = new FormData();
    formData.append("action", "load");
    formData.append("receiver_id", popupReceiverID);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("chatMessages");
        box.innerHTML = "";

        if (data.status !== "success") {
            box.innerHTML = `<div class="chat-empty">Không tải được tin nhắn</div>`;
            return;
        }

        if (data.messages.length === 0) {
            box.innerHTML = `<div class="chat-empty">Chưa có tin nhắn nào</div>`;
            return;
        }

        data.messages.forEach(msg => {
            let row = document.createElement("div");

            if (parseInt(msg.sender_id) === parseInt(currentUserID)) {
                row.className = "chat-row me";
            } else {
                row.className = "chat-row other";
            }

            row.innerHTML = `
                <div class="chat-bubble">
                    <div>${escapeHtml(msg.message)}</div>
                    <div class="chat-time">${msg.time_send}</div>
                </div>
            `;

            box.appendChild(row);
        });

        box.scrollTop = box.scrollHeight;
    });
}

function escapeHtml(text) {
    let div = document.createElement("div");
    div.innerText = text;
    return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("chatMessageInput");

    if (input) {
        input.addEventListener("keyup", function (e) {
            if (e.key === "Enter") {
                sendPopupMessage();
            }
        });
    }
});