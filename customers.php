<?php
$page_title = "Customers";
require "config.php";
require "auth.php";

/* =========================
   DELETE CUSTOMER
========================= */
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: customers.php");
    exit;
}

/* =========================
   ADD CUSTOMER
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $address);
    $stmt->execute();

    header("Location: customers.php");
    exit;
}

require "header.php";
$list = $conn->query("SELECT * FROM customers ORDER BY id DESC");
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

/* --- ANIMATIONS KEYFRAMES --- */
@keyframes panelSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
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
    animation: panelSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.form-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;
}
.form-group { display: flex; flex-direction: column; }
.form-group.full-width { grid-column: 1 / -1; }
label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }

input[type="text"], input[type="email"], textarea {
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
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(211, 84, 0, 0.25);
}
.btn-submit:hover {
    background: #e67e22;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 18px rgba(211, 84, 0, 0.4);
}

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
    width: 300px;
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

.customers-table {
    width: 100%; border-collapse: collapse; margin-top: 10px; background: #ffffff;
    border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}
.customers-table th, .customers-table td { padding: 12px 16px; text-align: left; font-size: 14px; vertical-align: middle; }
.customers-table th { background-color: #1e293b; color: #ffffff; font-weight: 500; }

.customers-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.25s ease;
    animation: rowFadeIn 0.5s ease-out forwards;
}

.customers-table tbody tr:hover {
    background-color: #f8fafc;
    transform: scale(1.005);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.btn-delete {
    background: #fee2e2; color: #ef4444; text-decoration: none; font-weight: 500;
    padding: 6px 12px; border-radius: 6px; display: inline-block; font-size: 13px;
    transition: all 0.2s ease;
}
.btn-delete:hover {
    background: #ef4444; color: #fff;
    animation: shakeBtn 0.3s ease-in-out;
}
</style>

<div class="panel">
    <h3 style="margin-top:0; color:#1e293b;">Add New Customer</h3>

    <form method="POST" action="customers.php">
        <div class="form-grid">
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email">
            </div>
            <div class="form-group full-width">
                <label>Address</label>
                <textarea name="address"></textarea>
            </div>
        </div>
        <button type="submit" class="btn-submit">Add Customer</button>
    </form>

    <!-- Search Bar Container -->
    <div class="search-container">
        <h3 style="margin:0; color:#1e293b;">Customer List</h3>
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" onkeyup="searchCustomers()" placeholder="Search customer name or phone...">
        </div>
    </div>

    <table class="customers-table" id="customersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $delay = 0.05;
        while ($row = $list->fetch_assoc()): 
        ?>
            <tr style="animation-delay: <?= $delay ?>s;">
                <td><b>#<?php echo $row["id"]; ?></b></td>
                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                <td><?php echo htmlspecialchars($row["phone"]); ?></td>
                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                <td><?php echo htmlspecialchars($row["address"]); ?></td>
                <td>
                    <a href="customers.php?delete=<?php echo $row["id"]; ?>" class="btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php 
            $delay += 0.05;
        endwhile; 
        ?>
        </tbody>
    </table>
</div>

<!-- Search Functionality JavaScript -->
<script>
function searchCustomers() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var table = document.getElementById("customersTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var tdName = tr[i].getElementsByTagName("td")[1];
        var tdPhone = tr[i].getElementsByTagName("td")[2];
        if (tdName || tdPhone) {
            var nameValue = tdName.textContent || tdName.innerText;
            var phoneValue = tdPhone.textContent || tdPhone.innerText;
            if (nameValue.toLowerCase().indexOf(filter) > -1 || phoneValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php require "footer.php"; ?>