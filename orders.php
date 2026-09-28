<?php
$page_title = "Orders";
require "config.php";
require "auth.php";

/* =========================
   UPDATE STATUS
========================= */
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header("Location: orders.php");
    exit;
}

/* =========================
   CREATE ORDER
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_order'])) {
    $customer_id = (int)$_POST['customer_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $order_date = $_POST['order_date'];
    $status = $_POST['status'];

    // Get Product Price
    $p_query = $conn->query("SELECT price FROM products WHERE id = $product_id");
    $product = $p_query->fetch_assoc();
    $price = $product['price'];
    $subtotal = $price * $quantity;

    // Create Order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, status, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $customer_id, $order_date, $status, $subtotal);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert Order Items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    $item_stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $price, $subtotal);
    $item_stmt->execute();

    header("Location: orders.php");
    exit;
}

require "header.php";

// Fetch Dropdown Data
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
$products = $conn->query("SELECT id, name, price FROM products WHERE stock > 0 ORDER BY name ASC");

// Fetch Orders
$orders = $conn->query("
    SELECT o.id, c.name AS customer_name, o.order_date, o.status, o.total 
    FROM orders o 
    JOIN customers c ON c.id = o.customer_id 
    ORDER BY o.id DESC
");
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
@keyframes panelEntrance {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.97);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInRow {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.panel {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    animation: panelEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.panel:nth-child(2) {
    animation-delay: 0.15s;
}

.panel h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #0f172a;
    font-size: 18px;
    text-align: center;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 12px;
    align-items: center;
}

input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
    background: #ffffff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

input:focus, select:focus {
    border-color: #d35400;
    outline: none;
    box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.2);
    transform: translateY(-2px);
}

.btn-create {
    background: #d35400;
    color: #ffffff;
    border: none;
    padding: 11px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.3);
}

.btn-create:hover {
    background: #e67e22;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 18px rgba(211, 84, 0, 0.45);
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.orders-table th, .orders-table td {
    padding: 12px 15px;
    text-align: left;
    font-size: 14px;
    border-bottom: 1px solid #f1f5f9;
}

.orders-table th {
    background-color: #1e293b;
    color: #ffffff;
    font-weight: 500;
}

.orders-table tbody tr {
    transition: all 0.25s ease;
    animation: slideInRow 0.4s ease-out forwards;
}

.orders-table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.status-form {
    display: flex;
    gap: 8px;
    align-items: center;
}

.select-status {
    padding: 6px 8px;
    font-size: 12px;
    border-radius: 6px;
}

.btn-update {
    background: #1e293b;
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.25s ease;
}

.btn-update:hover {
    background: #334155;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(30, 41, 59, 0.25);
}

.badge {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.3s ease;
}

.badge:hover {
    transform: scale(1.08);
}

.badge-completed {
    background: #dcfce7;
    color: #16a34a;
    box-shadow: 0 0 8px rgba(22, 163, 74, 0.2);
}

.badge-pending {
    background: #fef3c7;
    color: #d97706;
    box-shadow: 0 0 8px rgba(217, 119, 6, 0.2);
}

.badge-cancelled {
    background: #fee2e2;
    color: #ef4444;
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.2);
}
</style>

<div class="panel">
    <h3>Create Order</h3>
    <form method="POST" action="orders.php">
        <input type="hidden" name="create_order" value="1">
        <div class="form-grid">
            <select name="customer_id" required>
                <option value="">Select customer</option>
                <?php while ($c = $customers->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="product_id" required>
                <option value="">Select product</option>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Rs. <?= $p['price'] ?>)</option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="quantity" value="1" min="1" required>

            <input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required>

            <select name="status" required>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
            </select>

            <button type="submit" class="btn-create">Create Order</button>
        </div>
    </form>
</div>

<div class="panel">
    <h3>Order List</h3>
    <table class="orders-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Action (Change Status)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $delay = 0.05;
            while ($row = $orders->fetch_assoc()): 
            ?>
            <tr style="animation-delay: <?= $delay ?>s;">
                <td><b>#<?= $row["id"] ?></b></td>
                <td><?= htmlspecialchars($row["customer_name"]) ?></td>
                <td><?= $row["order_date"] ?></td>
                <td>
                    <span class="badge <?= $row['status'] === 'Completed' ? 'badge-completed' : ($row['status'] === 'Pending' ? 'badge-pending' : 'badge-cancelled') ?>">
                        <?= $row["status"] ?>
                    </span>
                </td>
                <td><b>Rs. <?= number_format($row["total"], 2) ?></b></td>
                <td>
                    <form method="POST" action="orders.php" class="status-form">
                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                        <select name="status" class="select-status">
                            <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-update">Update</button>
                    </form>
                </td>
            </tr>
            <?php 
                $delay += 0.05;
            endwhile; 
            ?>
        </tbody>
    </table>
</div>

<?php require "footer.php"; ?>