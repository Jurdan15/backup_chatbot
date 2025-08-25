<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM utters WHERE id = ? AND created_by = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();

header("Location: view_utters.php");
exit;
?>
