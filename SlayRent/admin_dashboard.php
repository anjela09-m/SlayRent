<?php
require 'includes/config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all costumes
$costumeQuery = "SELECT * FROM costumes ORDER BY id DESC";
$costumes = mysqli_query($conn, $costumeQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Costumes</title>
<style>
<style>
/* ===== COLOR PALETTE =====
   Soft Lavender Gray  #D1C2D9
   Deep Charcoal       #191919
============================ */

body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background-color: #ECECEC; /* Pale Silver */
  color: #2c3e50;
}
.main {
  margin-left: 0;
  padding: 20px;
  transition: margin-left 0.3s ease;
  background-color: #ECECEC; /* Pale Silver background for content */
  min-height: 100vh; /* ensures full height background */
}

/* Hamburger Menu */
.hamburger {
  position: fixed;
  top: 20px;
  left: 20px;
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
  background-color: #191919; /* Deep Charcoal */
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

/* Sidebar */
.sidebar {
  position: fixed;
  left: -250px;
  top: 0;
  width: 250px;
  height: 100%;
  background: #191919; /* Deep Charcoal */
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}
.sidebar.open {
  left: 0;
}
.sidebar a {
  display: block;
  margin: 15px 20px;
  padding: 10px;
  text-decoration: none;
  color: #D1C2D9; /* Soft Lavender Gray */
  border-radius: 4px;
  transition: background 0.3s;
}
.sidebar a:hover {
  background: #D1C2D9; /* Soft Lavender Gray */
  color: #191919; /* Deep Charcoal */
}

/* Main Content */
.main {
  margin-left: 0;
  padding: 20px;
  transition: margin-left 0.3s ease;
}
.main.shifted {
  margin-left: 250px;
}

/* Cards */
.card {
  border: 1px solid #191919;
  background: #D1C2D9; /* Soft Lavender Gray */
  padding: 15px;
  margin: 10px;
  display: inline-block;
  width: 220px;
  vertical-align: top;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 5px;
}
.card h4 {
  margin: 10px 0;
}

/* Delete Button */
.delete-btn {
  background-color: #191919; /* Deep Charcoal */
  color: #D1C2D9; /* Soft Lavender Gray */
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}
.delete-btn:hover {
  background-color: #D1C2D9;
  color: #191919;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  background: #D1C2D9;
  margin-top: 15px;
  border-radius: 8px;
  overflow: hidden;
}
th, td {
  padding: 10px;
  border-bottom: 1px solid #191919;
}
th {
  background-color: #191919;
  color: #D1C2D9;
}
tr:hover td {
  background-color: #191919;
  color: #D1C2D9;
}
</style>


</head>
<body>

<!-- Hamburger -->
<div class="hamburger" id="hamburger">
  <span></span>
  <span></span>
  <span></span>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <a href="admin_dashboard.php">Dashboard</a>
  <a href="add_costume.php">Add Costume</a>
  <a href="orders.php">View Orders</a>
  <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main" id="mainContent">
  <h1>Admin Dashboard - Costume Listings</h1>
  <h2>🎭 All Uploaded Costumes</h2>

  <?php while ($row = mysqli_fetch_assoc($costumes)) { ?>
    <div class="card">
      <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
      <h4><?php echo htmlspecialchars($row['title']); ?></h4>
      <p>₹<?php echo htmlspecialchars($row['price_per_day']); ?></p>
      <form method="POST" action="delete_costume.php" onsubmit="return confirm('Are you sure you want to delete this costume?');">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <button type="submit" class="delete-btn">🗑 Delete</button>
      </form>
    </div>
  <?php } ?>
</div>

<script>
// Hamburger toggle
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

hamburger.addEventListener('click', () => {
  hamburger.classList.toggle('active');
  sidebar.classList.toggle('open');
  mainContent.classList.toggle('shifted');
});

// Close sidebar when clicking outside
document.addEventListener('click', function (e) {
  if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.remove('open');
    hamburger.classList.remove('active');
    mainContent.classList.remove('shifted');
  }
});
</script>

</body>
</html>
