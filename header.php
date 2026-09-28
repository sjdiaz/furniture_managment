<?php require_once "auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Furniture Management System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div class="brand">🪑 <span>Furniture MS</span></div>
    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="customers.php">👥 Customers</a>
        <a href="products.php">🛋 Products</a>
        <a href="orders.php">📦 Orders</a>
        <a href="payments.php">💳 Payments</a>
        <a href="reports.php">📊 Reports</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </nav>
</aside>
<main class="main">
<header class="topbar">
    <div>
        <h2><?= htmlspecialchars($page_title ?? "Dashboard") ?></h2>
    </div>
    <div class="user">👤 <?= htmlspecialchars($_SESSION["user_name"] ?? "User") ?></div>
</header>
<div class="content">
