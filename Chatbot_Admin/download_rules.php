<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'generate_rules.php'; // generates rules.yml fresh

$file = "rules.yml";
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    echo "File does not exist.";
}
?>
