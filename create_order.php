<?php
session_start();
require 'vendor/autoload.php'; // Composer autoload
require 'includes/config.php';

use Razorpay\Api\Api;

// Ensure borrower is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get request data from fetch (JSON)
$data = json_decode(file_get_contents('php://input'), true);
$requestId = intval($data['request_id'] ?? 0);

if ($requestId <= 0) {
    echo json_encode(['error' => 'Invalid Request ID']);
    exit();
}

$borrowerId = $_SESSION['user_id'];

// Fetch rental request and total_price
$stmt = $conn->prepare("SELECT rr.id, rr.total_price, rr.status, rr.lender_id 
                        FROM rental_requests rr 
                        WHERE rr.id=? AND rr.borrower_id=?");
$stmt->bind_param("ii", $requestId, $borrowerId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['error' => 'Rental request not found']);
    exit();
}

if ($row['status'] === 'paid') {
    echo json_encode(['error' => 'Payment already completed']);
    exit();
}

// Razorpay API
$keyId = 'rzp_test_RDRydETJkRioj4';
$keySecret = 'TkjjXyi4uLSvGY6PLzyL8cVV';
$api = new Api($keyId, $keySecret);

// Create Razorpay order
$orderData = [
    'receipt'         => "request_" . $requestId,
    'amount'          => $row['total_price'] * 100, // convert ₹ to paise
    'currency'        => 'INR',
    'payment_capture' => 1
];

$order = $api->order->create($orderData);
$orderId = $order['id'];

// Insert or update payments table as PENDING
$stmt = $conn->prepare("
    INSERT INTO payments (rental_request_id, lender_id, borrower_id, razorpay_order_id, amount, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())
    ON DUPLICATE KEY UPDATE 
        razorpay_order_id = VALUES(razorpay_order_id),
        amount = VALUES(amount),
        status = 'PENDING'
");
$stmt->bind_param("iiisi", $row['id'], $row['lender_id'], $borrowerId, $orderId, $row['total_price']);
$stmt->execute();
$stmt->close();

// Send order details to frontend
echo json_encode([
    'order_id' => $orderId,
    'amount'   => $row['total_price'] * 100 // paise
]);
