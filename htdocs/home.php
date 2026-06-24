<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

$cat_query = "SELECT * FROM categories ORDER BY cat_name_en ASC";
$cat_result = mysqli_query($conn, $cat_query);

// Cart count fetch panna (If you have a cart session)
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Categories | Merchant Flow</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-light: #f8fafc;
            --primary: #2563eb;
            --dark-navy: #0f172a;
            --cyan-glow: #00f2ff;
            --primary-light: #dbeafe;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-dim: #64748b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        /* --- Global Reset & Container --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-main); min-height: 100vh; position: relative; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* --- Floating Cart Button Style --- */
        .floating-cart {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 65px;
            height: 65px;
            background: var(--dark-navy);
            color: var(--cyan-glow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(0, 242, 255, 0.3);
            border: 2px solid var(--cyan-glow);
            z-index: 9999;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .floating-cart:hover {
            transform: scale(1.1) rotate(-5deg);
            background: var(--cyan-glow);
            color: var(--dark-navy);
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.6);
        }

        .floating-cart i { font-size: 1.5rem; }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444; /* Alert Red */
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 10px;
            border: 2px solid var(--dark-navy);
        }

        /* --- Header & Search Styling --- */
        .header-area { margin-top: 20px; margin-bottom: 30px; text-align: center; }
        .section-title { font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px; }
        .section-title span { color: var(--primary); }
        .subtitle { color: var(--text-dim); font-size: 1rem; max-width: 600px; margin: 0 auto 25px; }

        .search-container { display: flex; justify-content: center; margin-bottom: 40px; padding: 0 10px; position: relative; }
        .search-wrapper { width: 100%; max-width: 500px; position: relative; }
        
        .search-box {
            width: 100%; background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 16px; padding: 2px 20px; display: flex; align-items: center;
            box-shadow: var(--shadow); transition: 0.3s ease;
        }
        .search-box:focus-within { border-color: var(--primary); box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1); }
        .search-icon { color: var(--primary); margin-right: 12px; }
        #catSearch { width: 100%; background: transparent; border: none; outline: none; padding: 14px 0; font-size: 1rem; }

        #search-results {
            position: absolute; top: 100%; left: 0; width: 100%; background: white;
            border: 1px solid var(--border); border-radius: 0 0 16px 16px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); z-index: 1000; display: none;
            max-height: 300px; overflow-y: auto;
        }

        /* --- Category Grid --- */
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .category-card {
            background: var(--card-bg); border: 1px solid var(--border); border-radius: 20px; padding: 15px;
            text-decoration: none; transition: 0.3s ease; display: flex; flex-direction: column;
        }
        .category-card:hover { transform: translateY(-5px); border-color: var(--cyan-glow); }
        .image-wrapper { width: 100%; height: 160px; border-radius: 14px; overflow: hidden; margin-bottom: 15px; background: #f1f5f9; }
        .category-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s ease; }
        .category-card:hover .category-img { transform: scale(1.08); }
        .cat-name { font-size: 1.2rem; font-weight: 700; color: var(--text-main); }
        .cat-lang { font-size: 0.85rem; color: var(--primary); font-weight: 600; }

        /* --- Mobile Responsive --- */
        @media (max-width: 640px) {
            .floating-cart { bottom: 20px; right: 20px; width: 55px; height: 55px; }
            .floating-cart i { font-size: 1.2rem; }
            .category-grid { grid-template-columns: repeat(3, 1fr) !important; gap: 6px !important; }
            .section-title { font-size: 1.1rem !important; }
            .image-wrapper { height: 55px !important; }
            .cat-name { font-size: 0.65rem !important; }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <a href="add_to_cart.php" class="floating-cart" title="View Cart">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <main class="container">
        <header class="header-area">
            <h1 class="section-title">
                <?php echo ($lang == 'ta') ? 'பொருட்களின் <span>பிரிவுகள்</span>' : 'Shop by <span>Category</span>'; ?>
            </h1>
            <p class="subtitle">
                <?php echo ($lang == 'ta') ? 'உங்களுக்கு தேவையான வகையை தேர்ந்தெடுக்கவும்' : 'Browse our wholesale collection by category'; ?>
            </p>

            <div class="search-container">
                <div class="search-wrapper">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="catSearch" 
                            placeholder="<?php echo ($lang == 'ta') ? 'தேடுக...' : 'Search categories or products...'; ?>" 
                            onkeyup="handleSearch()" autocomplete="off">
                    </div>
                    <div id="search-results"></div>
                </div>
            </div>
        </header>

        <div class="category-grid" id="categoryGrid">
            <?php 
            if (mysqli_num_rows($cat_result) > 0) {
                while($row = mysqli_fetch_assoc($cat_result)) {
                    $cat_url = urlencode($row['cat_name_en']);
                    $img_src = "uploads/categories/" . $row['cat_image'];
            ?>
                <a href="products.php?cat=<?php echo $cat_url; ?>" class="category-card">
                    <div class="image-wrapper">
                        <img src="<?php echo $img_src; ?>" alt="<?php echo $row['cat_name_en']; ?>" class="category-img" onerror="this.src='assets/images/placeholder.png'">
                    </div>
                    <div class="card-content">
                        <div class="cat-name">
                            <?php echo ($lang == 'ta') ? $row['cat_name_ta'] : $row['cat_name_en']; ?>
                        </div>
                        <div class="cat-lang">
                            <?php echo ($lang == 'ta') ? $row['cat_name_en'] : $row['cat_name_ta']; ?>
                        </div>
                    </div>
                </a>
            <?php 
                }
            } else {
                echo "<p id='dynamic-no-result'>No categories found yet. 🚀</p>";
            }
            ?>
        </div>
    </main>

    <footer style="text-align: center; padding: 40px; color: var(--text-dim); font-size: 0.85rem; border-top: 1px solid var(--border); margin-top: 40px;">
        &copy; 2026 Merchant Flow | High-Fidelity ERP Solutions
    </footer>

    <script>
        function handleSearch() {
            filterCategories();
            liveProductSearch();
        }

        function filterCategories() {
            let input = document.getElementById('catSearch').value.toLowerCase();
            let cards = document.getElementsByClassName('category-card');
            let grid = document.getElementById('categoryGrid');
            let hasResults = false;

            for (let i = 0; i < cards.length; i++) {
                let nameEnTa = cards[i].innerText.toLowerCase();
                if (nameEnTa.includes(input)) {
                    cards[i].style.display = "flex";
                    hasResults = true;
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        function liveProductSearch() {
            let input = document.getElementById('catSearch').value;
            let resBox = document.getElementById('search-results');
            if (input.length > 0) {
                fetch('fetch_products.php?q=' + input)
                    .then(response => response.text())
                    .then(data => {
                        resBox.innerHTML = data;
                        resBox.style.display = "block";
                    });
            } else {
                resBox.style.display = "none";
            }
        }

        document.addEventListener('click', function(e) {
            if (!document.getElementById('catSearch').contains(e.target)) {
                document.getElementById('search-results').style.display = "none";
            }
        });
    </script>
</body>
</html>