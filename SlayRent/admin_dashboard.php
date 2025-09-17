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
/* Layout */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #f4f6f9;
  display: flex;
}
.sidebar {
  width: 250px;
  background: #1e293b;
  color: white;
  height: 100vh;
  position: fixed;
  top: 0; left: -250px;
  transition: left 0.3s ease;
  z-index: 1000;
  padding: 20px;
}
.sidebar.active { left: 0; }
.sidebar h2 { margin: 0 0 20px; font-size: 20px; }
.sidebar a {
  display: block;
  padding: 10px;
  color: #fff;
  text-decoration: none;
  border-radius: 5px;
  margin: 5px 0;
}
.sidebar a:hover, .sidebar a.active { background: #475569; }
.main {
  flex: 1;
  margin-left: 0;
  padding: 20px;
  transition: margin-left 0.3s ease;
  width: 100%;
}
.main.shift { margin-left: 250px; }

/* Hamburger */
.hamburger {
  font-size: 24px;
  cursor: pointer;
  margin: 10px;
}

/* Cards */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
  gap: 20px;
}
.card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
  text-align: center;
}
.card button {
  background: #e190ba;
  border: none;
  color: white;
  padding: 8px 15px;
  border-radius: 6px;
  cursor: pointer;
}
.card button:hover { background: #c25d91; }

/* Tables */
table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}
th { background: #f1f5f9; }
.delete-btn {
  background: crimson;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
}
.delete-btn:hover { background: darkred; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h2>SlayRent Admin</h2>
  <p>Welcome, <b>Admin</b></p>
  <p>Total platform revenue: ₹<?= number_format($totalRevenue, 2) ?></p>
  <a href="?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>">Dashboard</a>
  <a href="?page=costumes" class="<?= $page==='costumes'?'active':'' ?>">Manage Costumes</a>
  <a href="?page=users" class="<?= $page==='users'?'active':'' ?>">Manage Users</a>
  <a href="?page=orders" class="<?= $page==='orders'?'active':'' ?>">Orders</a>
  <a href="?page=transactions" class="<?= $page==='transactions'?'active':'' ?>">Transactions</a>
  <a href="logout.php">Logout</a>
</div>

<!-- Main -->
<div class="main" id="main">
  <span class="hamburger" id="hamburger">&#9776;</span>

  <?php if ($page==='dashboard'): ?>
    <div class="card-grid">
      <div class="card">
        <h3>Manage Costumes</h3>
        <p>View and manage costume listings</p>
        <a href="?page=costumes"><button>Open</button></a>
      </div>
      <div class="card">
        <h3>Manage Users</h3>
        <p>View lenders and borrowers</p>
        <a href="?page=users"><button>Open</button></a>
      </div>
      <div class="card">
        <h3>Transactions</h3>
        <p>Platform transactions and commission details</p>
        <a href="?page=transactions"><button>Open</button></a>
      </div>
    </div>

  <?php elseif ($page==='costumes'): ?>
    <h2>Manage Costumes</h2>
    <div class="card-grid">
    <?php
      $res = $conn->query("SELECT c.*, l.shop_name FROM costumes c JOIN lenders l ON c.lender_id=l.id");
      while($row=$res->fetch_assoc()): ?>
        <div class="card">
          <img src="<?= $row['image'] ?>" width="100" height="100"><br>
          <b><?= htmlspecialchars($row['title']) ?></b><br>
          Shop: <?= htmlspecialchars($row['shop_name']) ?><br>
          Price/day: ₹<?= $row['price_per_day'] ?><br>
          <form method="post" action="delete_costume.php" onsubmit="return confirm('Delete this costume?')">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </div>
    <?php endwhile; ?>
    </div>

  <?php elseif ($page==='users'): ?>
    <h2>Manage Lenders</h2>
    <div class="card-grid">
    <?php
      $res = $conn->query("SELECT * FROM lenders");
      while($row=$res->fetch_assoc()): ?>
        <div class="card">
          <b><?= htmlspecialchars($row['shop_name']) ?></b><br>
          <?= $row['name'] ?> | <?= $row['email'] ?><br>
          Contact: <?= $row['contact'] ?><br>
          <form method="post" action="delete_lender.php" onsubmit="return confirm('Delete this lender?')">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </div>
    <?php endwhile; ?>
    </div>

    <h2>Manage Borrowers</h2>
    <div class="card-grid">
    <?php
      $res = $conn->query("SELECT * FROM borrowers");
      while($row=$res->fetch_assoc()): ?>
        <div class="card">
          <b><?= htmlspecialchars($row['name']) ?></b><br>
          <?= $row['email'] ?> | College ID: <?= $row['college_id'] ?><br>
          <form method="post" action="delete_borrower.php" onsubmit="return confirm('Delete this borrower?')">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </div>
    <?php endwhile; ?>
    </div>

  <?php elseif ($page==='orders'): ?>
    <h2>Orders</h2>
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

  <?php elseif ($page==='transactions'): ?>
    <h2>Transactions</h2>
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
  <?php endif; ?>
</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main');
const hamburger = document.getElementById('hamburger');

hamburger.addEventListener('click', () => {
  sidebar.classList.toggle('active');
  main.classList.toggle('shift');
  hamburger.style.display = sidebar.classList.contains('active') ? 'none' : 'block';
});
document.addEventListener('click', (e) => {
  if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
    sidebar.classList.remove('active');
    main.classList.remove('shift');
    hamburger.style.display = 'block';
  }
});
</script>
</body>
</html>
