<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$current_user = strtoupper($username); // For header
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM utters WHERE id = ? AND created_by = ?");
$stmt->bind_param("is", $id, $username);
$stmt->execute();
$result = $stmt->get_result();
$utter = $result->fetch_assoc();

if (!$utter) {
    die("Utter not found or access denied.");
}

// Fetch intents
$intent_stmt = $conn->prepare("SELECT name FROM intents WHERE office_in_charge = ?");
$intent_stmt->bind_param("s", $username);
$intent_stmt->execute();
$intents_result = $intent_stmt->get_result();
$intents = [];
while ($row = $intents_result->fetch_assoc()) {
    $intents[] = $row['name'];
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utter_name = trim($_POST['utter_name']);
    $type = $_POST['utter_type'];

    if ($type == 'text') {
        $content = $_POST['content'];
    } elseif ($type == 'image') {
        $text = trim($_POST['text_content']);
        $image = trim($_POST['image_url']);
        $content = json_encode(['text' => $text, 'image' => $image]);
    } elseif ($type == 'button') {
        $text = $_POST['text'];
        $titles = $_POST['title'] ?? [];
        $payloads = $_POST['payload'] ?? [];
        $buttons = [];
        for ($i = 0; $i < count($titles); $i++) {
            if (trim($titles[$i]) && trim($payloads[$i])) {
                $buttons[] = ['title' => $titles[$i], 'payload' => $payloads[$i]];
            }
        }
        $content = json_encode(['text' => $text, 'buttons' => $buttons]);
    } elseif ($type == 'card') {
        $title = trim($_POST['card_title']);
        $subtitle = trim($_POST['card_subtitle']);
        $image_url = trim($_POST['card_image_url']);
        $titles = $_POST['card_button_title'] ?? [];
        $payloads = $_POST['card_button_payload'] ?? [];
        $buttons = [];
        for ($i = 0; $i < count($titles); $i++) {
            if (trim($titles[$i]) && trim($payloads[$i])) {
                $buttons[] = ['title' => $titles[$i], 'payload' => $payloads[$i]];
            }
        }
        $content = json_encode([
            'title' => $title,
            'subtitle' => $subtitle,
            'image_url' => $image_url,
            'buttons' => $buttons
        ]);
    }

    // Store old utter_name before update
    $old_utter_name = $utter['utter_name'];

    // Update utters table
    $stmt = $conn->prepare("UPDATE utters SET utter_name = ?, content = ?, type = ? WHERE id = ? AND created_by = ?");
    $stmt->bind_param("sssis", $utter_name, $content, $type, $id, $username);
    $stmt->execute();

    // Update rules table if necessary
    $old_json_name = json_encode([$old_utter_name], JSON_UNESCAPED_SLASHES);
    $new_json_name = json_encode([$utter_name], JSON_UNESCAPED_SLASHES);

    $check_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM rules WHERE utter_name = ? AND created_by = ?");
    $check_stmt->bind_param("ss", $old_json_name, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result()->fetch_assoc();

    if ($check_result['cnt'] > 0) {
        $update_rules_stmt = $conn->prepare("UPDATE rules SET utter_name = ? WHERE utter_name = ? AND created_by = ?");
        $update_rules_stmt->bind_param("sss", $new_json_name, $old_json_name, $username);
        $update_rules_stmt->execute();
    }

    header("Location: view_utters.php");
    exit;
}

$data = json_decode($utter['content'], true);
$current_type = $utter['type'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Utter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Header */
        .header {
            background: #8B0000;
            border-bottom: 2px solid #ddd;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header img { height: 40px; }
        .header-text {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        /* Main actions */
        .main-actions {
            margin: 20px;
            display: flex;
            justify-content: flex-start;
        }
        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            transition: 0.3s;
            background: #8B0000;
        }
        .btn:hover { opacity: 0.85; }
        form h2, form label, form input{
            position: relative;
            right: 8px;
        }
        #utter_type{
            position: relative;
            right: 8px;
            width: 103%;
        }
        .draggable ,#text{
            position: relative;
            right: 8px;
            width: 99%;

        }
        

        /* Form container with green gradient */
        form {
            background: linear-gradient(135deg, #006400, #32cd32);
            padding: 30px;
            max-width: 700px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            color: #fff;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 6px 0 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: inherit;
        }
        textarea { resize: vertical; }

        .btn-small, .add-btn, button[type=submit] {
            border: none; cursor: pointer; border-radius: 5px;
        }
        .btn-small {
            background: #e53935;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }
        .add-btn {
            background: #ffc107;
            color: #333;
            padding: 10px 18px;
            margin-top: 10px;
            display: inline-block;
        }
        button[type=submit] {
            background: #fff;
            color: #006400;
            font-weight: bold;
            padding: 12px 24px;
            margin: 20px auto 0;
            display: block;
        }
        h2 { text-align: center; color: #fff; }

        .draggable{
            background: rgba(255,255,255,0.9);
            color: #333;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #228B22;
            border-radius: 6px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .type-section { display: none; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="logo.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
</div>

<!-- Back Button -->
<div class="main-actions">
    <a href="view_utters.php" class="btn">⬅️ Back</a>
</div>

<h2>Edit Utter: <?= htmlspecialchars($utter['utter_name']) ?></h2>

<form method="post">
    <label>Utter Name:</label>
    <input type="text" name="utter_name" 
       value="<?= htmlspecialchars($utter['utter_name']) ?>" 
       required 
       oninput="formatUtterName(this)">

    <label>Utter Type:</label>
    <select name="utter_type" id="utter_type" onchange="showSection(this.value)">
        <option value="text" <?= $current_type == 'text' ? 'selected' : '' ?>>Text</option>
        <option value="image" <?= $current_type == 'image' ? 'selected' : '' ?>>Image</option>
        <option value="button" <?= $current_type == 'button' ? 'selected' : '' ?>>Button</option>
        <option value="card" <?= $current_type == 'card' ? 'selected' : '' ?>>Card</option>
    </select>

    <!-- Sections -->
    <div class="type-section" id="section_text">
        <label>Text Content:</label>
        <textarea id="text"name="content" rows="6"><?= $current_type == 'text' ? htmlspecialchars($utter['content']) : '' ?></textarea>
    </div>

    <div class="type-section" id="section_image">
        <label>Text:</label>
        <input type="text" name="text_content" value="<?= $current_type == 'image' ? htmlspecialchars($data['text']) : '' ?>">
        <label>Image URL:</label>
        <input type="text" name="image_url" value="<?= $current_type == 'image' ? htmlspecialchars($data['image']) : '' ?>">
    </div>

    <div class="type-section" id="section_button">
        <label>Text Above Buttons:</label>
        <input type="text" name="text" value="<?= $current_type == 'button' ? htmlspecialchars($data['text']) : '' ?>">
        <div id="buttons-container">
            <?php if ($current_type == 'button'): foreach ($data['buttons'] as $btn): ?>
            <div class="draggable" draggable="true" ondragstart="drag(event)" ondragover="allowDrop(event)" ondrop="drop(event, this)">
                <input type="text" name="title[]" value="<?= htmlspecialchars($btn['title']) ?>" required>
                <select name="payload[]" required>
                    <option value="">Select Intent...</option>
                    <?php foreach ($intents as $intentName): ?>
                        <option value="/<?= htmlspecialchars($intentName) ?>" <?= ("/$intentName" == $btn['payload'] ? 'selected' : '') ?>>/<?= htmlspecialchars($intentName) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn-small" onclick="this.parentElement.remove();">Remove</button>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" class="add-btn" onclick="addButtonField()">➕ Add Button</button>
    </div>

    <div class="type-section" id="section_card">
        <label>Card Title:</label>
        <input type="text" name="card_title" value="<?= $current_type == 'card' ? htmlspecialchars($data['title']) : '' ?>">
        <label>Card Subtitle:</label>
        <input type="text" name="card_subtitle" value="<?= $current_type == 'card' ? htmlspecialchars($data['subtitle']) : '' ?>">
        <label>Image URL:</label>
        <input type="text" name="card_image_url" value="<?= $current_type == 'card' ? htmlspecialchars($data['image_url']) : '' ?>">
        <div id="card-buttons-container">
            <?php if ($current_type == 'card'): foreach ($data['buttons'] as $btn): ?>
            <div class="draggable" draggable="true" ondragstart="drag(event)" ondragover="allowDrop(event)" ondrop="drop(event, this)">
                <input type="text" name="card_button_title[]" value="<?= htmlspecialchars($btn['title']) ?>" required>
                <select name="card_button_payload[]" required>
                    <option value="">Select Intent...</option>
                    <?php foreach ($intents as $intentName): ?>
                        <option value="/<?= htmlspecialchars($intentName) ?>" <?= ("/$intentName" == $btn['payload'] ? 'selected' : '') ?>>/<?= htmlspecialchars($intentName) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn-small" onclick="this.parentElement.remove();">Remove</button>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" class="add-btn" onclick="addCardButtonField()">➕ Add Card Button</button>
    </div>

    <button type="submit">💾 Save Changes</button>
</form>

<script>
let dragSrc = null;
function allowDrop(ev) { ev.preventDefault(); }
function drag(ev) { dragSrc = ev.target; ev.dataTransfer.effectAllowed = 'move'; }
function drop(ev, target) { ev.preventDefault(); if (dragSrc && dragSrc !== target) { target.parentNode.insertBefore(dragSrc, target); } }

function showSection(type) {
    document.querySelectorAll('.type-section').forEach(sec => sec.style.display = 'none');
    const sec = document.getElementById('section_' + type);
    if (sec) sec.style.display = 'block';
}
window.onload = () => showSection(document.getElementById('utter_type').value);

function addButtonField() {
    const div = document.createElement('div');
    div.className = 'draggable'; div.draggable = true;
    div.ondragstart = drag; div.ondragover = allowDrop; div.ondrop = ev => drop(ev, div);
    div.innerHTML = `
        <input type="text" name="title[]" placeholder="Title" required>
        <select name="payload[]" required>
            <option value="">Select Intent...</option>
            <?php foreach ($intents as $intentName): ?>
            <option value="/<?= htmlspecialchars($intentName) ?>">/<?= htmlspecialchars($intentName) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn-small" onclick="this.parentElement.remove();">Remove</button>
    `;
    document.getElementById('buttons-container').appendChild(div);
}
function addCardButtonField() {
    const div = document.createElement('div');
    div.className = 'draggable'; div.draggable = true;
    div.ondragstart = drag; div.ondragover = allowDrop; div.ondrop = ev => drop(ev, div);
    div.innerHTML = `
        <input type="text" name="card_button_title[]" placeholder="Title" required>
        <select name="card_button_payload[]" required>
            <option value="">Select Intent...</option>
            <?php foreach ($intents as $intentName): ?>
            <option value="/<?= htmlspecialchars($intentName) ?>">/<?= htmlspecialchars($intentName) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn-small" onclick="this.parentElement.remove();">Remove</button>
    `;
    document.getElementById('card-buttons-container').appendChild(div);
}
function formatUtterName(input) {
    const prefix = "utter_";
    if (!input.value.startsWith(prefix)) {
        input.value = prefix;
    }
    let suffix = input.value.slice(prefix.length);
    suffix = suffix.replace(/[^a-zA-Z_ ]/g, '');
    suffix = suffix.replace(/\s+/g, '_');
    input.value = prefix + suffix;
}
</script>
</body>
</html>
