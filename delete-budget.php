<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

if (!isset($_GET['cat'])) {
    header('Location: manage-budgets.php');
    exit;
}

$category = $_GET['cat'];
$stmt = mysqli_prepare($conn, "DELETE FROM budgets WHERE user_id = ? AND category = ?");
mysqli_stmt_bind_param($stmt, 'is', $userId, $category);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: manage-budgets.php?deleted=' . urlencode($category));
exit;
