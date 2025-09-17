<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
}
$userId = $_SESSION['user_id'];
$errors = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $category  = trim($_POST['category']);
  $limit     = trim($_POST['limit']);
  $threshold = (int)$_POST['threshold'];
  if (!$category || !is_numeric($limit) || $limit <= 0 || $threshold < 1 || $threshold > 100) {
    $errors = "Please provide valid values.";
  } else {
    $stmt = mysqli_prepare($conn, "
      INSERT INTO budgets (user_id, category, limit_amount, threshold_percent)
      VALUES (?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE limit_amount = VALUES(limit_amount),
                              threshold_percent = VALUES(threshold_percent)
    ");
    mysqli_stmt_bind_param($stmt, 'isdi', $userId, $category, $limit, $threshold);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success = "Budget for {$category} saved.";
  }
}
?>

<?php include 'includes/header.php'; ?>

<section class="form-section">
  <h2>Set a Budget</h2>
  <?php if ($errors): ?>
    <div class="error-box"><?= htmlspecialchars($errors) ?></div>
  <?php elseif ($success): ?>
    <div class="success-box"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" class="form-box">
    <label>
      Category
      <input type="text" name="category" required placeholder="e.g., Food, Rent">
    </label>
    <label>
      Budget Limit (KSh)
      <input type="number" name="limit" step="0.01" required placeholder="1000.00">
    </label>
    <label>
      Notify at Threshold (%)
      <input type="number" name="threshold" min="1" max="100" value="80" required>
    </label>
    <button type="submit" class="btn primary">Save Budget</button>
  </form>
</section>

<?php include 'includes/footer.php'; ?>
