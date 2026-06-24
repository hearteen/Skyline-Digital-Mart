<?php
include 'includes/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $owner     = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $location  = mysqli_real_escape_string($conn, $_POST['location']);
    $whatsapp  = mysqli_real_escape_string($conn, $_POST['whatsapp_no']);
    $lang      = $_POST['language'];
    
    // Role logic: Default-ah 'shopkeeper'. 
    // Neenga manual-ah dealer create panna mattum 'dealer' nu maathuvom.
    $role = 'shopkeeper'; 

    // Generating Alphanumeric Roll_ID (Example: SHOP2026)
    $roll_id  = "SHOP" . rand(1000, 9999);
    $password = password_hash("123456", PASSWORD_DEFAULT); // Default Password

    $sql = "INSERT INTO users (roll_id, password, shop_name, owner_name, location, whatsapp_no, language, role) 
            VALUES ('$roll_id', '$password', '$shop_name', '$owner', '$location', '$whatsapp', '$lang', '$role')";

    if (mysqli_query($conn, $sql)) {
        session_start();
        $_SESSION['user_id'] = $roll_id;
        $_SESSION['role']    = $role; // Role-ah session-la store pandrom
        $_SESSION['lang']    = $lang;

        // --- ROLE BASED REDIRECT ---
        if ($role === 'dealer') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>