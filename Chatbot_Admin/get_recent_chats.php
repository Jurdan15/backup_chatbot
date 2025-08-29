<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    http_response_code(403); exit;
}

// subquery: last timestamp per user (where user is counterpart of admin)
$sql = "
SELECT t.user, c.message AS last_message, c.timestamp AS last_time
FROM (
  SELECT CASE WHEN sender='admin' THEN receiver ELSE sender END AS user, MAX(timestamp) AS last_time
  FROM chats
  WHERE sender='admin' OR receiver='admin'
  GROUP BY user
) t
JOIN chats c
  ON ((c.sender='admin' AND c.receiver=t.user) OR (c.sender=t.user AND c.receiver='admin'))
  AND c.timestamp = t.last_time
ORDER BY t.last_time DESC
";

$res = $conn->query($sql);
$out = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $out[] = [
      'username' => $r['user'],
      'last_message' => $r['last_message'],
      'last_time' => $r['last_time']
    ];
  }
}
header('Content-Type: application/json');
echo json_encode($out);
