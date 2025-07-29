<?php
session_start();
include 'includes/config.php';

// Redirect if not borrower
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Borrower';
$slayrent_id = $_SESSION['slayrent_id'] ?? '';

// Joined days
$joined_days = 0;
$stmt = $conn->prepare("SELECT created_at FROM borrowers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $joined_days = floor((time() - strtotime($row['created_at'])) / (60 * 60 * 24));
}

// === Browse / Search Costumes ===
$where = "availability = 'available'";
$params = [];
$types = "";

if (!empty($_GET['keyword'])) {
  $where .= " AND (title LIKE ? OR description LIKE ?)";
  $kw = '%' . $_GET['keyword'] . '%';
  $params[] = $kw; $params[] = $kw;
  $types .= "ss";
}
if (!empty($_GET['category'])) {
  $where .= " AND category = ?";
  $params[] = $_GET['category'];
  $types .= "s";
}
if (!empty($_GET['min_price'])) {
  $where .= " AND price_per_day >= ?";
  $params[] = $_GET['min_price'];
  $types .= "i";
}
if (!empty($_GET['max_price'])) {
  $where .= " AND price_per_day <= ?";
  $params[] = $_GET['max_price'];
  $types .= "i";
}

$sql = "SELECT * FROM costumes WHERE $where ORDER BY id DESC";
$costumes = [];
$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $costumes[] = $row;
}

// === Rentals ===
$recent_rentals = [];
$rstmt = $conn->prepare("SELECT r.*, c.title FROM rentals r JOIN costumes c ON r.costume_id = c.id WHERE r.borrower_id = ? ORDER BY r.rented_at DESC LIMIT 2");
$rstmt->bind_param("i", $user_id);
$rstmt->execute();
$rres = $rstmt->get_result();
while ($row = $rres->fetch_assoc()) {
  $recent_rentals[] = $row;
}

$pending_returns = [];
$pstmt = $conn->prepare("SELECT r.*, c.title FROM rentals r JOIN costumes c ON r.costume_id = c.id WHERE r.borrower_id = ? AND r.return_status = 'pending'");
$pstmt->bind_param("i", $user_id);
$pstmt->execute();
$pres = $pstmt->get_result();
while ($row = $pres->fetch_assoc()) {
  $pending_returns[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrower Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #fdf5fa;
    }
    .hamburger {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1000;
      background: #e190ba;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-size: 20px;
      cursor: pointer;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -260px;
      width: 240px;
      height: 100vh;
      background: #e190ba;
      color: white;
      padding: 30px 20px;
      transition: 0.3s ease;
      z-index: 999;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar h3 {
      margin: 0 0 20px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      margin: 15px 0;
    }
    .main {
      margin-left: 0;
      padding: 40px;
      transition: margin-left 0.3s ease;
    }
    .sidebar.active ~ .main {
      margin-left: 250px;
    }
    .filters {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .filters input, .filters select {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .costume-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
    }
    .costume-card {
      background: white;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }
    .costume-card img {
      width: 100%; height: 180px;
      object-fit: cover;
      border-radius: 10px;
    }
    .button {
      background: #e190ba;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }
    .right-boxes {
      display: flex;
      justify-content: flex-end;
      gap: 30px;
      margin: 20px 0;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 6px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <button class="hamburger" onclick="document.querySelector('.sidebar').classList.toggle('active')">☰</button>
  <div class="sidebar">
    <h3><?= htmlspecialchars($name) ?></h3>
    <a href="edit_borrower_profile.php">✏️ Edit Profile</a>
    <a href="#">📅 Joined <?= $joined_days ?> days ago</a>
    <a href="#">📦 My Orders</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
  <div class="main">
    <h2>Welcome to SlayRent, <?= htmlspecialchars($name) ?> 👗</h2>
    <p>Your SlayRent ID: <strong><?= htmlspecialchars($slayrent_id) ?></strong></p>

    <h2 style="color: #e190ba;">🎭 Browse Costumes</h2>
    <form method="GET" class="filters">
      <input type="text" name="keyword" placeholder="Search keywords (e.g. saree, Christmas, red)" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
      <select name="category">
        <option value="">All Categories</option>
        <option value="Onam" <?= ($_GET['category'] ?? '') === 'Onam' ? 'selected' : '' ?>>Onam</option>
        <option value="Christmas" <?= ($_GET['category'] ?? '') === 'Christmas' ? 'selected' : '' ?>>Christmas</option>
        <option value="Cultural Fest" <?= ($_GET['category'] ?? '') === 'Cultural Fest' ? 'selected' : '' ?>>Cultural Fest</option>
        <option value="Halloween" <?= ($_GET['category'] ?? '') === 'Halloween' ? 'selected' : '' ?>>Halloween</option>
      </select>
      <input type="number" name="min_price" placeholder="Min ₹" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
      <input type="number" name="max_price" placeholder="Max ₹" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
      <button type="submit" class="button">🔍 Search</button>
    </form>

    <div class="right-boxes">
      <div class="card">
        <h4>🕓 Recent Rentals</h4>
        <?php if (empty($recent_rentals)): ?>
          <p>No rentals yet.</p>
        <?php else: foreach ($recent_rentals as $r): ?>
          <p><b><?= htmlspecialchars($r['title']) ?></b> on <?= date('d M Y', strtotime($r['rented_at'])) ?></p>
        <?php endforeach; endif; ?>
      </div>
      <div class="card">
        <h4>🔁 Pending Returns</h4>
        <?php if (empty($pending_returns)): ?>
          <p>No pending returns.</p>
        <?php else: foreach ($pending_returns as $p): ?>
          <p><?= htmlspecialchars($p['title']) ?> → Return by <?= date('d M Y', strtotime($p['return_by'])) ?></p>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="costume-grid">
      <?php if (empty($costumes)): ?>
        <p style="grid-column: 1/-1;">No costumes found!</p>
      <?php else: foreach ($costumes as $c): ?>
        <div class="costume-card">
          <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= $c['price_per_day'] ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
          <p><?= htmlspecialchars($c['category']) ?></p>
          <a href="rent_now.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</body>
</html>
