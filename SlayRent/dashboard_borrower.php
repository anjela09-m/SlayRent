<?php
session_start();
include 'includes/config.php'; 

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Borrower';
$joined_days = 0;

$stmt = $conn->prepare("SELECT created_at FROM borrowers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $joined_days = floor((time() - strtotime($row['created_at'])) / (60 * 60 * 24));
}

// Costume filtering
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

// Rentals
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      margin: 0;
      background: #fdf5fa;
    }
    /* Hamburger with animation */
    .hamburger {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 24px;
      height: 18px;
      cursor: pointer;
    }
    .hamburger span {
      display: block;
      height: 3px;
      background-color: #000;
      border-radius: 2px;
      transition: all 0.3s ease;
    }
    .hamburger.active span:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px);
    }
    .hamburger.active span:nth-child(2) {
      opacity: 0;
    }
    .hamburger.active span:nth-child(3) {
      transform: rotate(-45deg) translate(5px, -5px);
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: -260px;
      width: 240px;
      height: 100vh;
      background: #ea7fb8ff;
      color: white;
      padding: 30px 20px;
      transition: left 0.3s ease;
      z-index: 999;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar h3 {
      margin-bottom: 20px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      margin: 15px 0;
    }
    .sidebar .card {
      background: rgba(255, 255, 255, 0.2);
      padding: 10px;
      border-radius: 8px;
      margin-top: 15px;
    }
    .main {
      margin-left: 0;
      padding: 30px 40px 40px 40px;
      transition: margin-left 0.3s ease;
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
  </style>
</head>
<body>
  <!-- Animated Hamburger -->
  <div class="hamburger" id="hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>

  <div class="sidebar" id="sidebar">
    <h3><?= htmlspecialchars($name) ?></h3>
    <a href="edit_borrower_profile.php">✏️ Edit Profile</a>
    <a href="#">📅 Joined <?= $joined_days ?> days ago</a>
    <a href="#">📦 My Rentals</a>

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

    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main">
    <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
    <div class="costume-grid">
      <?php if (empty($costumes)): ?>
        <p style="grid-column: 1/-1;">No costumes found!</p>
      <?php else: foreach ($costumes as $c): ?>
        <div class="costume-card">
          <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= $c['price_per_day'] ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
          <p><?= htmlspecialchars($c['category']) ?></p>
          <a href="rent_costume.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

<script>
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    hamburger.classList.toggle('active');
  });

  document.addEventListener('click', (event) => {
    if (sidebar.classList.contains('active') &&
        !sidebar.contains(event.target) &&
        !hamburger.contains(event.target)) {
      sidebar.classList.remove('active');
      hamburger.classList.remove('active');
    }
  });
</script>
</body>
</html>
