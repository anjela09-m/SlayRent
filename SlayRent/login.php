<?php
session_start();
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .show-password {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      font-size: 14px;
      margin-top: 8px;
    }
    .error-msg {
      color: red;
      font-size: 14px;
      margin-bottom: 10px;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggle = document.getElementById("showPassword");
      if (toggle) {
        toggle.addEventListener("change", function () {
          const passwordInput = document.querySelector('input[name="password"]');
          passwordInput.type = this.checked ? "text" : "password";
        });
      }
    });
  </script>
</head>
<body>

<div class="form-container">
  <h2>Welcome Back 👋</h2>
  <p class="subtitle">Log in to continue renting or lending your look</p>

  <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="process_login.php" method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <div class="show-password">
      <label for="showPassword">Show Password</label>
      <input type="checkbox" id="showPassword">
    </div>

    <button type="submit">Login</button>
  </form>

  <p class="switch-link">New here? <a href="register_type.php">Create an account</a></p>
</div>

</body>
</html>
