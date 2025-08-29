<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['username'];
$user_msg = strtolower(trim($_GET['message'] ?? ''));
$responses = [];

// 0. Check if message is a payload (like "yes" coming from "/yes")
$is_payload = false;
if (strpos($user_msg, "/") === 0) {
    $user_msg = substr($user_msg, 1); // remove leading "/"
    $is_payload = true;
}

// 1. If it's a payload, skip matching examples and go straight to rules
$matched_intent = null;
if ($is_payload) {
    $stmtP = $conn->prepare("SELECT utter_name FROM rules WHERE intent = ? AND created_by = ?");
    $stmtP->bind_param("ss", $user_msg, $username);
    $stmtP->execute();
    $rule_res = $stmtP->get_result();

    if ($rule = $rule_res->fetch_assoc()) {
        $actions_raw = $rule['utter_name'];
        $actions = (substr($actions_raw, 0, 1) == '[') ? json_decode($actions_raw, true) : array_map('trim', explode(',', $actions_raw));

        foreach ($actions as $utter_name) {
            $stmtU = $conn->prepare("SELECT * FROM utters WHERE utter_name = ? AND created_by = ?");
            $stmtU->bind_param("ss", $utter_name, $username);
            $stmtU->execute();
            $utter_res = $stmtU->get_result();

            if ($utter = $utter_res->fetch_assoc()) {
                switch ($utter['type']) {
                    case 'text':
                        $responses[] = ['type' => 'text', 'content' => $utter['content']];
                        break;
                    case 'image':
                        $data = json_decode($utter['content'], true);
                        $responses[] = ['type' => 'image','text' => $data['text'],'image' => $data['image']];
                        break;
                    case 'button':
                        $data = json_decode($utter['content'], true);
                        $responses[] = ['type' => 'button','text' => $data['text'],'buttons' => $data['buttons']];
                        break;
                    case 'card':
                        $data = json_decode($utter['content'], true);
                        $responses[] = ['type' => 'card','title' => $data['title'],'subtitle' => $data['subtitle'],'image' => $data['image_url'],'buttons' => $data['buttons']];
                        break;
                }
            }
        }
    }
} else {
    // === UPDATED INTENT MATCHING LOGIC WITH THRESHOLDS ===
    $intent_scores = [];

    $stmt = $conn->prepare("SELECT id, name FROM intents WHERE office_in_charge = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($intent = $res->fetch_assoc()) {
        $intent_id = $intent['id'];
        $stmt2 = $conn->prepare("SELECT example FROM examples WHERE intent_id = ?");
        $stmt2->bind_param("i", $intent_id);
        $stmt2->execute();
        $examples = $stmt2->get_result();

        $best_score = 0;
        while ($ex = $examples->fetch_assoc()) {
            $example = strtolower($ex['example']);

            // Similarity calculations
            similar_text($user_msg, $example, $percent);
            $lev = levenshtein($user_msg, $example);
            $lev_score = max(0, 100 - ($lev * 10)); // Convert distance into similarity

            $score = max($percent, $lev_score); // take the stronger metric
            if ($score > $best_score) {
                $best_score = $score;
            }
        }
        $intent_scores[$intent['name']] = $best_score;
    }

    // Pick top 2 intents
    arsort($intent_scores);
    $top_intents = array_slice($intent_scores, 0, 2, true);

    if (!empty($top_intents)) {
        $intents = array_keys($top_intents);
        $scores = array_values($top_intents);

        $best_intent = $intents[0];
        $best_score = $scores[0] / 100; // normalize 0–1
        $second_score = isset($scores[1]) ? $scores[1] / 100 : 0;

        // Apply thresholds
        if ($best_score < 0.7) {
            $matched_intent = null; // too low confidence
        } elseif (($best_score - $second_score) < 0.2) {
            $matched_intent = null; // ambiguous
        } else {
            $matched_intent = $best_intent;
        }
    }

    // === FETCH RULES AND UTTERS IF INTENT MATCHED ===
    if (!empty($matched_intent)) {
        $stmt3 = $conn->prepare("SELECT utter_name FROM rules WHERE intent = ? AND created_by = ?");
        $stmt3->bind_param("ss", $matched_intent, $username);
        $stmt3->execute();
        $rule_res = $stmt3->get_result();

        if ($rule = $rule_res->fetch_assoc()) {
            $actions_raw = $rule['utter_name'];
            $actions = (substr($actions_raw, 0, 1) == '[') ? json_decode($actions_raw, true) : array_map('trim', explode(',', $actions_raw));

            foreach ($actions as $utter_name) {
                $stmt4 = $conn->prepare("SELECT * FROM utters WHERE utter_name = ? AND created_by = ?");
                $stmt4->bind_param("ss", $utter_name, $username);
                $stmt4->execute();
                $utter_res = $stmt4->get_result();

                if ($utter = $utter_res->fetch_assoc()) {
                    switch ($utter['type']) {
                        case 'text':
                            $responses[] = ['type' => 'text', 'content' => $utter['content']];
                            break;
                        case 'image':
                            $data = json_decode($utter['content'], true);
                            $responses[] = ['type' => 'image','text' => $data['text'],'image' => $data['image']];
                            break;
                        case 'button':
                            $data = json_decode($utter['content'], true);
                            $responses[] = ['type' => 'button','text' => $data['text'],'buttons' => $data['buttons']];
                            break;
                        case 'card':
                            $data = json_decode($utter['content'], true);
                            $responses[] = ['type' => 'card','title' => $data['title'],'subtitle' => $data['subtitle'],'image' => $data['image_url'],'buttons' => $data['buttons']];
                            break;
                    }
                }
            }
        }
    }
}

// 3. Fallback
if (empty($responses)) {
    $matched_intent = 'fallback'; // ensure we log this as intent
    $responses[] = [
        'type' => 'button',
        'text' => "Sorry, I don't understand. Did you mean:",
        'buttons' => [
            ['title' => 'Try again', 'payload' => '/restart'],
            ['title' => 'Talk to support', 'payload' => '/help']
        ]
    ];
}

// === LOGGING INTENT AND MESSAGE ===
if ($is_payload) {
    // If came from button → intent is payload itself
    $intent_used = $user_msg ?: 'fallback';
} else {
    // If came from typed message → matched intent or fallback
    $intent_used = $matched_intent ?? 'fallback';
}

$stmtLog = $conn->prepare("INSERT INTO intent_logs (intent, message) VALUES (?, ?)");
$stmtLog->bind_param("ss", $intent_used, $user_msg);
$stmtLog->execute();
$stmtLog->close();



echo json_encode($responses);
?>
