<?php
$page_title = "Payments";
require "config.php";
require "auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = (int)$_POST["order_id"]; 
    $date = $_POST["payment_date"];
    $amount = (float)$_POST["amount"]; 
    $method = $_POST["method"]; 
    $status = $_POST["status"];

    $stmt = $conn->prepare("INSERT INTO payments(order_id, payment_date, amount, method, status) VALUES(?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $order_id, $date, $amount, $method, $status); 
    $stmt->execute();

    header("Location: payments.php"); 
    exit;
}

require "header.php";

$orders = $conn->query("SELECT id, total FROM orders WHERE status!='Cancelled' ORDER BY id DESC");
$payments = $conn->query("SELECT p.*, o.total order_total, c.name customer FROM payments p JOIN orders o ON o.id=p.order_id JOIN customers c ON c.id=o.customer_id ORDER BY p.id DESC");
?>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* Background Image Setup */
body {
    margin: 0;
    padding: 0;
    background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), 
                url("https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=1920&auto=format&fit=crop");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: 'Poppins', sans-serif;
    color: #334155;
    min-height: 100vh;
}

/* --- ANIMATION KEYFRAMES --- */
@keyframes panelEntrance {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.97);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInRow {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Glassmorphism Panel */
.panel {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 25px;
    margin: 25px 0;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    animation: panelEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.panel:nth-child(2) {
    animation-delay: 0.15s;
}

.panel h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1e293b;
    font-weight: 600;
    font-size: 20px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

/* Responsive Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-grid select,
.form-grid input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    box-sizing: border-box;
    font-family: inherit;
}

.form-grid select:focus,
.form-grid input:focus {
    border-color: #d35400;
    box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.2);
    transform: translateY(-2px);
}

/* Primary Button Styling */
.btn.primary {
    background: #d35400;
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3);
}

.btn.primary:hover {
    background: #e67e22;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 18px rgba(211, 84, 0, 0.45);
}

/* Modern Payments Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

table th,
table td {
    padding: 14px 16px;
    text-align: left;
    font-size: 14px;
    vertical-align: middle;
}

table th {
    background-color: #1e293b;
    color: #ffffff;
    font-weight: 500;
}

table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.25s ease;
    animation: slideInRow 0.4s ease-out forwards;
}

table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Status Badge Styles */
.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
    transition: all 0.3s ease;
}

.badge:hover {
    transform: scale(1.08);
}

/* Payment Status Colors */
.badge-paid {
    background-color: #dcfce7;
    color: #15803d;
    box-shadow: 0 0 8px rgba(21, 128, 61, 0.2);
}

.badge-partial {
    background-color: #fef3c7;
    color: #b45309;
    box-shadow: 0 0 8px rgba(180, 83, 9, 0.2);
}

.badge-pending {
    background-color: #fee2e2;
    color: #b91c1c;
    box-shadow: 0 0 8px rgba(185, 28, 28, 0.2);
}
</style>

<div class="panel">
<h3>Record Payment</h3>
<form method="POST" class="form-grid">
    <select name="order_id" required>
        <option value="">Select order</option>
        <?php while($o = $orders->fetch_assoc()): ?>
            <option value="<?= $o["id"] ?>">Order #<?= $o["id"] ?> - Rs. <?= number_format($o["total"], 2) ?></option>
        <?php endwhile; ?>
    </select>
    
    <input type="date" name="payment_date" value="<?= date("Y-m-d") ?>" required>
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    
    <select name="method">
        <option>Cash</option>
        <option>Card</option>
        <option>Bank Transfer</option>
        <option>Online</option>
    </select>
    
    <select name="status">
        <option>Paid</option>
        <option>Partial</option>
        <option>Pending</option>
    </select>
    
    <button class="btn primary" type="submit">Save Payment</button>
</form>
</div>

<div class="panel">
<h3>Payment History</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Order</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $delay = 0.05;
    while($p = $payments->fetch_assoc()): 
    ?>
        <tr style="animation-delay: <?= $delay ?>s;">
            <td><b>#<?= $p["id"] ?></b></td>
            <td>#<?= $p["order_id"] ?></td>
            <td><?= htmlspecialchars($p["customer"]) ?></td>
            <td><?= $p["payment_date"] ?></td>
            <td><b>Rs. <?= number_format($p["amount"], 2) ?></b></td>
            <td><?= htmlspecialchars($p["method"]) ?></td>
            <td>
                <?php 
                    $statusClass = 'badge-pending';
                    $statusLower = strtolower($p["status"]);
                    if ($statusLower === 'paid') $statusClass = 'badge-paid';
                    elseif ($statusLower === 'partial') $statusClass = 'badge-partial';
                ?>
                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($p["status"]) ?></span>
            </td>
        </tr>
    <?php 
        $delay += 0.05;
    endwhile; 
    ?>
    </tbody>
</table>
</div>

<?php require "footer.php"; ?>