<style>
    :root {
    --bg-dark: #0f172a;
    --cyan-glow: #00f2ff;
    --glass-bg: rgba(255, 255, 255, 0.05);
    --glass-border: rgba(255, 255, 255, 0.1);
}

/* Nav Container Styling */
.admin-nav {
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(15px); /* Glassmorphism effect */
    border-bottom: 1px solid var(--glass-border);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 10px 0;
}

.nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Logo Animation */
.logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: 1px;
}

.logo span {
    color: var(--cyan-glow);
    text-shadow: 0 0 10px rgba(0, 242, 255, 0.6);
}

/* Nav Menu & Hover Effects */
.nav-menu {
    display: flex;
    list-style: none;
    gap: 15px;
}

.nav-menu li a {
    text-decoration: none;
    color: #94a3b8;
    font-weight: 500;
    padding: 10px 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.nav-menu li a i {
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}

/* Hover & Active State */
.nav-menu li a:hover, .nav-menu li a.active {
    color: #fff;
    background: var(--glass-bg);
    box-shadow: inset 0 0 15px rgba(0, 242, 255, 0.1);
    border: 1px solid rgba(0, 242, 255, 0.3);
}

.nav-menu li a:hover i {
    color: var(--cyan-glow);
    transform: translateY(-2px) scale(1.2);
    text-shadow: 0 0 8px var(--cyan-glow);
}

/* Micro-interaction: Bottom Line Animation */
.nav-menu li a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: var(--cyan-glow);
    box-shadow: 0 0 10px var(--cyan-glow);
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.nav-menu li a:hover::after {
    width: 60%;
}

/* Logout Special Style */
.nav-menu li a[href*="logout"]:hover {
    color: #ff4d4d;
    border-color: rgba(255, 77, 77, 0.4);
    box-shadow: inset 0 0 15px rgba(255, 77, 77, 0.1);
}

.nav-menu li a[href*="logout"]:hover i {
    color: #ff4d4d;
    text-shadow: 0 0 8px #ff4d4d;
}

/* Responsive Menu Toggle */
#menu-toggle { display: none; }
.menu-icon { display: none; color: #fff; font-size: 1.5rem; cursor: pointer; }

@media (max-width: 768px) {
    .menu-icon { display: block; }
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #0f172a;
        flex-direction: column;
        padding: 20px;
        clip-path: circle(0% at 100% 0%);
        transition: all 0.5s ease-in-out;
    }
    #menu-toggle:checked ~ .nav-menu {
        clip-path: circle(150% at 100% 0%);
    }
    .nav-menu li { width: 100%; }
}
</style>
<nav class="admin-nav">
    <div class="nav-container">
        <div class="logo">MF <span>Admin</span></div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>
        <ul class="nav-menu">
    <li><a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
    <li><a href="inventory.php"><i class="fas fa-boxes"></i> add product</a></li>
    <li><a href="view_orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
    
    <li><a href="customer_history.php"><i class="fas fa-users-cog"></i> Customers</a></li>
    <li><a href="manage_categories.php"><i class="fas fa-users-cog"></i> categories</a></li>
    
    <li><a href="stock_status.php"><i class="fas fa-chart-line"></i> Stock Status</a></li>
    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
    </div>
</nav>