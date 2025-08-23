<?php
session_start();
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

/* Sidebar */
.sidebar {
  width: 270px; 
  background-color: var(--charcoal); 
  color: var(--text-light); 
  padding: 25px; 
  height: 100vh;
  position: fixed; 
  top: 0; 
  left: -270px; 
  transition: 0.3s; 
  z-index: 1000; 
  overflow-y: auto; 
}
.sidebar.active { left: 0; }
.sidebar h3 { margin-bottom:20px; font-size: 1.4em; }
.sidebar a { color: var(--text-light); text-decoration:none; display:block; margin:15px 0; font-size: 0.95em; }

.sidebar .rental-requests { margin-top:30px; }
.sidebar .rental-requests h4 { margin-bottom:10px; }

/* Overlay */
.overlay { 
  position: fixed; 
  top: 0; left: 0; 
  width: 100%; height: 100%; 
  background: rgba(0,0,0,0.5); 
  display: none; 
  z-index: 999; 
}
.overlay.active { display: block; }

/* Main Content */
.main-content { 
  margin-left: 0; 
  transition: 0.3s; 
  padding: 30px 40px; 
  background-color: var(--pale-silver); 
  min-height: 100vh; 
}
.main-content.shifted { margin-left: 270px; }

/* Hamburger */
.hamburger { 
  font-size: 24px; 
  background: none; 
  border: none; 
  color: var(--charcoal); 
  cursor: pointer; 
  margin-bottom: 15px; 
}

/* Costumes Grid */
.costume-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); 
  gap: 20px; 
  align-items: stretch; 
}

.costume-card { 
  background: var(--lavender); 
  padding: 15px; 
  border-radius: 10px; 
  text-align: center; 
  box-shadow: 0 2px 8px rgba(0,0,0,0.15); 
  color: var(--charcoal); 
  height: 410px;                 /* Fixed medium height */
  display: flex;                 
  flex-direction: column;        
}

.costume-card .card-content {
  flex-grow: 1;                   /* Take all available space */
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.costume-card .button-container {
  margin-top: auto;               /* Push button to bottom */
}

.costume-card img { 
  width: 100%; 
  height: 150px; 
  object-fit: cover; 
  border-radius: 10px; 
  margin-bottom: 8px; 
}

.costume-card h4 { 
  font-size: 1em; 
  margin: 6px 0; 
  line-height: 1.2em; 
  max-height: 2.4em;             /* ✅ Restrict to 2 lines */
  overflow: hidden; 
  text-overflow: ellipsis; 
  display: -webkit-box; 
  -webkit-line-clamp: 2;         /* ✅ Allow max 2 lines */
  -webkit-box-orient: vertical; 
}

.button { 
  background: var(--charcoal); 
  color: var(--text-light); 
  padding: 8px 12px; 
  border-radius: 6px; 
  text-decoration: none; 
  display: inline-block; 
  margin-top: 6px; 
  font-size: 0.9em; 
}
.button.disabled { background: #aaa; cursor: not-allowed; }

.status { font-weight:bold; margin-top:5px; font-size:0.85em; }
.status.available { color: var(--green); }
.status.soon { color: var(--orange); }
.status.unavailable { color: var(--red); }

/* Rental requests inside sidebar */
.sidebar .costume-card { background: #2b2b2b; padding:10px; margin-bottom:10px; border-radius:8px; box-shadow:0 1px 5px rgba(0,0,0,0.2); height:auto; }
.sidebar .costume-card h5 { margin:5px 0; font-size:0.95em; color: var(--text-light); }
.sidebar .costume-card p { font-size:0.8em; color: #ddd; margin:2px 0; }
.sidebar .costume-card .button { padding:5px 10px; font-size:0.8em; margin-top:5px; }
</style>
</head>
<body>

<!-- Sidebar -->
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

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
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
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
  document.getElementById('mainContent').classList.toggle('shifted');
}
</script>

</body>
</html>
