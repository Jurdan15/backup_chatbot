<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    exit("Not logged in");
}

$sender = $_SESSION['username'];
$receiver = $_POST['receiver'] ?? '';
$message = $_POST['message'] ?? '';

if ($receiver && $message) {
    $stmt = $conn->prepare("INSERT INTO chats (sender, receiver, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
}
?>
