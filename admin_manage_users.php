<?php
session_start();
require 'includes/config.php';

// ✅ Proper admin session check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

// ✅ Optional: See who is logged in (for debugging)
$loggedInEmail = $_SESSION['email'] ?? 'Unknown';

// ✅ Handle messages from delete_user.php
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Users - Admin</title>
  <style>
    body {
      font-family: Arial;
      padding: 20px;
      background: #fefefe;
    }
    h1 {
      color: #333;
    }
    h2 {
      color: #e190ba;
      margin-top: 50px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 40px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #f1b3d3ff;
      color: white;
    }
    form {
      display: inline;
    }
    .delete-btn {
      background: red;
      color: white;
      border: none;
      padding: 5px 10px;
      cursor: pointer;
      border-radius: 5px;
    }
    .message {
      padding: 10px;
      background-color: #e0ffe0;
      border: 1px solid #8bc48b;
      color: #0a570a;
      margin-bottom: 20px;
      width: fit-content;
    }
    .loggedin {
      font-size: 14px;
      color: #555;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <h1>Admin: Manage Users</h1>
  <div class="loggedin">Logged in as: <?= htmlspecialchars($loggedInEmail) ?></div>

  <?php if (!empty($msg)): ?>
    <div class="message"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <h2>Borrowers</h2>
  <table>
    <tr>
      <th>ID</th><th>Name</th><th>Email</th><th>College ID</th><th>Action</th>
    </tr>
    <?php
    $borrowers = mysqli_query($conn, "SELECT * FROM borrowers");
    if (mysqli_num_rows($borrowers) > 0) {
      while ($row = mysqli_fetch_assoc($borrowers)) {
        echo "<tr>
          <td>{$row['id']}</td>
          <td>{$row['name']}</td>
          <td>{$row['email']}</td>
          <td>{$row['college_id']}</td>
          <td>
            <form method='POST' action='delete_user.php'>
              <input type='hidden' name='user_type' value='borrower'>
              <input type='hidden' name='user_id' value='{$row['id']}'>
              <button class='delete-btn' onclick=\"return confirm('Delete this borrower?')\">Delete</button>
            </form>
          </td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='5'>No borrowers found.</td></tr>";
    }
    ?>
  </table>

  <h2>Lenders</h2>
  <table>
    <tr>
      <th>ID</th><th>Shop Name</th><th>Email</th><th>Shop ID</th><th>Action</th>
    </tr>
    <?php
    $lenders = mysqli_query($conn, "SELECT * FROM lenders");
    if (mysqli_num_rows($lenders) > 0) {
      while ($row = mysqli_fetch_assoc($lenders)) {
        echo "<tr>
          <td>{$row['id']}</td>
          <td>{$row['shop_name']}</td>
          <td>{$row['email']}</td>
          <td>
            <form method='POST' action='delete_user.php'>
              <input type='hidden' name='user_type' value='lender'>
              <input type='hidden' name='user_id' value='{$row['id']}'>
              <button class='delete-btn' onclick=\"return confirm('Delete this lender?')\">Delete</button>
            </form>
          </td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='5'>No lenders found.</td></tr>";
    }
    ?>
  </table>

</body>
</html>
