<?php
session_start();
require "config.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $check = $conn->prepare("SELECT id FROM customers WHERE email = ? OR phone = ?");
    $check->bind_param("ss", $email, $phone);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email or Phone Number already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $password);
        if ($stmt->execute()) {
            $message = "Registration Successful! Please <a href='index.php' style='color:#1B263B; font-weight:bold;'>Login</a>";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Charr Furnitures Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #1B263B; height: 100vh; margin: 0; display: flex; justify-content: center; align-items: center; color: #ffffff; }
        .card { background: #0d131d; padding: 40px; border-radius: 12px; border: 2px solid #A9A227; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 360px; }
        .card h2 { margin-bottom: 5px; color: #A9A227; text-align: center; font-size: 22px; font-weight: 700; }
        .card p { color: #e2e8f0; font-size: 13px; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #A9A227; display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; background: #A9A227; color: #1B263B; border: 1px solid #A9A227; border-radius: 8px; box-sizing: border-box; font-size: 14px; font-weight: 600; outline: none; }
        .form-group input::placeholder { color: #4a4608; }
        .btn-submit { background: #A9A227; color: #1B263B; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #8e881f; color: #ffffff; }
        .alert-success { background: #A9A227; color: #1B263B; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; font-weight: 600; }
        .alert-error { background: #7f1d1d; color: #fecaca; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        .login-link { margin-top: 15px; font-size: 13px; color: #ffffff; text-align: center; }
        .login-link a { color: #A9A227; text-decoration: underline; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <h2>🛋️ Charr Furnitures Hub</h2>
    <p>Create a new customer account</p>

    <?php if (!empty($message)): ?>
        <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="customer_register.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter Full Name" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter Email" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="Enter Phone Number" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Create Password" required>
        </div>
        <button type="submit" class="btn-submit">Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="index.php">Login Here</a>
    </div>
</div>

</body>
</html>