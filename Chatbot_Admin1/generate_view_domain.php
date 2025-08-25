<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$yaml = "responses:\n";

$stmt = $conn->prepare("SELECT * FROM utters WHERE created_by = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

while ($utter = $result->fetch_assoc()) {
    $utter_name = $utter['utter_name'];
    $type = $utter['type'];
    $yaml .= "  $utter_name:\n";

    if ($type == 'text') {
        $text = trim($utter['content']);
        $yaml .= "    - text: |\n";
        foreach (explode("\n", $text) as $line) {
            $yaml .= "        " . rtrim($line) . "\n";
        }
    }
    elseif ($type == 'image') {
        $data = json_decode($utter['content'], true);
        $singleLineText = str_replace(["\r", "\n"], " ", trim($data['text']));
        $yaml .= "    - text: \"$singleLineText\"\n";
        $yaml .= "      image: \"" . $data['image'] . "\"\n";
    }
    elseif ($type == 'button') {
        $data = json_decode($utter['content'], true);
        $yaml .= "    - custom:\n";
        $yaml .= "        attachment:\n";
        $yaml .= "          type: template\n";
        $yaml .= "          payload:\n";
        $yaml .= "            template_type: button\n";
        $yaml .= "            text: \"" . addslashes($data['text']) . "\"\n";
        $yaml .= "            buttons:\n";
        foreach ($data['buttons'] as $btn) {
            $yaml .= "              - type: postback\n";
            $yaml .= "                title: \"" . addslashes($btn['title']) . "\"\n";
            $yaml .= "                payload: \"" . addslashes($btn['payload']) . "\"\n";
        }
    }
    elseif ($type == 'card') {
        $data = json_decode($utter['content'], true);
        $yaml .= "    - custom:\n";
        $yaml .= "        attachment:\n";
        $yaml .= "          type: template\n";
        $yaml .= "          payload:\n";
        $yaml .= "            template_type: generic\n";
        $yaml .= "            elements:\n";
        $yaml .= "              - title: \"" . addslashes($data['title']) . "\"\n";
        $yaml .= "                subtitle: \"" . addslashes($data['subtitle']) . "\"\n";
        $yaml .= "                image_url: \"" . addslashes($data['image_url']) . "\"\n";
        if (!empty($data['buttons'])) {
            $yaml .= "                buttons:\n";
            foreach ($data['buttons'] as $btn) {
                $yaml .= "                  - type: postback\n";
                $yaml .= "                    title: \"" . addslashes($btn['title']) . "\"\n";
                $yaml .= "                    payload: \"" . addslashes($btn['payload']) . "\"\n";
            }
        }
    }

    $yaml .= "\n";
}

// Save to file
file_put_contents("domain.yml", $yaml);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generated domain.yml</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1e1e2f;
            color: #eee;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #00f2ff;
        }
        pre {
            background: #2b2b3d;
            border-left: 4px solid #00f2ff;
            padding: 20px;
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,242,255,0.2);
        }
        .back-btn {
            display: block;
            width: max-content;
            margin: 30px auto 0;
            padding: 12px 24px;
            background: #28c76f;
            color: #1e1e2f;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 0 8px rgba(40,199,111,0.4);
            transition: background 0.3s, box-shadow 0.3s;
        }
        .back-btn:hover {
            background: #00f2ff;
            box-shadow: 0 0 10px rgba(0,242,255,0.6);
            color: #1e1e2f;
        }
    </style>
</head>
<body>

<h2>Generated <code>domain.yml</code></h2>

<pre><?= htmlspecialchars($yaml) ?></pre>

<a class="back-btn" href="view_utters.php">⬅ Back to Utters</a>

</body>
</html>
