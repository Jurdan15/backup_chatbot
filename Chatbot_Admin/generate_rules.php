<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$yaml = "version: \"3.1\"\n\nrules:\n";

$stmt = $conn->prepare("SELECT * FROM rules WHERE created_by = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

while ($rule = $result->fetch_assoc()) {
    $yaml .= "- rule: " . $rule['rule_name'] . "\n";
    $yaml .= "  steps:\n";
    $yaml .= "  - intent: " . $rule['intent'] . "\n";
    $yaml .= "  - action: action_log_user_message\n";

    $actions_raw = $rule['utter_name'];
    if (substr($actions_raw, 0, 1) == '[') {
        $actions = json_decode($actions_raw, true);
    } else {
        $actions = array_map('trim', explode(',', $actions_raw));
    }

    foreach ($actions as $action) {
        if (!empty($action)) {
            $yaml .= "  - action: " . $action . "\n";
        }
    }
    $yaml .= "\n";
}

file_put_contents("rules.yml", $yaml);
?>
