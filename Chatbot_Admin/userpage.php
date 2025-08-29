<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); exit;
}
$username = $_SESSION['username'];
if ($username === 'admin') {
    header("Location: adminpage.php"); exit;
}
$current_user = strtoupper($username);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Chat</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            margin: 0; 
            padding: 0; 
        }

        /* Header */
        .header {
            background: #8B0000; /* deep bamboo green */
            border-bottom: 2px solid #ddd;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header img {
            height: 40px;
        }
        .header-text {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        /* Back button */
        .back-btn {
            display: inline-block;
            margin: 20px;
            padding: 10px 18px;
            background: #8B0000;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        .back-btn:hover {
            opacity: 0.85;
        }

        /* Chat container */
        .chat-container {
            height: 550px;
            width: 500px;
            max-width: 95%;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: auto;
        }

        /* Chat header inside the box */
        .chat-header {
            background: #198754; /* bamboo mid-green */
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        /* Chat messages area */
        #chat-box { 
            flex: 1;
            overflow-y: auto; 
            padding: 12px; 
            background: linear-gradient(to bottom, #2e562e, #398d39ff, #9cd19cff); /* dark to light green */
        }

        /* Message bubbles */
        .msg { 
            margin: 6px 0; 
            padding: 10px 14px; 
            border-radius: 18px; 
            max-width: 40%;           
            word-wrap: break-word;    
            overflow-wrap: break-word;
            display: inline-block; 
            clear: both;
        }
        .me { 
            background: #d1ffd1; 
            float: right; 
            text-align: left; 
        }
        .other { 
            background: #f5f5f5; 
            float: left; 
            text-align: left; 
        }

        /* Chat form */
        .chat-form {
            display: flex;
            border-top: 1px solid #ccc;
        }
        .chat-form input { 
            flex: 1; 
            padding: 12px; 
            border: none; 
            outline: none; 
            font-size: 14px;
        }
        .chat-form button { 
            padding: 12px 18px; 
            border: none; 
            background: #145214; 
            color: white; 
            cursor: pointer; 
            font-size: 14px;
            transition: background 0.3s;
        }
        .chat-form button:hover {
            background: #1f6f1f;
        }
    </style>
</head>
<body>

<!-- Page header -->
<div class="header">
    <img src="tangquery_icon.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> CHAT
    </div>
</div>

<!-- Back button -->
<a class="back-btn" href="dashboard.php">⬅ Back</a>

<!-- Chat container -->
<div class="chat-container">
    <div class="chat-header">Chat with Admin</div>
    <div id="chat-box"></div>
    <form id="chat-form" class="chat-form">
        <input type="text" id="message" placeholder="Type message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
function loadMessages() {
    fetch("get_messages.php?other=admin")
    .then(res => res.json())
    .then(data => {
        let box = document.getElementById("chat-box");
        box.innerHTML = "";
        data.forEach(m => {
            let div = document.createElement("div");
            div.className = "msg " + (m.sender === "<?php echo $username; ?>" ? "me" : "other");
            div.textContent = m.message;
            box.appendChild(div);
        });
        box.scrollTop = box.scrollHeight;
    });
}
setInterval(loadMessages, 2000);
loadMessages();

document.getElementById("chat-form").addEventListener("submit", function(e) {
    e.preventDefault();
    let msg = document.getElementById("message").value;
    fetch("send_message.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "receiver=admin&message=" + encodeURIComponent(msg)
    }).then(() => {
        document.getElementById("message").value = "";
        loadMessages();
    });
});
</script>
</body>
</html>
