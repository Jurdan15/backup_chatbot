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
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .top-bar {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 0 40px;
            margin-bottom: 30px;
        }
        .logout-btn {
            background: #f44336;
            color: #fff;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
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
    <form action="logout.php" method="post">
        <button class="logout-btn" type="submit">🔓 Logout</button>
    </form>
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
    <a class="card" href="register_user.php">
        <h2>👥 Manage Users</h2>
        <p>View & control user accounts</p>
    </a>
    <a class="card" href="assist_office.php">
        <h2>🏢 Assist Office</h2>
        <p>Office support tools & records</p>
    </a>
    <a class="card" href="intent_breakdown.php">
        <h2>📊 Office Inquiries</h2>
        <p>Intent breakdown by office</p>
    </a>
</div>

<div class="download-buttons">
    <a class="download-btn" href="download_nlu.php">📄 Download Intents PDF</a>
    <a class="download-btn" href="download_domain.php" download>💬 Download Domain PDF</a>
    <a class="download-btn" href="download_rules.php" download>⚙️ Download Rules PDF</a>
</div>

</body>
</html>
