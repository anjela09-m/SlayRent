<?php
require('razorpay-php/Razorpay.php');
require('includes/config.php'); // Add your DB connection
use Razorpay\Api\Api;

// Test credentials
$keyId = 'rzp_test_RDRydETJkRioj4';
$keySecret = 'TkjjXyi4uLSvGY6PLzyL8cVV';

if (!isset($_GET['payment_id']) || !isset($_GET['request_id'])) {
    echo "No payment ID or request ID provided.";
    exit;
}

$paymentId = $_GET['payment_id'];
$requestId = $_GET['request_id'];

// Fetch total price from booking
$stmt = $conn->prepare("SELECT total_price FROM rental_requests WHERE id = ?");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$stmt->bind_result($total_price);
if ($stmt->fetch()) {
    $amount = $total_price * 100; // Convert to paise
} else {
    echo "Booking not found.";
    exit;
}
$stmt->close();

// Creates a Razorpay API instance
$api = new Api($keyId, $keySecret);

try {
    // Fetch payment details
    $payment = $api->payment->fetch($paymentId);

    if ($payment->status == 'captured') {
        echo "<h2>✅ Payment Already Captured</h2>";
    } else {
        // Capture the payment with correct amount
        $api->payment->fetch($paymentId)->capture([
            'amount' => $amount
        ]);
        echo "<h2>✅ Payment Captured Successfully!</h2>";
    }

    echo "<h3>Price Summary</h3>";
    echo "<p>Total Price: ₹" . number_format($total_price, 2) . "</p>";
    echo "<pre>";
    print_r($payment->toArray());
    echo "</pre>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}