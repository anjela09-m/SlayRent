<?php
session_start();
include 'includes/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // ✅ Admin Login
  $adminEmail = "annapadiyara123@gmail.com";
  $adminPasswordHash = '$2y$10$OKrNT929mfdg.HwkQGdWuubZ8dswBi87wfiEY0BKFR4T4Z6ceO1TS'; // hash of 'slayyy123'

  if ($email === $adminEmail && password_verify($password, $adminPasswordHash)) {
    $_SESSION['admin'] = true;
     $_SESSION['email'] = $email; 
     header("Location: admin_dashboard.php");
    exit();
  }

  // ✅ Lender Login
  $stmt = $conn->prepare("SELECT * FROM lenders WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 1) {
    $user = $res->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_type'] = 'lender';
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['slayrent_id'] = $user['slayrent_id'];
      $_SESSION['shop_name'] = $user['shop_name'];

      header("Location: dashboard_lender.php");
      exit();
    }
  }

// ✅ Borrower Login
$stmt = $conn->prepare("SELECT * FROM borrowers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
  $user = $res->fetch_assoc();
  if (password_verify($password, $user['password'])) {
    $_SESSION['user_type'] = 'borrower';
    $_SESSION['user_id'] = $user['id'];   
    $_SESSION['borrower_id'] = $user['id'];   
    $_SESSION['slayrent_id'] = $user['slayrent_id'];
    $_SESSION['name'] = $user['name']; 

    header("Location: dashboard_borrower.php");
    exit();
  }
}


  // ❌ Invalid Login
  echo "❌ Invalid email or password.";
}
?>
