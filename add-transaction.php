<?php
include 'config.php';
session_start();

// Redirect guests
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$type = $_GET['type'] ?? 'expense';
$amount = $date = $category = $description = '';

// Validate type value
if (!in_array($type, ['income', 'expense'])) {
    $type = 'expense';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = trim($_POST['amount']);
    $date = $_POST['date'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);

    // Validation
    if (!in_array($type, ['income', 'expense'])) {
        $errors[] = "Invalid transaction type.";
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $errors[] = "Enter a valid amount.";
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = "Enter a valid date (YYYY-MM-DD).";
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO transactions 
            (user_id, `type`, amount, date, category, description, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        mysqli_stmt_bind_param($stmt, 'isdsss', $userId, $type, $amount, $date, $category, $description);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = "Failed to save transaction. Try again.";
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<style>
    /* Global Resets and Styles */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f9f9f9;
  color: #333;
}
.container {
  width: 90%;
  max-width: 960px;
  margin: auto;
  padding: 1em 0;
}

/* Header & Footer */
.site-header, .site-footer {
  background: #005f73;
  color: #fff;
}
.site-header .container, .site-footer .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.site-header a, .site-footer a {
  color: #fff;
  margin-left: 1em;
  text-decoration: none;
}
.site-header a:hover, .site-footer a:hover {
  text-decoration: underline;
}

/* Hero Section */
.hero {
  background: #0a9396;
  color: #fff;
  text-align: center;
  padding: 4em 0;
}
.hero h2 {
  font-size: 2.2rem;
  margin-bottom: 0.5em;
}
.hero p {
  font-size: 1.1rem;
  margin-bottom: 1.5em;
}

/* Buttons */
.btn {
  display: inline-block;
  padding: 0.75em 1.5em;
  margin: 0.5em;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
  text-decoration: none;
  text-align: center;
}
.btn.primary {
  background: #94d2bd; color: #005f73;
}
.btn.primary:hover {
  background: #ee9b00; color: #fff;
}
.btn.secondary {
  background: transparent;
  border: 2px solid #94d2bd; color: #94d2bd;
}
.btn.secondary:hover {
  background: #94d2bd; color: #005f73;
}
.btn.tertiary {
  background: transparent;
  border: 2px solid #94d2bd; color: #005f73;
}
.btn.tertiary:hover {
  background: #94d2bd; color: #fff;
}

/* Features Cards */
.features {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  margin: 2em 0;
}
.feature {
  background: #fff;
  border-radius: 8px;
  padding: 1.5em;
  margin: 1em;
  flex: 1 1 250px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.feature h3 {
  margin-bottom: 0.5em;
}

/* Form Section */
.form-section {
  max-width: 400px;
  margin: 2em auto;
  background: #fff;
  padding: 2em;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.form-box label {
  display: block;
  margin-bottom: 1em;
}
.form-box input, .form-box textarea {
  width: 100%;
  padding: 0.6em;
  margin-top: 0.3em;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.form-box textarea {
  height: 80px;
}
.error-box {
  background: #f8d7da;
  color: #842029;
  padding: 1em;
  margin-bottom: 1em;
  border-radius: 4px;
}
.form-tip {
  text-align: center;
  margin-top: 1em;
}
.form-tip a {
  color: #0a9396;
}

/* Dashboard Styles */
.dashboard-intro {
  text-align: center;
  margin: 2em 0;
}
.summary-cards {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-around;
  margin-bottom: 2em;
}
.card {
  background: #fff;
  border-radius: 8px;
  padding: 1.5em;
  margin: 1em;
  flex: 1 1 200px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  text-align: center;
}
.card.income    { border-top: 4px solid #0a9396; }
.card.expenses  { border-top: 4px solid #ee9b00; }
.card.balance   { border-top: 4px solid #005f73; }
.card .amount {
  font-size: 1.8rem;
  margin-top: 0.5em;
}

/* Transactions Table */
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
.positive {
  color: green;
}
.negative {
  color: red;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2em;
}

/* Page Intros */
.page-intro {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 2em 0;
}

/* Footer */
.site-footer {
  margin-top: 4em;
  padding: 2em 0;
}
.site-footer p {
  font-size: 0.9rem;
}

    .form-section textarea {
  width: 100%;
  height: 80px;
  padding: 0.6em;
  border: 1px solid #ccc;
  border-radius: 4px;
  margin-top: 0.3em;
}

    </style>
<section class="form-section">
  <h2><?= ucfirst($type) ?> Transaction</h2>

  <?php if ($errors): ?>
    <div class="error-box">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="add-transaction.php?type=<?= urlencode($type) ?>" class="form-box">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

    <label>
      Amount (KSh)
      <input type="number" name="amount" step="0.01" value="<?= htmlspecialchars($amount) ?>" required autofocus>
    </label>

    <label>
      Date
      <input type="date" name="date" value="<?= htmlspecialchars($date ?: date('Y-m-d')) ?>" required>
    </label>

    <label>
      Category
      <input type="text" name="category" value="<?= htmlspecialchars($category) ?>" placeholder="e.g., Rent, Salary">
    </label>

    <label>
      Description (optional)
      <textarea name="description"><?= htmlspecialchars($description) ?></textarea>
    </label>

    <button type="submit" class="btn primary">
      <?= $type === 'income' ? 'Add Income' : 'Add Expense' ?>
    </button>
  </form>

  <p class="form-tip">
    <a href="dashboard.php">← Back to Dashboard</a>
  </p>
</section>

<?php include 'includes/footer.php'; ?>
