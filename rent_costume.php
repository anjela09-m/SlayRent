<?php
session_start();
include 'includes/config.php';

// ✅ Check if borrower is logged in
if (!isset($_SESSION['borrower_id'])) {
    echo "<script>alert('⚠️ Please log in as a borrower to rent a costume.'); window.location.href='login.php';</script>";
    exit();
}

// Costume ID from URL
if (!isset($_GET['id'])) {
    die("Costume not found!");
}
$costume_id = intval($_GET['id']);

// Fetch costume details
$sql = "SELECT * FROM costumes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $costume_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Costume not found!");
}
$costume = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rent Costume</title>
<style>
body { font-family: Arial, sans-serif; background: #ede6f2; margin:0; padding:20px; color:#2f2f2f; }
.rent-box { max-width:500px; margin:auto; padding:20px; background:#fff; border-radius:12px; box-shadow:0px 4px 10px rgba(0,0,0,0.15);}
h2 { color:#7c6e9f; margin-bottom:15px;}
.costume-img { width:100%; border-radius:10px; margin-bottom:15px;}
label { display:block; margin-top:10px; font-weight:bold; }
input[type="date"], input[type="number"] { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:6px; }
.total-price { margin-top:15px; font-size:18px; font-weight:bold; color:#333; }
button { margin-top:20px; background:#7c6e9f; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; width:100%; font-size:16px;}
button:hover { background:#5c4d7d; }
.description { margin:15px 0; font-size:15px; color:#555; line-height:1.5; }
.modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background: rgba(47,47,47,0.7); align-items:center; justify-content:center; }
.modal-content { background:#fff; padding:25px; border-radius:12px; text-align:center; box-shadow:0px 6px 15px rgba(0,0,0,0.2); max-width:350px; }
.modal-content h3 { color:#7c6e9f; margin-bottom:10px; }
.modal-content p { color:#2f2f2f; }
</style>
</head>
<body>

<div class="rent-box">
    <h2>Rent: <?= htmlspecialchars($costume['title']); ?></h2>
    <img src="<?= htmlspecialchars($costume['image']) ?>" alt="Costume" class="costume-img">

    <p><strong>Price per day:</strong> ₹<?= $costume['price_per_day']; ?></p>

    <div class="description">
        <strong>Description:</strong><br>
        <?= nl2br(htmlspecialchars($costume['description'])) ?>
    </div>

    <form id="rentalForm">
        <input type="hidden" name="costume_id" value="<?= $costume['id']; ?>">

        <label>Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>

        <label>End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <label>Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" required>

        <div class="total-price">Total Price: ₹<span id="total">0</span></div>

        <button type="submit">Confirm Request</button>
    </form>
</div>

<!-- Success Modal -->
<div class="modal" id="successModal">
    <div class="modal-content">
        <h3>✅ Request Sent!</h3>
        <p>Your costume rental request was successfully submitted.</p>
    </div>
</div>

<script>
const startDate = document.getElementById('start_date');
const endDate = document.getElementById('end_date');
const totalSpan = document.getElementById('total');
const quantityInput = document.getElementById('quantity');
const pricePerDay = <?= $costume['price_per_day']; ?>;

function calculateTotal() {
    if(startDate.value && endDate.value){
        let start = new Date(startDate.value);
        let end = new Date(endDate.value);
        let qty = parseInt(quantityInput.value) || 1;

        if(end >= start){
            let days = Math.ceil((end - start)/(1000*60*60*24)) + 1;
            let base = pricePerDay * qty;
            let total = 0;

            if(days <= 3){
                total = base;
            } else {
                let extraDays = days - 3;
                // Formula: total = base for 3 days + sum of extras for extra days
                total = base + extraDays * 10 ;
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

// Handle form submission
const form = document.getElementById('rentalForm');
form.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(form);

    fetch('process_rental.php', {
        method:'POST',
        body: formData
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status === "success"){
            document.getElementById('successModal').style.display = "flex";
            setTimeout(()=>{ window.location.href="dashboard_borrower.php"; },2000);
        } else {
            alert(data.message);
        }
    })
    .catch(err=>alert("⚠️ Network error. Please try again."));
});
</script>

</body>
</html>
