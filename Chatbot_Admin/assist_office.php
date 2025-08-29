<?php
session_start();
include 'db.php';

// If not logged in, redirect to login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = strtoupper($_SESSION['username']); // Uppercase

// Fetch existing usernames excluding 'admin'
$offices = [];
$result = $conn->query("SELECT username FROM users WHERE username != 'admin' ORDER BY username ASC");
while ($row = $result->fetch_assoc()) {
    $offices[] = $row['username'];
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedOffice = trim($_POST['username']);

    if ($selectedOffice) {
        // Set session like login
        $_SESSION['username'] = $selectedOffice;
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "<p style='color: #ff6b6b;'>Please select an office.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
     <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Choose Office</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            background: #8B0000; /* dark red */
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

        /* Container box */
        .container {
             background: linear-gradient(to bottom, #2e562e, #398d39ff, #9cd19cff); /* dark to light green */
            padding: 30px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            text-align: center;
            margin: 50px auto;
            color: #fff;
        }

        h2 {
            margin-bottom: 20px;
            color: #fff;
        }

        select {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 6px;
            background: rgba(255,255,255,0.9);
            color: #333;
            font-size: 15px;
        }
        select:focus {
            outline: none;
            border: 2px solid #228B22;
        }

      button{
    padding: 12px 25px;
    background: #1b4d1b; /* dark green */
    color: #fff; /* white font */
    border: none;
    border-radius: 30px; /* fully rounded sides */
    font-size: 14px;
    cursor: pointer;
    display: block;
    margin: 20px auto 0 auto;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* subtle shadow */
    transition: background 0.3s, transform 0.2s;
}
button:hover {
    background: #2f7032; /* lighter green, but not too light */
    transform: translateY(-2px); /* subtle lift */
}


 .main-container {
            max-width: 1200px;
            width: 70%;
            margin: 0 auto 40px auto;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="tangquery_icon.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
</div>

<a class="back-btn" href="admindashboard.php">⬅ Back</a>
<div class="main-container">
<div class="container">
    <h2>Choose an Office</h2>
    <form method="post">
        <select name="username" required>
            <option value="">Select an office</option>
            <?php foreach ($offices as $office): ?>
                <option value="<?= htmlspecialchars($office) ?>"><?= htmlspecialchars($office) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Go to Dashboard</button>
    </form>
    <?= $message ?>
</div>
            </div>
</body>
</html>
