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
    <title>View Utters</title>
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

        /* Main actions */
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
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            transition: 0.3s;
            background: #8B0000;
        }
        .btn:hover {
            opacity: 0.85;
        }

        h3 {
            text-align: center;
            margin: 20px 0;
            color: #333;
        }
        #btnadd{
            position: relative;
            top:  50px;
            right:42px;
        }

        /* Utter Dropdown */
        .utter-box {
            width: 70%;       /* wider */
            margin: 20px auto;
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
        }
        .utter-title {
            background: #228B22; /* bamboo green */
            color: #fff;
            padding: 12px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .utter-title.active {
            background: linear-gradient(to bottom, #006400, #32cd32);
        }

        .utter-content {
            display: none;
            padding: 15px;
            background: #f9f9f9;
        }

        /* Buttons list */
        .buttons-list {
            list-style: none;
            padding-left: 0;
        }
        .buttons-list li {
            background: #fff;
            margin-bottom: 6px;
            padding: 6px 10px;
            border-left: 4px solid #228B22;
            border-radius: 4px;
            font-family: monospace;
            color: #333;
        }

        /* Actions inside utter */
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

        img {
            border-radius: 5px;
            margin-top: 8px;
            max-width: 300px;
            display: block;
        }
        small {
            color: #666;
            font-family: monospace;
        }
        #con_title{
            position: relative;
            right: 32%;
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
        <a href="dashboard.php" class="btn">⬅️ Back</a>
    </div>
    <div class="actions-right">
        <a href="add_utter.php" class="btn" id="btnadd">➕ Add Utter</a>
        <a href="generate_view_domain.php" class="btn">📝 View Code</a>
    </div>
</div>

<h3 id="con_title">UTTERS</h3>

<?php
$stmt = $conn->prepare("SELECT * FROM utters WHERE created_by = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($utter = $result->fetch_assoc()) {
        echo "<div class='utter-box'>";
        echo "<div class='utter-title' id='title-" . $utter['id'] . "' onclick='toggleDropdown(" . $utter['id'] . ")'>" 
                . htmlspecialchars($utter['utter_name']) . " (" . htmlspecialchars($utter['type']) . ")</div>";
        
        echo "<div class='utter-content' id='content-" . $utter['id'] . "'>";

        if ($utter['type'] == 'text') {
            echo "<p>" . nl2br(htmlspecialchars($utter['content'])) . "</p>";
        } elseif ($utter['type'] == 'image') {
            $data = json_decode($utter['content'], true);
            echo "<p>" . htmlspecialchars($data['text']) . "</p>";
            echo "<img src='" . htmlspecialchars($data['image']) . "' alt='Image'><br>";
            echo "<small>" . htmlspecialchars($data['image']) . "</small>";
        } elseif ($utter['type'] == 'button') {
            $data = json_decode($utter['content'], true);
            if ($data) {
                echo "<p><strong>Text:</strong> " . htmlspecialchars($data['text']) . "</p>";
                echo "<ul class='buttons-list'>";
                foreach ($data['buttons'] as $btn) {
                    echo "<li><strong>Title:</strong> " . htmlspecialchars($btn['title']) . 
                         " | <strong>Payload:</strong> " . htmlspecialchars($btn['payload']) . "</li>";
                }
                echo "</ul>";
            }
        } elseif ($utter['type'] == 'card') {
            $data = json_decode($utter['content'], true);
            if ($data) {
                echo "<p><strong>Title:</strong> " . htmlspecialchars($data['title']) . "</p>";
                echo "<p><strong>Subtitle:</strong> " . htmlspecialchars($data['subtitle']) . "</p>";
                echo "<img src='" . htmlspecialchars($data['image_url']) . "' alt='Card Image'><br>";
                echo "<small>" . htmlspecialchars($data['image_url']) . "</small>";
                echo "<ul class='buttons-list'>";
                foreach ($data['buttons'] as $btn) {
                    echo "<li><strong>Title:</strong> " . htmlspecialchars($btn['title']) . 
                         " | <strong>Payload:</strong> " . htmlspecialchars($btn['payload']) . "</li>";
                }
                echo "</ul>";
            }
        }

        echo "<div class='actions'>";
        echo "<a href='edit_utter.php?id=" . $utter['id'] . "' class='btn-action btn-edit'>✏️ Edit</a>";
        echo "<a href='delete_utter.php?id=" . $utter['id'] . "' class='btn-action btn-delete' onclick=\"return confirm('Delete this utter?')\">🗑️ Delete</a>";
        echo "</div>";

        echo "</div></div>";
    }
} else {
    echo "<p style='text-align:center; color:#666;'>No utters found for you.</p>";
}
?>

</body>
</html>
