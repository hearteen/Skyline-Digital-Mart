<?php
session_start();
include '../includes/db_config.php';

if (!isset($_GET['id'])) {
    die("Invalid Request!");
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Fetch Order & Shop Details
$order_query = "SELECT o.*, u.shop_name, u.whatsapp_no, u.pending_balance 
                FROM orders o 
                JOIN users u ON o.shop_id = u.roll_id 
                WHERE o.order_id = '$order_id'";
$order_res = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_res);

if (!$order) { die("Order Not Found!"); }

// 2. Fetch Order Items
$items_query = "SELECT oi.*, p.brand_en 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = '$order_id'";
$items_res = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #ORD-<?php echo $order_id; ?> | Merchant Flow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --cyan: #00f2ff; --success: #10b981; }
        body { background: var(--bg); color: #f8fafc; font-family: 'Inter', sans-serif; padding: 20px; }
        .invoice-card { background: var(--card); border: 1px solid rgba(0, 242, 255, 0.2); border-radius: 20px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 0 30px rgba(0, 0, 0, 0.5); }
        .bill-header { border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 20px; margin-bottom: 20px; }
        .brand-name { color: var(--cyan); font-weight: 800; letter-spacing: 1px; }
        .item-row { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 10px 0; }
        .total-section { background: rgba(0, 242, 255, 0.05); border-radius: 10px; padding: 15px; margin-top: 20px; }
        .status-stamp { border: 2px solid var(--success); color: var(--success); display: inline-block; padding: 5px 15px; border-radius: 5px; font-weight: 900; transform: rotate(-10deg); opacity: 0.8; margin-top: 10px; }
        .cyan-glow-text { color: var(--cyan); text-shadow: 0 0 10px rgba(0,242,255,0.5); }
        @media print { .no-print { display: none; } body { background: white; color: black !important; } .invoice-card { border: none; box-shadow: none; color: black !important; background: white; } .brand-name, .text-success, .text-info { color: black !important; } }
    </style>
</head>
<body>

<div class="invoice-card">
    <div class="bill-header text-center">
        <h3 class="brand-name">MERCHANT FLOW</h3>
        <p class="small text-secondary mb-0">Official Digital Receipt</p>
        <div class="status-stamp"><?php echo strtoupper($order['status']); ?></div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <small class="text-secondary d-block">Invoice To:</small>
            <strong><?php echo strtoupper($order['shop_name']); ?></strong>
        </div>
        <div class="col-6 text-end">
            <small class="text-secondary d-block">Order Date:</small>
            <strong><?php echo date('d M, Y', strtotime($order['order_date'])); ?></strong><br>
            <small class="text-secondary">#ORD-<?php echo $order_id; ?></small>
        </div>
    </div>

    <div class="items-list">
        <div class="row fw-bold text-secondary border-bottom pb-2 mb-2" style="font-size: 0.8rem;">
            <div class="col-6">PRODUCT</div>
            <div class="col-2 text-center">QTY</div>
            <div class="col-4 text-end">PRICE</div>
        </div>
        <?php while($item = mysqli_fetch_assoc($items_res)): ?>
        <div class="row item-row small">
            <div class="col-6"><?php echo $item['brand_en']; ?></div>
            <div class="col-2 text-center">x<?php echo $item['quantity']; ?></div>
            <div class="col-4 text-end">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="total-section">
        <div class="d-flex justify-content-between align-items-center small">
            <span class="text-secondary">Current Order Bill</span>
            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>

        <?php if ($order['status'] == 'Delivered'): 
            $received = $order['received_amount'];
            $old_outstanding = ($order['pending_balance'] + $received) - $order['total_amount'];
        ?>
            <div class="d-flex justify-content-between align-items-center mt-1 small">
                <span class="text-secondary">Previous Outstanding</span>
                <span class="text-warning">₹<?php echo number_format($old_outstanding, 2); ?></span>
            </div>
            <hr style="border-top: 1px dashed rgba(255,255,255,0.1); margin: 10px 0;">
            <div class="d-flex justify-content-between align-items-center fw-bold">
                <span>Total Payable</span>
                <span class="text-info">₹<?php echo number_format($old_outstanding + $order['total_amount'], 2); ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1 text-success">
                <span class="small italic">Amount Received (Cash)</span>
                <span class="fw-bold">(-) ₹<?php echo number_format($received, 2); ?></span>
            </div>
            <hr style="border-top: 2px solid var(--cyan); margin: 10px 0;">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">REMAINING BALANCE</span>
                <h4 class="cyan-glow-text mb-0">₹<?php echo number_format($order['pending_balance'], 2); ?></h4>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mt-1 small">
                <span class="text-secondary">Previous Outstanding</span>
                <span class="text-warning">₹<?php echo number_format($order['pending_balance'], 2); ?></span>
            </div>
            <hr style="border-top: 1px dashed rgba(255,255,255,0.1); margin: 10px 0;">
            <div class="d-flex justify-content-between align-items-center fw-bold">
                <span>Total Payable</span>
                <h4 class="cyan-glow-text mb-0">₹<?php echo number_format($order['total_amount'] + $order['pending_balance'], 2); ?></h4>
            </div>
            <p class="text-center small text-muted mt-2 mb-0 italic">* Payment details update after delivery</p>
        <?php endif; ?>
    </div>

    <div class="mt-4 text-center text-secondary small no-print">
        <button onclick="window.print()" class="btn btn-sm btn-outline-info me-2"><i class="fas fa-print"></i> Download PDF</button>
        <a href="https://wa.me/<?php echo $order['whatsapp_no']; ?>" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i> Share</a>
    </div>
</div>
</body>
</html>