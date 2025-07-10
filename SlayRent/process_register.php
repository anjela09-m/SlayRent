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
  $stmt1->store_result(); // ✅ Fix: avoid get_result to prevent "commands out of sync"
  $emailExistsInBorrowers = $stmt1->num_rows > 0;
  $stmt1->close();

  // ✅ Check email in lenders table
  $stmt2 = $conn->prepare("SELECT id FROM lenders WHERE email = ?");
  $stmt2->bind_param("s", $email);
  $stmt2->execute();
  $stmt2->store_result(); // ✅ Same fix
  $emailExistsInLenders = $stmt2->num_rows > 0;
  $stmt2->close();

  if ($emailExistsInBorrowers || $emailExistsInLenders) {
    die("Email already registered.");
  }

  if ($type === 'borrower') {
    $name = $_POST['name'];
    $college_id = $_POST['college_id'];

    $stmt = $conn->prepare("INSERT INTO borrowers (name, college_id, email, password, slayrent_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $college_id, $email, $password, $slayrent_id);
    $stmt->execute();
    $stmt->close();

    echo "<h3>Registration Successful 🎉</h3>Your SlayRent ID is <b>$slayrent_id</b><br><a href='login.php'>Login now</a>";

  } elseif ($type === 'lender') {
    $name = $_POST['name'];
    $shop_name = $_POST['shop_name'];
    $shop_id = $_POST['shop_id'];
    $contact = $_POST['contact'];
    $auth_id = $_POST['auth_id'];

    if (!preg_match('/^\d{16}$/', $auth_id)) {
      die("Invalid Aadhar number. Must be 16 digits.");
    }

    $stmt = $conn->prepare("INSERT INTO lenders (name, shop_name, shop_id, contact, email, password, auth_id, slayrent_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $shop_name, $shop_id, $contact, $email, $password, $auth_id, $slayrent_id);
    $stmt->execute();
    $stmt->close();

    echo "<h3>Registration Successful 🎉</h3>Your SlayRent ID is <b>$slayrent_id</b><br><a href='login.php'>Login now</a>";
  }
}
?>
