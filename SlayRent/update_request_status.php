<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['id']);
    $status = $_POST['status'];

    if (!in_array($status, ['accepted', 'rejected'])) {
        die("Invalid status.");
    }

    $stmt = $conn->prepare("UPDATE rental_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
