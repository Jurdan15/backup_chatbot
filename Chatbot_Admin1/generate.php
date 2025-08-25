<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION['username'];

$yaml = "version: \"3.1\"\n\nnlu:\n";

$stmt_intents = $conn->prepare("SELECT * FROM intents WHERE office_in_charge = ?");
$stmt_intents->bind_param("s", $current_user);
$stmt_intents->execute();
$intent_query = $stmt_intents->get_result();

while ($intent = $intent_query->fetch_assoc()) {
    $yaml .= "- intent: " . $intent['name'] . "\n  examples: |\n";

    $stmt_examples = $conn->prepare("SELECT example FROM examples WHERE intent_id = ?");
    $stmt_examples->bind_param("i", $intent['id']);
    $stmt_examples->execute();
    $result = $stmt_examples->get_result();

    while ($row = $result->fetch_assoc()) {
        $yaml .= "    - " . $row['example'] . "\n";
    }
    $yaml .= "\n";
}

// Save to file only
file_put_contents("nlu.yml", $yaml);
?>
