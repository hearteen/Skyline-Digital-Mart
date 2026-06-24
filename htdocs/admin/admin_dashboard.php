<?php
session_start();
include '../includes/db_config.php'; // Folder structure check: '..' use panniruken
include '../includes/lang.php';

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

$lang = $_SESSION['lang'];

// 2. Fetch REAL Dynamic Stats from Database
// Total Sales Calculation (Accepted Orders only)
$sales_query = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status='accepted'");
$sales_data = mysqli_fetch_assoc($sales_query);
$total_sales = $sales_data['total'] ?? 0;

// Pending Orders Count
$pending_query = mysqli_query($conn, "SELECT COUNT(*) as p_count FROM orders WHERE status='pending'");
$p_data = mysqli_fetch_assoc($pending_query);
$pending_orders = $p_data['p_count'] ?? 0;

// Low Stock Alert (Items less than 10)
$stock_query = mysqli_query($conn, "SELECT COUNT(*) as s_count FROM products WHERE stock_qty < 10");
$s_data = mysqli_fetch_assoc($stock_query);
$low_stock_items = $s_data['s_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Dashboard | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>

    
/* --- Merchant Flow Admin Core Styles --- */
:root {
    --bg-dark: #0a192f;
    --card-bg: rgba(255, 255, 255, 0.05);
    --cyan-glow: #00f2ff;
    --primary-blue: #0077ff;
    --text-white: #e6f1ff;
    --text-gray: #8892b0;
    --neon-yellow: #facc15;
    --neon-red: #ef4444;
}

body.light-theme {
    background: #f0f4f8; /* Light professional gray */
    margin: 0;
    font-family: 'Inter', sans-serif;
    color: #333;
}

/* --- Navigation Bar --- */
.admin-nav {
    background: var(--bg-dark);
    padding: 15px 5%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: white;
}

.logo span { color: var(--cyan-glow); text-shadow: 0 0 10px var(--cyan-glow); }

.nav-menu {
    display: flex;
    list-style: none;
    gap: 25px;
}

.nav-menu a {
    color: var(--text-gray);
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
    font-size: 0.95rem;
}

.nav-menu a:hover, .nav-menu a.active {
    color: var(--cyan-glow);
    text-shadow: 0 0 8px var(--cyan-glow);
}

/* --- Welcome Banner --- */
.welcome-banner {
    background: linear-gradient(135deg, #0077ff, #00c3ff);
    margin: 30px 5%;
    padding: 40px;
    border-radius: 20px;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 119, 255, 0.3);
}

.welcome-banner h1 { font-size: 2rem; margin-bottom: 10px; }

/* --- Stats Grid & Glow Cards --- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin: 0 5%;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    text-align: center;
    transition: 0.4s;
    border: 1px solid #e2e8f0;
}

.stat-card i { font-size: 2.5rem; margin-bottom: 15px; }

/* Glow variants */
.cyan-glow:hover { border-color: var(--cyan-glow); box-shadow: 0 0 20px rgba(0, 242, 255, 0.2); }
.cyan-glow i { color: var(--primary-blue); }

.yellow-glow:hover { border-color: var(--neon-yellow); box-shadow: 0 0 20px rgba(250, 204, 21, 0.2); }
.yellow-glow i { color: var(--neon-yellow); }

.red-glow:hover { border-color: var(--neon-red); box-shadow: 0 0 20px rgba(239, 68, 68, 0.2); }
.red-glow i { color: var(--neon-red); }

.stat-card h3 { color: #64748b; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px; }
.stat-card p { font-size: 1.8rem; font-weight: 800; color: #1e293b; }

/* --- Chart Container --- */
.chart-container {
    background: white;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.chart-container:hover {
    box-shadow: 0 0 25px rgba(0, 242, 255, 0.1);
}

/* --- Admin Footer --- */
.admin-footer {
    text-align: center;
    padding: 40px;
    color: #64748b;
    font-size: 0.85rem;
}

/* --- Mobile Responsiveness --- */
#menu-toggle { display: none; }

@media (max-width: 768px) {
    .menu-icon { display: block; color: white; font-size: 1.5rem; cursor: pointer; }
    .nav-menu {
        position: fixed; top: 70px; left: -100%; width: 100%; height: 100vh;
        background: var(--bg-dark); flex-direction: column; align-items: center;
        padding-top: 50px; transition: 0.5s;
    }
    #menu-toggle:checked ~ .nav-menu { left: 0; }
    .stats-grid { grid-template-columns: 1fr; }
}
    /* Admin Table Styling */
.admin-table-responsive {
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow-x: auto;
}

.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }

/* Status Glow Badges */
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.pending-glow { 
    background: #FFF9E6; color: #D97706; 
    box-shadow: 0 0 10px rgba(217, 119, 6, 0.2); 
}

.accepted-glow { 
    background: #ECFDF5; color: #059669; 
    box-shadow: 0 0 10px rgba(5, 150, 105, 0.3); /* Green Glow */
}

.rejected-glow { 
    background: #FEF2F2; color: #DC2626; 
    box-shadow: 0 0 10px rgba(220, 38, 38, 0.3); /* Red Glow */
}

/* Action Buttons */
.btn-accept { background: #059669; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-right: 5px; }
.btn-reject { background: #DC2626; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
</style>
<body class="light-theme">
  <?php include 'includes/admin_nav.php'; ?>
    

    <main class="dashboard-content">
        <div class="welcome-banner">
            <h1>Welcome back, Admin! 🚀</h1>
            <p>Real-time updates from your wholesale network.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card cyan-glow">
                <i class="fas fa-wallet"></i>
                <h3>Total Sales</h3>
                <p>₹<?php echo number_format($total_sales, 2); ?></p>
            </div>
            <div class="stat-card yellow-glow">
                <i class="fas fa-clock"></i>
                <h3>Pending Orders</h3>
                <p><?php echo $pending_orders; ?></p>
            </div>
            <div class="stat-card red-glow">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Low Stock</h3>
                <p><?php echo $low_stock_items; ?> Items</p>
            </div>
        </div>

        <div class="chart-container card-glow" style="margin: 30px 5%; background: #fff; padding: 20px; border-radius: 15px;">
            <h3>Sales Trajectory</h3>
            <canvas id="salesChart"></canvas>
        </div>
    </main>

    <footer class="admin-footer">
        <p>&copy; 2026 Merchant Flow - Admin Control Panel | High Fidelity Digital Solutions</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales Growth',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 45000],
                    borderColor: '#00FFFF',
                    backgroundColor: 'rgba(0, 255, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            }
        });
    </script>
</body>
</html>