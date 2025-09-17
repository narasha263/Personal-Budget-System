<?php
include 'config.php';
session_start();

// Redirect guest users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// 1. Get monthly income & expenses
$monthStart = date('Y-m-01');
$monthEnd   = date('Y-m-t');

$sql = "
  SELECT
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END),
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END)
  FROM transactions
  WHERE user_id = ? AND date BETWEEN ? AND ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'iss', $userId, $monthStart, $monthEnd);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $totalIncome, $totalExpenses);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$totalIncome   = $totalIncome ?: 0;
$totalExpenses = $totalExpenses ?: 0;
$balance       = $totalIncome - $totalExpenses;

// 2. Fetch budgets for this user
$alerts  = [];
$budgets = [];

$stmt = mysqli_prepare($conn,
  "SELECT category, limit_amount, threshold_percent FROM budgets WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cat, $lim, $thr);

while (mysqli_stmt_fetch($stmt)) {
    $budgets[$cat] = ['limit' => $lim, 'threshold' => $thr];
}
mysqli_stmt_close($stmt);

// 3. Calculate spending per budget and generate alerts
foreach ($budgets as $cat => $info) {
    $stmt = mysqli_prepare($conn,
      "SELECT SUM(amount) FROM transactions
       WHERE user_id = ? AND type='expense' AND category = ? AND date BETWEEN ? AND ?"
    );
    mysqli_stmt_bind_param($stmt, 'isss', $userId, $cat, $monthStart, $monthEnd);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $spent);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $spent = $spent ?: 0;
    $usedPercent = $info['limit'] > 0
                   ? intval(($spent / $info['limit']) * 100)
                   : 0;

    if ($usedPercent >= $info['threshold']) {
        $alerts[] = "⚠️ You've used {$usedPercent}% of your '{$cat}' budget.";
    }

    $budgets[$cat]['spent'] = $spent;
    $budgets[$cat]['used']  = $usedPercent;
}

include 'includes/header.php';
?>
<style>
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
 .dashboard-intro {
  margin: 2em 0;
  text-align: center;
}
.summary-cards {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  margin: 2em 0;
}
.card {
  background: #fff;
  border-radius: 8px;
  padding: 1.5em;
  margin: 0.5em;
  flex: 1 1 250px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  text-align: center;
}
.card.income { border-top: 4px solid #0a9396; }
.card.expenses { border-top: 4px solid #ee9b00; }
.card.balance { border-top: 4px solid #005f73; }
.card .amount {
  font-size: 1.8rem;
  margin-top: 0.5em;
}
.quick-actions {
  text-align: center;
  margin: 2em 0;
}
.quick-actions .btn {
  margin: 0.5em;
}
.btn.tertiary {
  background: transparent;
  border: 2px solid #94d2bd;
  color: #005f73;
}
.btn.tertiary:hover {
  background: #94d2bd;
  color: #005f73;
}
.success-box {
  background: #d1e7dd;
  color: #0f5132;
  padding: 1em;
  margin-bottom: 1em;
  border-radius: 4px;
  border: 1px solid #badbcc;
}
  </style>
<section class="dashboard-intro">
  <h2>Welcome back, <?= htmlspecialchars($userName) ?>!</h2>
  <p>Here’s your financial snapshot for <?= date('F Y') ?>.</p>
</section>

<section class="summary-cards">
  <div class="card income">
    <h3>Total Income</h3>
    <p class="amount">KSh <?= number_format($totalIncome, 2) ?></p>
  </div>
  <div class="card expenses">
    <h3>Total Expenses</h3>
    <p class="amount">KSh <?= number_format($totalExpenses, 2) ?></p>
  </div>
  <div class="card balance">
    <h3>Current Balance</h3>
    <p class="amount">KSh <?= number_format($balance, 2) ?></p>
  </div>
</section>

<?php if (!empty($alerts)): ?>
  <div class="alert-box">
    <?php foreach ($alerts as $msg): ?>
      <p><?= htmlspecialchars($msg) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<section class="quick-actions">
  <a href="add-transaction.php?type=income" class="btn primary">Add Income</a>
  <a href="add-transaction.php?type=expense" class="btn secondary">Add Expense</a>
  <a href="transactions.php" class="btn tertiary">View Transactions</a>
  <a href="manage-budgets.php" class="btn tertiary">Manage Budgets</a>
</section>

<?php if (!empty($budgets)): ?>
<section>
  <h3>Budget Summary</h3>
  <table class="budgets-table">
    <thead>
      <tr>
        <th>Category</th>
        <th>Limit (KSh)</th>
        <th>Spent</th>
        <th>% Used</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($budgets as $cat => $info): ?>
        <tr>
          <td><?= htmlspecialchars($cat) ?></td>
          <td>KSh <?= number_format($info['limit'], 2) ?></td>
          <td>KSh <?= number_format($info['spent'], 2) ?></td>
          <td><?= $info['used'] ?>%</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
