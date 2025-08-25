<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];

        if ($user['username'] === 'admin') {
            header("Location: admindashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | Intent Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1e1e2f;
            color: #00f2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #2b2b3d;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,242,255,0.2);
            text-align: center;
        }
        .container img {
            width: 160px;
            height: 120px;
            border-radius: 20%;
            object-fit: cover;
            box-shadow: 0 0 20px rgba(0,242,255,0.5);
            margin-bottom: 20px;
            transition: box-shadow 0.3s;
        }
        .container img:hover {
            box-shadow: 0 0 30px rgba(0,242,255,0.8);
        }
        h2 {
            margin-bottom: 20px;
            color: #00f2ff;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            background: #1e1e2f;
            color: #00f2ff;
            box-shadow: inset 0 0 5px rgba(0,242,255,0.2);
        }
        input[type=text]:focus, input[type=password]:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(0,242,255,0.6);
        }
        button {
            width: 100%;
            background: #28c76f;
            color: #1e1e2f;
            padding: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            transition: background 0.3s, box-shadow 0.3s;
            box-shadow: 0 0 6px rgba(40,199,111,0.3);
        }
        button:hover {
            background: #00f2ff;
            color: #1e1e2f;
            box-shadow: 0 0 8px rgba(0,242,255,0.5);
        }
        .error {
            color: #ff6b6b;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <img src="logo.png" alt="Logo">
    <h2>Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
