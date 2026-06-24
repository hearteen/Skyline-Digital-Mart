<?php
session_start();
include '../includes/db_config.php';
include '../includes/lang.php';

// Check Admin Auth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

// Handle Delete Product Logic
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Optional: Image-ayum folder la irundhu delete panna indha logic use pannalam
    $img_res = mysqli_query($conn, "SELECT image_path FROM products WHERE product_id = '$id'");
    $img_row = mysqli_fetch_assoc($img_res);
    if($img_row['image_path'] != 'default_product.jpg') {
        unlink("../uploads/products/" . $img_row['image_path']);
    }

    $del_query = "DELETE FROM products WHERE product_id = '$id'";
    if (mysqli_query($conn, $del_query)) {
        header("Location: stock_status.php?msg=deleted");
    } else {
        header("Location: stock_status.php?msg=error");
    }
}

// Fetch All Products for Stock Monitoring
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY stock_qty ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Status | Merchant Flow</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prod-img-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid var(--cyan-glow); }
        .container { width: 95%; margin: 0 auto; padding: 20px; }
        .low-stock-row { background: rgba(239, 68, 68, 0.05); }
        
        /* Action Buttons Styling */
        .btn-edit { color: #0ea5e9; margin-right: 15px; font-size: 1.1rem; transition: 0.3s; }
        .btn-edit:hover { color: #00f2ff; text-shadow: 0 0 8px #00f2ff; }
        .btn-delete { color: #ef4444; font-size: 1.1rem; transition: 0.3s; border: none; background: none; cursor: pointer; }
        .btn-delete:hover { color: #ff0000; text-shadow: 0 0 8px #ff0000; }
        
        .msg-alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .msg-deleted { background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; }
    </style>
</head>

<body class="light-theme">

    <?php include 'includes/admin_nav.php'; ?>

    <main class="dashboard-content">
        <div class="container">
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="msg-alert msg-deleted">Product Deleted Successfully!</div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 class="section-title" style="margin: 0;"><i class="fas fa-chart-line"></i> Current Stock Status</h2>
                <a href="inventory.php" class="btn-add" style="padding: 10px 20px; text-decoration: none;">
                    <i class="fas fa-plus"></i> Add Products
                </a>
            </div>

            <div class="admin-card card-glow">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Details</th>
                                <th>Wholesale Info</th>
                                <th>Available Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($products)): 
                                $is_low = ($row['stock_qty'] < 10);
                            ?>
                            <tr class="<?php echo $is_low ? 'low-stock-row' : ''; ?>">
                                <td>
                                    <img src="../uploads/products/<?php echo $row['image_path'] ?: 'default.jpg'; ?>" class="prod-img-thumb" onerror="this.src='../assets/images/placeholder.png'">
                                </td>
                                <td>
                                    <strong><?php echo $row['brand_en']; ?></strong><br>
                                    <small><?php echo $row['brand_ta']; ?></small>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem;">
                                        ₹<?php echo number_format($row['price'], 2); ?> / <?php echo $row['unit_type']; ?><br>
                                        <i class="fas fa-box-open" style="font-size: 0.7rem;"></i> 1 <?php echo $row['unit_type']; ?> = <?php echo $row['inner_qty']; ?> Pcs
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 1.1rem;"><?php echo $row['stock_qty']; ?></strong>
                                </td>
                                <td>
                                    <?php if($is_low): ?>
                                        <span class="stock-badge red-glow-bg"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
                                    <?php else: ?>
                                        <span class="stock-badge green-glow-bg"><i class="fas fa-check-circle"></i> In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn-edit" title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="stock_status.php?delete_id=<?php echo $row['product_id']; ?>" 
                                       class="btn-delete" 
                                       title="Delete Product"
                                       onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="admin-footer">
        <p>&copy; 2026 Merchant Flow - Admin Control Panel</p>
    </footer>

</body>

<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
    <div id="edit-toast" class="toast-msg" style="display: none; background: #0f172a; color: #00f2ff; padding: 15px 25px; border-radius: 12px; border: 1px solid #00f2ff; box-shadow: 0 0 15px rgba(0, 242, 255, 0.4); font-weight: bold; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle"></i> Product Updated Successfully! 🚀
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toast = document.getElementById("edit-toast");
        toast.style.display = "flex";
        
        // "High-Fidelity" Animation Style
        toast.animate([
            { transform: 'translateX(100%)', opacity: 0 },
            { transform: 'translateX(0)', opacity: 1 }
        ], { duration: 500, easing: 'ease-out' });

        setTimeout(function() {
            toast.style.opacity = "0";
            toast.style.transition = "0.5s";
            setTimeout(() => { toast.style.display = "none"; }, 500);
        }, 3000);
    });
</script>
<?php endif; ?>
</html>