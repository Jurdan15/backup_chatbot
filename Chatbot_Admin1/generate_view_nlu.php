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

// Save to file
file_put_contents("nlu.yml", $yaml);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generated NLU File</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1e1e2f;
            color: #eee;
            padding: 20px;
        }
        h2 {
            color: #00f2ff;
            text-align: center;
            margin-bottom: 20px;
        }
        .code-block {
            background: #2b2b3d;
            color: #ccc;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            white-space: pre;
            overflow-x: auto;
            box-shadow: 0 0 12px rgba(0,242,255,0.2);
            max-width: 900px;
            margin: auto;
        }
        .back-btn {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #28c76f;
            color: #1e1e2f;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s, color 0.3s;
        }
        .back-btn:hover {
            background: #fff;
            color: #1e1e2f;
        }
    </style>
</head>
<body>

<h2>📝 Generated nlu.yml</h2>

<div class="code-block"><?= htmlspecialchars($yaml) ?></div>

<div style="text-align:center;">
    <a href="view_intents.php" class="back-btn">⬅ Back to Intents</a>
</div>

</body>
</html>
