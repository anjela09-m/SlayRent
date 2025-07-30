// fetch_costumes.php
<?php
session_start();
require 'includes/config.php';

$all = isset($_GET['all']) && $_GET['all'] == 1;
$query = $all ? "SELECT * FROM costumes" : "SELECT * FROM costumes LIMIT 10";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
<?php
  echo '
    <div class="card">
      <img src="' . $row['image'] . '" alt="' . $row['title'] . '">
      <h4>' . $row['title'] . '</h4>
      <p>₹' . $row['price_per_day'] . '</p>
    
    </div>
  ';
}
?>
