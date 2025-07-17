<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id          = $_POST['id'];
  $title       = $_POST['title'];
  $category    = $_POST['category'];
  $size        = $_POST['size'];
  $quantity    = $_POST['quantity'];
  $price       = $_POST['price_per_day'];
  $description = $_POST['description'];

  $image_sql = '';
  $params    = [$title, $category, $size, $quantity, $price, $description];
  $types     = "sssids";

  // ✅ Optional image upload
  if (!empty($_FILES['image']['name'])) {
    $imageName = $_FILES['image']['name'];
    $imageTmp  = $_FILES['image']['tmp_name'];
    $safeName  = time() . '_' . basename($imageName);
    $uploadDir = 'uploads/';
    $imagePath = $uploadDir . $safeName;

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    move_uploaded_file($imageTmp, $imagePath);

    $image_sql = ", image_path = ?";
    $params[]  = $imagePath;
    $types    .= "s";
  }

  $params[] = $id;
  $types   .= "i";

  $sql = "UPDATE costumes 
          SET title = ?, category = ?, size = ?, quantity = ?, price_per_day = ?, description = ?" 
          . $image_sql . " 
          WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);

  if ($stmt->execute()) {
    header("Location: view_costumes.php?updated=1");
    exit();
  } else {
    header("Location: edit_costume.php?id=$id&error=1");
    exit();
  }
}
?>
