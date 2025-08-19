<?php
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    header("Location: login.php");
    exit();
}

$slayrent_id = $_SESSION['slayrent_id'] ?? '';
$user_id     = $_SESSION['user_id'];
$shop_name   = $_SESSION['shop_name'] ?? 'Your Shop';

// Get join date
$query = $conn->prepare("SELECT created_at, name FROM lenders WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();
$created_at = $data['created_at'];
$lender_name = $data['name'];
$joined_days = floor((time() - strtotime($created_at)) / (60 * 60 * 24));

// Fetch costumes
$costumes = [];
$stmt = $conn->prepare("SELECT * FROM costumes WHERE lender_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $costumes[] = $row;
}

// Fetch rental requests
$requests = [];
$req = $conn->prepare("SELECT r.id, b.name AS borrower_name, c.title AS costume_title, r.status
                       FROM rental_requests r 
                       JOIN borrowers b ON r.borrower_id = b.id 
                       JOIN costumes c ON r.costume_id = c.id 
                       WHERE r.lender_id = ?");
$req->bind_param("i", $user_id);
$req->execute();
$reqRes = $req->get_result();
while ($row = $reqRes->fetch_assoc()) {
    $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lender Dashboard | SlayRent</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    :root {
      --lavender: #D1C2D9;
      --charcoal: #191919;
      --pale-silver: #ECECEC;
      --text-light: #ffffff;
    }
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: var(--lavender); color: var(--charcoal); }
    .sidebar { width: 270px; background-color: var(--charcoal); color: var(--text-light); padding: 25px; height: 100vh;
      position: fixed; top: 0; left: -270px; transition: 0.3s; z-index: 1000; overflow-y: auto; }
    .sidebar.active { left: 0; }
    .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
    .overlay.active { display: block; }
    .main-content { margin-left: 0; transition: 0.3s; padding: 30px; background-color: var(--pale-silver); min-height: 100vh; }
    .main-content.shifted { margin-left: 270px; }
    .hamburger { font-size: 24px; background: none; border: none; color: var(--charcoal); cursor: pointer; margin-bottom: 15px; }
    .card { background: var(--lavender); padding: 25px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 30px; color: var(--charcoal); }
    .costume-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
    .costume-card { background: var(--lavender); border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; text-align: center; }
    .costume-card img { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; }
    .button { background-color: var(--charcoal); color: var(--text-light); padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; margin: 5px; display: inline-block; }
    .button:hover { background-color: var(--text-light); color: var(--charcoal); }
    .request-box { background: #2c2c2c; padding: 12px; margin-bottom: 10px; border-radius: 6px; font-size: 14px; color: white; }
    .accept-btn { background: green; color: white; }
    .reject-btn { background: crimson; color: white; }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($shop_name) ?></h3>
  <ul>
    <li><a href="edit_profile_lender.php">✏️ Edit Profile</a></li>
    <li><a href="#">📅 Joined <?= $joined_days ?> days ago</a></li>
  </ul>

  <h3>📝 Rental Requests</h3>
<?php if (count($requests) === 0): ?>
  <p>No requests yet.</p>
<?php else: ?>
  <?php foreach ($requests as $r): ?>
    <div class="request-box" id="request-<?= $r['id'] ?>">
      <p><b><?= htmlspecialchars($r['borrower_name']) ?></b> wants <i><?= htmlspecialchars($r['costume_title']) ?></i></p>
      <p>Status: <span id="status-req-<?= $r['id'] ?>"><?= ucfirst($r['status']) ?></span></p>
      <?php if ($r['status'] === 'pending'): ?>
        <button class="accept-btn" onclick="updateRequestStatus(<?= $r['id'] ?>, 'accepted')">Accept</button>
        <button class="reject-btn" onclick="updateRequestStatus(<?= $r['id'] ?>, 'rejected')">Reject</button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

  <ul><li><a href="logout.php">🚪 Logout</a></li></ul>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="main-content" id="mainContent">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>
  <div class="welcome-section">
    <h2>Welcome, <?= htmlspecialchars($lender_name) ?> 👋</h2>
    <p>Manage your costumes here.</p>
  </div>

  <div class="card">
    <h3>Add a New Costume</h3>
    <a href="add_costume.php" class="button">Upload Costume</a>
  </div>

  <div class="card">
    <h3>My Costume Listings</h3>
    <div class="costume-grid">
      <?php if (count($costumes) === 0): ?>
        <p>No costumes uploaded yet.</p>
      <?php else: ?>
        <?php foreach ($costumes as $c): ?>
          <div class="costume-card" id="costume-<?= $c['id'] ?>">
            <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
            <h4><?= htmlspecialchars($c['title']) ?></h4>
            <p>₹<?= htmlspecialchars($c['price_per_day']) ?> | <?= htmlspecialchars($c['size']) ?></p>
            <p>Status: 
              <b id="status-<?= $c['id'] ?>" style="color:<?= $c['availability'] === 'available' ? 'lightgreen' : 'crimson' ?>">
                <?= $c['availability'] ?>
              </b>
            </p>
            <a href="edit_costume.php?id=<?= $c['id'] ?>" class="button">Edit</a>

            <!-- toggle button has unique id -->
            <button id="toggle-btn-<?= $c['id'] ?>" class="button"
              onclick="toggleStatus(<?= $c['id'] ?>, '<?= $c['availability'] === 'available' ? 'unavailable' : 'available' ?>')">
              <?= $c['availability'] === 'available' ? 'Mark Unavailable' : 'Mark Available' ?>
            </button>

            <a href="delete_costume.php?id=<?= $c['id'] ?>" class="button" onclick="return confirm('Are you sure you want to delete this costume?');">Delete</a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
  document.getElementById('mainContent').classList.toggle('shifted');
}

function toggleStatus(costumeId, newStatus) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "update_costume_status.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (this.responseText === "success") {
      let statusEl = document.getElementById("status-" + costumeId);
      let btn = document.getElementById("toggle-btn-" + costumeId);

      statusEl.innerText = newStatus;
      statusEl.style.color = (newStatus === "available") ? "lightgreen" : "crimson";

      if (newStatus === "available") {
        btn.innerText = "Mark Unavailable";
        btn.setAttribute("onclick", "toggleStatus("+costumeId+", 'unavailable')");
      } else {
        btn.innerText = "Mark Available";
        btn.setAttribute("onclick", "toggleStatus("+costumeId+", 'available')");
      }
    } else {
      alert("Update failed. Try again.");
    }
  };
  xhr.send("id="+costumeId+"&status="+newStatus);
}

function updateRequestStatus(requestId, newStatus) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "update_request_status.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (this.responseText === "success") {
      let statusEl = document.getElementById("status-req-" + requestId);
      statusEl.innerText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
      let box = document.getElementById("request-" + requestId);
      box.querySelectorAll("button").forEach(btn => btn.style.display = "none");
    } else {
      alert("❌ Failed to update request.");
    }
  };
  xhr.send("id=" + requestId + "&status=" + newStatus);
}
</script>
</body>
</html>
