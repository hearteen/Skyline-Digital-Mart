<?php
session_start();
include '../includes/db_config.php';

// Security Check: Only Dealer can process
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    die("Unauthorized Access!");
}

// --- CASE 1: DELIVERY & PAYMENT SETTLEMENT (POST Method from Modal) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deliver') {
    
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $received_amount = floatval($_POST['received_amount']);

    $fetch_sql = "SELECT o.total_amount, o.shop_id, u.pending_balance 
                  FROM orders o 
                  JOIN users u ON o.shop_id = u.roll_id 
                  WHERE o.order_id = '$order_id'";
    
    $res = mysqli_query($conn, $fetch_sql);
    $data = mysqli_fetch_assoc($res);

    if ($data) {
        $shop_id = $data['shop_id'];
        $order_total = $data['total_amount'];
        $old_balance = $data['pending_balance'];

        // Logic: New Bal = (Old + Current Bill) - Cash Received
        $new_balance = ($old_balance + $order_total) - $received_amount;

        mysqli_begin_transaction($conn);
        try {
            // 1. Status-ah Delivered-nu maathu & vaanguna kaasa record pannu
            mysqli_query($conn, "UPDATE orders SET status = 'Delivered', received_amount = '$received_amount' WHERE order_id = '$order_id'");
            
            // 2. User-oda total pending balance-ah update pannu
            mysqli_query($conn, "UPDATE users SET pending_balance = '$new_balance' WHERE roll_id = '$shop_id'");

            mysqli_commit($conn);
            header("Location: view_orders.php?msg=Delivery Completed. New Balance: ₹" . number_format($new_balance, 2));
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            die("Transaction Failed: " . $e->getMessage());
        }
    }
}

// --- CASE 2: ACCEPT / REJECT ORDER (GET Method from Table Buttons) ---
if (isset($_GET['id']) && isset($_GET['action'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = strtolower($_GET['action']); 
    $status = ($action == 'accept') ? 'Accepted' : 'Rejected';

    $check_sql = "SELECT o.status, o.shop_id, o.total_amount, u.shop_name, u.whatsapp_no, u.pending_balance 
                  FROM orders o 
                  JOIN users u ON o.shop_id = u.roll_id 
                  WHERE o.order_id = '$order_id'";
    
    $check_res = mysqli_query($conn, $check_sql);
    $order_data = mysqli_fetch_assoc($check_res);

    if ($order_data && strcasecmp($order_data['status'], 'pending') === 0) {
        
        $update_query = "UPDATE orders SET status = '$status' WHERE order_id = '$order_id'";
        
        if (mysqli_query($conn, $update_query)) {
            
            if ($status == 'Accepted') {
                $shop_name = $order_data['shop_name'];
                $phone = $order_data['whatsapp_no'];
                $order_total = $order_data['total_amount'];
                $pending_balance = $order_data['pending_balance'];
                
                $total_payable = $order_total + $pending_balance;

                $item_details_msg = "";
                $items_query = mysqli_query($conn, "SELECT oi.quantity, p.brand_en 
                                                   FROM order_items oi 
                                                   JOIN products p ON oi.product_id = p.product_id 
                                                   WHERE oi.order_id = '$order_id'");
                
                while ($item = mysqli_fetch_assoc($items_query)) {
                    $item_details_msg .= "• " . $item['brand_en'] . " (x" . $item['quantity'] . ")\n";
                }

                // WhatsApp Message
                $bill_link = "http://localhost/wholesale/admin/generate_bill.php?id=" . $order_id; 
                $message = "⭐ *ORDER CONFIRMED* ⭐\n\n";
                $message .= "Hello *$shop_name*,\nYour Order *#ORD-$order_id* has been approved! ✅\n\n";
                $message .= "*Summary:*\n" . $item_details_msg . "\n";
                $message .= "--------------------------\n";
                $message .= "Current Bill: ₹" . number_format($order_total, 2) . "\n";
                $message .= "Old Balance: ₹" . number_format($pending_balance, 2) . "\n";
               // $message .= "*Estimated Total: ₹" . number_format($total_payable, 2) . "* 💰\n";
                $message .= "--------------------------\n\n";
               // $message .= "📥 *Digital Bill:* $bill_link";
                
                $wa_url = "https://wa.me/$phone?text=" . urlencode($message);

                echo "
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Order Approved</title>
                    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
                    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
                    <style>
                        body { background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: 'Inter', sans-serif; overflow: hidden; }
                        .card { background: rgba(255,255,255,0.05); padding: 40px; border-radius: 20px; border: 1px solid #00f2ff33; text-align: center; box-shadow: 0 0 20px rgba(0,242,255,0.1); }
                        .btn-wa { background: #10b981; color: white; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: bold; display: block; margin-top: 20px; border: none; }
                        .btn-wa:hover { background: #059669; color: white; transform: translateY(-2px); }
                        .spinner-border { width: 1.5rem; height: 1.5rem; }
                    </style>
                </head>
                <body>
                    <div class='card shadow-lg'>
                        <h2 style='color: #00f2ff;'>Order #$order_id Accepted!</h2>
                        <p class='text-secondary'>Stock updated & notification ready.</p>
                        <h4 class='mt-3'>Estimated Total: ₹".number_format($total_payable, 2)."</h4>
                        
                        <a href='$wa_url' class='btn-wa'><i class='fab fa-whatsapp me-2'></i> SEND WHATSAPP NOW</a>
                        
                        <div class='mt-4 small text-muted'>
                             <div class='spinner-border text-info me-2'></div> Auto-redirecting in 3 seconds...
                        </div>
                        <br>
                        <a href='view_orders.php' style='color: #64748b; text-decoration: none; font-size: 0.8rem;'><i class='fas fa-arrow-left me-1'></i> Return to Dashboard</a>
                    </div>
                    <script>setTimeout(function(){ window.location.href = '$wa_url'; }, 3000);</script>
                </body>
                </html>";
                exit();

            } else {
                header("Location: view_orders.php?msg=Order Rejected Successfully! ❌");
                exit();
            }
        }
    } else {
        header("Location: view_orders.php?msg=Error: Order already processed.");
        exit();
    }
}
?>