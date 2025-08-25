<?php
include 'db.php';
session_start();

// Get intent ID
$intent_id = $_GET['id'] ?? null;
if (!$intent_id) {
    die("No intent selected.");
}

// Fetch intent
$stmt = $conn->prepare("SELECT * FROM intents WHERE id = ?");
$stmt->bind_param("i", $intent_id);
$stmt->execute();
$intent = $stmt->get_result()->fetch_assoc();

// Fetch examples
$stmt = $conn->prepare("SELECT * FROM examples WHERE intent_id = ?");
$stmt->bind_param("i", $intent_id);
$stmt->execute();
$examples = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle update
if (isset($_POST['update'])) {
    $new_intent = trim($_POST['intent']);
    $new_examples = array_filter($_POST['examples'], fn($ex) => trim($ex) !== "");
    $count_examples = count($new_examples);

    if (!preg_match('/^[A-Za-z0-9_]+$/', $new_intent)) {
        echo "<p style='color:red;'>Intent name can only contain letters, numbers, and underscores (_).</p>";
    } elseif ($count_examples < 5) {
        echo "<p style='color:red;'>At least 5 examples are required.</p>";
    } else {
        $old_intent_name = $intent['name'];

        // Update intent name
        $stmt = $conn->prepare("UPDATE intents SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_intent, $intent_id);
        $stmt->execute();

        // Update rules table
        $stmt = $conn->prepare("UPDATE rules SET intent = ? WHERE intent = ?");
        $stmt->bind_param("ss", $new_intent, $old_intent_name);
        $stmt->execute();

        // Delete old examples
        $conn->query("DELETE FROM examples WHERE intent_id = $intent_id");

        // Insert updated examples
        $stmt = $conn->prepare("INSERT INTO examples (intent_id, example) VALUES (?, ?)");
        foreach ($new_examples as $example) {
            $stmt->bind_param("is", $intent_id, $example);
            $stmt->execute();
        }

        echo "<script>alert('Intent updated successfully'); window.location='view_intents.php';</script>";
        exit;
    }
}

$current_user = strtoupper($_SESSION['username']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Intent</title>
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

        /* Back button bar */
        .main-actions {
            margin: 20px;
            display: flex;
            justify-content: flex-start;
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
        .btn-back {
            background: #8B0000; /* red */
        }
        .btn:hover {
            opacity: 0.85;
        }

        /* Form container */
        form {
            width: 40%;
            margin: 20px auto;
            padding: 25px;
            border-radius: 10px;
            background: linear-gradient(to bottom, #006400, #32cd32); /* gradient green */
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            color: #fff;
        }
        input[type=text] {
            width: 100%;
            margin: 8px 0 15px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #fff;
            color: #333;
            font-family: monospace;
            font-size: 14px;
            outline: none;
        }
        input[type=text]:focus {
            box-shadow: 0 0 5px #228B22;
        }
        button {
            padding: 12px 25px;
            background: #28c76f;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background: #218838;
        }
        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #fff;
        }
        form h2, form label, form input, form button{
            position: relative;
            right: 8px;
        }
    </style>
    <script>
    window.onload = function() {
        const intentInput = document.querySelector("input[name='intent']");
        intentInput.addEventListener('input', function() {
            this.value = this.value.replace(/[\s\-]/g, '_').replace(/[^\w]/g, '');
        });
    };
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

<!-- Back button -->
<div class="main-actions">
    <a href="view_intents.php" class="btn btn-back">⬅️ Back</a>
</div>

<!-- Edit Form -->
<form method="post">
    <h2>Edit Intent</h2>

    <label>Intent Name:</label>
    <input type="text" name="intent" value="<?= htmlspecialchars($intent['name']) ?>" required>

    <label>Edit up to 10 Examples (at least 5 required):</label>
    <?php
    for ($i = 0; $i < 10; $i++) {
        $value = $examples[$i]['example'] ?? '';
        echo "<input type='text' name='examples[]' value=\"" . htmlspecialchars($value) . "\">";
    }
    ?>

    <button type="submit" name="update">Update</button>
</form>

</body>
</html>
