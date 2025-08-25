<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: logout.php");
    exit;
}
?>