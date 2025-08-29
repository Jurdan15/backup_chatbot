<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    http_response_code(403); exit;
}

$name = trim($_GET['name'] ?? '');
$name_like = '%' . $name . '%';

$stmt = $conn->prepare("
  SELECT u.username,
         t.last_time,
         c.message as last_message,
         CASE WHEN t.user IS NULL THEN 0 ELSE 1 END AS has_chat
  FROM users u
  LEFT JOIN (
    SELECT CASE WHEN sender='admin' THEN receiver ELSE sender END AS user, MAX(timestamp) AS last_time
    FROM chats
    WHERE sender='admin' OR receiver='admin'
    GROUP BY user
  ) t ON t.user = u.username
  LEFT JOIN chats c ON ((c.sender='admin' AND c.receiver=u.username) OR (c.sender=u.username AND c.receiver='admin'))
                    AND c.timestamp = t.last_time
  WHERE u.username != 'admin' AND u.username LIKE ?
  ORDER BY t.last_time DESC, u.username ASC
  LIMIT 50
");
$stmt->bind_param("s", $name_like);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
  $out[] = [
    'username' => $r['username'],
    'has_chat' => (bool)$r['has_chat'],
    'last_message' => $r['last_message'],
    'last_time' => $r['last_time']
  ];
}

header('Content-Type: application/json');
echo json_encode($out);
