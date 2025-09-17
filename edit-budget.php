<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
$errors = [];
$success = "";

if (!isset($_GET['cat'])) {
    header('Location: manage-budgets.php');
    exit;
}

// Prepare
$category = $_GET['cat'];
$stmt = mysqli_prepare($conn, "SELECT limit_amount, threshold_percent FROM budgets WHERE user_id=? AND category=?");
mysqli_stmt_bind_param($stmt, 'is', $userId, $category);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $limit, $threshold);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: manage-budgets.php');
    exit;
}
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $limit = trim($_POST['limit']);
    $threshold = (int)$_POST['threshold'];
    if (!is_numeric($limit) || $limit <= 0 || $threshold < 1 || $threshold > 100) {
        $errors = "Provide a positive limit and threshold between 1‑100.";
    } else {
        $stmt = mysqli_prepare($conn, "
          UPDATE budgets SET limit_amount=?, threshold_percent=?
           WHERE user_id=? AND category=?
        ");
        mysqli_stmt_bind_param($stmt, 'disi', $limit, $threshold, $userId, $category);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = "Budget updated for '{$category}'.";
    }
}

include 'includes/header.php';
?>

<section class="form-section">
  <h2>Edit Budget - <?= htmlspecialchars($category) ?></h2>
  <?php if ($errors): ?>
    <div class="error-box"><?= htmlspecialchars($errors) ?></div>
  <?php elseif ($success): ?>
    <div class="success-box"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <form method="post" class="form-box">
    <label>Limit (KSh)
      <input type="number" name="limit" step="0.01" value="<?= htmlspecialchars($limit) ?>" required>
    </label>
    <label>Threshold (%) for notification
      <input type="number" name="threshold" min="1" max="100" value="<?= htmlspecialchars($threshold) ?>" required>
    </label>
    <button type="submit" class="btn primary">Save Changes</button>
  </form>
  <p class="form-tip"><a href="manage-budgets.php">← Back to Manage Budgets</a></p>
</section>

<?php include 'includes/footer.php'; ?>
