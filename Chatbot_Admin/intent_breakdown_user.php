<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$current_user = strtoupper($username);

// Fetch intents for the current user's office_in_charge
$sql = "SELECT intent FROM intent_logs";
$result = $conn->query($sql);

$total = 0;
$intent_counts = [];

while ($row = $result->fetch_assoc()) {
    $intent = $row['intent'];

    // Get office_in_charge for the intent
    $stmt = $conn->prepare("SELECT office_in_charge FROM intents WHERE name = ?");
    $stmt->bind_param("s", $intent);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($data = $res->fetch_assoc()) {
        $office = $data['office_in_charge'];
        if ($office === $username) {
            if (!isset($intent_counts[$intent])) {
                $intent_counts[$intent] = 0;
            }
            $intent_counts[$intent]++;
            $total++;
        }
    }
}

$labels = [];
$values = [];
$colors = [];

foreach ($intent_counts as $intent => $count) {
    $labels[] = $intent;
    $values[] = round(($count / $total) * 100, 2);
    $colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); // Random color
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>User Intent Breakdown</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header img { height: 40px; }
        .header-text { font-size: 18px; font-weight: bold; color: white; }

        /* Top bar */
        .top-bar {
            margin: 20px;
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

        h2 {
            text-align: center;
            margin-bottom: 10px; /* ✅ Reduced spacing below heading */
        }

        .chart-container {
            width: 50%;
            margin: 0 auto;
            padding-top: 5px;   /* ✅ Reduced padding above chart */
        }


        canvas {
            max-width: 500px;
        }
  
    .table-container {
    max-width: 70%;
    margin: 20px auto;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(40,199,111,0.2);
    overflow: hidden;
}


/* Table base styles */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    table-layout: fixed;   /* force equal column widths */
}

/* Headings */
th {
    background: #2e562e;
    color: #fff;
    padding: 12px;
    text-align: center;
    font-weight: bold;
    word-wrap: break-word;
}

/* Table rows */
td {
    padding: 10px;
    text-align: center;
    border: 1px solid #28c76f33;
    color: #333;
    word-wrap: break-word;
    overflow-wrap: break-word; /* wrap long text */
}

/* Alternate row shading */
tbody tr:nth-child(even) {
    background-color: rgba(40,199,111,0.08); /* light green translucent */
}
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="tangquery_icon.png" alt="Logo">
    <div class="header-text"><?= htmlspecialchars($current_user) ?> OFFICE</div>
</div>

<!-- Back Button -->
<div class="top-bar">
    <a href="dashboard.php" class="btn">⬅ Back</a>
</div>

<h2><?= htmlspecialchars($username) ?>'s Intent Breakdown (in %)</h2>

<div class="chart-container" style="display: flex; justify-content: center; margin-top: 0px;">
    <canvas id="intentPie" width="500" height="500"></canvas>
</div>
<div class="main-container">
    <div class="table-container">
<table>
    <tr>
        <th>Intent</th>
        <th>Percentage</th>
        <th>Count</th>
    </tr>
    <?php foreach ($intent_counts as $intent => $count): ?>
        <tr>
            <td><?= htmlspecialchars($intent) ?></td>
            <td><?= round(($count / $total) * 100, 2) ?>%</td>
            <td><?= $count ?></td>
        </tr>
    <?php endforeach; ?>
</table>
    </div>
    </div>

<script>
    const ctx = document.getElementById('intentPie').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($values) ?>,
                backgroundColor: <?= json_encode($colors) ?>
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const countMap = <?= json_encode($intent_counts) ?>;
                            const label = context.label || '';
                            const percentage = context.parsed;
                            return `${label}: ${percentage}% (${countMap[label]} count)`;
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
</script>

</body>
</html>
