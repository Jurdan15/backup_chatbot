<?php
include 'db.php';

$intent_id = $_GET['id'] ?? null;
if (!$intent_id) {
    die("Invalid request.");
}

// Delete intent (examples will auto-delete due to foreign key constraint)
$stmt = $conn->prepare("DELETE FROM intents WHERE id = ?");
$stmt->bind_param("i", $intent_id);
$stmt->execute();

echo "<script>alert('Intent deleted successfully'); window.location='view_intents.php';</script>";
?>
