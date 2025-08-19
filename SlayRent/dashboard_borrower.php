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

// Fetch costumes (main content)
$costumes = [];
$sql = "SELECT * FROM costumes ORDER BY id DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) $costumes[] = $row;

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
while ($row = $qres->fetch_assoc()) $requests[] = $row;
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
body { margin:0; background: var(--lavender); color: var(--charcoal); overflow-x: hidden; }

.sidebar {
  position: fixed; top:0; left:-260px; width:260px; height:100vh; background: var(--charcoal); color: var(--text-light);
  padding: 30px 20px; transition: left 0.3s ease; z-index:1000; overflow-y: auto;
}
.sidebar.active { left:0; }
.sidebar h3 { margin-bottom:20px; font-size: 1.4em; }
.sidebar a { color: var(--text-light); text-decoration:none; display:block; margin:15px 0; font-size: 0.95em; }
.sidebar .rental-requests { margin-top:30px; }
.sidebar .rental-requests h4 { margin-bottom:10px; }

.main { margin-left:0; padding:30px 40px; background: var(--pale-silver); min-height:100vh; transition: margin-left 0.3s ease; }
.main.shifted { margin-left:260px; }

.hamburger { position: fixed; top:20px; left:20px; z-index:1100; cursor:pointer; display:flex; flex-direction:column; gap:5px; }
.hamburger div { width:30px; height:4px; background: var(--charcoal); border-radius:2px; }

.overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.3); display:none; z-index:900; }
.overlay.active { display:block; }

.costume-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap:20px; }
.costume-card { background: var(--lavender); padding:15px; border-radius:10px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.15); color: var(--charcoal); }
.costume-card img { width:100%; height:180px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
.button { background: var(--charcoal); color: var(--text-light); padding:8px 16px; border-radius:6px; text-decoration:none; display:inline-block; margin-top:10px; font-size:0.9em; }
.button.disabled { background: #aaa; cursor:not-allowed; }
.status { font-weight:bold; margin-top:5px; font-size:0.85em; }
.status.available { color: var(--green); }
.status.soon { color: var(--orange); }
.status.unavailable { color: var(--red); }

/* Rental requests inside sidebar */
.sidebar .costume-card { background: #2b2b2b; padding:10px; margin-bottom:10px; border-radius:8px; box-shadow:0 1px 5px rgba(0,0,0,0.2); }
.sidebar .costume-card h5 { margin:5px 0; font-size:0.95em; color: var(--text-light); }
.sidebar .costume-card p { font-size:0.8em; color: #ddd; margin:2px 0; }
.sidebar .costume-card .button { padding:5px 10px; font-size:0.8em; margin-top:5px; }
</style>
</head>
<body>

<div class="hamburger" id="hamburger">
  <div></div>
  <div></div>
  <div></div>
</div>

<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($name) ?></h3>
  <a href="edit_borrower_profile.php">✏️ Edit Profile</a>
  <a href="#">📅 Joined <?= $joined_days ?> days ago</a>

  <div class="rental-requests">
    <h4>📬 My Rental Requests</h4>
    <?php if(empty($requests)): ?>
      <p>No rental requests yet.</p>
    <?php else: foreach($requests as $req): ?>
      <div class="costume-card">
        <h5><?= htmlspecialchars($req['costume_title']) ?></h5>
        <p>Requested: <?= date('d M Y', strtotime($req['request_date'])) ?></p>
        <p class="status <?= strtolower($req['status']) ?>"><?= ucfirst($req['status']) ?></p>
        <?php if(strtolower($req['status'])=='accepted'): ?>
          <a href="fake_payment.php?request_id=<?= $req['id'] ?>" class="button">Proceed to Payment</a>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <a href="logout.php">🚪 Logout</a>
</div>

<div class="overlay" id="overlay"></div>

<div class="main" id="main">
  <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
  <h3>🎭 Costumes</h3>
  <div class="costume-grid">
    <?php if(empty($costumes)): ?>
      <p style="grid-column:1/-1;">No costumes found!</p>
    <?php else: foreach($costumes as $c):
      $status = ($c['quantity'] >= 2) ? "available" : (($c['quantity']==1)?"soon":"unavailable");
    ?>
      <div class="costume-card">
        <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
        <h4><?= htmlspecialchars($c['title']) ?></h4>
        <p>₹<?= $c['price_per_day'] ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
        <p>Quantity: <?= $c['quantity'] ?></p>
        <p class="status <?= $status ?>">
          <?= $status=="available"?"Available":($status=="soon"?"Soon to be out of stock":"Unavailable") ?>
        </p>
        <?php if($c['quantity']>0): ?>
          <a href="rent_costume.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
        <?php else: ?>
          <span class="button disabled">Rent Now</span>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<script>
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main');
const overlay = document.getElementById('overlay');

hamburger.addEventListener('click', () => {
  sidebar.classList.add('active');
  overlay.classList.add('active');
  main.classList.add('shifted');
  document.body.style.overflow = 'hidden'; // prevent main scrolling
});

overlay.addEventListener('click', () => {
  sidebar.classList.remove('active');
  overlay.classList.remove('active');
  main.classList.remove('shifted');
  document.body.style.overflow = 'auto'; // restore scrolling
});
</script>

</body>
</html>
