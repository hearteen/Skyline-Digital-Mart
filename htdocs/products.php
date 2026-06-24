<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$lang = $_SESSION['lang']; 
$category = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';

// Fixed: Undefined variable issue-ah avoid panna ingaye define panrom
$highlight_id = isset($_GET['highlight']) ? (int)$_GET['highlight'] : 0;

// Cart count fetch panna
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Updated Query to fetch wholesale details
$query = "SELECT product_id, category_$lang AS cat_name, brand_$lang AS brand_name, 
          price, stock_qty, image_path, unit_type, inner_qty 
          FROM products WHERE category_en = '$category' OR category_ta = '$category'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category; ?> | Merchant Flow</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --dark-navy: #0f172a;
            --cyan-glow: #00f2ff;
            --text-slate: #64748b;
            --border-light: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #f8fafc; color: var(--dark-navy); position: relative; min-height: 100vh; }

        /* --- Floating Cart Button --- */
        .floating-cart {
            position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px;
            background: var(--dark-navy); color: var(--cyan-glow); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; box-shadow: 0 10px 25px rgba(0, 242, 255, 0.3);
            border: 2px solid var(--cyan-glow); z-index: 9999;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .floating-cart:hover { transform: scale(1.1) rotate(-5deg); background: var(--cyan-glow); color: var(--dark-navy); }
        .cart-badge {
            position: absolute; top: -5px; right: -5px; background: #ef4444;
            color: white; font-size: 0.75rem; font-weight: 800; padding: 4px 8px;
            border-radius: 10px; border: 2px solid var(--dark-navy);
        }

        /* Toast Notification */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 10000; }
        .toast-msg {
            visibility: hidden; min-width: 280px; background-color: var(--dark-navy);
            color: var(--cyan-glow); text-align: center; border-radius: 12px;
            padding: 16px; border: 1px solid var(--cyan-glow);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.2); font-weight: bold;
            transform: translateX(120%); transition: transform 0.4s ease-in-out;
        }
        .toast-msg.show { visibility: visible; transform: translateX(0); }

        /* Section Title */
        .section-title { 
            font-size: 1.8rem; font-weight: 800; margin-bottom: 25px; 
            padding-left: 15px; border-left: 5px solid var(--cyan-glow); 
        }

        /* Product Grid */
        .product-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px; padding: 10px;
        }

        .product-card {
            background: #ffffff; border-radius: 20px; padding: 15px;
            border: 1px solid var(--border-light); transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; position: relative;
        }

        .product-card:hover { transform: translateY(-8px); border-color: var(--cyan-glow); }

        .product-card img {
            width: 100%; height: 160px; object-fit: contain;
            margin-bottom: 15px; background: #f1f5f9; border-radius: 14px;
        }

        .product-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; color: #1e293b; }
        .price { font-size: 1.2rem; font-weight: 800; color: var(--dark-navy); }
        .price span { font-size: 0.75rem; color: var(--text-slate); font-weight: 500; }

        .wholesale-info {
            background: #f1f5f9; padding: 8px; border-radius: 8px;
            font-size: 0.8rem; color: #475569; margin: 10px 0;
            display: flex; align-items: center; gap: 8px;
        }

        .qty-selector { display: flex; align-items: center; gap: 8px; margin: 10px 0; }
        .qty-selector input {
            width: 70px; padding: 8px; border-radius: 10px;
            border: 1px solid var(--border-light); text-align: center; font-weight: 700;
        }

        .btn-add-cart {
            background: var(--dark-navy); color: white; border: none;
            padding: 12px; border-radius: 12px; cursor: pointer;
            width: 100%; font-weight: 700; transition: 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-add-cart:hover { background: var(--cyan-glow); color: #000; }

        .highlight-card { border: 2px solid var(--cyan-glow) !important; box-shadow: 0 0 20px rgba(0, 242, 255, 0.4) !important; }

        /* --- MOBILE OPTIMIZED (3 COLUMNS) --- *//* --- MOBILE OPTIMIZED (Height Reduced for 3 Columns) --- */
@media (max-width: 640px) {
    .floating-cart { bottom: 20px; right: 20px; width: 52px; height: 52px; }
    .floating-cart i { font-size: 1.1rem; }

    .container { padding: 0 5px !important; margin-top: 70px !important; }
    .section-title { font-size: 1rem; margin: 10px 5px; }
    
    .product-grid { 
        grid-template-columns: repeat(3, 1fr) !important; 
        gap: 6px !important; /* Gap-ah innum kammi panniyachu */
        padding: 4px !important; 
    }

    .product-card { 
        padding: 6px 4px !important; 
        border-radius: 10px !important; 
        min-height: auto !important; /* Fixed height-ah remove panniyachu */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-card img { 
        height: 55px !important; /* Height-ah nalla kammi panniyachu */
        margin-bottom: 4px !important; 
        border-radius: 6px;
        padding: 2px;
    }

    .product-card h3 { 
        font-size: 0.62rem !important; /* Text size-ah optimize panniyachu */
        font-weight: 700;
        height: 28px; /* Two lines mattum allow pannuvom */
        overflow: hidden; 
        line-height: 1.1;
        margin-bottom: 2px !important;
    }

    .price { 
        font-size: 0.75rem !important; 
        margin-bottom: 4px !important;
        line-height: 1;
    }
    .price span { font-size: 0.5rem !important; display: inline-block; }

    /* Space saving: Hide info and labels */
    .wholesale-info, .qty-selector label, .unit-tag { display: none !important; }

    .qty-selector { 
        margin: 2px 0 !important; 
        height: 24px !important; 
    }
    .qty-selector input { 
        width: 100% !important; 
        height: 22px !important; 
        font-size: 0.75rem !important; 
        border-radius: 4px; 
        padding: 0 !important;
    }

    .btn-add-cart { 
        height: 28px !important; 
        padding: 0 !important; 
        font-size: 0 !important; /* Only Icon will show */
        border-radius: 6px;
        margin-top: 4px;
    }
    .btn-add-cart i { font-size: 0.9rem !important; }
    
    .btn-disabled { 
        padding: 5px 0 !important; 
        font-size: 0.55rem !important; 
        border-radius: 6px;
        height: 28px;
    }
}
        
    </style>
</head>
<body>

    <div id="toast-container">
        <div id="toast" class="toast-msg">
            <i class="fas fa-check-circle"></i> Item Added to Cart!
        </div>
    </div>

    <?php include 'includes/navbar.php'; ?>

    <a href="add_to_cart.php" class="floating-cart" title="View Cart">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <main class="container" style="max-width: 1200px; margin: 100px auto 40px; padding: 0 20px;">
        <h2 class="section-title">
            <?php echo $category; ?> <span style="color: var(--text-slate); font-weight: 400; font-size: 1rem;">Collections</span>
        </h2>
        
        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $current_id = (int)$row['product_id'];
                    $is_highlighted = ($current_id === $highlight_id) ? 'highlight-card' : '';
                ?>
                <div class="product-card <?php echo $is_highlighted; ?>" id="p-<?php echo $current_id; ?>">
                    <img src="uploads/products/<?php echo $row['image_path']; ?>" 
                         alt="<?php echo $row['brand_name']; ?>" 
                         onerror="this.src='assets/images/placeholder.png';">
                    
                    <h3><?php echo $row['brand_name']; ?></h3>
                    
                    <p class="price">₹<?php echo number_format($row['price'], 0); ?>
                        <span>/<?php echo $row['unit_type']; ?></span>
                    </p>

                    <?php if($row['unit_type'] !== 'Pcs'): ?>
                        <div class="wholesale-info">
                            <i class="fas fa-box-open"></i> 
                            1 <?php echo $row['unit_type']; ?> = <?php echo $row['inner_qty']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($row['stock_qty'] > 0): ?>
                        <form action="add_session.php" method="POST">
                            <input type="hidden" name="p_id" value="<?php echo $row['product_id']; ?>">
                            <div class="qty-selector">
                                <label style="font-size: 0.75rem;">Qty:</label>
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $row['stock_qty']; ?>">
                                <span class="unit-tag" style="font-size: 0.7rem;"><?php echo $row['unit_type']; ?></span>
                            </div>
                            <button type="submit" class="btn-add-cart">
                                <i class="fas fa-cart-plus"></i> <span>Add to Cart</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="margin-top: 10px;">
                            <button class="btn-disabled" disabled>Out of Stock</button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 100px 20px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3>No products found.</h3>
                    <a href="home.php" style="color: var(--primary); text-decoration: none; font-weight: 700;">← Back</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Toast logic
        <?php if (isset($_SESSION['success_msg'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var toast = document.getElementById("toast");
            toast.classList.add("show");
            setTimeout(function() { toast.classList.remove("show"); }, 3000);
        });
        <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        // Smooth scroll for highlighted item
        window.addEventListener('load', function() {
            const highlighted = document.querySelector('.highlight-card');
            if (highlighted) {
                setTimeout(() => {
                    highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 500);
            }
        });
    </script>
</body>
</html>