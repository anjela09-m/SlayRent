<?php
session_start();
require 'includes/config.php';

if(!isset($_SESSION['admin'])){
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$action = strtolower(trim($action));
header('X-Content-Type-Options: nosniff');

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/* ---------- DASHBOARD (three big cards returned as html fragment) ---------- */
if($action === 'dashboard'){
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:10px">';
    echo '<div class="big-card" data-page="costumes"><h2>Manage Costumes</h2><p>Open the costumes panel</p><button class="cta">Open</button></div>';
    echo '<div class="big-card" data-page="users"><h2>Manage Users</h2><p>Open the users panel</p><button class="cta">Open</button></div>';
    echo '<div class="big-card" data-page="transactions"><h2>Transactions</h2><p>Open transactions</p><button class="cta">Open</button></div>';
    echo '</div>';
    exit;
}

/* ---------- COSTUMES (cards) ---------- */
if($action === 'costumes'){
    $sql = "SELECT c.*, l.shop_name, COALESCE(c.name, c.title, '') AS costume_name 
            FROM costumes c 
            LEFT JOIN lenders l ON c.lender_id = l.id
            ORDER BY c.id DESC";
    $res = $conn->query($sql);
    echo '<h3 style="margin:0 0 12px 0">Manage Costumes</h3>';
    if(!$res){
        echo '<div class="table-wrap">Database error: '.esc($conn->error).'</div>';
        exit;
    }
    if($res->num_rows === 0){
        echo '<div class="table-wrap"><p style="padding:14px">No costumes found.</p></div>';
        exit;
    }
    echo '<div class="cards-grid" style="margin-top:10px">';
    while($r = $res->fetch_assoc()){
        $img = (!empty($r['image']) && file_exists($r['image'])) ? $r['image'] : 'assets/no-image.png';
        $name = $r['costume_name'] ?: ('#'.$r['id']);
        $price = isset($r['price_per_day']) ? '₹'.number_format($r['price_per_day'],2).' / day' : '₹'.($r['price'] ?? '0').' / day';
        echo '<div class="card">';
        if(@getimagesize($img)){
            echo '<img src="'.esc($img).'" alt="'.esc($name).'">';
        }
        echo '<h4>'.esc($name).'</h4>';
        echo '<small>Shop: '.esc($r['shop_name'] ?? 'N/A').'</small><br>';
        echo '<small>'.$price.'</small><br>';
        echo '<button class="btn btn-danger delete-costume" style="margin-top:10px" data-id="'.intval($r['id']).'">Delete</button>';
        echo '</div>';
    }
    echo '</div>';
    exit;
}

/* ---------- USERS (lenders and borrowers as cards) ---------- */
if($action === 'users'){
    // Lenders
    $sqlL = "SELECT l.*, 
                COUNT(DISTINCT c.id) AS total_costumes,
                COUNT(DISTINCT rr.id) AS total_orders,
                IFNULL(SUM(p.amount),0) AS total_revenue
             FROM lenders l
             LEFT JOIN costumes c ON c.lender_id = l.id
             LEFT JOIN rental_requests rr ON rr.lender_id = l.id
             LEFT JOIN payments p ON p.rental_request_id = rr.id
             GROUP BY l.id
             ORDER BY l.id DESC";
    $resL = $conn->query($sqlL);

    // Borrowers
    $sqlB = "SELECT b.*, 
                COUNT(rr.id) AS total_orders,
                IFNULL(SUM(p.amount),0) AS total_spent
             FROM borrowers b
             LEFT JOIN rental_requests rr ON rr.borrower_id = b.id
             LEFT JOIN payments p ON p.rental_request_id = rr.id
             GROUP BY b.id
             ORDER BY b.id DESC";
    $resB = $conn->query($sqlB);

    echo '<h3 style="margin:0 0 12px 0">Lenders</h3>';
    if(!$resL){ echo '<div class="table-wrap">Error: '.esc($conn->error).'</div>'; }
    else if($resL->num_rows === 0){ echo '<div class="table-wrap"><p style="padding:14px">No lenders found.</p></div>'; }
    else{
        echo '<div class="cards-grid">';
        while($r = $resL->fetch_assoc()){
            echo '<div class="card">';
            echo '<h4>'.esc($r['shop_name'] ?? 'Shop #'.$r['id']).'</h4>';
            echo '<small>Owner: '.esc($r['owner_name'] ?? $r['owner'] ?? 'N/A').'</small><br>';
            echo '<small>Email: '.esc($r['email'] ?? 'N/A').'</small><br>';
            echo '<small>Costumes: '.intval($r['total_costumes']).' &nbsp; Orders: '.intval($r['total_orders']).'</small><br>';
            echo '<small>Revenue: ₹'.number_format($r['total_revenue'],2).'</small><br>';
            echo '<button class="btn btn-danger delete-user" data-id="'.intval($r['id']).'" data-type="lender">Delete</button>';
            echo '</div>';
        }
        echo '</div>';
    }

    echo '<hr style="margin:18px 0">';

    echo '<h3 style="margin:0 0 12px 0">Borrowers</h3>';
    if(!$resB){ echo '<div class="table-wrap">Error: '.esc($conn->error).'</div>'; }
    else if($resB->num_rows === 0){ echo '<div class="table-wrap"><p style="padding:14px">No borrowers found.</p></div>'; }
    else{
        echo '<div class="cards-grid">';
        while($r = $resB->fetch_assoc()){
            echo '<div class="card">';
            echo '<h4>'.esc($r['name'] ?? 'User #'.$r['id']).'</h4>';
            echo '<small>Email: '.esc($r['email'] ?? 'N/A').'</small><br>';
            echo '<small>Orders: '.intval($r['total_orders']).'</small><br>';
            echo '<small>Spent: ₹'.number_format($r['total_spent'],2).'</small><br>';
            echo '<button class="btn btn-danger delete-user" data-id="'.intval($r['id']).'" data-type="borrower">Delete</button>';
            echo '</div>';
        }
        echo '</div>';
    }
    exit;
}

/* ---------- ORDERS (table) ---------- */
if($action === 'orders'){
    $sql = "SELECT rr.*, 
                   COALESCE(c.name,c.title,'') AS costume_name, 
                   c.price_per_day,
                   l.shop_name, 
                   b.name AS borrower_name,
                   DATEDIFF(rr.end_date, rr.start_date) AS rental_days
            FROM rental_requests rr
            LEFT JOIN costumes c ON rr.costume_id = c.id
            LEFT JOIN lenders l ON rr.lender_id = l.id
            LEFT JOIN borrowers b ON rr.borrower_id = b.id
            ORDER BY rr.id DESC";
    $res = $conn->query($sql);
    echo '<h3 style="margin:0 0 12px 0">Orders</h3>';
    if(!$res){ echo '<div class="table-wrap">Error: '.esc($conn->error).'</div>'; exit; }
    echo '<div class="table-wrap" style="margin-top:10px"><table><thead><tr>
          <th>Order ID</th><th>Costume</th><th>Shop</th><th>Borrower</th><th>Period</th><th>Days</th><th>Total</th><th>Commission (10%)</th><th>Status</th>
          </tr></thead><tbody>';
    if($res->num_rows === 0){ echo '<tr><td colspan="9" style="padding:14px">No orders found.</td></tr>'; }
    while($r = $res->fetch_assoc()){
        $days = intval($r['rental_days'] ?? 0);
        // prefer total_price if present, else compute from price_per_day
        $total = isset($r['total_price']) && $r['total_price'] !== null ? floatval($r['total_price']) : (floatval($r['price_per_day'] ?? 0) * max(1,$days));
        $commission = $total * 0.10;
        $period = (isset($r['start_date']) && isset($r['end_date'])) ? (date('M j, Y', strtotime($r['start_date'])) . ' - ' . date('M j, Y', strtotime($r['end_date']))) : 'N/A';
        echo '<tr>';
        echo '<td>#'.intval($r['id']).'</td>';
        echo '<td>'.esc($r['costume_name']).'</td>';
        echo '<td>'.esc($r['shop_name'] ?? 'N/A').'</td>';
        echo '<td>'.esc($r['borrower_name'] ?? 'N/A').'</td>';
        echo '<td>'.esc($period).'</td>';
        echo '<td>'.intval($days).'</td>';
        echo '<td>₹'.number_format($total,2).'</td>';
        echo '<td>₹'.number_format($commission,2).'</td>';
        echo '<td>'.esc(ucfirst($r['status'] ?? 'N/A')).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

/* ---------- TRANSACTIONS (table) ---------- */
if($action === 'transactions'){
    $sql = "SELECT p.*, rr.id AS rr_id, COALESCE(c.name,c.title,'') AS costume_name, l.shop_name, b.name AS borrower_name
            FROM payments p
            LEFT JOIN rental_requests rr ON p.rental_request_id = rr.id
            LEFT JOIN costumes c ON rr.costume_id = c.id
            LEFT JOIN lenders l ON rr.lender_id = l.id
            LEFT JOIN borrowers b ON rr.borrower_id = b.id
            ORDER BY p.id DESC";
    $res = $conn->query($sql);
    echo '<h3 style="margin:0 0 12px 0">Transactions</h3>';
    if(!$res){ echo '<div class="table-wrap">Error: '.esc($conn->error).'</div>'; exit; }
    echo '<div class="table-wrap" style="margin-top:10px"><table><thead><tr>
          <th>Txn ID</th><th>Costume</th><th>Shop</th><th>Borrower</th><th>Amount</th><th>Commission (10%)</th><th>Method</th><th>Status</th><th>Date</th>
          </tr></thead><tbody>';
    if($res->num_rows === 0) echo '<tr><td colspan="9" style="padding:14px">No transactions found.</td></tr>';
    while($r = $res->fetch_assoc()){
        $amt = floatval($r['amount'] ?? 0);
        $commission = $amt * 0.10;
        echo '<tr>';
        echo '<td>#'.intval($r['id']).'</td>';
        echo '<td>'.esc($r['costume_name']).'</td>';
        echo '<td>'.esc($r['shop_name'] ?? 'N/A').'</td>';
        echo '<td>'.esc($r['borrower_name'] ?? 'N/A').'</td>';
        echo '<td>₹'.number_format($amt,2).'</td>';
        echo '<td>₹'.number_format($commission,2).'</td>';
        echo '<td>'.esc(ucfirst($r['payment_method'] ?? 'N/A')).'</td>';
        echo '<td>'.esc(ucfirst($r['status'] ?? 'N/A')).'</td>';
        echo '<td>'.esc(date('M j, Y g:i A', strtotime($r['created_at'] ?? $r['created'] ?? 'now'))).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

/* ------------------- DELETE COSTUME ------------------- (POST) */
if(isset($_GET['action']) && $_GET['action'] === 'delete_costume' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id'] ?? 0);
    if($id <= 0){ header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Invalid id']); exit; }
    // safety: check active rentals
    $chk = $conn->query("SELECT COUNT(*) as c FROM rental_requests WHERE costume_id = $id AND status IN ('pending','accepted')")->fetch_assoc()['c'] ?? 0;
    if($chk > 0){
        header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Cannot delete costume with active rentals']); exit;
    }
    // remove image file if present
    $imgRes = $conn->query("SELECT image FROM costumes WHERE id=$id");
    if($imgRes && $imgRes->num_rows){
        $img = $imgRes->fetch_assoc()['image'];
        if($img && file_exists($img) && strpos($img,'no-image') === false) @unlink($img);
    }
    if($conn->query("DELETE FROM costumes WHERE id=$id")){
        header('Content-Type: application/json'); echo json_encode(['status'=>1,'message'=>'Costume deleted']); exit;
    } else {
        header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Error: '.$conn->error]); exit;
    }
}

/* ------------------- DELETE USER ------------------- (POST) */
if(isset($_GET['action']) && $_GET['action'] === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id'] ?? 0);
    $type = ($_POST['type'] ?? 'borrower') === 'lender' ? 'lender' : 'borrower';
    if($id <= 0){ header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Invalid id']); exit; }

    $col = ($type === 'lender') ? 'lender_id' : 'borrower_id';
    $chk = $conn->query("SELECT COUNT(*) as c FROM rental_requests WHERE $col = $id AND status IN ('pending','accepted')")->fetch_assoc()['c'] ?? 0;
    if($chk > 0){ header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Cannot delete user with active rentals']); exit; }

    if($type === 'lender'){
        $costCount = $conn->query("SELECT COUNT(*) as c FROM costumes WHERE lender_id=$id")->fetch_assoc()['c'] ?? 0;
        if($costCount > 0){ header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Remove lender costumes first']); exit; }
    }

    $table = ($type === 'lender') ? 'lenders' : 'borrowers';
    if($conn->query("DELETE FROM $table WHERE id=$id")){
        header('Content-Type: application/json'); echo json_encode(['status'=>1,'message'=>ucfirst($type).' deleted']); exit;
    } else {
        header('Content-Type: application/json'); echo json_encode(['status'=>0,'message'=>'Error: '.$conn->error]); exit;
    }
}

/* -------- default fallback -------- */
echo '<div class="table-wrap"><p style="padding:14px">Unknown action.</p></div>';
exit;
