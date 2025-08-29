<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    exit("Not logged in");
}

$user = $_SESSION['username'];
$other = $_GET['other'] ?? '';

if ($other) {
    $stmt = $conn->prepare("SELECT * FROM chats 
        WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?) 
        ORDER BY timestamp ASC");
    $stmt->bind_param("ssss", $user, $other, $other, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode($messages);
}
?>
