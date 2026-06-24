<?php
session_start();
include 'includes/db_config.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $order_id = $_GET['id'];
    $shop_id = $_SESSION['user_id'];

    // Security Check: Order 'Pending'-ah irundha mattum dhaan cancel panna mudiyum
    $check = mysqli_query($conn, "SELECT status FROM orders WHERE order_id = '$order_id' AND shop_id = '$shop_id'");
    $row = mysqli_fetch_assoc($check);

    if ($row && $row['status'] === 'Pending') {
        // Status-ah 'Cancelled'-nu update panrom
        mysqli_query($conn, "UPDATE orders SET status = 'Cancelled' WHERE order_id = '$order_id'");
        header("Location: my_orders.php?msg=Order Cancelled!");
    } else {
        header("Location: my_orders.php?error=Cannot cancel processed order!");
    }
}
?>