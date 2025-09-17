<?php
include '../config.php';
session_start();

// Only allow if logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch system stats
$totalUsers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$sql = "
  SELECT
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END),
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END)
  FROM transactions
";
$row = mysqli_fetch_row(mysqli_query($conn, $sql));
$totalIncome = $row[0];
$totalExpenses = $row[1];

include '../admin/includes/admin-header.php';
?>
<style>
   /* Admin Dashboard Styles */
body{
    background:rgb(211, 196, 121);
}
/* Card Container + Cards */
.admin-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin: 2rem 0;
}
.admin-cards .card {
  background: #fff;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  flex: 1 1 200px;
  min-width: 200px;
  text-align: center;
}
.admin-cards .card p {
  font-size: 2rem;
  margin: 0.5rem 0;
  color: #005f73;
}

/* Button Style */
.btn.tertiary {
  display: inline-block;
  margin: 0.5rem 0.25rem;
  padding: 0.6rem 1.2rem;
  border: 2px solid #94d2bd;
  border-radius: 0.4rem;
  background: transparent;
  color: #005f73;
  text-decoration: none;
  transition: background 0.3s, color 0.3s;
}
.btn.tertiary:hover {
  background: #94d2bd;
  color: #fff;
}

/* Page Header */
.page-intro {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  margin: 2rem 0;
}
.page-intro h2 {
  font-size: 1.8rem;
}

/* Responsive Behavior */
@media (max-width: 768px) {
  .admin-cards {
    flex-direction: column;
  }
  .page-intro {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* Navbar Adjustments */
.site-header nav a {
  margin: 0 0.75rem;
  color: #fff;
}
.site-header nav a:hover {
  opacity: 0.85;
}


    </style>
<h2>Admin Dashboard</h2>
<div class="admin-cards">
  <div class="card">Users: <?= $totalUsers ?></div>
  <div class="card">Total Income: KSh <?= number_format($totalIncome,2) ?></div>
  <div class="card">Total Expense: KSh <?= number_format($totalExpenses,2) ?></div>
</div>

<a href="users.php" class="btn tertiary">Manage Users</a>
<a href="settings.php" class="btn tertiary">Settings</a>

<?php include '../admin/includes/admin-footer.php'; ?>
