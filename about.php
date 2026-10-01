<?php
session_start();
require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About me - Charr Furnitures Hub</title>
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
            background: #1B263B;
            color: #ffffff;
            min-height: 100vh;
            padding: 20px 5%;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Backgrounds */
        .bg-glow-1 {
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.15) 0%, rgba(27, 38, 59, 0) 70%);
            top: -100px;
            left: -100px;
            border-radius: 50%;
            z-index: -1;
            animation: floatGlow 10s infinite alternate ease-in-out;
        }

        .bg-glow-2 {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.12) 0%, rgba(27, 38, 59, 0) 70%);
            bottom: -150px;
            right: -150px;
            border-radius: 50%;
            z-index: -1;
            animation: floatGlow 14s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.1); }
        }

        /* Header Navigation */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(13, 19, 29, 0.8);
            backdrop-filter: blur(15px);
            padding: 20px 30px;
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.35);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            0% { opacity: 0; transform: translateY(-30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            color: #A9A227;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
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
        }

        .cart-link {
            color: #1B263B !important;
            background: #A9A227;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 700 !important;
        }

        /* Hero Banner */
        .hero-banner {
            position: relative;
            background: linear-gradient(rgba(13, 19, 29, 0.65), rgba(13, 19, 29, 0.85)), url('https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 320px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border: 1px solid rgba(169, 162, 39, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            margin-bottom: 50px;
            padding: 0 20px;
            animation: zoomIn 1.2s ease-out;
        }

        @keyframes zoomIn {
            0% { opacity: 0; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
        }

        .hero-banner h2 {
            font-family: 'Cinzel', serif;
            font-size: 38px;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .hero-banner span {
            color: #A9A227;
        }

        .hero-banner p {
            color: #cbd5e1;
            font-size: 15px;
            max-width: 600px;
        }

        /* About Section Showcase */
        .about-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
            margin-bottom: 60px;
        }

        /* Floating Overlapping Images */
        .image-container {
            position: relative;
            height: 400px;
        }

        .img-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 70%;
            height: 270px;
            border-radius: 20px;
            object-fit: cover;
            border: 2px solid rgba(169, 162, 39, 0.4);
            box-shadow: 0 15px 30px rgba(0,0,0,0.5);
            animation: floatSlow 6s infinite alternate ease-in-out;
        }

        .img-front {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 65%;
            height: 270px;
            border-radius: 20px;
            object-fit: cover;
            border: 3px solid #A9A227;
            box-shadow: 0 20px 40px rgba(0,0,0,0.7);
            animation: floatFast 5s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatSlow {
            0% { transform: translateY(0); }
            100% { transform: translateY(-15px); }
        }

        @keyframes floatFast {
            0% { transform: translateY(0); }
            100% { transform: translateY(-20px); }
        }

        /* Content Right */
        .about-content h3 {
            color: #A9A227;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .about-content h2 {
            font-family: 'Cinzel', serif;
            font-size: 30px;
            margin-bottom: 18px;
            line-height: 1.3;
        }

        .about-content p {
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(13, 19, 29, 0.6);
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(169, 162, 39, 0.2);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            border-color: #A9A227;
            transform: translateX(5px);
        }

        .feature-item i {
            color: #A9A227;
            font-size: 16px;
            margin-top: 3px;
        }

        .feature-item div h4 {
            font-size: 14px;
            color: #ffffff;
            margin-bottom: 3px;
        }

        .feature-item div p {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 60px;
            text-align: center;
        }

        .stat-card {
            background: rgba(13, 19, 29, 0.7);
            border: 1px solid rgba(169, 162, 39, 0.3);
            backdrop-filter: blur(15px);
            padding: 25px 15px;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #A9A227;
        }

        .stat-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            color: #A9A227;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #cbd5e1;
            font-size: 13px;
        }

        .btn-explore {
            display: inline-block;
            background: #A9A227;
            color: #1B263B;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.3);
        }

        .btn-explore:hover {
            background: #ffffff;
            color: #1B263B;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
        }

        .footer {
            text-align: center;
            padding: 20px 0 10px;
            border-top: 1px solid rgba(169, 162, 39, 0.2);
            color: #64748b;
            font-size: 12px;
        }

        /* Fully Optimized Mobile Responsive View */
        @media (max-width: 900px) {
            body {
                padding: 15px 4%;
            }

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

            .hero-banner {
                height: 280px;
            }

            .hero-banner h2 {
                font-size: 28px;
            }

            .hero-banner p {
                font-size: 13px;
            }

            .about-section {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .image-container {
                height: 300px;
            }

            .img-back {
                height: 210px;
            }

            .img-front {
                height: 210px;
            }

            .about-content h2 {
                font-size: 24px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .stats-section {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .btn-explore {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .image-container {
                height: 250px;
            }

            .img-back, .img-front {
                height: 170px;
            }

            .hero-banner h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<!-- Header Navigation -->
<div class="header">
    <h1>🛋️ Charr Furnitures Hub</h1>
    <div class="nav-links">
        <a href="shop.php">Home</a>
        <a href="shop.php">Shop / Products</a>
        <a href="about.php" style="color: #A9A227; font-weight: 700;">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="cart.php" class="cart-link">🛒 Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
        <a href="my_orders.php">My Orders</a>
        <a href="logout.php" style="color: #f87171;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<!-- Dynamic Hero Banner -->
<div class="hero-banner">
    <h2>Charr Furnitures <span>Is The Future</span></h2>
    <p>We blend timeless craftsmanship with modern aesthetics to build furniture that lasts generations.</p>
</div>

<!-- Main About Showcase -->
<div class="about-section">
    <!-- Overlapping Floating Images -->
    <div class="image-container">
        <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=800&auto=format&fit=crop" class="img-back" alt="Luxury Sofa">
        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=800&auto=format&fit=crop" class="img-front" alt="Modern Chair">
    </div>

    <!-- Right Side Content -->
    <div class="about-content">
        <h3>Why Choose Us?</h3>
        <h2>We're shaping comfort, we make your home's future!</h2>
        <p>At Charr Furnitures Hub, we craft luxury handcrafted pieces designed to match your unique lifestyle. Our goal is to transform ordinary living spaces into extraordinary havens of sophistication.</p>

        <div class="features-grid">
            <div class="feature-item">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <h4>Crafted Design</h4>
                    <p>Handmade precision and luxury styling.</p>
                </div>
            </div>
            <div class="feature-item">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <h4>Professional Quality</h4>
                    <p>Durable hardwood & premium fabrics.</p>
                </div>
            </div>
            <div class="feature-item">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <h4>Easy Customization</h4>
                    <p>Tailored sizes and custom color options.</p>
                </div>
            </div>
            <div class="feature-item">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <h4>Responsive Support</h4>
                    <p>Dedicated assistance for all orders.</p>
                </div>
            </div>
        </div>

        <a href="shop.php" class="btn-explore">Explore Our Products <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</div>

<!-- Key Highlights / Metrics -->
<div class="stats-section">
    <div class="stat-card">
        <h3>1,500+</h3>
        <p>Happy Clients Served</p>
    </div>
    <div class="stat-card">
        <h3>100%</h3>
        <p>Solid Hardwood Quality</p>
    </div>
    <div class="stat-card">
        <h3>10+</h3>
        <p>Years of Legacy Craftsmanship</p>
    </div>
</div>

<div class="footer">
    <p>&copy; <?= date('Y') ?> Charr Furnitures Hub. All Rights Reserved.</p>
</div>

</body>
</html>