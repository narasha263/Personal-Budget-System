<?php
include 'config.php';
session_start();



$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors[] = "Please enter your password.";
    }

    if (empty($errors)) {
        // Look up user by email
        $stmt = mysqli_prepare($conn, "SELECT id, name, password_hash FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id, $name, $hash);
            mysqli_stmt_fetch($stmt);

            if (password_verify($password, $hash)) {
                // Successful login
                $_SESSION['user_id']   = $id;
                $_SESSION['user_name'] = $name;
                mysqli_stmt_close($stmt);
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="form-section">
  <h2>Login to Your Account</h2>

  <?php if ($errors): ?>
    <div class="error-box">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
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
  <form method="post" action="login.php" class="form-box">
    <label>
      Email
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required autofocus>
    </label>

    <label>
      Password
      <input type="password" name="password" required>
    </label>

    <button type="submit" class="btn primary">Login</button>
  </form>

  <p class="form-tip">
    Don’t have an account? <a href="register.php">Create one</a>.<br>
    <a href="forgot-password.php">Forgot password?</a>
  </p>
</section>

<?php include 'includes/footer.php'; ?>
