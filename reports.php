<?php
$page_title = "Reports";
require "config.php";
require "auth.php";
require "header.php";

// Total Paid Revenue
$revenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) x 
    FROM payments 
    WHERE status='Paid'
")->fetch_assoc()["x"];

// Total Orders
$orders = $conn->query("
    SELECT COUNT(*) x 
    FROM orders
")->fetch_assoc()["x"];

// Pending Orders
$pending = $conn->query("
    SELECT COUNT(*) x 
    FROM orders 
    WHERE status='Pending'
")->fetch_assoc()["x"];

// Total Realized Profit from Sold Items
$profit_query = $conn->query("
    SELECT 
        COALESCE(
            SUM(
                (p.price - COALESCE(p.buy_price, 0)) * oi.quantity
            ), 
            0
        ) AS total_profit 
    FROM order_items oi 
    JOIN products p ON p.id = oi.product_id
");

$profit = $profit_query->fetch_assoc()["total_profit"];

// Best Selling Products with Buying Price, Selling Price and Profit
$top = $conn->query("
    SELECT 
        p.name,
        p.buy_price,
        p.price AS selling_price,
        SUM(oi.quantity) AS qty,
        SUM(oi.subtotal) AS sales,
        SUM(
            (p.price - COALESCE(p.buy_price, 0)) * oi.quantity
        ) AS profit
    FROM order_items oi 
    JOIN products p ON p.id = oi.product_id 
    GROUP BY p.id 
    ORDER BY qty DESC 
    LIMIT 10
");

// Check if min_stock column exists
$has_min_stock = false;

$check_col = $conn->query("
    SHOW COLUMNS FROM products LIKE 'min_stock'
");

if ($check_col && $check_col->num_rows > 0) {
    $has_min_stock = true;
}

// Low Stock Alert
if ($has_min_stock) {

    $low_stock = $conn->query("
        SELECT 
            name, 
            stock, 
            category, 
            COALESCE(min_stock, 10) AS threshold 
        FROM products 
        WHERE stock <= COALESCE(min_stock, 10) 
        ORDER BY stock ASC
    ");

} else {

    $low_stock = $conn->query("
        SELECT 
            name, 
            stock, 
            category, 
            10 AS threshold 
        FROM products 
        WHERE stock <= 10 
        ORDER BY stock ASC
    ");
}

// Monthly Performance
$monthly = $conn->query("
    SELECT 
        DATE_FORMAT(py.payment_date, '%Y-%m') AS month, 
        SUM(py.amount) AS revenue,

        COALESCE(
            SUM(
                (p.price - COALESCE(p.buy_price, 0)) * oi.quantity
            ), 
            0
        ) AS monthly_profit

    FROM payments py

    LEFT JOIN orders o 
        ON py.order_id = o.id

    LEFT JOIN order_items oi 
        ON o.id = oi.order_id

    LEFT JOIN products p 
        ON p.id = oi.product_id

    WHERE py.status='Paid'

    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 12
");
?>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
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

/* --- ANIMATIONS --- */
@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(25px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cardPop {
    0% {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes slideInRow {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes glowPulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.print-btn {
    background: #d35400;
    color: white;
    padding: 11px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3);
}

.print-btn:hover {
    background: #e67e22;
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 18px rgba(211, 84, 0, 0.45);
}

.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.stat {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 12px;
    padding: 22px;
    flex: 1;
    min-width: 200px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.4);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.3s ease;
    animation: cardPop 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.stat:nth-child(1) { animation-delay: 0.1s; }
.stat:nth-child(2) { animation-delay: 0.2s; }
.stat:nth-child(3) { animation-delay: 0.3s; }
.stat:nth-child(4) { animation-delay: 0.4s; }

.stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.3);
}

.stat span {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 500;
}

.stat strong {
    font-size: 22px;
    color: #0f172a;
    font-weight: 700;
}

.stat div {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 28px;
}

.panel {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 25px;
    margin: 25px 0;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.panel h3 {
    margin-top: 0;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 12px;
    font-size: 18px;
    font-weight: 600;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

table th,
table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
    vertical-align: middle;
    font-size: 14px;
}

table th {
    background-color: #1e293b;
    color: #ffffff;
    font-weight: 500;
}

table tbody tr {
    transition: all 0.25s ease;
    animation: slideInRow 0.4s ease-out forwards;
}

table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.profit-text {
    color: #16a34a;
    font-weight: 600;
}

.low-stock-badge {
    background: #fee2e2;
    color: #ef4444;
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    animation: glowPulse 2s infinite;
}

/* Total Profit Row */
.total-profit-row {
    background: #f8fafc;
    font-weight: bold;
}

.total-profit-row td {
    border-top: 2px solid #1e293b;
}

.total-profit {
    color: #16a34a;
    font-size: 18px;
    font-weight: bold;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    table {
        font-size: 13px;
    }

    table th,
    table td {
        padding: 8px 10px;
    }

    .stat {
        min-width: 100%;
    }
}

/* Print Optimization */
@media print {
    body {
        background: white !important;
        color: #000;
    }

    .print-btn,
    .sidebar,
    header {
        display: none !important;
    }

    .panel,
    .stat {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        background: white !important;
        backdrop-filter: none !important;
        animation: none !important;
    }

    table tbody tr {
        animation: none !important;
    }
}
</style>

<div class="report-header">
    <h2 style="color:white; margin:0; font-weight: 600;">
        Reports Analytics
    </h2>
    <button class="print-btn" onclick="window.print()">
        🖨 Print Report
    </button>
</div>

<!-- SUMMARY CARDS -->
<div class="cards">
    <div class="stat">
        <span>Total Orders</span>
        <strong><?= $orders ?></strong>
        <div>📦</div>
    </div>

    <div class="stat">
        <span>Pending Orders</span>
        <strong><?= $pending ?></strong>
        <div>⏳</div>
    </div>

    <div class="stat">
        <span>Total Paid Revenue</span>
        <strong>
            Rs. <?= number_format($revenue, 2) ?>
        </strong>
        <div>💰</div>
    </div>

    <div class="stat">
        <span>Total Profit Earned</span>
        <strong>
            Rs. <?= number_format($profit, 2) ?>
        </strong>
        <div>📈</div>
    </div>
</div>

<!-- BEST SELLING PRODUCTS -->
<div class="panel">
    <h3>
        Best Selling Products & Profit
    </h3>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity Sold</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>Total Sales</th>
                <th>Profit Earned</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $delay = 0.04;
        while ($r = $top->fetch_assoc()): 
        ?>
            <tr style="animation-delay: <?= $delay ?>s;">
                <td>
                    <b><?= htmlspecialchars($r["name"]) ?></b>
                </td>
                <td>
                    <?= $r["qty"] ?>
                </td>
                <td>
                    Rs. <?= number_format($r["buy_price"], 2) ?>
                </td>
                <td>
                    Rs. <?= number_format($r["selling_price"], 2) ?>
                </td>
                <td>
                    Rs. <?= number_format($r["sales"], 2) ?>
                </td>
                <td class="profit-text">
                    +Rs. <?= number_format($r["profit"], 2) ?>
                </td>
            </tr>
        <?php 
            $delay += 0.04;
        endwhile; 
        ?>
        </tbody>

        <!-- TOTAL PROFIT -->
        <tfoot>
            <tr class="total-profit-row">
                <td colspan="5" style="text-align:right;">
                    Total Profit Earned
                </td>
                <td class="total-profit">
                    Rs. <?= number_format($profit, 2) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- LOW STOCK -->
<div class="panel">
    <h3>
        ⚠️ Low Stock Alert (Re-order Needed)
    </h3>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th>Threshold Limit</th>
                <th>Current Stock Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($low_stock && $low_stock->num_rows > 0): ?>
            <?php 
            $delay_ls = 0.04;
            while ($ls = $low_stock->fetch_assoc()): 
            ?>
                <tr style="animation-delay: <?= $delay_ls ?>s;">
                    <td>
                        <b><?= htmlspecialchars($ls["name"]) ?></b>
                    </td>
                    <td>
                        <?= htmlspecialchars($ls["category"]) ?>
                    </td>
                    <td>
                        <?= $ls["threshold"] ?> units
                    </td>
                    <td>
                        <span class="low-stock-badge">
                            <?= $ls["stock"] ?> items remaining
                        </span>
                    </td>
                </tr>
            <?php 
                $delay_ls += 0.04;
            endwhile; 
            ?>
        <?php else: ?>
            <tr>
                <td colspan="4">
                    All products have sufficient stock.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MONTHLY PERFORMANCE -->
<div class="panel">
    <h3>
        Monthly Performance
    </h3>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Revenue</th>
                <th>Monthly Profit</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $delay_m = 0.04;
        while ($r = $monthly->fetch_assoc()): 
        ?>
            <tr style="animation-delay: <?= $delay_m ?>s;">
                <td>
                    <b><?= $r["month"] ?></b>
                </td>
                <td>
                    Rs. <?= number_format($r["revenue"], 2) ?>
                </td>
                <td class="profit-text">
                    Rs. <?= number_format($r["monthly_profit"], 2) ?>
                </td>
            </tr>
        <?php 
            $delay_m += 0.04;
        endwhile; 
        ?>
        </tbody>
    </table>
</div>

<?php require "footer.php"; ?>