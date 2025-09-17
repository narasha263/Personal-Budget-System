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
if (isset($_GET['delete'])) {
    $deleteCategory = $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM budgets WHERE user_id = ? AND category = ?");
    mysqli_stmt_bind_param($stmt, 'is', $userId, $deleteCategory);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success = "Budget for '$deleteCategory' deleted.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $limit = trim($_POST['limit']);
    $threshold = (int)$_POST['threshold'];
    if (!$category || !is_numeric($limit) || $limit <= 0 || $threshold < 1 || $threshold > 100) {
        $errors = "Enter valid values for all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO budgets(user_id, category, limit_amount, threshold_percent)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE limit_amount = VALUES(limit_amount),
                                    threshold_percent = VALUES(threshold_percent)
        ");
        mysqli_stmt_bind_param($stmt, 'isdi', $userId, $category, $limit, $threshold);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = "Budget saved for '{$category}'.";
    }
}

// Fetch existing budgets
$budgets = [];
$stmt = mysqli_prepare($conn, "SELECT category, limit_amount, threshold_percent FROM budgets WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $bcat, $blim, $bthr);
while (mysqli_stmt_fetch($stmt)) {
    $budgets[] = ['category'=>$bcat, 'limit'=>$blim, 'threshold'=>$bthr];
}
mysqli_stmt_close($stmt);
// Fetch existing settings
$settings = [];
$res = mysqli_query($conn, "SELECT `key`,`value` FROM settings");
while ($row = mysqli_fetch_assoc($res)) {
    $settings[$row['key']] = $row['value'];
}

include 'includes/header.php';
?>

<section class="form-section">
  <h2>Manage Budgets</h2>
  <?php if ($errors): ?>
    <div class="error-box"><?= htmlspecialchars($errors) ?></div>
  <?php elseif ($success): ?>
    <div class="success-box"><?= htmlspecialchars($success) ?></div>
    
  <?php endif; ?>

  <form method="post" class="form-box">
    <label>Category
      <input type="text" name="category" required placeholder="e.g., Food">
    </label>
    <label>Limit (KSh)
      <input type="number" name="limit" step="0.01" required>
    </label>
    <label>Threshold (%) (notify at)
  <label>
    Default Budget Threshold (%)
    <input type="number" name="threshold" value="<?= htmlspecialchars($settings['default_threshold'] ?? 80) ?>" min="1" max="100" required>
  </label>    </label>
    <button type="submit" class="btn primary">Save Budget</button>
  </form>
  <!-- Back to dashboard link -->
  <div class="navigation-links">
    <a href="dashboard.php" class="btn tertiary">← Back to Dashboard</a>
  </div>
</section>

<?php if ($budgets): ?>
<section>
  <h3>Your Budgets</h3>
  <table class="budgets-table">
    <thead><tr><th>Category</th><th>Limit</th><th>Threshold (%)</th></tr></thead>
    <tbody>
      <?php foreach ($budgets as $b): ?>
  <tr>
    <td><?= htmlspecialchars($b['category']) ?></td>
    <td>KSh <?= number_format($b['limit'],2) ?></td>
    <td><?= intval($b['threshold']) ?>%</td>
    <td><a href="edit-budget.php?cat=<?= urlencode($b['category']) ?>">Edit</a></td>
    <td>
  <a href="manage-budgets.php?delete=<?= urlencode($b['category']) ?>" onclick="return confirm('Are you sure you want to delete this budget?');">Delete</a>
</td>

  </tr>
<?php endforeach; ?>

    </tbody>
  </table>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<style>
    .budgets-table a {
  color: #007BFF;
  text-decoration: none;
  margin-right: 8px;
}

.budgets-table a:hover {
  text-decoration: underline;
}
.budgets-table td a {
  color: #005f73; text-decoration: none;
}
.budgets-table td a:hover {
  text-decoration: underline;
}

.success-box {
  background: #d1e7dd;
  color: #0f5132;
  padding: 1em;
  margin-bottom: 1em;
  border-radius: 4px;
  border: 1px solid #badbcc;
}
.budgets-table td a { color: #005f73; text-decoration: none; }
.budgets-table td a:hover { text-decoration: underline; }
.alert-box {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
  padding: 1em;
  margin: 1.5em 0;
  border-radius: 4px;
}
.budgets-table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5em 0;
}
.budgets-table th, .budgets-table td {
  padding: 0.75em;
  border: 1px solid #ddd;
  text-align: left;
}
.budgets-table tr:nth-child(even) {
  background: #f9f9f9;
}

    </style>