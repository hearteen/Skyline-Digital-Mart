<?php
session_start();
include '../includes/db_config.php';

// Auth Check (Security-kaaga)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dealer') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $cat_id = (int)$_GET['id'];

    // 1. First, andha category-oda image file name-ah edukanum (Folder-la irundhu delete panna)
    $res = mysqli_query($conn, "SELECT cat_image FROM categories WHERE cat_id = $cat_id");
    $row = mysqli_fetch_assoc($res);
    
    if ($row) {
        $image_name = $row['cat_image'];
        $image_path = "../uploads/categories/" . $image_name;

        // 2. Database-la irundhu delete panna
        $delete_sql = "DELETE FROM categories WHERE cat_id = $cat_id";
        
        if (mysqli_query($conn, $delete_sql)) {
            // 3. Image "default_cat.jpg" illa-na mattum folder-la irundhu delete pannanum
            if ($image_name != 'default_cat.jpg' && file_exists($image_path)) {
                unlink($image_path);
            }
            
            $_SESSION['success'] = "Category deleted successfully! 🗑️";
        } else {
            $_SESSION['error'] = "Error deleting category: " . mysqli_error($conn);
        }
    }
}

// Thanda thirumbi manage_categories.php-ke redirect panniduvom
header("Location: manage_categories.php");
exit();
?>