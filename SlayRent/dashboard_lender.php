<?php
session_start();

// Check if user is a logged-in lender
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    header("Location: login.php");
    exit();
}

// Fetch session values
$slayrent_id = $_SESSION['slayrent_id'] ?? '';
$shop_name   = $_SESSION['shop_name'] ?? 'Your Shop';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lender Dashboard | SlayRent</title>
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
  <h2>Welcome, <?= htmlspecialchars($shop_name) ?> 👑</h2>
  <p>Your SlayRent ID: <strong><?= htmlspecialchars($slayrent_id) ?></strong></p>

  <div class="section">
    <h3>📸 Upload a Costume</h3>
    <p>Add a new costume to your shop.</p>
    <a href="#" class="button">Upload Costume</a>
  </div>

  <div class="section">
    <h3>🎽 My Costumes</h3>
    <p>Manage your active and rented costumes.</p>
    <a href="#" class="button">View Costumes</a>
  </div>

  <div class="section">
    <h3>📊 Rental Stats</h3>
    <p>Track your rentals and earnings.</p>
    <a href="#" class="button">View Stats</a>
  </div>

  <div class="section">
    <a href="logout.php" class="button" style="background-color: crimson;">Logout</a>
  </div>
</div>

</body>
</html>
