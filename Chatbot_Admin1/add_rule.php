<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Handle save
if (isset($_POST['save'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $rule_name = trim($_POST['rule_name']);
    $intent = $_POST['intent'];
    $utter_names = isset($_POST['utter_name']) ? $_POST['utter_name'] : [];
    $utter_json = json_encode($utter_names);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE rules SET rule_name = ?, intent = ?, utter_name = ? WHERE id = ? AND created_by = ?");
        $stmt->bind_param("sssis", $rule_name, $intent, $utter_json, $id, $username);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO rules (rule_name, intent, utter_name, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $rule_name, $intent, $utter_json, $username);
        $stmt->execute();
    }
    header("Location: manage_rules.php");
    exit;
}

// If editing
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_rule = null;
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM rules WHERE id = ? AND created_by = ?");
    $stmt->bind_param("is", $edit_id, $username);
    $stmt->execute();
    $edit_rule = $stmt->get_result()->fetch_assoc();
}

// Fetch rules to know which intents are used
$rules = [];
$used_intents = [];
$stmt = $conn->prepare("SELECT intent FROM rules WHERE created_by = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $used_intents[] = $row['intent'];
}

// Fetch intents
$intents = [];
$i_stmt = $conn->prepare("SELECT name FROM intents WHERE office_in_charge = ?");
$i_stmt->bind_param("s", $username);
$i_stmt->execute();
$i_result = $i_stmt->get_result();
while ($i_row = $i_result->fetch_assoc()) {
    $intents[] = $i_row['name'];
}

// Fetch utters
$utters = [];
$u_stmt = $conn->prepare("SELECT utter_name FROM utters WHERE created_by = ?");
$u_stmt->bind_param("s", $username);
$u_stmt->execute();
$u_result = $u_stmt->get_result();
while ($u_row = $u_result->fetch_assoc()) {
    $utters[] = $u_row['utter_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $edit_rule ? "Edit Rule" : "Add New Rule" ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #8B0000;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header img { height: 40px; }
        .header-text { font-size: 18px; font-weight: bold; color: white; }
        .top-bar { margin: 20px; }
        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            transition: 0.3s;
        }
        #textt{
            width: 97%;
        }
        .btn-back { background: #8B0000; }
        .btn:hover { opacity: 0.85; }
        .btn-small { padding: 6px 12px; font-size: 13px; border-radius: 4px; font-weight: bold; cursor: pointer; color: #fff; }
        .btn-delete { background: #dc3545; }
        .btn-add { background: #228B22; }
        .rule-container {
            background: linear-gradient(to bottom, #006400, #32cd32);
            border: 1px solid #228B22;
            border-radius: 8px;
            padding: 20px;
            width: 40%;
            margin: 30px auto;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h2 { color: black; margin-bottom: 15px; }
        input, select {
            background: #fff;
            border: 1px solid #ccc;
            color: #333;
            padding: 8px;
            margin: 6px 0;
            border-radius: 4px;
            width: 100%;
        }
    </style>
    <script>
    function addNewAction() {
        let container = document.getElementById("actions");
        let div = document.createElement("div");
        div.style.display = "flex";
        div.style.alignItems = "center";
        div.style.gap = "5px";
        div.style.marginTop = "5px";
        div.innerHTML = `<select name="utter_name[]" required>
            <?php foreach ($utters as $utter): ?>
            <option value="<?= $utter ?>"><?= $utter ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn-small btn-delete" onclick="this.parentElement.remove()">✖</button>`;
        container.appendChild(div);
    }
    </script>
</head>
<body>

<div class="header">
    <img src="logo.png" alt="Logo">
    <div class="header-text"><?= strtoupper($username) ?> OFFICE</div>
</div>

<div class="top-bar">
    <a class="btn btn-back" href="manage_rules.php">⬅️ Back</a>
</div>

<div class="rule-container">
    <h2><?= $edit_rule ? "Edit Rule" : "Add New Rule" ?></h2>
    <form method="post">
        <input id="textt" type="hidden" name="id" value="<?= $edit_rule['id'] ?? 0 ?>">
        <input id="textt" type="text" name="rule_name" placeholder="Rule Name" required value="<?= $edit_rule['rule_name'] ?? '' ?>">

        <select name="intent" required>
            <option value="">Select Intent</option>
            <?php foreach ($intents as $intent): ?>
                <?php if (!in_array($intent, $used_intents) || ($edit_rule && $edit_rule['intent'] == $intent)): ?>
                    <option value="<?= htmlspecialchars($intent) ?>" <?= ($edit_rule && $edit_rule['intent'] == $intent) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($intent) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <div id="actions">
            <?php
            $utter_list = $edit_rule ? json_decode($edit_rule['utter_name'], true) : [""];
            if (!is_array($utter_list)) $utter_list = [$edit_rule['utter_name'] ?? ""];
            foreach ($utter_list as $u): ?>
                <div style="display: flex; align-items: center; gap: 5px; margin-top: 5px;">
                    <select name="utter_name[]" required>
                        <?php foreach ($utters as $utter): ?>
                            <option value="<?= $utter ?>" <?= ($u == $utter) ? 'selected' : '' ?>><?= $utter ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-small btn-delete" onclick="this.parentElement.remove()">✖</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn-small btn-add" onclick="addNewAction()">➕ Add another action</button>
        <br><br>
        <button type="submit" name="save" class="btn btn-back">💾 Save Rule</button>
    </form>
</div>

</body>
</html>
