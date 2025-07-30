
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
      font-family: Arial, sans-serif;
    }
    .hamburger {
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1000;
  background: #f1b3d3;
  color: white;
  border: none;
  padding: 12px 12px;
  border-radius: 8px;
  font-size: 15px;
  cursor: pointer;
}
.hamburger.hidden {
  display: none;
}
    .sidebar {
  position: fixed;
  left: -250px;
  top: 0;
  width: 250px;
  height: 100%;
  background: #f1b3d3;
  transition: left 0.3s ease;
  z-index: 999;
}

    .sidebar.active {
      left: 0;
    }
.sidebar.open {
  left: 0;
}
    .sidebar a {
      display: block;
      margin: 20px 0;
      text-decoration: none;
      color: #333;
      font-weight: bold;
    }

    .main {
      margin-left: 0;
      padding: 20px;
      transition: 0.3s;
    }

    .main.shifted {
      margin-left: 250px;
    }

    .card {
      border: 1px solid #ddd;
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

    .transactions {
      margin-top: 50px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      padding: 10px;
      border: 1px solid #aaa;
      text-align: left;
    }

    .toggle-btn {
      margin: 20px 0;
      padding: 10px 20px;
      background-color: #e190ba;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
  <a href="#">Dashboard</a>
  <a href="admin_manage_users.php">Manage Users</a>
  <a href="#">View Transactions</a>
  <a href="logout.php">Logout</a>
</div>

<div class="main" id="mainContent">
  <h2>🎭 View Costume Listings</h2>

  <div id="costumeCards">
  <?php while ($row = mysqli_fetch_assoc($costumes)) { ?>
    <div class="card">
      <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>">
      <h4><?php echo $row['title']; ?></h4>
      <p>₹<?php echo $row['price_per_day']; ?></p>
     

      <!-- Admin Delete Form -->
      <form method="POST" action="admin_delete_costume.php" onsubmit="return confirm('Delete this costume?')">
        <input type="hidden" name="costume_id" value="<?php echo $row['id']; ?>">
        <button class="delete-btn">Delete</button>
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
    const mainContent = document.getElementById('mainContent');

    hamburger.addEventListener('click', () => {
      sidebar.classList.add('open');
      hamburger.classList.add('hidden');
    });

    mainContent.addEventListener('click', () => {
      if (sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        hamburger.classList.remove('hidden');
      }
    });
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
  document.getElementById("mainContent").classList.toggle("shifted");
}

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
