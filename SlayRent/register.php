<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register | SlayRent</title>
  <link rel="stylesheet" href="assets/css/auth.css">

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    form.addEventListener('submit', function (e) {
      const userType = document.querySelector('input[name="user_type"]').value;
      const email = form.email.value.trim();
      const password = form.password.value.trim();

      if (!userType || (userType !== 'lender' && userType !== 'borrower')) {
        alert("Invalid user type.");
        e.preventDefault();
        return;
      }

      if (!validateEmail(email)) {
        alert("Please enter a valid email address.");
        e.preventDefault();
        return;
      }

      if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        e.preventDefault();
        return;
      }

      if (userType === 'lender') {
        const contact = form.contact.value.trim();
        const authId = form.auth_id.value.trim();

        if (!/^\d{10}$/.test(contact)) {
          alert("Contact number must be exactly 10 digits.");
          e.preventDefault();
          return;
        }

        if (!/^\d{16}$/.test(authId)) {
          alert("Authentication ID must be exactly 16 digits.");
          e.preventDefault();
          return;
        }
      }

      if (userType === 'borrower') {
        const collegeId = form.college_id.value.trim();
        if (!/^\d{2}[a-z]{3}\d{3}$/.test(collegeId)) {
          alert("College ID format should be like 23ubc118 (year+course+rollno)");
          e.preventDefault();
          return;
        }
      }
    });

    function validateEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Password toggle
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

<?php
$type = $_GET['type'] ?? 'borrower';
include 'templates/header.php';
?>

<div class="form-container">
  <h2><?= ucfirst($type); ?> Registration</h2>

  <form action="process_register.php" method="POST">
    <!-- This field identifies user type -->
    <input type="hidden" name="user_type" value="<?= $type ?>">

    <?php if ($type === 'lender'): ?>
      <h3>Lender Details</h3>
      <input type="text" name="name" placeholder="Full Name" required><br>
      <input type="text" name="shop_name" placeholder="Shop Name" required><br>
      <input type="text" name="shop_id" placeholder="Shop ID" required><br>
      <input type="tel" name="contact" placeholder="Contact Number" pattern="[0-9]{10}" maxlength="10" title="Enter 10-digit contact number" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="text" name="auth_id" placeholder="Authentication ID (Aadhaar)" pattern="\d{16}" maxlength="16" title="Enter a valid 16-digit Aadhaar number" required><br>

    <?php else: ?>
      <h3>Borrower Details</h3>
      <input type="text" name="name" placeholder="Full Name" required><br>
      <input type="text" name="college_id" placeholder="College ID (e.g., 23ubc118)" pattern="\d{2}[a-z]{3}\d{3}" title="Format: 23ubc118" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
    <?php endif; ?>

    <input type="password" name="password" placeholder="Create Password" minlength="6" required><br>

    <div style="text-align:left; margin-top: 5px;">
      <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
    </div>

    <button type="submit">Register</button>
  </form>
</div>

<?php include 'templates/footer.php'; ?>
</body>
</html>
