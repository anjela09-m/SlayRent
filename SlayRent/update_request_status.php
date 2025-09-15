<?php
session_start();
include 'includes/config.php';

// JSON helper
function respond($success, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success], $extra));
    exit;
}

// Role check
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['lender','borrower'])) {
    http_response_code(403);
    respond(false, ['error' => 'Unauthorized access']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['error' => 'Invalid request method']);
}

// Request data
$request_id = intval($_POST['id'] ?? 0);
$new_status = trim($_POST['status'] ?? '');
$user_id    = intval($_SESSION['user_id'] ?? 0);
$user_type  = $_SESSION['user_type'];

if ($request_id <= 0 || !$new_status || $user_id <= 0) {
    http_response_code(400);
    respond(false, ['error' => 'Invalid request data']);
}

// Fetch rental request
$stmt = $conn->prepare("SELECT lender_id, borrower_id, status, total_price FROM rental_requests WHERE id=?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$res = $stmt->get_result();
$request = $res->fetch_assoc();
$stmt->close();

if (!$request) respond(false, ['error' => 'Request not found']);

$current_status = $request['status'];
$lender_id      = $request['lender_id'];
$borrower_id    = $request['borrower_id'];
$amount         = $request['total_price'];

// Lifecycle rules
$transitions = [
    'pending'    => ['accepted','rejected'],
    'accepted'   => ['paid'],
    'paid'       => ['dispatched'],
    'dispatched' => ['delivered'],
    'delivered'  => ['returned'],
    'returned'   => ['completed']
];

// Role-based permissions
$role_permissions = [
    'lender'   => ['accepted','rejected','dispatched','completed'],
    'borrower' => ['paid','delivered','returned']
];

// Transition validation
if (!isset($transitions[$current_status]) || !in_array($new_status, $transitions[$current_status])) {
    respond(false, ['error' => "Invalid transition from $current_status to $new_status"]);
}

// Permission validation
$valid = false;
if ($user_type === 'lender' && $lender_id == $user_id && in_array($new_status, $role_permissions['lender'])) $valid = true;
if ($user_type === 'borrower' && $borrower_id == $user_id && in_array($new_status, $role_permissions['borrower'])) $valid = true;

if (!$valid) {
    http_response_code(403);
    respond(false, ['error' => 'You are not allowed to perform this action']);
}

// === Perform Update ===

// 1. Borrower makes payment → log in payments table
if ($new_status === 'paid') {
    $stmt2 = $conn->prepare("
        INSERT INTO payments (request_id, borrower_id, lender_id, amount, status, created_at)
        VALUES (?, ?, ?, ?, 'paid', NOW())
    ");
    $stmt2->bind_param("iiid", $request_id, $borrower_id, $lender_id, $amount);
    $stmt2->execute();
    $stmt2->close();
}

// 2. Lender confirms return → mark completed & set end_date
if ($new_status === 'completed') {
    $stmt2 = $conn->prepare("UPDATE rental_requests SET end_date=NOW() WHERE id=?");
    $stmt2->bind_param("i", $request_id);
    $stmt2->execute();
    $stmt2->close();
}

// Update rental_requests status
$stmt = $conn->prepare("UPDATE rental_requests SET status=? WHERE id=?");
$stmt->bind_param("si", $new_status, $request_id);
$stmt->execute();
$stmt->close();

// Respond
respond(true, [
    'message'    => "Status updated to $new_status",
    'new_status' => $new_status,
    'user_type'  => $user_type,
    'amount'     => $amount
]);
?>
