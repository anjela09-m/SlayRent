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
    :root {
      --lavender: #D1C2D9;
      --charcoal: #191919;
      --pale-silver: #ECECEC;
      --text-light: #ffffff;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--lavender);
      color: var(--charcoal);
    }

    .sidebar {
      width: 250px;
      background-color: var(--charcoal);
      color: var(--text-light);
      padding: 25px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: -250px;
      transition: 0.3s;
      z-index: 1000;
    }
    .sidebar.active {
      left: 0;
    }
    .sidebar h3 {
      font-size: 22px;
      margin-bottom: 20px;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar ul li {
      margin: 15px 0;
    }
    .sidebar ul li a {
      color: var(--text-light);
      text-decoration: none;
      font-size: 16px;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      z-index: 999;
    }
    .overlay.active {
      display: block;
    }

    .main-content {
      margin-left: 0;
      transition: 0.3s;
      padding: 30px;
      background-color: var(--pale-silver);
      min-height: 100vh;
    }
    .main-content.shifted {
      margin-left: 250px;
    }

    .hamburger {
      font-size: 24px;
      background: none;
      border: none;
      color: var(--charcoal);
      cursor: pointer;
      margin-bottom: 15px;
    }

    .welcome-section {
      margin-bottom: 30px;
    }
    .welcome-section h2 {
      font-size: 28px;
      color: var(--charcoal);
    }

    .dashboard-flex {
      display: flex;
      gap: 30px;
    }

    .card {
      background: var(--lavender);
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      color: var(--charcoal);
    }

    .left-panel {
      flex: 2;
    }
    .right-panel {
      flex: 1;
    }

    .costume-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
    }

    .costume-card {
      background: var(--lavender);
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 15px;
      text-align: center;
      color: var(--charcoal);
    }
    .costume-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
    }

    .button {
      background-color: var(--charcoal);
      color: var(--text-light);
      padding: 10px 18px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
      margin: 5px;
      display: inline-block;
    }
    .button:hover {
      background-color: var(--text-light);
      color: var(--charcoal);
    }
</style>

</head>
<body>

<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($shop_name) ?></h3>
  <ul>
    <li><a href="edit_profile_lender.php"> Edit Profile</a></li>
    <li><a href="#"> Joined <?= $joined_days ?> days ago</a></li>
    <li><a href="#"> Requests from Borrowers</a></li>
    <li><a href="logout.php"> Logout</a></li>
  </ul>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="main-content" id="mainContent">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>

  <div class="welcome-section">
    <h2>Welcome, <?= htmlspecialchars($lender_name) ?> </h2>
    <p>Manage your costumes and rental requests in one place.</p>
  </div>

  <div class="dashboard-flex">
    <div class="left-panel">
      <div class="card">
        <h3>Add a New Costume</h3>
        <p>Add costumes to your shop and start renting!</p>
        <a href="add_costume.php" class="button">Upload Costume</a>
      </div>

      <div class="card">
        <h3>My Costume Listings</h3>
        <div class="costume-grid">
          <?php if (count($costumes) === 0): ?>
            <p>No costumes uploaded yet.</p>
          <?php else: ?>
           <?php foreach ($costumes as $c): ?>
  <div class="costume-card">
    <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
    <h4><?= htmlspecialchars($c['title']) ?></h4>
    <p>₹<?= htmlspecialchars($c['price_per_day']) ?> | <?= htmlspecialchars($c['size']) ?></p>
    <p>Status: <b style="color:<?= $c['availability'] === 'available' ? 'lightgreen' : 'crimson' ?>"><?= $c['availability'] ?></b></p>
    <a href="edit_costume.php?id=<?= $c['id'] ?>" class="button">Edit</a>
    <?php if ($c['availability'] === 'available'): ?>
      <a href="mark_unavailable.php?id=<?= $c['id'] ?>" class="button" style="background-color: crimson; color: white;">Out of Stock</a>
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

    <div class="right-panel">
      <div class="card">
        <h3>Rental Requests</h3>
        <p>Borrower requests will appear here.</p>
      </div>
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
