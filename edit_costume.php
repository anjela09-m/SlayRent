<?php
session_start();
include 'includes/config.php';

// Check login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
  header("Location: login.php");
  exit();
}

$lender_id = $_SESSION['user_id'];
$costume_id = $_GET['id'] ?? 0;

// Fetch costume details
$stmt = $conn->prepare("SELECT * FROM costumes WHERE id = ? AND lender_id = ?");
$stmt->bind_param("ii", $costume_id, $lender_id);
$stmt->execute();
$result = $stmt->get_result();
$costume = $result->fetch_assoc();

if (!$costume) {
  echo "Costume not found or unauthorized access.";
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Costume | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="form-container">
  <h2>✏️ Edit Costume</h2>
  <form action="process_edit_costume.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $costume['id'] ?>">

    <input type="text" name="title" value="<?= htmlspecialchars($costume['title']) ?>" required><br>

    <select name="category" required>
      <option value="">Select Category</option>
      <?php foreach (['Onam', 'Christmas', 'Cultural Fest', 'Halloween'] as $cat): ?>
        <option value="<?= $cat ?>" <?= $costume['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
      <?php endforeach; ?>
    </select><br>

    <input type="text" name="size" value="<?= htmlspecialchars($costume['size']) ?>" required><br>

    <input type="number" name="quantity" value="<?= $costume['quantity'] ?>" min="1" required><br>

    <input type="number" step="0.01" name="price_per_day" value="<?= $costume['price_per_day'] ?>" required><br>

    <textarea name="description" rows="4" required><?= htmlspecialchars($costume['description']) ?></textarea><br>

    <label>Change Image (optional):</label>
    <input type="file" name="image"><br>

    <button type="submit">Update Costume</button>
  </form>
</div>

</body>
</html>
