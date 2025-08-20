<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - SlayRent</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
:root {
  --lavender: #D1C2D9;
  --charcoal: #191919;
  --pale-silver: #ECECEC;
  --text-light: #ffffff;
  --green: #4caf50;
  --orange: #ff9800;
}

* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { margin:0; background: var(--pale-silver); color: var(--charcoal); overflow-x: hidden; }

/* Sidebar */
.sidebar {
  position: fixed; top:0; left:-270px;
  width:270px; height:100vh; background: var(--charcoal); color: var(--text-light);
  padding: 25px; transition: left 0.3s ease; z-index:1000; overflow-y:auto;
  display:flex; flex-direction: column;
}
.sidebar.active { left:0; }
.sidebar h3 { margin-bottom:20px; font-size:1.4em; }
.sidebar a {
  color: var(--text-light); text-decoration:none; display:flex; align-items:center;
  margin:10px 0; font-size:0.95em; padding:8px 12px; border-radius:6px; transition:0.2s;
}
.sidebar a span { margin-left:10px; }
.sidebar a:hover { background: var(--lavender); color: var(--charcoal); }

/* Overlay */
.overlay { 
  position: fixed; top:0; left:0; width:100%; height:100%; 
  background: rgba(0,0,0,0.5); display: none; z-index: 999; 
}
.overlay.active { display:block; }

/* Main Content */
.main { margin-left:0; transition: 0.3s; padding: 30px 40px; min-height:100vh; }
.main.shifted { margin-left: 270px; }

/* Hamburger */
.hamburger { 
  font-size: 24px; background: none; border: none; 
  cursor:pointer; position: fixed; top:20px; left:20px; z-index:1100;
  display:flex; flex-direction:column; gap:5px;
}
.hamburger div { width:30px; height:4px; background: var(--charcoal); border-radius:2px; }

/* Costume Cards */
.card { 
  background: var(--lavender); padding:15px; border-radius:10px; 
  text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.15); color: var(--charcoal); 
  display:inline-block; width:220px; margin:10px; vertical-align:top;
  transition: transform 0.2s;
}
.card:hover { transform: translateY(-5px); }
.card img { width:100%; height:180px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
.card h4 { margin:10px 0; }
.delete-btn {
  background: var(--charcoal); color: var(--lavender); border:none;
  padding:8px 14px; border-radius:6px; font-size:14px; cursor:pointer;
  transition:0.2s;
}
.delete-btn:hover { background: var(--lavender); color: var(--charcoal); }

/* Lender Orders Table */
table { width:100%; border-collapse: collapse; margin-top:30px; }
th, td { border:1px solid #ccc; padding:10px; text-align:left; }
th { background: var(--lavender); }
.status-pending { color: var(--orange); font-weight:bold; }
.status-accepted { color: var(--green); font-weight:bold; }
</style>
</head>
<body>

<!-- Hamburger -->
<button class="hamburger" id="hamburger">
  <div></div><div></div><div></div>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h3>⚙️ Admin Panel</h3>
  <a href="#" class="nav-link" data-page="dashboard">📊 <span>Dashboard</span></a>
  <a href="#" class="nav-link" data-page="orders">📦 <span>Orders</span></a>
  <a href="#" class="nav-link" data-page="transactions">💰 <span>Transactions</span></a>
  <a href="logout.php">🚪 <span>Logout</span></a>
</div>

<div class="overlay" id="overlay"></div>

<!-- Main Content -->
<div class="main" id="main">
  <!-- Content will be loaded here via AJAX -->
</div>

<script>
// Hamburger toggle
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main');
const overlay = document.getElementById('overlay');

hamburger.addEventListener('click', () => {
  sidebar.classList.add('active');
  overlay.classList.add('active');
  main.classList.add('shifted');
  document.body.style.overflow = 'hidden';
});
overlay.addEventListener('click', () => {
  sidebar.classList.remove('active');
  overlay.classList.remove('active');
  main.classList.remove('shifted');
  document.body.style.overflow = 'auto';
});

// AJAX navigation
const links = document.querySelectorAll('.nav-link');

function loadPage(page) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'admin_dashboard_ajax.php?page=' + page, true);
    xhr.onload = function() {
        if (this.status === 200) {
            main.innerHTML = this.responseText;
        } else {
            main.innerHTML = '<p>Error loading page.</p>';
        }
    };
    xhr.send();
}

// Load default page (dashboard) on page load
loadPage('dashboard');

// Add click events
links.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.dataset.page;
        loadPage(page);
    });
});
</script>

</body>
</html>
