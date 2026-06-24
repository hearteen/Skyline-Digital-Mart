<?php
session_start();

// Check if data is coming from products.php form
if (isset($_POST['p_id']) && isset($_POST['qty'])) {
    $p_id = $_POST['p_id'];
    $qty = (int)$_POST['qty'];

    // 1. Cart session illana create pannu
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // 2. Item already cart-la irundha quantity-ah update pannu
    if (isset($_SESSION['cart'][$p_id])) {
        $_SESSION['cart'][$p_id] += $qty;
    } else {
        $_SESSION['cart'][$p_id] = $qty;
    }

    // 3. Success Message trigger for Toast Notification
    // Intha message dhaan product.php-la andha neon box-ah kaatum
    $_SESSION['success_msg'] = "Success"; 

    // 4. Redirect back to the same category page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: home.php");
    }
    exit();
} else {
    // Direct-ah intha page-ku vandha home-ku thallidu
    header("Location: home.php");
    exit();
}
?>