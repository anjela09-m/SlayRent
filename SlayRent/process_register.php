<?php
include 'includes/config.php';

function generateSlayrentID($length = 5) {
  return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = $_POST['user_type'];
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $slayrent_id = generateSlayrentID();

  // ✅ Check email in borrowers table
  $stmt1 = $conn->prepare("SELECT id FROM borrowers WHERE email = ?");
  $stmt1->bind_param("s", $email);
  $stmt1->execute();
  $stmt1->store_result();
  $emailExistsInBorrowers = $stmt1->num_rows > 0;
  $stmt1->close();

  // ✅ Check email in lenders table
  $stmt2 = $conn->prepare("SELECT id FROM lenders WHERE email = ?");
  $stmt2->bind_param("s", $email);
  $stmt2->execute();
  $stmt2->store_result();
  $emailExistsInLenders = $stmt2->num_rows > 0;
  $stmt2->close();

  if ($emailExistsInBorrowers || $emailExistsInLenders) {
    die("Email already registered.");
  }

  $successHTML = "";

  if ($type === 'borrower') {
    $name = $_POST['name'];
    $college_id = $_POST['college_id'];

    $stmt = $conn->prepare("INSERT INTO borrowers (name, college_id, email, password, slayrent_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $college_id, $email, $password, $slayrent_id);
    $stmt->execute();
    $stmt->close();

    $successHTML = "<p>Your SlayRent ID is: <strong>$slayrent_id</strong></p>";

  } elseif ($type === 'lender') {
    $name = $_POST['name'];
    $shop_name = $_POST['shop_name'];
    $shop_id = $_POST['shop_id'];
    $contact = $_POST['contact'];
    $auth_id = $_POST['auth_id'];

    if (!preg_match('/^\d{12}$/', $auth_id)) {
      die("Invalid Aadhar number. Must be 12 digits.");
    }

    $stmt = $conn->prepare("INSERT INTO lenders (name, shop_name, shop_id, contact, email, password, auth_id, slayrent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $shop_name, $shop_id, $contact, $email, $password, $auth_id, $slayrent_id);
    $stmt->execute();
    $stmt->close();

    $successHTML = "<p>Your SlayRent ID is: <strong>$slayrent_id</strong></p>";
  }

  // 🎉 Final Output with styles and confetti
  echo "
  <!DOCTYPE html>
  <html lang='en'>
  <head>
    <meta charset='UTF-8'>
    <title>Registration Successful</title>
    <link rel='stylesheet' href='assets/css/styles.css'>
    <style>
      body {
        margin: 0;
        padding: 0;
        background:rgb(245, 206, 225);
        font-family: 'Poppins', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
      }
      .success-box {
        background: #fff;
        padding: 40px 50px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        text-align: center;
        animation: fadeSlideIn 1s ease;
      }
      .success-box h3 {
        color:rgb(243, 98, 190);
        font-size: 26px;
        margin-bottom: 15px;
      }
      .success-box p {
        font-size: 16px;
        margin: 8px 0;
      }
      .success-box a {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        color: #fff;
        background:rgb(245, 174, 210);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
      }
      .success-box a:hover {
        background: rgb(245, 174, 210);
      }
      @keyframes fadeSlideIn {
        from {
          opacity: 0;
          transform: translateY(40px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
    </style>
  </head>
  <body>
    <div class='success-box'>
      <h3>🎉 Registration Successful!</h3>
      $successHTML
      <p>Now you can Login.</p>
      <a href='login.php'>Login Now</a>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js'></script>
    <script>
      confetti({
        particleCount: 250,
        spread: 100,
        origin: { y: 0.6 }
      });
    </script>
  </body>
  </html>
  ";
}
?>
