<?php
// mark_notification_read.php
session_start();
include 'includes/config.php';
include 'includes/notification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized or invalid method']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$notification_id = intval($_POST['notification_id'] ?? 0);

if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
    exit;
}

markNotificationAsRead($conn, $notification_id, $user_id);

echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
?>