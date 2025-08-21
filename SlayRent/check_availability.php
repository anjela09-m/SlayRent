<?php
include 'includes/config.php';

if (isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    $isTaken = false;

    if ($field === 'email') {
        // check email in both borrowers and lenders
        $stmt1 = $conn->prepare("SELECT id FROM borrowers WHERE email = ?");
        $stmt1->bind_param("s", $value);
        $stmt1->execute();
        $stmt1->store_result();
        $isTaken = $stmt1->num_rows > 0;
        $stmt1->close();

        if (!$isTaken) {
            $stmt2 = $conn->prepare("SELECT id FROM lenders WHERE email = ?");
            $stmt2->bind_param("s", $value);
            $stmt2->execute();
            $stmt2->store_result();
            $isTaken = $stmt2->num_rows > 0;
            $stmt2->close();
        }
    } elseif ($field === 'college_id') {
        // check college_id only in borrowers
        $stmt = $conn->prepare("SELECT id FROM borrowers WHERE college_id = ?");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        $isTaken = $stmt->num_rows > 0;
        $stmt->close();
    } elseif ($field === 'auth_id') {
        // check Aadhaar only in lenders
        $stmt = $conn->prepare("SELECT id FROM lenders WHERE auth_id = ?");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        $isTaken = $stmt->num_rows > 0;
        $stmt->close();
    }

    echo json_encode(['taken' => $isTaken]);
}
?>
