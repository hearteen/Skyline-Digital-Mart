<?php
// Current page check for active state
$current_page = basename($_SERVER['PHP_SELF']);
$lang = $_SESSION['lang'] ?? 'en';
include_once 'includes/lang.php'; // Lang file link panni irukaen
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary-bg: #ffffff;
        --nav-bg: rgba(255, 255, 255, 0.85);
        --accent-cyan: #00f2ff;
        --accent-emerald: #10b981;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --glass-border: rgba(0, 242, 255, 0.1);
        --active-glow: 0 0 15px rgba(0, 242, 255, 0.3);
    }

    body { font-family: 'Inter', sans-serif; margin-top: 70px; }

    .main-nav {
        background: var(--nav-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--glass-border);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        height: 70px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Logo Style */
    .logo {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-main);
        text-decoration: none;
        letter-spacing: -0.5px;
    }
    .logo span {
        color: var(--accent-emerald);
        text-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
    }

    /* Menu Desktop */
    .nav-menu {
        display: flex;
        list-style: none;
        gap: 15px;
        margin: 0;
        padding: 0;
        align-items: center;
    }

    .nav-link {
        text-decoration: none;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.95rem;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link:hover {
        color: var(--text-main);
        background: rgba(0, 242, 255, 0.05);
    }

    .nav-link.active {
        color: #0891b2;
        background: rgba(0, 242, 255, 0.1);
        box-shadow: inset 0 0 0 1px var(--glass-border);
    }

    /* Logout Special Style */
    .logout-btn {
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.1);
    }
    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.05) !important;
        border-color: rgba(239, 68, 68, 0.3);
    }

    /* Mobile Toggle */
    .mobile-toggle {
        display: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-main);
    }

    /* Tablet/Mobile View */
    @media (max-width: 768px) {
        .mobile-toggle { display: block; }

        .nav-menu {
            position: fixed;
            top: 70px;
            left: -100%;
            width: 100%;
            height: calc(100vh - 70px);
            background: #fff;
            flex-direction: column;
            padding: 30px;
            gap: 20px;
            transition: 0.4s ease-in-out;
        }

        .nav-menu.active { left: 0; }

        .nav-link {
            width: 100%;
            font-size: 1.1rem;
            padding: 15px;
        }
    }
</style>

<nav class="main-nav">
    <div class="nav-container">
        <a href="home.php" class="logo">
            MF <span>Store</span>
        </a>

        <div class="mobile-toggle" id="mobile-menu">
            <i class="fas fa-bars-staggered"></i>
        </div>

        <ul class="nav-menu" id="nav-list">
            <li>
                <a href="home.php" class="nav-link <?= ($current_page == 'home.php') ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> <?= $languages[$lang]['home'] ?>
                </a>
            </li>
            <li>
                <a href="add_to_cart.php" class="nav-link <?= ($current_page == 'add_to_cart.php') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> <?= $languages[$lang]['cart'] ?>
                </a>
            </li>
            <li>
                <a href="my_orders.php" class="nav-link <?= ($current_page == 'my_orders.php') ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> <?= $languages[$lang]['order_status'] ?>
                </a>
            </li>
            <li>
                <a href="logout.php" class="nav-link logout-btn">
                    <i class="fas fa-power-off"></i> <?= $languages[$lang]['logout'] ?>
                </a>
            </li>
        </ul>
    </div>
</nav>

<script>
    const menuToggle = document.getElementById('mobile-menu');
    const navList = document.getElementById('nav-list');

    menuToggle.addEventListener('click', () => {
        navList.classList.toggle('active');
        // Toggle icon animation
        const icon = menuToggle.querySelector('i');
        icon.classList.toggle('fa-bars-staggered');
        icon.classList.toggle('fa-times');
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!menuToggle.contains(e.target) && !navList.contains(e.target)) {
            navList.classList.remove('active');
            menuToggle.querySelector('i').classList.add('fa-bars-staggered');
            menuToggle.querySelector('i').classList.remove('fa-times');
        }
    });
</script>