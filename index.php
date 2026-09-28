<?php
session_start();
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_phone = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt_admin = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt_admin->bind_param("s", $username_or_phone);
    $stmt_admin->execute();
    $res_admin = $stmt_admin->get_result();

    if ($res_admin->num_rows > 0) {
        $admin = $res_admin->fetch_assoc();
        if (password_verify($password, $admin['password']) || $password == $admin['password']) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $stmt_cust = $conn->prepare("SELECT * FROM customers WHERE name = ? OR phone = ? OR email = ?");
        $stmt_cust->bind_param("sss", $username_or_phone, $username_or_phone, $username_or_phone);
        $stmt_cust->execute();
        $res_cust = $stmt_cust->get_result();

        if ($res_cust->num_rows > 0) {
            $cust = $res_cust->fetch_assoc();
            if (password_verify($password, $cust['password']) || $password == $cust['password']) {
                $_SESSION['user_id'] = $cust['id'];
                $_SESSION['customer_id'] = $cust['id'];
                $_SESSION['customer_name'] = $cust['name'];
                $_SESSION['role'] = 'customer';
                header("Location: shop.php");
                exit;
            } else {
                $error = "Invalid Password!";
            }
        } else {
            $error = "Account not found! Please Register first.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charr Furnitures Hub - Ultra Luxury Login</title>
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1B263B;
            overflow-x: hidden;
            position: relative;
            padding: 20px 10px;
        }

        /* Animated Luxury Floating Gold Orbs Background */
        .bg-glow-1 {
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.25) 0%, rgba(27, 38, 59, 0) 70%);
            top: -100px;
            left: -100px;
            border-radius: 50%;
            animation: floatGlow 10s infinite alternate ease-in-out;
        }

        .bg-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.20) 0%, rgba(27, 38, 59, 0) 70%);
            bottom: -150px;
            right: -150px;
            border-radius: 50%;
            animation: floatGlow 12s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.15); }
        }

        /* Main Glassmorphic Wrapper */
        .login-wrapper {
            position: relative;
            width: 950px;
            max-width: 100%;
            height: 560px;
            background: rgba(13, 19, 29, 0.75);
            backdrop-filter: blur(15px);
            border-radius: 28px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6), inset 0 0 2px rgba(169, 162, 39, 0.5);
            display: flex;
            overflow: hidden;
            border: 1px solid rgba(169, 162, 39, 0.35);
            animation: entranceEntrance 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes entranceEntrance {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Left Side: Animated Furniture Image Showcase */
        .left-side {
            width: 48%;
            height: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 45px;
            color: #ffffff;
            z-index: 1;
        }

        .left-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=1000&auto=format&fit=crop') no-repeat center center/cover;
            animation: slowZoom 18s infinite alternate ease-in-out;
            z-index: -2;
        }

        .left-side::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(27, 38, 59, 0.2) 0%, rgba(13, 19, 29, 0.9) 100%);
            z-index: -1;
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.12); }
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(169, 162, 39, 0.15);
            border: 1px solid rgba(169, 162, 39, 0.4);
            padding: 6px 14px;
            border-radius: 20px;
            color: #A9A227;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            width: fit-content;
            margin-bottom: 15px;
            backdrop-filter: blur(5px);
        }

        .left-side h1 {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            color: #A9A227;
            margin-bottom: 12px;
            line-height: 1.2;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        .left-side p {
            font-size: 13px;
            color: #cbd5e1;
            line-height: 1.7;
            font-weight: 300;
        }

        /* Right Side: Curved Luxury Form Section */
        .right-side {
            width: 58%;
            height: 100%;
            background: #1B263B;
            position: absolute;
            right: 0;
            top: 0;
            border-radius: 160px 0 0 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 60px;
            z-index: 2;
            box-shadow: -15px 0 35px rgba(0,0,0,0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-family: 'Cinzel', serif;
            color: #A9A227;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .login-header p {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 6px;
        }

        form {
            width: 100%;
            max-width: 320px;
        }

        .input-box {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
        }

        .input-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #1B263B;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .input-box input {
            width: 100%;
            padding: 13px 18px 13px 48px;
            background: #A9A227;
            color: #1B263B;
            border: 2px solid #A9A227;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            outline: none;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .input-box input::placeholder {
            color: #3d3b07;
            font-weight: 500;
        }

        .input-box input:focus {
            background: #c2ba2b;
            box-shadow: 0 0 20px rgba(169, 162, 39, 0.6);
            transform: translateY(-2px);
        }

        .input-box input:focus + i {
            transform: translateY(-50%) scale(1.15);
        }

        .btn-login {
            position: relative;
            width: 100%;
            padding: 13px;
            background: #A9A227;
            color: #1B263B;
            border: none;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.4s ease;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(169, 162, 39, 0.3);
            letter-spacing: 0.5px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transition: 0.6s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            background: #8e881f;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(169, 162, 39, 0.5);
        }

        .alert-error {
            background: rgba(127, 29, 29, 0.85);
            color: #fecaca;
            border: 1px solid #ef4444;
            padding: 10px 15px;
            border-radius: 20px;
            margin-bottom: 18px;
            font-size: 12px;
            text-align: center;
            width: 100%;
            max-width: 320px;
            backdrop-filter: blur(5px);
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .bottom-link {
            margin-top: 22px;
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
        }

        .bottom-link a {
            color: #A9A227;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: color 0.3s ease;
        }

        .bottom-link a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #A9A227;
            transition: width 0.3s ease;
        }

        .bottom-link a:hover::after {
            width: 100%;
        }

        /* Mobile & Tablet Responsive Media Query */
        @media (max-width: 900px) {
            body {
                overflow-y: auto;
            }

            .left-side { 
                display: none; 
            }

            .login-wrapper { 
                width: 100%; 
                max-width: 420px; 
                height: auto; 
                border-radius: 20px;
            }

            .right-side { 
                width: 100%; 
                position: relative; 
                border-radius: 0; 
                padding: 40px 25px; 
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="login-wrapper">
        <div class="left-side">
            <div class="brand-badge">
                <i class="fa-solid fa-gem"></i> Premium Collection
            </div>
            <h1>Charr Furnitures Hub</h1>
            <p>Experience the art of luxury living. Premium handcrafted wooden elegance, tailored specifically for your modern home aesthetics.</p>
        </div>

        <div class="right-side">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php" autocomplete="off">
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username / Phone / Email" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <button type="submit" class="btn-login">SIGN IN</button>
            </form>

            <div class="bottom-link">
                Don't have an account? <a href="customer_register.php">Sign up here</a>
            </div>
        </div>
    </div>

</body>
</html>