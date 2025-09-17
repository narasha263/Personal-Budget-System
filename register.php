<?php
include 'config.php';
session_start();

$errors = [];
$name = $email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Validation
    if (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (count($errors) === 0) {
        // Check for duplicate email
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "An account with that email already exists.";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
            mysqli_stmt_bind_param($ins, 'sss', $name, $email, $hash);

            if (mysqli_stmt_execute($ins)) {
                header('Location: login.php?registered=1');
                exit;

            } else {
                $errors[] = "Database error. Please try again.";
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="form-section">
  <h2>Create Your Account</h2>

  <?php if ($errors): ?>
    <div class="error-box">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <html>
  <head>
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
        </head>
  <form method="post" action="register.php" class="form-box">
    <label>
      Name
      <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
    </label>

    <label>
      Email
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
    </label>

    <label>
      Password
      <input type="password" name="password" required>
    </label>

    <label>
      Confirm Password
      <input type="password" name="confirm" required>
    </label>

    <button type="submit" class="btn primary">Sign Up</button>
  </form>
  <p class="form-tip">Already have an account? <a href="login.php">Log in here</a>.</p>
</section>

<?php include 'includes/footer.php'; ?>
