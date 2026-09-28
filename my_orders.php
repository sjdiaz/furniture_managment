<?php
session_start();
require "config.php";

// User or Customer Login Check
if (!isset($_SESSION['user_id']) && !isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

// Get the authenticated customer ID safely
$customer_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'];

// Secure Query Execution using Prepared Statements
$orders = false;

// Try fetching using 'customer_id'
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
if ($stmt) {
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $orders = $stmt->get_result();
    
    // Fallback to 'user_id' column if no rows found or query didn't match schema
    if (!$orders || $orders->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
        if ($stmt) {
            $stmt->bind_param("s", $customer_id);
            $stmt->execute();
            $orders = $stmt->get_result();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders History - Charr Furnitures Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #0B111E;
            min-height: 100vh;
            padding: 30px 5%;
            color: #ffffff;
            position: relative;
            overflow-x: hidden;
        }

        .neon-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
            background: radial-gradient(circle at 50% 50%, #1B263B 0%, #0B111E 100%);
        }

        .smoke-wave {
            position: absolute;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(60px);
            opacity: 0.45;
            animation: morphWave 12s infinite alternate ease-in-out;
        }

        .wave-gold {
            top: -10%;
            left: -10%;
            width: 650px;
            height: 650px;
            background: linear-gradient(135deg, rgba(169, 162, 39, 0.8), rgba(255, 230, 0, 0.2));
            box-shadow: 0 0 100px rgba(169, 162, 39, 0.6);
        }

        .wave-blue {
            bottom: -15%;
            right: -10%;
            width: 750px;
            height: 750px;
            background: linear-gradient(135deg, rgba(27, 38, 59, 0.9), rgba(0, 180, 216, 0.3));
            animation-duration: 16s;
            animation-delay: -3s;
        }

        .wave-accent {
            top: 40%;
            right: 20%;
            width: 450px;
            height: 450px;
            background: linear-gradient(135deg, rgba(169, 162, 39, 0.4), rgba(46, 204, 113, 0.25));
            animation-duration: 10s;
            animation-delay: -5s;
        }

        @keyframes morphWave {
            0% {
                transform: rotate(0deg) scale(1) translate(0, 0);
                border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            }
            50% {
                transform: rotate(180deg) scale(1.15) translate(30px, -20px);
                border-radius: 60% 40% 30% 70% / 50% 30% 70% 40%;
            }
            100% {
                transform: rotate(360deg) scale(1) translate(0, 0);
                border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            }
        }

        .glow-particle {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #A9A227;
            border-radius: 50%;
            box-shadow: 0 0 15px #A9A227, 0 0 30px #A9A227;
            animation: floatParticle 8s infinite ease-in-out;
        }

        .p1 { top: 25%; left: 15%; animation-delay: 0s; }
        .p2 { top: 70%; left: 80%; animation-delay: 2s; }
        .p3 { top: 85%; left: 25%; animation-delay: 4s; }

        @keyframes floatParticle {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
            50% { transform: translateY(-40px) scale(1.8); opacity: 0.9; }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(13, 19, 29, 0.75);
            backdrop-filter: blur(20px);
            padding: 20px 35px;
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6), 0 0 20px rgba(169, 162, 39, 0.15);
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            0% { opacity: 0; transform: translateY(-30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            color: #A9A227;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 0 12px rgba(169, 162, 39, 0.5);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            color: #A9A227;
            text-shadow: 0 0 10px rgba(169, 162, 39, 0.8);
        }

        .cart-link {
            color: #1B263B !important;
            background: #A9A227;
            padding: 9px 20px;
            border-radius: 30px;
            font-weight: 700 !important;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.4);
            transition: all 0.3s ease !important;
        }

        .cart-link:hover {
            background: #8e881f;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(169, 162, 39, 0.6);
        }

        .active-link {
            color: #A9A227 !important;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(169, 162, 39, 0.6);
        }

        .btn-logout {
            color: #f87171 !important;
        }

        .btn-logout:hover {
            color: #ef4444 !important;
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
        }

        .orders-container {
            background: rgba(13, 19, 29, 0.75);
            backdrop-filter: blur(25px);
            border-radius: 20px;
            padding: 35px;
            border: 1px solid rgba(169, 162, 39, 0.35);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.7), 0 0 30px rgba(169, 162, 39, 0.15);
            animation: fadeInUp 1s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            color: #A9A227;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(169, 162, 39, 0.25);
            padding-bottom: 15px;
            text-shadow: 0 0 10px rgba(169, 162, 39, 0.4);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            position: relative;
            z-index: 1;
        }

        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            margin-top: 10px;
        }

        .orders-table th {
            background: #A9A227;
            color: #1B263B;
            font-family: 'Cinzel', serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
            padding: 16px 20px;
            text-align: left;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.3);
        }

        .orders-table th:first-child {
            border-radius: 12px 0 0 12px;
        }

        .orders-table th:last-child {
            border-radius: 0 12px 12px 0;
        }

        .orders-table tbody tr {
            background: rgba(27, 38, 59, 0.65);
            backdrop-filter: blur(10px);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .orders-table tbody tr:hover {
            background: rgba(27, 38, 59, 0.95);
            transform: translateY(-4px) scale(1.008);
            box-shadow: 0 10px 30px rgba(169, 162, 39, 0.35), 0 0 20px rgba(169, 162, 39, 0.2);
            border-color: #A9A227;
        }

        .orders-table td {
            padding: 18px 20px;
            font-size: 15px;
            border-top: 1px solid rgba(169, 162, 39, 0.18);
            border-bottom: 1px solid rgba(169, 162, 39, 0.18);
            color: #e2e8f0;
        }

        .orders-table td:first-child {
            border-left: 1px solid rgba(169, 162, 39, 0.18);
            border-radius: 12px 0 0 12px;
            font-weight: 700;
            color: #ffffff;
        }

        .orders-table td:last-child {
            border-right: 1px solid rgba(169, 162, 39, 0.18);
            border-radius: 0 12px 12px 0;
        }

        .order-price {
            color: #A9A227;
            font-weight: 700;
            font-family: 'Cinzel', serif;
            font-size: 16px;
            text-shadow: 0 0 8px rgba(169, 162, 39, 0.4);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
            letter-spacing: 0.3px;
        }

        .status-pending {
            background: rgba(169, 162, 39, 0.15);
            color: #A9A227;
            border: 1px solid rgba(169, 162, 39, 0.5);
            box-shadow: 0 0 12px rgba(169, 162, 39, 0.3);
        }

        .status-completed {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.5);
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.3);
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
        }

        .no-orders {
            text-align: center;
            padding: 50px 20px;
            color: #94a3b8;
        }

        .no-orders i {
            font-size: 50px;
            color: #A9A227;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(169, 162, 39, 0.5));
        }

        .btn-shop-now {
            display: inline-block;
            margin-top: 20px;
            background: #A9A227;
            color: #1B263B;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.4);
        }

        .btn-shop-now:hover {
            background: #8e881f;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(169, 162, 39, 0.6);
        }

        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 15px; text-align: center; }
            .nav-links { flex-wrap: wrap; justify-content: center; }
            .orders-container { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="neon-bg">
    <div class="smoke-wave wave-gold"></div>
    <div class="smoke-wave wave-blue"></div>
    <div class="smoke-wave wave-accent"></div>
    <div class="glow-particle p1"></div>
    <div class="glow-particle p2"></div>
    <div class="glow-particle p3"></div>
</div>

<div class="header">
    <h1>🛋️ Charr Furnitures Hub</h1>
    <div class="nav-links">
        <a href="shop.php">Home</a>
        <a href="shop.php">Shop / Products</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="cart.php" class="cart-link"><i class="fa-solid fa-cart-shopping"></i> Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
        <a href="my_orders.php" class="active-link">My Orders</a>
        <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="orders-container">
    <div class="section-title">
        <i class="fa-solid fa-box-open"></i> My Orders History
    </div>

    <div class="table-responsive">
        <?php if ($orders && $orders->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <?php 
                            $status_class = 'status-pending';
                            $status_icon = 'fa-clock';
                            $status = strtolower($row['status'] ?? 'pending');

                            if ($status == 'completed' || $status == 'delivered') {
                                $status_class = 'status-completed';
                                $status_icon = 'fa-circle-check';
                            } elseif ($status == 'cancelled') {
                                $status_class = 'status-cancelled';
                                $status_icon = 'fa-circle-xmark';
                            }
                        ?>
                        <tr>
                            <td>#<?= htmlspecialchars($row['id']) ?></td>
                            <td class="order-price">Rs. <?= number_format((float)($row['total_amount'] ?? $row['total_price'] ?? 0), 2) ?></td>
                            <td><?= date("d M Y, h:i A", strtotime($row['created_at'] ?? $row['order_date'] ?? 'now')) ?></td>
                            <td>
                                <span class="status-badge <?= $status_class ?>">
                                    <i class="fa-solid <?= $status_icon ?>"></i> <?= ucfirst($status) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                <i class="fa-solid fa-receipt"></i>
                <p>You haven't placed any orders yet.</p>
                <a href="shop.php" class="btn-shop-now">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>