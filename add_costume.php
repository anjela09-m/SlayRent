<?php
session_start();
include 'includes/config.php';

// Redirect if not logged in as lender
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
  header("Location: login.php");
  exit();
}

$lender_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Upload Costume | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      background-color: #fdf5fa;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
    }
    .form-container {
      max-width: 500px;
      margin: 60px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      color: #e190ba;
      margin-bottom: 25px;
    }
    input[type="text"], input[type="number"], select, textarea, input[type="file"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }
    input[readonly] {
      background-color: #f0f0f0;
      color: #666;
    }
    textarea {
      resize: vertical;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #e190ba;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #c46d9e;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>👗 Upload Costume</h2>
  <form action="process_add_costume.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="lender_id" value="<?= $lender_id ?>">

    <input type="text" name="title" placeholder="Costume Title" required>

    <select name="category" required>
      <option value="">Select Category</option>
      <option value="Onam">Onam</option>
      <option value="Christmas">Christmas</option>
      <option value="Cultural Fest">Cultural Fest</option>
      <option value="Halloween">Halloween</option>
    </select>

    <input type="text" name="size" value="Free Size" readonly>

    <input type="number" name="quantity" placeholder="Quantity" min="1" required>

    <input type="number" name="price_per_day" placeholder="Price per Day (₹)" min="100" required>

    <textarea name="description" placeholder="Costume Description" rows="4" required></textarea>

    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Upload Costume</button>
  </form>
</div>

</body>
</html>
