<?php
session_start();
include 'includes/config.php';

// Costume ID from URL
if (!isset($_GET['id'])) {
    die("Costume not found!");
}
$costume_id = intval($_GET['id']);

// Fetch costume details
$sql = "SELECT * FROM costumes WHERE id = $costume_id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Costume not found!");
}
$costume = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent Costume</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fdf5fa;
            margin: 0;
            padding: 20px;
        }
        .rent-box {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #e190ba;
            margin-bottom: 15px;
        }
        .costume-img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="date"], input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .total-price {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        button {
            margin-top: 20px;
            background: #e190ba;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background: #c46e9d;
        }
        .description {
            margin: 15px 0;
            font-size: 15px;
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="rent-box">
        <h2>Rent: <?php echo $costume['title']; ?></h2>
        <img src="<?= htmlspecialchars($costume['image']) ?>" alt="Costume" class="costume-img">

        <p><strong>Price per day:</strong> ₹<?php echo $costume['price_per_day']; ?></p>

        <!-- Costume description -->
        <div class="description">
            <strong>Description:</strong><br>
            <?= nl2br(htmlspecialchars($costume['description'])) ?>
        </div>

        <form method="POST" action="process_rental.php">
            <input type="hidden" name="costume_id" value="<?php echo $costume['id']; ?>">

            <label>Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label>End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <!-- Quantity Option -->
            <label>Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" required>

            <div class="total-price">
                Total Price: ₹<span id="total">0</span>
            </div>

            <button type="submit">Confirm Request</button>
        </form>
    </div>

    <script>
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const totalSpan = document.getElementById('total');
        const quantityInput = document.getElementById('quantity');
        const pricePerDay = <?php echo $costume['price_per_day']; ?>;

        function calculateTotal() {
            if (startDate.value && endDate.value) {
                let start = new Date(startDate.value);
                let end = new Date(endDate.value);
                let qty = parseInt(quantityInput.value) || 1;

                if (end >= start) {
                    let days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

                    let basePrice = pricePerDay * qty;
                    let total = 0;

                    if (days <= 3) {
                        total = basePrice; // flat for up to 3 days
                    } else {
                        let extraDays = days - 3;
                        total = basePrice  + 10;
                    }

                    totalSpan.textContent = total;
                } else {
                    totalSpan.textContent = "0";
                }
            }
        }

        startDate.addEventListener('change', calculateTotal);
        endDate.addEventListener('change', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);
    </script>
</body>
</html>
