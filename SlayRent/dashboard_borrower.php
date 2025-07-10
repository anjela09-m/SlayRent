<?php
session_start();

// Check if user is a logged-in borrower
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    header("Location: login.php");
    exit();
}

// Fetch session info
$slayrent_id = $_SESSION['slayrent_id'] ?? '';
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Borrower'; // ✅ Display actual name if available
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrower Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .dashboard-container {
      max-width: 800px;
      margin: 50px auto;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      background: #fff;
    }

    .dashboard-container h2 {
      font-size: 28px;
      margin-bottom: 10px;
    }

    .dashboard-container .section {
      margin-top: 20px;
    }

    .dashboard-container a.button {
      display: inline-block;
      padding: 10px 20px;
      margin-top: 10px;
      background-color: #333;
      color: #fff;
      border-radius: 6px;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <h2>Welcome to SlayRent, <?= htmlspecialchars($name) ?> 👗</h2>
  <p>Your SlayRent ID: <strong><?= htmlspecialchars($slayrent_id) ?></strong></p>

  <div class="section">
    <h3>🎭 Browse Costumes</h3>
    <p>Explore Onam, Christmas, Cultural Fest, and Halloween outfits.</p>
    <a href="#" class="button">View Costumes</a>
  </div>

  <div class="section">
    <h3>📦 My Rentals</h3>
    <p>Check your current and past rentals.</p>
    <a href="#" class="button">View Rentals</a>
  </div>

  <div class="section">
    <h3>👤 My Profile</h3>
    <p>Update your personal info and college ID.</p>
    <a href="#" class="button">Edit Profile</a>
  </div>

  <div class="section">
    <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
  </div>
</div>

</body>
</html>
