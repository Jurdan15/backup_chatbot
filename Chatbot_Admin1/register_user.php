<?php
session_start();
include 'db.php';

// If not logged in, redirect to login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$current_user = strtoupper($username);

$message = "";

// Handle user creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // keep secure hashing

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $message = "<p style='color: #28c76f;'>✅ User created: $username</p>";
    } else {
        $message = "<p style='color: #ff6b6b;'>⚠️ Error: " . htmlspecialchars($stmt->error) . "</p>";
    }
}

// Handle delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $delete_id = intval($_POST['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "<p style='color: #28c76f;'>✅ User deleted successfully.</p>";
    } else {
        $message = "<p style='color: #ff6b6b;'>⚠️ Error deleting user.</p>";
    }
}

// Fetch all users
$result = $conn->query("SELECT id, username FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            background: #8B0000;
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

        /* Containers */
        .container {
            background: linear-gradient(to bottom, #006400, #32cd32); /* bamboo green gradient */
            padding: 30px;
            width: 90%;
            max-width: 700px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            margin: 20px auto;
            color: #fff;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }

        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            background: rgba(255,255,255,0.9);
            color: #333;
            font-size: 15px;
        }
        input[type=text]:focus, input[type=password]:focus {
            outline: none;
            border: 2px solid #28c76f;
        }
        button {
            background: #ffc107;
            color: #333;
            padding: 10px 18px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 6px;
            transition: background 0.3s, transform 0.2s;
            font-weight: bold;
        }
        button:hover {
            background: #ffca2c;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.95);
            color: #333;
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #28c76f;
            color: white;
        }
        td form {
            margin: 0;
        }
        .container h2, .container input, .container button{
            position: relative;
            right: 7px;
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
    <h2>Create New User</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="create_user">➕ Create User</button>
    </form>
    <?= $message ?>
</div>

<div class="container">
    <h2>Existing Users</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td>
                <form method="post">
                    <button type="submit" name="delete_user" value="<?= $row['id'] ?>">🗑 Delete</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
