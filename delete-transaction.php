<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Validate and retrieve transaction ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: transactions.php');
    exit;
}

$trId = (int) $_GET['id'];

// Prepare DELETE statement
$stmt = mysqli_prepare($conn, "DELETE FROM transactions WHERE id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $trId, $userId);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: transactions.php?deleted=1');
    exit;
} else {
    mysqli_stmt_close($stmt);
    die("Error deleting transaction: " . mysqli_error($conn));
}
