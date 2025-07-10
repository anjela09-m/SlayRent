<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">

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
