<?php
session_start();
include 'includes/config.php';

// Fetch available costumes
$stmt = $conn->prepare("SELECT * FROM costumes WHERE status = 'available'");
$stmt->execute();
$result = $stmt->get_result();

// Check login type
$showLoginMessage = !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Costumes | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff4fa;
      margin: 0;
      padding: 40px 20px;
    }

    h2 {
      color: #e190ba;
      text-align: center;
      font-size: 32px;
      margin-bottom: 20px;
    }

    .login-message {
      background-color: #ffe0ef;
      color: #c0467f;
      border: 1px solid #f7c2db;
      text-align: center;
      padding: 12px 20px;
      border-radius: 8px;
      max-width: 600px;
      margin: 0 auto 30px;
      font-size: 16px;
    }

    .costume-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: auto;
    }

    .costume-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.08);
      overflow: hidden;
      text-align: center;
      transition: transform 0.3s;
      padding: 16px;
    }

    .costume-card:hover {
      transform: scale(1.03);
    }

    .costume-card img {
      width: 100%;
      height: 230px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 12px;
    }

    .costume-card h4 {
      margin: 8px 0 4px;
      color: #c46d9e;
      font-size: 20px;
    }

    .costume-card p {
      margin: 3px 0;
      color: #555;
      font-size: 14px;
    }

    .btn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 18px;
      background: #e190ba;
      color: white;
      text-decoration: none;
      border-radius: 20px;
      font-size: 14px;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #c46d9e;
    }

    .no-costumes {
      text-align: center;
      color: #999;
      font-size: 18px;
      margin-top: 50px;
    }
  </style>
</head>
<body>

<h2>👗 Explore Our Costumes</h2>

<?php if ($showLoginMessage): ?>
  <div class="login-message">
    Please <a href="login.php" style="color:#a7336e; text-decoration: underline;">login as a borrower</a> or register to view full costume details and start renting.
  </div>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
  <div class="costume-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="costume-card">
        <img src="uploads/<?= htmlspecialchars($row['image_path']) ?>" alt="Costume Image">
        <h4><?= htmlspecialchars($row['title']) ?></h4>
        <p>₹<?= $row['price_per_day'] ?>/day</p>
        <p>Size: <?= htmlspecialchars($row['size']) ?> | <?= htmlspecialchars($row['category']) ?></p>

        <?php if (!$showLoginMessage): ?>
          <a href="costume_detail.php?id=<?= $row['id'] ?>" class="btn">See More</a>
        <?php else: ?>
          <a href="login.php" class="btn">Login to Rent</a>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <p class="no-costumes">No costumes are currently available for rent. Please check back soon!</p>
<?php endif; ?>

</body>
</html>
