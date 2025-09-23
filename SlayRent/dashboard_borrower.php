<?php
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'borrower') {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$name = $_SESSION['name'] ?? 'Borrower';
$joined_days = 0;

// Calculate joined days
$stmt = $conn->prepare("SELECT created_at FROM borrowers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $joined_days = floor((time() - strtotime($row['created_at'])) / (60 * 60 * 24));
}
$stmt->close();

// Fetch costumes with average ratings per costume
$costumes = [];
$sql = "SELECT c.*, 
               AVG(r.rating) as avg_rating, 
               COUNT(r.id) as review_count 
        FROM costumes c 
        LEFT JOIN reviews r ON c.id = r.costume_id 
        GROUP BY c.id 
        ORDER BY c.id DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $row['avg_rating'] = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
    $row['review_count'] = intval($row['review_count']);
    $costumes[] = $row;
}

// Fetch borrower's rental requests with latest payment and costume details
$requests = [];
$qstmt = $conn->prepare("
    SELECT rr.id, rr.status, rr.request_date, rr.quantity, rr.total_price, 
           c.title AS costume_title, c.lender_id, c.id AS costume_id,
           p.status AS payment_status
    FROM rental_requests rr
    JOIN costumes c ON rr.costume_id = c.id
    LEFT JOIN (
        SELECT p1.rental_request_id, p1.status
        FROM payments p1
        INNER JOIN (
            SELECT rental_request_id, MAX(id) AS max_id
            FROM payments
            GROUP BY rental_request_id
        ) p2 ON p1.rental_request_id = p2.rental_request_id AND p1.id = p2.max_id
    ) p ON rr.id = p.rental_request_id
    WHERE rr.borrower_id = ?
    ORDER BY 
        CASE rr.status
            WHEN 'pending' THEN 1
            WHEN 'accepted' THEN 2
            WHEN 'paid' THEN 3
            WHEN 'dispatched' THEN 4
            WHEN 'delivered' THEN 5
            WHEN 'return_requested' THEN 6
            WHEN 'returned' THEN 7
            WHEN 'completed' THEN 8
            WHEN 'rejected' THEN 9
            ELSE 99
        END, rr.request_date DESC
");
$qstmt->bind_param("i", $user_id);
$qstmt->execute();
$qres = $qstmt->get_result();
while ($row = $qres->fetch_assoc()) $requests[] = $row;
$qstmt->close();

// Check which completed rentals have costume reviews
foreach ($requests as &$request) {
    if ($request['status'] === 'completed') {
        $reviewCheckStmt = $conn->prepare("
            SELECT id FROM reviews 
            WHERE borrower_id = ? AND costume_id = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $reviewCheckStmt->bind_param("ii", $user_id, $request['costume_id']);
        $reviewCheckStmt->execute();
        $reviewResult = $reviewCheckStmt->get_result();
        $request['review_id'] = $reviewResult->num_rows > 0 ? $reviewResult->fetch_assoc()['id'] : null;
        $reviewCheckStmt->close();
    }
}

// Fetch borrower's past costume reviews
$pastReviews = [];
$reviewStmt = $conn->prepare("
    SELECT r.*, c.title as costume_title, l.shop_name
    FROM reviews r
    JOIN costumes c ON r.costume_id = c.id
    JOIN lenders l ON r.lender_id = l.id
    WHERE r.borrower_id = ?
    ORDER BY r.created_at DESC
");
$reviewStmt->bind_param("i", $user_id);
$reviewStmt->execute();
$reviewRes = $reviewStmt->get_result();
while ($row = $reviewRes->fetch_assoc()) $pastReviews[] = $row;
$reviewStmt->close();

$keyId = 'rzp_test_RDRydETJkRioj4';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Borrower Dashboard | SlayRent</title>
<link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
/* --- Custom Color Palette & Fonts --- */
:root {
  --cream: #FBDB93;
  --coral: #BE5B50;
  --burgundy: #8A2D3B;
  --dark-burgundy: #641B2E;
  --white: #ffffff;
  --light-gray: #f8f8f8;
  --green: #4caf50;
  --red: #ff4d4d;
  --orange: #ff9800;
  --blue: #007bff;
  --gold: #ffd700;
}

* { 
  box-sizing: border-box; 
  font-family: 'Candara', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
}

body { 
  margin: 0; 
  background: var(--cream); 
  color: var(--burgundy); 
  overflow-x: hidden; 
}

/* Headings use Times New Roman */
h1, h2, h3, h4, h5, h6 {
  font-family: 'Times New Roman', serif;
  color: var(--dark-burgundy);
}

/* Header Controls for Hamburger + Notifications */
.header-controls {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

/* Fixed Notification Styles - Replace your existing notification CSS with this */
/* REPLACE your existing notification CSS with this fixed version */

.header-controls {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  position: relative;
  z-index: 2000; /* Higher z-index */
}

.notification-container {
  position: relative;
  display: inline-block;
  margin-left: 20px;
  z-index: 2100; /* Even higher */
}

.notification-bell {
  position: relative;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--burgundy);
  transition: color 0.3s ease;
  padding: 5px;
  z-index: 2101;
}

.notification-bell:hover {
  color: var(--coral);
}

.notification-badge {
  position: absolute;
  top: 0px;
  right: 0px;
  background: var(--red);
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
  font-weight: bold;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
  z-index: 2102;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.notification-dropdown {
  position: fixed; /* Changed from absolute to fixed */
  top: 70px; /* Adjust based on your header height */
  right: 50px; /* Adjust based on your layout */
  background: var(--white);
  border: 3px solid var(--coral);
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(138, 45, 59, 0.4);
  width: 350px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 9999; /* Very high z-index */
  display: none;
}

.notification-dropdown.active {
  display: block;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.notification-header {
  padding: 15px 20px;
  border-bottom: 2px solid var(--cream);
  background: var(--coral);
  color: white;
  border-radius: 9px 9px 0 0; /* Adjusted for border */
  font-weight: 600;
  text-align: center;
  font-family: 'Times New Roman', serif;
  position: relative;
  z-index: 9998;
}

.notification-item {
  padding: 15px 20px;
  border-bottom: 1px solid var(--cream);
  cursor: pointer;
  transition: background-color 0.2s ease;
  position: relative;
  background: var(--white);
  z-index: 9997;
}

.notification-item:hover {
  background-color: var(--light-gray);
}

.notification-item:last-child {
  border-bottom: none;
  border-radius: 0 0 9px 9px; /* Adjusted for border */
}

.notification-item.unread {
  background-color: #fff8f3;
  border-left: 4px solid var(--coral);
}

.notification-title {
  font-weight: 600;
  color: var(--burgundy);
  font-size: 14px;
  margin-bottom: 5px;
  font-family: 'Times New Roman', serif;
}

.notification-message {
  color: var(--burgundy);
  font-size: 13px;
  line-height: 1.4;
  margin-bottom: 5px;
}

.notification-time {
  font-size: 11px;
  color: var(--coral);
  font-style: italic;
}

.notification-mark-read {
  position: absolute;
  top: 12px;
  right: 15px;
  background: var(--green);
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease;
  z-index: 9996;
}

.notification-mark-read:hover {
  background: #2e7d32;
}

.notification-empty {
  padding: 30px 20px;
  text-align: center;
  color: var(--burgundy);
  opacity: 0.7;
  font-style: italic;
}

.notification-modal {
  display: none;
  position: fixed;
  z-index: 10000; /* Highest z-index */
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(100, 27, 46, 0.8);
  align-items: center;
  justify-content: center;
}

.notification-modal-content {
  background: var(--white);
  padding: 25px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0px 8px 25px rgba(0,0,0,0.3);
  max-width: 450px;
  width: 90%;
  border: 2px solid var(--coral);
  animation: modalSlideIn 0.3s ease;
  position: relative;
  z-index: 10001;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.notification-modal h3 {
  color: var(--burgundy);
  font-family: 'Times New Roman', serif;
  margin-bottom: 15px;
  font-size: 1.3em;
}

.notification-modal p {
  color: var(--burgundy);
  font-family: 'Candara', sans-serif;
  margin-bottom: 20px;
  line-height: 1.5;
  font-size: 1em;
}

.notification-modal button {
  margin: 8px 5px;
  padding: 12px 20px;
  background: var(--coral);
  color: var(--white);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'Candara', sans-serif;
  transition: background 0.3s ease;
  font-size: 1em;
  font-weight: 500;
}

.notification-modal button:hover {
  background: var(--burgundy);
}

/* Ensure other elements don't interfere */
.sidebar {
  z-index: 1000; /* Lower than notifications */
}

.overlay {
  z-index: 999; /* Lower than notifications */
}

.main-content {
  position: relative;
  z-index: 1; /* Lower than notifications */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .notification-dropdown {
    width: 300px;
    right: 20px;
    top: 60px;
  }
  
  .notification-modal-content {
    width: 95%;
    padding: 20px;
  }
  
  .header-controls {
    margin-bottom: 10px;
  }
}

/* Ensure sidebar doesn't cover notifications */
.sidebar {
  z-index: 1000;
}

.overlay {
  z-index: 999;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .notification-dropdown {
    width: 300px;
    right: -80px;
  }
  
  .notification-modal-content {
    width: 95%;
    padding: 20px;
  }
  
  .header-controls {
    margin-bottom: 10px;
  }
}
/* Sidebar */
.sidebar { 
  width: 320px; 
  background-color: var(--dark-burgundy); 
  color: var(--cream); 
  padding: 25px; 
  height: 100vh; 
  position: fixed; 
  top: 0; 
  left: -320px; 
  transition: 0.28s; 
  z-index: 1000; 
  overflow-y: auto; 
}
.sidebar.active { left: 0; }
.sidebar h3 { 
  margin-bottom: 20px; 
  font-size: 1.4em; 
  font-family: 'Times New Roman', serif;
  color: var(--cream);
}
.sidebar a { 
  color: var(--cream); 
  text-decoration: none; 
  display: block; 
  margin: 12px 0; 
  font-size: 0.95em; 
  transition: color 0.3s ease;
  cursor: pointer;
}
.sidebar a:hover {
  color: var(--coral);
}
.sidebar .rental-requests { margin-top: 20px; }
.sidebar .rental-requests h4 { 
  margin-bottom: 10px; 
  color: var(--cream);
  font-family: 'Times New Roman', serif;
}
.sidebar .costume-card { 
  background: var(--cream); 
  padding: 10px; 
  margin-bottom: 10px; 
  border-radius: 8px; 
  box-shadow: 0 2px 8px rgba(100, 27, 46, 0.2); 
}
.sidebar .costume-card h5 { 
  margin: 5px 0; 
  font-size: 0.95em; 
  color: var(--burgundy); 
  font-weight: 600; 
  font-family: 'Times New Roman', serif;
}
.sidebar .costume-card p { 
  font-size: 0.82em; 
  color: var(--burgundy); 
  margin: 3px 0; 
}
.sidebar .costume-card .button { 
  padding: 6px 10px; 
  font-size: 0.84em; 
  margin-top: 6px; 
  display: block; 
  text-align: center; 
  background: var(--coral); 
  border: none; 
  border-radius: 6px; 
  cursor: pointer; 
  color: var(--white); 
  font-weight: 500; 
  transition: background 0.3s ease;
}
.sidebar .costume-card .button:hover {
  background: var(--burgundy);
}
.sidebar .costume-card .button.disabled { 
  background: #aaa; 
  cursor: not-allowed; 
}
.sidebar .costume-card .button.disabled:hover { 
  background: #aaa; 
}
.sidebar .completed-log { 
  background: var(--white); 
  color: var(--burgundy); 
  padding: 6px 10px; 
  margin: 6px 0; 
  border-radius: 6px; 
  font-size: 0.82em; 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  box-shadow: 0 1px 3px rgba(138, 45, 59, 0.1); 
}
.sidebar .completed-log span.status { 
  font-weight: bold; 
  color: var(--green); 
}

/* Reviews section in sidebar */
.sidebar .reviews-section { 
  margin-top: 20px; 
}
.sidebar .review-item {
  background: var(--cream);
  padding: 8px;
  margin-bottom: 8px;
  border-radius: 6px;
  font-size: 0.8em;
}
.sidebar .review-item .stars {
  color: var(--gold);
  font-size: 0.9em;
  margin-bottom: 2px;
}
.sidebar .review-item .review-title {
  font-weight: 600;
  color: var(--burgundy);
  margin-bottom: 2px;
}
.sidebar .review-item .review-shop {
  color: var(--burgundy);
  font-size: 0.75em;
  opacity: 0.8;
  margin-bottom: 2px;
}
.sidebar .review-item .review-comment {
  color: var(--burgundy);
  font-size: 0.75em;
  opacity: 0.8;
}

/* Overlay */
.overlay { 
  position: fixed; 
  top: 0; 
  left: 0; 
  width: 100%; 
  height: 100%; 
  background: rgba(100, 27, 46, 0.5); 
  display: none; 
  z-index: 999; 
}
.overlay.active { display: block; }

/* Main content */
.main-content { 
  margin-left: 0; 
  transition: 0.28s; 
  padding: 30px 40px; 
  background-color: var(--cream); 
  min-height: 100vh; 
  color: var(--burgundy); 
}
.main-content.shifted { margin-left: 320px; }

/* Hamburger */
.hamburger { 
  font-size: 24px; 
  background: none; 
  border: none; 
  color: var(--burgundy); 
  cursor: pointer; 
  transition: color 0.3s ease;
}
.hamburger:hover {
  color: var(--coral);
}

/* Search Bar */
.search-container { 
  position: relative; 
  margin: 20px 0 30px 0; 
}
.search-bar { 
  width: 100%; 
  padding: 15px 50px 15px 20px; 
  font-size: 16px; 
  border: 2px solid var(--coral); 
  border-radius: 25px; 
  background: var(--white); 
  color: var(--burgundy); 
  outline: none;
  transition: all 0.3s ease;
  font-family: 'Candara', sans-serif;
}
.search-bar:focus { 
  border-color: var(--burgundy); 
  box-shadow: 0 0 15px rgba(138, 45, 59, 0.3); 
}
.search-bar::placeholder { 
  color: var(--burgundy); 
  opacity: 0.6; 
}
.search-icon { 
  position: absolute; 
  right: 18px; 
  top: 50%; 
  transform: translateY(-50%); 
  font-size: 18px; 
  color: var(--coral); 
  pointer-events: none; 
}
.clear-search { 
  position: absolute; 
  right: 50px; 
  top: 50%; 
  transform: translateY(-50%); 
  background: none; 
  border: none; 
  color: var(--burgundy); 
  font-size: 18px; 
  cursor: pointer; 
  display: none; 
  transition: color 0.3s ease;
}
.clear-search:hover {
  color: var(--coral);
}

/* Costume grid + cards */
.costume-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
  gap: 20px; 
  align-items: stretch; 
}
.costume-card { 
  background: var(--white); 
  padding: 15px; 
  border-radius: 10px; 
  text-align: center; 
  box-shadow: 0 4px 15px rgba(138, 45, 59, 0.15); 
  color: var(--burgundy); 
  height: 470px; 
  display: flex; 
  flex-direction: column; 
  border: 1px solid var(--cream); 
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.costume-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(138, 45, 59, 0.2);
}
.costume-card.hidden { display: none; }
.costume-card .card-content { 
  flex-grow: 1; 
  display: flex; 
  flex-direction: column; 
  justify-content: flex-start; 
}
.costume-card .button-container { margin-top: 10px; }
.costume-card img { 
  width: 100%; 
  height: 150px; 
  object-fit: cover; 
  border-radius: 8px; 
  margin-bottom: 8px; 
  border: 2px solid var(--cream);
}
.costume-card h4 { 
  font-size: 1em; 
  margin: 6px 0; 
  line-height: 1.2em; 
  max-height: 2.4em; 
  overflow: hidden; 
  text-overflow: ellipsis; 
  display: -webkit-box; 
  -webkit-line-clamp: 2; 
  -webkit-box-orient: vertical; 
  color: var(--burgundy); 
  font-weight: 600; 
  font-family: 'Times New Roman', serif;
}

/* Rating display */
.rating-display {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 5px 0;
  font-size: 0.9em;
}
.stars {
  color: var(--gold);
  margin-right: 5px;
}
.rating-text {
  color: var(--burgundy);
  font-weight: 500;
}

/* Buttons */
.button { 
  background: var(--coral); 
  color: var(--white); 
  padding: 8px 16px; 
  border-radius: 6px; 
  text-decoration: none; 
  display: inline-block; 
  margin-top: 6px; 
  font-size: 0.85em; 
  cursor: pointer; 
  border: none; 
  transition: all 0.3s ease; 
  font-weight: 500; 
  font-family: 'Candara', sans-serif;
}
.button:hover { 
  background: var(--burgundy); 
  transform: translateY(-1px);
}
.button.disabled { 
  background: #aaa; 
  cursor: not-allowed; 
}
.button.disabled:hover { 
  background: #aaa; 
  transform: none;
}

.button.review-btn {
  background: var(--green);
  font-size: 0.8em;
  padding: 5px 12px;
}
.button.review-btn:hover {
  background: #2e7d32;
}

/* Status colors */
.status { 
  font-weight: bold; 
  margin-top: 5px; 
  font-size: 0.85em; 
}
.status.pending { color: var(--orange); }
.status.accepted { color: var(--green); }
.status.rejected { color: var(--red); }
.status.paid { color: var(--blue); }
.status.dispatched { color: var(--coral); }
.status.delivered { color: #2ecc71; }
.status.return_requested { color: #e67e22; }
.status.returned { color: #d35400; }
.status.completed { color: var(--green); }

/* Modals */
.modal { 
  display: none; 
  position: fixed; 
  z-index: 1001; 
  left: 0; 
  top: 0; 
  width: 100%; 
  height: 100%; 
  background: rgba(100, 27, 46, 0.7); 
  align-items: center; 
  justify-content: center; 
}
.modal-content { 
  background: var(--white); 
  padding: 25px; 
  border-radius: 12px; 
  text-align: center; 
  box-shadow: 0px 6px 15px rgba(0,0,0,0.2); 
  max-width: 400px; 
  width: 90%;
  border: 2px solid var(--cream);
}
.modal-content h3 {
  color: var(--burgundy);
  font-family: 'Times New Roman', serif;
}
.modal-content p {
  color: var(--burgundy);
  font-family: 'Candara', sans-serif;
}
.modal-content button { 
  margin: 8px 5px; 
  padding: 10px 18px; 
  background: var(--coral); 
  color: var(--white); 
  border: none; 
  border-radius: 8px; 
  cursor: pointer; 
  font-family: 'Candara', sans-serif;
  transition: background 0.3s ease;
}
.modal-content button:hover {
  background: var(--burgundy);
}
.modal-content button.secondary {
  background: #6c757d;
}
.modal-content button.secondary:hover {
  background: #5a6268;
}

/* Review form */
.review-form {
  text-align: left;
}
.review-form .form-group {
  margin-bottom: 15px;
}
.review-form label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: var(--burgundy);
}
.review-form .star-rating {
  display: flex;
  gap: 5px;
  margin-bottom: 10px;
}
.review-form .star {
  font-size: 24px;
  color: #ddd;
  cursor: pointer;
  transition: color 0.2s ease;
}
.review-form .star:hover,
.review-form .star.active {
  color: var(--gold);
}
.review-form textarea {
  width: 100%;
  padding: 10px;
  border: 2px solid var(--coral);
  border-radius: 6px;
  font-family: 'Candara', sans-serif;
  resize: vertical;
  min-height: 80px;
}
.review-form textarea:focus {
  outline: none;
  border-color: var(--burgundy);
}

/* Search results info */
.search-info { 
  margin: 15px 0; 
  font-size: 14px; 
  color: var(--coral); 
  font-weight: 500; 
  display: none; 
  font-family: 'Candara', sans-serif;
}

/* Welcome message styling */
.main-content h2 {
  color: var(--dark-burgundy);
  font-family: 'Times New Roman', serif;
  font-size: 1.8em;
  margin-bottom: 20px;
}

.main-content h3 {
  color: var(--burgundy);
  font-family: 'Times New Roman', serif;
  font-size: 1.4em;
  margin-bottom: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .main-content { padding: 20px; }
  .costume-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
  .costume-card { height: 430px; }
  .notification-dropdown {
    width: 300px;
    right: -100px;
  }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h3><?= htmlspecialchars($name) ?></h3>
  <a href="edit_borrower_profile.php">✏️ Edit Profile</a>
  <a href="#" onclick="showReviewsSection()">⭐ My Reviews (<?= count($pastReviews) ?>)</a>
  <a href="#">📅 Joined <?= $joined_days ?> days ago</a>

  <div class="rental-requests">
    <h4>📬 My Rental Requests</h4>
    <?php
    $activeRequests = [];
    $completedRequests = [];
    foreach($requests as $req){
        $req['status'] = strtolower($req['status'] ?? 'pending');
        $req['payment_status'] = strtolower($req['payment_status'] ?? '');
        if($req['status'] === 'completed'){
            $completedRequests[] = $req;
        } else { $activeRequests[] = $req; }
    }
    if(empty($activeRequests)) echo "<p>No active rental requests.</p>";
    foreach($activeRequests as $req):
        $status = htmlspecialchars($req['status']);
        $paymentStatus = htmlspecialchars($req['payment_status']);
    ?>
    <div class="costume-card" id="rental-<?= $req['id'] ?>">
        <h5><?= htmlspecialchars($req['costume_title']) ?></h5>
        <p>Status: <span class="status <?= $status ?>" id="status-<?= $req['id'] ?>"><?= ucfirst($status) ?></span></p>
        <p>Qty: <?= intval($req['quantity']) ?> | ₹<?= number_format($req['total_price'],2) ?></p>

        <?php if($status === 'accepted' && $paymentStatus !== 'paid'): ?>
            <button class="button" onclick="payNow(<?= $req['id'] ?>, <?= $req['total_price'] ?>, '<?= addslashes($req['costume_title']) ?>')">💰 Pay Now</button>
        <?php elseif($status === 'paid'): ?>
            <span class="button disabled">✅ Paid (Waiting Dispatch)</span>
        <?php elseif($status === 'dispatched'): ?>
            <button class="button" onclick="updateStatus(<?= $req['id'] ?>,'delivered')">📦 Confirm Delivery</button>
        <?php elseif($status === 'delivered'): ?>
            <button class="button" onclick="updateStatus(<?= $req['id'] ?>,'returned')">🔄 Return Item</button>
        <?php elseif($status === 'returned'): ?>
            <span class="button disabled">📦 Returned (Lender Confirmed)</span>
        <?php elseif($status === 'rejected'): ?>
            <span class="button disabled">❌ Rejected</span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if(!empty($completedRequests)): ?>
        <h4 style="margin-top:12px;">✔ Completed Requests</h4>
        <?php foreach($completedRequests as $req): ?>
            <div class="completed-log" id="completed-<?= $req['id'] ?>">
                <span><?= htmlspecialchars($req['costume_title']) ?></span>
                <span class="status completed">Completed</span>
                <?php if(!$req['review_id']): ?>
                    <button class="button review-btn" onclick="openReviewModal(<?= $req['id'] ?>, <?= $req['costume_id'] ?>, '<?= addslashes($req['costume_title']) ?>')">⭐ Review</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Reviews section in sidebar -->
  <div class="reviews-section" id="reviewsSection" style="display:none;">
    <h4>⭐ My Costume Reviews</h4>
    <?php if(empty($pastReviews)): ?>
        <p style="font-size:0.8em;color:var(--cream);">No reviews yet.</p>
    <?php else: foreach($pastReviews as $review): ?>
        <div class="review-item">
            <div class="stars"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5-$review['rating']) ?></div>
            <div class="review-title"><?= htmlspecialchars($review['costume_title']) ?></div>
            <div class="review-shop">from <?= htmlspecialchars($review['shop_name']) ?></div>
            <?php if($review['comment']): ?>
                <div class="review-comment"><?= htmlspecialchars(substr($review['comment'], 0, 60)) . (strlen($review['comment']) > 60 ? '...' : '') ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; endif; ?>
    <a href="#" onclick="hideReviewsSection()" style="font-size:0.8em;margin-top:10px;">← Back to Rentals</a>
  </div>

  <a href="logout.php">🚪 Logout</a>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
  <div class="header-controls">
    <button class="hamburger" onclick="toggleSidebar()">☰</button>
    
    <!-- Notification Bell Component -->
    <div class="notification-container">
      <button class="notification-bell" id="notificationBell" onclick="toggleNotifications()">
        🔔
        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
      </button>
      
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <span>Notifications</span>
        </div>
        <div id="notificationList">
          <div class="notification-empty">No new notifications</div>
        </div>
      </div>
    </div>
  </div>
  
  <h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
  
  <!-- Search Bar -->
  <div class="search-container">
    <input type="text" id="searchBar" class="search-bar" placeholder="Search costumes by keywords (onam, christmas, dance, theme, cultural, halloween, white, saree...)">
    <button class="clear-search" id="clearSearch" onclick="clearSearch()">✕</button>
    <span class="search-icon">🔍</span>
  </div>
  <div class="search-info" id="searchInfo"></div>
  
  <h3>🎭 Costumes</h3>

  <div class="costume-grid" id="costumeGrid">
    <?php if(empty($costumes)): ?>
      <p style="grid-column:1/-1;">No costumes found!</p>
    <?php else: foreach($costumes as $c):
      $statusQty = ($c['quantity'] >= 2) ? "available" : (($c['quantity']==1)?"soon":"unavailable");
    ?>
      <div class="costume-card" data-title="<?= htmlspecialchars(strtolower($c['title'])) ?>" data-description="<?= htmlspecialchars(strtolower($c['description'] ?? '')) ?>">
        <img src="<?= htmlspecialchars($c['image']) ?>" alt="Costume">
        <div class="card-content">
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <div class="rating-display">
            <?php if($c['avg_rating'] > 0): ?>
                <span class="stars"><?= str_repeat('★', floor($c['avg_rating'])) . (($c['avg_rating'] - floor($c['avg_rating'])) >= 0.5 ? '★' : '') . str_repeat('☆', 5 - ceil($c['avg_rating'])) ?></span>
                <span class="rating-text"><?= $c['avg_rating'] ?> (<?= $c['review_count'] ?>)</span>
            <?php else: ?>
                <span class="rating-text">No reviews yet</span>
            <?php endif; ?>
          </div>
          <p>₹<?= htmlspecialchars($c['price_per_day']) ?>/day | Size: <?= htmlspecialchars($c['size']) ?></p>
          <p>Quantity: <?= intval($c['quantity']) ?></p>
          <p class="status <?= $statusQty ?>"><?= $statusQty=="available"?"Available":($statusQty=="soon"?"Soon":"Unavailable") ?></p>
        </div>
        <div class="button-container">
          <?php if($c['quantity']>0): ?>
            <a href="rent_costume.php?id=<?= $c['id'] ?>" class="button">Rent Now</a>
          <?php else: ?>
            <span class="button disabled">Rent Now</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Payment Success Modal -->
<div class="modal" id="paymentSuccessModal">
    <div class="modal-content">
        <h3>✅ Payment Successful!</h3>
        <p>Your payment has been recorded successfully.</p>
        <button onclick="closeModal()">Go to Dashboard</button>
    </div>
</div>

<!-- Review Modal -->
<div class="modal" id="reviewModal">
    <div class="modal-content">
        <h3>⭐ Write a Review</h3>
        <form class="review-form" id="reviewForm">
            <input type="hidden" id="rentalRequestId" name="rental_request_id">
            <input type="hidden" id="costumeId" name="costume_id">
            
            <div class="form-group">
                <label>Rating:</label>
                <div class="star-rating" id="starRating">
                    <span class="star" data-rating="1">★</span>
                    <span class="star" data-rating="2">★</span>
                    <span class="star" data-rating="3">★</span>
                    <span class="star" data-rating="4">★</span>
                    <span class="star" data-rating="5">★</span>
                </div>
                <input type="hidden" id="ratingValue" name="rating" required>
            </div>
            
            <div class="form-group">
                <label for="reviewComment">Comment (optional):</label>
                <textarea id="reviewComment" name="comment" placeholder="Share your experience with this costume..."></textarea>
            </div>
            
            <button type="submit">Submit Review</button>
            <button type="button" class="secondary" onclick="closeReviewModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Notification Modal -->
<div class="notification-modal" id="notificationModal">
  <div class="notification-modal-content">
    <h3 id="modalTitle">Notification</h3>
    <p id="modalMessage"></p>
    <button onclick="closeNotificationModal()">OK</button>
  </div>
</div>

<script>
// Notification System JavaScript
let notificationDropdownOpen = false;
let currentNotifications = [];

// Toggle notification dropdown
function toggleNotifications() {
  const dropdown = document.getElementById('notificationDropdown');
  
  if (notificationDropdownOpen) {
    dropdown.classList.remove('active');
    notificationDropdownOpen = false;
  } else {
    loadNotifications();
    dropdown.classList.add('active');
    notificationDropdownOpen = true;
  }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  const container = document.querySelector('.notification-container');
  if (!container.contains(event.target) && notificationDropdownOpen) {
    document.getElementById('notificationDropdown').classList.remove('active');
    notificationDropdownOpen = false;
  }
});

// Load notifications from server
function loadNotifications() {
  fetch('get_notifications.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        currentNotifications = data.notifications;
        updateNotificationUI(data.notifications, data.count);
      } else {
        console.error('Failed to load notifications:', data.error);
      }
    })
    .catch(error => {
      console.error('Error loading notifications:', error);
    });
}

// Update notification UI
function updateNotificationUI(notifications, count) {
  const badge = document.getElementById('notificationBadge');
  const list = document.getElementById('notificationList');
  
  // Update badge
  if (count > 0) {
    badge.textContent = count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
  
  // Update notification list
  if (notifications.length === 0) {
    list.innerHTML = '<div class="notification-empty">No new notifications</div>';
  } else {
    list.innerHTML = notifications.map(notification => `
      <div class="notification-item unread" onclick="showNotificationModal(${notification.id}, '${escapeHtml(notification.title)}', '${escapeHtml(notification.message)}')">
        <div class="notification-title">${escapeHtml(notification.title)}</div>
        <div class="notification-message">${escapeHtml(notification.message)}</div>
        <div class="notification-time">${formatTime(notification.created_at)}</div>
        <button class="notification-mark-read" onclick="event.stopPropagation(); markAsRead(${notification.id})" title="Mark as read">✓</button>
      </div>
    `).join('');
  }
}

// Show notification modal
function showNotificationModal(notificationId, title, message) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMessage').textContent = message;
  document.getElementById('notificationModal').style.display = 'flex';
  
  // Mark as read when opened
  markAsRead(notificationId);
}

// Close notification modal
function closeNotificationModal() {
  document.getElementById('notificationModal').style.display = 'none';
}

// Mark notification as read
function markAsRead(notificationId) {
  const formData = new FormData();
  formData.append('notification_id', notificationId);
  
  fetch('mark_notification_read.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Reload notifications to update UI
      loadNotifications();
    }
  })
  .catch(error => {
    console.error('Error marking notification as read:', error);
  });
}

// Utility functions
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function formatTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}

// Auto-refresh notifications every 30 seconds
setInterval(function() {
  if (!notificationDropdownOpen) {
    loadNotifications();
  }
}, 30000);

// Load notifications when page loads
document.addEventListener('DOMContentLoaded', function() {
  loadNotifications();
});

// Existing JavaScript functions continue below...

function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
  document.getElementById('mainContent').classList.toggle('shifted');
}

function closeModal(){ document.getElementById('paymentSuccessModal').style.display='none'; }

function showReviewsSection() {
    document.getElementById('reviewsSection').style.display = 'block';
    document.querySelector('.rental-requests').style.display = 'none';
}

function hideReviewsSection() {
    document.getElementById('reviewsSection').style.display = 'none';
    document.querySelector('.rental-requests').style.display = 'block';
}

// Review Modal Functions
function openReviewModal(rentalRequestId, costumeId, costumeTitle) {
    document.getElementById('rentalRequestId').value = rentalRequestId;
    document.getElementById('costumeId').value = costumeId;
    document.getElementById('reviewModal').style.display = 'flex';
    document.querySelector('#reviewModal h3').textContent = '⭐ Review: ' + costumeTitle;
    
    // Reset form
    document.getElementById('reviewForm').reset();
    document.getElementById('ratingValue').value = '';
    document.querySelectorAll('.star').forEach(star => star.classList.remove('active'));
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.getAttribute('data-rating');
            document.getElementById('ratingValue').value = rating;
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseover', function() {
            const rating = this.getAttribute('data-rating');
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.style.color = '#ffd700';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    document.getElementById('starRating').addEventListener('mouseleave', function() {
        const currentRating = document.getElementById('ratingValue').value;
        stars.forEach((s, index) => {
            if (index < currentRating) {
                s.style.color = '#ffd700';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
});

// Review form submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const rating = document.getElementById('ratingValue').value;
    
    if (!rating) {
        alert('Please select a rating');
        return;
    }
    
    fetch('submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Review submitted successfully!');
            closeReviewModal();
            // Remove review button from completed rental
            const rentalId = document.getElementById('rentalRequestId').value;
            const reviewBtn = document.querySelector(`#completed-${rentalId} .review-btn`);
            if (reviewBtn) {
                reviewBtn.remove();
            }
            // Refresh page to show updated reviews
            location.reload();
        } else {
            alert('❌ ' + (data.error || 'Failed to submit review'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Network error occurred');
    });
});

// --- Search Functionality ---
const searchKeywords = {
  'onam': ['onam', 'kerala', 'traditional', 'south indian', 'festival'],
  'christmas': ['christmas', 'xmas', 'santa', 'festive', 'winter', 'holiday'],
  'dance': ['dance', 'dancing', 'performance', 'stage', 'classical', 'folk'],
  'theme': ['theme', 'themed', 'party', 'costume party', 'fancy dress'],
  'cultural': ['cultural', 'traditional', 'ethnic', 'folk', 'heritage'],
  'halloween': ['halloween', 'scary', 'spooky', 'ghost', 'witch', 'vampire'],
  'white': ['white', 'cream', 'ivory', 'off-white'],
  'saree': ['saree', 'sari', 'traditional wear', 'indian wear', 'ethnic wear']
};

function searchCostumes() {
  const searchTerm = document.getElementById('searchBar').value.toLowerCase().trim();
  const costumes = document.querySelectorAll('.costume-card[data-title]');
  const clearBtn = document.getElementById('clearSearch');
  const searchInfo = document.getElementById('searchInfo');
  
  if (searchTerm === '') {
    // Show all costumes
    costumes.forEach(costume => {
      costume.classList.remove('hidden');
    });
    clearBtn.style.display = 'none';
    searchInfo.style.display = 'none';
    return;
  }
  
  clearBtn.style.display = 'block';
  let visibleCount = 0;
  
  costumes.forEach(costume => {
    const title = costume.getAttribute('data-title');
    const description = costume.getAttribute('data-description') || '';
    let isMatch = false;
    
    // Direct text match
    if (title.includes(searchTerm) || description.includes(searchTerm)) {
      isMatch = true;
    }
    
    // Keyword matching
    if (!isMatch) {
      for (let keyword in searchKeywords) {
        if (searchTerm.includes(keyword) || keyword.includes(searchTerm)) {
          const relatedTerms = searchKeywords[keyword];
          for (let term of relatedTerms) {
            if (title.includes(term) || description.includes(term)) {
              isMatch = true;
              break;
            }
          }
          if (isMatch) break;
        }
      }
    }
    
    // Additional fuzzy matching for common terms
    if (!isMatch) {
      const searchWords = searchTerm.split(' ');
      for (let word of searchWords) {
        if (word.length > 2 && (title.includes(word) || description.includes(word))) {
          isMatch = true;
          break;
        }
      }
    }
    
    if (isMatch) {
      costume.classList.remove('hidden');
      visibleCount++;
    } else {
      costume.classList.add('hidden');
    }
  });
  
  // Show search results info
  searchInfo.style.display = 'block';
  searchInfo.textContent = `Found ${visibleCount} costume${visibleCount !== 1 ? 's' : ''} matching "${searchTerm}"`;
}

function clearSearch() {
  document.getElementById('searchBar').value = '';
  searchCostumes();
}

// Add event listeners for search
document.getElementById('searchBar').addEventListener('input', searchCostumes);
document.getElementById('searchBar').addEventListener('keyup', function(e) {
  if (e.key === 'Escape') {
    clearSearch();
  }
});

// --- Update Request Status ---
function updateStatus(rentalId, newStatus){
    $.post('update_request_status.php',{id:rentalId,status:newStatus},function(res){
        if(!res || !res.success){ alert(res && res.error ? res.error : "Unknown error"); return; }
        var ns = res.new_status;
        $('#status-'+rentalId).text(ns.charAt(0).toUpperCase()+ns.slice(1)).attr('class','status '+ns);
        var card = $('#rental-'+rentalId);
        card.find('.button, .button.disabled').remove();
        if(ns==='paid') card.append('<span class="button disabled">✅ Paid (Waiting Dispatch)</span>');
        else if(ns==='dispatched') card.append('<button class="button" onclick="updateStatus('+rentalId+',\'delivered\')">📦 Confirm Delivery</button>');
        else if(ns==='delivered') card.append('<button class="button" onclick="updateStatus('+rentalId+',\'returned\')">🔄 Return Item</button>');
        else if(ns==='returned') card.append('<span class="button disabled">⏳ Waiting for Lender Confirmation</span>');
        else if(ns==='completed') {
            card.replaceWith('<div class="completed-log" id="completed-'+rentalId+'"><span>'+card.find('h5').text()+'</span><span class="status completed">Completed</span><button class="button review-btn" onclick="openReviewModal('+rentalId+', '+res.costume_id+', \''+card.find('h5').text().replace(/'/g, '\\\'').replace(/"/g, '\\"')+'\')">⭐ Review</button></div>');
        }
        else if(ns==='rejected') card.append('<span class="button disabled">❌ Rejected</span>');
    },'json').fail(function(xhr){ alert("❌ Server error: " + xhr.responseText); });
}

// --- Pay Now with Razorpay ---
function payNow(requestId, amount, title) {
    fetch('create_order.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({request_id: requestId})
    })
    .then(res => res.json())
    .then(data => {
        if(!data || data.error){ alert(data ? data.error : 'Create order failed'); return; }
        const options = {
            "key": "<?php echo $keyId ?>",
            "amount": data.amount,
            "currency": "INR",
            "name": "SlayRent",
            "description": title,
            "order_id": data.order_id,
            "handler": function (response){
                $.post('verify_payment.php',{
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    request_id: requestId
                }, function(res){
                    if(res && res.success){
                        // Show modal
                        document.getElementById('paymentSuccessModal').style.display='flex';
                        // Update UI as Paid
                        $('#status-'+requestId).text(res.new_status.charAt(0).toUpperCase()+res.new_status.slice(1)).attr('class','status '+res.new_status);
                        var card = $('#rental-'+requestId);
                        card.find('.button, .button.disabled').remove();
                        if(res.new_status==='paid') card.append('<span class="button disabled">✅ Paid (Waiting Dispatch)</span>');
                    } else {
                        alert("⚠ " + (res.message || "Payment verification failed"));
                    }
                }, "json").fail(function(xhr){ alert("❌ Server error: "+xhr.responseText); });
            },
            "prefill": {
                "name": "<?php echo addslashes($_SESSION['name'] ?? '') ?>",
                "email": "<?php echo addslashes($_SESSION['email'] ?? '') ?>"
            },
            "theme": { "color": "#BE5B50" }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    }).catch(err => { alert("Network error contacting create_order.php"); console.error(err); });
}
</script>

</body>
</html>
