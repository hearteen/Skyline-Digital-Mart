<?php
session_start();
include 'includes/db_config.php';

if (isset($_POST['login_btn'])) {
    // 1. Sanitization & Formatting
    $roll_id = mysqli_real_escape_string($conn, trim($_POST['roll_id']));
    $password = trim($_POST['password']);

    // 2. Empty Field Check
    if (empty($roll_id) || empty($password)) {
        header("Location: index.php?error=All fields are required");
        exit();
    }

    // 3. Database Query
    $query = "SELECT * FROM users WHERE roll_id = '$roll_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // 4. Hash Password Verification
        if (password_verify($password, $row['password'])) {
            
            // Security: Regenerate session ID on login to prevent session fixation
            session_regenerate_id(true);

            // Success! Store in Session
            $_SESSION['user_id']   = $row['roll_id'];
            $_SESSION['role']      = $row['role']; 
            $_SESSION['lang']      = $row['language'];
            $_SESSION['shop_name'] = $row['shop_name'];
            $_SESSION['logged_in'] = true;

            // --- ROLE BASED REDIRECT ---
            if ($row['role'] === 'dealer') {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            // Wrong Password
            header("Location: index.php?error=Invalid Credentials");
            exit();
        }
    } else {
        // User not found
        header("Location: index.php?error=Invalid Credentials");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>