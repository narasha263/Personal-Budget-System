<?php
include '../config.php';
session_start();

// Ensure only admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Validate "id" parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}
$viewId = (int)$_GET['id'];

// Fetch basic user info
$stmt = mysqli_prepare($conn, "SELECT name, email, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $viewId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $uName, $uEmail, $uCreated);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: users.php');
    exit;
}
mysqli_stmt_close($stmt);

// Fetch recent transactions
$stmt = mysqli_prepare(
    $conn,
    "SELECT type, amount, category, date, description
     FROM transactions
     WHERE user_id = ?
     ORDER BY date DESC
     LIMIT 20"
);
mysqli_stmt_bind_param($stmt, 'i', $viewId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $tType, $tAmount, $tCategory, $tDate, $tDesc);

$transactions = [];
while (mysqli_stmt_fetch($stmt)) {
    $transactions[] = [
        'type'     => $tType,
        'amount'   => $tAmount,
        'category' => $tCategory,
        'date'     => $tDate,
        'description' => $tDesc
    ];
}
mysqli_stmt_close($stmt);

include 'includes/admin-header.php';
?>

<h2>User Details</h2>
<table class="budgets-table">
  <tr><th>Name</th><td><?= htmlspecialchars($uName) ?></td></tr>
  <tr><th>Email</th><td><?= htmlspecialchars($uEmail) ?></td></tr>
  <tr><th>Joined On</th><td><?= htmlspecialchars($uCreated) ?></td></tr>
</table>

<h3>Recent Transactions (up to 20)</h3>
<?php if (count($transactions) > 0): ?>
<table class="budgets-table">
  <thead>
    <tr><th>Date</th><th>Type</th><th>Amount</th><th>Category</th><th>Description</th></tr>
  </thead>
  <tbody>
    <?php foreach ($transactions as $t): ?>
    <tr>
      <td><?= htmlspecialchars($t['date']) ?></td>
      <td><?= htmlspecialchars(ucfirst($t['type'])) ?></td>
      <td><?= htmlspecialchars(number_format($t['amount'],2)) ?></td>
      <td><?= htmlspecialchars($t['category']) ?></td>
      <td><?= htmlspecialchars($t['description'] ?: '-') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <p>No transactions available.</p>
<?php endif; ?>

<p><a href="users.php" class="btn tertiary">← Back to Users</a></p>

<?php include 'includes/admin-footer.php'; ?>
<style>
    .budgets-table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5em 0;
}

.budgets-table th,
.budgets-table td {
  padding: 0.75em 1em;
  border: 1px solid #ddd;
  text-align: left;
}

.budgets-table thead {
  background: #005f73;
  color: #fff;
}

.budgets-table tr:nth-child(even) {
  background: #f9f9f9;
}

.budgets-table tr:hover {
  background: #e9f9fd;
}

    </style>