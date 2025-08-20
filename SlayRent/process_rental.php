<?php
session_start();
include 'includes/config.php';

// Check if borrower is logged in
if (!isset($_SESSION['borrower_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in as a borrower."
    ]);
    exit();
}

$borrower_id = $_SESSION['borrower_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['costume_id'])) {
    $costume_id = intval($_POST['costume_id']);

    // ✅ Get lender_id of this costume
    $sql = "SELECT lender_id FROM costumes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $costume_id);
    $stmt->execute();
    $stmt->bind_result($lender_id);
    $stmt->fetch();
    $stmt->close();

    if (!$lender_id) {
        echo json_encode([
            "status" => "error",
            "message" => "This costume has no lender assigned."
        ]);
        exit();
    }

    // ✅ Insert into rental_requests with lender_id
    $sql = "INSERT INTO rental_requests (lender_id, borrower_id, costume_id, status) VALUES (?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $lender_id, $borrower_id, $costume_id);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Rental Request Sent!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Something went wrong. Please try again."
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request! No costume selected."
    ]);
}

$conn->close();
?>
