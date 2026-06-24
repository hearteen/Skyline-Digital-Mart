<?php
session_start();
include 'includes/db_config.php';

// Language selection
$lang = $_SESSION['lang'] ?? 'en';

// Search query vangi clean pannuvom
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

if ($q !== '') {
    // Brand name (EN/TA) or Category-la search panna query
    $query = "SELECT product_id, brand_en, brand_ta, category_en, image_path, price 
              FROM products 
              WHERE brand_en LIKE '%$q%' 
              OR brand_ta LIKE '%$q%' 
              OR category_en LIKE '%$q%'
              LIMIT 6"; 
    
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="suggestion-wrapper" style="background: #ffffff; border-radius: 0 0 16px 16px; overflow: hidden;">';
        
        while($row = mysqli_fetch_assoc($result)) {
            $p_id = $row['product_id'];
            $p_name = ($lang == 'ta') ? $row['brand_ta'] : $row['brand_en'];
            $cat_name = urlencode($row['category_en']);
            $img = "uploads/products/" . $row['image_path'];
            $price = $row['price'];

            // Google-style list item with hover effect
            echo "
            <a href='products.php?cat=$cat_name&highlight=$p_id' class='live-search-item'>
                <div class='item-container'>
                    <div class='img-box'>
                        <img src='$img' onerror=\"this.src='assets/images/placeholder.png'\">
                    </div>
                    <div class='details'>
                        <span class='product-title'>$p_name</span>
                        <span class='product-meta'>" . ($row['category_en']) . " • ₹$price</span>
                    </div>
                    <div class='arrow-icon'>
                        <i class='fas fa-chevron-right'></i>
                    </div>
                </div>
            </a>";
        }
        echo '</div>';
    } else {
        // Results illana indha layout varum
        echo "
        <div style='padding: 20px; text-align: center; color: #64748b;'>
            <i class='fas fa-box-open' style='display: block; font-size: 1.5rem; margin-bottom: 8px; opacity: 0.5;'></i>
            <span style='font-size: 0.85rem;'>No products found matching your search.</span>
        </div>";
    }
}
?>

<style>
    .live-search-item {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .live-search-item:last-child {
        border-bottom: none;
    }

    .live-search-item:hover {
        background-color: #f8fafc;
    }

    .item-container {
        display: flex;
        align-items: center;
        padding: 12px 16px;
    }

    .img-box {
        width: 45px;
        height: 45px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        overflow: hidden;
    }

    .img-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 4px;
    }

    .details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    .product-meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
    }

    .arrow-icon {
        color: #cbd5e1;
        font-size: 0.8rem;
        transition: transform 0.2s ease;
    }

    .live-search-item:hover .arrow-icon {
        color: #2563eb;
        transform: translateX(3px);
    }

    /* Mobile Responsive Tweaks */
    @media (max-width: 640px) {
        .item-container { padding: 10px; }
        .img-box { width: 35px; height: 35px; margin-right: 10px; }
        .product-title { font-size: 0.85rem; }
        .product-meta { font-size: 0.7rem; }
    }
</style>