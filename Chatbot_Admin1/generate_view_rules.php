<?php
session_start();
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
<!DOCTYPE html>
<html>
<head>
    <title>Generated rules.yml</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e2f;
            color: #00f2ff;
            padding: 20px;
        }
        pre {
            background: #2b2b3d;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-wrap;
            word-wrap: break-word;
            box-shadow: 0 0 10px rgba(0,242,255,0.2);
        }
        a.back-btn {
            display: inline-block;
            background: #28c76f;
            color: #1e1e2f;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 0 6px rgba(40,199,111,0.3);
        }
        a.back-btn:hover {
            background: #00f2ff;
            color: #1e1e2f;
            box-shadow: 0 0 8px rgba(0,242,255,0.5);
        }
    </style>
</head>
<body>

<h2>Generated rules.yml</h2>
<pre><?= htmlspecialchars($yaml) ?></pre>

<a class="back-btn" href="manage_rules.php">⬅ Back to Manage Rules</a>

</body>
</html>
