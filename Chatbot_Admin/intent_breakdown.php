<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$current_user = strtoupper($_SESSION['username']); // Uppercase

// Fetch all intent logs
$sql = "SELECT intent FROM intent_logs";
$result = $conn->query($sql);

$total = 0;
$office_counts = [];

while ($row = $result->fetch_assoc()) {
    $intent = $row['intent'];
    $total++;

    $stmt = $conn->prepare("SELECT office_in_charge FROM intents WHERE name = ?");
    $stmt->bind_param("s", $intent);
    $stmt->execute();
    $res = $stmt->get_result();

    $office = ($data = $res->fetch_assoc()) ? $data['office_in_charge'] : "Others";

    if (!isset($office_counts[$office])) {
        $office_counts[$office] = 0;
    }
    $office_counts[$office]++;
}

// Prepare data
$labels = [];
$values = [];
$counts = [];
$colors = [];

foreach ($office_counts as $office => $count) {
    $labels[] = $office;
    $counts[] = $count;
    $values[] = round(($count / $total) * 100, 2);
    $colors[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Intent Breakdown by Office</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Top Actions */
        .top-actions {
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
            background: #8B0000;
            transition: 0.3s;
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

        /* Chart */
        canvas {
            max-width: 500px;
        }
        .chart-container {
            display: flex;
            justify-content: center;
        }

              /* Table container */
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
        .color-box {
            width: 20px;
            height: 20px;
            display: inline-block;
            border-radius: 3px;
            margin-right: 8px;
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
<div class="top-actions">
    <a href="dashboard.php" class="btn">⬅ Back</a>
</div>

<h2>Intent Breakdown by Office (in %)</h2>

<div class="chart-container">
    <canvas id="intentPie" width="500" height="500"></canvas>
</div>
<div class="main-container">
  
    <div class="table-container">
<table>
    <thead>
        <tr>
            <th>Office</th>
            <th>Count</th>
            <th>Percentage</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($labels as $index => $office): ?>
            <tr>
                <td><span class="color-box" style="background-color: <?= $colors[$index] ?>"></span><?= htmlspecialchars($office) ?></td>
                <td><?= $counts[$index] ?></td>
                <td><?= $values[$index] ?>%</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
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
                backgroundColor: <?= json_encode($colors) ?>,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const percentage = context.parsed;
                            const counts = <?= json_encode($counts) ?>;
                            const count = counts[context.dataIndex];
                            return `${label}: ${percentage}% (${count} count${count !== 1 ? 's' : ''})`;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>
