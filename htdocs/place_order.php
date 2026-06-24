<?php
session_start();
include 'includes/db_config.php';

// Security Check: User login aagi irukanum, Cart-la items irukanum
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

// User details and Form Data
$shop_id = $_SESSION['user_id']; 
$total_amount = mysqli_real_escape_string($conn, $_POST['total_amount']);
$status = 'Pending'; // Default status for new orders

// ---------------------------------------------------------
// STEP 1: Insert into 'orders' (Header Table) - Only ONCE
// ---------------------------------------------------------
$order_sql = "INSERT INTO orders (shop_id, total_amount, status) VALUES ('$shop_id', '$total_amount', '$status')";

if (mysqli_query($conn, $order_sql)) {
    // Pudhusa create aana Order ID-ah edukurom
    $order_id = mysqli_insert_id($conn); 

    // ---------------------------------------------------------
    // STEP 2: Loop through each item in the Cart Session
    // ---------------------------------------------------------
    foreach ($_SESSION['cart'] as $p_id => $qty) {
        
        // Product oda current price-ah database-la irundhu edukkurom
        $price_check = mysqli_query($conn, "SELECT price FROM products WHERE product_id = '$p_id'");
        $p_data = mysqli_fetch_assoc($price_check);
        $current_unit_price = $p_data['price'];

        // Insert into 'order_items' (Detail Table) - Points to the same $order_id
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                       VALUES ('$order_id', '$p_id', '$qty', '$current_unit_price')";
        
        mysqli_query($conn, $item_sql);
    }

    // ---------------------------------------------------------
    // STEP 3: SUCCESS FLOW
    // ---------------------------------------------------------
    unset($_SESSION['cart']); // Clear the cart

    // Redirect to My Orders Page
    header("Location: my_orders.php?status=success&order_id=" . $order_id);
    exit();

} else {
    // Database error handling
    die("Database Error: " . mysqli_error($conn));
}
?>