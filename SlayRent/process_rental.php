<?php
session_start();
include 'includes/config.php';

// Always return JSON
header('Content-Type: application/json');

// Ensure borrower is logged in
if (!isset($_SESSION['borrower_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in as a borrower."
    ]);
    exit();
}

$borrower_id = $_SESSION['borrower_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['costume_id'], $_POST['quantity'], $_POST['start_date'], $_POST['end_date'])) {

    $costume_id = intval($_POST['costume_id']);
    $quantity   = max(1, intval($_POST['quantity']));

    // 🔹 Validate & format dates
    $start_date_ts = strtotime($_POST['start_date']);
    if (!$start_date_ts) $start_date_ts = time();
    $start_date = date('Y-m-d', $start_date_ts);

    $end_date_ts = strtotime($_POST['end_date']);
    if (!$end_date_ts || $end_date_ts < $start_date_ts) $end_date_ts = $start_date_ts + 86400; // +1 day
    $end_date = date('Y-m-d', $end_date_ts);

    // ✅ Fetch costume details
    $sql = "SELECT lender_id, price_per_day FROM costumes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $costume_id);
    $stmt->execute();
    $stmt->bind_result($lender_id, $price_per_day);

    if (!$stmt->fetch()) {
        echo json_encode([
            "status" => "error",
            "message" => "Costume not found."
        ]);
        exit();
    }
    $stmt->close();

    if (!$lender_id) {
        echo json_encode([
            "status" => "error",
            "message" => "This costume has no lender assigned."
        ]);
        exit();
    }

    // ✅ Calculate rental days and total price according to new logic
    $days = max(1, ceil(($end_date_ts - $start_date_ts) / 86400) + 1);
    $base_price = $price_per_day * $quantity;

    if ($days <= 3) {
        $total_price = $base_price;
    } else {
        $extra_days = $days - 3;
        // total = base for first 3 days + (base * extra_days) + 10 * sum(1..extra_days)
        $total_price = $base_price + ($extra_days*10);
    }

    // ✅ Insert rental request with status 'pending'
    $sql = "INSERT INTO rental_requests 
            (borrower_id, lender_id, costume_id, quantity, start_date, end_date, total_price, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissdd", $borrower_id, $lender_id, $costume_id, $quantity, $start_date, $end_date, $total_price);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Rental Request Sent Successfully!",
            "request_id" => $stmt->insert_id,
            "total_price" => $total_price
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Something went wrong while sending request."
        ]);
    }
    $stmt->close();

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request! Missing data."
    ]);
}

$conn->close();
?>
