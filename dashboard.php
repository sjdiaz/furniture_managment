<?php
$page_title = "Dashboard";
require "config.php";
require "header.php";

$customers = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()["c"];
$products = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()["c"];
$orders = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()["c"];
$revenue = $conn->query("SELECT COALESCE(SUM(amount),0) total FROM payments WHERE status='Paid'")->fetch_assoc()["total"];

// Total Estimated Profit Calculation
$profit_query = $conn->query("SELECT SUM((price - buy_price) * stock) AS total_profit FROM products");
$profit_data = $profit_query->fetch_assoc();
$total_profit = $profit_data["total_profit"] ?? 0;

// Customer Messages Count Calculation
$messages_query = $conn->query("SELECT COUNT(*) c FROM contact_messages");
$messages_count = $messages_query ? $messages_query->fetch_assoc()["c"] : 0;
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

/* --- ANIMATION KEYFRAMES --- */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulseIcon {
    0% { transform: translateY(-50%) scale(1); }
    50% { transform: translateY(-50%) scale(1.1); }
    100% { transform: translateY(-50%) scale(1); }
}

@keyframes glowBtn {
    0% { box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3); }
    50% { box-shadow: 0 4px 20px rgba(211, 84, 0, 0.6); }
    100% { box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3); }
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.cards .stat {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.4);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    color: inherit;
    
    /* Animation Applied */
    opacity: 0;
    animation: fadeInUp 0.6s ease-out forwards;
}

/* Staggered Delay for Cards */
.cards .stat:nth-child(1) { animation-delay: 0.1s; }
.cards .stat:nth-child(2) { animation-delay: 0.2s; }
.cards .stat:nth-child(3) { animation-delay: 0.3s; }
.cards .stat:nth-child(4) { animation-delay: 0.4s; }
.cards .stat:nth-child(5) { animation-delay: 0.5s; }
.cards .stat:nth-child(6) { animation-delay: 0.6s; }

.cards .stat:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 18px 35px rgba(0, 0, 0, 0.35);
}

.cards .stat:hover div {
    animation: pulseIcon 0.6s infinite ease-in-out;
}

.cards .stat span {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cards .stat strong {
    font-size: 24px;
    color: #0f172a;
    font-weight: 700;
    margin-top: 8px;
    display: block;
}

.cards .stat div {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 38px;
    opacity: 0.85;
    background: rgba(241, 245, 249, 0.6);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.panel {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 30px;
    margin: 25px 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    
    /* Panel Entrance Animation */
    opacity: 0;
    animation: fadeInUp 0.6s ease-out 0.6s forwards;
}

.panel h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #0f172a;
    font-weight: 600;
    font-size: 22px;
}

.panel p {
    color: #475569;
    font-size: 15px;
    margin-bottom: 25px;
    line-height: 1.6;
}

.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.quick-links .btn {
    text-decoration: none;
    padding: 12px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.quick-links .btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.quick-links .btn.primary {
    background: #d35400;
    color: #ffffff;
    border: none;
    animation: glowBtn 2s infinite ease-in-out;
}

.quick-links .btn.primary:hover {
    background: #e67e22;
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 20px rgba(211, 84, 0, 0.45);
}
</style>

<div class="cards">
    <div class="stat"><span>Customers</span><strong><?= $customers ?></strong><div>👥</div></div>
    <div class="stat"><span>Products</span><strong><?= $products ?></strong><div>🛋</div></div>
    <div class="stat"><span>Orders</span><strong><?= $orders ?></strong><div>📦</div></div>
    <div class="stat"><span>Paid Revenue</span><strong>Rs. <?= number_format($revenue,2) ?></strong><div>💰</div></div>
    <div class="stat"><span>Stock Profit Margin</span><strong>Rs. <?= number_format($total_profit,2) ?></strong><div>📈</div></div>
    
    <!-- Customer Messages Stat Card -->
    <a href="admin_messages.php" class="stat">
        <span>Messages</span>
        <strong><?= $messages_count ?></strong>
        <div>📩</div>
    </a>
</div>

<div class="panel">
    <h3>Furniture Management System</h3>
    <p>Manage customers, furniture products, supplier purchasing details, orders, payments, customer messages and reports from one place.</p>
    <div class="quick-links">
        <a class="btn primary" href="customers.php">Add Customer</a>
        <a class="btn" href="products.php">Manage Products</a>
        <a class="btn" href="orders.php">Create Order</a>
        <a class="btn" href="admin_messages.php">View Messages</a>
        <a class="btn" href="reports.php">View Reports</a>
    </div>
</div>

<?php require "footer.php"; ?>