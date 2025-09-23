<?php
session_start();
include 'includes/config.php';

// Ensure only borrower can submit reviews
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrower_id = $_SESSION['user_id'];
    $costume_id = intval($_POST['costume_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'error' => 'Invalid rating']);
        exit();
    }
    
    // Check if borrower has completed a rental for this costume
    $checkRental = $conn->prepare("
        SELECT rr.id, rr.lender_id, c.title 
        FROM rental_requests rr 
        JOIN costumes c ON rr.costume_id = c.id 
        WHERE rr.borrower_id = ? AND rr.costume_id = ? 
        AND LOWER(rr.status) = 'completed'
    ");
    $checkRental->bind_param("ii", $borrower_id, $costume_id);
    $checkRental->execute();
    $rentalResult = $checkRental->get_result();
    
    if ($rentalResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'You can only review costumes after completing a rental']);
        exit();
    }
    
    $rentalData = $rentalResult->fetch_assoc();
    $lender_id = $rentalData['lender_id'];
    
    // Check if review already exists for this costume by this borrower
    $checkExisting = $conn->prepare("
        SELECT id FROM reviews 
        WHERE borrower_id = ? AND costume_id = ?
    ");
    $checkExisting->bind_param("ii", $borrower_id, $costume_id);
    $checkExisting->execute();
    
    if ($checkExisting->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'You have already reviewed this costume']);
        exit();
    }
    
    // Insert the costume review
    $insertReview = $conn->prepare("
        INSERT INTO reviews (lender_id, borrower_id, costume_id, rating, comment, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $insertReview->bind_param("iiiis", $lender_id, $borrower_id, $costume_id, $rating, $comment);
    
    if ($insertReview->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Review submitted successfully!',
            'costume_title' => $rentalData['title']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit review']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>