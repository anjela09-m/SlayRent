<?php
session_start();
include 'includes/config.php';
require('razorpay-php/Razorpay.php'); // Include SDK

use Razorpay\Api\Api;

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrower_id = $_SESSION['user_id'];
    $costume_id = intval($_POST['costume_id']);
    $quantity = intval($_POST['quantity']);
    $days = intval($_POST['days']);

    if ($quantity < 1 || $days < 1 || $days > 10) {
        die("Invalid input.");
    }

    $stmt = $conn->prepare("SELECT title, price_per_day FROM costumes WHERE id = ?");
    $stmt->bind_param("i", $costume_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $costume = $result->fetch_assoc();

    if (!$costume) {
        die("Costume not found.");
    }

    // Calculate amount: price * days * quantity
    $amount = $costume['price_per_day'] * $days * $quantity;

    // Razorpay order creation
    $api = new Api('YOUR_KEY_ID', 'YOUR_KEY_SECRET');
    $razorpayOrder = $api->order->create([
        'receipt' => 'rcptid_' . time(),
        'amount' => $amount * 100, // in paise
        'currency' => 'INR'
    ]);

    $_SESSION['rental_temp'] = [
        'costume_id' => $costume_id,
        'quantity' => $quantity,
        'days' => $days,
        'amount' => $amount,
        'order_id' => $razorpayOrder['id']
    ];

    // Redirect to payment page
    header("Location: pay_now.php");
    exit();
}
