<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$lang = $_SESSION['lang'] ?? 'en'; // Default set panniyaachu
$user_id = $_SESSION['user_id'];

// 1. Fetch REAL Pending Balance from Database
$user_query = "SELECT owner_name, whatsapp_no, pending_balance FROM users WHERE roll_id = '$user_id'";
$user_res = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_res);

$pending_bal = $user_data['pending_balance'] ?? 0.00; 

// 2. Fetch REAL Cart Items and Calculate Total
$total_order_amount = 0;
$cart_display_items = [];

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $p_id => $qty) {
        $product_query = "SELECT brand_$lang AS brand_name, price FROM products WHERE product_id = '$p_id'";
        $p_res = mysqli_query($conn, $product_query);
        if ($product = mysqli_fetch_assoc($p_res)) {
            $subtotal = $product['price'] * $qty;
            $total_order_amount += $subtotal;
            
            $cart_display_items[] = [
                'name' => $product['brand_name'],
                'qty' => $qty,
                'price' => $product['price'],
                'subtotal' => $subtotal
            ];
        }
    }
}

// Net Payable Calculation (Indha order oda amount mattum)
$net_payable = $total_order_amount;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $languages[$lang]['my_cart']; ?> | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-summary {
            background: #fff; padding: 30px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;
            margin-top: 20px; text-align: right;
        }
        .red-glow { color: #EF4444; text-shadow: 0 0 8px rgba(239, 68, 68, 0.4); font-weight: 600; }
        /* Light theme-la cyan glow-ku badhula dark blue use panna nalla irukum */
        .cyan-glow-text { color: #0891b2; font-size: 1.8rem; font-weight: 700; }
        
        .place-order-btn {
            background: #10b981; color: white;
            padding: 15px 30px; border: none;
            border-radius: 10px; font-weight: 600;
            cursor: pointer; font-size: 1.1rem; margin-top: 20px;
            width: 100%; transition: 0.3s;
        }
        .place-order-btn:hover { background: #059669; transform: translateY(-2px); }
        
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cart-table th, .cart-table td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body class="light-theme">

    <?php include 'includes/header.php'; ?>

    <section class="container">
        <h2 class="section-title"><i class="fas fa-shopping-cart"></i> <?php echo $languages[$lang]['my_cart']; ?></h2>
        
        <div class="cart-container">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th><?php echo $languages[$lang]['product']; ?></th>
                        <th><?php echo $languages[$lang]['qty']; ?></th>
                        <th><?php echo $languages[$lang]['price']; ?></th>
                        <th><?php echo $languages[$lang]['subtotal']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cart_display_items)): ?>
                        <?php foreach ($cart_display_items as $item): ?>
                            <tr>
                                <td><strong><?php echo $item['name']; ?></strong></td>
                                <td><?php echo $item['qty']; ?></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; padding: 50px;"><?php echo $languages[$lang]['empty_cart']; ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($cart_display_items)): ?>
            <div class="cart-summary">
                <p><?php echo $languages[$lang]['total']; ?>: <strong>₹<?php echo number_format($total_order_amount, 2); ?></strong></p>
                <p class="balance-text"><?php echo $languages[$lang]['prev_balance']; ?>: <span class="red-glow">₹<?php echo number_format($pending_bal, 2); ?></span></p>
                <hr style="border: 0.5px solid #eee; margin: 15px 0;">
                
                <h3><?php echo $languages[$lang]['net_payable']; ?>: <span class="cyan-glow-text">₹<?php echo number_format($net_payable, 2); ?></span></h3>
                
                <form action="place_order.php" method="POST">
                    <input type="hidden" name="total_amount" value="<?php echo $net_payable; ?>">
                    <button type="submit" class="place-order-btn">
                        <i class="fas fa-truck me-2"></i> <?php echo $languages[$lang]['place_order']; ?> (COD)
                    </button>
                </form>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 10px; text-align: center;">
                    <?php if($lang == 'ta'): ?>
                        * டெலிவரியின் போது நிர்வாகியால் கட்டணம் சரிபார்க்கப்படும்.
                    <?php else: ?>
                        * Payment will be verified by the admin upon delivery.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>
</html>