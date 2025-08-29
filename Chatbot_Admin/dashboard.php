<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = strtoupper($_SESSION['username']); // Uppercase
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <meta charset="UTF-8">
    <title>Admin Dashboard | TangQuery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #8B0000; /* Dark red */
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 30px;
        }
        .header .logo-title {
            display: flex;
            align-items: center;
            font-weight: bold;
            font-size: 20px;

        }

        .header-text {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }


        .header img {
            width: 40px;
            margin-right: 10px;
           
        }

        .logout-btn {
            background: #a00000;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2); /* subtle shadow */
    transition: background 0.3s, transform 0.2s;
        }
        .logout-btn:hover {
            background: #cf3535ff;
            transform: translateY(-2px); /* subtle lift */
        }
        .welcome {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 40px 0 20px;
            color: #222;
        }
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
        }
        .card {
            background: #2e562e; /* Dark green */
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            text-align: center;
            width: 220px;
            text-decoration: none;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.3);
        }
        .card h2 {
            margin: 0 0 10px;
            font-size: 18px;
        }
        .card p {
            margin: 0;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo-title">
        <img src="favicon.ico" alt="logo">
       <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
    </div>
    <form method="post" action="logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<div class="welcome">
    WELCOME, ADMIN!
</div>

<div class="dashboard">
    <a class="card" href="view_intents.php">
        <h2>Intents</h2>
        <p>View & Manage Intents</p>
    </a>
    <a class="card" href="view_utters.php">
        <h2>Domain</h2>
        <p>View & Manage Utters</p>
    </a>
    <a class="card" href="manage_rules.php">
        <h2>Rules</h2>
        <p>View & Manage Rules</p>
    </a>
    <a class="card" href="test_bot.php">
        <h2>Test Bot</h2>
        <p>Test your chatbot responses</p>
    </a>
    <a class="card" href="intent_breakdown_user.php">
        <h2>Analytics</h2>
        <p>View & Manage Intents</p>
    </a>
    <a class="card" href="userpage.php">
        <h2>Chat</h2>
        <p>Need to ask a question or get help? Chat with the admin here.</p>
    </a>

</div>

</body>
</html>
