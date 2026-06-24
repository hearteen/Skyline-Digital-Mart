<?php
session_start();
include 'includes/db_config.php';
include 'includes/lang.php'; 

// Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Language and User Session Handling
$lang = $_SESSION['lang'] ?? 'en'; 
$shop_id = $_SESSION['user_id']; 

// Query to get orders only for THIS shopkeeper
$query = "SELECT * FROM orders WHERE shop_id = '$shop_id' ORDER BY order_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>My Orders | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; }
        .container { max-width: 950px; margin: 40px auto; padding: 0 20px; }
        
        .section-title { color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }

        .order-card { 
            background: #fff; 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 15px; 
            border: 1px solid #e2e8f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: 0.3s ease;
        }
        .order-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

        .order-info strong { font-size: 1.1rem; color: #334155; }
        .order-info small { color: #64748b; }

        .price-tag { font-weight: 700; color: #0f172a; font-size: 1.1rem; }

        /* Status Styling */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            min-width: 90px;
            text-align: center;
            display: inline-block;
        }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-accepted { background: #dcfce7; color: #16a34a; }
        .status-rejected { background: #fee2e2; color: #dc2626; }

        .action-buttons { display: flex; gap: 10px; align-items: center; }

        .view-btn { 
            background: #0f172a; 
            color: #fff; 
            padding: 8px 18px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
            border: 1px solid transparent;
        }
        .view-btn:hover { background: #1e293b; color: #fff; }

        /* Cyan Glow Bill Button */
        .bill-btn {
            background: rgba(0, 242, 255, 0.05);
            color: #008a91;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #00f2ff;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bill-btn:hover {
            background: #00f2ff;
            color: #000;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.4);
        }
    </style>
</head>
<body class="light-theme">

    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-history" style="color: #00f2ff;"></i> 
            <?php echo ($lang == 'ta') ? 'எனது ஆர்டர்கள்' : 'My Order History'; ?>
        </h2>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="order-card">
                    <div class="order-info">
                        <strong>Order #<?php echo $row['order_id']; ?></strong><br>
                        <small><i class="far fa-calendar-alt"></i> <?php echo date('d M Y, h:i A', strtotime($row['order_date'])); ?></small>
                    </div>

                    <div class="price-tag">
                        ₹<?php echo number_format($row['total_amount'], 2); ?>
                    </div>

                    <div>
                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </div>

                    <div class="action-buttons">
                        <a href="order_details.php?id=<?php echo $row['order_id']; ?>" class="view-btn">
                            <i class="fas fa-eye"></i> <?php echo ($lang == 'ta') ? 'பார்க்க' : 'Items'; ?>
                        </a>

                        <?php if (strtolower($row['status']) == 'accepted'): ?>
                            <a href="admin/generate_bill.php?id=<?php echo $row['order_id']; ?>" target="_blank" class="bill-btn">
                                <i class="fas fa-file-pdf"></i> <?php echo ($lang == 'ta') ? 'தற்காலிக ரசீது' : 'Bill'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: #fff; border-radius: 15px;">
                <i class="fas fa-shopping-basket" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                <p style="color: #64748b;">No orders placed yet. Start shopping!</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>