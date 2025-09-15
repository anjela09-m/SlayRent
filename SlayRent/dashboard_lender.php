<?php
session_start();
include 'includes/config.php';

// Ensure only lender can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    header("Location: login.php");
    exit();
}

$slayrent_id = $_SESSION['slayrent_id'] ?? '';
$user_id     = $_SESSION['user_id'];
$shop_name   = $_SESSION['shop_name'] ?? 'Your Shop';

// Get join date + lender name
$query = $conn->prepare("SELECT created_at, name FROM lenders WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();
$created_at = $data['created_at'] ?? date("Y-m-d");
$lender_name = $data['name'] ?? "Lender";
$joined_days = floor((time() - strtotime($created_at)) / (60 * 60 * 24));

// Fetch costumes
$costumes = [];
$stmt = $conn->prepare("SELECT * FROM costumes WHERE lender_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $costumes[] = $row;

// Fetch rental requests
$requests = [];
$req = $conn->prepare("
    SELECT r.id, b.name AS borrower_name, c.title AS costume_title, r.status, r.total_price
    FROM rental_requests r 
    JOIN borrowers b ON r.borrower_id = b.id 
    JOIN costumes c ON r.costume_id = c.id 
    WHERE r.lender_id = ?
    ORDER BY r.request_date DESC
");
$req->bind_param("i", $user_id);
$req->execute();
$reqRes = $req->get_result();
while ($row = $reqRes->fetch_assoc()) $requests[] = $row;

// Calculate total revenue
$totalRevenue = 0;
foreach($requests as $r) {
    if(in_array(strtolower($r['status']), ['paid', 'completed'])) {
        $totalRevenue += $r['total_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lender Dashboard | SlayRent</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
:root {
  --main-color: #e190ba;
  --main-dark: #c17297;
  --charcoal: #191919;
  --pale-silver: #ECECEC;
  --text-light: #ffffff;
  --green: #4caf50;
  --orange: #ff9800;
  --red: #ff4d4d;
  --blue: #007bff;
  --lavender:#f5e7f0;
}
* { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { margin:0; background: var(--lavender); color: var(--charcoal); }

/* Sidebar */
.sidebar {
  width: 270px; background-color: var(--lavender); padding: 25px;
  height: 100vh; position: fixed; top: 0; left: -270px; transition: 0.3s;
  z-index: 1000; overflow-y: auto;
}
.sidebar.active { left: 0; }
.sidebar h3 { margin-bottom: 20px; font-size: 1.4em; }
.sidebar a { display: block; background-color: var(--main-dark); color: var(--text-light); text-decoration:none; padding: 8px 12px; border-radius:6px; margin:6px 0; transition:0.3s; }
.sidebar a:hover { background-color: var(--main-color); }
.overlay { position: fixed; top: 0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); display:none; z-index:999; }
.overlay.active { display:block; }
.main-content { margin-left:0; transition:0.3s; padding:30px; min-height:100vh; }
.main-content.shifted { margin-left:270px; }
.hamburger { font-size:24px; background:none; border:none; color: var(--charcoal); cursor:pointer; margin-bottom:15px; }

/* Cards */
.card { background: var(--text-light); padding:25px; border-radius:12px; box-shadow:0 0 10px rgba(0,0,0,0.1); margin-bottom:30px; }
.costume-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:20px; }
.costume-card { background: var(--text-light); border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:15px; text-align:center; display:flex; flex-direction:column; }
.costume-card img { width:100%; height:180px; object-fit:cover; border-radius:10px; margin-bottom:8px; }
.button { background-color: var(--charcoal); color: var(--text-light); padding:8px 14px; border:none; border-radius:6px; cursor:pointer; margin:5px; display:inline-block; }
.button:hover { background-color: var(--main-dark); }
.button.disabled { background:#555; cursor:default; }

/* Requests */
.request-box { background: var(--charcoal); padding:12px; margin-bottom:10px; border-radius:6px; font-size:14px; color:white; }
.request-box span.status { font-weight:bold; }
.status.pending { color: var(--orange); }
.status.accepted { color: var(--green); }
.status.paid { color: var(--blue); }
.status.dispatched { color: #8e44ad; }
.status.delivered { color: #2ecc71; }
.status.return_requested { color: #e67e22; }
.status.returned { color: #d35400; }
.status.completed { color: #27ae60; }
.status.rejected { color: var(--red); }

/* Completed section */
.completed-requests { margin-top:20px; }
.completed-log { background: #fff0f5; padding:10px; margin-bottom:6px; border-radius:8px; font-size:13px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 1px 4px rgba(0,0,0,0.1); }
.completed-log .status.completed { font-weight:bold; color: var(--green); }

/* Modal */
.modal { display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; }
.modal-content { background: var(--text-light); padding:20px; border-radius:12px; text-align:center; width:320px; color: var(--charcoal); position:relative; }
.modal-content h3 { margin-top:0; }
.close { position:absolute; top:10px; right:20px; font-size:22px; cursor:pointer; }
#confirmDeleteBtn { background: crimson; color:white; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($shop_name) ?></h3>
  <a href="edit_profile_lender.php">✏️ Edit Profile</a>
  <a href="#">📅 Joined <?= $joined_days ?> days ago</a>
  <a href="#" id="totalRevenue">💰 Total Revenue: ₹<?= number_format($totalRevenue,2) ?></a>

  <h3>📝 Rental Requests</h3>
  <div id="rentalRequestsContainer">
  <?php if(empty($requests)): ?>
    <p>No requests.</p>
  <?php else: foreach($requests as $r): 
        $status = strtolower(trim($r['status']));
        if($status==='completed') continue; // Skip completed in main loop
  ?>
   <div class="request-box" id="request-<?= $r['id'] ?>">
      <p><b><?= htmlspecialchars($r['borrower_name']) ?></b> requested <i><?= htmlspecialchars($r['costume_title']) ?></i></p>
      <p>Status: <span id="status-req-<?= $r['id'] ?>" class="status <?= $status ?>"><?= ucfirst($status) ?></span></p>

      <?php if($status === 'pending'): ?>
        <button class="button" onclick="updateRequestStatus(<?= $r['id'] ?>,'accepted')">Accept</button>
        <button class="button" onclick="updateRequestStatus(<?= $r['id'] ?>,'rejected')">Reject</button>

      <?php elseif($status === 'accepted'): ?>
        <span class="button disabled">⏳ Waiting for Payment</span>

      <?php elseif($status === 'paid'): ?>
        <button class="button" onclick="updateRequestStatus(<?= $r['id'] ?>,'dispatched')">🚚 Dispatch</button>

      <?php elseif($status === 'dispatched'): ?>
        <span class="button disabled">📦 Dispatched</span>

      <?php elseif($status === 'delivered'): ?>
        <span class="button disabled">⏳ Waiting for Borrower Return</span>

      <?php elseif($status === 'returned'): ?>
        <button class="button" onclick="updateRequestStatus(<?= $r['id'] ?>,'completed')">✅ Confirm Return</button>

      <?php elseif($status === 'rejected'): ?>
        <span class="button disabled">Rejected</span>
      <?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
  </div>

  <!-- Completed Rentals Section -->
  <h3>✔ Completed Rentals</h3>
  <div class="completed-requests" id="completedRequestsContainer">
    <?php foreach($requests as $r): if(strtolower($r['status'])==='completed'): ?>
      <div class="completed-log" id="completed-<?= $r['id'] ?>">
        <span><?= htmlspecialchars($r['borrower_name']) ?>: <?= htmlspecialchars($r['costume_title']) ?></span>
        <span class="status completed">Completed</span>
      </div>
    <?php endif; endforeach; ?>
  </div>

  <a href="logout.php">🚪 Logout</a>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="main-content" id="mainContent">
  <button class="hamburger" onclick="toggleSidebar()">☰</button>

  <div class="card">
    <h2>Welcome, <?= htmlspecialchars($lender_name) ?> 👋</h2>
    <p>Manage your costumes and rentals!</p>
  </div>

  <div class="card">
    <h3>Add a New Costume</h3>
    <a href="add_costume.php" class="button">Upload Costume</a>
  </div>

  <div class="card">
    <h3>My Costume Listings</h3>
    <div class="costume-grid">
      <?php if(empty($costumes)): ?>
        <p>No costumes uploaded yet.</p>
      <?php else: foreach($costumes as $c): ?>
        <div class="costume-card" id="costume-<?= $c['id'] ?>">
          <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= htmlspecialchars($c['price_per_day']) ?> | <?= htmlspecialchars($c['size']) ?></p>
          <?php if($c['quantity']<=0): ?>
            <p style="color:red;"><b>Out of Stock</b></p>
          <?php elseif($c['quantity']<=2): ?>
            <p style="color:orange;"><b>Low Stock (<?= $c['quantity'] ?> left)</b></p>
          <?php else: ?>
            <p style="color:green;"><b>In Stock (<?= $c['quantity'] ?> available)</b></p>
          <?php endif; ?>
          <a href="edit_costume.php?id=<?= $c['id'] ?>" class="button">Edit</a>
          <button class="button" onclick="openDeleteModal(<?= $c['id'] ?>,'<?= htmlspecialchars($c['title']) ?>')">Delete</button>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteModal()">&times;</span>
    <h3>Delete Costume</h3>
    <p>Are you sure you want to delete <b id="costumeTitle"></b>?</p>
    <div style="margin-top:15px;">
      <button class="button" id="confirmDeleteBtn">Yes, Delete</button>
      <button class="button" onclick="closeDeleteModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
  document.getElementById('mainContent').classList.toggle('shifted');
}

// AJAX status update
function updateRequestStatus(requestId, newStatus) {
    $.post("update_request_status.php", { id: requestId, status: newStatus }, function(response) {
        try {
            if (response.success) {
                let statusEl = $("#status-req-" + requestId);
                let box = $("#request-" + requestId);

                statusEl.text(response.new_status.charAt(0).toUpperCase() + response.new_status.slice(1));
                statusEl.attr("class", "status " + response.new_status.toLowerCase());
                box.find("button, span.button").remove();

                switch(response.new_status){
                    case "accepted":
                        box.append('<span class="button disabled">⏳ Waiting for Payment</span>');
                        break;
                    case "paid":
                        box.append('<button class="button" onclick="updateRequestStatus('+requestId+',\'dispatched\')">🚚 Dispatch</button>');
                        break;
                    case "dispatched":
                        box.append('<span class="button disabled">📦 Dispatched</span>');
                        break;
                    case "delivered":
                        box.append('<span class="button disabled">⏳ Waiting for Borrower Return</span>');
                        break;
                    case "returned":
                        box.append('<button class="button" onclick="updateRequestStatus('+requestId+',\'completed\')">✅ Confirm Return</button>');
                        break;
                    case "completed":
                        var completedContainer = $('#completedRequestsContainer');
                        var borrowerName = $("#request-"+requestId+" p:first b").text();
                        var costumeTitle = $("#request-"+requestId+" p:first i").text();
                        var cardHtml = '<div class="completed-log" id="completed-'+requestId+'"><span>'+borrowerName+': '+costumeTitle+'</span><span class="status completed">Completed</span></div>';
                        completedContainer.append(cardHtml);
                        box.remove();
                        break;
                    case "rejected":
                        box.fadeOut(300, function(){ $(this).remove(); });
                        break;
                }

                if(response.new_status === 'paid') {
                    let revenueEl = $("#totalRevenue");
                    let current = parseFloat(revenueEl.text().replace(/[^\d.]/g,'')) || 0;
                    revenueEl.text("💰 Total Revenue: ₹" + (current + parseFloat(response.amount || 0)).toFixed(2));
                }

            } else {
                alert("⚠ " + response.error);
            }
        } catch(e) {
            alert("❌ Server error: " + JSON.stringify(response));
        }
    }, "json");
}

// Delete modal
let deleteCostumeId = null;
function openDeleteModal(id,title) {
  deleteCostumeId = id;
  document.getElementById("costumeTitle").innerText = title;
  document.getElementById("deleteModal").style.display = "flex";
}
function closeDeleteModal() {
  document.getElementById("deleteModal").style.display = "none";
  deleteCostumeId = null;
}
$("#confirmDeleteBtn").click(function() {
  if(!deleteCostumeId) return;
  $.post("delete_costume.php", { id: deleteCostumeId }, function(res) {
      if(res.trim() === "success") {
          $("#costume-" + deleteCostumeId).remove();
          closeDeleteModal();
      } else alert("❌ Failed to delete costume.");
  });
});
</script>
</body>
</html>
