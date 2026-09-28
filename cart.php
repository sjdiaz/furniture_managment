<?php
session_start();
require "config.php";

// Initialize Cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Cart Actions (Remove / Update Quantity / Clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $product_id = $_POST['product_id'] ?? null;

        if ($_POST['action'] === 'remove' && $product_id !== null) {
            unset($_SESSION['cart'][$product_id]);
        } elseif ($_POST['action'] === 'update' && $product_id !== null) {
            $qty = max(1, (int)$_POST['quantity']);
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] = $qty;
            }
        } elseif ($_POST['action'] === 'clear') {
            $_SESSION['cart'] = [];
        }
        header("Location: cart.php");
        exit();
    }
}

// Calculate Total
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $price = (float)str_replace(['$', 'Rs', ',', ' '], '', $item['price']);
    $subtotal += $price * $item['quantity'];
}
$shipping = $subtotal > 0 ? 50.00 : 0.00;
$grand_total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Charr Furnitures Hub</title>
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
            0% { box-shadow: 0 0 10px rgba(169, 162, 39, 0.3), inset 0 0 10px rgba(169, 162, 39, 0.1); }
            50% { box-shadow: 0 0 25px rgba(169, 162, 39, 0.6), inset 0 0 15px rgba(169, 162, 39, 0.3); }
            100% { box-shadow: 0 0 10px rgba(169, 162, 39, 0.3), inset 0 0 10px rgba(169, 162, 39, 0.1); }
        }

        @keyframes speedLineMove {
            0% { transform: translateX(-100%); opacity: 0.1; }
            50% { opacity: 0.8; }
            100% { transform: translateX(100%); opacity: 0.1; }
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(-3deg); }
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

        .nav-links a { 
            color: #ffffff; text-decoration: none; font-weight: 500; font-size: 14px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover { color: #A9A227; }

        .cart-link { 
            color: #1B263B !important; background: #A9A227; padding: 8px 18px; border-radius: 20px; 
            font-weight: 700 !important; transition: all 0.3s ease !important;
        }
        .cart-link:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(169, 162, 39, 0.5); }

        /* --- Custom Neon Speed Hero Banner --- */
        .neon-banner {
            position: relative;
            background: linear-gradient(135deg, rgba(13, 19, 29, 0.9) 0%, rgba(27, 38, 59, 0.8) 100%);
            border: 1px solid rgba(169, 162, 39, 0.4);
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            animation: fadeInUp 0.9s ease-out forwards;
        }

        .neon-banner::before {
            content: '';
            position: absolute;
            top: 50%; left: -50%; width: 200%; height: 2px;
            background: linear-gradient(90deg, transparent, #A9A227, #00f0ff, transparent);
            animation: speedLineMove 3s infinite linear;
        }

        .banner-text h2 {
            font-family: 'Cinzel', serif;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 8px;
            display: flex; align-items: center; gap: 12px;
        }

        .banner-text h2 span { color: #A9A227; text-shadow: 0 0 10px rgba(169, 162, 39, 0.6); }

        .banner-text p { color: #94a3b8; font-size: 14px; }

        .neon-cart-graphic {
            position: relative;
            font-size: 55px;
            color: #A9A227;
            text-shadow: 0 0 15px #A9A227, 0 0 30px #A9A227;
            animation: floatIcon 3s infinite ease-in-out;
        }

        /* --- Cart Layout --- */
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            animation: fadeInUp 1s ease-out forwards;
        }

        .cart-items-card {
            background: rgba(13, 19, 29, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.3);
            padding: 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .cart-items-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            color: #A9A227;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(169, 162, 39, 0.2);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 90px 1fr auto auto;
            align-items: center;
            gap: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(169, 162, 39, 0.15);
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(5px);
            border-color: #A9A227;
            box-shadow: 0 5px 15px rgba(169, 162, 39, 0.2);
        }

        .item-img {
            width: 80px; height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(169, 162, 39, 0.3);
            transition: transform 0.3s ease;
        }

        .cart-item:hover .item-img { transform: scale(1.05); }

        .item-details h4 { font-size: 16px; color: #ffffff; margin-bottom: 4px; }
        .item-details p { font-size: 14px; color: #A9A227; font-weight: 600; }

        .qty-control {
            display: flex; align-items: center; gap: 8px;
            background: rgba(27, 38, 59, 0.8);
            border: 1px solid rgba(169, 162, 39, 0.4);
            border-radius: 20px;
            padding: 4px 10px;
        }

        .qty-control input {
            width: 45px;
            background: transparent;
            border: none;
            color: #ffffff;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            outline: none;
        }

        .btn-update {
            background: none; border: none; color: #A9A227; cursor: pointer; font-size: 13px;
            transition: transform 0.2s;
        }
        .btn-update:hover { transform: scale(1.2); }

        .btn-remove {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: #ef4444;
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* --- Order Summary Card --- */
        .summary-card {
            background: rgba(13, 19, 29, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.35);
            padding: 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
            height: fit-content;
            animation: neonPulse 4s infinite ease-in-out;
        }

        .summary-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            color: #A9A227;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(169, 162, 39, 0.2);
        }

        .summary-line {
            display: flex; justify-content: space-between;
            margin-bottom: 15px; font-size: 14px; color: #cbd5e1;
        }

        .summary-line.total {
            font-size: 18px; font-weight: 700; color: #ffffff;
            border-top: 1px solid rgba(169, 162, 39, 0.3);
            padding-top: 15px; margin-top: 15px;
        }

        .summary-line.total span { color: #A9A227; }

        .btn-checkout {
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
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; justify-content: center; align-items: center; gap: 10px;
            text-decoration: none;
        }

        .btn-checkout:hover {
            background: #c5be2e;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(169, 162, 39, 0.5);
        }

        .empty-cart-box {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-cart-box i {
            font-size: 60px;
            color: #A9A227;
            margin-bottom: 15px;
            opacity: 0.8;
            animation: floatIcon 3s infinite ease-in-out;
        }

        .empty-cart-box p {
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .btn-shop {
            background: #A9A227;
            color: #1B263B;
            padding: 10px 25px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-shop:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(169, 162, 39, 0.4);
        }

        /* --- Mobile Responsive Rules --- */
        @media (max-width: 768px) {
            body { padding: 15px 10px; }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 15px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }

            .nav-links a { font-size: 12px; }

            .neon-banner {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 20px;
            }

            .banner-text h2 {
                font-size: 22px;
                justify-content: center;
            }

            .cart-container {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 12px;
                justify-items: center;
            }

            .item-img {
                width: 100px;
                height: 100px;
            }
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
        <a href="cart.php" class="cart-link">🛒 Cart (<?= count($_SESSION['cart']) ?>)</a>
        <a href="my_orders.php">My Orders</a>
    </div>
</div>

<!-- Animated Neon Speed Hero Banner -->
<div class="neon-banner">
    <div class="banner-text">
        <h2><span>Fast & Secure</span> Shopping Cart</h2>
        <p>Review your luxury furniture items and proceed to instant checkout.</p>
    </div>
    <div class="neon-cart-graphic">
        <i class="fa-solid fa-cart-arrow-down"></i>
    </div>
</div>

<!-- Main Cart Container -->
<div class="cart-container">
    
    <!-- Cart Items Section -->
    <div class="cart-items-card">
        <h3><i class="fa-solid fa-bag-shopping"></i> Selected Items (<?= count($_SESSION['cart']) ?>)</h3>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart-box">
                <i class="fa-solid fa-cart-flatbed"></i>
                <h2>Your Shopping Cart is Empty!</h2>
                <p>Explore our premium collection and add luxury items to your cart.</p>
                <a href="shop.php" class="btn-shop"><i class="fa-solid fa-arrow-left"></i> Go to Shop</a>
            </div>
        <?php else: ?>
            
            <?php foreach ($_SESSION['cart'] as $p_id => $item): ?>
                <?php 
                    // Image Check and Folder Path Fix
                    $img_raw = $item['image'] ?? '';
                    
                    if (!empty($img_raw)) {
                        if (!str_starts_with($img_raw, 'http') && !str_starts_with($img_raw, 'uploads/')) {
                            $img_src = 'uploads/products/' . $img_raw;
                        } else {
                            $img_src = $img_raw;
                        }
                    } else {
                        // Fallback Image
                        $img_src = 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=300';
                    }
                ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="item-img" alt="<?= htmlspecialchars($item['name']) ?>">
                    
                    <div class="item-details">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p>Rs. <?= number_format((float)$item['price'], 2) ?></p>
                    </div>

                    <form method="POST" action="cart.php" class="qty-control">
                        <input type="hidden" name="product_id" value="<?= $p_id ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" onchange="this.form.submit()">
                        <button type="submit" class="btn-update" title="Update Quantity"><i class="fa-solid fa-rotate"></i></button>
                    </form>

                    <form method="POST" action="cart.php">
                        <input type="hidden" name="product_id" value="<?= $p_id ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="btn-remove" title="Remove Item"><i class="fa-solid fa-trash-can"></i></button>
                    </form>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <a href="shop.php" style="color: #A9A227; text-decoration: none; font-size: 14px; font-weight: 500;">
                    <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                </a>
                
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 13px;">
                        <i class="fa-solid fa-broom"></i> Clear Cart
                    </button>
                </form>
            </div>

        <?php endif; ?>
    </div>

    <!-- Order Summary Section -->
    <div class="summary-card">
        <h3><i class="fa-solid fa-receipt"></i> Order Summary</h3>
        
        <div class="summary-line">
            <span>Subtotal</span>
            <span>Rs. <?= number_format($subtotal, 2) ?></span>
        </div>
        
        <div class="summary-line">
            <span>Estimated Shipping</span>
            <span>Rs. <?= number_format($shipping, 2) ?></span>
        </div>

        <div class="summary-line total">
            <span>Total Amount</span>
            <span>Rs. <?= number_format($grand_total, 2) ?></span>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
            <a href="checkout.php" class="btn-checkout">
                Proceed to Checkout <i class="fa-solid fa-bolt"></i>
            </a>
        <?php else: ?>
            <button class="btn-checkout" style="opacity: 0.5; cursor: not-allowed;" disabled>
                Proceed to Checkout
            </button>
        <?php endif; ?>
    </div>

</div>

</body>
</html>