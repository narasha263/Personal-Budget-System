<?php
include '../config.php';
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $defaultThreshold = intval($_POST['default_threshold']);
    $currencySymbol = $_POST['currency_symbol'];
    $contactEmail    = $_POST['contact_email'];

    // Save settings
    $fields = ['default_threshold' => $defaultThreshold, 'currency_symbol' => $currencySymbol, 'contact_email' => $contactEmail];
    foreach ($fields as $k => $v) {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO settings (`key`,`value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
        mysqli_stmt_bind_param($stmt, 'ss', $k, $v);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Update thresholds in budgets table globally
    $stmt = mysqli_prepare($conn,
        "UPDATE budgets SET threshold_percent = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $defaultThreshold);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $notice = 'Settings saved and all budgets thresholds updated.';
}

// Fetch existing settings
$settings = [];
$res = mysqli_query($conn, "SELECT `key`,`value` FROM settings");
while ($row = mysqli_fetch_assoc($res)) {
    $settings[$row['key']] = $row['value'];
}

include 'includes/admin-header.php';
?>

<h2>Site Settings</h2>
<?php if ($notice): ?>
  <div class="success-box"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<form method="post" class="form-box" style="max-width:500px;">
  <label>
    Default Budget Threshold (%)
    <input type="number" name="default_threshold" value="<?= htmlspecialchars($settings['default_threshold'] ?? 80) ?>" min="1" max="100" required>
  </label>
  <label>
    Default Currency Symbol
    <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'KSh') ?>" required>
  </label>
  <label>
    Contact Email
    <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
  </label>
  <button type="submit" class="btn primary">Save Settings</button>
</form>

<p><a href="dashboard.php" class="btn tertiary">← Back to Dashboard</a></p>

<?php include 'includes/admin-footer.php'; ?>

<style>
    .form-box label {
  display: block;
  margin-bottom: 1em;
}
.form-box input {
  width: 100%;
  padding: 0.6em;
  margin-top: 0.3em;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.success-box {
  background: #d1e7dd;
  color: #0f5132;
  padding: 1em;
  border: 1px solid #badbcc;
  border-radius: 4px;
  margin-bottom: 1em;
}

    </style>
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