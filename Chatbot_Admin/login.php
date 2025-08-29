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
<html lang="en">
<head>
    <meta charset="UTF-8">
     <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Login | TangQuery</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 15px;
        }
        .container {
            background: #fff;
            padding: 30px;
            width: 100%;
            max-width: 500px; /* responsive container */
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .container img {
            width: 80%;
            max-width: 300px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: normal;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 10px 0; /* equal spacing top & bottom only */
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            display: block;
        }
        input:focus {
            outline: none;
            border-color: #3c6e3c;
            box-shadow: 0 0 6px rgba(60,110,60,0.3);
        }
        button {
            width: 100%;
            background: #3c6e3c;
            color: white;
            padding: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            transition: background 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background: #2e562e;
        }
        .error {
            color: #ff0000;
            margin-top: 15px;
        }

        /* Mobile responsiveness */
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            input[type=text], input[type=password], button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Replace with your actual logo -->
    <img src="tangquery_logo.png" alt="TangQuery Logo">
    <h2>The CSU-Gonzaga Inquiry Assistance Chatbot</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log in</button>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
