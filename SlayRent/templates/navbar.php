<nav class="main-nav">
    <div class="nav-brand">
        <a href="index.php">
            <img src="assets/images/logo.png" alt="SlayRent Logo" width="120">
        </a>
    </div>
    <nav>
  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="#about">About Us</a></li>
    <li><a href="#how-it-works">How to Rent</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="register_type.php">Register</a></li>
  </ul>
</nav>


    
    <ul class="nav-links">
        <li><a href="browse.php">Browse Costumes</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register_type.php" class="btn btn-primary">Sign Up</a></li>
        <?php endif; ?>
    </ul>
    
    <div class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </div>
</nav>
