<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
  header("Location: login.php");
  exit();
}

$lender_id = $_SESSION['user_id'];
$shop_name = $_POST['shop_name'];
$contact   = $_POST['contact'];
$email     = $_POST['email'];
$auth_id   = $_POST['auth_id'];

$stmt = $conn->prepare("UPDATE lenders SET shop_name = ?, contact = ?, email = ?, auth_id = ? WHERE id = ?");
$stmt->bind_param("sssssi", $shop_name,  $contact, $email, $auth_id, $lender_id);

if ($stmt->execute()) {
  echo "<script>alert('✅ Profile updated successfully!'); window.location.href = 'dashboard_lender.php';</script>";
} else {
  echo "<script>alert('❌ Update failed. Please try again.'); history.back();</script>";
}
?>
