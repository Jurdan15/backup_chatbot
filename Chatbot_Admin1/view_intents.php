<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = strtoupper($_SESSION['username']); // Uppercase
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Intents & Examples</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff; /* White background */
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

        /* Action buttons bar */
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
        .btn-add{
            position: absolute;
            top:20%;
            right:30%;
        }
        .btn:hover {
            opacity: 0.85;
        }

        /* Intent Dropdown */
        .intent-box {
            width: 40%;               /* 40% width */
            margin: 20px auto;        /* centered */
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
        }
        .intent-title {
            background: #228B22; /* bamboo green */
            color: #fff;
            padding: 12px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
           
        }
        .intent-title.active {
            background: linear-gradient(to bottom, #006400, #32cd32); /* dark to light green */
        }

        .intent-content {
            display: none;
            padding: 15px;
            background: #f9f9f9;
        }

        /* Example list */
        .example-list {
            list-style: none;
            padding-left: 0;
        }
        .example-list li {
            background: #fff;
            margin-bottom: 6px;
            padding: 8px 12px;
            border-left: 4px solid #228B22;
            border-radius: 4px;
            font-family: monospace;
            color: #333;
        }

        /* Action links inside intent */
        .actions {
            margin-top: 12px;
        }
        .btn-action {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            margin-right: 10px;
            text-decoration: none;
        }
        .btn-edit {
            background: #ffc107; /* yellow */
        }
        .btn-delete {
            background: #dc3545; /* red */
        }
    </style>
    <script>
        function toggleDropdown(id) {
            var content = document.getElementById("content-" + id);
            var title = document.getElementById("title-" + id);
            if (content.style.display === "block") {
                content.style.display = "none";
                title.classList.remove("active");
            } else {
                content.style.display = "block";
                title.classList.add("active");
            }
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

<!-- Main Actions -->
<div class="main-actions">
    <div class="actions-left">
        <a href="dashboard.php" class="btn btn-back">⬅️ Back</a>
    </div>
    <div class="actions-right">
        <a href="add_intent.php" class="btn btn-add">➕ Add Intent</a>
        <a href="generate_view_nlu.php" class="btn btn-generate">📝 View Code</a>
    </div>
</div>

<h3 style="margin-left:20px; position:relative; left:29%;">INTENTS</h3>

<?php
$stmt = $conn->prepare("SELECT * FROM intents WHERE office_in_charge = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($intent = $result->fetch_assoc()) {
        echo "<div class='intent-box'>";
        echo "<div class='intent-title' id='title-" . $intent['id'] . "' onclick='toggleDropdown(" . $intent['id'] . ")'>" . htmlspecialchars($intent['name']) . "</div>";
        
        echo "<div class='intent-content' id='content-" . $intent['id'] . "'>";

        // Fetch examples
        $stmt_ex = $conn->prepare("SELECT example FROM examples WHERE intent_id = ?");
        $stmt_ex->bind_param("i", $intent['id']);
        $stmt_ex->execute();
        $examples = $stmt_ex->get_result();

        echo "<ul class='example-list'>";
        while ($row = $examples->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['example']) . "</li>";
        }
        echo "</ul>";

        echo "<div class='actions'>";
        echo "<a href='edit_intent.php?id=" . $intent['id'] . "' class='btn-action btn-edit'>✏️ Edit</a>";
        echo "<a href='delete_intent.php?id=" . $intent['id'] . "' class='btn-action btn-delete' onclick=\"return confirm('Delete this intent and all examples?')\">🗑️ Delete</a>";
        echo "</div>";

        echo "</div></div>";
    }
} else {
    echo "<p style='margin-left:20px; color:#666;'>No intents found for you.</p>";
}
?>

</body>
</html>
