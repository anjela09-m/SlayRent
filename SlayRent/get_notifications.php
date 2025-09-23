<?php
// get_notifications.php
session_start();
include 'includes/config.php';
include 'includes/notification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_type = $_SESSION['user_type'];

$notifications = getUnreadNotifications($conn, $user_id, $user_type);
$count = count($notifications);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'count' => $count
]);
?>