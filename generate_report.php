<?php
session_start();
require 'includes/config.php';

// ✅ Only admin can access
if (!isset($_SESSION['admin'])) {
    exit("Unauthorized");
}

// Filter by type (week or month)
$filter = $_GET['filter'] ?? 'month'; // default monthly

if ($filter === 'week') {
    $query = "SELECT p.*, l.shop_name 
              FROM payments p
              JOIN rental_requests rr ON p.rental_request_id=rr.id
              JOIN lenders l ON rr.lender_id=l.id
              WHERE p.status='paid' AND YEARWEEK(p.created_at) = YEARWEEK(NOW())";
} else { // month
    $query = "SELECT p.*, l.shop_name 
              FROM payments p
              JOIN rental_requests rr ON p.rental_request_id=rr.id
              JOIN lenders l ON rr.lender_id=l.id
              WHERE p.status='paid' AND MONTH(p.created_at)=MONTH(NOW()) 
              AND YEAR(p.created_at)=YEAR(NOW())";
}

$res = $conn->query($query);

// Totals
$totalAmount = 0;
$totalCommission = 0;
$transactions = [];

while ($row = $res->fetch_assoc()) {
    $commission = $row['amount'] * 0.1;
    $transactions[] = [
        'id' => $row['id'],
        'shop_name' => $row['shop_name'],
        'amount' => $row['amount'],
        'commission' => $commission,
        'date' => $row['created_at']
    ];
    $totalAmount += $row['amount'];
    $totalCommission += $commission;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate Report - SlayRent</title>
  <style>
    /* Import Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Candara:wght@400;700&display=swap');

    body { 
      font-family: 'Times New Roman', serif; 
      margin: 0; 
      padding: 30px;
      background: linear-gradient(135deg, #BDB93A 0%, #BE5B50 100%);
      min-height: 100vh;
      color: #641B2E;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 15px 40px rgba(100, 27, 46, 0.2);
      border: 3px solid #BE5B50;
      position: relative;
      overflow: hidden;
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #BE5B50, #8A2D3B, #641B2E);
    }

    h2 { 
      font-family: 'Candara', sans-serif;
      font-weight: 700;
      color: #641B2E; 
      font-size: 32px;
      text-align: center;
      margin-bottom: 30px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
    }

    h3 {
      font-family: 'Candara', sans-serif;
      font-weight: 700;
      color: #8A2D3B;
      font-size: 24px;
      margin-bottom: 20px;
      border-bottom: 2px solid #BE5B50;
      padding-bottom: 10px;
    }

    .filter { 
      margin-bottom: 30px; 
      background: linear-gradient(135deg, rgba(189, 185, 58, 0.1), rgba(190, 91, 80, 0.1));
      padding: 25px;
      border-radius: 15px;
      border: 2px solid #BE5B50;
      box-shadow: 0 5px 15px rgba(100, 27, 46, 0.1);
    }

    .filter label {
      font-family: 'Candara', sans-serif;
      font-weight: 700;
      color: #641B2E;
      font-size: 18px;
      margin-right: 15px;
      display: inline-block;
    }

    .filter select {
      font-family: 'Times New Roman', serif;
      padding: 12px 20px;
      border: 2px solid #BE5B50;
      border-radius: 10px;
      background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(189, 185, 58, 0.1));
      color: #641B2E;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 150px;
    }

    .filter select:hover {
      border-color: #8A2D3B;
      box-shadow: 0 4px 12px rgba(190, 91, 80, 0.3);
    }

    .filter select:focus {
      outline: none;
      border-color: #641B2E;
      box-shadow: 0 0 10px rgba(100, 27, 46, 0.4);
    }

    #reportContent {
      background: rgba(255, 255, 255, 0.8);
      padding: 30px;
      border-radius: 15px;
      border: 2px solid #BE5B50;
      box-shadow: 0 8px 25px rgba(100, 27, 46, 0.1);
      margin-bottom: 30px;
    }

    table { 
      width: 100%; 
      border-collapse: collapse; 
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(100, 27, 46, 0.15);
      border: 2px solid #BE5B50;
    }

    th, td { 
      border: 1px solid #BE5B50; 
      padding: 15px; 
      text-align: left;
      font-family: 'Times New Roman', serif;
    }

    th { 
      background: linear-gradient(135deg, #641B2E, #8A2D3B);
      color: #BDB93A;
      font-family: 'Candara', sans-serif;
      font-weight: 700;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    td {
      color: #641B2E;
      font-size: 15px;
      transition: background-color 0.3s ease;
    }

    tr:nth-child(even) {
      background: rgba(189, 185, 58, 0.1);
    }

    tr:hover {
      background: rgba(190, 91, 80, 0.1);
    }

    .summary { 
      margin-top: 30px; 
      font-weight: 700;
      background: linear-gradient(135deg, rgba(100, 27, 46, 0.1), rgba(138, 45, 59, 0.1));
      padding: 25px;
      border-radius: 15px;
      border: 2px solid #8A2D3B;
      box-shadow: 0 6px 20px rgba(138, 45, 59, 0.2);
    }

    .summary p {
      font-family: 'Candara', sans-serif;
      font-size: 18px;
      color: #641B2E;
      margin: 12px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid rgba(190, 91, 80, 0.3);
    }

    .summary p:last-child {
      border-bottom: none;
      font-size: 20px;
      font-weight: 700;
      color: #8A2D3B;
    }

    .download-section {
      text-align: center;
      margin-top: 40px;
      padding: 25px;
      background: linear-gradient(135deg, rgba(189, 185, 58, 0.1), rgba(255, 255, 255, 0.8));
      border-radius: 15px;
      border: 2px solid #BDB93A;
    }

    button {
      background: linear-gradient(135deg, #BE5B50, #8A2D3B);
      color: #BDB93A;
      border: none;
      padding: 15px 30px;
      border-radius: 25px;
      cursor: pointer;
      font-family: 'Candara', sans-serif;
      font-weight: 700;
      font-size: 18px;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      box-shadow: 0 6px 20px rgba(190, 91, 80, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    button:hover { 
      background: linear-gradient(135deg, #641B2E, #BE5B50);
      border: 2px solid #BDB93A;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(100, 27, 46, 0.4);
    }

    button:active {
      transform: translateY(0);
      box-shadow: 0 4px 15px rgba(100, 27, 46, 0.3);
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #8A2D3B;
      font-style: italic;
      font-size: 18px;
    }

    .report-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(190, 91, 80, 0.3);
    }

    .report-header h3 {
      margin: 0;
      border: none;
      padding: 0;
    }

    .report-meta {
      font-family: 'Times New Roman', serif;
      color: #8A2D3B;
      font-size: 14px;
      text-align: right;
    }

    /* Print styles for PDF */
    @media print {
      body {
        background: white !important;
        color: black !important;
      }
      
      .container {
        background: white !important;
        box-shadow: none !important;
        border: none !important;
      }
      
      .download-section {
        display: none !important;
      }
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        padding: 15px;
      }
      
      .container {
        padding: 20px;
      }
      
      h2 {
        font-size: 24px;
        flex-direction: column;
        gap: 10px;
      }
      
      .filter {
        text-align: center;
      }
      
      .filter label {
        display: block;
        margin-bottom: 10px;
      }
      
      table {
        font-size: 12px;
      }
      
      th, td {
        padding: 8px;
      }
      
      .summary p {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>
      <span>📑</span>
      <span>Transaction Report (<?= ucfirst($filter) ?>)</span>
    </h2>

    <!-- Filter -->
    <div class="filter">
      <form method="get">
        <label for="filter">Select report type:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
          <option value="month" <?= $filter==='month'?'selected':'' ?>>This Month</option>
          <option value="week" <?= $filter==='week'?'selected':'' ?>>This Week</option>
        </select>
      </form>
    </div>

    <!-- Report Content -->
    <div id="reportContent">
      <div class="report-header">
        <h3>Transactions</h3>
        <div class="report-meta">
          Generated on: <?= date('M j, Y g:i A') ?><br>
          Period: <?= ucfirst($filter) ?>ly Report
        </div>
      </div>
      
      <table>
        <tr>
          <th>ID</th>
          <th>Shop Name</th>
          <th>Amount</th>
          <th>Commission (10%)</th>
          <th>Date</th>
        </tr>
        <?php if (!empty($transactions)): ?>
          <?php foreach ($transactions as $t): ?>
            <tr>
              <td>#<?= $t['id'] ?></td>
              <td><?= htmlspecialchars($t['shop_name']) ?></td>
              <td>₹<?= number_format($t['amount'], 2) ?></td>
              <td>₹<?= number_format($t['commission'], 2) ?></td>
              <td><?= date('M j, Y g:i A', strtotime($t['date'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="no-data">No transactions found for this <?= $filter ?>.</td></tr>
        <?php endif; ?>
      </table>

      <div class="summary">
        <p>
          <span>Total Transaction Amount:</span>
          <span>₹<?= number_format($totalAmount, 2) ?></span>
        </p>
        <p>
          <span>Total Revenue (Commission Earned):</span>
          <span>₹<?= number_format($totalCommission, 2) ?></span>
        </p>
      </div>
    </div>

    <!-- Download Button -->
    <div class="download-section">
      <button onclick="downloadPDF()">
        <span>⬇️</span>
        <span>Download PDF Report</span>
      </button>
    </div>
  </div>

  <!-- html2pdf.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      const element = document.getElementById("reportContent");
      const opt = {
        margin: 0.5,
        filename: 'SlayRent_Transaction_Report_<?= ucfirst($filter) ?>_<?= date('Y-m-d') ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(element).save();
    }
  </script>
</body>
</html>