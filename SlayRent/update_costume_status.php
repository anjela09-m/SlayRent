<?php
include 'includes/config.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'lender') {
    exit("unauthorized");
}


if (isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'] === 'available' ? 'available' : 'unavailable';

    $stmt = $conn->prepare("UPDATE costumes SET availability=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
