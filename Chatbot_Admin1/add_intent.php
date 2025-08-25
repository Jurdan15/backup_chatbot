<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$current_user = strtoupper($_SESSION['username']); // uppercase
?>

<!DOCTYPE html>
<html>
<head>
    <title>NLU Intent Editor</title>
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
            color: #228B22;
        }

        /* Back button row */
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
            border-radius: 8px;
            background: linear-gradient(to bottom, #006400, #32cd32); /* dark to light green */
            color: #fff;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #fff;
        }
        input[type=text] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px 0;
            border: none;
            border-radius: 5px;
            background: #f9f9f9;
            color: #333;
            font-family: monospace;
            font-size: 14px;
            outline: none;
        }
        input[type=text]:focus {
            box-shadow: 0 0 5px #00f2ff;
        }
        button {
            padding: 12px 25px;
            background: #ffee02ff; /* yellow button */
            color: #000000ff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: block;
            margin: 20px auto 0 auto;
        }
        button:hover {
            background: #ffda6a;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .hint {
            font-size: 12px;
            color: #eee;
            margin-top: -8px;
            margin-bottom: 10px;
        }
        .counter {
            text-align: right;
            font-size: 12px;
            color: #fff;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        form h2, form label, form input, form button{
            position: relative;
            right: 9px;
        }
    </style>
    <script>
    window.onload = function() {
        // Sanitize intent as you type
        const intentInput = document.querySelector("input[name='intent']");
        intentInput.addEventListener('input', function() {
            this.value = this.value.replace(/[\s\-]/g, '_'); 
            this.value = this.value.replace(/[^\w]/g, ''); 
        });

        const exampleInputs = document.querySelectorAll("input[name='examples[]']");
        const submitBtn = document.getElementById('saveBtn');
        const counterEl = document.getElementById('filledCounter');

        function updateCounterAndValidity() {
            let filled = 0;
            exampleInputs.forEach(inp => {
                if (inp.value.trim() !== '') filled++;
            });
            counterEl.textContent = filled + " / 10 filled (min 5)";
            submitBtn.disabled = filled < 5;
            submitBtn.style.opacity = filled < 5 ? 0.6 : 1;
            submitBtn.style.cursor  = filled < 5 ? 'not-allowed' : 'pointer';
        }

        exampleInputs.forEach(inp => {
            inp.addEventListener('input', updateCounterAndValidity);
        });
        updateCounterAndValidity();

        document.getElementById('intentForm').addEventListener('submit', function(e) {
            let filled = 0;
            exampleInputs.forEach(inp => {
                if (inp.value.trim() !== '') filled++;
            });
            if (filled < 5) {
                e.preventDefault();
                alert('Please provide at least 5 example sentences (you can add up to 10).');
            }
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

<!-- Back Button -->
<div class="main-actions">
    <a href="view_intents.php" class="btn btn-back">⬅ Back</a>
</div>

<!-- Form -->
<h2>Add New Intent</h2>
<form method="post" id="intentForm">
    <label>Intent Name (no "-"):</label>
    <input type="text" name="intent" required>

    <label>Enter Examples</label><br>
    <div class="hint">At least 5 required, up to 10 allowed.</div>
    <div class="counter" id="filledCounter">0 / 10 filled (min 5)</div>

    <?php for ($i = 0; $i < 10; $i++): ?>
        <input type="text" name="examples[]" placeholder="Example <?= $i + 1 ?>">
    <?php endfor; ?>

    <button type="submit" id="saveBtn" name="save">💾 Save to Database</button>
</form>

<?php
if (isset($_POST['save'])) {
    $intent = isset($_POST['intent']) ? trim($_POST['intent']) : '';
    $username = $_SESSION['username'];

    if (!preg_match('/^[A-Za-z0-9_]+$/', $intent)) {
        echo "<p class='message' style='color:red;'>Intent name can only contain letters, numbers, and underscores (_).</p>";
        exit;
    }

    $examples_raw = $_POST['examples'] ?? [];
    $examples = [];
    foreach ($examples_raw as $ex) {
        $trimmed = trim($ex);
        if ($trimmed !== '') {
            $examples[] = $trimmed;
        }
    }

    if (count($examples) < 5) {
        echo "<p class='message' style='color:red;'>Please provide at least 5 example sentences. You entered ".count($examples).".</p>";
        exit;
    }
    if (count($examples) > 10) {
        echo "<p class='message' style='color:red;'>You can provide up to 10 examples only.</p>";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO intents (name, office_in_charge) VALUES (?, ?)");
    $stmt->bind_param("ss", $intent, $username);
    $stmt->execute();
    $intent_id = $stmt->insert_id;

    $stmtEx = $conn->prepare("INSERT INTO examples (intent_id, example) VALUES (?, ?)");
    $stmtEx->bind_param("is", $intent_id, $exVal);
    foreach ($examples as $exVal) {
        $stmtEx->execute();
    }

    echo "<p class='message' style='color:#28c76f;'>Intent and ".count($examples)." example(s) saved successfully!</p>";
}
?>
</body>
</html>
