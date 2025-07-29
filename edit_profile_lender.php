<?php
session_start();
include 'includes/config.php';

// Check if lender is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
  header("Location: login.php");
  exit();
}

$lender_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM lenders WHERE id = ?");
$stmt->bind_param("i", $lender_id);
$stmt->execute();
$lender = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff4fa;
      padding: 40px;
    }
    .edit-container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      color: #e190ba;
      margin-bottom: 25px;
    }
    input, textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 15px;
    }
    button {
      width: 100%;
      background: #e190ba;
      color: white;
      padding: 12px;
      border: none;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background: #c46d9e;
    }
  </style>
</head>
<body>

<div class="edit-container">
  <h2>✏️ Edit Profile</h2>
  <form action="update_lender_profile.php" method="POST">
    <input type="text" name="shop_name" placeholder="Shop Name" value="<?= htmlspecialchars($lender['shop_name']) ?>" required>
    <input type="text" name="contact" placeholder="Contact" value="<?= htmlspecialchars($lender['contact']) ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($lender['email']) ?>" required>
    <input type="text" name="auth_id" placeholder="Authentication ID" value="<?= htmlspecialchars($lender['auth_id']) ?>" required>
    <button type="submit">Update Profile</button>
  </form>
</div>

</body>
</html>
