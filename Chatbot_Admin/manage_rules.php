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
    <link rel="icon" type="image/x-icon" href="favicon.ico">
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

             
        .rules-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px auto;
    width: 70%; /* same as intent-box width for consistency */
}

.btn-add {
    background: #8B0000; /* red */
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
    text-decoration: none;
    transition: 0.3s;
}
.btn-add:hover {
    opacity: 0.85;
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

/* Hover effect */
tbody tr:hover {
    background: rgba(40,199,111,0.2);
}

/* Action buttons inside table */
.btn-small {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: bold;
    color: #fff;
    margin: 2px;
    display: inline-block;
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

.btn-edit {
            background: #f1c40f;
            color: black;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* subtle shadow */
    transition: background 0.3s, transform 0.2s;
            
        }
        .btn-delete {
            background: #8B0000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* subtle shadow */
    transition: background 0.3s, transform 0.2s;
        }

        .btn-edit:hover, .btn-delete:hover, .btn-add:hover{
             transform: translateY(-2px); /* subtle lift */
        }

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-container {
        max-width: 95%; /* allow more space on smaller screens */
    }
    th, td {
        padding: 8px;
        font-size: 13px;
    }
    .btn-small {
        font-size: 12px;
        padding: 5px 10px;
    }
}
@media (max-width: 480px) {
    th, td {
        padding: 6px;
        font-size: 12px;
    }
    .btn-small {
        font-size: 11px;
        padding: 4px 8px;
    }
}


    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="tangquery_icon.png" alt="Logo">
    <div class="header-text">
        <?= htmlspecialchars($current_user) ?> OFFICE
    </div>
</div>

<!-- Main Actions -->
<div class="main-actions">
    <div class="actions-left">
        <a href="dashboard.php" class="btn btn-back">⬅ Back</a>
    </div>
    <div class="actions-right">
        <a href="generate_view_rules.php" class="btn btn-generate">⚡ Generate rules.yml</a>
    </div>
</div>

<div class="main-container">
   <div class="rules-header">
    <h3>MANAGE RULES</h3>
    <a href="add_rule.php" class="btn btn-add">➕ Add New Rule</a>
</div>
        
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rule Name</th>
                    <th>Intent</th>
                    <th>Utter Actions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
