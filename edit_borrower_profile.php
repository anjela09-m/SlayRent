<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
  header("Location: login.php");
  exit();
}

$borrower_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM borrowers WHERE id = ?");
$stmt->bind_param("i", $borrower_id);
$stmt->execute();
$borrower = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      background-color: #fdf5fa;
      font-family: 'Segoe UI', sans-serif;
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
    }
    input {
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
  <form action="update_borrower_profile.php" method="POST">
    <input type="text" name="name" placeholder="Name" value="<?= htmlspecialchars($borrower['name']) ?>" required>
    <input type="text" name="college_id" placeholder="College ID" value="<?= htmlspecialchars($borrower['college_id']) ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($borrower['email']) ?>" required>
    <button type="submit">Update Profile</button>
  </form>
</div>

</body>
</html>
