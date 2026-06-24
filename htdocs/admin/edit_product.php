<?php
session_start();
include '../includes/db_config.php';
include '../includes/lang.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$product_res = mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$id'");
$product = mysqli_fetch_assoc($product_res);

if (!$product) { die("Product Not Found!"); }

// Handle Update Logic
if (isset($_POST['update_product'])) {
    // Unga DB column names correct-ah match aaganum
    $brand_en    = mysqli_real_escape_string($conn, $_POST['brand_en'] ?? '');
    $brand_ta    = mysqli_real_escape_string($conn, $_POST['brand_ta'] ?? '');
    $cat_en      = mysqli_real_escape_string($conn, $_POST['cat_en'] ?? '');
    $cat_ta      = mysqli_real_escape_string($conn, $_POST['cat_ta'] ?? '');
    $price       = $_POST['price'] ?? 0;
    $base_price  = $_POST['base_price'] ?? 0;
    $stock       = $_POST['stock'] ?? 0;
    $unit_type   = $_POST['unit_type'] ?? 'Pcs';
    $inner_qty   = $_POST['inner_qty'] ?? 1;
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');

    $image_path = $product['image_path']; 
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../uploads/products/";
        $image_name = time() . "_" . basename($_FILES["product_image"]["name"]);
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_dir . $image_name)) {
            $image_path = $image_name;
        }
    }

    // UPDATE query with exact column names from your list
    $update_query = "UPDATE products SET 
        brand_en='$brand_en', 
        brand_ta='$brand_ta', 
        category_en='$cat_en', 
        category_ta='$cat_ta',
        price='$price', 
        base_price='$base_price',
        stock_qty='$stock', 
        unit_type='$unit_type', 
        inner_qty='$inner_qty', 
        description='$description', 
        image_path='$image_path'
        WHERE product_id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo "<script>window.location.href='stock_status.php?status=updated';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product | Merchant Flow</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-container { width: 90%; max-width: 900px; margin: 30px auto; }
        .form-section { background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #00f2ff; }
        .current-img { width: 120px; border-radius: 10px; border: 2px solid #00f2ff; margin: 10px 0; }
        input, select, textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; margin-top: 5px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
    </style>
</head>
<body class="light-theme">
    <?php include 'includes/admin_nav.php'; ?>

    <main class="dashboard-content">
        <div class="edit-container">
            <h2 class="section-title"><i class="fas fa-edit"></i> Edit Product Detail</h2>
            
            <div class="admin-card card-glow">
                <form action="" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-grid">
                        <div>
                            <label>Brand Name (English)</label>
                            <input type="text" name="brand_en" value="<?php echo $product['brand_en']; ?>" required>
                        </div>
                        <div>
                            <label>பிராண்ட் பெயர் (தமிழ்)</label>
                            <input type="text" name="brand_ta" value="<?php echo $product['brand_ta']; ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Category (English)</label>
                            <input type="text" name="cat_en" value="<?php echo $product['category_en']; ?>" required>
                        </div>
                        <div>
                            <label>வகை (தமிழ்)</label>
                            <input type="text" name="cat_ta" value="<?php echo $product['category_ta']; ?>" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-boxes"></i> Inventory & Wholesale Settings</h4>
                        <div class="form-grid">
                            <div>
                                <label>Unit Type</label>
                                <select name="unit_type">
                                    <option value="Pcs" <?php echo ($product['unit_type'] == 'Pcs') ? 'selected' : ''; ?>>Pcs</option>
                                    <option value="Saram" <?php echo ($product['unit_type'] == 'Saram') ? 'selected' : ''; ?>>Saram</option>
                                    <option value="Box" <?php echo ($product['unit_type'] == 'Box') ? 'selected' : ''; ?>>Box</option>
                                </select>
                            </div>
                            <div>
                                <label>Inner Quantity (How many Pcs in 1 <?php echo $product['unit_type']; ?>?)</label>
                                <input type="number" name="inner_qty" value="<?php echo $product['inner_qty']; ?>">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div>
                                <label>Wholesale Price (Per <?php echo $product['unit_type']; ?>)</label>
                                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
                            </div>
                            <div>
                                <label>Retail Price (Single Pcs Rate)</label>
                                <input type="number" step="0.01" name="base_price" value="<?php echo $product['base_price']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Stock Available</label>
                            <input type="number" name="stock" value="<?php echo $product['stock_qty']; ?>" required>
                        </div>
                        <div>
                            <label>Product Image</label>
                            <input type="file" name="product_image">
                            <img src="../uploads/products/<?php echo $product['image_path']; ?>" class="current-img">
                        </div>
                    </div>

                    <div>
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo $product['description']; ?></textarea>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 15px;">
                        <button type="submit" name="update_product" class="btn-add" style="flex: 2; border: none; cursor: pointer;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="stock_status.php" style="flex: 1; text-align: center; background: #64748b; color: white; padding: 12px; border-radius: 8px; text-decoration: none;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>