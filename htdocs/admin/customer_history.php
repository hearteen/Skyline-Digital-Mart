<?php
session_start();
include '../includes/db_config.php';

// Admin login check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

// Fetch all shopkeepers from users table
// Role 'shopkeeper' irukura users-ah mattum edukkurom
$query = "SELECT roll_id, owner_name, shop_name, whatsapp_no, pending_balance 
          FROM users 
          WHERE role = 'shopkeeper' 
          ORDER BY shop_name ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer History | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Table Glow Effect matching your theme */
        .card-glow {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 242, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .balance-badge {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .view-btn {
            background: var(--glass-bg);
            color: var(--cyan-glow);
            border: 1px solid var(--cyan-glow);
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .view-btn:hover {
            background: var(--cyan-glow);
            color: #0f172a;
            box-shadow: 0 0 15px var(--cyan-glow);
        }
    </style>
</head>
<body class="light-theme" style="background: #0f172a; color: #fff;">

    <?php include 'includes/admin_nav.php'; ?>

    <main class="dashboard-content" style="padding: 40px 20px;">
        <div class="container" style="max-width: 1100px; margin: 0 auto;">
            
            <div class="welcome-banner" style="margin-bottom: 30px;">
                <h2 style="font-size: 2rem;"><i class="fas fa-users"></i> Customer <span>Database</span></h2>
                <p style="color: #94a3b8;">Manage shopkeepers and view their entire order history.</p>
            </div>

            <div class="table-container card-glow">
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid rgba(255,255,255,0.1);">
                            <th style="padding: 15px;">Shop Name</th>
                            <th>Owner Name</th>
                            <th>Contact</th>
                            <th>Pending Bal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px;">
                                <strong><?php echo $row['shop_name']; ?></strong><br>
                                <small style="color: #64748b;">ID: <?php echo $row['roll_id']; ?></small>
                            </td>
                            <td><?php echo $row['owner_name']; ?></td>
                            <td>
                                <a href="https://wa.me/<?php echo $row['whatsapp_no']; ?>" target="_blank" style="color: #25D366; text-decoration: none;">
                                    <i class="fab fa-whatsapp"></i> <?php echo $row['whatsapp_no']; ?>
                                </a>
                            </td>
                            <td>
                                <span class="balance-badge">₹<?php echo number_format($row['pending_balance'], 2); ?></span>
                            </td>
                            <td>
                                <a href="../order_details.php?shop_id=<?php echo $row['roll_id']; ?>" class="view-btn">
                                    <i class="fas fa-history"></i> View Orders
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer style="text-align: center; margin-top: 50px; color: #475569; font-size: 0.9rem;">
        <p>&copy; 2026 Merchant Flow | Professional Wholesale Management</p>
    </footer>

</body>
</html>