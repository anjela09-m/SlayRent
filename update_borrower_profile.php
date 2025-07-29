<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
  header("Location: login.php");
  exit();
}

$borrower_id = $_SESSION['user_id'];
$name = $_POST['name'];
$college_id = $_POST['college_id'];
$email = $_POST['email'];

$stmt = $conn->prepare("UPDATE borrowers SET name = ?, college_id = ?, email = ? WHERE id = ?");
$stmt->bind_param("sssi", $name, $college_id, $email, $borrower_id);

if ($stmt->execute()) {
  // Optional: Update session name
  $_SESSION['name'] = $name;
  echo "<script>alert('✅ Profile updated successfully!'); window.location.href = 'dashboard_borrower.php';</script>";
} else {
  echo "<script>alert('❌ Update failed. Please try again.'); history.back();</script>";
}
?>
