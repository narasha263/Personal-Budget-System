<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total transactions
$totalStmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM transactions WHERE user_id = ?");
mysqli_stmt_bind_param($totalStmt, 'i', $userId);
mysqli_stmt_execute($totalStmt);
mysqli_stmt_bind_result($totalStmt, $totalCount);
mysqli_stmt_fetch($totalStmt);
mysqli_stmt_close($totalStmt);

$totalPages = ceil($totalCount / $limit);

// Fetch transactions for current page
$stmt = mysqli_prepare($conn, "
  SELECT id, date, type, category, amount, description 
  FROM transactions
  WHERE user_id = ?
  ORDER BY date DESC, id DESC
  LIMIT ? OFFSET ?
");
mysqli_stmt_bind_param($stmt, 'iii', $userId, $limit, $offset);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $date, $type, $category, $amount, $description);
?>

<?php include 'includes/header.php'; ?>

<section class="page-intro">
  <h2>Your Transactions</h2>
  <a href="add-transaction.php?type=expense" class="btn secondary">+ New Expense</a>
  <a href="add-transaction.php?type=income" class="btn secondary">+ New Income</a>
  <!-- Back to dashboard link -->
  
    <a href="dashboard.php" class="btn tertiary">← Back to Dashboard</a>
  </div>
</section>

<table class="transactions-table">
  <thead>
    <tr>
      <th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Amount (KSh)</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while (mysqli_stmt_fetch($stmt)): ?>
      <tr>
        <td><?= htmlspecialchars($date) ?></td>
        <td><?= htmlspecialchars($type) ?></td>
        <td><?= htmlspecialchars($category) ?></td>
        <td><?= htmlspecialchars($description) ?></td>
        <td class="<?= $type === 'expense' ? 'negative' : 'positive' ?>">
          <?= number_format($amount, 2) ?>
        </td>
        <td>
          <a href="edit-transaction.php?id=<?= $id ?>">Edit</a> |
          <a href="delete-transaction.php?id=<?= $id ?>" onclick="return confirm('Delete this?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php mysqli_stmt_close($stmt); ?>
<style>
    .transactions-table {
  width: 100%;
  border-collapse: collapse;
  margin: 2em 0;
}
.transactions-table th, .transactions-table td {
  border: 1px solid #ddd;
  padding: 0.75em;
  text-align: left;
}
.transactions-table tr:nth-child(even) {
  background: #f9f9f9;
}
.positive { color: green; }
.negative { color: red; }
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2em;
}
.btn.tertiary {
  background: transparent;
  border: 2px solid #94d2bd;
  border-radius: 4px;
  padding: 0.5em 1em;
  text-decoration: none;
  color: #005f73;
}
.btn.tertiary:hover {
  background: #94d2bd;
  color: #005f73;
}

    </style>
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?>" class="btn tertiary">&larr; Previous</a>
  <?php endif; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?>" class="btn tertiary">Next &rarr;</a>
  <?php endif; ?>
  
  <span>Page <?= $page ?> of <?= $totalPages ?></span>
</div>

<?php include 'includes/footer.php'; ?>
