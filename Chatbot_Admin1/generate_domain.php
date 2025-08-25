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
    } elseif ($type == 'image') {
        $data = json_decode($utter['content'], true);
        $singleLineText = str_replace(["\r", "\n"], " ", trim($data['text']));
        $yaml .= "    - text: \"$singleLineText\"\n";
        $yaml .= "      image: \"" . $data['image'] . "\"\n";
    } elseif ($type == 'button') {
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
    } elseif ($type == 'card') {
        $data = json_decode($utter['content'], true);
        $yaml .= "    - custom:\n";
        $yaml .= "        attachment:\n";
        $yaml .= "          type: \"template\"\n";
        $yaml .= "          payload:\n";
        $yaml .= "            template_type: \"generic\"\n";
        $yaml .= "            elements:\n";
        $yaml .= "              - title: \"" . addslashes($data['title']) . "\"\n";
        $yaml .= "                subtitle: \"" . addslashes($data['subtitle']) . "\"\n";
        $yaml .= "                image_url: \"" . $data['image_url'] . "\"\n";
        $yaml .= "                buttons:\n";
        foreach ($data['buttons'] as $btn) {
            $yaml .= "                  - type: \"postback\"\n";
            $yaml .= "                    title: \"" . addslashes($btn['title']) . "\"\n";
            $yaml .= "                    payload: \"" . addslashes($btn['payload']) . "\"\n";
        }
    }

    $yaml .= "\n";
}

file_put_contents("domain.yml", $yaml);
?>
