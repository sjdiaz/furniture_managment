<?php
$page_title = "Products";
require "config.php";
require "auth.php";

// DELETE PRODUCT
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];

    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product && !empty($product["image"])) {
        $imagePath = __DIR__ . "/uploads/products/" . basename($product["image"]);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: products.php");
    exit;
}

// EDIT DATA FETCH
$edit_data = null;
if (isset($_GET["edit"])) {
    $edit_id = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// ADD OR UPDATE PRODUCT
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $supplier = trim($_POST["supplier"]);
    $description = trim($_POST["description"]);
    $buy_price = (float)$_POST["buy_price"];
    $price = (float)$_POST["price"];
    $stock = (int)$_POST["stock"];

    $imageName = "";

    // Image Upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
        $uploadDir = "uploads/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = $_FILES["image"]["name"];
        $tmpName = $_FILES["image"]["tmp_name"];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

        if (in_array($extension, $allowedExtensions)) {
            $imageName = uniqid("product_", true) . "." . $extension;
            move_uploaded_file($tmpName, $uploadDir . $imageName);
        }
    }

    if ($product_id > 0) {
        // Update Existing Product
        if (!empty($imageName)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, supplier=?, description=?, buy_price=?, price=?, stock=?, image=? WHERE id=?");
            $stmt->bind_param("ssssddisi", $name, $category, $supplier, $description, $buy_price, $price, $stock, $imageName, $product_id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, supplier=?, description=?, buy_price=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("ssssddii", $name, $category, $supplier, $description, $buy_price, $price, $stock, $product_id);
        }
    } else {
        // Insert New Product
        $stmt = $conn->prepare("INSERT INTO products (name, category, supplier, description, buy_price, price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddis", $name, $category, $supplier, $description, $buy_price, $price, $stock, $imageName);
    }

    $stmt->execute();
    header("Location: products.php");
    exit;
}

require "header.php";
$list = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>

<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0; padding: 0;
    background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), 
                url("https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?q=80&w=1920&auto=format&fit=crop");
    background-size: cover; background-position: center; background-attachment: fixed;
    font-family: 'Poppins', sans-serif; color: #334155; min-height: 100vh;
}

/* --- ANIMATION KEYFRAMES --- */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes rowFadeIn {
    from {
        opacity: 0;
        transform: translateX(-15px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes shakeBtn {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    75% { transform: translateX(3px); }
}

.panel {
    background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px);
    border-radius: 16px; padding: 25px; margin: 25px 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.4);
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.form-grid-container {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 15px;
}
.form-group { display: flex; flex-direction: column; }
.form-group.full-width { grid-column: 1 / -1; }
label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }

input[type="text"], input[type="number"], input[type="file"], textarea {
    width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px;
    font-size: 14px; outline: none; background: #ffffff; box-sizing: border-box; font-family: inherit;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
textarea { resize: vertical; height: 75px; }

input:focus, textarea:focus { 
    border-color: #d35400; 
    box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.2); 
    transform: translateY(-2px);
}

.btn-submit {
    background: #d35400; color: #ffffff; border: none; padding: 12px 24px;
    border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; 
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.25);
}
.btn-submit:hover { 
    background: #e67e22; 
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 18px rgba(211, 84, 0, 0.4);
}

.btn-cancel {
    background: #64748b; color: #ffffff; text-decoration: none; padding: 12px 24px;
    border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block; margin-left: 10px;
    transition: all 0.3s ease;
}
.btn-cancel:hover { background: #475569; transform: translateY(-2px); }

/* Search Bar Styles */
.search-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    margin-bottom: 15px;
}
.search-box {
    position: relative;
    width: 320px;
}
.search-box input {
    width: 100%;
    padding: 10px 15px 10px 38px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}
.search-box input:focus {
    border-color: #1e293b;
    box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.15);
}
.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}

.products-table {
    width: 100%; border-collapse: collapse; margin-top: 10px; background: #ffffff;
    border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}
.products-table th, .products-table td { padding: 12px 16px; text-align: left; font-size: 14px; vertical-align: middle; }
.products-table th { background-color: #1e293b; color: #ffffff; font-weight: 500; }

.products-table tbody tr { 
    border-bottom: 1px solid #f1f5f9; 
    opacity: 0;
    animation: rowFadeIn 0.5s ease-out forwards;
    transition: all 0.25s ease;
}

.products-table tbody tr:hover {
    background-color: #f8fafc;
    transform: scale(1.005);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Image Hover Zoom Animation */
.product-image { 
    width: 60px; height: 60px; object-fit: cover; border-radius: 8px; 
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.product-image:hover {
    transform: scale(1.2);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

.no-image { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; color: #64748b; font-size: 11px; border-radius: 8px; }

.action-btn { 
    text-decoration: none; font-weight: 500; padding: 6px 12px; border-radius: 6px; 
    display: inline-block; font-size: 13px; transition: all 0.2s ease;
}
.btn-edit { background: #e0f2fe; color: #0284c7; margin-right: 5px; }
.btn-edit:hover { background: #0284c7; color: #fff; transform: translateY(-2px); }

.btn-delete { background: #fee2e2; color: #ef4444; }
.btn-delete:hover { 
    background: #ef4444; color: #fff; 
    animation: shakeBtn 0.3s ease-in-out;
}

.profit-badge { 
    color: #16a34a; font-weight: 600; background: #dcfce7; 
    padding: 4px 8px; border-radius: 6px; font-size: 13px;
    display: inline-block;
    transition: transform 0.2s ease;
}
.profit-badge:hover { transform: scale(1.08); }
</style>

<div class="panel">

<h3 style="margin-top:0; color:#1e293b;"><?= $edit_data ? "Edit Product" : "Add New Product" ?></h3>

<form method="POST" action="products.php" enctype="multipart/form-data">
    <input type="hidden" name="product_id" value="<?= $edit_data ? $edit_data["id"] : 0 ?>">

    <div class="form-grid-container">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($edit_data["name"] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($edit_data["category"] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Supplier Source</label>
            <input type="text" name="supplier" value="<?= htmlspecialchars($edit_data["supplier"] ?? '') ?>" placeholder="e.g. ABC Wood Suppliers" required>
        </div>

        <div class="form-group">
            <label>Cost Price (Buy)</label>
            <input type="number" name="buy_price" step="0.01" value="<?= htmlspecialchars($edit_data["buy_price"] ?? '') ?>" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>Selling Price</label>
            <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($edit_data["price"] ?? '') ?>" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>Stock</label>
            <input type="number" name="stock" value="<?= htmlspecialchars($edit_data["stock"] ?? '') ?>" required>
        </div>

        <div class="form-group full-width">
            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($edit_data["description"] ?? '') ?></textarea>
        </div>

        <div class="form-group full-width">
            <label>Product Photo <?= $edit_data ? "(Optional if not changing)" : "" ?></label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" <?= $edit_data ? "" : "required" ?>>
        </div>
    </div>

    <button type="submit" class="btn-submit"><?= $edit_data ? "Update Product" : "Add Product" ?></button>
    <?php if ($edit_data): ?>
        <a href="products.php" class="btn-cancel">Cancel</a>
    <?php endif; ?>
</form>

<div class="search-container">
    <h3 style="margin:0; color:#1e293b;">Product List</h3>
    <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" onkeyup="searchProducts()" placeholder="Search product, category, supplier...">
    </div>
</div>

<table class="products-table" id="productsTable">
    <thead>
        <tr>
            <th>Photo</th>
            <th>ID</th>
            <th>Name</th>
            <th>Category & Supplier</th>
            <th>Cost Price</th>
            <th>Sell Price</th>
            <th>Profit / Item</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
    <?php 
    $delay = 0.05;
    while ($row = $list->fetch_assoc()): 
        $profit = $row["price"] - $row["buy_price"];
    ?>
        <tr style="animation-delay: <?= $delay ?>s;">
            <td>
                <?php if (!empty($row["image"])): ?>
                    <img src="uploads/products/<?php echo htmlspecialchars($row["image"]); ?>" class="product-image">
                <?php else: ?>
                    <div class="no-image">No Image</div>
                <?php endif; ?>
            </td>
            <td><b>#<?php echo $row["id"]; ?></b></td>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td>
                <b>Category:</b> <?php echo htmlspecialchars($row["category"]); ?><br>
                <small style="color:#64748b;"><b>Supplier:</b> <?php echo htmlspecialchars($row["supplier"] ?? 'N/A'); ?></small>
            </td>
            <td>Rs. <?php echo number_format($row["buy_price"] ?? 0, 2); ?></td>
            <td><b>Rs. <?php echo number_format($row["price"], 2); ?></b></td>
            <td><span class="profit-badge">+Rs. <?php echo number_format($profit, 2); ?></span></td>
            <td><?php echo $row["stock"]; ?></td>
            <td>
                <a href="products.php?edit=<?php echo $row["id"]; ?>" class="action-btn btn-edit">Edit</a>
                <a href="products.php?delete=<?php echo $row["id"]; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
    <?php 
        $delay += 0.05;
    endwhile; 
    ?>
    </tbody>
</table>

</div>

<script>
function searchProducts() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("productsTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var tdName = tr[i].getElementsByTagName("td")[2];
        var tdCatSup = tr[i].getElementsByTagName("td")[3];
        if (tdName || tdCatSup) {
            var nameValue = tdName.textContent || tdName.innerText;
            var catSupValue = tdCatSup.textContent || tdCatSup.innerText;
            if (nameValue.toLowerCase().indexOf(filter) > -1 || catSupValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php require "footer.php"; ?>