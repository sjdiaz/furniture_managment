<?php
session_start();
require "config.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM customers WHERE phone = '$phone'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            header("Location: shop.php");
            exit;
        } else {
            $message = "Invalid Password!";
        }
    } else {
        $message = "Customer not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Login</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #fff; padding: 30px; border-radius: 10px; width: 350px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #0f172a; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Customer Login</h2>
    <?php if (isset($_GET['registered'])): ?><p style="color:green;">Account created! Please Login.</p><?php endif; ?>
    <?php if ($message): ?><p style="color:red;"><?= $message ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login to Shop</button>
    </form>
    <p style="text-align:center;">New Customer? <a href="customer_register.php">Register here</a></p>
</div>
</body>
</html>