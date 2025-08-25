<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rasa NLU Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1e1e2f;
            color: #eee;
            margin: 0;
            padding: 40px;
            min-height: 100vh;
        }
        .top-bar {
            display: flex;
            justify-content: flex-end;
            padding: 0 40px;
            margin-bottom: 20px;
        }
        .logout-btn {
            background: #f44336;
            color: #1e1e2f;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s ease;
            box-shadow: 0 0 8px rgba(0,242,255,0.4);
        }
        .logout-btn:hover {
            background: #d32f2f;
        }
        .dashboard {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .card {
            background: #2b2b3d;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,242,255,0.15);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #eee;
            width: 220px;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 20px rgba(0,242,255,0.4);
            background: #1e1e2f;
        }
        .card h2 {
            margin: 0 0 10px;
            font-size: 22px;
            color: #00f2ff;
        }
        .card p {
            font-size: 14px;
            color: #ccc;
        }
        .download-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .download-btn {
            background: #00f2ff;
            color: #1e1e2f;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            transition: background 0.3s, color 0.3s;
            box-shadow: 0 0 8px rgba(0,242,255,0.4);
        }
        .download-btn:hover {
            background: #28c76f;
            color: #1e1e2f;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <a class="logout-btn" href="logout.php">🚪 Logout</a>
</div>

<div class="dashboard">
    <a class="card" href="view_intents.php">
        <h2>📄 Intents</h2>
        <p>View & manage intents</p>
    </a>
    <a class="card" href="view_utters.php">
        <h2>💬 Domain (Utters)</h2>
        <p>View & manage utters</p>
    </a>
    <a class="card" href="manage_rules.php">
        <h2>⚙️ Rules</h2>
        <p>View & manage rules</p>
    </a>
    <a class="card" href="test_bot.php">
        <h2>🤖 Test Bot</h2>
        <p>Test your chatbot responses</p>
    </a>
    <a class="card" href="intent_breakdown_user.php">
        <h2>📊 Office Inquiries</h2>
        <p>Intent breakdown per office</p>
    </a>
</div>

<div class="download-buttons">
    <a class="download-btn" href="download_nlu.php">📄 Download Intents PDF</a>
    <a class="download-btn" href="download_domain.php" download>💬 Download Domain PDF</a>
    <a class="download-btn" href="download_rules.php" download>⚙️ Download Rules PDF</a>
</div>

</body>
</html>
