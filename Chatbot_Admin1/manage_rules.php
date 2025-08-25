<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = strtoupper($_SESSION['username']); // Uppercase
$username = $_SESSION['username'];

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM rules WHERE id = ? AND created_by = ?");
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    header("Location: manage_rules.php");
    exit;
}

// Fetch rules
$rules = [];
$stmt = $conn->prepare("SELECT * FROM rules WHERE created_by = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $rules[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rules</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #333;
            padding: 0;
            margin: 0;
        }

        /* Header */
        .header {
            background: #8B0000;
            border-bottom: 2px solid #ddd;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
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

        /* Main Actions */
        .main-actions {
            margin: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .actions-left, .actions-right {
            display: flex;
            gap: 15px;
        }
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
        .btn-back, .btn-add, .btn-generate {
            background: #8B0000; /* red */
        }
        .btn:hover {
            opacity: 0.85;
        }

        /* Rules Table */
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(40,199,111,0.3);
            overflow: hidden;
        }
        th, td {
            border: 1px solid #28c76f55;
            padding: 10px;
            text-align: left;
            color: black;
        }
        th {
            background: #228B22;
            color: #fff;
        }
        tr:hover {
            background: #28c76f55;
        }

        /* Action buttons inside table */
        .btn-small {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            margin-right: 8px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-edit {
            background: #ffc107; /* yellow */
            color: #000;
        }
        .btn-delete {
            background: #dc3545; /* red */
        }
        .btn-small:hover {
            opacity: 0.85;
        }

        h2 {
            text-align: center;
            margin-top: 10px;
            color: #228B22;
        }
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

<!-- Main Actions -->
<div class="main-actions">
    <div class="actions-left">
        <a href="dashboard.php" class="btn btn-back">⬅️ Back</a>
    </div>
    <div class="actions-right">
        <a href="add_rule.php" class="btn btn-add">➕ Add New Rule</a>
        <a href="generate_view_rules.php" class="btn btn-generate">⚡ Generate rules.yml</a>
    </div>
</div>

<h2>MANAGE RULES</h2>

<table>
    <tr>
        <th>Rule Name</th>
        <th>Intent</th>
        <th>Utter Actions</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($rules as $r): 
        $utter_list = json_decode($r['utter_name'], true);
        if (!is_array($utter_list)) $utter_list = [$r['utter_name']];
    ?>
    <tr>
        <td><?= htmlspecialchars($r['rule_name']) ?></td>
        <td><?= htmlspecialchars($r['intent']) ?></td>
        <td><?= htmlspecialchars(implode(", ", $utter_list)) ?></td>
        <td>
            <a class="btn-small btn-edit" href="add_rule.php?edit=<?= $r['id'] ?>">✏️ Edit</a>
            <a class="btn-small btn-delete" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this rule?')">🗑️ Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
