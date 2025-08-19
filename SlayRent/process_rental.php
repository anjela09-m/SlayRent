<?php
session_start();
include 'includes/config.php';

// Check if borrower is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'borrower') {
    echo "You must be logged in as a borrower to request a rental.";
    exit();
}
// Get costume_id from POST
if (!isset($_POST['costume_id'])) {
    echo "Invalid request.";
    exit();
}
$borrower_id = $_SESSION['user_id'];   // ✅ Correct now
$costume_id  = intval($_POST['costume_id']);


// 🔹 Fetch lender_id from costumes table
$stmt = $conn->prepare("SELECT lender_id FROM costumes WHERE id = ?");
$stmt->bind_param("i", $costume_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Costume not found.";
    exit();
}

$row = $result->fetch_assoc();
$lender_id = $row['lender_id'];

// 🔹 Insert rental request
$stmt = $conn->prepare("INSERT INTO rental_requests (costume_id, borrower_id, lender_id, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iii", $costume_id, $borrower_id, $lender_id);

if ($stmt->execute()) {
    echo "Rental request sent successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>
