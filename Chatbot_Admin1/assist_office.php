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
            background: linear-gradient(to bottom, #006400, #32cd32); /* bamboo green gradient */
            padding: 30px;
            width: 100%;
            max-width: 400px;
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

        button {
            width: 100%;
            background: #28c76f;
            color: #fff;
            padding: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="logo.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
</div>

<a class="back-btn" href="dashboard.php">⬅ Back</a>

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

</body>
</html>
