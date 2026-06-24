<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php'; // Language file include panniyaachu

// Check if ID is provided in URL
if (!isset($_GET['id'])) {
    die("Error: Order ID missing!");
}

$lang = $_SESSION['lang'] ?? 'en'; // Current language fetch
$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// SQL-la brand_$lang use panni dynamic-ah name fetch panrom
$items_query = "SELECT oi.*, p.brand_$lang AS brand_name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = '$order_id'";

$items_res = mysqli_query($conn, $items_query);

if (!$items_res) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Order Details | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f172a; font-family: 'Inter', sans-serif; color: #fff; padding: 20px; }
        .details-card {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 242, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }
        .cyan-text { color: #00f2ff; text-shadow: 0 0 10px rgba(0, 242, 255, 0.5); }
        .admin-table { width: 100%; border-collapse: collapse; color: #fff; margin-top: 20px; }
        .admin-table th { text-align: left; border-bottom: 1px solid #334155; padding: 12px; color: #94a3b8; }
        .admin-table td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .back-btn {
            background: transparent;
            border: 1px solid #00f2ff;
            color: #00f2ff;
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background: #00f2ff;
            color: #00f2ff; /* Maintaining the glow feel */
            color: #0f172a;
            box-shadow: 0 0 15px #00f2ff;
        }
    </style>
</head>
<body>

    <div class="details-card">
        <h2 class="cyan-text">
            <i class="fas fa-box-open"></i> 
            <?= ($lang == 'ta') ? 'ஆர்டர் விவரங்கள்' : 'Order Items'; ?>: #ORD-<?php echo $order_id; ?>
        </h2>
        <hr style="border: 0.1px solid #334155; margin-bottom: 20px;">

        <table class="admin-table">
            <thead>
                <tr>
                    <th><?php echo $languages[$lang]['product']; ?></th>
                    <th><?php echo $languages[$lang]['qty']; ?></th>
                    <th><?php echo $languages[$lang]['price']; ?></th>
                    <th><?php echo $languages[$lang]['subtotal']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                if(mysqli_num_rows($items_res) > 0): 
                    while($item = mysqli_fetch_assoc($items_res)): 
                        $subtotal = $item['quantity'] * $item['price'];
                        $grand_total += $subtotal;
                ?>
                <tr>
                    <td><strong><?php echo $item['brand_name']; ?></strong></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>₹<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="background: rgba(0, 242, 255, 0.05);">
                    <td colspan="3" style="text-align: right; font-weight: bold; padding: 15px;">
                        <?php echo $languages[$lang]['total']; ?>:
                    </td>
                    <td style="color: #00f2ff; font-weight: bold; font-size: 1.2rem;">₹<?php echo number_format($grand_total, 2); ?></td>
                </tr>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 30px;">
                        <?php echo $languages[$lang]['empty_cart']; ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-chevron-left"></i> 
                <?= ($lang == 'ta') ? 'பின்செல்ல' : 'Go Back'; ?>
            </a>
        </div>
    </div>

</body>
</html>