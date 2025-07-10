<?php
session_start();
include 'includes/config.php';

// ✅ Access check: only admin
if (!isset($_SESSION['email']) || $_SESSION['email'] !== 'annapadiyara123@gmail.com') {
    header("Location: login.php");
    exit();
}

// 🔍 Count total lenders
$lenderCount = $conn->query("SELECT COUNT(*) AS count FROM lenders")->fetch_assoc()['count'];

// 🔍 Count total borrowers
$borrowerCount = $conn->query("SELECT COUNT(*) AS count FROM borrowers")->fetch_assoc()['count'];

// 🔍 Count total costumes (if table exists)
$costumeResult = $conn->query("SHOW TABLES LIKE 'costumes'");
$costumeCount = ($costumeResult->num_rows > 0)
    ? $conn->query("SELECT COUNT(*) AS count FROM costumes")->fetch_assoc()['count']
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .admin-container {
      max-width: 900px;
      margin: 50px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .admin-container h2 {
      font-size: 28px;
      margin-bottom: 20px;
    }

    .stat-box {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .stat {
      flex: 1 1 30%;
      background: #f1f1f1;
      margin: 10px;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
    }

    .links a {
      display: inline-block;
      margin: 10px 15px 0 0;
      background-color: #333;
      color: white;
      padding: 10px 15px;
      border-radius: 6px;
      text-decoration: none;
    }

    .logout {
      margin-top: 30px;
    }
  </style>
</head>
<body>

<div class="admin-container">
  <h2>Welcome, Admin 👑</h2>

  <div class="stat-box">
    <div class="stat">
      <h3>Lenders</h3>
      <p><?= $lenderCount ?></p>
    </div>
    <div class="stat">
      <h3>Borrowers</h3>
      <p><?= $borrowerCount ?></p>
    </div>
    <div class="stat">
      <h3>Costumes</h3>
      <p><?= $costumeCount ?></p>
    </div>
  </div>

  <div class="links">
    <a href="admin_users.php">👥 Manage Users</a>
    <a href="admin_costumes.php">👗 Manage Costumes</a>
  </div>

  <div class="logout">
    <a href="logout.php" style="background-color: crimson;">Logout</a>
  </div>
</div>

</body>
</html>
