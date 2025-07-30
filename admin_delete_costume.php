<?php
session_start();
require 'includes/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['costume_id'])) {
  $costumeId = intval($_POST['costume_id']);

  // First, delete the image file (optional but clean)
  $imgQuery = "SELECT image FROM costumes WHERE id = $costumeId";
  $imgResult = mysqli_query($conn, $imgQuery);
  if ($imgRow = mysqli_fetch_assoc($imgResult)) {
    $imagePath = 'uploads/' . $imgRow['image'];
    if (file_exists($imagePath)) {
      unlink($imagePath); // delete the file
    }
  }

  // Delete the costume from DB
  $deleteQuery = "DELETE FROM costumes WHERE id = $costumeId";
  if (mysqli_query($conn, $deleteQuery)) {
    header("Location: admin_dashboard.php"); // or wherever your listings are
    exit();
  } else {
    echo "Error deleting costume: " . mysqli_error($conn);
  }
}
?>
