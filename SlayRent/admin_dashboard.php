<?php
session_start();
require 'includes/config.php';

// ✅ Only admin can access
if (!isset($_SESSION['admin'])) {
    exit("Unauthorized");
}

// Calculate total revenue (10% commission of payments)
$totalRevenue = 0;
$revQuery = $conn->query("SELECT SUM(amount*0.1) AS revenue FROM payments WHERE status='paid'");
if ($revQuery && $revRow = $revQuery->fetch_assoc()) {
    $totalRevenue = $revRow['revenue'] ?? 0;
}

// Page
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - SlayRent</title>
<style>
/* Import Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap');

/* CSS Variables for Color Palette */
:root {
  --cream: #FBDB93;
  --warm-red: #BE5B50;
  --burgundy: #8A2D3B;
  --dark-burgundy: #641B2E;
  --white: #ffffff;
  --light-cream: rgba(251, 219, 147, 0.1);
  --shadow: rgba(100, 27, 46, 0.15);
  --shadow-dark: rgba(100, 27, 46, 0.25);
}

/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, var(--cream) 0%, rgba(251, 219, 147, 0.8) 50%, var(--cream) 100%);
  display: flex;
  min-height: 100vh;
  color: var(--dark-burgundy);
  overflow-x: hidden;
  position: relative;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(190, 91, 80, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(138, 45, 59, 0.1) 0%, transparent 50%);
  pointer-events: none;
  z-index: -1;
}

/* Sidebar Styles */
.sidebar {
  width: 320px;
  background: linear-gradient(180deg, var(--dark-burgundy) 0%, var(--burgundy) 50%, var(--dark-burgundy) 100%);
  color: var(--cream);
  height: 100vh;
  position: fixed;
  top: 0; 
  left: -320px;
  transition: left 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  z-index: 1000;
  padding: 0;
  box-shadow: 4px 0 30px var(--shadow-dark);
  border-right: 3px solid var(--warm-red);
  backdrop-filter: blur(10px);
  overflow-y: auto;
}

.sidebar.active { 
  left: 0; 
}

.sidebar-header {
  padding: 30px 25px;
  text-align: center;
  border-bottom: 2px solid var(--warm-red);
  background: rgba(251, 219, 147, 0.05);
}

.sidebar h2 { 
  font-family: 'Playfair Display', serif;
  font-size: 32px; 
  font-weight: 700;
  color: var(--cream);
  text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
  margin-bottom: 15px;
  letter-spacing: 1px;
}

.revenue-display {
  background: linear-gradient(135deg, var(--cream), rgba(251, 219, 147, 0.9));
  color: var(--dark-burgundy);
  padding: 20px;
  border-radius: 15px;
  margin: 20px 25px;
  text-align: center;
  box-shadow: 0 8px 25px var(--shadow);
  border: 2px solid var(--warm-red);
}

.revenue-display p {
  font-size: 14px;
  margin-bottom: 10px;
  font-weight: 500;
}

.revenue-amount {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--dark-burgundy);
}

.sidebar nav {
  padding: 20px 0;
}

.sidebar a {
  display: block;
  padding: 18px 25px;
  color: var(--cream);
  text-decoration: none;
  border-radius: 0;
  margin: 2px 0;
  font-weight: 500;
  font-size: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  position: relative;
  border-left: 4px solid transparent;
}

.sidebar a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 0;
  background: linear-gradient(135deg, var(--warm-red), var(--cream));
  transition: width 0.3s ease;
}

.sidebar a:hover, .sidebar a.active { 
  background: rgba(251, 219, 147, 0.15);
  color: var(--cream);
  border-left: 4px solid var(--warm-red);
  transform: translateX(8px);
  padding-left: 35px;
}

.sidebar a:hover::before, .sidebar a.active::before {
  width: 4px;
}

/* Main Content Area */
.main {
  flex: 1;
  margin-left: 0;
  padding: 0;
  transition: margin-left 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  width: 100%;
  background: transparent;
  min-height: 100vh;
}

.main.shift { 
  margin-left: 320px; 
}

/* Hamburger Menu */
.hamburger {
  font-size: 24px;
  cursor: pointer;
  margin: 20px;
  color: var(--dark-burgundy);
  background: var(--cream);
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 25px var(--shadow);
  transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  border: 3px solid var(--warm-red);
  position: relative;
  z-index: 999;
}

.hamburger:hover {
  background: var(--warm-red);
  color: var(--cream);
  transform: scale(1.1) rotate(90deg);
  box-shadow: 0 12px 35px var(--shadow-dark);
}

/* Content Area */
.content-wrapper {
  padding: 20px 30px 50px;
}

/* Dashboard Styles */
.dashboard-welcome {
  text-align: center;
  padding: 60px 40px;
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.3) 100%);
  border-radius: 25px;
  box-shadow: 0 15px 40px var(--shadow);
  border: 3px solid var(--warm-red);
  margin-bottom: 50px;
  position: relative;
  overflow: hidden;
}

.dashboard-welcome::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(251, 219, 147, 0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.dashboard-welcome h1 {
  font-family: 'Playfair Display', serif;
  color: var(--dark-burgundy);
  font-size: 48px;
  font-weight: 700;
  margin-bottom: 20px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.1);
  position: relative;
  z-index: 2;
}

.dashboard-welcome p {
  color: var(--burgundy);
  font-size: 20px;
  font-weight: 500;
  position: relative;
  z-index: 2;
}

/* Card Grid - Random Placement */
.card-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-template-rows: repeat(8, 100px);
  gap: 20px;
  margin-top: 40px;
  position: relative;
}

.card {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.2) 100%);
  border-radius: 25px;
  padding: 40px 30px;
  box-shadow: 0 15px 40px var(--shadow);
  text-align: center;
  border: 3px solid var(--warm-red);
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

/* Random Positioning for Dashboard Cards */
.card:nth-child(1) {
  grid-column: 1 / 5;
  grid-row: 1 / 4;
  transform: rotate(-2deg);
}

.card:nth-child(2) {
  grid-column: 6 / 10;
  grid-row: 2 / 5;
  transform: rotate(1deg);
}

.card:nth-child(3) {
  grid-column: 3 / 7;
  grid-row: 5 / 8;
  transform: rotate(-1deg);
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy), var(--dark-burgundy));
}

.card:hover {
  transform: translateY(-15px) scale(1.05) rotate(0deg) !important;
  box-shadow: 0 25px 60px var(--shadow-dark);
  border-color: var(--burgundy);
}

.card h3 {
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  color: var(--dark-burgundy);
  font-size: 28px;
  margin-bottom: 15px;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

.card p {
  color: var(--burgundy);
  font-size: 16px;
  margin-bottom: 25px;
  line-height: 1.6;
}

.card button {
  background: linear-gradient(135deg, var(--warm-red), var(--burgundy));
  border: none;
  color: var(--cream);
  padding: 15px 35px;
  border-radius: 30px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  box-shadow: 0 8px 20px rgba(190, 91, 80, 0.3);
}

.card button:hover { 
  background: linear-gradient(135deg, var(--dark-burgundy), var(--warm-red));
  border: 2px solid var(--cream);
  transform: scale(1.1);
  box-shadow: 0 12px 30px var(--shadow-dark);
}

/* Content Sections */
.content-header {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  color: var(--dark-burgundy);
  font-size: 42px;
  margin-bottom: 30px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.1);
  text-align: center;
}

/* Management Cards for Lists */
.management-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 30px;
  margin-top: 30px;
}

.management-card {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.1) 100%);
  border-radius: 20px;
  padding: 35px;
  box-shadow: 0 12px 35px var(--shadow);
  border: 3px solid var(--warm-red);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.management-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 5px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy));
}

.management-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 45px var(--shadow-dark);
  border-color: var(--burgundy);
}

.management-card img {
  border-radius: 15px;
  margin-bottom: 20px;
  border: 3px solid var(--warm-red);
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  object-fit: cover;
}

.management-card h4 {
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  color: var(--dark-burgundy);
  font-size: 24px;
  margin-bottom: 15px;
  text-align: center;
}

.management-card b {
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  color: var(--dark-burgundy);
  font-size: 20px;
  display: block;
  text-align: center;
  margin-bottom: 10px;
}

.management-card p, .management-card br + text {
  color: var(--burgundy);
  font-size: 16px;
  line-height: 1.6;
  margin-bottom: 10px;
  text-align: center;
}

/* Tables */
.table-container {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.1) 100%);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 15px 40px var(--shadow);
  border: 3px solid var(--warm-red);
  margin-top: 30px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

th {
  background: linear-gradient(135deg, var(--dark-burgundy), var(--burgundy));
  color: var(--cream);
  padding: 20px 18px;
  text-align: left;
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  font-size: 16px;
  text-transform: uppercase;
  letter-spacing: 1px;
  border: none;
}

td {
  padding: 18px;
  color: var(--dark-burgundy);
  font-size: 15px;
  border-bottom: 1px solid rgba(190, 91, 80, 0.2);
}

tr:nth-child(even) {
  background: var(--light-cream);
}

tr:hover {
  background: rgba(190, 91, 80, 0.1);
  transition: all 0.2s ease;
}

/* Delete Button */
.delete-btn {
  background: linear-gradient(135deg, var(--burgundy), var(--dark-burgundy));
  color: var(--cream);
  border: none;
  padding: 12px 25px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 14px;
  box-shadow: 0 6px 15px rgba(138, 45, 59, 0.3);
}

.delete-btn:hover { 
  background: linear-gradient(135deg, var(--dark-burgundy), var(--warm-red));
  transform: scale(1.05);
  box-shadow: 0 8px 20px var(--shadow-dark);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .card-grid {
    grid-template-columns: repeat(8, 1fr);
    grid-template-rows: repeat(10, 80px);
  }
  
  .card:nth-child(1) {
    grid-column: 1 / 4;
    grid-row: 1 / 4;
  }
  
  .card:nth-child(2) {
    grid-column: 5 / 8;
    grid-row: 2 / 5;
  }
  
  .card:nth-child(3) {
    grid-column: 2 / 5;
    grid-row: 6 / 9;
  }
}

@media (max-width: 768px) {
  .sidebar {
    width: 280px;
    left: -280px;
  }
  
  .main.shift {
    margin-left: 280px;
  }
  
  .card-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  .card {
    transform: none !important;
  }
  
  .management-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-welcome h1 {
    font-size: 36px;
  }
  
  .content-header {
    font-size: 32px;
  }
}

@media (max-width: 480px) {
  .content-wrapper {
    padding: 15px 20px;
  }
  
  .dashboard-welcome {
    padding: 40px 20px;
  }
  
  .card {
    padding: 30px 20px;
  }
  
  .management-card {
    padding: 25px;
  }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2>SlayRent Admin</h2>
    <div class="revenue-display">
      <p><strong>Welcome, Admin</strong></p>
      <p>Platform Commission Revenue:</p>
      <div class="revenue-amount">₹<?= number_format($totalRevenue, 2) ?></div>
    </div>
  </div>
  
  <nav>
    <a href="?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>">🏠 Dashboard</a>
    <a href="?page=costumes" class="<?= $page==='costumes'?'active':'' ?>">👗 Manage Costumes</a>
    <a href="?page=users" class="<?= $page==='users'?'active':'' ?>">👥 Manage Users</a>
    <a href="?page=orders" class="<?= $page==='orders'?'active':'' ?>">📋 Orders</a>
    <a href="?page=transactions" class="<?= $page==='transactions'?'active':'' ?>">💰 Transactions</a>
    <a href="generate_report.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</div>

<!-- Main -->
<div class="main" id="main">
  <span class="hamburger" id="hamburger">☰</span>

  <div class="content-wrapper">
    <?php if ($page==='dashboard'): ?>
      <div class="dashboard-welcome">
        <h1>SlayRent Admin Dashboard</h1>
        <p>Welcome to your comprehensive management center</p>
      </div>
      
      <div class="card-grid">
        <div class="card">
          <h3>Manage Costumes</h3>
          <p>View and manage costume listings across the platform</p>
          <a href="?page=costumes"><button>Open</button></a>
        </div>
        <div class="card">
          <h3>Manage Users</h3>
          <p>Oversee lenders and borrowers on the platform</p>
          <a href="?page=users"><button>Open</button></a>
        </div>
        <div class="card">
          <h3>Transactions</h3>
          <p>Monitor platform transactions and commission details</p>
          <a href="?page=transactions"><button>Open</button></a>
        </div>
      </div>

    <?php elseif ($page==='costumes'): ?>
      <h2 class="content-header">Manage Costumes</h2>
      <div class="management-grid">
      <?php
        $res = $conn->query("SELECT c.*, l.shop_name FROM costumes c JOIN lenders l ON c.lender_id=l.id");
        while($row=$res->fetch_assoc()): ?>
          <div class="management-card">
            <img src="<?= $row['image'] ?>" width="120" height="120"><br>
            <b><?= htmlspecialchars($row['title']) ?></b><br>
            <p>Shop: <?= htmlspecialchars($row['shop_name']) ?></p>
            <p>Price/day: ₹<?= $row['price_per_day'] ?></p>
            <form method="post" action="delete_costume.php" onsubmit="return confirm('Delete this costume?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          </div>
      <?php endwhile; ?>
      </div>

    <?php elseif ($page==='users'): ?>
      <h2 class="content-header">Manage Lenders</h2>
      <div class="management-grid">
      <?php
        $res = $conn->query("SELECT * FROM lenders");
        while($row=$res->fetch_assoc()): ?>
          <div class="management-card">
            <b><?= htmlspecialchars($row['shop_name']) ?></b>
            <p><?= $row['name'] ?> | <?= $row['email'] ?></p>
            <p>Contact: <?= $row['contact'] ?></p>
            <form method="post" action="delete_lender.php" onsubmit="return confirm('Delete this lender?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          </div>
      <?php endwhile; ?>
      </div>

      <h2 class="content-header">Manage Borrowers</h2>
      <div class="management-grid">
      <?php
        $res = $conn->query("SELECT * FROM borrowers");
        while($row=$res->fetch_assoc()): ?>
          <div class="management-card">
            <b><?= htmlspecialchars($row['name']) ?></b>
            <p><?= $row['email'] ?> | College ID: <?= $row['college_id'] ?></p>
            <form method="post" action="delete_borrower.php" onsubmit="return confirm('Delete this borrower?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          </div>
      <?php endwhile; ?>
      </div>

    <?php elseif ($page==='orders'): ?>
      <h2 class="content-header">Orders</h2>
      <div class="table-container">
        <table>
          <tr><th>ID</th><th>Costume</th><th>Shop</th><th>Start</th><th>End</th><th>Total Price</th><th>Status</th></tr>
          <?php
          $res = $conn->query("SELECT rr.*, c.title, l.shop_name 
                               FROM rental_requests rr 
                               JOIN costumes c ON rr.costume_id=c.id
                               JOIN lenders l ON rr.lender_id=l.id");
          while($row=$res->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['shop_name']) ?></td>
              <td><?= $row['start_date'] ?></td>
              <td><?= $row['end_date'] ?></td>
              <td>₹<?= $row['total_price'] ?></td>
              <td><?= $row['status'] ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>

    <?php elseif ($page==='transactions'): ?>
      <h2 class="content-header">Transactions</h2>
      <div class="table-container">
        <table>
          <tr><th>ID</th><th>Costume</th><th>Shop</th><th>Amount</th><th>Commission (10%)</th><th>Status</th><th>Date</th></tr>
          <?php
          $res = $conn->query("SELECT p.*, c.title, l.shop_name 
                               FROM payments p 
                               JOIN rental_requests rr ON p.rental_request_id=rr.id
                               JOIN costumes c ON rr.costume_id=c.id
                               JOIN lenders l ON rr.lender_id=l.id");
          while($row=$res->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['shop_name']) ?></td>
              <td>₹<?= $row['amount'] ?></td>
              <td>₹<?= $row['amount']*0.1 ?></td>
              <td><?= $row['status'] ?></td>
              <td><?= $row['created_at'] ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Ensure DOM is fully loaded before running
document.addEventListener('DOMContentLoaded', function() {
  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const main = document.getElementById('main');
  const hamburger = document.getElementById('hamburger');

  // Ensure sidebar starts hidden
  sidebar.classList.remove('active');
  main.classList.remove('shift');
  
  hamburger.addEventListener('click', function(e) {
    e.preventDefault();
    sidebar.classList.toggle('active');
    main.classList.toggle('shift');
    hamburger.style.display = sidebar.classList.contains('active') ? 'none' : 'flex';
  });

  // Click outside to close sidebar
  document.addEventListener('click', function(e) {
    if (sidebar.classList.contains('active') && 
        !sidebar.contains(e.target) && 
        !hamburger.contains(e.target)) {
      sidebar.classList.remove('active');
      main.classList.remove('shift');
      hamburger.style.display = 'flex';
    }
  });
});
</script>
</body>
</html>
