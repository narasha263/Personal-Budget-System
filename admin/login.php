<?php
session_start();

// Admin credentials (hardcoded)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', password_hash('admin', PASSWORD_DEFAULT));

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username !== ADMIN_USER || !password_verify($password, ADMIN_PASS_HASH)) {
        $errors[] = 'Incorrect username or password.';
    } else {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: dashboard.php');
        exit;
    }
}
?>

<style>
        .form-section {
  max-width: 400px;
  margin: 2em auto;
  padding: 2em;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
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
.form-box button {
  width: 100%;
  margin-top: 1em;
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

        </style>
<section class="form-section" style="max-width:350px; margin:auto;">
  <h2>Admin Login</h2>
  <?php if ($errors): ?>
    <div class="error-box">
      <?php foreach ($errors as $err): ?>
        <p><?= htmlspecialchars($err) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" action="login.php" class="form-box">
    <label>Username
      <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit" class="btn primary">Login</button>
  </form>
</section>

<?php include 'includes/admin-footer.php'; ?>
