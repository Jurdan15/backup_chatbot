<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$current_user = strtoupper($username);
$message = "";

// Fetch intents for dropdown
$intent_stmt = $conn->prepare("SELECT name FROM intents WHERE office_in_charge = ?");
$intent_stmt->bind_param("s", $username);
$intent_stmt->execute();
$intents_result = $intent_stmt->get_result();

$intents = [];
while ($row = $intents_result->fetch_assoc()) {
    $intents[] = $row['name'];
}

// Save data
if (isset($_POST['save'])) {
    $utter_name = trim($_POST['utter_name']);
    $type = $_POST['type'];

    if ($type == 'text') {
        $content = trim($_POST['text']);
    } elseif ($type == 'image') {
        $content = json_encode([
            'text' => trim($_POST['image_text']),
            'image' => trim($_POST['image_url'])
        ]);
    } elseif ($type == 'button') {
        $button_text = trim($_POST['button_text']);
        $buttons = [];
        if (isset($_POST['button_titles']) && isset($_POST['button_payloads'])) {
            foreach ($_POST['button_titles'] as $i => $title) {
                $buttons[] = [
                    'title' => $title,
                    'payload' => $_POST['button_payloads'][$i]
                ];
            }
        }
        $content = json_encode([
            'text' => $button_text,
            'buttons' => $buttons
        ]);
    } elseif ($type == 'card') {
        $card_title = trim($_POST['card_title']);
        $card_subtitle = trim($_POST['card_subtitle']);
        $card_image_url = trim($_POST['card_image_url']);
        $buttons = [];
        if (isset($_POST['card_button_titles']) && isset($_POST['card_button_payloads'])) {
            foreach ($_POST['card_button_titles'] as $i => $title) {
                $buttons[] = [
                    'title' => $title,
                    'payload' => $_POST['card_button_payloads'][$i]
                ];
            }
        }
        $content = json_encode([
            'title' => $card_title,
            'subtitle' => $card_subtitle,
            'image_url' => $card_image_url,
            'buttons' => $buttons
        ]);
    } else {
        $content = "";
    }

    $stmt = $conn->prepare("INSERT INTO utters (utter_name, type, content, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $utter_name, $type, $content, $username);
    if ($stmt->execute()) {
        $message = "<p style='color:#28c76f;'>✅ Utter saved successfully!</p>";
    } else {
        $message = "<p style='color:#ff6f61;'>⚠️ Error saving utter: " . $stmt->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Utter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #333;
            margin: 0;
            padding: 0;
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
        .header img {
            height: 40px;
        }
        .header-text {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        /* Back button */
        .back-btn {
            display: inline-block;
            margin: 20px;
            padding: 10px 18px;
            background: #8B0000;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: opacity 0.3s;
        }
        .back-btn:hover {
            opacity: 0.85;
        }
        form h2, form label, form input{
            position: relative;
            right: 8px;
        }
        form select{
            position: relative;
            right: 8px;
            width: 103%;
        }
        #text_{
            position: relative;
            right: 8px;
        }
        #add_utter{
            color: black;
        }

        /* Utter form box */
        form {
            background: linear-gradient(to bottom, #006400, #32cd32);
            padding: 30px;
            width: 50%;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            color: #fff;
        }
        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }
        input[type=text], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            background: rgba(255,255,255,0.9);
            border: 1px solid #ccc;
            color: #333;
            border-radius: 5px;
            font-size: 14px;
        }
        textarea { resize: vertical; }
        .add-btn {
            background: #ffc107;
            color: #333;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 6px;
        }
        .add-btn:hover {
            background: #ffca2c;
        }
        button[type=submit] {
            background: #28c76f;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: block;
            margin: 20px auto 0;
            font-size: 15px;
            font-weight: bold;
        }
        button[type=submit]:hover {
            background: #218838;
        }
    </style>
    <script>
        function showFields(type) {
            document.getElementById('text_field').style.display = (type === 'text') ? 'block' : 'none';
            document.getElementById('image_field').style.display = (type === 'image') ? 'block' : 'none';
            document.getElementById('button_field').style.display = (type === 'button') ? 'block' : 'none';
            document.getElementById('card_field').style.display = (type === 'card') ? 'block' : 'none';
        }
        function addButton() {
            var container = document.getElementById('buttons_list');
            var div = document.createElement('div');
            div.innerHTML = 
                `<input type="text" name="button_titles[]" placeholder="Button Title" required>
                <select name="button_payloads[]" required>
                    <option value="">Select Intent...</option>
                    <?php foreach ($intents as $intentName): ?>
                        <option value="/<?= htmlspecialchars($intentName) ?>">/<?= htmlspecialchars($intentName) ?></option>
                    <?php endforeach; ?>
                </select>`;
            container.appendChild(div);
        }
        function addCardButton() {
            var container = document.getElementById('card_buttons_list');
            var div = document.createElement('div');
            div.innerHTML = 
                `<input type="text" name="card_button_titles[]" placeholder="Button Title" required>
                <select name="card_button_payloads[]" required>
                    <option value="">Select Intent...</option>
                    <?php foreach ($intents as $intentName): ?>
                        <option value="/<?= htmlspecialchars($intentName) ?>">/<?= htmlspecialchars($intentName) ?></option>
                    <?php endforeach; ?>
                </select>`;
            container.appendChild(div);
        }
        function formatUtterName(input) {
            const prefix = "utter_";
            if (!input.value.startsWith(prefix)) {
                input.value = prefix;
            }
            let suffix = input.value.substring(prefix.length);
            suffix = suffix.replace(/[^a-zA-Z_ ]/g, '');
            suffix = suffix.replace(/\s+/g, '_');
            input.value = prefix + suffix;
        }
    </script>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="logo.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
</div>

<a class="back-btn" href="view_utters.php">⬅ Back</a>

<h2 id="add_utter">Add Utter</h2>
<?= $message ?>

<form method="post">
    <label>Utter Name:</label>
    <input type="text" id="utter_name" name="utter_name" required placeholder="e.g. utter_greet" value="utter_" oninput="formatUtterName(this)" >

    <label>Type:</label>
    <select name="type" onchange="showFields(this.value)" required>
        <option value="text">Text</option>
        <option value="image">Image with Text</option>
        <option value="button">Button Template</option>
        <option value="card">Generic Card Template</option>
    </select>

    <div id="text_field" style="display:block;">
        <label>Text Response:</label>
        <textarea id="text_"name="text" placeholder="Hello, how can I help you?"></textarea>
    </div>

    <div id="image_field" style="display:none;">
        <label>Text Above Image:</label>
        <input type="text" name="image_text" placeholder="Here is our brochure:">
        <label>Image URL:</label>
        <input type="text" name="image_url" placeholder="https://...">
    </div>

    <div id="button_field" style="display:none;">
        <label>Main Question Text:</label>
        <input type="text" name="button_text" placeholder="How can I help you?">
        <div id="buttons_list"></div>
        <button type="button" class="add-btn" onclick="addButton()">➕ Add Button</button>
    </div>

    <div id="card_field" style="display:none;">
        <label>Card Title:</label>
        <input type="text" name="card_title" placeholder="Balance Inquiry">
        <label>Card Subtitle:</label>
        <input type="text" name="card_subtitle" placeholder="Easily check your balance.">
        <label>Image URL:</label>
        <input type="text" name="card_image_url" placeholder="https://...">
        <div id="card_buttons_list"></div>
        <button type="button" class="add-btn" onclick="addCardButton()">➕ Add Card Button</button>
    </div>

    <button type="submit" name="save">💾 Save Utter</button>
</form>

</body>
</html>
