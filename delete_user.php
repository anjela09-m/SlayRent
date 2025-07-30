<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId = intval($_POST['user_id']);
  $userType = $_POST['user_type'];

  if (!in_array($userType, ['borrower', 'lender'])) {
    die("Invalid user type.");
  }

  $table = $userType === 'borrower' ? 'borrowers' : 'lenders';

  // Use prepared statement
  $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
  $stmt->bind_param("i", $userId);

  if ($stmt->execute()) {
    header("Location: admin_manage_users.php");
    exit();
  } else {
    echo "Error deleting user: " . $stmt->error;
  }
}
?>
