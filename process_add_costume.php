<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lender_id   = $_POST['lender_id'];
  $title       = $_POST['title'];
  $category    = $_POST['category'];
  $size        = $_POST['size'];
  $quantity    = $_POST['quantity'];
  $price       = $_POST['price_per_day'];
  $description = $_POST['description'];

  // ✅ Image Upload Handling
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageName = $_FILES['image']['name'];
    $imageTmp  = $_FILES['image']['tmp_name'];
    $safeName  = time() . '_' . basename($imageName);
    $uploadDir = 'uploads/';
    $imagePath = $uploadDir . $safeName;

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true); // create uploads folder if missing
    }

    // This is the fix — use $imagePath, NOT $image
    move_uploaded_file($imageTmp, $imagePath);
  } else {
    echo "❌ Error uploading image.";
    exit();
  }

  // ✅ Save image *path* in 'image' column
  $stmt = $conn->prepare("INSERT INTO costumes (lender_id, title, category, size, quantity, price_per_day, description, image, availability) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')");
  $stmt->bind_param("isssidss", $lender_id, $title, $category, $size, $quantity, $price, $description, $imagePath);

  if ($stmt->execute()) {
    echo "<h3>🎉 Costume Uploaded Successfully!</h3>";
    echo "<a href='dashboard_lender.php'>Back to Dashboard</a>";
  } else {
    echo "❌ Error uploading costume: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
