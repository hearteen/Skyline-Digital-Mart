<?php
session_start();
// Oru velai user munnadiye login pannirundha, direct-ah dashboard-ku kootitu poga:
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'dealer') {
        header("Location: admin/admin_dashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Login | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    /* --- Merchant Flow ERP Theme Configuration --- */
:root {
    --bg-dark: #0a192f;
    --card-bg: rgba(255, 255, 255, 0.05); /* Glass Effect */
    --cyan-glow: #00f2ff;
    --primary-blue: #0077ff;
    --text-white: #e6f1ff;
    --text-gray: #8892b0;
    --error-red: #ff4d4d;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

body.erp-bg {
    background: radial-gradient(circle at center, #112240 0%, #0a192f 100%);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-white);
    overflow: hidden;
}

/* --- Login Wrapper & Logo --- */
.login-wrapper {
    width: 100%;
    max-width: 400px;
    padding: 20px;
    text-align: center;
    animation: fadeIn 0.8s ease-in-out;
}

.erp-logo {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 30px;
    letter-spacing: -1px;
}

.erp-logo span {
    color: var(--cyan-glow);
    text-shadow: 0 0 15px var(--cyan-glow);
}

.erp-logo p {
    font-size: 0.9rem;
    color: var(--text-gray);
    font-weight: 400;
    margin-top: 5px;
}

/* --- Glassmorphism Card --- */
.login-card {
    background: var(--card-bg);
    backdrop-filter: blur(15px); /* Blur effect */
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 40px 30px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    transition: 0.3s ease;
}

.card-glow:hover {
    border-color: rgba(0, 242, 255, 0.5);
    box-shadow: 0 0 30px rgba(0, 242, 255, 0.15);
}

.card-header h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.card-header p {
    color: var(--text-gray);
    font-size: 0.85rem;
    margin-bottom: 30px;
}

/* --- ERP Form Elements --- */
.erp-form {
    text-align: left;
}

.input-group {
    margin-bottom: 20px;
}

.input-group label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-gray);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.input-group i {
    margin-right: 8px;
    color: var(--cyan-glow);
}

.input-group input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 12px 15px;
    border-radius: 8px;
    color: white;
    outline: none;
    transition: 0.3s;
}

.input-group input:focus {
    border-color: var(--cyan-glow);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 0 10px rgba(0, 242, 255, 0.2);
}

/* --- Primary Glow Button --- */
.btn-primary-glow {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-blue), #00d4ff);
    color: white;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    transition: 0.4s;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.btn-primary-glow:hover {
    box-shadow: 0 0 20px var(--cyan-glow);
    transform: translateY(-2px);
}

/* --- Footer & Status --- */
.card-footer {
    margin-top: 25px;
    font-size: 0.85rem;
    color: var(--text-gray);
}

.card-footer a {
    color: var(--cyan-glow);
    text-decoration: none;
    font-weight: 600;
}

.system-status {
    margin-top: 30px;
    font-size: 0.75rem;
    color: var(--text-gray);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 8px #22c55e;
}

/* --- Animations --- */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Fix */
@media (max-width: 480px) {
    .login-card { padding: 30px 20px; }
}
</style>
<body class="erp-bg">

    <div class="login-wrapper">
        <div class="erp-logo">
            Merchant<span>Flow</span>
            <p>Wholesale Management System v2.0</p>
        </div>

        <div class="login-card card-glow">
            <div class="card-header">
                <h2><i class="fas fa-lock"></i> Secure ERP Login</h2>
                <p>Enter your credentials to access the hub</p>
            </div>

            <form action="login_process.php" method="POST" class="erp-form">
                <div class="input-group">
                    <label><i class="fas fa-id-badge"></i> Roll ID / Admin ID</label>
                    <input type="text" name="roll_id" placeholder="e.g. SHOP1234 or ADMIN001" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-key"></i> Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login_btn" class="btn-primary-glow">
                    Login to System <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="card-footer">
                <p>Don't have an account? <a href="signup.php">Register Shop</a></p>
            </div>
        </div>
        
        <div class="system-status">
            <span class="status-dot"></span> System Online | 2026 High-Fidelity Solutions
        </div>
    </div>

</body>
</html>