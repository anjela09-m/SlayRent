<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register | SlayRent</title>
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #BDB93 0%, #BE5B50 50%, #BDB93 100%);
      font-family: "Times New Roman", serif;
      min-height: 100vh;
      padding: 20px 0;
    }

    .form-container {
      background: #BDB93;
      max-width: 450px;
      margin: 40px auto;
      padding: 40px 35px;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(100, 27, 46, 0.3);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #641B2E, #8A2D3B, #641B2E);
    }

    .form-container h2 {
      font-family: "Candara", sans-serif;
      font-size: 2.2rem;
      color: #641B2E;
      margin-bottom: 8px;
      font-weight: 600;
    }

    .form-container h3 {
      font-family: "Candara", sans-serif;
      font-size: 1.3rem;
      color: #8A2D3B;
      margin: 25px 0 20px 0;
      font-weight: 500;
      text-align: left;
      padding-left: 15px;
      border-left: 3px solid #BE5B50;
    }

    .form-container form {
      margin-top: 25px;
    }

    .form-container input[type="text"],
    .form-container input[type="email"],
    .form-container input[type="tel"],
    .form-container input[type="password"] {
      width: 100%;
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      border: 2px solid rgba(138, 45, 59, 0.3);
      font-family: "Times New Roman", serif;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
      color: #641B2E;
    }

    .form-container input:focus {
      outline: none;
      border-color: #BE5B50;
      background: white;
      box-shadow: 0 0 15px rgba(190, 91, 80, 0.2);
      transform: translateY(-2px);
    }

    .form-container input::placeholder {
      color: #8A2D3B;
      font-style: normal;
    }

    .show-password-container {
      text-align: left;
      margin: 15px 0 25px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .show-password-container input[type="checkbox"] {
      width: 18px;
      height: 18px;
      margin: 0;
      accent-color: #BE5B50;
    }

    .show-password-container label {
      font-family: "Times New Roman", serif;
      font-size: 0.9rem;
      color: #641B2E;
      cursor: pointer;
      user-select: none;
    }

    .form-container button {
      background: #BE5B50;
      color: white;
      padding: 15px 25px;
      border: none;
      width: 100%;
      font-family: "Candara", sans-serif;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 6px 20px rgba(100, 27, 46, 0.4);
    }

    .form-container button:hover {
      background: #8A2D3B;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(100, 27, 46, 0.5);
    }

    .form-container button:active {
      transform: translateY(-1px);
    }

    /* Availability message styling */
    .availability-msg {
      font-family: "Times New Roman", serif;
      font-size: 0.85rem;
      margin-top: -15px;
      margin-bottom: 15px;
      text-align: left;
      font-weight: 500;
    }

    /* Input validation styling */
    .form-container input:invalid {
      border-color: #BE5B50;
    }

    .form-container input:valid {
      border-color: #8A2D3B;
    }

    /* Responsive design */
    @media (max-width: 480px) {
      .form-container {
        margin: 20px auto;
        padding: 30px 25px;
        max-width: 90%;
      }

      .form-container h2 {
        font-size: 1.8rem;
      }

      .form-container h3 {
        font-size: 1.1rem;
      }

      .form-container input[type="text"],
      .form-container input[type="email"],
      .form-container input[type="tel"],
      .form-container input[type="password"] {
        padding: 12px 15px;
        font-size: 0.95rem;
      }
    }

    /* Loading state for button */
    .form-container button:disabled {
      background: #8A2D3B !important;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
      opacity: 0.6;
    }

    /* Success/Error states */
    .success {
      color: #8A2D3B;
      font-weight: 600;
    }

    .error {
      color: #BE5B50;
      font-weight: 600;
    }

    /* Form field grouping */
    .field-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .field-label {
      display: block;
      margin-bottom: 5px;
      color: #641B2E;
      font-family: "Candara", sans-serif;
      font-weight: 500;
      font-size: 0.9rem;
    }

    /* Enhanced focus states */
    .form-container input:focus + .field-label {
      color: #BE5B50;
    }

    /* Pattern hint styling */
    .pattern-hint {
      font-family: "Times New Roman", serif;
      font-size: 0.8rem;
      color: #8A2D3B;
      margin-top: -15px;
      margin-bottom: 15px;
      text-align: left;
      font-style: normal;
    }
  </style>

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

          if (!/^\d{12}$/.test(authId)) {
            alert("Authentication ID must be exactly 12 digits.");
            e.preventDefault();
            return;
          }
        }

        if (userType === 'borrower') {
          const collegeId = form.college_id.value.trim();
          const regex = /^\d{2}[a-z]{3}\d{3}$/i;

          if (!regex.test(collegeId)) {
            alert("College ID format should be like 23UBC118 (year + course + rollno)");
            e.preventDefault();
            return;
          }
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.textContent = 'Registering...';
        submitBtn.disabled = true;
      });

      // Password toggle
      const toggle = document.getElementById("showPassword");
      if (toggle) {
        toggle.addEventListener("change", function () {
          const passwordInput = document.querySelector('input[name="password"]');
          passwordInput.type = this.checked ? "text" : "password";
        });
      }

      // Email validation function
      function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      }

      // Real-time availability checking
      const emailInput = document.querySelector('input[name="email"]');
      const collegeIdInput = document.querySelector('input[name="college_id"]');
      const authIdInput = document.querySelector('input[name="auth_id"]');

      function checkAvailability(field, value, inputElement) {
        if (!value) return;

        // Add loading indicator
        let message = inputElement.nextElementSibling;
        if (!message || !message.classList.contains('availability-msg')) {
          message = document.createElement('small');
          message.className = 'availability-msg';
          message.style.display = 'block';
          inputElement.insertAdjacentElement('afterend', message);
        }

        message.textContent = 'Checking availability...';
        message.style.color = "#8A2D3B";

        fetch('check_availability.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `field=${field}&value=${encodeURIComponent(value)}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.taken) {
            message.textContent = `${field.replace('_', ' ')} already in use.`;
            message.className = 'availability-msg error';
            inputElement.style.borderColor = '#BE5B50';
          } else {
            message.textContent = `${field.replace('_', ' ')} is available ✓`;
            message.className = 'availability-msg success';
            inputElement.style.borderColor = '#8A2D3B';
          }
        })
        .catch(err => {
          console.error(err);
          message.textContent = 'Could not check availability';
          message.style.color = '#BE5B50';
        });
      }

      // Add debounced availability checking
      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }

      if (emailInput) {
        emailInput.addEventListener('input', debounce(() => {
          if (validateEmail(emailInput.value)) {
            checkAvailability('email', emailInput.value, emailInput);
          }
        }, 500));
      }

      if (collegeIdInput) {
        collegeIdInput.addEventListener('input', debounce(() => {
          if (collegeIdInput.value.length >= 7) {
            checkAvailability('college_id', collegeIdInput.value, collegeIdInput);
          }
        }, 500));
      }

      if (authIdInput) {
        authIdInput.addEventListener('input', debounce(() => {
          if (authIdInput.value.length === 12) {
            checkAvailability('auth_id', authIdInput.value, authIdInput);
          }
        }, 500));
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
    <input type="hidden" name="user_type" value="<?= $type ?>">

    <?php if ($type === 'lender'): ?>
      <h3>🏪 Lender Details</h3>
      <input type="text" name="name" placeholder="Full Name" required>
      
      <input type="text" name="shop_name" placeholder="Shop Name" required>
      
      <input type="tel" name="contact" placeholder="Contact Number" pattern="[0-9]{10}" maxlength="10" title="Enter 10-digit contact number" required>
      <div class="pattern-hint">Format: 10-digit mobile number</div>
      
      <input type="email" name="email" placeholder="Email Address" required>
      
      <input type="text" name="auth_id" placeholder="Authentication ID (Aadhaar)" pattern="\d{12}" maxlength="12" title="Enter a valid 12-digit Aadhaar number" required>
      <div class="pattern-hint">Format: 12-digit Aadhaar number</div>

    <?php else: ?>
      <h3>🎓 Borrower Details</h3>
      <input type="text" name="name" placeholder="Full Name" required>
      
      <input type="text" name="college_id" placeholder="College ID (e.g., 23UBC118)" pattern="\d{2}[a-z]{3}\d{3}" title="Format: 23ubc118" required>
      <div class="pattern-hint">Format: Year + Course + Roll Number (e.g., 23UBC118)</div>
      
      <input type="email" name="email" placeholder="Email Address" required>
    <?php endif; ?>

    <input type="password" name="password" placeholder="Create Password" minlength="6" required>
    <div class="pattern-hint">Minimum 6 characters required</div>

    <div class="show-password-container">
      <input type="checkbox" id="showPassword">
      <label for="showPassword">Show Password</label>
    </div>

    <button type="submit">Create Account</button>
  </form>
</div>

<?php include 'templates/footer.php'; ?>
</body>
</html>
