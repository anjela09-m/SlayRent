<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

// Fetch first 10 costumes initially
$costumeQuery = "SELECT * FROM costumes LIMIT 10";
$costumes = mysqli_query($conn, $costumeQuery);

// Fetch all transactions
$transactionQuery = "SELECT t.*, c.title AS costume_name, b.name AS borrower_name, l.shop_name AS lender_name, b.email AS borrower_email, l.email AS lender_email 
FROM transactions t
JOIN costumes c ON t.costume_id = c.id
JOIN borrowers b ON t.borrower_id = b.id
JOIN lenders l ON t.lender_id = l.id";
$transactions = mysqli_query($conn, $transactionQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f7fa;
      color: #2c3e50;
    }

    /* New Hamburger Style */
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
      background-color: #000; /* Change to #fff if on dark bg */
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
      background: #1a2a3a;
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
      color: #ecf0f1;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .sidebar a:hover {
      background: #4db6ac;
      color: white;
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

    /* Costume Cards */
    .card {
      border: 1px solid #ddd;
      background: white;
      padding: 15px;
      margin: 10px;
      display: inline-block;
      width: 220px;
      vertical-align: top;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      transition: box-shadow 0.3s ease;
    }
    .card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background-color 0.3s ease, transform 0.1s ease;
    }
    .delete-btn:hover {
      background-color: #4db6ac;
      transform: scale(1.03);
    }

    /* Toggle Button */
    .toggle-btn {
      margin: 20px 0;
      padding: 10px 20px;
      background-color: #4db6ac;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .toggle-btn:hover {
      background-color: #399187;
    }

    /* Transactions Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      margin-top: 15px;
      border-radius: 8px;
      overflow: hidden;
    }
    th, td {
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #4db6ac;
      color: white;
    }
    tr:hover td {
      background-color: #f0f5f4;
    }
  </style>
</head>
<body>

<!-- New Hamburger -->
<div class="hamburger" id="hamburger">
  <span></span>
  <span></span>
  <span></span>
</div>

<div class="sidebar" id="sidebar">
  <a href="#">Dashboard</a>
  <a href="admin_manage_users.php">Manage Users</a>
  <a href="#">View Transactions</a>
  <a href="logout.php">Logout</a>
</div>

<div class="main" id="mainContent">
  <h2>  <br>View Costume Listings</h2>

  <div id="costumeCards">
    <?php while ($row = mysqli_fetch_assoc($costumes)) { ?>
      <div class="card">
        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
        <h4><?php echo $row['title']; ?></h4>
        <p>₹<?php echo $row['price_per_day']; ?></p>
        <form method="POST" action="admin_delete_costume.php" onsubmit="return confirm('Delete this costume?')">
          <input type="hidden" name="costume_id" value="<?php echo $row['id']; ?>">
          <button class="delete-btn">🗑 Delete</button>
        </form>
      </div>
    <?php } ?>
  </div>

  <button class="toggle-btn" onclick="toggleCostumes()" id="toggleBtn">View All</button>

  <div class="transactions">
    <h2>💰 View Transactions</h2>
    <table>
      <tr>
        <th>Costume</th>
        <th>Lender</th>
        <th>Borrower</th>
        <th>Rent Amount</th>
        <th>Commission (10%)</th>
        <th>Lender Earnings (90%)</th>
        <th>Date</th>
      </tr>
      <?php while ($t = mysqli_fetch_assoc($transactions)) {
        $rent = $t['rent_amount'];
        $commission = $rent * 0.10;
        $lenderEarning = $rent * 0.90;
      ?>
      <tr>
        <td><?php echo $t['costume_name']; ?></td>
        <td><?php echo $t['lender_name']; ?> (<?php echo $t['lender_email']; ?>)</td>
        <td><?php echo $t['borrower_name']; ?> (<?php echo $t['borrower_email']; ?>)</td>
        <td>₹<?php echo $rent; ?></td>
        <td>₹<?php echo number_format($commission, 2); ?></td>
        <td>₹<?php echo number_format($lenderEarning, 2); ?></td>
        <td><?php echo $t['date']; ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>
</div>

<script>
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');

  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    hamburger.classList.toggle('active');
  });

  document.addEventListener('click', function (e) {
    if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
      sidebar.classList.remove('open');
      hamburger.classList.remove('active');
    }
  });

  let viewAll = false;
  function toggleCostumes() {
    const btn = document.getElementById("toggleBtn");
    fetch(`fetch_costumes.php?all=${viewAll ? 0 : 1}`)
      .then(response => response.text())
      .then(data => {
        document.getElementById("costumeCards").innerHTML = data;
        viewAll = !viewAll;
        btn.textContent = viewAll ? "View Less" : "View All";
      });
  }
</script>

</body>
</html>
