<?php
session_start();
require "config.php";

$message = "";
$error = "";

// Contact Form Submit Logic
if (isset($_POST['submit_contact'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $user_message = htmlspecialchars(trim($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($user_message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $user_message);
        
        if ($stmt->execute()) {
            $message = "Thank you $name, your message has been sent successfully!";
        } else {
            $error = "Failed to send message. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Check for User's Previous Messages & Admin Replies
$user_replies = [];
if (isset($_SESSION['user_email'])) {
    $u_email = $_SESSION['user_email'];
    $reply_stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? AND reply IS NOT NULL ORDER BY id DESC");
    $reply_stmt->bind_param("s", $u_email);
    $reply_stmt->execute();
    $user_replies = $reply_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Session illai endraal general view
    $reply_result = $conn->query("SELECT * FROM contact_messages WHERE reply IS NOT NULL AND reply != '' ORDER BY id DESC LIMIT 5");
    if ($reply_result) {
        $user_replies = $reply_result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Charr Furnitures Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        
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

        /* Entrance Keyframe Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseGlow {
            0% { box-shadow: 0 0 15px rgba(169, 162, 39, 0.2); }
            50% { box-shadow: 0 0 25px rgba(169, 162, 39, 0.4); }
            100% { box-shadow: 0 0 15px rgba(169, 162, 39, 0.2); }
        }

        /* Header Navigation */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(13, 19, 29, 0.8); backdrop-filter: blur(15px);
            padding: 20px 35px; border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.35); 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .header h1 { font-family: 'Cinzel', serif; font-size: 24px; color: #A9A227; }
        
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
            transition: color 0.3s ease;
        }
        .nav-links a:hover { color: #A9A227; }

        .cart-link { 
            color: #1B263B !important; 
            background: #A9A227; 
            padding: 8px 18px; 
            border-radius: 20px; 
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
        }
        .cart-link:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(169, 162, 39, 0.4);
        }

        /* Contact Card Animation */
        .contact-card {
            display: grid; grid-template-columns: 1fr 1fr;
            background: rgba(13, 19, 29, 0.75); backdrop-filter: blur(15px);
            border-radius: 24px; border: 1px solid rgba(169, 162, 39, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5); overflow: hidden; margin-bottom: 40px;
            animation: fadeInUp 1s ease-out forwards;
            animation-delay: 0.2s;
            opacity: 0;
        }

        .form-side { padding: 50px 40px; }
        .form-side h2 { font-family: 'Cinzel', serif; font-size: 32px; color: #ffffff; margin-bottom: 10px; }
        .form-side p { color: #94a3b8; font-size: 14px; margin-bottom: 35px; }

        .input-group { margin-bottom: 25px; position: relative; }
        .input-group label { display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 8px; font-weight: 500; }
        
        .input-group input, .input-group textarea {
            width: 100%; background: transparent; border: none;
            border-bottom: 2px solid rgba(169, 162, 39, 0.4); padding: 10px 0;
            color: #ffffff; font-size: 15px; outline: none; transition: all 0.4s ease;
        }

        .input-group input:focus, .input-group textarea:focus { 
            border-bottom-color: #A9A227; 
            padding-left: 8px;
        }

        /* Submit Button Animation */
        .btn-submit {
            background: #A9A227; color: #1B263B; border: none; padding: 14px 30px;
            border-radius: 30px; font-size: 15px; font-weight: 700; cursor: pointer; text-transform: uppercase;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 100%;
        }

        .btn-submit:hover {
            background: #c5be2e;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(169, 162, 39, 0.4);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .info-side { background: rgba(27, 38, 59, 0.5); padding: 50px 40px; border-left: 1px solid rgba(169, 162, 39, 0.2); }
        
        .showroom-image { 
            width: 100%; height: 220px; border-radius: 16px; object-fit: cover; 
            border: 2px solid rgba(169, 162, 39, 0.3); margin-bottom: 30px; 
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }
        .showroom-image:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.6);
        }
        
        .locations-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        
        .location-item {
            transition: transform 0.3s ease;
            padding: 8px;
            border-radius: 8px;
        }
        .location-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.03);
        }
        .location-item h4 { color: #A9A227; font-size: 14px; margin-bottom: 6px; }
        .location-item p { color: #94a3b8; font-size: 11px; }

        /* Admin Replies Section Animations */
        .replies-section {
            background: rgba(13, 19, 29, 0.75); backdrop-filter: blur(15px);
            border-radius: 20px; border: 1px solid rgba(169, 162, 39, 0.3);
            padding: 30px; margin-top: 30px; margin-bottom: 40px;
            animation: fadeInUp 1s ease-out forwards;
            animation-delay: 0.4s;
            opacity: 0;
        }
        .replies-section h3 { font-family: 'Cinzel', serif; color: #A9A227; margin-bottom: 20px; font-size: 20px; }
        
        .reply-item {
            background: rgba(255, 255, 255, 0.05); border-left: 4px solid #A9A227;
            padding: 15px 20px; border-radius: 8px; margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .reply-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(6px);
        }
        
        .user-msg-title { font-size: 13px; color: #94a3b8; margin-bottom: 5px; }
        
        .admin-reply-box { 
            font-size: 14px; color: #ffffff; font-weight: 500; margin-top: 8px; 
            background: rgba(169, 162, 39, 0.15); padding: 12px; border-radius: 6px;
            animation: pulseGlow 3s infinite ease-in-out;
        }

        .alert-success { background: rgba(169, 162, 39, 0.2); border: 1px solid #A9A227; color: #A9A227; padding: 12px; border-radius: 10px; margin-bottom: 20px; animation: fadeInUp 0.5s ease; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 10px; margin-bottom: 20px; animation: fadeInUp 0.5s ease; }

        .footer {
            text-align: center;
            padding: 20px 0 10px;
            border-top: 1px solid rgba(169, 162, 39, 0.2);
            color: #64748b;
            font-size: 12px;
        }

        /* Optimized Mobile Responsive View */
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

            .contact-card {
                grid-template-columns: 1fr;
            }

            .form-side, .info-side {
                padding: 30px 20px;
            }

            .info-side {
                border-left: none;
                border-top: 1px solid rgba(169, 162, 39, 0.2);
            }

            .locations-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-side h2, .info-header h2 {
                font-size: 26px !important;
            }
        }
    </style>
</head>
<body>

<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="header">
    <h1>🛋️ Charr Furnitures Hub</h1>
    <div class="nav-links">
        <a href="shop.php">Home</a>
        <a href="shop.php">Shop / Products</a>
        <a href="about.php">About Us</a>
        <a href="contact.php" style="color: #A9A227; font-weight: 700;">Contact Us</a>
        <a href="cart.php" class="cart-link">🛒 Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
        <a href="my_orders.php">My Orders</a>
    </div>
</div>

<div class="contact-card">
    <div class="form-side">
        <h2>Get in Touch</h2>
        <p>Have an inquiry or some feedback for us? Fill out the form below to contact our team.</p>

        <?php if (!empty($message)): ?>
            <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= $message ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Enter your Name" required autocomplete="off">
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter a valid email address" required autocomplete="off">
            </div>

            <div class="input-group">
                <label>How can we help?</label>
                <textarea name="message" rows="3" placeholder="Write your message here..." required></textarea>
            </div>

            <button type="submit" name="submit_contact" class="btn-submit">Submit</button>
        </form>
    </div>

    <div class="info-side">
        <div class="info-header">
            <h2 style="color: #A9A227; font-family: 'Cinzel', serif; font-size: 32px; margin-bottom: 8px;">Contact Us</h2>
            <p style="color: #cbd5e1; font-size: 14px; margin-bottom: 25px;">Any questions or remarks? Just write us a message!</p>
        </div>

        <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=800&auto=format&fit=crop" class="showroom-image" alt="Charr Luxury Showroom">

        <div class="locations-grid">
            <div class="location-item">
                <h4>Main Showroom</h4>
                <p>45 Luxury Avenue, City Center, Chennai</p>
            </div>
            <div class="location-item">
                <h4>Branch Store</h4>
                <p>163 Craft Street, Design District, Coimbatore</p>
            </div>
            <div class="location-item">
                <h4>Support Hub</h4>
                <p>340 Heritage Road, Madurai</p>
            </div>
        </div>
    </div>
</div>

<!-- Admin Replies Display Box for Users -->
<?php if (!empty($user_replies)): ?>
<div class="replies-section">
    <h3><i class="fa-solid fa-comments"></i> Admin Replies to Inquiries</h3>
    <?php foreach ($user_replies as $rep): ?>
        <div class="reply-item">
            <div class="user-msg-title">
                <strong>Question (by <?= htmlspecialchars($rep['name']) ?>):</strong> "<?= htmlspecialchars($rep['message']) ?>"
            </div>
            <div class="admin-reply-box">
                <i class="fa-solid fa-reply"></i> <strong>Admin Response:</strong> <?= htmlspecialchars($rep['reply']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="footer">
    <p>&copy; <?= date('Y') ?> Charr Furnitures Hub. All Rights Reserved.</p>
</div>

</body>
</html>