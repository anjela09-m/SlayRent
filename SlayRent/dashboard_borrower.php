<?php
include 'includes/config.php'; 

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Borrower';
$joined_days = 0;

// Calculate joined days
$stmt = $conn->prepare("SELECT created_at FROM borrowers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $joined_days = floor((time() - strtotime($row['created_at'])) / (60 * 60 * 24));
}

// Costume filtering
$where = "1"; // Show all
$params = [];
$types = "";

if (!empty($_GET['keyword'])) {
  $where .= " AND (title LIKE ? OR description LIKE ?)";
  $kw = '%' . $_GET['keyword'] . '%';
  $params[] = $kw; $params[] = $kw;
  $types .= "ss";
}
if (!empty($_GET['category'])) {
  $where .= " AND category = ?";
  $params[] = $_GET['category'];
  $types .= "s";
}
if (!empty($_GET['min_price'])) {
  $where .= " AND price_per_day >= ?";
  $params[] = $_GET['min_price'];
  $types .= "i";
}
if (!empty($_GET['max_price'])) {
  $where .= " AND price_per_day <= ?";
  $params[] = $_GET['max_price'];
  $types .= "i";
}

// Fetch costumes
$sql = "SELECT * FROM costumes WHERE $where ORDER BY id DESC";
$costumes = [];
$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $costumes[] = $row;
}

// Fetch borrower's rental requests
$requests = [];
$qstmt = $conn->prepare("
  SELECT rr.id, rr.status, rr.request_date, c.title AS costume_title
  FROM rental_requests rr
  JOIN costumes c ON rr.costume_id = c.id
  WHERE rr.borrower_id = ?
  ORDER BY rr.request_date DESC
");
$qstmt->bind_param("i", $user_id);
$qstmt->execute();
$qres = $qstmt->get_result();
while ($row = $qres->fetch_assoc()) {
  $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrower Dashboard | SlayRent</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender: #D1C2D9;
      --charcoal: #191919;
      --pale-silver: #ECECEC;
      --text-light: #ffffff;
      --red: #ff4d4d;
      --green: #4caf50;
      --orange: #ff9800;
    }
    * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { margin:0; background: var(--lavender); color: var(--charcoal); }
    .sidebar { position:fixed; top:0; left:0; width:240px; height:100vh; background:var(--charcoal); color:var(--text-light); padding:30px 20px; }
    .sidebar h3 { margin-bottom:20px; }
    .sidebar a { color:var(--text-light); text-decoration:none; display:block; margin:15px 0; }
    .main { margin-left:260px; padding:30px 40px 40px 40px; background: var(--pale-silver); min-height:100vh; }
    .costume-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap:20px; }
    .costume-card { background: var(--lavender); padding:15px; border-radius:10px; text-align:center; box-shadow:0 0 8px rgba(0,0,0,0.1); color:var(--charcoal); }
    .costume-card img { width:100%; height:180px; object-fit:cover; border-radius:10px; }
    .button { background: var(--charcoal); color: var(--text-light); padding:8px 16px; border-radius:6px; text-decoration:none; display:inline-block; margin-top:10px; }
    .button.disabled { background: #aaa; cursor:not-allowed; }
    .status { font-weight:bold; margin-top:5px; }
    .status.available { color: var(--green); }
    .status.soon { color: var(--orange); }
    .status.unavailable { color: var(--red); }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3><?= htmlspecialchars($name) ?></h3>
    <a href="edit_borrower_profile.php">✏️ Edit Profile</a>
    <a href="#">📅 Joined <?= $joined_days ?> days ago</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main">
    <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>

    <h3>🎭 Costumes</h3>
    <div class="costume-grid">
      <?php if(empty($costumes)): ?>
        <p style="grid-column:1/-1;">No costumes found!</p>
      <?php else: foreach($costumes as $c): 
        $status = "";
        if ($c['quantity'] >= 2) $status = "available";
        elseif ($c['quantity'] == 1) $status = "soon";
        else $status = "unavailable";
      ?>
        <div class="costume-card">
          <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= $c['price_per_day'] ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
          <p>Quantity: <?= $c['quantity'] ?></p>
          <p class="status <?= $status ?>">
            <?php if($status=="available"): ?>Available
            <?php elseif($status=="soon"): ?>Soon to be out of stock
            <?php else: ?>Unavailable<?php endif; ?>
          </p>
          <?php if($c['quantity'] > 0): ?>
            <a href="rent_costume.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
          <?php else: ?>
            <span class="button disabled">Rent Now</span>
          <?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <h3>📬 My Rental Requests</h3>
    <?php if(empty($requests)): ?>
      <p>No rental requests yet.</p>
    <?php else: foreach($requests as $req): ?>
      <div class="costume-card">
        <h4><?= htmlspecialchars($req['costume_title']) ?></h4>
        <p>Requested on <?= date('d M Y', strtotime($req['request_date'])) ?></p>
        <p class="status <?= strtolower($req['status']) ?>"><?= $req['status'] ?></p>
        <?php if($req['status']=='Accepted'): ?>
          <a href="fake_payment.php?request_id=<?= $req['id'] ?>" class="button">Proceed to Payment</a>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>

  </div>
</body>
</html>
