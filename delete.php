<?php
session_start();
include 'db.php';

if (!isset($_SESSION['leader'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM lineups WHERE id=?");
    $stmt->execute([$_GET['id']]);
}

header("Location: dashboard.php");
exit;
?>
