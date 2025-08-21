<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['admin'])) {
    exit('Unauthorized');
}

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'dashboard') {
    // Fetch costumes
    $costumeQuery = "SELECT * FROM costumes ORDER BY id DESC";
    $costumes = mysqli_query($conn, $costumeQuery);
    ?>
    <h1>🎭 Costume Listings</h1>
    <?php while ($row = mysqli_fetch_assoc($costumes)) { ?>
        <div class="card">
            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
            <h4><?= htmlspecialchars($row['title']) ?></h4>
            <p>₹<?= $row['price_per_day'] ?>/day</p>
            <form method="POST" action="delete_costume.php" onsubmit="return confirm('Are you sure you want to delete this costume?');">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="delete-btn">🗑 Delete</button>
            </form>
        </div>
    <?php }
} elseif ($page === 'orders') {
    // Fetch lender orders summary
    $lenderOrdersQuery = "
        SELECT l.id AS lender_id, l.shop_name,
               COUNT(rr.id) AS total_orders,
               SUM(rr.status='pending') AS pending_orders,
               SUM(rr.status='accepted') AS accepted_orders
        FROM lenders l
        LEFT JOIN costumes c ON c.lender_id = l.id
        LEFT JOIN rental_requests rr ON rr.costume_id = c.id
        GROUP BY l.id
        ORDER BY l.id DESC
    ";
    $lenderOrders = mysqli_query($conn, $lenderOrdersQuery);
    ?>
    <h1>📦 Lender Orders Summary</h1>
    <table>
        <tr>
            <th>Lender Shop</th>
            <th>Total Orders</th>
            <th>Pending Orders</th>
            <th>Accepted Orders</th>
        </tr>
        <?php while ($lender = mysqli_fetch_assoc($lenderOrders)) { ?>
            <tr>
                <td><?= htmlspecialchars($lender['shop_name']) ?></td>
                <td><?= $lender['total_orders'] ?></td>
                <td class="status-pending"><?= $lender['pending_orders'] ?></td>
                <td class="status-accepted"><?= $lender['accepted_orders'] ?></td>
            </tr>
        <?php } ?>
    </table>
    <?php
} elseif ($page === 'transactions') {
    echo "<h1>💰 Transactions Page (Coming Soon)</h1>";
}
?>
