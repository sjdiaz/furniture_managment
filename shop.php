<?php
session_start();
require "config.php";

// Cart Array-a initialize panroam
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Product-a Cart-la add panra logic
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = (float)$_POST['product_price'];
    
    // Image fallback handling
    $product_image = !empty($_POST['product_image']) ? $_POST['product_image'] : ''; 
    $quantity = (int)$_POST['quantity'];

    // Already cart-la irundha quantity-a update pannum
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = array(
            'name'     => $product_name,
            'price'    => $product_price,
            'image'    => $product_image, // Session-la Image Store aagum
            'quantity' => $quantity
        );
    }
    $message = "Product added to cart!";
}

// Search Logic
$search = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $sql = "SELECT * FROM products WHERE stock > 0 AND (name LIKE '%$search%' OR description LIKE '%$search%') ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC";
}

// Database-il irundhu Products-a fetch panroam
$products = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charr Furnitures Hub - Luxury Shop</title>
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
            min-height: 100vh;
            padding: 30px 5%;
            color: #ffffff;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Gold Floating Background Orbs */
        .bg-glow-1 {
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.15) 0%, rgba(27, 38, 59, 0) 70%);
            top: -150px;
            left: -150px;
            border-radius: 50%;
            z-index: -1;
            animation: floatGlow 12s infinite alternate ease-in-out;
        }

        .bg-glow-2 {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(169, 162, 39, 0.12) 0%, rgba(27, 38, 59, 0) 70%);
            bottom: -200px;
            right: -200px;
            border-radius: 50%;
            z-index: -1;
            animation: floatGlow 15s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(70px, 50px) scale(1.15); }
        }

        /* Luxury Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(13, 19, 29, 0.8);
            backdrop-filter: blur(15px);
            padding: 20px 35px;
            border-radius: 20px;
            border: 1px solid rgba(169, 162, 39, 0.35);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
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
            gap: 10px;
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
            position: relative;
        }

        .nav-links a:hover {
            color: #A9A227;
        }

        .cart-link {
            color: #1B263B !important;
            background: #A9A227;
            padding: 9px 20px;
            border-radius: 30px;
            font-weight: 700 !important;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.3);
            transition: all 0.3s ease !important;
        }

        .cart-link:hover {
            background: #8e881f;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(169, 162, 39, 0.5);
        }

        .btn-logout {
            color: #f87171 !important;
        }

        .btn-logout:hover {
            color: #ef4444 !important;
        }

        /* Search Section Styling */
        .search-container {
            background: rgba(13, 19, 29, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(169, 162, 39, 0.25);
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
        }

        .search-form {
            display: flex;
            gap: 12px;
            width: 100%;
            max-width: 600px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            background: rgba(27, 38, 59, 0.8);
            border: 1px solid rgba(169, 162, 39, 0.4);
            border-radius: 30px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #A9A227;
            box-shadow: 0 0 12px rgba(169, 162, 39, 0.4);
        }

        .btn-search {
            background: #A9A227;
            color: #1B263B;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-search:hover {
            background: #8e881f;
            color: #ffffff;
        }

        .btn-reset {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border-color: #f87171;
        }

        /* Alert Notification */
        .alert {
            background: rgba(169, 162, 39, 0.2);
            border: 1px solid #A9A227;
            color: #A9A227;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Grid Setup */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 30px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Luxury Product Card */
        .product-card {
            background: rgba(13, 19, 29, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 22px;
            border: 1px solid rgba(169, 162, 39, 0.25);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: #A9A227;
            box-shadow: 0 18px 35px rgba(169, 162, 39, 0.25);
        }

        .img-wrapper {
            width: 100%;
            height: 200px;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 18px;
            background: #1B263B;
            border: 1px solid rgba(169, 162, 39, 0.15);
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.1);
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            text-transform: capitalize;
        }

        .product-price {
            color: #A9A227;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 18px;
            font-family: 'Cinzel', serif;
        }

        /* Quantity Box */
        .qty-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .qty-wrapper label {
            font-size: 13px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .qty-input {
            width: 70px;
            padding: 8px;
            background: #A9A227;
            color: #1B263B;
            border: 1px solid #A9A227;
            border-radius: 8px;
            font-weight: 700;
            outline: none;
            text-align: center;
            transition: all 0.3s ease;
        }

        .qty-input:focus {
            box-shadow: 0 0 10px rgba(169, 162, 39, 0.6);
        }

        /* Gold Shine Button */
        .btn-add {
            position: relative;
            background: #A9A227;
            color: #1B263B;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(169, 162, 39, 0.2);
        }

        .btn-add::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transition: 0.5s;
        }

        .btn-add:hover::before {
            left: 100%;
        }

        .btn-add:hover {
            background: #8e881f;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(169, 162, 39, 0.4);
        }

        .no-products {
            text-align: center;
            grid-column: 1 / -1;
            padding: 50px;
            color: #94a3b8;
        }

        .no-products i {
            font-size: 50px;
            color: #A9A227;
            margin-bottom: 15px;
        }

        /* Enhanced Mobile Responsive Styling */
        @media (max-width: 768px) {
            body {
                padding: 15px 10px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 15px 12px;
            }

            .header h1 {
                font-size: 20px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
                width: 100%;
            }

            .nav-links a {
                font-size: 12px;
                padding: 4px 6px;
            }

            .cart-link {
                padding: 7px 15px;
            }

            .search-container {
                padding: 12px;
            }

            .search-form {
                flex-direction: column;
                gap: 10px;
            }

            .search-input, .btn-search, .btn-reset {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
                gap: 18px;
            }

            .product-card {
                padding: 15px;
            }

            .img-wrapper {
                height: 180px;
            }
        }
    </style>
</head>
<body>

<!-- Ambient Gold Glows -->
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<!-- Header with Brand Name & Navigation -->
<div class="header">
    <h1>🛋️ Charr Furnitures Hub</h1>
    <div class="nav-links">
        <a href="shop.php" style="color:#A9A227;">Home</a>
        <a href="shop.php">Shop / Products</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <a href="cart.php" class="cart-link"><i class="fa-solid fa-cart-shopping"></i> Cart (<?= count($_SESSION['cart']) ?>)</a>
        <a href="my_orders.php">My Orders</a>
        <a href="index.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<!-- Simple Search Bar -->
<div class="search-container">
    <form method="GET" action="shop.php" class="search-form">
        <input type="text" name="search" class="search-input" placeholder="Search furniture (e.g. Sofa, Chair, Table...)" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        <?php if (!empty($search)): ?>
            <a href="shop.php" class="btn-reset"><i class="fa-solid fa-xmark"></i> Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if (isset($message)): ?>
    <div class="alert"><i class="fa-solid fa-circle-check"></i> <?= $message ?></div>
<?php endif; ?>

<div class="products-grid">
    <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($row = $products->fetch_assoc()): ?>
            <div class="product-card">
                <div>
                    <div class="img-wrapper">
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/products/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-img">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?q=80&w=500&auto=format&fit=crop" alt="Furniture" class="product-img">
                        <?php endif; ?>
                    </div>
                    <h3 class="product-title"><?= htmlspecialchars($row['name']) ?></h3>
                    <div class="product-price">Rs. <?= number_format($row['price'], 2) ?></div>
                </div>

                <form method="POST" action="shop.php">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($row['name']) ?>">
                    <input type="hidden" name="product_price" value="<?= $row['price'] ?>">
                    
                    <!-- Dynamic Image Input Field -->
                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($row['image']) ?>"> 
                    
                    <div class="qty-wrapper">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" class="qty-input">
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="btn-add"><i class="fa-solid fa-plus"></i> Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-products">
            <i class="fa-solid fa-box-open"></i>
            <h3>No products found matching "<?= htmlspecialchars($search) ?>"</h3>
            <p>Try searching with another keyword.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>