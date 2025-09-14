<?php
session_start();
require 'vendor/autoload.php'; // Composer autoload
require 'includes/config.php'; 

use Razorpay\Api\Api;

// Ensure borrower is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    echo json_encode(['success' => false, 'message' => "❌ Unauthorized access."]);
    exit;
}

// Grab POST data safely
$paymentId = $_POST['razorpay_payment_id'] ?? '';
$orderId   = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';
$requestId = intval($_POST['request_id'] ?? 0);

if (!$paymentId || !$orderId || !$signature || !$requestId) {
    echo json_encode(['success' => false, 'message' => "❌ Missing payment details."]);
    exit;
}

// Razorpay credentials
$keyId     = 'rzp_test_RDRydETJkRioj4';
$keySecret = 'TkjjXyi4uLSvGY6PLzyL8cVV';
$api       = new Api($keyId, $keySecret);

try {
    // Verify Razorpay signature
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature'  => $signature
    ]);

    // Fetch rental request
    $stmt = $conn->prepare("SELECT borrower_id, lender_id, total_price, status 
                            FROM rental_requests WHERE id=?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => "❌ Rental request not found."]);
        exit;
    }
    if ($row['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => "⚠️ Payment already completed for this request."]);
        exit;
    }

    $borrowerId = $row['borrower_id'];
    $lenderId   = $row['lender_id'];
    $amount     = $row['total_price'];

    // --- UPDATE RENTAL REQUEST STATUS TO PAID ---
    $stmt = $conn->prepare("UPDATE rental_requests SET status='paid' WHERE id=?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();

    // --- DUPLICATE-SAFE PAYMENT LOGIC ---
    $check = $conn->prepare("SELECT id FROM payments WHERE rental_request_id=?");
    $check->bind_param("i", $requestId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update existing payment
        $stmt = $conn->prepare("UPDATE payments 
            SET razorpay_payment_id=?, status='PAID'
            WHERE rental_request_id=?");
        $stmt->bind_param("si", $paymentId, $requestId);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new payment
        $stmt = $conn->prepare("INSERT INTO payments 
            (rental_request_id, borrower_id, lender_id, razorpay_order_id, razorpay_payment_id, amount, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'PAID', NOW())");
        $stmt->bind_param("iiissd", $requestId, $borrowerId, $lenderId, $orderId, $paymentId, $amount);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();
    // --- END PAYMENT LOGIC ---

    // Return JSON success response with lifecycle info for AJAX update
    echo json_encode([
        'success' => true,
        'message' => "✅ Payment Successful! Booking confirmed.",
        'new_status' => 'paid',
        'amount' => $amount
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "❌ Payment Verification Failed: " . $e->getMessage()]);
}
