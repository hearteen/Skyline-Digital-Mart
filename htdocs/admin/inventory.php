<?php
session_start();
include '../includes/db_config.php';
include '../includes/lang.php';

// Check Admin Auth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

// 1. Fetch Categories for Dropdown
$cat_list_query = "SELECT cat_id, cat_name_en, cat_name_ta FROM categories ORDER BY cat_name_en ASC";
$cat_list_res = mysqli_query($conn, $cat_list_query);

// Handle Add Product Logic
if (isset($_POST['add_product'])) {
    $brand_en    = mysqli_real_escape_string($conn, $_POST['brand_en'] ?? '');
    $brand_ta    = mysqli_real_escape_string($conn, $_POST['brand_ta'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $price       = $_POST['price'] ?? 0;
    $stock       = $_POST['stock'] ?? 0;
    $unit_type   = mysqli_real_escape_string($conn, $_POST['unit_type'] ?? 'Pcs');
    $inner_qty   = $_POST['inner_qty'] ?? 1;
    $base_price  = $_POST['base_price'] ?? 0;

    // 2. Get Selected Category Details from ID
    $cat_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $get_cat_sql = "SELECT cat_name_en, cat_name_ta FROM categories WHERE cat_id = '$cat_id'";
    $cat_res = mysqli_query($conn, $get_cat_sql);
    $cat_row = mysqli_fetch_assoc($cat_res);
    
    $cat_en = $cat_row['cat_name_en'];
    $cat_ta = $cat_row['cat_name_ta'];

    // --- Image Upload Logic ---
    $image_path = "default_product.jpg";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../uploads/products/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_ext = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_path = $image_name;
        }
    }

    // Final Insert Query
    $insert_query = "INSERT INTO products 
        (category, brand, price, stock_qty, image_path, description, category_en, category_ta, brand_en, brand_ta, unit_type, inner_qty, base_price) 
        VALUES 
        ('$cat_en', '$brand_en', '$price', '$stock', '$image_path', '$description', '$cat_en', '$cat_ta', '$brand_en', '$brand_ta', '$unit_type', '$inner_qty', '$base_price')";
    
    if (mysqli_query($conn, $insert_query)) {
        $msg = "Product Added Successfully! 🚀";
        header("Refresh:1; url=inventory.php");
    } else {
        $msg = "Error: " . mysqli_error($conn);
    }
}

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory | Merchant Flow</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prod-img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #00f2ff; }
        .full-width { grid-column: span 2; }
        textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; resize: vertical; }
        .container { width: 95%; margin: 0 auto; padding: 20px; }
        .unit-badge { background: #00f2ff33; color: #004494; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; }
        .red-glow-bg { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 6px; }
        .green-glow-bg { background: #f0fdf4; color: #16a34a; padding: 4px 8px; border-radius: 6px; }
    </style>
</head>
<body class="light-theme">

    <?php include 'includes/admin_nav.php'; ?>

    <main class="dashboard-content">
        <div class="container">
            <h2 class="section-title"><i class="fas fa-boxes"></i> Inventory Management</h2>

            <?php if(isset($msg)) echo "<p class='success-alert' style='color: green; font-weight:bold;'>$msg</p>"; ?>

            <div class="admin-card card-glow">
                <h3><i class="fas fa-plus-circle"></i> Add New Product</h3>
                <form action="" method="POST" enctype="multipart/form-data" class="inventory-form">
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>Brand Name (English)</label>
                            <input type="text" name="brand_en" placeholder="e.g. Clinic Plus" required>
                        </div>
                        <div class="input-group">
                            <label>பிராண்ட் பெயர் (தமிழ்)</label>
                            <input type="text" name="brand_ta" placeholder="எ.கா. கிளினிக் பிளஸ்" required>
                        </div>
                    </div>

                    <div class="form-row" style="background: rgba(0, 242, 255, 0.05); padding: 15px; border-radius: 10px;">
                        <div class="input-group full-width">
                            <label>Select Category (வகை)</label>
                            <select name="category_id" required>
                                <option value="">-- Choose Category --</option>
                                <?php while($c = mysqli_fetch_assoc($cat_list_res)): ?>
                                    <option value="<?php echo $c['cat_id']; ?>">
                                        <?php echo $c['cat_name_en']; ?> (<?php echo $c['cat_name_ta']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small style="color: #64748b;">*Categories are managed in <a href="manage_categories.php">Category Master</a></small>
                        </div>
                    </div>

                    <div class="form-row" style="background: #f0f9ff; padding: 15px; border-radius: 10px; border: 1px dashed #00f2ff; margin-top: 15px;">
                        <div class="input-group">
                            <label>Unit Type</label>
                            <select name="unit_type">
                                <option value="Pcs">Pcs (Single)</option>
                                <option value="Saram">Saram (Strip)</option>
                                <option value="Box">Box</option>
                                <option value="Bundle">Bundle</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Inner Quantity (Pcs inside)</label>
                            <input type="number" name="inner_qty" value="1" min="1" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Wholesale Price (₹)</label>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                        <div class="input-group">
                            <label>Single Piece Rate (₹)</label>
                            <input type="number" name="base_price" step="0.01">
                        </div>
                        <div class="input-group">
                            <label>Initial Stock</label>
                            <input type="number" name="stock" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group full-width">
                            <label>Product Description</label>
                            <textarea name="description" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group full-width">
                            <label>Product Image</label>
                            <input type="file" name="product_image" accept="image/*" required>
                        </div>
                    </div>

                    <button type="submit" name="add_product" class="btn-add">
                        <i class="fas fa-cloud-upload-alt"></i> Save to Inventory
                    </button>
                </form>
            </div>

            <div class="admin-card mt-30">
                <h3><i class="fas fa-list-ul"></i> Current Stock Status</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product (EN / TA)</th>
                                <th>Category</th>
                                <th>Unit Info</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><img src="../uploads/products/<?php echo $row['image_path']; ?>" class="prod-img-thumb" onerror="this.src='../assets/images/placeholder.png'"></td>
                                <td>
                                    <strong><?php echo $row['brand_en']; ?></strong><br>
                                    <small><?php echo $row['brand_ta']; ?></small>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: #64748b;">
                                        <?php echo $row['category_en']; ?><br>
                                        <?php echo $row['category_ta']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="unit-badge"><?php echo $row['unit_type']; ?></span><br>
                                    <small>(1 = <?php echo $row['inner_qty']; ?> Pcs)</small>
                                </td>
                                <td><strong>₹<?php echo number_format($row['price'], 2); ?></strong></td>
                                <td>
                                    <span class="stock-badge <?php echo ($row['stock_qty'] < 10) ? 'red-glow-bg' : 'green-glow-bg'; ?>">
                                        <?php echo $row['stock_qty']; ?>
                                    </span>
                                </td>
                                <td><a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="edit-icon"><i class="fas fa-edit"></i></a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="admin-footer" style="text-align: center; padding: 20px; color: #64748b;">
        <p>&copy; 2026 Merchant Flow | High-Fidelity Solutions</p>
    </footer>

</body>
</html>