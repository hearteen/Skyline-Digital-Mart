<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php';

$lang = $_SESSION['lang'];
$p_id = $_GET['id']; // Product ID from previous brand list

// Fetch specific product details using namma Multi-language logic
$query = "SELECT product_id, brand_$lang AS brand_name, category_$lang AS cat_name, price, stock_qty, image_path, description 
          FROM products WHERE product_id = '$p_id'";
$result = mysqli_fetch_assoc(mysqli_query($conn, $query));
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $result['brand_name']; ?> | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<style>
    
.product-detail-flex {
    display: flex;
    gap: 50px;
    padding: 50px 0;
    align-items: center;
}

.detail-image img {
    width: 350px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #eee;
}

.price-tag {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--primary-blue);
    text-shadow: 0 0 10px rgba(0, 255, 255, 0.2); /* Soft Cyan Glow */
}

.qty-box input {
    padding: 10px;
    width: 80px;
    border-radius: 8px;
    border: 2px solid #E2E8F0;
    font-size: 1.1rem;
    text-align: center;
}

.btn-add-cart-large {
    background: var(--primary-blue);
    color: white;
    padding: 18px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 20px;
    transition: 0.4s;
    width: 100%;
}

.btn-add-cart-large:hover {
    box-shadow: 0 0 20px var(--cyan-glow); /* The Master Cyan Glow */
    transform: scale(1.02);
}
</style>
<body class="light-theme">

    <?php include 'includes/header.php'; ?>

    <section class="container detail-section">
        <div class="product-detail-flex">
            <div class="detail-image">
                <img src="<?php echo $result['image_path']; ?>" alt="Product">
                <div class="image-glow"></div>
            </div>

            <div class="detail-info">
                <nav class="breadcrumb"><?php echo $result['cat_name']; ?> / <?php echo $result['brand_name']; ?></nav>
                <h1><?php echo $result['brand_name']; ?></h1>
                <p class="price-tag">₹<?php echo $result['price']; ?> <span>/ unit</span></p>
                
                <p class="description"><?php echo $result['description']; ?></p>

                <hr>

                <form action="add_to_cart.php" method="POST" class="cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $result['product_id']; ?>">
                    
                    <div class="qty-box">
                        <label><?php echo $languages[$lang]['qty']; ?>:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $result['stock_qty']; ?>">
                        <span class="stock-left">(<?php echo $result['stock_qty']; ?> items in stock)</span>
                    </div>

                    <?php if($result['stock_qty'] > 0): ?>
                        <button type="submit" class="btn-add-cart-large">
                            <?php echo $languages[$lang]['add_to_cart']; ?>
                        </button>
                    <?php else: ?>
                        <button class="btn-out-of-stock" disabled><?php echo $languages[$lang]['rejected']; ?></button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>
</html>