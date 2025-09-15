<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
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
$stmt->close();

// Fetch costumes
$costumes = [];
$sql = "SELECT * FROM costumes ORDER BY id DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) $costumes[] = $row;

// Fetch borrower's rental requests with latest payment
$requests = [];
$qstmt = $conn->prepare("
    SELECT rr.id, rr.status, rr.request_date, rr.quantity, rr.total_price, 
           c.title AS costume_title,
           p.status AS payment_status
    FROM rental_requests rr
    JOIN costumes c ON rr.costume_id = c.id
    LEFT JOIN (
        SELECT p1.rental_request_id, p1.status
        FROM payments p1
        INNER JOIN (
            SELECT rental_request_id, MAX(id) AS max_id
            FROM payments
            GROUP BY rental_request_id
        ) p2 ON p1.rental_request_id = p2.rental_request_id AND p1.id = p2.max_id
    ) p ON rr.id = p.rental_request_id
    WHERE rr.borrower_id = ?
    ORDER BY 
        CASE rr.status
            WHEN 'pending' THEN 1
            WHEN 'accepted' THEN 2
            WHEN 'paid' THEN 3
            WHEN 'dispatched' THEN 4
            WHEN 'delivered' THEN 5
            WHEN 'return_requested' THEN 6
            WHEN 'returned' THEN 7
            WHEN 'completed' THEN 8
            WHEN 'rejected' THEN 9
            ELSE 99
        END, rr.request_date DESC
");
$qstmt->bind_param("i", $user_id);
$qstmt->execute();
$qres = $qstmt->get_result();
while ($row = $qres->fetch_assoc()) $requests[] = $row;
$qstmt->close();

$keyId = 'rzp_test_RDRydETJkRioj4';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Borrower Dashboard | SlayRent</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
/* --- Styles with Fiery Pink Palette --- */
:root {
  --primary: #F0386B;
  --accent: #FF5376;
  --light: #F8C0C8;   /* text color */
  --soft: #E2C290;    /* background area */
  --dark: #6B2D5C;    /* card background */
  --text-light: #F8C0C8;
  --green: #4caf50;
  --red: #ff4d4d;
  --orange: #ff9800;
  --blue: #007bff;
}

* {
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  margin: 0;
  background: var(--soft);   /* background area = E2C290 */
  color: var(--light);       /* text = F8C0C8 */
  overflow-x: hidden;
}

/* Sidebar */
.sidebar {
  width: 320px;
  background-color: var(--dark);
  color: var(--text-light);
  padding: 25px;
  height: 100vh;
  position: fixed;
  top: 0;
  left: -320px;
  transition: 0.28s;
  z-index: 1000;
  overflow-y: auto;
}
.sidebar.active {
  left: 0;
}
.sidebar h3 {
  margin-bottom: 20px;
  font-size: 1.4em;
}
.sidebar a {
  color: var(--text-light);
  text-decoration: none;
  display: block;
  margin: 12px 0;
  font-size: 0.95em;
}
.sidebar .rental-requests {
  margin-top: 20px;
}
.sidebar .rental-requests h4 {
  margin-bottom: 10px;
}
.sidebar .costume-card {
  background: var(--accent);
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 8px;
  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
}
.sidebar .costume-card h5 {
  margin: 5px 0;
  font-size: 0.95em;
  color: var(--text-light);
}
.sidebar .costume-card p {
  font-size: 0.82em;
  color: var(--light);
  margin: 3px 0;
}
.sidebar .costume-card .button {
  padding: 6px 10px;
  font-size: 0.84em;
  margin-top: 6px;
  display: block;
  text-align: center;
  background: var(--primary);
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
}
.sidebar .costume-card .button.disabled {
  background: #aaa;
  cursor: not-allowed;
}
.sidebar .completed-log {
  background: var(--soft);
  color: var(--dark);
  padding: 6px 10px;
  margin: 6px 0;
  border-radius: 6px;
  font-size: 0.82em;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.sidebar .completed-log span.status {
  font-weight: bold;
  color: var(--green);
}

/* Overlay */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.45);
  display: none;
  z-index: 999;
}
.overlay.active {
  display: block;
}

/* Main content */
.main-content {
  margin-left: 0;
  transition: 0.28s;
  padding: 30px 40px;
  background-color: var(--soft); /* bg = E2C290 */
  min-height: 100vh;
  color: var(--light);           /* text = F8C0C8 */
}
.main-content.shifted {
  margin-left: 320px;
}

/* Hamburger */
.hamburger {
  font-size: 24px;
  background: none;
  border: none;
  color: var(--light);
  cursor: pointer;
  margin-bottom: 15px;
}

/* Costume grid + cards */
.costume-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
  align-items: stretch;
}
.costume-card {
  background: var(--dark);   /* cards = 6B2D5C */
  padding: 15px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  color: var(--light);      /* text = F8C0C8 */
  height: 410px;
  display: flex;
  flex-direction: column;
}
.costume-card .card-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}
.costume-card .button-container {
  margin-top: auto;
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
  max-height: 2.4em;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Buttons */
.button {
  background: var(--primary);
  color: #fff;
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
  display: inline-block;
  margin-top: 6px;
  font-size: 0.9em;
  cursor: pointer;
  border: none;
}
.button.disabled {
  background: #aaa;
  cursor: not-allowed;
}

/* Status colors */
.status {
  font-weight: bold;
  margin-top: 5px;
  font-size: 0.85em;
}
.status.pending {
  color: var(--orange);
}
.status.accepted {
  color: var(--green);
}
.status.rejected {
  color: var(--red);
}
.status.paid {
  color: var(--blue);
}
.status.dispatched {
  color: var(--accent);
}
.status.delivered {
  color: #2ecc71;
}
.status.return_requested {
  color: #e67e22;
}
.status.returned {
  color: #d35400;
}
.status.completed {
  color: var(--green);
}
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

    <?php
    $activeRequests = [];
    $completedRequests = [];

    foreach($requests as $req){
        // ensure status is lower-case string to match CSS classes
        $req['status'] = $req['status'] ?? 'pending'; // FIX: default if NULL
        $req['status'] = strtolower($req['status']);
        $req['payment_status'] = strtolower($req['payment_status'] ?? '');
        if($req['status'] === 'completed'){
            $completedRequests[] = $req;
        } else {
            $activeRequests[] = $req;
        }
    }

    if(empty($activeRequests)){
        echo "<p>No active rental requests.</p>";
    }

    foreach($activeRequests as $req):
        $status = htmlspecialchars($req['status']);
        $paymentStatus = htmlspecialchars($req['payment_status'] ?? '');
        // NOTE: IDs used below: rental-<id> and status-<id> (consistent with JS)
    ?>
        <div class="costume-card" id="rental-<?= $req['id'] ?>">
            <h5><?= htmlspecialchars($req['costume_title']) ?></h5>
            <p>Status: <span class="status <?= $status ?>" id="status-<?= $req['id'] ?>"><?= ucfirst($status) ?></span></p>
            <p>Qty: <?= intval($req['quantity']) ?> | ₹<?= number_format($req['total_price'],2) ?></p>

            <?php if($status === 'accepted' && $paymentStatus !== 'paid'): ?>
                <button class="button" onclick="payNow(<?= $req['id'] ?>, <?= $req['total_price'] ?>, '<?= addslashes($req['costume_title']) ?>')">💰 Pay Now</button>

            <?php elseif($status === 'paid'): ?>
                <span class="button disabled">✅ Paid (Waiting Dispatch)</span>

            <?php elseif($status === 'dispatched'): ?>
                <button class="button" onclick="updateStatus(<?= $req['id'] ?>,'delivered')">📦 Confirm Delivery</button>

           <?php elseif($status === 'delivered'): ?>
    <button class="button" onclick="updateStatus(<?= $req['id'] ?>,'returned')">🔄 Return Item</button>
    
<?php elseif($status === 'returned'): ?>
    <span class="button disabled">📦 Returned (Lender Confirmed)</span>


            <?php elseif($status === 'rejected'): ?>
                <span class="button disabled">❌ Rejected</span>

            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if(!empty($completedRequests)): ?>
        <h4 style="margin-top:12px;">✔ Completed Requests</h4>
        <?php foreach($completedRequests as $req): ?>
            <div class="completed-log" id="completed-<?= $req['id'] ?>">
                <span><?= htmlspecialchars($req['costume_title']) ?></span>
                <span class="status completed">Completed</span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <a href="logout.php">🚪 Logout</a>
</div>

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
      $statusQty = ($c['quantity'] >= 2) ? "available" : (($c['quantity']==1)?"soon":"unavailable");
    ?>
      <div class="costume-card">
        <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
        <div class="card-content">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= htmlspecialchars($c['price_per_day']) ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
          <p>Quantity: <?= intval($c['quantity']) ?></p>
          <p class="status <?= $statusQty ?>">
            <?= $statusQty=="available"?"Available":($statusQty=="soon"?"Soon":"Unavailable") ?>
          </p>
        </div>
        <div class="button-container">
          <?php if($c['quantity']>0): ?>
            <a href="rent_costume.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
          <?php else: ?>
            <span class="button disabled">Rent Now</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
  document.getElementById('mainContent').classList.toggle('shifted');
}

/*
  updateStatus:
  - POSTs to update_request_status.php which must return JSON:
    { success: true, new_status: "dispatched" }
  - On failure return { success: false, error: "..." }
*/
function updateStatus(rentalId, newStatus){
    $.post('update_request_status.php',{id:rentalId,status:newStatus},function(res){
        if(!res || !res.success){ 
            alert(res && res.error ? res.error : "Unknown error"); 
            return; 
        }

        var ns = res.new_status;
        // Update the status text + class
        $('#status-'+rentalId)
            .text(ns.charAt(0).toUpperCase() + ns.slice(1))
            .attr('class','status ' + ns);

        var card = $('#rental-'+rentalId);
        // remove any existing buttons
        card.find('.button, .button.disabled').remove();

        // Recreate UI according to new status
        if(ns === 'paid'){
            card.append('<span class="button disabled">✅ Paid (Waiting Dispatch)</span>');
        }
        else if(ns === 'dispatched'){
            card.append('<button class="button" onclick="updateStatus('+rentalId+',\'delivered\')">📦 Confirm Delivery</button>');
        }
        else if(ns === 'delivered'){
            card.append('<button class="button" onclick="updateStatus('+rentalId+',\'returned\')">🔄 Return Item</button>');
        }
        else if(ns === 'returned'){
            card.append('<span class="button disabled">⏳ Waiting for Lender Confirmation</span>');
        }
        else if(ns === 'completed'){
            card.replaceWith(
                '<div class="completed-log" id="completed-'+rentalId+'">' +
                    '<span>'+$('#status-'+rentalId).text()+'</span>' +
                    '<span class="status completed">Completed</span>' +
                '</div>'
            );
        }
        else if(ns === 'rejected'){
            card.append('<span class="button disabled">❌ Rejected</span>');
        }

    },'json').fail(function(xhr){
        alert("❌ Server error: " + xhr.responseText);
    });
}

/*
  payNow:
  - Calls create_order.php (POST JSON { request_id })
    expected JSON response: { order_id: "...", amount: 12300 }  // amount in paise
  - Razorpay handler calls verify_payment.php (POST) with payment details and request_id
    verify_payment.php should validate & create payment row, and update rental_requests.status -> 'paid'
    verify_payment.php expected response: { success:true, new_status:"paid", amount: 390.00 }
*/
function payNow(requestId, amount, title) {
    // call create_order.php to get order id + amount (in paise)
    fetch('create_order.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({request_id: requestId})
    })
    .then(res => res.json())
    .then(data => {
        if(!data || data.error){ alert(data ? data.error : 'Create order failed'); return; }

        const options = {
            "key": "<?php echo $keyId ?>",
            "amount": data.amount, // in paise from server
            "currency": "INR",
            "name": "SlayRent",
            "description": title,
            "order_id": data.order_id,
            "handler": function (response){
                // Send verified payment to backend
                $.post('verify_payment.php', {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    request_id: requestId
                }, function(res){
                    if(!res){ alert("Invalid server response"); return; }
                    if(res.success){
                        alert(res.message || "Payment recorded");

                        // FIX: use correct IDs (status-<id> and rental-<id>)
                        $('#status-'+requestId).text(res.new_status.charAt(0).toUpperCase()+res.new_status.slice(1)).attr('class','status '+res.new_status);
                        var card = $('#rental-'+requestId);
                        card.find('.button, .button.disabled').remove();
                        // Display Paid state
                        if(res.new_status === 'paid'){
                            card.append('<span class="button disabled">✅ Paid (Waiting Dispatch)</span>');
                        }

                        // Optionally update revenue UI if you have one (example id "totalRevenue")
                        if(res.amount){
                            var revenueEl = $("#totalRevenue");
                            if(revenueEl.length){
                                let current = parseFloat(revenueEl.data('value') || 0);
                                current += parseFloat(res.amount);
                                revenueEl.data('value', current);
                                revenueEl.text("💰 Total Revenue: ₹" + current.toFixed(2));
                            }
                        }
                    } else {
                        alert("⚠ " + (res.message || "Payment verification failed"));
                    }
                }, "json").fail(function(xhr){ alert("❌ Server error: "+xhr.responseText); });
            },
            "prefill": {
                "name": "<?php echo addslashes($_SESSION['name'] ?? '') ?>",
                "email": "<?php echo addslashes($_SESSION['email'] ?? '') ?>"
            },
            "theme": { "color": "#e190ba" }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    }).catch(err => {
        alert("Network error contacting create_order.php");
        console.error(err);
    });
}

</script>
</body>
</html>
