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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - SlayRent</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    radial-gradient(circle at 80% 20%, rgba(138, 45, 59, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(100, 27, 46, 0.05) 0%, transparent 50%);
  pointer-events: none;
  z-index: -1;
  animation: float 20s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 0.6; }
}

/* Sidebar Styles */
.sidebar {
  width: 350px;
  background: linear-gradient(180deg, var(--dark-burgundy) 0%, var(--burgundy) 30%, var(--dark-burgundy) 70%, #4a1520 100%);
  color: var(--cream);
  height: 100vh;
  position: fixed;
  top: 0;
  left: -350px;
  transition: left 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  z-index: 1000;
  padding: 0;
  box-shadow: 4px 0 30px var(--shadow-dark);
  border-right: 4px solid var(--warm-red);
  backdrop-filter: blur(15px);
  overflow-y: auto;
}

.sidebar.active {
  left: 0;
}

.sidebar-header {
  padding: 40px 30px;
  text-align: center;
  border-bottom: 3px solid var(--warm-red);
  background: linear-gradient(135deg, rgba(251, 219, 147, 0.1), rgba(251, 219, 147, 0.05));
  position: relative;
}

.sidebar-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 50% 50%, rgba(251, 219, 147, 0.1) 0%, transparent 70%);
}

.sidebar h2 {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 700;
  color: var(--cream);
  text-shadow: 3px 3px 10px rgba(0,0,0,0.4);
  margin-bottom: 20px;
  letter-spacing: 2px;
  position: relative;
  z-index: 2;
}

.revenue-info {
  background: linear-gradient(135deg, var(--cream), rgba(251, 219, 147, 0.9));
  color: var(--dark-burgundy);
  padding: 25px 20px;
  border-radius: 20px;
  margin: 25px 0;
  text-align: center;
  box-shadow: 0 10px 30px var(--shadow);
  border: 3px solid var(--warm-red);
  position: relative;
  overflow: hidden;
}

.revenue-info::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(from 0deg, transparent, rgba(190, 91, 80, 0.1), transparent);
  animation: spin 15s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.revenue-info p {
  margin: 8px 0;
  font-size: 15px;
  font-weight: 500;
  position: relative;
  z-index: 2;
}

.revenue-amount {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--dark-burgundy);
  position: relative;
  z-index: 2;
}

.sidebar nav {
  padding: 25px 0;
}

.sidebar a {
  display: block;
  padding: 20px 30px;
  color: var(--cream);
  text-decoration: none;
  border-radius: 0;
  margin: 3px 0;
  font-weight: 500;
  font-size: 17px;
  transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  position: relative;
  border-left: 5px solid transparent;
  cursor: pointer;
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

.sidebar a::after {
  content: '';
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 8px solid var(--cream);
  border-top: 5px solid transparent;
  border-bottom: 5px solid transparent;
  opacity: 0;
  transition: all 0.3s ease;
}

.sidebar a:hover, .sidebar a.active {
  background: rgba(251, 219, 147, 0.15);
  color: var(--cream);
  border-left: 5px solid var(--warm-red);
  transform: translateX(12px);
  padding-left: 40px;
  box-shadow: inset 0 0 20px rgba(251, 219, 147, 0.1);
}

.sidebar a:hover::after, .sidebar a.active::after {
  opacity: 1;
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
  margin-left: 350px;
}

/* Enhanced Hamburger Menu */
.hamburger {
  font-size: 28px;
  cursor: pointer;
  margin: 25px;
  color: var(--dark-burgundy);
  background: linear-gradient(135deg, var(--cream), rgba(251, 219, 147, 0.9));
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 30px var(--shadow);
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  border: 4px solid var(--warm-red);
  position: relative;
  z-index: 999;
  overflow: hidden;
}

.hamburger::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(190, 91, 80, 0.1), transparent);
  transition: transform 0.4s ease;
}

.hamburger:hover {
  background: linear-gradient(135deg, var(--warm-red), var(--burgundy));
  color: var(--cream);
  transform: scale(1.15) rotate(180deg);
  box-shadow: 0 15px 40px var(--shadow-dark);
  border-color: var(--cream);
}

.hamburger:hover::before {
  transform: rotate(180deg);
}

/* Content Area */
#content {
  padding: 30px 40px 60px;
  position: relative;
}

/* Enhanced Dashboard Welcome */
.dashboard-welcome {
  text-align: center;
  padding: 80px 50px;
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.4) 50%, var(--white) 100%);
  border-radius: 30px;
  box-shadow: 0 20px 50px var(--shadow);
  border: 4px solid var(--warm-red);
  margin-bottom: 60px;
  position: relative;
  overflow: hidden;
}

.dashboard-welcome::before {
  content: '';
  position: absolute;
  top: -100%;
  left: -100%;
  width: 300%;
  height: 300%;
  background: 
    radial-gradient(circle at 30% 30%, rgba(251, 219, 147, 0.2) 0%, transparent 50%),
    radial-gradient(circle at 70% 70%, rgba(190, 91, 80, 0.1) 0%, transparent 50%);
  animation: rotate 25s linear infinite;
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.dashboard-welcome h1 {
  font-family: 'Playfair Display', serif;
  color: var(--dark-burgundy);
  font-size: 52px;
  font-weight: 700;
  margin-bottom: 25px;
  text-shadow: 3px 3px 10px rgba(0,0,0,0.15);
  position: relative;
  z-index: 2;
  letter-spacing: 1px;
}

.dashboard-welcome p {
  color: var(--burgundy);
  font-size: 22px;
  font-weight: 500;
  position: relative;
  z-index: 2;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Enhanced Card Grid - Random Placement */
.card-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-template-rows: repeat(10, 90px);
  gap: 25px;
  margin-top: 50px;
  position: relative;
  min-height: 900px;
}

.big-card {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.3) 50%, var(--white) 100%);
  border-radius: 30px;
  padding: 50px 40px;
  box-shadow: 0 20px 50px var(--shadow);
  text-align: center;
  border: 4px solid var(--warm-red);
  transition: all 0.5s cubic-bezier(0.4, 0.0, 0.2, 1);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 280px;
}

/* Creative Random Positioning */
.big-card[data-page="costumes"] {
  grid-column: 1 / 5;
  grid-row: 1 / 5;
  transform: rotate(-3deg);
  border-color: var(--warm-red);
}

.big-card[data-page="users"] {
  grid-column: 7 / 11;
  grid-row: 2 / 6;
  transform: rotate(2deg);
  border-color: var(--burgundy);
}

.big-card[data-page="transactions"] {
  grid-column: 3 / 7;
  grid-row: 6 / 10;
  transform: rotate(-1deg);
  border-color: var(--dark-burgundy);
}

.big-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 8px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy), var(--dark-burgundy), var(--warm-red));
  background-size: 200% 100%;
  animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
  0%, 100% { background-position: -200% 0; }
  50% { background-position: 200% 0; }
}

.big-card::after {
  content: '';
  position: absolute;
  top: 20px;
  right: 20px;
  width: 40px;
  height: 40px;
  background: var(--cream);
  border-radius: 50%;
  opacity: 0;
  transform: scale(0);
  transition: all 0.3s ease;
}

.big-card:hover {
  transform: translateY(-20px) scale(1.08) rotate(0deg) !important;
  box-shadow: 0 35px 80px var(--shadow-dark);
  border-color: var(--cream);
}

.big-card:hover::after {
  opacity: 0.3;
  transform: scale(1);
}

.big-card .icon {
  font-size: 72px;
  margin-bottom: 30px;
  color: var(--warm-red);
  filter: drop-shadow(3px 3px 6px rgba(0,0,0,0.2));
}

.big-card[data-page="costumes"] .icon { color: var(--warm-red); }
.big-card[data-page="users"] .icon { color: var(--burgundy); }
.big-card[data-page="transactions"] .icon { color: var(--dark-burgundy); }

.big-card h2 {
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  color: var(--dark-burgundy);
  font-size: 32px;
  margin-bottom: 20px;
  text-shadow: 2px 2px 6px rgba(0,0,0,0.1);
  letter-spacing: 1px;
}

.big-card p {
  color: var(--burgundy);
  font-size: 18px;
  margin-bottom: 30px;
  line-height: 1.7;
  max-width: 400px;
}

.big-card .cta {
  background: linear-gradient(135deg, var(--warm-red), var(--burgundy));
  border: none;
  color: var(--cream);
  padding: 20px 45px;
  border-radius: 35px;
  cursor: pointer;
  font-weight: 600;
  font-size: 18px;
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  border: 3px solid transparent;
  box-shadow: 0 10px 25px rgba(190, 91, 80, 0.4);
  position: relative;
  overflow: hidden;
}

.big-card .cta::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s ease;
}

.big-card:hover .cta {
  background: linear-gradient(135deg, var(--dark-burgundy), var(--warm-red));
  border: 3px solid var(--cream);
  transform: scale(1.15);
  box-shadow: 0 15px 35px var(--shadow-dark);
}

.big-card:hover .cta::before {
  left: 100%;
}

/* Content Headers */
.content-header {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  color: var(--dark-burgundy);
  font-size: 46px;
  margin-bottom: 40px;
  text-shadow: 3px 3px 10px rgba(0,0,0,0.1);
  text-align: center;
  position: relative;
}

.content-header::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 4px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy));
  border-radius: 2px;
}

.content-header .icon {
  margin-right: 15px;
  color: var(--warm-red);
}

/* Enhanced Management Cards */
.costume-grid, .user-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
  gap: 35px;
  margin-top: 40px;
}

.costume-card, .user-card {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.2) 100%);
  border-radius: 25px;
  padding: 40px;
  box-shadow: 0 15px 45px var(--shadow);
  text-align: center;
  border: 4px solid var(--warm-red);
  transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.costume-card::before, .user-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy), var(--dark-burgundy));
}

.costume-card:hover, .user-card:hover {
  transform: translateY(-15px) scale(1.02);
  box-shadow: 0 25px 60px var(--shadow-dark);
  border-color: var(--burgundy);
}

.costume-card img {
  border-radius: 20px;
  margin-bottom: 25px;
  border: 4px solid var(--warm-red);
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  object-fit: cover;
  transition: transform 0.3s ease;
}

.costume-card:hover img {
  transform: scale(1.05);
}

.costume-card h4, .user-card h4 {
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  color: var(--dark-burgundy);
  font-size: 26px;
  margin-bottom: 18px;
}

.costume-card .details, .user-card .details {
  color: var(--burgundy);
  font-size: 17px;
  line-height: 1.7;
  margin-bottom: 25px;
  text-align: left;
}

.price-highlight {
  color: var(--warm-red);
  font-weight: 600;
  font-size: 19px;
}

.revenue-highlight {
  color: var(--dark-burgundy);
  font-weight: 600;
  font-size: 19px;
}

/* Enhanced Buttons */
.delete-costume, .delete-user {
  background: linear-gradient(135deg, var(--burgundy), var(--dark-burgundy));
  color: var(--cream);
  border: none;
  padding: 18px 35px;
  border-radius: 30px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 16px;
  margin-top: 20px;
  box-shadow: 0 8px 20px rgba(138, 45, 59, 0.4);
  position: relative;
  overflow: hidden;
}

.delete-costume::before, .delete-user::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.4s ease;
}

.delete-costume:hover, .delete-user:hover {
  background: linear-gradient(135deg, var(--dark-burgundy), var(--warm-red));
  transform: scale(1.08);
  box-shadow: 0 12px 30px var(--shadow-dark);
}

.delete-costume:hover::before, .delete-user:hover::before {
  left: 100%;
}

/* Enhanced Tables */
.table-container {
  background: linear-gradient(135deg, var(--white) 0%, rgba(251, 219, 147, 0.15) 100%);
  border-radius: 25px;
  overflow: hidden;
  box-shadow: 0 20px 50px var(--shadow);
  border: 4px solid var(--warm-red);
  margin-top: 40px;
  position: relative;
}

.table-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--warm-red), var(--burgundy), var(--dark-burgundy), var(--warm-red));
  background-size: 200% 100%;
  animation: shimmer 4s ease-in-out infinite;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

th {
  background: linear-gradient(135deg, var(--dark-burgundy), var(--burgundy));
  color: var(--cream);
  padding: 25px 20px;
  text-align: left;
  font-family: 'Playfair Display', serif;
  font-weight: 600;
  font-size: 17px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  border: none;
  position: relative;
}

td {
  padding: 20px;
  color: var(--dark-burgundy);
  font-size: 16px;
  border-bottom: 2px solid rgba(190, 91, 80, 0.2);
  transition: all 0.2s ease;
}

tr:nth-child(even) {
  background: var(--light-cream);
}

tr:nth-child(odd) {
  background: rgba(255, 255, 255, 0.7);
}

tr:hover {
  background: rgba(190, 91, 80, 0.15);
  transform: scale(1.01);
}

.commission-highlight {
  color: var(--burgundy);
  font-weight: 600;
}

.status-cell {
  color: var(--warm-red);
  font-weight: 600;
  text-transform: capitalize;
}

/* Loading and Error States */
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--light-cream);
  border-top: 4px solid var(--warm-red);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.error-message, .empty-message {
  background: var(--cream);
  border-radius: 25px;
  padding: 40px;
  box-shadow: 0 15px 40px var(--shadow);
  border: 4px solid var(--warm-red);
  font-family: 'Playfair Display', serif;
  color: var(--dark-burgundy);
  text-align: center;
  font-size: 20px;
  margin: 30px 0;
}

/* Responsive Design */
@media (max-width: 1400px) {
  .card-grid {
    grid-template-columns: repeat(8, 1fr);
    grid-template-rows: repeat(12, 80px);
  }
  
  .big-card[data-page="costumes"] {
    grid-column: 1 / 4;
    grid-row: 1 / 5;
  }
  
  .big-card[data-page="users"] {
    grid-column: 5 / 8;
    grid-row: 2 / 6;
  }
  
  .big-card[data-page="transactions"] {
    grid-column: 2 / 5;
    grid-row: 7 / 11;
  }
}

@media (max-width: 1024px) {
  .sidebar {
    width: 320px;
    left: -320px;
  }
  
  .main.shift {
    margin-left: 320px;
  }
  
  .card-grid {
    display: flex;
    flex-direction: column;
    gap: 25px;
    min-height: auto;
  }
  
  .big-card {
    transform: none !important;
    min-height: 250px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    width: 300px;
    left: -300px;
  }
  
  .main.shift {
    margin-left: 300px;
  }
  
  .costume-grid, .user-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-welcome h1 {
    font-size: 40px;
  }
  
  .content-header {
    font-size: 36px;
  }
  
  #content {
    padding: 20px 25px;
  }
}

@media (max-width: 480px) {
  .main.shift {
    margin-left: 0;
  }
  
  .sidebar {
    width: 100%;
    left: -100%;
  }
  
  .dashboard-welcome {
    padding: 50px 25px;
  }
  
  .big-card {
    padding: 40px 25px;
    min-height: 220px;
  }
  
  .costume-card, .user-card {
    padding: 30px 20px;
  }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2>SlayRent Admin</h2>
    
    <div class="revenue-info">
      <p><strong>Welcome, Admin</strong></p>
      <p>Platform Commission Revenue:</p>
      <div class="revenue-amount">₹<?= number_format($totalRevenue, 2) ?></div>
    </div>
  </div>
  
  <nav>
    <a href="#" onclick="loadPage('dashboard')" class="active" id="nav-dashboard">🏠 Dashboard</a>
    <a href="#" onclick="loadPage('costumes')" id="nav-costumes">👗 Manage Costumes</a>
    <a href="#" onclick="loadPage('users')" id="nav-users">👥 Manage Users</a>
    <a href="#" onclick="loadPage('orders')" id="nav-orders">📋 Orders</a>
    <a href="#" onclick="loadPage('transactions')" id="nav-transactions">💰 Transactions</a>
    <a href="generate_report.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</div>

<!-- Main -->
<div class="main" id="main">
  <span class="hamburger" id="hamburger">☰</span>

  <div id="content">
    <!-- Dashboard content will be loaded here -->
  </div>
</div>

<script>
// Ensure DOM is fully loaded
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

  // Load dashboard on page load
  loadPage('dashboard');
});

// Navigation function with enhanced loading
function loadPage(page) {
  // Show loading spinner
  $('#content').html(`
    <div class="loading-spinner">
      <div class="spinner"></div>
    </div>
  `);
  
  // Update active nav
  document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
  const navElement = document.getElementById('nav-' + page);
  if (navElement) {
    navElement.classList.add('active');
  }
  
  // Load content based on page
  switch(page) {
    case 'dashboard':
      loadDashboard();
      break;
    case 'costumes':
      loadCostumes();
      break;
    case 'users':
      loadUsers();
      break;
    case 'orders':
      loadOrders();
      break;
    case 'transactions':
      loadTransactions();
      break;
    default:
      $('#content').html('<div class="error-message">Page not found.</div>');
  }
}

// Dashboard content
function loadDashboard() {
  const dashboardHTML = `
    <div class="dashboard-welcome">
      <h2>SlayRent Admin Dashboard</h2>
      <p>Welcome to your dashboard.</p>
    </div>
    
    <div class="card-grid">
      <div class="big-card" data-page="costumes">
        <div class="icon">👗</div>
        <h2>Manage Costumes</h2>
        <p>View, edit, and manage all costume listings across the platform. Monitor inventory and pricing.</p>
        <button class="cta">Explore Costumes</button>
      </div>
      
      <div class="big-card" data-page="users">
        <div class="icon">👥</div>
        <h2>Manage Users</h2>
        <p>Oversee lenders and borrowers.</p>
        <button class="cta">View</button>
      </div>
      
      <div class="big-card" data-page="transactions">
        <div class="icon">💰</div>
        <h2>Transactions</h2>
        <p>Track all platform transactions, commission earnings, and financial insights.</p>
        <button class="cta">View</button>
      </div>
    </div>
  `;
  
  setTimeout(() => {
    $('#content').html(dashboardHTML);
  }, 500);
}

// Load costumes section
function loadCostumes() {
  // This would typically make an AJAX call to fetch costume data
  // For now, we'll simulate with a timeout
  setTimeout(() => {
    const costumesHTML = `
      <h2 class="content-header">
        <span class="icon">👗</span>
        Manage Costumes
      </h2>
      
      <div class="costume-grid">
        <!-- Costume cards would be loaded here via AJAX -->
        <div class="costume-card">
          <img src="sample-costume.jpg" width="150" height="150" alt="Costume">
          <h4>Sample Costume</h4>
          <div class="details">
            <p><strong>Shop:</strong> Sample Shop</p>
            <p class="price-highlight">Price/day: ₹500</p>
          </div>
          <button class="delete-costume" data-id="1">Delete Costume</button>
        </div>
        <!-- More costume cards... -->
      </div>
      
      <div class="empty-message">
        <p>Connect to your database to load actual costume data. This is a demo interface.</p>
      </div>
    `;
    
    $('#content').html(costumesHTML);
  }, 500);
}

// Load users section
function loadUsers() {
  setTimeout(() => {
    const usersHTML = `
      <h2 class="content-header">
        <span class="icon">👥</span>
        Manage Users
      </h2>
      
      <div class="user-grid">
        <!-- User cards would be loaded here via AJAX -->
        <div class="user-card">
          <h4>Sample Lender</h4>
          <div class="details">
            <p><strong>Shop:</strong> Fashion Hub</p>
            <p><strong>Contact:</strong> lender@example.com</p>
            <p><strong>Phone:</strong> +91 98765 43210</p>
          </div>
          <button class="delete-user" data-id="1" data-type="lender">Delete User</button>
        </div>
        <!-- More user cards... -->
      </div>
      
      <div class="empty-message">
        <p>Connect to your database to load actual user data. This is a demo interface.</p>
      </div>
    `;
    
    $('#content').html(usersHTML);
  }, 500);
}

// Load orders section
function loadOrders() {
  setTimeout(() => {
    const ordersHTML = `
      <h2 class="content-header">
        <span class="icon">📋</span>
        Orders Management
      </h2>
      
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Costume</th>
              <th>Shop</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Total Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>001</td>
              <td>Wedding Saree</td>
              <td>Elegant Rentals</td>
              <td>2024-01-15</td>
              <td>2024-01-18</td>
              <td class="price-highlight">₹1,500</td>
              <td class="status-cell">Completed</td>
            </tr>
            <tr>
              <td>002</td>
              <td>Party Dress</td>
              <td>Glamour Hub</td>
              <td>2024-01-20</td>
              <td>2024-01-22</td>
              <td class="price-highlight">₹800</td>
              <td class="status-cell">Active</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="empty-message">
        <p>Connect to your database to load actual order data. Sample data shown above.</p>
      </div>
    `;
    
    $('#content').html(ordersHTML);
  }, 500);
}

// Load transactions section
function loadTransactions() {
  setTimeout(() => {
    const transactionsHTML = `
      <h2 class="content-header">
        <span class="icon">💰</span>
        Transaction History
      </h2>
      
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Costume</th>
              <th>Shop</th>
              <th>Amount</th>
              <th>Commission (10%)</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>TXN001</td>
              <td>Wedding Saree</td>
              <td>Elegant Rentals</td>
              <td class="price-highlight">₹1,500</td>
              <td class="commission-highlight">₹150</td>
              <td class="status-cell">Paid</td>
              <td>2024-01-18</td>
            </tr>
            <tr>
              <td>TXN002</td>
              <td>Party Dress</td>
              <td>Glamour Hub</td>
              <td class="price-highlight">₹800</td>
              <td class="commission-highlight">₹80</td>
              <td class="status-cell">Paid</td>
              <td>2024-01-22</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="empty-message">
        <p>Connect to your database to load actual transaction data. Sample data shown above.</p>
      </div>
    `;
    
    $('#content').html(transactionsHTML);
  }, 500);
}

// Big card click handlers
$(document).on('click', '.big-card', function() {
  const page = $(this).data('page');
  if (page) {
    loadPage(page);
  }
});

// Delete handlers (for demo purposes)
$(document).on('click', '.delete-costume, .delete-user', function(e) {
  e.preventDefault();
  const id = $(this).data('id');
  const type = $(this).hasClass('delete-costume') ? 'costume' : 'user';
  
  if (confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`)) {
    const card = $(this).closest(type === 'costume' ? '.costume-card' : '.user-card');
    card.fadeOut(300, function() {
      $(this).remove();
    });
    alert(`✅ ${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully!`);
  }
});
</script>

</body>
</html>
