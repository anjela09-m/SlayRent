<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    header("Location: login.php");
    exit();
}

$slayrent_id = $_SESSION['slayrent_id'] ?? '';
$user_id     = $_SESSION['user_id'];
$shop_name   = $_SESSION['shop_name'] ?? 'Your Shop';

// Get join date
$query = $conn->prepare("SELECT created_at, name FROM lenders WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();
$created_at = $data['created_at'];
$lender_name = $data['name'];
$joined_days = floor((time() - strtotime($created_at)) / (60 * 60 * 24));

// Fetch costumes
$costumes = [];
$stmt = $conn->prepare("SELECT * FROM costumes WHERE lender_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $costumes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lender Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fdf5fa;
    }
    .dashboard {
      display: flex;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -260px;
      width: 250px;
      height: 100%;
      background-color: #e190ba;
      color: white;
      padding: 25px;
      transition: left 0.3s ease-in-out;
      z-index: 1000;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar h3 {
      margin-top: 0;
      font-size: 24px;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar ul li {
      margin: 18px 0;
    }
    .sidebar ul li a {
      color: white;
      text-decoration: none;
      font-size: 16px;
    }
    .main-content {
      flex-grow: 1;
      padding: 40px;
      margin-left: 0;
      transition: margin-left 0.3s ease-in-out;
      width: 100%;
    }
    .main-content.shifted {
      margin-left: 250px;
    }
    .card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }
    .card h2 {
      margin-top: 0;
      color: #c46d9e;
    }
    .button {
      background-color: #e190ba;
      color: white;
      padding: 10px 22px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-size: 15px;
      display: inline-block;
      margin-top: 10px;
    }
    .button:hover {
      background-color: #c46d9e;
    }
    .hamburger {
      font-size: 26px;
      cursor: pointer;
      background: none;
      border: none;
      color: #e190ba;
      margin-bottom: 20px;
      z-index: 1001;
    }
    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: rgba(0, 0, 0, 0.4);
      z-index: 999;
    }
    .overlay.active {
      display: block;
    }
    .costume-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }
    .costume-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 10px;
      text-align: center;
    }
    .costume-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
    }
    .costume-card h4 {
      margin: 10px 0 5px;
      font-size: 16px;
      color: #c46d9e;
    }
    .costume-card p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($shop_name) ?></h3>
  <ul>
    <li><a href="#">✏️ Edit Profile</a></li>
    <li><a href="#">📅 Joined <?= $joined_days ?> days ago</a></li>
    <li><a href="#">📝 Requests from Borrowers</a></li>
    <li><a href="logout.php" style="color: #fff;">🚪 Logout</a></li>
  </ul>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="main-content" id="mainContent">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <h2>Welcome, <?= htmlspecialchars($lender_name) ?> 🛍️</h2>
  <p>Manage your costumes and rental requests in one place.</p>

  <div class="card">
    <h2>📸 Add a New Costume</h2>
    <p>Add costumes to your shop and start renting!</p>
    <a href="add_costume.php" class="button">Upload Costume</a>
  </div>

  <div class="card">
    <h2>🎽 My Costume Listings</h2>
    <div class="costume-grid">
      <?php if (count($costumes) === 0): ?>
        <p>No costumes uploaded yet.</p>
      <?php else: ?>
        <?php foreach ($costumes as $c): ?>
          <div class="costume-card">
            <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">

            <h4><?= htmlspecialchars($c['title']) ?></h4>
            <p>₹<?= htmlspecialchars($c['price_per_day']) ?> | <?= htmlspecialchars($c['size']) ?></p>
            <p>Status: <b style="color:<?= $c['availability'] === 'available' ? 'green' : 'red' ?>"><?= $c['availability'] ?></b></p>
            <a href="edit_costume.php?id=<?= $c['id'] ?>" class="button">Edit</a>
            <?php if ($c['availability'] === 'available'): ?>
              <a href="mark_unavailable.php?id=<?= $c['id'] ?>" class="button" style="background-color: crimson;">Out of Stock</a>
            <?php else: ?>
              <a href="mark_available.php?id=<?= $c['id'] ?>" class="button">Mark Available</a>
            <?php endif; ?>
            <a href="delete_costume.php?id=<?= $c['id'] ?>" class="button" onclick="return confirm('Are you sure you want to delete this costume?');">Delete</a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
    document.getElementById('mainContent').classList.toggle('shifted');
  }
</script>

</body>
</html>
