<?php
session_start();
require "config.php";
require "whatsapp_helper.php";

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit;
}

// Logged-in Customer details fetch
$cust_id = NULL;
$c_name = "";
$c_phone = "";
$c_address = "";

if (isset($_SESSION['customer_id'])) {
    $cust_id = $_SESSION['customer_id'];
    $stmt_user = $conn->prepare("SELECT name, phone, address FROM customers WHERE id = ?");
    $stmt_user->bind_param("i", $cust_id);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if ($row_user = $res_user->fetch_assoc()) {
        $c_name = $row_user['name'];
        $c_phone = $row_user['phone'];
        $c_address = $row_user['address'];
    }
}

// Calculate Order Subtotal & Grand Total
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $clean_price = (float)str_replace(['$', 'Rs', ',', ' '], '', $item['price']);
    $subtotal += $clean_price * $item['quantity'];
}
$shipping = $subtotal > 0 ? 50.00 : 0.00;
$grand_total = $subtotal + $shipping;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = trim($_POST['name']);
    $customer_phone = trim($_POST['phone']);
    $customer_address = trim($_POST['address']);

    $order_date = date("Y-m-d");
    $status = "Pending";

    // Insert Order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, customer_name, phone, address, order_date, status, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssd", $cust_id, $customer_name, $customer_phone, $customer_address, $order_date, $status, $grand_total);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Insert Order Items
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $clean_price = (float)str_replace(['$', 'Rs', ',', ' '], '', $item['price']);
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_item->bind_param("iiid", $order_id, $product_id, $item['quantity'], $clean_price);
            $stmt_item->execute();
        }

        // Send WhatsApp Notification to Customer
        if (function_exists('sendWhatsAppOrderNotification')) {
            sendWhatsAppOrderNotification($customer_phone, $customer_name, $order_id, $grand_total);
        } elseif (function_exists('sendWhatsAppOrder')) {
            sendWhatsAppOrder($customer_phone, $customer_name, $order_id, $grand_total);
        }

        // Clear Cart
        unset($_SESSION['cart']);

        echo "<script>alert('Order placed successfully!'); window.location.href='my_orders.php';</script>";
        exit;
    } else {
        $message = "Order placement failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Charr Furnitures Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }

        body { 
            background: #1B263B; 
            color: #ffffff; 
            min-height: 100vh; 
            padding: 30px 5%; 
            position: relative; 
            overflow-x: hidden; 
        }

        /* --- Keyframe Animations --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(35px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes neonPulse {
            0% { box-shadow: 0 0 10px rgba(169, 162, 39, 0.3); }
            50% { box-shadow: 0 0 25px rgba(169, 162, 39, 0.6); }
            100% { box-shadow: 0 0 10px rgba(169, 162, 39, 0.3); }
        }

        /* --- Header Styling --- */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(13, 19, 29, 0.85); backdrop-filter: blur(15px);
            padding: 20px 35px; border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.35); 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .header h1 { font-family: 'Cinzel', serif; font-size: 24px; color: #A9A227; }
        
        .nav-links { display: flex; align-items: center; gap: 15px; }
        .nav-links a { color: #ffffff; text-decoration: none; font-weight: 500; font-size: 14px; transition: color 0.3s ease; }
        .nav-links a:hover { color: #A9A227; }

        /* --- Main Layout --- */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            animation: fadeInUp 1s ease-out forwards;
        }

        /* --- Card Styles --- */
        .glass-card {
            background: rgba(13, 19, 29, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.3);
            padding: 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .glass-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            color: #A9A227;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(169, 162, 39, 0.2);
        }

        /* --- Form Elements --- */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; color: #cbd5e1; margin-bottom: 8px; font-weight: 500; }
        
        .form-control {
            width: 100%;
            background: rgba(27, 38, 59, 0.8);
            border: 1px solid rgba(169, 162, 39, 0.4);
            border-radius: 12px;
            padding: 12px 15px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #A9A227;
            box-shadow: 0 0 10px rgba(169, 162, 39, 0.4);
        }

        /* --- Order Summary Lines --- */
        .summary-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px dashed rgba(169, 162, 39, 0.15);
            font-size: 14px; color: #cbd5e1;
        }

        .summary-item .item-title { font-weight: 500; color: #fff; }

        .summary-line {
            display: flex; justify-content: space-between;
            margin-top: 15px; font-size: 14px; color: #cbd5e1;
        }

        .summary-line.total {
            font-size: 18px; font-weight: 700; color: #ffffff;
            border-top: 1px solid rgba(169, 162, 39, 0.3);
            padding-top: 15px; margin-top: 15px;
        }

        .summary-line.total span { color: #A9A227; }

        .btn-confirm {
            width: 100%;
            background: #A9A227;
            color: #1B263B;
            border: none;
            padding: 15px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }

        .btn-confirm:hover {
            background: #c5be2e;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(169, 162, 39, 0.5);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Responsive UI */
        @media (max-width: 768px) {
            body { padding: 15px 10px; }
            .header { flex-direction: column; gap: 15px; text-align: center; }
            .nav-links { flex-wrap: wrap; justify-content: center; }
            .checkout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Header Navigation -->
<div class="header">
    <h1>🛋️ Charr Furnitures Hub</h1>
    <div class="nav-links">
        <a href="shop.php">Home</a>
        <a href="shop.php">Shop / Products</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="cart.php">🛒 Cart (<?= count($_SESSION['cart']) ?>)</a>
        <a href="my_orders.php">My Orders</a>
    </div>
</div>

<!-- Main Checkout Layout -->
<div class="checkout-grid">

    <!-- Form Section -->
    <div class="glass-card">
        <h3><i class="fa-solid fa-address-card"></i> Shipping & Delivery Details</h3>

        <?php if ($message): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label><i class="fa-solid fa-user"></i> Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($c_name) ?>" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-phone"></i> WhatsApp Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($c_phone) ?>" required placeholder="e.g. +94771234567">
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-location-dot"></i> Delivery Address</label>
                <textarea name="address" class="form-control" rows="4" required placeholder="Enter complete home or business delivery address"><?= htmlspecialchars($c_address) ?></textarea>
            </div>

            <button type="submit" class="btn-confirm">
                Confirm & Place Order <i class="fa-solid fa-circle-check"></i>
            </button>
        </form>
    </div>

    <!-- Order Review Section -->
    <div class="glass-card" style="animation: neonPulse 4s infinite ease-in-out; height: fit-content;">
        <h3><i class="fa-solid fa-receipt"></i> Order Summary</h3>

        <div style="max-height: 250px; overflow-y: auto; margin-bottom: 15px; padding-right: 5px;">
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <?php $item_price = (float)str_replace(['$', 'Rs', ',', ' '], '', $item['price']); ?>
                <div class="summary-item">
                    <div>
                        <div class="item-title"><?= htmlspecialchars($item['name']) ?></div>
                        <small style="color: #94a3b8;">Qty: <?= $item['quantity'] ?></small>
                    </div>
                    <div>Rs. <?= number_format($item_price * $item['quantity'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-line">
            <span>Subtotal</span>
            <span>Rs. <?= number_format($subtotal, 2) ?></span>
        </div>

        <div class="summary-line">
            <span>Shipping Fee</span>
            <span>Rs. <?= number_format($shipping, 2) ?></span>
        </div>

        <div class="summary-line total">
            <span>Total Payable</span>
            <span>Rs. <?= number_format($grand_total, 2) ?></span>
        </div>
    </div>

</div>

</body>
</html>