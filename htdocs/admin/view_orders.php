<?php
session_start();
include '../includes/db_config.php';
include '../includes/lang.php';

// Security: Check if dealer (Admin) is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

$lang = $_SESSION['lang'] ?? 'en';

// Updated Query: Order status order (Pending first, then Accepted, then Delivered)
$query = "SELECT o.*, u.shop_name, u.whatsapp_no, u.pending_balance 
          FROM orders o 
          JOIN users u ON o.shop_id = u.roll_id 
          ORDER BY 
            CASE 
                WHEN o.status = 'Pending' THEN 1
                WHEN o.status = 'Accepted' THEN 2
                WHEN o.status = 'Delivered' THEN 3
                ELSE 4 
            END, o.order_date DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Hub | Merchant Flow Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #0f172a;
            --cyan-glow: #00f2ff;
            --card-border: rgba(0, 242, 255, 0.2);
        }

        body { background-color: var(--primary-dark); color: #f8fafc; font-family: 'Inter', sans-serif; }
        .dashboard-content { padding: 20px 10px; }
        .welcome-banner {
            background: linear-gradient(135deg, rgba(0, 242, 255, 0.1), transparent);
            border-left: 5px solid var(--cyan-glow);
            padding: 20px; border-radius: 15px; margin-bottom: 30px;
        }
        .table-container {
            background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px);
            border: 1px solid var(--card-border); border-radius: 15px; padding: 15px;
        }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .admin-table th { 
            background: rgba(0, 242, 255, 0.05); padding: 15px; color: var(--cyan-glow); 
            text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;
        }
        .admin-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .price-text { color: #10b981; font-weight: 700; }
        
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .pending-glow { color: #f39c12; border: 1px solid #f39c12; }
        .accepted-glow { color: #3498db; border: 1px solid #3498db; }
        .delivered-glow { color: #10b981; border: 1px solid #10b981; }

        .btn-action { background: none; border: none; font-size: 1.3rem; transition: 0.3s; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .btn-accept { color: #3498db; }
        .btn-deliver { color: #10b981; font-size: 0.8rem; border: 1px solid #10b981; padding: 6px 12px; border-radius: 6px; background: transparent; transition: 0.3s; }
        .btn-deliver:hover { background: #10b981; color: white; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
        .btn-reject { color: #ef4444; }
        .btn-action:hover { transform: scale(1.1); filter: drop-shadow(0 0 8px currentColor); }

        .modal-content { background: #1e293b; border: 1px solid var(--cyan-glow); color: white; border-radius: 15px; }
        .modal-header { border-bottom: 1px solid var(--card-border); }
        .form-control { background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); color: white; }
        .form-control:focus { background: rgba(255,255,255,0.1); color: white; border-color: var(--cyan-glow); box-shadow: 0 0 10px rgba(0,242,255,0.3); }
        
        .bill-btn { color: var(--cyan-glow); border: 1px solid var(--cyan-glow); padding: 5px 10px; border-radius: 5px; font-size: 0.8rem; text-decoration: none; }
        .bill-btn:hover { background: var(--cyan-glow); color: var(--primary-dark); }
    </style>
</head>
<body>

    <?php include 'includes/admin_nav.php'; ?>

    <main class="dashboard-content">
        <div class="container-fluid px-md-5">
            <div class="welcome-banner">
                <h2 class="section-title"><i class="fas fa-boxes me-2"></i> Master Order Control</h2>
                <p class="mb-0 text-secondary small">Manage life cycle of shopkeeper orders and settle payments.</p>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Order Info</th>
                                <th>Partner Shop</th>
                                <th>Bill Amount</th>
                                <th>Status</th>
                                <th>Control Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <span class="text-info fw-bold">#ORD-<?php echo $row['order_id']; ?></span><br>
                                        <small class="text-muted"><?php echo date('d M, Y', strtotime($row['order_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-uppercase"><?php echo $row['shop_name']; ?></strong><br>
                                        <small class="text-warning">Bal: ₹<?php echo number_format($row['pending_balance'], 2); ?></small>
                                    </td>
                                    <td class="price-text">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($row['status']); ?>-glow">
                                            <?php echo strtoupper($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-3 align-items-center">
                                            <?php if($row['status'] == 'Pending'): ?>
                                                <a href="process_order.php?id=<?php echo $row['order_id']; ?>&action=accept" class="btn-action btn-accept" title="Accept Order">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                                <a href="process_order.php?id=<?php echo $row['order_id']; ?>&action=reject" class="btn-action btn-reject" title="Reject Order">
                                                    <i class="fas fa-times-circle"></i>
                                                </a>

                                            <?php elseif($row['status'] == 'Accepted'): ?>
                                                <button class="btn-deliver fw-bold" onclick="openPaymentModal(<?php echo $row['order_id']; ?>, <?php echo $row['total_amount']; ?>, <?php echo $row['pending_balance']; ?>, '<?php echo $row['shop_name']; ?>')">
                                                    <i class="fas fa-truck me-1"></i> DELIVER
                                                </button>

                                            <?php elseif($row['status'] == 'Delivered'): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-success small fw-bold">
                                                        <i class="fas fa-check-double me-1"></i> DONE
                                                    </span>
                                                    <a href="generate_bill.php?id=<?php echo $row['order_id']; ?>" target="_blank" class="bill-btn">
                                                        <i class="fas fa-file-invoice me-1"></i> BILL
                                                    </a>
                                                </div>

                                            <?php else: ?>
                                                <span class="text-muted small italic"><i class="fas fa-lock"></i> Locked</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-5">No orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="process_order.php" method="POST" class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title text-info"><i class="fas fa-hand-holding-usd me-2"></i> Payment Settlement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="m_order_id">
                    <input type="hidden" name="action" value="deliver">
                    
                    <p class="mb-1 text-secondary">Settling bill for: <strong id="m_shop_name" class="text-white"></strong></p>
                    
                    <div class="p-3 my-3 rounded bg-dark border border-secondary">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Current Bill:</span>
                            <span class="text-success fw-bold">₹<span id="m_order_amt">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Previous Outstanding:</span>
                            <span class="text-warning fw-bold">₹<span id="m_old_bal">0.00</span></span>
                        </div>
                        <hr class="border-secondary my-2">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total Payable:</span>
                            <span class="text-info" style="font-size: 1.1rem;">₹<span id="m_total_payable">0.00</span></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-secondary">Amount Collected (Cash)</label>
                        <input type="number" step="0.01" name="received_amount" id="m_received" class="form-control form-control-lg text-center fw-bold" placeholder="0.00" oninput="calculateBalance()" required>
                    </div>

                    <div class="text-center p-2 rounded bg-opacity-10 bg-danger">
                        <span class="small text-secondary">New Pending Balance:</span><br>
                        <span id="m_final_bal" class="h5 fw-bold text-danger">₹0.00</span>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-info w-100 py-2 fw-bold text-dark">
                        <i class="fas fa-check-circle me-2"></i> COMPLETE DELIVERY
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let globalTotalPayable = 0;

        function openPaymentModal(id, orderAmt, oldBal, shop) {
            // Logic: Total = Indha bill + Pazhaya baaki
            globalTotalPayable = parseFloat(orderAmt) + parseFloat(oldBal);
            
            document.getElementById('m_order_id').value = id;
            document.getElementById('m_shop_name').innerText = shop;
            document.getElementById('m_order_amt').innerText = parseFloat(orderAmt).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('m_old_bal').innerText = parseFloat(oldBal).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('m_total_payable').innerText = globalTotalPayable.toLocaleString('en-IN', {minimumFractionDigits: 2});
            
            // Default-ah full amount balance-la kaatu
            document.getElementById('m_final_bal').innerText = "₹" + globalTotalPayable.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('m_received').value = ""; 
            
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }

        function calculateBalance() {
            let received = parseFloat(document.getElementById('m_received').value) || 0;
            let remaining = globalTotalPayable - received;
            document.getElementById('m_final_bal').innerText = "₹" + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2});
        }
    </script>
</body>
</html>